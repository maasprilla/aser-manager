<div id='view'>
<div class="document">
<div id="entete">
	<div class="left" >
		<?php echo $this->element('company'); ?>
	</div>
	<div id="facture_details">
			<?php echo 'Créée le : '.$this->MugTime->toFrench($facture['Facture']['date']);?>	<br />
			<?php if(!in_array($facture['Facture']['date_emission'],array('0000-00-00','',null))) echo 'Emise le : '.$this->MugTime->toFrench($facture['Facture']['date_emission']).'<br />';?>	
			<?php 
				$etat=($type=='proforma')?'proforma':$facture['Facture']['etat'];
				echo 'Etat :  <span id="etat">'.$etat.'</span>';
			?>	<br />
			<?php if(!empty($facture['Facture']['observation']))
					echo 'Motif : '.$facture['Facture']['observation'];
					echo '<br/>';
			?>
			<?php if(!empty($facture['Personnel']['name']))
					echo 'Serveur : '.$facture['Personnel']['name'];
			?><br />
			<?php if(!empty($journalInfo)){
					echo 'Rapport du : '.$this->MugTime->toFrench($journalInfo['Journal']['date']);
					echo ' de : '.$journalInfo['Personnel']['name'].'<br/>';
					echo $this->Html->link("N° : ".$journalInfo['Journal']['numero'], array('controller' => 'ventes', 'action' => 'journal', $journalInfo['Journal']['id']));
					}
			?><br />
			<?php if(Configure::read('aser.xls_copy') && $facture['Facture']['aserb_num']){
					echo 'Numéro lié '.$facture['Facture']['aserb_num'];
				}
			?>
		</div>
	<div class="right">
		
		<?php  
			if(!in_array($facture['Facture']['date_emission'],array('0000-00-00','',null)))  echo '<span id="dateId">Date : '.$this->MugTime->toFrench($facture['Facture']['date_emission']).'</span><br/>';
			else  echo '<span id="dateId">Date : '.$this->MugTime->toFrench($facture['Facture']['date']).'</span><br/>';
			echo '<br/><br/><br/><br/>';
		?>	
			<div id="client_details">
				<?php 
					echo $this->element('../tiers/details',array('client'=>$facture['Tier']));
					if(!empty($facture['Facture']['bon_commande'])) echo '<br/> Bon de commande : '.$facture['Facture']['bon_commande'].'<br/>';
					if(!empty($facture['Facture']['beneficiaire'])&&(Configure::read('aser.beneficiaires'))) echo '<br/> Béneficiaire : '.$facture['Facture']['beneficiaire'].'<br/>';
				?>
			</div>
		 <?php 
		 //exit(debug($locationInfo));
		 if($model=='Location' && $locationInfo['Location']['arrivee'] && $locationInfo['Location']['depart']){
		 	echo 'Date d\'entrée : '.$this->MugTime->toFrench($locationInfo['Location']['arrivee']);
			echo '<br/>';
			echo 'Date de sortie : '.$this->MugTime->toFrench($locationInfo['Location']['depart']);
			}
		?>
		 <br/>
	</div>
	<div style="clear:both"></div>
</div>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br/>
<br/>
<div id="billView">
<span class="titre">Facture 
	<?php echo $nature;?>
		 <span id="displayed_num" xls_copy="<?php echo Configure::read('aser.xls_copy')*1 ?>">
			<?php echo ' n° '.$facture['Facture']['numero'];?>
		</span>
	 </span>
<br>
	<?php if(($nature=='Proforma')&&(!empty($facture['Facture']['date_validite']))) {
			echo '<h4>(valide jusqu\'au '.$this->MugTime->toFrench($facture['Facture']['date_validite']).')</h4>';
		}
	?>
