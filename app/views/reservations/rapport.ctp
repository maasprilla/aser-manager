<div id='view'>
<div class="document">
<h3><?php echo 'Rapport des Réservations';
		if(isset($date1)){
			echo 'de la période entre le '.$this->MugTime->toFrench($date1).' et le '.$this->MugTime->toFrench($date2);
		}
	?>
</h3>
<br>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th>Tier</th>
			<th>Facture</th>
			<th>Etat de Paiement</th>
			<th>Type Chambre</th>
			<th>Nombre</th>
			<th>Adultes</th>
			<th>Enfants</th>
			<th>Arrivee</th>
			<th>Depart</th>
			<th>PU</th>
			<th>Montant</th>
			<th>Monnaie</th>
			<th>Etat Réservation</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($groupReservations as $groupReservation):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $groupReservation['Tier']['name']; ?></td>
			<td name="facture" valeur="<?php echo $groupReservation['Facture']['id']; ?>">
				<?php echo $this->Html->link($groupReservation['Facture']['numero'], array('controller' => 'factures', 'action' => 'view', $groupReservation['Facture']['id'])); ?>
			</td>
			<td><?php echo  $groupReservation['Facture']['etat']; ?></td>
			<td><?php echo  $groupReservation['TypeChambre']['name']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['nombre']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['adultes']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['enfants']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['arrivee']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['depart']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['PU']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['montant']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['monnaie']; ?></td>
			<td><?php echo  $groupReservation['Reservation']['etat']; ?></td>
	</tr>
<?php endforeach; ?>
	<tr>
		<td>TOTAL</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><?php echo $totaux['montant']+0; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<tr>
</table>
</div>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li class="link" onclick = "print_documents()" >Imprimer</li>
		<li class="link"  onclick = "recherche()" >Options de Recherche</li>
		<li><?php echo $this->Html->link('Liste des Reservations', array('controller' => 'reservations', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link('Gestion des Réservations', array('controller' => 'reservations', 'action' => 'tabella')); ?> </li>
	</ul>
</div>
<!--recherche form -->
<div id="recherche_boxe" style="display:none" title="Options de Recherche">
<div class="dialog">
	<div id="message_recherche"></div>
	<?php echo $this->Form->create('Reservation',array('id'=>'recherche'));?>
	<span class="left">
		<?php
			echo $this->Form->input('Tier.name',array('value'=>'toutes','label'=>'Nom du client'));
			echo $this->Form->input('type_chambre_id',array('selected'=>0,'label'=>'Catégorie de la chambre'));
			echo $this->Form->input('etat',array('label'=>'Choisir l\'etat',
												'options'=>array('toutes'=>'toutes',
																'confirmee'=>'confirmee',
																'en_attente'=>'en_attente',
																'partie'=>'partie',
																'arrivee'=>'arrivee',
																'annulee'=>'annulee'
																)
												)
									);
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('date1',array('label'=>'Choisissez une date début','type'=>'text'));									
			echo $this->Form->input('date2',array('label'=>'et une date fin pour le rapport','type'=>'text'));
  			$options=array('1'=>'Répartition par tier',
   						  '2'=>'Comparaison de deux années'
   						  );
   			$attributes=array('legend'=>false,'value'=>false);
    		echo $form->radio('export',$options,$attributes);
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>