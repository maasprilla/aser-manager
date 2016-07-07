	<tr id="<?php echo $produit['Produit']['id'];?>">
		<td>
			<?php echo $this->Form->input('Id.'.$produit['Produit']['id'].'',array('label'=>'','type'=>'checkbox','value'=>$produit['Produit']['id'])); ?>
		</td>
		
		<td><?php echo $produit['Produit']['id']; ?>&nbsp;</td>
		<td>
			<?php if(isset($sections[$produit['Groupe']['section_id']])) echo $sections[$produit['Groupe']['section_id']]; ?>
		</td>
		<td>
			<?php echo $produit['Groupe']['name']; ?>
		</td>
		<td name="nom"><?php echo $produit['Produit']['name']; ?>&nbsp;</td>
		<td name="pa">
			<?php echo $produit['Produit']['PA']; ?>
		</td>
		<?php if(!Configure::read('aser.multi_pv')): ?>
			<td name="pv"><input style="width:100px;" onchange="change_pv(this);" type="text" value="<?php echo $produit['Produit']['PV']; ?>" id="<?php echo $produit['Produit']['id'];?>"/>&nbsp;</td>
		<?php else : ?>
			<?php 
			foreach($bars as $barCode=>$barName):?>
				<td name="pv"><input style="width:80px;" onchange="change_pv(this);" type="text" bar="<?php echo $barCode; ?>"  value="<?php echo $produit['Produit'][$barCode]; ?>" id="<?php echo $produit['Produit']['id'];?>"/>&nbsp;</td>
			<?php endforeach;?>
		<? endif;?>
		<?php if(Configure::read('aser.default_stock')>0):?>
			<td>
			<?php echo $produit['Produit']['quantite']; ?>
		</td>
		<?php endif;?>
		<td>
			<?php echo $produit['Unite']['name']; ?>
		</td>
		<td><?php echo $produit['Produit']['type']; ?>&nbsp;</td>
		<?php if(Configure::read('aser.pharmacie')):?>
		<td><?php echo $produit['Produit']['expiration']; ?>&nbsp;</td>
		<?php endif; ?>
		<?php if(Configure::read('aser.advanced_stock')):?>
		<td><?php echo $produit['Produit']['acc']; ?>&nbsp;</td>
		<?php endif; ?>
			<?php if(Configure::read('aser.comptabilite')):?>
		<td><?php echo $produit['GroupeComptable']['name']; ?>&nbsp;</td>
		<?php endif; ?>
		<td><?php echo $produit['Produit']['min']; ?>&nbsp;</td>
		<td><?php echo $produit['Produit']['description']; ?>&nbsp;</td>
		<td><?php echo $produit['Produit']['actif']; ?>&nbsp;</td>
	</tr>
