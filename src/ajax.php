<?php
include_once('db.php');

$db = db_connect();

if(isset($_POST['bid']) && isset($_POST['month']) && isset($_POST['year']) && isset($_POST['tab'])){
	$ajax = $_POST['tab'];
	
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
	
	// On calcule le tableau initial
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
	
	$conversion =array(
		0						=>	'd1',
		1						=>	'f1',
		2						=>	'd2',
		3						=>	'f2',
		4						=>	'd3',
		5						=>	'f3',
		6						=>	'd4',
		7						=>	'f4',
		'commentaires'	=>	'commentaires'
	);
	
	// On récupère les informations déjà enregistrées
	$req = $db->query('SELECT date, (SELECT time_format (d1, "%H:%i")) "0", (SELECT time_format (f1, "%H:%i")) "1", (SELECT time_format (d2, "%H:%i")) "2", (SELECT time_format (f2, "%H:%i")) "3", (SELECT time_format (d3, "%H:%i")) "4", (SELECT time_format (f3, "%H:%i")) "5", (SELECT time_format (d4, "%H:%i")) "6", (SELECT time_format (f4, "%H:%i")) "7", commentaires FROM modifs WHERE bid ="' . $_POST['bid'] . '" AND date ' . $sql);
	$modifs = $req->fetchAll(PDO::FETCH_ASSOC);
	$modifs = chercheCle($modifs);
	
	$prep = $db->prepare('INSERT INTO modifs (bid, date, d1, f1, d2, f2, d3, f3, d4, f4, commentaires) VALUES (:bid, :date, :d1, :f1, :d2, :f2, :d3, :f3, :d4, :f4, :commentaires) ON DUPLICATE KEY UPDATE d1=:d1, f1=:f1, d2=:d2, f2=:f2, d3=:d3, f3=:f3, d4=:d4, f4=:f4, commentaires=:commentaires;');
	$prep->bindParam(':bid', $_POST['bid'], PDO::PARAM_INT, 4);
	$prep->bindParam(':date', $date, PDO::PARAM_STR, 10);
	$prep->bindParam(':d1', $aEnregistrer[0], PDO::PARAM_STR, 5);
	$prep->bindParam(':f1', $aEnregistrer[1], PDO::PARAM_STR, 5);
	$prep->bindParam(':d2', $aEnregistrer[2], PDO::PARAM_STR, 5);
	$prep->bindParam(':f2', $aEnregistrer[3], PDO::PARAM_STR, 5);
	$prep->bindParam(':d3', $aEnregistrer[4], PDO::PARAM_STR, 5);
	$prep->bindParam(':f3', $aEnregistrer[5], PDO::PARAM_STR, 5);
	$prep->bindParam(':d4', $aEnregistrer[6], PDO::PARAM_STR, 5);
	$prep->bindParam(':f4', $aEnregistrer[7], PDO::PARAM_STR, 5);
	$prep->bindParam(':commentaires', $aEnregistrer['commentaires'], PDO::PARAM_STR);

	// On supprime les valeurs modifiées en bdd qui disparaissent du tableau
	$aSupprimer = array();
	foreach($modifs as $date => $value){
		$aSupprimer = array_diff_assoc($value, $ajax[$date]);
		if(!empty($aSupprimer)){
			foreach($aSupprimer as $key => $delete){
				if($delete != null)
					$db->exec('UPDATE modifs SET ' . $conversion[$key] . ' = NULL WHERE bid = "' . $_POST['bid'] . '" AND date = "' . $date . '";');
			}
		}
	}

	// On incorpore en bdd les valeurs qui ont été supprimées du tableau
	$aVider = array();
	foreach($donnees as $date => $value){
		$aVider = array_diff_assoc($value, $ajax[$date]);
		if(!empty($aVider)){
			foreach($aVider as $key => $insert){
				if(array_key_exists($date, $modifs)){
					$db->exec('UPDATE modifs SET ' . $conversion[$key] . ' = "--:--" WHERE bid = "' . $_POST['bid'] . '" AND date = "' . $date . '";');
				}
				else{
					$db->exec('INSERT INTO modifs (bid, date, ' . $conversion[$key] . ') VALUES ("' . $_POST['bid'] . '", "' . $date . '", "--:--");');
				}
			}
		}
	}
	
	// On incorpore en bdd les nouvelles valeurs du tableau
	$aEnregistrer = array();
	foreach($ajax as $date => $value){
		if(array_key_exists($date, $modifs)){
			$aEnregistrer = array_diff_assoc($value, $modifs[$date], $donnees[$date]);
			if(!empty($aEnregistrer)){
				foreach($aEnregistrer as $key =>	$update){
					$db->exec('UPDATE modifs SET ' . $conversion[$key] . ' = "' . $update . '" WHERE bid = "' . $_POST['bid'] . '" AND date = "' . $date . '";');
				}
			}
		}
		else{
			$aEnregistrer = array_diff_assoc($value, $donnees[$date]);
			if(!empty($aEnregistrer)){
				$i = 0;
				foreach($aEnregistrer as $key =>	$insert){
					if($i == 0){
						$db->exec('INSERT INTO modifs (bid, date, ' . $conversion[$key] . ') VALUES ("' . $_POST['bid'] . '", "' . $date . '", "' . $insert . '");');
						$i++;
					}
					else{
						$db->exec('UPDATE modifs SET ' . $conversion[$key] . ' = "' . $insert . '" WHERE bid = "' . $_POST['bid'] . '" AND date = "' . $date . '";');
					}
				}
			}
		}
	}
}

function chercheCle($tableau){
	$new_tab = array();
	foreach($tableau as $value){
		$new_tab[$value['date']] = $value;
		unset($new_tab[$value['date']]['date']);
	}
	return $new_tab;
}


?>