<?php 
// Affiche les feuilles de temps par personne et par mois, modifiable
include_once('src/header.php'); 
include_once('src/db.php');

$db = db_connect();

if(isset($_POST['month'])){
	switch(intval($_POST['month'])){
		case '1':
		case '2':
		case '3':
		case '4':
		case '5';
		case '6':
		case '7':
		case '8':
		case '9':
			$_POST['month'] = '0' . intval($_POST['month']);
			break;
	}
}

?>
<div id="mod_container">
	<section id="mod_filtres">
		<form method="POST" action="modifications.php">
			<fieldset id="mod_user">
				<legend>Personnel</legend>
				<?php
					$users = $db->query('SELECT bid, badge, nom FROM badges_users ORDER BY nom ASC');
					foreach($users as $row){
						if(isset($_POST['bid']) && $_POST['bid'] == $row['bid']){
							$checked = 'checked';
							$badge = $row['badge'];
							$current = $row['nom'];
						}
						else
							$checked = '';
						
						echo '<input type="radio" name="bid" value="' . $row['bid'] . '" ' . $checked . '>' . $row['nom'] . '</br>';
					}
				?>
			</fieldset>
			<fieldset id="mod_date">
				<legend>Date</legend>
				Mois : <input type="text" id="month" name="month" value="<?php echo (isset($_POST['month'])) ? $_POST['month'] : date('n'); ?>" maxlength="2" size="2"><br/>
				Année : <input type="year" id="year" name="year" value="<?php echo (isset($_POST['year'])) ? $_POST['year'] : date('Y'); ?>" maxlength="4" size="4"><br/>
				<input type="submit" name="tri" value="Valider">
			</fieldset>
			<fieldset>
			<legend>Modifications du tableau</legend>
				<input type="button" id="save_table" name="save_table" value="Enregistrer" <?php echo (isset($_POST['month']) && isset($_POST['year']) && isset($_POST['bid'])) ? 'data-bid="' . $_POST['bid'] . '" data-month="' . $_POST['month'] . '" data-year="' . $_POST['year'] . '"' : ''; ?>><br/>
				<input type="submit" id="reset" name="reset" value="Reset">
			</fieldset>
			<input id="impression" name="impression" type="button" onclick="imprimer_page()" value="Imprimer cette page">
		</form>
	</section>
	<div id="popup"></div>
	<section id="mod_affich">
		<?php include('src/tableau.php'); ?>
	</section>
</div>
<?php
include('src/footer.php');


?>


