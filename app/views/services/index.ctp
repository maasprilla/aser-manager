<div class="services index">
	<h2><?php __('Services');?></h2>

	<!--recherche form -->
<div id="recherche_boxe" style="display:none" title="Options de Recherche">
<div class="dialog">
	<div id="message_recherche"></div>
	<?php echo $this->Form->create('Service',array('id'=>'recherche'));?>
	<span class="left">
		<?php
			echo $this->Form->input('Facture.numero',array('value'=>''));
			echo $this->Form->input('Service.tier_id',array('selected'=>0,'options'=>$tiers1));
			echo $this->Form->input('Facture.etat',array('options'=>$etats1));
			echo $this->Form->input('montant',array('value'=>''));
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('type_service_id',array('selected'=>0));
			echo $this->Form->input('Facture.monnaie',array('options'=>$monnaies1));
			echo $this->Form->input('Facture.date1',array('label'=>'Choisissez une date début','type'=>'text'));				
			echo $this->Form->input('Facture.date2',array('label'=>'et une date fin pour la recherche','type'=>'text'));	
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
	<?php echo $this->Form->create('Service',array('name'=>'checkbox','id'=>'Service_services'));?>	
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><input type="checkbox" name="master" value="" onclick="checkAll(document.checkbox)"></th>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('tier_id');?></th>
			<th><?php echo $this->Paginator->sort('facture_id');?></th>
			<th><?php echo $this->Paginator->sort('Paiement','Facture.etat');?></th>
			<th><?php echo $this->Paginator->sort('type_service_id');?></th>
			<th><?php echo $this->Paginator->sort('montant');?></th>
			<th><?php echo $this->Paginator->sort('monnaie');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('personnel_id');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
		</tr>
	<?php
	$i = 0;
	$j = 0;
	foreach ($services as $service):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Form->input('Id.'.$j.'',array('label'=>'','type'=>'checkbox','value'=>$service['Service']['id'])); ?>
		</td>
		<td><?php echo $service['Service']['id']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($service['Tier']['name'], array('controller' => 'tiers', 'action' => 'view', $service['Tier']['id'])); ?>
		</td>
		<td name="facture" valeur="<?php echo $service['Facture']['id']; ?>">
			<?php echo $this->Html->link($service['Facture']['numero'], array('controller' => 'factures', 'action' => 'view', $service['Facture']['id'])); ?>
		</td>
		<td><?php echo $service['Facture']['etat']; ?></td>
		<td>
			<?php echo $this->Html->link($service['TypeService']['name'], array('controller' => 'type_services', 'action' => 'view', $service['TypeService']['id'])); ?>
		</td>
		<td><?php echo $service['Service']['montant']; ?>&nbsp;</td>
		<td><?php echo $service['Service']['monnaie']; ?>&nbsp;</td>
		<td><?php echo $service['Service']['description']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($service['Personnel']['name'], array('controller' => 'personnels', 'action' => 'view', $service['Personnel']['id'])); ?>
		</td>
		<td><?php echo $service['Service']['created']; ?>&nbsp;</td>
		<td><?php echo $service['Service']['modified']; ?>&nbsp;</td>
	</tr>
<?php  $j++; endforeach; ?>
	</table>
</form>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% de %pages%, affichage de %current% enregistrements sur %count% au total, à partir du numéro %start%, jusqu\'au numéro %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< '.__('précédent', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('suivant', true).' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div id="separator" class="back" title="Cacher Le Menu" onclick="hider()"></div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		
		<li><?php echo $this->Html->link(sprintf(__('Créer %s', true), __('Service', true)), array('action' => 'add')); ?></li>
		<li class= "link" onclick = "actions('checkbox','edit')" >Modifier</li>
		<li class="link" onclick="actions('checkbox','delete')" >Effacer</li>
		<li class="link"  onclick = "recherche()" >Options de Recherche</li>
		<li><?php echo $this->Html->link('Edition de Rapport', array('controller' => 'services', 'action' => 'rapport')); ?> </li>
		<?php echo $this->element('docs',array('actions'=>array('factures'),'type'=>'links')); ?>
	</ul>
</div>
<!-- Divs for commande , bon & factures dialog-->
<?php echo $this->element('docs',array('actions'=>array('factures'),'model'=>'Service','type'=>'divs')); ?>

<!--gestion des services dialog box -->
<div id="serv_boxe" style="display:none" title="Gestion des services">
</div>