<br/>
<br/>
<br/><br />
<?php if($model=='Reservation') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th>Date</th>
			<th>N° de Chambre</th>
			<th>Montant (<?php echo $facture['Facture']['monnaie']; ?>)</th>
	</tr>
		<?php

	foreach ($modelInfos as $book):

	?>
	<?php 
		$nuitee=$this->MugTime->diff( $book['Reservation']['arrivee'], $book['Reservation']['depart'])+1;
		for($i=0;$i<$nuitee;$i++):
	?>
			<tr>
				<td><?php echo  $this->MugTime->toFrench($this->MugTime->increase_date($book['Reservation']['arrivee'],$i)); ?></td>
				<td><?php echo  $book['Chambre']['name']; ?></td>
				<td><?php echo  $number->format($book['Reservation']['PU'],$formatting); ?></td>
			</tr>
	<?php endfor;?>
<?php endforeach; ?>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format($facture['Facture']['tva']+0); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<td>&nbsp;</td>
		<td><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>RESTE A PAYER</td>
		<td>&nbsp;</td>
		<td><span id="reste_a_payer"><?php echo ''.$number->format($facture['Facture']['reste']+0); ?></span></td>
	<tr>
</table>
<?php endif;?>
<?php if($model=='Vente') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
			<th>Date</th>
			<th>Béneficiaire</th>
		<?php endif; ?>
			<th>Produit</th>
			<th>Quantité</th>
			<th>PU</th>
			<th>Montant (en <?php echo $facture['Facture']['monnaie'];?>)</th>
	</tr>
		<?php
	$i = 0;
	foreach ($modelInfos as $vente):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
			<td><?php echo  $vente['Facture']['date']; ?></td>
			<td><?php echo $facture['Facture']['beneficiaire']; ?></td>
		<?php endif; ?>
			<td><?php echo  $vente['Produit']['name']; ?></td>
			<td><?php echo  $vente['Vente']['quantite']; ?></td>
			<td><?php echo  $number->format($vente['Vente']['PU'],$formatting); ?></td>
			<td style="text-align=right;"><?php echo  $number->format($vente['Vente']['montant']+0,$formatting); ?></td>
	</tr>
<?php endforeach; ?>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0,$formatting); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span ><?php echo ''.$number->format($facture['Facture']['tva']+0,$formatting); ?></span></td>
	<tr>
<?php endif;?>
<?php if($facture['Facture']['reduction']!=0) :?>
	<tr class="strong">
		<td>SOUS TOTAL</td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span ><?php echo ''.$number->format($facture['Facture']['original']+0,$formatting); ?></span></td>
	<tr>
	<tr class="strong">
		<td>REDUCTION</td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span ><?php echo ' - '.($facture['Facture']['original']-$facture['Facture']['montant']+0); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0,$formatting); ?></span></td>
	<tr>
	<tr class="strong">
		<td>RESTE A PAYER</td>
		<?php if(Configure::read('aser.beneficiaires')&&!empty($facture['Facture']['beneficiaire'])):?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	<?php endif; ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="text-align=right;"><span id="reste_a_payer"><?php echo ''.$number->format($facture['Facture']['reste']+0,$formatting); ?></span></td>
	<tr>
</table>
<br />
<br />
<br />
<?php endif;?>
<br>

<?php if($model=='Location') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th>Libellé</th>
			<th>Quantité</th>
			<th>PU</th>
			<th>Montant (<?php echo $facture['Facture']['monnaie']; ?>)</th>
	</tr>
		<?php
	$i = 0;
	foreach ($modelInfos as $location):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr>
			<?php if((!is_null($location['LocationExtra']['heure']))&&($location['LocationExtra']['extra']=='non')):?>
				<td><?php echo  $location['LocationExtra']['name'];
						if(!empty($location['LocationExtra']['heure'])) echo ' à '.$location['LocationExtra']['heure']; 
					?>
				</td>
			<?php else: ?>
					<td><?php echo  $location['LocationExtra']['name']; ?></td>
			<?php endif;?>
			<td><?php echo  $number->format($location['LocationExtra']['quantite'],$formatting); ?></td>
			<td><?php echo  $number->format($location['LocationExtra']['PU'],$formatting); ?></td>
			<td><?php echo  $number->format($location['LocationExtra']['montant'],$formatting); ?></td>
	</tr>
