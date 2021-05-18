<?php 
// Affiche les enregistrements de la pointeuse de façon amélioré et paramétrable.
include_once('src/header.php'); 
include_once('src/db.php');

$db = db_connect();

$data = $db->query('SELECT bks_badge, bks_date FROM BKS_FILE ORDER BY bks_date DESC LIMIT 1500', PDO::FETCH_ASSOC);
// Réaliser les filtres d'affichage
?>
		<div id="filtres">
			
		</div>
		<table>
			<tr>
				<th>Numéro de badge</th>
				<th>Correspondance</th>
				<th>Date et heure</th>
			</tr>
			<?php
			foreach ($data as $row){
				$user = $db->query('SELECT nom FROM badges_users WHERE badge = "' . $row['bks_badge'] . '" AND debut <= "' . $row['bks_date'] . '" AND (fin >= "' . $row['bks_date'] . '" OR fin IS NULL);');
				$nom = $user->fetch(PDO::FETCH_ASSOC);
				if(empty($nom)){
					$nom['nom'] = 'N/A';
				}
				
				echo '<tr>';
				echo '<td>' . $row['bks_badge'] . '</td>';
				echo '<td>' . $nom['nom'] . '</td>';
				echo '<td>' . $row['bks_date'] . '</td>';
				echo '</tr>';
			}
			?>
		</table>
<?php include('src/footer.php'); ?>
