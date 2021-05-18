<?php 
// Affiche la correspondance entre les badges et les noms, en prenant en compte la date d'entrée et de sortie.
include_once('src/header.php'); 
include_once('src/db.php');

$db = db_connect();

if(isset($_POST['badge'])){
	if(strlen($_POST['badge']) == 12 && ctype_alnum($_POST['badge'])){
		if(isset($_POST['ajout'])){
			$db->exec('INSERT INTO badges(badge) VALUES ("' . strtoupper(htmlspecialchars($_POST['badge'])) . '");');
			echo '<p class="success">Le badge ' . strtoupper($_POST['badge']) . ' a bien été ajouté.</p>';
		}
		elseif(isset($_POST['suppr'])){
			$db->exec('DELETE FROM badges WHERE badge = "' . strtoupper(htmlspecialchars($_POST['badge'])) . '";');
			echo '<p class="success">Le badge ' . strtoupper($_POST['badge']) . ' a bien été supprimé.</p>';
		}
	}
	else{
		echo '<p class="error">Le badge rentré ne possède pas 12 caractères alphanumériques.</p>';
	}
}
elseif(isset($_POST['assoc'])){
	$badges_bdd = $db->query('SELECT badge, utilisation FROM badges')->fetchAll(PDO::FETCH_ASSOC);
	if(in_array(array('badge' => $_POST['assocBadge'], 'utilisation' => false), $badges_bdd)){
		$db->exec('INSERT INTO badges_users(badge, nom, debut) VALUES ("' . $_POST['assocBadge'] . '", "' . htmlspecialchars($_POST['nom']) . '", "' . $_POST['date'] . '"); 
		UPDATE badges SET utilisation = 1, user = "' . htmlspecialchars($_POST['nom']) . '" WHERE badge = "' . $_POST['assocBadge'] . '";'
		);
	}
}
elseif(isset($_POST['valid'])){
	$db->exec('UPDATE badges SET utilisation = 0, user = NULL WHERE badge = "' . $_POST['assocBadge'] . '"; 
		UPDATE badges_users SET fin = "' . $_POST['date'] . '" WHERE bid = "' . $_POST['bid'] . '";'
	);
}
elseif(isset($_POST['edit'])){
	$db->exec('UPDATE badges_users SET nom = "' . htmlspecialchars($_POST['nom']) . '", debut = "' . $_POST['date'] . '" WHERE bid = "' . $_POST['bid'] . '";');
}
elseif(isset($_POST['delete'])){
	$db->exec('DELETE FROM badges_users WHERE bid = "' . $_POST['bid'] . '";
		UPDATE badges SET utilisation = 0, user = NULL WHERE badge = "' . $_POST['assocBadge'] . '";'
	);
}

$res = $db->query('SELECT badge, utilisation, user FROM badges;');
$stock = $res->fetchAll(PDO::FETCH_ASSOC);
?>
		<div id="badge_conteneur">
			<div>
				<table>
					<caption>Badges utilisés</caption>
					<tr>
						<th>Numéro de badge</th>
						<th>Nom</th>
						<th>Date d'entrée</th>
						<th>Action</th>
					</tr>
					<?php
					foreach ($stock as $row){
						if($row['utilisation'] == true){
							$entree = $db->query('SELECT bid, debut FROM badges_users WHERE badge = "'. $row['badge'] . '" AND nom = "' . $row['user'] . '";')->fetch();
							echo '<tr>';
							echo '<td>' . $row['badge'] . '</td>';
							echo '<td>' . $row['user'] . '</td>';
							echo '<td>' . $entree['debut'] . '</td>';
							echo '<td id="' . $row['badge'] . '" nom="' . $row['user'] . '" date="' . $entree['debut'] . '" bid="' . $entree['bid'] . '"><a class="valid_ico"><img alt="Clôturer" title="Clôturer" src="img/valid.png"></a> <a class="edit_ico"><img alt="Modifier" title="Modifier" src="img/edit.png"></a> <a class="suppr_ico"><img alt="Supprimer" title="Supprimer" src="img/delete.png"></a></td>';
							echo '</tr>';
						}
					}
					?>
				</table>
				<table>
					<caption>Badges en stock</caption>
					<tr>
						<th>Numéro de badge</th>
						<th>Action</th>
					</tr>
					<?php
					foreach ($stock as $row){
						if($row['utilisation'] == false){
							echo '<tr>';
							echo '<td>' . $row['badge'] . '</td>';
							echo '<td><a class="newUse" id="' . $row['badge'] . '">+</a></td>';
							echo '</tr>';
						}
					}
					?>
				</table>
				<table>
					<caption>Historique</caption>
					<tr>
						<th>Numéro de badge</th>
						<th>Nom</th>
						<th>Date d'entrée</th>
						<th>Date de sortie</th>
					</tr>
					<?php
					$data = $db->query('SELECT badge, nom, debut, fin FROM badges_users WHERE fin IS NOT NULL ORDER BY fin DESC;');
					$utilisation = $data->fetchAll(PDO::FETCH_ASSOC);
					foreach ($utilisation as $row){
						echo '<tr>';
						echo '<td>' . $row['badge'] . '</td>';
						echo '<td>' . $row['nom'] . '</td>';
						echo '<td>' . $row['debut'] . '</td>';
						echo '<td>' . $row['fin'] . '</td>';
						echo '</tr>';
					}
					?>
				</table>
			</div>
			<div id="popup">
			</div>
			<div id="actions">
				<a id="a_ajout_badge">Ajouter un badge en stock</a>
				<a id="a_suppr_badge">Supprimer un badge du stock</a>
			</div>
		</div>
<?php include('src/footer.php'); ?>
