<div class="dialog">
<?php echo $this->Form->create('Groupe',array('id'=>'edit_form'));?>
	<span class="left">
		<?php
		echo $this->Form->input('id');
		echo $this->Form->input('section_id');
		echo $this->Form->input('name');
		?>
	</span>
	<span class="right">
		<?php
		echo $this->Form->input('afficher',array('options'=>array('oui'=>'oui','non'=>'non')));
		echo $this->Form->input('accompagnement',array('options'=>array('oui'=>'oui','non'=>'non')));
		echo $this->Form->input('actif',array('options'=>array('oui'=>'oui','non'=>'non')));
	?>
	</span>
	</form>
<div style="clear:both"></div>
</div>