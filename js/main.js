// JavaScript Document
jQuery(function(){
	$('.datepicker').datepicker();
	
	// Popup d'ajout de badge
	$('#a_ajout_badge').on('click', function(){
		$('#popup').html('<div id="ajout_badge"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><p>Numéro de badge : <input type="text" name="badge" maxlength="12" required></p><input type="submit" name="ajout" value="Ajouter le badge"></form></div></div>');
		closePopup();
	});
	
	// Popup de suppression de badge
	$('#a_suppr_badge').on('click', function(){
		$('#popup').html('<div id="suppr_badge"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><p>Numéro de badge : <input type="text" name="badge" maxlength="12" required></p><input type="submit" name="suppr" value="Supprimer le badge"></form></div></div>');
		closePopup();
	});
	
	// Popup d'ajout d'utilisateur à un badge
	$('.newUse').on('click', function(){
		var id = $(this).attr('id');
		$('#popup').html('<div id="newUseForm"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><p>Nom : <input type="text" name="nom" required></p><p>Date d\'entrée : <input type="date" name="date" required class="datepicker" pattern="^(19|20)[0-9]{2}-(0[1-9]|1[012])-([012][0-9]|3[01])$"> <i>yyyy-mm-dd</i></p><input type="hidden" name="assocBadge" value="' + id + '"><input type="submit" name="assoc" value="Associer le badge"></form></div></div>');
		$('.datepicker').datepicker();
		closePopup();
	});
	
	// Popup de validation d'utilisation du badge
	$('.valid_ico').on('click', function(){
		var id = $(this).parent('td').attr('id');
		var bid = $(this).parent('td').attr('bid');
		$('#popup').html('<div id="ajout_badge"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><p>Date de sortie : <input type="date" name="date" required class="datepicker" pattern="^(19|20)[0-9]{2}-(0[1-9]|1[012])-([012][0-9]|3[01])$"> <i>yyyy-mm-dd</i></p><input type="hidden" name="assocBadge" value="' + id + '"><input type="hidden" name="bid" value="' + bid + '"><input type="submit" name="valid" value="Valider l\'utilisateur du badge"></form></div></div>');
		$('.datepicker').datepicker();
		closePopup();
	});
	
	// Popup de modification de l'utilisation du badge
	$('.edit_ico').on('click', function(){
		var id = $(this).parent('td').attr('id');
		var nom = $(this).parent('td').attr('nom');
		var date = $(this).parent('td').attr('date');
		var bid = $(this).parent('td').attr('bid');
		$('#popup').html('<div id="ajout_badge"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><p>Nom : <input type="text" name="nom" required value="' + nom + '"></p><p>Date d\'entrée : <input type="date" name="date" required class="datepicker" pattern="^(19|20)[0-9]{2}-(0[1-9]|1[012])-([012][0-9]|3[01])$" value="' + date + '"> <i>yyyy-mm-dd</i></p><input type="hidden" name="assocBadge" value="' + id + '"><input type="hidden" name="bid" value="' + bid + '"><input type="submit" name="edit" value="Modifier"></form></div></div>');
		$('.datepicker').datepicker();
		closePopup();
	});
	
	// Popup de suppression de l'utilisation du badge
	$('.suppr_ico').on('click', function(){
		var id = $(this).parent('td').attr('id');
		var nom = $(this).parent('td').attr('nom');
		var bid = $(this).parent('td').attr('bid');
		$('#popup').html('<div id="ajout_badge"><div class="popup_block"><a class="close"><img alt="Fermer" title="Fermer la fenêtre" class="btn_close" src="img/close_pop.png"></a><form method="POST" action="badges.php"><input type="hidden" name="assocBadge" value="' + id + '"><input type="hidden" name="bid" value="' + bid + '"><input type="submit" name="delete" value="Supprimer ' + nom + ' du badge ' + id + '"></form></div></div>');
		$('.datepicker').datepicker();
		closePopup();
	});
	
	// Selection du personnel pour l'affichage du mois
	$('.select_user').on('click', function(){
		$.post( "modifications.php", { bid: $(this).attr('bid'), month: $('#month').val(), year: $('#year').val() } );
	});
	
	// Vérification de l'heure rentrée dans le tableau et enregistrement dans un array pour enregistrement
	$('td').keyup(function(){
		var classe = ($(this).attr('class')).split(" ");
		var pos = classe[1];
		if(pos >= 0 && pos < 8){
			var format = /^[0-9]{2}\:[0-9]{2}$/;
			if(!format.test($(this).text()))
				$(this).css('border', 'solid red');
			else{
				$(this).css('border', 'none');
			}
		}
	});

	// Sauvegarde des modifications sur la table des horaires
	$('input#save_table').click(function() {
 		var bid = $('input#save_table').data('bid'); 		
		var month = $('input#save_table').data('month');
		var year = $('input#save_table').data('year');		
		var lignes = document.querySelectorAll('#table_horaires tr');
		var nom = $('caption').text();
		var obj = {};
		// On parcours les lignes en ne prenant pas la première ligne (en-tête) ni la dernière (total)
		for(var i = 1; i < lignes.length - 1; i++){
			var cellules = lignes[i].cells;
			var date = lignes[i].id;
 			//obj[[date]] = {'we': lignes[i].dataset.we};
			obj[[date]] = {};
			
			// On parcours les cellules de la ligne sauf la première
			for(var j = 1; j < cellules.length; j++){
				if(cellules[j].innerText != '\n' && j < 9){
					obj[[date]][j-1] = cellules[j].innerText;
				}
				else if(cellules[j].innerText != '\n' && j == 11){
					obj[[date]]['commentaires'] = cellules[j].textContent;
				}
			}
 		}
		
 		$.ajax({
			url: "../src/ajax.php",
			dataType: 'text',
			type: 'POST',
			data:{
				"bid": bid,
				"month": month,
				"year": year,
				"tab": obj
			},
			success: function (res) {
			  $('#message').text('Enregistrement réussi');
			  setTimeout(function (){
				  $('#message').text('');
			  }, 6000);
			  $('#mod_affich').load('../src/tableau.php', {
				"tri": true,
				"bid": bid,
				"month": month,
				"year": year,
				"nom": nom,
				"js": true
			  });
			},
			error: function(){
			  $('#message').text('Problème lors de l\'enregistrement');
			  setTimeout(function (){
				  $('#message').text('');
			  }, 6000);
			}
		});
 	});
	
	// Alerte lors du reset
	$('input#reset').click(function(e){
		if(confirm("Voulez-vous réinitialiser le tableau ?")){
			$('form').submit();
		}
	});
});

