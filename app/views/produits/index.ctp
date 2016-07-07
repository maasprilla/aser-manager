<?php $config=Configure::read('aser'); ?>
<div class="produits index">
	<h2 id="produits" caissier="<?php echo $caissier;?>" serveur="<?php echo $serveur;?>" fonction="<?php echo $fonction;?>"><?php __('Gestions Des Produits');?></h2>
	
	<!--recherche form -->
<?php echo $this->element('../produits/recherche',array('action'=>'index'));?>
<div id="quick_add">
	<table cellpadding="0" cellspacing="0" class="advanced1">
	
	<tr>
		<th>Nom Du Produit</th>
		<th>Prix D'Achat</th>
		<?php if(!Configure::read('aser.multi_pv')): ?>
		<th>Prix De Vente</th>
		<?php endif;?>
		<th>Section</th>
		<th>Groupe</th>
		<th>Type Du Produit</th>
		<th>Unité De Mesure</th>
		<?php if(Configure::read('aser.pharmacie')):?>
			<th>Produit Périssable</th>
		<?php endif;?>
		<?php if($config['advanced_stock']):?>
			<th title="">Accompagnement</th>
		<?php endif;?>
		
		<?php if(Configure::read('aser.comptabilite')):?>
			<th>Groupe Comptable</th>
		<?php endif;?>
		<th>Stock Min</th>
		<th>Description</th>
		<th>Actions</th>
	</tr>
	<?php for($i=0;$i<1;$i++): ?>
	<tr name="<?php echo $i?>">
		<?php echo $this->Form->create('Produit',array('action'=>'add'));?>
		<td><?php echo $ajax->autoComplete($i.'produit','/produits/autoComplete/actif',array('id'=>$i.'produit',
																					'name'=>'data[Produit][name]'));?>
		</td>
		<td><?php echo $this->Form->input('PA',array('id'=>'PA','label'=>'','value'=>0));?></td>
		<?php if(!Configure::read('aser.multi_pv')): ?>
			<td><?php echo $this->Form->input('PV',array('id'=>'PV','label'=>'','value'=>0));?></td>
		<?php else : ?>
			<?php echo $this->Form->input('PV',array('type'=>'hidden','value'=>0));?>
		<?php endif;?>
		<td><?php echo $this->Form->input('section_id',array('id'=>$i.'SectionId','label'=>'','selected'=>0,'title'=>'Le nom de la section auquel appartient le produit'));
				echo $ajax->observeField($i.'SectionId', array('url' => 'updateGroupe/0/1','update' => 'groupe'.$i,
    		'loading'=>'jQuery("#loading'.$i.'").attr("class","advanced_loading").show();',
    		'complete'=>'jQuery("#loading'.$i.'").attr("class","advanced_loading").hide();'
    	));
		?></td>
		<td><?php echo '<span id="groupe'.$i.'">'.$this->Form->input('groupe_id',array('label'=>'','selected'=>0)).'</span>';?></td>
		<td><?php echo $this->Form->input('type',array('label'=>'',
													'id'=>'type',
													'selected'=>'stockable',
													'options'=>$typeDeProduits
													)
										);?>
		</td>
		<td><?php echo $this->Form->input('unite_id',array('label'=>'','class'=>'nullable','selected'=>0));?></td>
		<?php if(Configure::read('aser.pharmacie')): ?>
			<td><?php echo $this->Form->input('expiration',array('label'=>'','class'=>'nullable'));?></td>
		<?php endif; ?>
		<?php if($config['advanced_stock']):?>
		<td><?php echo $this->Form->input('acc',array('label'=>'',
															'options'=>array('sans'=>'sans',
																			'avec'=>'avec',
																			'acc'=>'acc',
																			)
															)
										);?>
		</td>
		<?php endif; ?>
		<?php if(Configure::read('aser.comptabilite')): ?>
			<td><?php echo $this->Form->input('groupe_comptable_id',array('label'=>'','options'=>$groupeComptables));?></td>
		<?php endif; ?>
		<td><?php echo $this->Form->input('min',array('label'=>'','class'=>'nullable','value'=>10));?></td>
		<td><?php echo $this->Form->input('description',array('label'=>''));?></td>
		<?php echo $this->Form->input('actif',array('type'=>'hidden','value'=>'oui'));?>
		<td><input type="submit" value="Envoyer"/></td>
		</form>
		
	</tr>
	<?php endfor; ?>
</table>
</div>
	<?php echo $this->Form->create('Produit',array('name'=>'checkbox','id'=>'Produit_produits','action'=>'index'));?>	
	<table cellpadding="0" cellspacing="0">
	<tr id="first">
		<th><input type="checkbox" name="master" value="" onclick="checkAll(document.checkbox)"></th>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('section_id');?></th>
			<th><?php echo $this->Paginator->sort('groupe_id');?></th>
			<th><?php echo $this->Paginator->sort('Nom du Produit','name');?></th>
			<th><?php echo $this->Paginator->sort('Prix D\'Achat','PA');?></th>
		<?php if(!Configure::read('aser.multi_pv')): ?>
			<th><?php echo $this->Paginator->sort('Prix De Vente','PV');?></th>
		<?php else : ?>
			<?php foreach($bars as $bar):?>
				<th><?php echo $bar;?></th>
			<?php endforeach;?>
		<? endif;?>
		<?php if(Configure::read('aser.default_stock')>0):?>
			<th><?php echo $this->Paginator->sort('quantite');?></th>
		<? endif;?>
		<th><?php echo $this->Paginator->sort('Unité De Mesure','unite_id');?></th>
		<th><?php echo $this->Paginator->sort('Type De Produit','type');?></th>
		<?php if(Configure::read('aser.pharmacie')): ?>
			<th><?php echo $this->Paginator->sort('Produit Périssable','expiration');?></th>
		<?php endif; ?>
		<?php if($config['advanced_stock']):?>
			<th><?php echo $this->Paginator->sort('Accompagnement','acc');?></th>
		<?php endif; ?>
		
		<?php if(Configure::read('aser.comptabilite')): ?>
			<th><?php echo $this->Paginator->sort('groupe_comptable_id');?></th>
		<?php endif; ?>
			<th><?php echo $this->Paginator->sort('min');?></th>
			<th><?php echo $this->Paginator->sort('description');?></th>
			<th><?php echo $this->Paginator->sort('actif');?></th>
		</tr>
	<?php
	foreach ($produits as $produit) {
		echo $this->element('../produits/add',array('produit'=>$produit));
	}
	?>
	</table>
