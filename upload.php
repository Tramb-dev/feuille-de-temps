<?php include_once('src/header.php'); ?>
<form method="POST" action="upload.php" enctype="multipart/form-data">	
	Fichier BKS_FILE : <input type="file" name="bks">
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
	<input type="submit" name="envoyer" value="Envoyer le fichier">
</form> 

<?php
if(isset($_FILES['bks'])){
	// Vérifie l'extension
	if(strrchr($_FILES['bks']['name'], '.') != '.TXT'){
		$erreur = 'Vous devez uploader un fichier de type .TXT';
	}

	// Vérifie la taille
	$taille_maxi = 1000000;
	$taille = filesize($_FILES['bks']['tmp_name']);
	if($taille>$taille_maxi){
		$erreur = 'Le fichier est trop gros.';
	}
	
	// Vérifie le nom
	$fichier = basename($_FILES['bks']['name']);
	if($fichier !== 'BKS_FILE.TXT'){
		$erreur = 'Mauvais nom de fichier.';
	}
	
	$dossier = 'upload/';
	if(!isset($erreur)){
		if(move_uploaded_file($_FILES['bks']['tmp_name'], $dossier . $fichier)){
			parse_bks($dossier . $fichier);
			
			echo 'Upload effectué avec succès !';
			unlink($dossier . $fichier);
		}
		else{
			echo 'Echec de l\'upload !';
		}
	}
	else{
		echo $erreur;
	}
}

function parse_bks($file){
	include_once('src/db.php');
	$db = db_connect();
	@$handle = fopen($file, 'r') or die('Ouverture en lecture de "' . $fichier . '" impossible !');
	$insert_badge = $db->prepare('INSERT INTO BKS_FILE(bks_badge, bks_date) VALUES(:badge, :newDate)');
	
	$insert_badge->bindParam(':badge', $badge, PDO::PARAM_STR);
	$insert_badge->bindParam(':newDate', $dateheure, PDO::PARAM_STR);
	
	$data = $db->query('SELECT bks_badge, bks_date FROM BKS_FILE ORDER BY bks_date ASC', PDO::FETCH_ASSOC)->fetchAll();
	
	while(!feof($handle)){
		$ligne = fgets($handle);
		$badge = substr($ligne, 0, 12);
		$date = substr($ligne, 12, 10);
		$heure = substr($ligne, 22, 8);

		if($badge != '' && $date != '' && $heure != ''){
			$SQLDateTime = DateTime::createFromFormat('d/m/Y', $date);
			$newDate = $SQLDateTime->format('Y-m-d');			
			$dateheure = $newDate . ' ' . $heure;
			if(!in_array(array('bks_badge' => $badge, 'bks_date' => $dateheure), $data)){
				$insert_badge->execute();
			}
		}
	}
	$db = null;
	fclose($handle);	
}

include('src/footer.php');		
?>