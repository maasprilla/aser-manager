<?php echo $this->element('../produits/nullable');?>
<div class="dialog">
<?php echo $this->Form->create('Produit',array('id'=>'edit_form'));?>
	<span class="left">
		<?php
			echo $this->Form->input('id');
			echo $this->Form->input('name',array('label'=>'Nom du Produit','id'=>'nom'));
			echo $this->Form->input('PA',array('label'=>'Prix d\'Achat','id'=>'pa'));
			if(!Configure::read('aser.multi_pv'))
				echo $this->Form->input('PV',array('label'=>'Prix de Vente','id'=>'pv'));
			echo $this->element('combobox',array('selected'=>false,'n°'=>3));
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('type',array('label'=>'Type de Produit',
												'options'=>$typeDeProduits,
												'id'=>'type'
												));
			echo $this->Form->input('unite_id',array('label'=>'Unité de Mesure'));
			
			if(Configure::read('aser.pharmacie'))
				echo $this->Form->input('expiration',array('label'=>'Produit expirable'));
			
			if(Configure::read('aser.advanced_stock'))
				echo $this->Form->input('acc',array(
												'options'=>array('avec'=>'avec',
																'acc'=>'acc',
																'sans'=>'sans'
																)
												));
			if(Configure::read('aser.comptabilite'))
				echo $this->Form->input('groupe_comptable_id');
			echo $this->Form->input('min',array('label'=>'Stock Minimale'));
			echo $this->Form->input('description',array('type'=>'text'));
			echo $this->Form->input('actif',array('options'=>array('oui'=>'oui','non'=>'non'),'id'=>'actif'));
			echo $this->Form->input('old_name',array('type'=>'hidden'));
	?>
	</span>
	</form>
<div style="clear:both"></div>
</div>