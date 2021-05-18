<div id="hot">
	<table id="table_horaires">
	<caption>
		<?php
			if(isset($current))
				echo $current;
			elseif(isset($_POST['nom']))
				echo $_POST['nom'];
		?>
	</caption>
		<thead>
			<tr>
				<th>Date</th>
				<th>Début 1</th>
				<th>Fin 1</th>
				<th>Début 2</th>
				<th>Fin 2</th>
				<th>Début 3</th>
				<th>Fin 3</th>
				<th>Début 4</th>
				<th>Fin 4</th>
				<th>Heures comptabilisées</th>
				<th>Cumul hebdo</th>
				<th>Commentaires</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(isset($_POST['js'])){
				include_once('db.php');
				
				$db = db_connect();
			}
			if((isset($_POST['tri']) || isset($_POST['reset'])) && isset($_POST['bid']) && isset($_POST['month']) && isset($_POST['year'])){
				// TODO : intégrer le reset
				// Faire attention si on est en janvier			
				if($_POST['month'] == 01){
					$moisPrev = mktime(0, 0, 0, 12, 21, $_POST['year'] - 1);
					$sql = 'BETWEEN "' . (intval($_POST['year']) -  1) . '-12-21" AND "' . $_POST['year'] . '-01-20";';
				}
				else {
					$moisPrev = mktime(0, 0, 0, $_POST['month'] - 1, 21, $_POST['year']);
					$sql = 'BETWEEN "' . $_POST['year'] . '-' . (intval($_POST['month']) - 1) . '-21" AND "' . $_POST['year'] . '-' . $_POST['month'] . '-20";';
				}
				$nombreDeJoursPrev = intval(date("t", $moisPrev));
				
				$donnees = array();
				// comptabilisation des jours du mois précédent
				for($i = 21; $i <= $nombreDeJoursPrev; $i++){
					if($_POST['month'] == 01){
						$donnees[date('Y-m-d', mktime(0, 0, 0, 12, $i, $_POST['year'] - 1))]['we'] = date('N', mktime(0, 0, 0, 12, $i, $_POST['year'] - 1));
					}
					else{
						$donnees[date('Y-m-d', mktime(0, 0, 0, $_POST['month'] - 1, $i, $_POST['year']))]['we'] = date('N', mktime(0, 0, 0, $_POST['month'] - 1, $i, $_POST['year']));
					}
				}
				// comptabilisation des jours du mois en cours
				for($i = 1; $i < 21; $i++){
					$donnees[date('Y-m-d', mktime(0, 0, 0, $_POST['month'], $i, $_POST['year']))]['we'] = date('N', mktime(0, 0, 0, $_POST['month'], $i, $_POST['year']));
				}
				
				$req2 = $db->query('SELECT bks_date FROM BKS_FILE WHERE bks_badge = (SELECT badge FROM badges_users WHERE bid = "' . $_POST['bid'] . '") AND bks_date ' . $sql);
				$travail_jour = '';
				while($date = $req2->fetch(PDO::FETCH_ASSOC)){
					$jour = explode(' ', $date['bks_date']);
					// Si on travail sur le même jour
					if($jour[0] == $travail_jour){
						$donnees[$jour[0]][] = substr($date['bks_date'], 11, 5);
					}
					else{
						$donnees[$jour[0]][] = substr($date['bks_date'], 11, 5);
					}
					$travail_jour = $jour[0];
				}

				$liste_modifs = array();
				// On supprime les données si l'utilisateur l'a demandé
				if(isset($_POST['reset'])){
					$db->exec('DELETE FROM modifs WHERE bid ="' . $_POST['bid'] . '" AND date ' . $sql);
				}
				// On incorpore les modifications
				elseif(isset($_POST['tri'])){
					$req = $db->query('SELECT date, d1 "0", f1 "1", d2 "2", f2 "3", d3 "4", f3 "5", d4 "6", f4 "7", commentaires FROM modifs WHERE bid ="' . $_POST['bid'] . '" AND date ' . $sql);
					while($res = $req->fetch(PDO::FETCH_ASSOC)){
						$sup = 0;
						for($i = 0; $i < 8; $i++){
							if(isset($res[$i])){
								$liste_modifs[$res['date']][$i] = (isset($donnees[$res['date']][$i]) ? $donnees[$res['date']][$i] : '--:--');
								$donnees[$res['date']][$i] = $res[$i];
							}
							else{
								$sup++;
							}
						}
						(isset($res['commentaires']) ? $donnees[$res['date']]['commentaires'] = $res['commentaires'] : $sup++);
						if($sup == 9){
							$db->exec('DELETE FROM modifs WHERE bid = "' . $_POST['bid'] . '" AND date = "'. $res['date'] . '";');
						}
					}
				}			

				$donnees = heuresCompta($donnees);
				affiche($donnees, $liste_modifs);
			}
			?>
		</tbody>
	</table>