<?php endforeach; ?>
<?php
	foreach ($ventes as $vente):
		
	?>
	<tr>
			<td><?php echo  $vente['Produit']['name']; ?></td>
			<td><?php echo  $number->format($vente['Vente']['quantite'],$formatting); ?></td>
			<td><?php echo  $number->format($vente['Vente']['PU'],$formatting); ?></td>
			<td><?php echo  $number->format($vente['Vente']['montant'],$formatting); ?></td>
	</tr>
<?php endforeach; ?>

<?php
	foreach ($services as $service):
		
	?>
	<tr>
			<td><?php echo  $service['Service']['description']; ?></td>
			<td><?php echo  1;?></td>
			<td><?php echo  $number->format($service['Service']['montant'],$formatting); ?></td>
			<td><?php echo  $number->format($service['Service']['montant'],$formatting); ?></td>
	</tr>
<?php endforeach; ?>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0,$formatting); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format($facture['Facture']['tva']+0,$formatting); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0,$formatting); ?></span></td>
	<tr>
<?php if(!in_array($type,array('proforma','globale'))):?>
	<tr class="strong">
		<td>RESTE A PAYER</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span id="reste_a_payer"><?php echo ''.$number->format($facture['Facture']['reste']+0,$formatting); ?></span></td>
	<tr>
<?php endif; ?>
</table>
<br />
<br />
<br />
<br />
<div class="bas_page">
	<div class="left">
		<div class="text">
		
		</div>
	</div>
	<div class="right"><?php  
		echo ucwords($signature).'<br />';
	?>
	</div>
	<div style="clear:both"></div>
</div>
<br>
<br>
<p>
	<?php 
	if(!is_array($warning)&& ($warning!=''))
		echo $warning; 
	?> 
</p>

<?php endif;?>
<br>
<?php if($model=='Proforma') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th>Produit</th>
			<th>Quantité</th>
			<th>PU</th>
			<th>Montant (<?php echo $facture['Facture']['monnaie']; ?>)</th>
	</tr>
		<?php
	$i = 0;
	foreach ($modelInfos as $vente):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr>
			<td><?php echo  $vente['Produit']['name']; ?></td>
			<td><?php echo  $vente['Proforma']['quantite'].' '.$vente['Unite']['name']; ?></td>
			<td><?php echo  $number->format($vente['Proforma']['PU']); ?></td>
			<td><?php echo  $number->format($vente['Proforma']['montant']); ?></td>
	</tr>
<?php endforeach; ?>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span ><?php echo ''.$number->format($facture['Facture']['tva']+0); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0); ?></span></td>
	<tr>
</table>
<?php endif;?>
<br>

<?php if($model=='Service') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<?php if(!Configure::read('aser.mahanaim')):?>
				<th>Type de Service</th>
				<th>Description</th>
			<?php else : ?>
				<th>Appareil : <?php echo implode(' & ',$typeAppareils);?></th>	
			<?php endif;?>
			<th>Montant (<?php echo $facture['Facture']['monnaie']; ?>)</th>
	</tr>
		<?php
	$i = 0;
	foreach ($modelInfos as $service):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr>
			<?php if(!Configure::read('aser.mahanaim')):?>
				<td><?php echo  $service['TypeService']['name']; ?></td>
			<?php endif;?>
			<td><?php echo  $service['Service']['description']; ?></td>
			<td><?php echo  $number->format($service['Service']['montant']); ?></td>
	</tr>
<?php endforeach; ?>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<?php if(!Configure::read('aser.mahanaim')):?>
			<td>&nbsp;</td>
		<?php endif;?>
		<td><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<?php if(!Configure::read('aser.mahanaim')):?>
			<td>&nbsp;</td>
		<?php endif;?>
		<td><span ><?php echo ''.$number->format($facture['Facture']['tva']+0); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<?php if(!Configure::read('aser.mahanaim')):?>
			<td>&nbsp;</td>
		<?php endif;?>
		<td><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>RESTE A PAYER</td>
		<?php if(!Configure::read('aser.mahanaim')):?>
			<td>&nbsp;</td>
		<?php endif;?>
		<td><span id="reste_a_payer"><?php echo ''.$number->format($facture['Facture']['reste']+0); ?></span></td>
	<tr>
