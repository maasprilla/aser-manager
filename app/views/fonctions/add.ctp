<div class="fonctions form">
<?php echo $this->Form->create('Fonction');?>
	<fieldset>
 		<legend  class="add"><?php printf(__('Ajouter %s', true), __('Fonction', true)); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Envoyer', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(sprintf(__('Lister %s', true), __('Fonctions', true)), array('action' => 'index'));?></li>
	</ul>
</div>
