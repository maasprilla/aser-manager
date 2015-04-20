<div class="proformas form">
<?php echo $this->Form->create('Proforma');?>
	<fieldset>
 		<legend class="add"><?php printf(__('Add %s', true), __('Proforma', true)); ?></legend>
	<?php
		echo $this->Form->input('facture_id');
		echo $this->Form->input('name');
		echo $this->Form->input('quantite');
		echo $this->Form->input('PU');
		echo $this->Form->input('montant');
		echo $this->Form->input('monnaie');	?>
	</fieldset>
<?php echo $this->Form->end(__('Envoyer', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(sprintf(__('Lister %s', true), __('Proformas', true)), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(sprintf(__('Lister %s', true), __('Factures', true)), array('controller' => 'factures', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Créer %s', true), __('Facture', true)), array('controller' => 'factures', 'action' => 'add')); ?> </li>
		
	</ul>
</div>