function closePopup(){
	$('.btn_close').click(function(){
		$('#popup').empty();
	});
}

function imprimer_page(){
  window.print();
}

jQuery(function($) {
	$.datepicker.regional['fr'] = {
			renderer: $.ui.datepicker.defaultRenderer,
			monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
			'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
			monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
			'Jul','Aoû','Sep','Oct','Nov','Déc'],
			dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
			dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
			dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
			dateFormat: 'yy-mm-dd',
			firstDay: 1,
			prevText: '&#x3c;Préc', prevStatus: 'Voir le mois précédent',
			prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: 'Voir l\'année précédent',
			nextText: 'Suiv&#x3e;', nextStatus: 'Voir le mois suivant',
			nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: 'Voir l\'année suivant',
			currentText: 'Courant', currentStatus: 'Voir le mois courant',
			todayText: 'Aujourd\'hui', todayStatus: 'Voir aujourd\'hui',
			clearText: 'Effacer', clearStatus: 'Effacer la date sélectionnée',
			closeText: 'Fermer', closeStatus: 'Fermer sans modifier',
			yearStatus: 'Voir une autre année', monthStatus: 'Voir un autre mois',
			weekText: 'Sm', weekStatus: 'Semaine de l\'année',
			dayStatus: '\'Choisir\' le DD d MM',
			defaultStatus: 'Choisir la date',
			isRTL: false,
			numberOfMonths: 1,
			showButtonPanel: true
	};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});

function save_table(bid, month, year){
	
}