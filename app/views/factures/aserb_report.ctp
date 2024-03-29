
<!--recherche form -->
<div id="recherche_boxe" style="display:none" title="Options de Recherche">
<div class="dialog">
  <div id="message_recherche"></div>
  <?php echo $this->Form->create('Facture',array('id'=>'recherche'));?>
  <span class="left">
    <?php
      echo $this->Form->input('date1',array('label'=>__('Date de Début',true),'type'=>'text'));
      echo $this->Form->input('date2',array('label'=>__('Date de Fin',true),'type'=>'text'));
    ?>
  </span>
  <span class="right">
    <?php
      echo $this->Form->input('Tier.compagnie',array('label'=>'Compagnie du client','type'=>'text'));     
    ?>
  </span>
  </form>
<div style="clear:both"></div>
</div>
</div>
<div id='view'>
<div class="document">
<h3><?php 
  echo 'LISTE DES FACTURES ENVOYES';
    if(isset($date1)&&isset($date1)){
      echo '<h4>(Pour la période du '.$this->MugTime->toFrench($date1).' au '.$this->MugTime->toFrench($date2).' )</h4>';
    }
  ?>
</h3>
<br>
<table cellpadding="0" cellspacing="0">
  <tr>
      <th>Date de Création</th>
       <th>Date d'envoie</th>
      <th>N° Facture</th>
      <th>Montant</th>
      <th>Reste</th>
      <th>Client</th>
      <th>Compagnie</th>
      <th>Etat</th>
  </tr>
    <?php
  $i=0;
  foreach ($factures as $facture):
    $i++;
  ?>
  <tr>
      <td><?php echo  $this->MugTime->toFrench($facture['Facture']['date']); ?></td>
      <td><?php echo  $this->MugTime->toFrench($facture['Facture']['aserb_date']); ?></td>
      <td>
      <?php echo $this->Html->link($facture['Facture']['aserb_num'], array('controller' => 'factures', 'action' => 'view', $facture['Facture']['id'])); ?>
      </td>
      <td><?php echo  $facture['Facture']['montant']; ?></td>
      <td><?php echo  $facture['Facture']['reste']; ?></td>
      <td><?php echo  $facture['Tier']['name']; ?></td>
      <td><?php echo  $facture['Tier']['compagnie']; ?></td>
      <td><?php echo  $facture['Facture']['etat']; ?></td>
  </tr>
<?php 
endforeach; ?>
</table>
</div>
</div>
<div class="actions">
  <h3><?php __('Actions'); ?></h3>
  <ul>
    <li class="link" onclick = "print_documents()" >Imprimer</li>
    <li class="link"  onclick = "recherche()" >Options de Recherche</li>
    <li><?php echo $this->Html->link('Gestion des Réservations', array('controller' => 'factures', 'action' => 'tabella')); ?> </li>
  </ul>
</div>
