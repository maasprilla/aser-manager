<script>
 jQuery.noConflict();
     jQuery(document).ready(function(){
    resto_date();
	indicator();
	jQuery('fieldset#form_vente').bind('keypress', function(e) {
		if ((e.keyCode || e.which) == 13){
			var etat_facture=jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text();
			var classement=jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name');
			var printed=jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed');
			 if((factureId!=0)&&(etat_facture!='annulee')&&(classement!='1')&&(printed!='1'))
  				 resto_create(factureId);
		 }	
	});
	jQuery('input#cash').bind('keypress', function(e) {
		if ((e.keyCode || e.which) == 13){
			if(jQuery('span#montant').text()!=''){
				var change=parseInt(jQuery('input#cash').val())-parseInt(jQuery('span#montant').text());
				jQuery('input#change').val(change);
			}
		 }	
	});
	jQuery('fieldset#paiement').bind('keypress',function(e) {
		if ((e.keyCode || e.which) == 13){
			return false
		 }	
	});
	
	var key_press=true;
	
	jQuery('input,select,textarea').focus(function(){
		 key_press=false;
	})
	jQuery('input,select,textarea').blur(function(){
		 key_press=true;
	})
	//shortcut for printing
	jQuery(document).bind('keypress',function(e) {
		
		if (((e.keyCode || e.which || e.charCode) ==80)||((e.keyCode || e.which || e.charCode) ==112)){
			//sama stuff
			 if(print&&key_press&&(factureId!=0)&&(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text()!='annulee')){
  				//	 print_facture(factureId);
  			}
		 }	
	});
	jQuery('#VenteBeneficiaire').change(function(){
		if(jQuery(this).val()!=''){
			jQuery('.ben').removeAttr('disabled');
		}
		else {
			jQuery('.ben').attr('disabled','disabled');
		}
	})
	//for getting client ari muri chambre
	jQuery('#chambre').bind('keypress', function(e) {
		if ((e.keyCode || e.which) == 13){
  				 chambre();
		 }	
	});
	
	jQuery('#equi').change(function(){
		var equi=jQuery(this).val();
 		if(equi!=''){
 			jQuery('#monnaie').removeAttr('disabled');
 		}
 		else {
 			jQuery('#monnaie').attr('disabled','disabled');
 		}
 	})
 	jQuery('#mode').change(function(){
 		
 		if(jQuery(this).val()!='cash'){
 			jQuery('#monnaie option[value="EUR"]').hide();
 		}
 		else {
 			jQuery('#monnaie option[value="EUR"]').show();
 		}
 	})
 	/*	
 	if(jQuery('#pos').attr('longlist')=='1'){
 		//filter list of produits
		jQuery('#produits').selectFilter({
  		'clearInputOnEscape': true,
    	'disableRegex': true,
    	// The class to apply to the filter bar.
    	'filterClass': 'filter-select',
    	'inputPlaceholder': 'Taper pour filtrer',
    	'minimumCharacters': 1,
    	'minimumSelectElementSize': 3,
    	// Amount of time to delay filtering (in ms) after a key is pressed.
    	'searchDelay':0,
    	'searchFromBeginning':false,
    	// The width for the select element and its input filter box.
    	// If -1, both the select element and its filter box have their size set to the width of
    	// the select element before any filtering occurs.
    	'width': -1
  		});
  	}
  	//*/
 	window.onload=function(){ 
    	if(jQuery('#pos').attr('stock')==''){
       		parameters();		
       	}
       	if(jQuery('#pos').attr('search-detailed-names')=='1'){
       		detailed_products_names();
       	}
    }
    jQuery('#GroupeId option:first').attr('selected','selected') //making sure the first group is always selected at page load;
});
</script>
<?php
		$config=Configure::read('aser');
