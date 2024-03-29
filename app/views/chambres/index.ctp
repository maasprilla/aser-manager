
<div class="chambres index">
	<h2><?php __('Gestion Des Chambres');?></h2>
	
<div id="quick_add">
	<table cellpadding="0" cellspacing="0" class="advanced1">
	
	<tr>
		<th>Numéro de la chambre</th>
		<th>Type De Chambre</th>
		<th>N° d'Etage</th>
		<th>Actions</th>
	</tr>
	<?php for($i=0;$i<1;$i++): ?>
	<tr name="<?php echo $i?>">
		<?php echo $this->Form->create('Chambre',array('action'=>'add'));?>
		<td><?php echo $this->Form->input('name',array('label'=>''));?></td>
		<td><?php echo $this->Form->input('type_chambre_id',array('label'=>''));?></td>
		<td><?php echo $this->Form->input('etage',array('label'=>''));?></td>
		<td><input type="submit" value="Envoyer"/></td>
		</form>
		
	</tr>
	<?php endfor; ?>
</table>
</div>
	<?php echo $this->Form->create('Chambre',array('name'=>'checkbox','action'=>'cleaner','id'=>'Chambre_chambres'));?>	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><input type="checkbox" name="master" value="" onclick="checkAll(document.checkbox)"></th>
			<th><?php echo $this->Paginator->sort('Numéro de la chambre','name');?></th>
			<th><?php echo $this->Paginator->sort('Type De Chambre','type_chambre_id');?></th>
			<th><?php echo $this->Paginator->sort('N° d\'étage','etage');?></th>
			<th><?php echo $this->Paginator->sort('Chambre Opérationnelle','operationnelle');?></th>
			<th><?php echo $this->Paginator->sort('message');?></th>
		</tr>
	<?php
	foreach ($chambres as $chambre){
		echo $this->element('../chambres/add',array('chambre'=>$chambre));
	}
	 ?>
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
		<li class= "link" onclick = "edit()" >Modifier</li>
		<li class="link" onclick="mass_delete()" >Effacer</li>
		<li><?php echo $this->Html->link('Gestion des Réservations', array('controller' => 'reservations', 'action' => 'tabella')); ?> </li>
		<!--
		<li class="link" onclick="cleaner('oui')" >Marquer "PROPRE"</li>
		<li class="link" onclick="cleaner('non')" >Marquer "NON PROPRE"</li>
		<li class="link" onclick="msg_gouvernante()" >Enregistrer un message</li>
		<li><?php echo $this->Html->link('Fiche gouvernante', array('controller' => 'chambres', 'action' => 'fiche')); ?> </li>
		-->	
	</ul>
</div>

<div id="msg_gouvernante" style="display:none" title='Message pour gouvernante'>
<div class="dialog">
	<div id="action_msg"></div>
	<?php echo $this->Form->create('Chambre',array('id'=>'msg','action'=>'message'));?>
	<span class='left'>
		<?php
			echo $this->Form->input('message',array('type'=>'textarea','label'=>''));			
		?>
	</span>
	<span class="right">
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
