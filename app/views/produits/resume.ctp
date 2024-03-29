<h3 align=center >Resumé
	<span style="font-size:13px !important; color:#367889; cursor:pointer;" onclick="jQuery('#filter').toggle();">
		(Options de recherche)
	</span>
</h3>
<fieldset class="recherche">
<div id="filter" style="display:none;" class="recherche">
<fieldset>
<?php echo $this->Form->create('Produit');?>
<?php
		//echo $this->Form->input('Produit.id',array('label'=>'Produit id','type'=>'text'));
		echo $this->Form->input('date1',array('label'=>'Choisissez une date début',
												'type'=>'text',
												'format'=>'d-m-y')
											);									
		echo $this->Form->input('date2',array('label'=>'et une date fin pour le rapport',
												'type'=>'text',
												'format'=>'d-m-y')
											);
		echo $this->Form->end(__('Envoyer', true));
		?>
</fieldset>
</div>

<?php if(!empty($resume['Entree'])):?>
<div class="conteneur">
<h4 style="cursor:pointer" onclick="jQuery('#Entree').toggle();" title="Réduire / Etendre" >* Entrees</h4>
<table id="Entree" class="tableau" cellpadding="0" cellspacing="0">
	<tr>
			<th>Produits</th>
			<th>Element</th>
			<th>quantité</th>
			<th>Montant</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($resume['Entree'] as $Entree):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $Entree['Produit']['name']; ?></td>
			<td><?php echo  $Entree['Entree']['element']; ?></td>
			<td><?php echo  $Entree['Entree']['quantite']; ?></td>
			<td><?php echo  $Entree['Entree']['montant']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if(!empty($resume['Sorti'])):?>
<div class="conteneur">
<h4 style="cursor:pointer" onclick="jQuery('#sorti').toggle();" title="Réduire / Etendre" >* Sortis</h4>
<table id="sorti" class="tableau" cellpadding="0" cellspacing="0">
	<tr>
			<th>Produits</th>
			<th>Elément</th>
			<th>quantité</th>
			<th>Montant</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($resume['Sorti'] as $sorti):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $sorti['Produit']['name']; ?></td>
			<td><?php echo  $sorti['Sorti']['element']; ?></td>
			<td><?php echo  $sorti['Sorti']['quantite']; ?></td>
			<td><?php echo  $sorti['Sorti']['montant']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if(!empty($resume['Retour'])):?>
<div class="conteneur">
<h4 style="cursor:pointer" onclick="jQuery('#Retour').toggle();" title="Réduire / Etendre" >* Retours</h4>
<table id="Retour" class="tableau" cellpadding="0" cellspacing="0">
	<tr >
			<th>Opération</th>
			<th>Produit</th>
			<th>Element</th>
			<th>Quantite</th>
			<th>Montant</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($resume['Retour'] as $Retour):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $Retour['Retour']['operation']; ?></td>
			<td><?php echo  $Retour['Produit']['name']; ?></td>
			<td><?php echo  $Retour['Retour']['element']; ?></td>
			<td><?php echo  $Retour['Retour']['quantite']; ?></td>
			<td><?php echo  $Retour['Retour']['montant']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if(!empty($resume['Perte'])):?>
<div class="conteneur">
<h4 style="cursor:pointer" onclick="jQuery('#Perte').toggle();" title="Réduire / Etendre" >* Pertes</h4>
<table id="Perte" class="tableau" cellpadding="0" cellspacing="0">
	<tr >
			<th>Produit</th>
			<th>Element</th>
			<th>Nature</th>
			<th>Quantite</th>
			<th>Total</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($resume['Perte'] as $Perte):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $Perte['Produit']['name']; ?></td>
			<td><?php echo  $Perte['Perte']['element']; ?></td>
			<td><?php echo  $Perte['Perte']['nature']; ?></td>
			<td><?php echo  $Perte['Perte']['quantite']; ?></td>
			<td><?php echo  $Perte['Perte']['total']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if(!empty($resume['Pret'])):?>
<div class="conteneur">
<h4 style="cursor:pointer" onclick="jQuery('#Pret').toggle();" title="Réduire / Etendre" >* Prets</h4>
<table id="Pret" class="tableau" cellpadding="0" cellspacing="0">
	<tr >
			<th>Produit</th>
			<th>Element</th>
			<th>Pris</th>
			<th>Remis</th>
			<th>Reste</th>
		
	</tr>
		<?php
	$i = 0;
	foreach ($resume['Pret'] as $Pret):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
			<td><?php echo  $Pret['Produit']['name']; ?></td>
			<td><?php echo  $Pret['Pret']['element']; ?></td>
			<td><?php echo  $Pret['Pret']['pris']; ?></td>
			<td><?php echo  $Pret['Pret']['remis']; ?></td>
			<td><?php echo  $Pret['Pret']['reste']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
</fieldset>