?>
<div id="pos" 
	class="vente"
	magasin="<?php echo $config['magasin'];?>"
	beneficiaires="<?php echo $config['beneficiaires'];?>"
	thermal="<?php echo $thermal;?>"
	touch="off"
	stock="<?php echo $stock;?>"
	fonction="<?php echo $session->read('Auth.Personnel.fonction_id');?>"
	printonce="<?php echo  $config['one_time_printing'];?>"
	search-detailed-names="<?php echo  ($config['multi_pv']||($config['default_stock']<1))? 1 : 0;?>"
	>
<div id="resto_layout">
	<div id="message_tier"></div>
	<span id='first'>
		<?php echo $this->Form->create('Vente',array('id'=>'vente_add','action'=>'add'));?>
		<fieldset id="form_vente"><legend><?php __('Détails Vente');?></legend>
		<?php
			if(Configure::read('aser.groupes_on_index')){
				echo $this->Form->input('groupe_id',array('id'=>'GroupeId','label'=>'Catégorie'));
				echo $ajax->observeField('GroupeId', array('url' => 'combobox','update' => 'resto_produits'));
			}
			echo '<span id="resto_produits">'.$this->Form->input('produit_id',array('id'=>'produits')).'</span>';

			if(Configure::read('aser.manual_bill_numbering')){
				echo $this->Form->input('facture_numero',array('value'=>'', 'id'=>'facture_numero', 'label'=>"N° de la Facture"));
			}
			if(!$config['magasin']&&$config['bon']){
			//	echo $this->Form->input('garnish',array('options'=>$garnishList,'selected'=>0,'label'=>'Accompagnements'));
			}
			if(!$config['magasin']){
				echo $this->Form->input('table',array('value'=>0));
			}

			echo $this->Form->input('quantite',array('value'=>1,'label'=>'Quantité'));
			if(Configure::read('aser.PU'))
				echo $this->Form->input('PU',array('label'=>'Autre Prix Unitaire'));
			if(!$config['magasin']) {
				echo $this->Form->input('personnel_id',array('selected'=>0,'label'=>'Serveur'));
			 }
			if($config['beneficiaires']) {
				echo $this->Form->input('beneficiaire',array('label'=>'Béneficiaire'));
				echo $this->Form->input('pourcentage',array('class'=>'ben','disabled'=>'disabled'));
				if($config['detailed_ben']) {
					echo $this->Form->input('matricule',array('label'=>'N° de Matricule'));
					echo $this->Form->input('liasse',array('label'=>'N° de Liasse'));
					echo $this->Form->input('employeur',array('label'=>'Nom de l\'employeur'));
				 }
			 }
		?>
		</fieldset>
		<fieldset id="form_client"><legend>Client Info</legend>
		<?php
			if(Configure::read('aser.hotel'))
				echo $this->Form->input('chambre',array('id'=>'chambre'));
			echo $this->Form->input('tier_id',array('id'=>'tierId','selected'=>0,'options'=>$tiers1,'label'=>'Client'));
		?>
		</fieldset>
		</form>
	</span>
	
	<span id="second">
		<h4>(Utilisateur connecté : <?php echo ucfirst($session->read('Auth.Personnel.identifiant')); ?>)</h4>
		<fieldset id="list_factures"><legend>Liste des Factures </legend>
			<table cellpadding="0" cellspacing="0" id="list_factures">
				<tr>
					<th>N° Facture</th>
					<?php if(!$config['magasin']): ?>
						<th>Table</th>
					<?php endif; ?>
					<th>Sous Total</th>
					<th>Reduction (%)</th>
					<th>Total</th>
					<th>Etat</th>
				<?php if(!$config['magasin']): ?>
					<th>Serveur</th>
				<?php endif; ?>
					<th><?php echo $this->Form->input('date_resto',array('label'=>'','value'=>$date,'id'=>'DateResto','type'=>'text'));?></th>
				</tr>
				<?php echo $this->element('../ventes/list_factures',array('factures'=>$factures));?>
			</table>
		</fieldset>
		<fieldset id="list_produits"><legend>Liste des Produits</legend>
			<table cellpadding="0" cellspacing="0" id="list_produits">
				<tr>
						<th></th>
				<?php if($config['beneficiaires']): ?>
					<th>%</th>
				<?php endif; ?>
					<th>Produit</th>
					<th>Quantité</th>
					<th>PU</th>
					<th>PT</th>
					<th>Tps</th>
				</tr>
			</table>
		</fieldset>
	</span>
	<span id='third'>
		<fieldset id="vente_info"><legend>Détails Facture</legend>
			<span class="info">Facture: <span id="facture"></span></span>
			<span class="info">Client : <span id="client" name=""></span></span>
			<?php if(Configure::read('aser.beneficiaires')):?>
			<span class="info">Béneficiaire : <span id="ben"></span></span>
				<?php if(Configure::read('aser.detailed_ben')):?>
					<span class="info">N° de matricule : <span id="matricule"></span></span>
					<span class="info">N° de liasse : <span id="liasse"></span></span>
					<span class="info">Nom de l'employeur : <span id="employeur"></span></span>
				<?php endif;?>
			<?php endif;?>
		
		</fieldset>
		<fieldset id="paiement"><legend>Réglement</legend>
			
			<span class="info">Montant Total : <span id="montant"></span></span>
			<span class="info">Reste : <span id="reste"></span></span>
		<form>
		<?php
				echo '<span class="bouttons">';
				echo '<span name="payment" value="1" class="boutton_selected" onclick="switcher(this)">PAYEE</span>';	
				echo '<span name="payment" value="2" class="boutton" onclick="switcher(this)">CREDIT</span>';
				if(Configure::read('aser.bonus'))	
					echo '<span name="payment" value="3" class="boutton" onclick="switcher(this)">BONUS</span>';	
				echo '<span name="payment" value="4" class="boutton" onclick="switcher(this)">AVANCE</span>';	
				echo '</span>';
			echo $this->Form->input('avance',array('value'=>0,'id'=>'avance'));
		if($change=='oui'){
			echo $this->Form->input('cash',array('value'=>0,'id'=>'cash'));
			echo $this->Form->input('change',array('value'=>0,'id'=>'change'));
		}
			echo $this->Form->input('observation',array('type'=>'textarea'));
		?>
			</form>
		</fieldset>
	</span>
	<fieldset id="actions"><legend>Actions</legend>
		<?php if(!in_array($fonction,array(3,5,8))): ?>
			<span class="boutton" onclick="resto_create('creation')" title="Créer la nouvelle facture">Créer</span>
		<?php endif;?> 
		<span id="add_facture" name="disable" class="boutton" title="ajouter la nouvelle consommation à la facture sélectionnée">Ajouter</span>
		<span id="remove_facture" name="annuler" class="boutton"  title="Annuler la facture">Annuler</span>
		<span id="remove_conso" name="disable" class="boutton"  title="Supprimer la consommation de la facture sélectionnée ">Effacer</span>
		<?php if(in_array($fonction,array(3,5,8))): ?>
			<span id="direct_reduction" name="disable" class="boutton"  title="Réduire directement le montant de la facture ">Réduction</span>
			<span id="unlock" name="unlock" class="boutton"  title="Débloquer la facture" onclick="unlock(factureId)">Débloquer</span>
		<?php endif;?> 
		<span id="paiement_facture" name="classer" class="boutton" title="Clôturer la facture en marquant son état de paiement">Classer</span>
		<span class="boutton" onclick="print_facture(factureId)" title="Imprimer la facture">Imprimer</span>
		<?php if(Configure::read('aser.xls_copy')):?>
			<span class="boutton" onclick="custom_printing(factureId, 'ventes/print_facture/'+factureId)" title="Imprimer la facture">Imprimer avec les Détails</span>
		<?php endif;?>
		<?php if($config['multi_resto']): ?>
			<span class="boutton" onclick="parameters()" title="Configurer les paramètres">Paramètres</span>
		<?php endif;?> 
		<?php if($config['stock_option_caisse']): ?>
			<span class="boutton" onclick="edit_produit()" title="Modifier un produit">Modifier Produit</span>
			<span class="boutton" onclick="add_produit()" title="Créer un produit de type non_stockable">Créer Produit</span>
		<?php endif;?> 
		<?php if(!$config['magasin']): ?>
			<span id="serveur" name="disable" class="boutton" title="Changer le Serveur">Serveur</span>
			<span id="table" name="disable" class="boutton" title="Changer la table">Table</span>
		<?php endif;?> 
		<?php if(Configure::read('aser.manual_bill_numbering')): ?>
			<span id="change_bill_number" name="disable" class="boutton" title="Changer le numero de la facture">N° Facture</span>
		<?php endif;?> 
		<?php if(!$config['magasin']&&$config['bon']): ?>
			<span class="boutton" onclick="ask(factureId)" title="Imprimer les bons pour le bar et la cuisine pour la facture sélectionnée">Bon</span>
		<?php endif;?> 
		<?php if(!Configure::read('aser.manual_bill_numbering')): ?>
		<span id ="separator" name="disable" class="boutton" title="Séparer une facture en deux ou plus">Séparateur</span>
		<?php endif;?> 

		<?php if($config['client_auto_creation']): ?>
			<span class="boutton" onclick="resto_tier()" title="Créer un nouveau client">Client</span>
		<?php endif;?>
		<span class="boutton" onclick="document.location.href=getBase()+'ventes/journal'" title="Rapport Caisse">Rapport</span>
	</fieldset>