</div>
<?php // Calcul les heures comptabilisées et le cumul hebdomadaire
function heuresCompta($tableau){
	end($tableau);
	$derniereCle = key($tableau);
	$cumul = 0;
	$total = 0;
	
	foreach($tableau as $date => $valeur){		
		// Calcul des heures comptabilisées
		end($valeur);
		if(isset($valeur['commentaires']))
			prev($valeur);
		$max = key($valeur);
		$compta = '';
		
		for($i = $max - 1; $i >= 0; $i--){
			if($i % 2 == 0 && $valeur[$i+1] != '--:--' && $valeur[$i] != '--:--')
				$compta += strtotime($valeur[$i + 1]) - strtotime($valeur[$i]);
		}
		$tableau[$date]['compta'] = $compta;
		
		// Calcul du cumul hebdo
		if($cumul != 0)
			$cumul += $compta;
		else
			$cumul = $compta;
		
		if($valeur['we'] == 7 || $date === $derniereCle){
			$tableau[$date]['cumul'] = $cumul;
			
			// calcul du total mensuel
			if($total == 0){
				$total = $cumul;
			}
			else{
				$total += $cumul;
			}
			
			$cumul = 0;
		}
	}
	
	$tableau['total'] = $total;
	return $tableau;
}

// Affiche les données dans un tableau éditable
function affiche($tableau, $liste_modifs){
	foreach($tableau as $jour => $row){
		if($jour != 'total'){
			$explode = explode('-', $jour);
			$date = $explode[2] . '-' . $explode[1] . '-' . $explode[0];
			echo '<tr id="' . $jour . '" data-we="' . $row['we'] . '"' . (($row['we'] >= 6) ? ' class="weekend"' : '') . '>';
			echo '<td>' . $date . '</td>';
			for($i = 0; $i < 8; $i++){
				if(!empty($liste_modifs[$jour][$i])){
					echo '<td contenteditable="true" class="' . $jour . ' ' . $i . ' modif">' . ((isset($row[$i])) ? $row[$i] : '') . '<span>' . $liste_modifs[$jour][$i] . '</span></td>';
				}
				else{
					echo '<td contenteditable="true" class="' . $jour . ' ' . $i . '">' . ((isset($row[$i])) ? $row[$i] : '') . '</td>';
				}
			}
			echo '<td class="' . $jour . ' compta">' . ((isset($row['compta'])) ? conversion($row['compta']) : '') . '</td>';
			echo '<td class="' . $jour . ' cumul">' . ((isset($row['cumul'])) ? conversion($row['cumul']) : '') . '</td>';
			echo '<td contenteditable="true" class="' . $jour . ' commentaires">' . ((isset($row['commentaires'])) ? $row['commentaires'] : '') . '</td>';
			echo '</tr>';
		}
		else{
			echo '<tr class="total">';
			echo '<td id="total_label">Total</td>';
			echo '<td id="total" colspan="11">' . conversion($row) . '</td>';
			echo '</tr>';
		}
	}
}

// Converti les secondes en affichage heures : minutes
function conversion($secondes) {
	$temp = $secondes % 3600;
	$time[0] = ( $secondes - $temp ) / 3600 ;
	$time[2] = $temp % 60;
	$time[1] = ( $temp - $time[2] ) / 60;
		
	return $time[0] . 'h ' . $time[1] . 'min';
}
?>