</table>
<?php endif;?>
</br>
<?php if($model=='Buanderie') :?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th>Service</th>
			<th>Montant (<?php echo $facture['Facture']['monnaie']; ?>)</th>
	</tr>
	
	<tr>
			<td><?php echo  __('Buanderie'); ?></td>
			<td><?php echo  $number->format($facture['Facture']['montant']); ?></td>
	</tr>
<?php if($facture['Facture']['tva']!=0) :?>
	<tr class="strong">
		<td>HTVA</td>
		<td><span ><?php echo ''.$number->format(($facture['Facture']['montant']-$facture['Facture']['tva'])+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>TVA</td>
		<td><span ><?php echo ''.$number->format($facture['Facture']['tva']+0); ?></span></td>
	<tr>
<?php endif;?>
	<tr class="strong">
		<td>TOTAL <?php if($facture['Facture']['tva']!=0) echo '(TTC)';?></td>
		<td><span id="a_payer" total="<?php echo $facture['Facture']['montant']; ?>"><?php echo ''.$number->format($facture['Facture']['montant']+0); ?></span></td>
	<tr>
	<tr class="strong">
		<td>RESTE A PAYER</td>
		<td><span id="reste_a_payer"><?php echo ''.$number->format($facture['Facture']['reste']+0); ?></span></td>
	<tr>
</table>
<?php endif;?>
</br>
</div>
<!-- extras vue cptable list -->
<div id="extras_vue_cptable" style="display:none;">
<?php echo $this->element('../factures/vue_cptable',array('ventes'=>$venteCptables,'no'=>$facture['Facture']['numero']));?>
</div>

<!-- div contenant les places pour les signatures -->

<?
	$modele=Configure::read('aser.modele_signature');
	$modele=(!empty($modele))?$modele:1;
 echo $this->element('signature',array('modele'=>$modele,'signature'=>$signature));?>
<!-- paiements list -->
<div id="pyts" style="display:none;">
<?php echo $this->element('../paiements/payments_table',array('pyts'=>$pyts,'facture'=>false,'checkbox'=>true));?>
</div>
</div>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		
		<li class="link" onclick = "print_documents()" >Imprimer</li>
		<?php if(Configure::read('aser.xls_copy')):?>
			<li class="link" onclick = "custom_printing('<?php echo $facture['Facture']['id']?>','<?php echo 'factures/view/'.$facture['Facture']['id'].'/'.$type.'/2'?>')" >Imprimer avec Détails</li>
		<?php endif;?>
		<?php if(($model!='Proforma')&&(!in_array($type,array('proforma')))&&($facture['Facture']['etat'])!='annulee'):?>
			<li class="link" onclick = "jQuery('#pyts').slideToggle();jQuery('#pyts_links').slideToggle()" >Afficher/Masquer les Paiements</li>
			<li class="link" onclick = "pyt('<?php echo $this->params['pass'][0];?>','<?php echo $type;?>')" >Créer un Paiement</li>
			<span id="pyts_links" style="display:none;">
				<?php if(in_array($session->read('Auth.Personnel.fonction_id'),array(3,5,8))) :?>
					<li class="link" onclick = "remove_pyt('off')" >Effacer un Paiement</li>
				<?php endif;?>
			</span>
		<?php endif;?>
		<?php if($facture['Facture']['etat']!='annulee'):?>
			<?php 
				$config['annulee']=Configure::read('aser.annulee');
			if((in_array($session->read('Auth.Personnel.fonction_id'),array(3,5,8))&&empty($config['annulee']))
					||in_array($session->read('Auth.Personnel.id'),$config['annulee'])) :?>
				<li class="link" onclick = "edit_facture()" >Modifier la facture</li>
				<li class="link" onclick = "annuler_facture('<?php echo $model.'_'.$this->params['pass'][0];?>')" >Annuler la facture</li>
			<?php endif;?>
		<?php endif;?>
		<?php if($model=='Vente'):?>
			<?php if(Configure::read('aser.touchscreen')):?>
				<li><?php echo $this->Html->link('Interface de Vente', array('controller' => 'ventes', 'action' => 'touchscreen')); ?> </li>
			<?php else:?>
				<li><?php echo $this->Html->link('Interface de Vente', array('controller' => 'ventes', 'action' => 'index')); ?> </li>
			<?php endif;?>
			<?php if(Configure::read('aser.comptabilite')):?>
			<li class="link" onclick = "jQuery('#billView').slideToggle();jQuery('#extras_vue_cptable').slideToggle();" >Afficher/Masquer La version comptable</li>
			<?php endif;?>
			<li><?php echo $this->Html->link(__('Afficher les Commandes envoyées',true), array('controller' => 'ventes', 'action' => 'showOrders/'.$facture['Facture']['id'])); ?> </li>
		<?php endif;?>
		<?php if($model=='Location'):?>
			<li><?php echo $this->Html->link('Gestion des Locations', array('controller' => 'locations', 'action' => 'tabella')); ?> </li>
			<?php if($type=='standard'):?>
				<li><?php echo $this->Html->link('facture Proforma', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'].'/proforma')); ?> </li>
				<?php if(!Configure::read('aser.conference-resto-reception')):?>
					<li><?php echo $this->Html->link('facture Globale', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'].'/globale')); ?> </li>
				<?php endif;?>
			<?php elseif($type=='proforma'):?>
				<li><?php echo $this->Html->link('Facture location', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'])); ?> </li>
				<?php if(!Configure::read('aser.conference-resto-reception')):?>
					<li><?php echo $this->Html->link('facture Globale', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'].'/globale')); ?> </li>
				<?php endif;?>
			<?php else :?>
				<li><?php echo $this->Html->link('Facture location', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'])); ?> </li>
				<li><?php echo $this->Html->link('facture Proforma', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'].'/proforma')); ?> </li>
			<?php endif;?>
		<?php endif;?>
		<li><?php echo $this->Html->link('Gestion des Factures', array('controller' => 'factures', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link('Afficher l\'historique', array('controller' => 'traces', 'action' => 'index',$id,'Facture')); ?> </li>
		<?php if(Configure::read('aser.export_bills')):?>
				<li><?php echo $this->Html->link('Exporter vers excel', array('controller' => 'factures', 'action' => 'view/'.$facture['Facture']['id'].'/'.$type.'/1')); ?> </li>
			<?php endif;?>
		<?php if(in_array(Configure::read('aser.name'),array('aserb','belair'))||Configure::read('aser.chg_num')):?>
			<li class="link" onclick = "num()" >Changer le numéro</li>
		<?php endif;?>
		<?php if(($model=='Service')&&in_array($session->read('Auth.Personnel.fonction_id'),array(3,5,8))):?>
			<li class="link" onclick = "facturation_services('<?php echo $id;?>')" >Modifier le service</li>
		<?php endif;?>
		<li><?php echo $this->Html->link('Retour En Arrière', $referer); ?> </li>
	</ul>
</div>
<!-- form for paiement creation -->
<?php 
	$montant_a_payer = ($type=='globale')?0:$facture['Facture']['reste'];
	echo $this->element('../paiements/edit',array('reste'=>$montant_a_payer,'action'=>'add'));
?>

<!--edit facture box -->
<?php  echo $this->element('../factures/edit',array('facture'=>$facture['Facture']));?>

<!--services stuff-->
<div id="services_boxe" style="display:none" title='Facturation des Services'>
	<?php 
	echo $this->element('../services/facturation',array('typeServices'=>$typeServices,'modification'=>true));
	?>
</div>
