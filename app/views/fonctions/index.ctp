<div class="fonctions index">
	<h2><?php __('Fonctions');?></h2>
	<?php echo $this->Form->create('Fonction',array('name'=>'checkbox','id'=>'Fonction_fonctions'));?>	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><input type="checkbox" name="master" value="" onclick="checkAll(document.checkbox)"></th>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('personnel_id');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
		</tr>
	<?php
	$i = 0;
	$j = 0;
	foreach ($fonctions as $fonction):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Form->input('Id.'.$j.'',array('label'=>'','type'=>'checkbox','value'=>$fonction['Fonction']['id'])); ?>
		</td>
		<td><?php echo $fonction['Fonction']['id']; ?>&nbsp;</td>
		<td><?php echo $fonction['Fonction']['name']; ?>&nbsp;</td>
		<td><?php echo $fonction['Fonction']['description']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($fonction['Personnel']['name'], array('controller' => 'personnels', 'action' => 'view', $fonction['Personnel']['id'])); ?>
		</td>
		<td><?php echo $fonction['Fonction']['created']; ?>&nbsp;</td>
		<td><?php echo $fonction['Fonction']['modified']; ?>&nbsp;</td>
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
	</ul>
</div>