</div>
</div>
<?php if(Configure::read('aser.multi_resto')){
	echo $this->element('../ventes/parametres',array('bars'=>$bars,'stocks'=>$stocks,'action'=>'index'));
}
?>
<div id="edit_boxe" style="display:none" title='Modifier le produit'>
</div>
<div id="add_produit" style="display:none" title='Créer un nouveau produit de type non stockable</span>'
<div class="dialog">
	<div id="message_produit"></div>
	<?php echo $this->Form->create('Produit',array('id'=>'add_produit','action'=>'add'));?>
	<span class="left">
		<?php
			echo $this->element('combobox',array('n°'=>1));
			if(Configure::read('aser.comptabilite')){
				echo $this->Form->input('Produit.groupe_comptable_id',array('label'=>'Groupe comptable','options'=>$groupeComptables1));
			}
		?>
	</span>
	<span class="right">
		<?php
			echo '<label>Nom</label>';
			echo $ajax->autoComplete('produit','/produits/autoComplete/actif',array('id'=>'produit',
																					'name'=>'data[Produit][name]',
																					));
			if(!Configure::read('aser.multi_pv'))
				echo $this->Form->input('Produit.PV',array('value'=>0));
			echo $this->Form->input('type',array('value'=>'non_stockable','type'=>'hidden'));	
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
<?php echo $this->element('../ventes/commande');?>
<div id="add_client" style="display:none" title='Créer un nouveau client</span>'
<div class="dialog">
	<div id="message_client"></div>
	<?php echo $this->element('../tiers/edit',array('action'=>'add'));?>
<div style="clear:both"></div>
</div>
</div>

<!-- form for paiement creation -->
<div id="pyt_boxe" style="display:none" title="Détails du Paiement">
<div class="dialog">
	<div id="message_pyt"></div>
	<?php echo $this->Form->create('Paiement');?>
	<span class="left">
		<?php
			echo $this->Form->input('mode_paiement',array('id'=>'mode'));
			echo $this->Form->input('reference',array('id'=>'ref'));
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('montant_equivalent',array('id'=>'equi'));
			echo $this->Form->input('monnaie',array('id'=>'monnaie','disabled'=>'disabled','selected'=>'USD'));
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
<!--printing iframe -->
<iframe id="printPage" name="printPage" src='' style="position:absolute;top:0px; left:0px;width:0px; height:0px;border:0px;overfow:none; z-index:-1"></iframe>