</form>
<div id="quantites" style='display:none;'></div>
<div id='produit_tarifs' style='display:none;'></div>
	<div id="pagination">
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
</div>
<div id="separator" class="back" title="Cacher Le Menu" onclick="hider()"></div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li class= "link" onclick = "edit('produits')" >Modifier</li>
		<li class= "link" onclick = "mass_delete()">Effacer</li>
		<li class="link"  onclick = "recherche()" >Options de Recherche</li>
		<li class="link"  onclick="quantites()" >Afficher les Quantités</li>
		<li class="link"  onclick="actions('checkbox','historique')" >Afficher l'Historique du Stock</li>
		<li  class="link" onclick = "mass_modification()" >Modification en Masse</li>
		<?php if(false&&Configure::read('aser.multi_pv')):?>
			<li class="link"  onclick = "produit_tarifs('checkbox','index')" >Afficher/Masquer des Tarifs</li>
			<span id="produit_tarifs_links" style="display:none">
				<li class="link"  onclick = "produit_tarifs('checkbox','add')" >Créer Tarif</li>
				<li class="link" onclick = "produit_tarifs('checkbox','delete','produit_tarifs_form')" >Effacer Tarif</li>
			</span>
		<?php endif;?>
		<li><?php echo $this->Html->link(sprintf(__('%s', true), __('Rapport Des Produits', true)), array('controller' => 'produits', 'action' => 'rapport')); ?> </li>
		<li><?php echo $this->Html->link('Mouvements Des Produits', array('controller' => 'produits', 'action' => 'balance')); ?> </li>
		<li class= "link" onclick = "merge('produits')" ><? echo  __('Fusionner les Enregistrements');?></li>
		<li class="link"  onclick="actions('checkbox','trace')" >Historique des Modifications </li>
		<?php if(Configure::read('aser.ingredient')):?>
			<li class= "link" onclick = "ingredient_boxe()" ><? echo  __('Gerer les ingredients');?></li>
		<?php endif;?>
	</ul>
</div>


<!-- stock select for historique -->
<div id="historique_boxe" style="display:none" title="Afficher l'historique">
<div class="dialog">
	
	<div id="message_tarif"></div>
	<?php echo $this->Form->create('Produit',array('id'=>'historique_form'));?>
	<span class="left">
		<?php
			echo $this->Form->input('Historique.stock_id',array('label'=>'Stock'));
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('Historique.date1',array('label'=>'Choisissez une date début'));
			echo $this->Form->input('Historique.date2',array('label'=>'et une date fin pour le rapport','type'=>'text'));
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
<!-- form for tarif creation -->
<div id="tarif_boxe" style="display:none" title="Création d'un tarif">
<div class="dialog">
	<?php echo $this->Form->create('Produit',array('id'=>'tarifAdd','action'=>'tarif_add'));?>
	<span class="left">
		<?php	
			echo $this->Form->input('Tarif.name',array('label'=>'Nom','id'=>'place','options'=>$bars));
		?>
	</span>
	<span class="right">
		<?php
			echo $this->Form->input('Tarif.PV',array('id'=>'pu'));
		?>
	</span>
	</form>
<div style="clear:both"></div>
</div>
</div>
<div id="mass_modification" title="Modification en masse" style="display:none">
	<div class="dialog">
		<span class="left">
		<?php 
			echo $this->element('combobox',array('n°'=>2));
			echo $this->Form->input('unite_id',array('options'=>$unites,'selected'=>0));
		?>
		</span>
		<span class="right">
			<?php
				echo $this->Form->input('type',array('options'=>$typeDeProduits1,
													'selected'=>0,
												'label'=>'Type De Produit'
												));
				if(Configure::read('aser.advanced_stock')) {
					echo $this->Form->input('acc',array('label'=>'Accompagnement',
																'options'=>array(''=>'',
																				'avec'=>'avec',
																				'acc'=>'acc',
																				'sans'=>'sans'
																				)
																)
											);
				}
				if(Configure::read('aser.comptabilite')) 
					echo $this->Form->input('groupe_comptable_id',array('options'=>$groupeComptables1));
					
				echo $this->Form->input('actif',array('label'=>'Actif',
													'options'=>array(''=>'',
																	'oui'=>'oui',
																	'non'=>'non',																		)
															)
										);
			?>
		</span>
	</div>
</div>
<!-- form for perte creation -->
<?php echo $this->element('../pertes/perte');?>

<!--merge form -->
<?php echo $this->element('merge');?>
<!--gestion ingredient form box -->
<div id="ing_boxe" style="display:none" title="Gestion des ingredients">
</div>