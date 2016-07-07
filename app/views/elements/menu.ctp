<?php
	// search for modules settings 
	$config=Configure::read('aser'); 
	$loggedUser=$session->read('Auth.Personnel.name');
?>
	<? if($enabled):?> 
		<div id="menu">
			<ul id="nav">
				<?php if(Configure::read('aser.POS')):?>
					<li><?php echo $html->link(__('Stock', true), '#'); ?>
						<ul>
							<li class="folder"><?php echo $this->Html->link(__('Configuration des Produits', true), '#'); ?>
								<ul>	
									<?php if(Configure::read('aser.stock')):?>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des stocks', true), '/stocks/index'); ?></li>
									<?endif;?>
									<li class="folder"><?php echo $this->Html->link(__('Gestion des sections', true), '/sections/index'); ?></li>
									<li class="folder"><?php echo $this->Html->link(__('Gestion des groupes', true), '/groupes/index'); ?></li>
									<?php if(Configure::read('aser.comptabilite')):?>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des Groupes Comptables', true), '/groupe_comptables/index'); ?></li>
									<?endif;?>
									<?php if(Configure::read('aser.stock')):?>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des Unités de Mesure', true), '/unites/index'); ?></li>
									<?endif;?>
									<li class="folder"><?php echo $this->Html->link(__('Gestion des produits', true), '/produits/index'); ?>
				    					<?php if(Configure::read('aser.stock')):?>
				    	  					<ul>
												<li class="rapport"><?php echo $this->Html->link(__('Rapport Des Produits', true), '/produits/rapport'); ?></li>
												<li class="rapport"><?php echo $this->Html->link(__('Mouvements Des Produits', true), '/produits/balance'); ?></li>
												<li class="rapport"><?php echo $this->Html->link(__('Evolution journalière', true), '/produits/monthly'); ?></li>
												
												<!-- <li class="rapport"><?php echo $this->Html->link(__('Etat journalier', true), '/produits/shifts'); ?></li>
												<li class="rapport"><?php echo $this->Html->link(__('Conso Théoriques', true), '/produits/conso_theorique'); ?></li> -->
												<?php if($session->read('Auth.Personnel.fonction_id') == 3):?>
												<li class="upload"><?php echo $this->Html->link(__('Importer des produits', true), '/produits/upload_xls'); ?></li>
												<?php endif;?>
											</ul>
										<?endif;?>
									</li>
								</ul>
							</li>
							<?php if(Configure::read('aser.stock')):?>
								<li class="folder"><?php echo $this->Html->link(__('Gestion des Opérations', true), '#'); ?>			
									<ul>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des Entrées', true), '/entrees/index'); ?>
											<ul>
												<li class="rapport"><?php echo $this->Html->link(__('Edition de rapport', true), '/entrees/rapport'); ?></li>
											</ul>
										</li>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des Sorties', true), '/sortis/index'); ?>
											<ul>
												<li class="rapport"><?php echo $this->Html->link(__('Edition de rapport', true), '/sortis/rapport'); ?></li>
											</ul>
										</li>
										<li class="folder"><?php echo $this->Html->link(__('Gestion des Pertes', true), '/pertes/index'); ?>
											<ul>
												<li class="rapport"><?php echo $this->Html->link(__('Rapport Pertes', true), '/pertes/rapport'); ?></li>
											</ul>
										</li>
										<li  class="folder"><?php echo $this->Html->link(__('Gestion des Mouvements', true), '/mouvements/index'); ?></li>
									</ul>	
								</li>
							<?php endif;?>
					</ul>
				</li>
				<?php endif;?>
				<li><?php echo $this->Html->link(__('Clients & Fournisseurs', true), '#'); ?>
					<ul>
						<li class="folder"><?php echo $this->Html->link(__('Clients & Fournisseurs', true), '/tiers/index'); ?></li>
						<?php if(Configure::read('aser.gestion_reduction')):?>
							<li class="folder"><?php echo $this->Html->link(__('Gestion des Réductions', true), '/reductions'); ?></li>
						<?php endif;?>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Factures', true), '/factures/index'); ?>
							<ul>	
								<li class="rapport"><?php echo $this->Html->link(__('Rapport des Factures', true), '/factures/rapport'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Ventes Mensuelle', true), '/factures/monthly'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport Journalier', true), '/factures/cash'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport des Paiements', true), '/paiements/payment/no'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Liste des Débiteurs', true), '/factures/credit'); ?></li>
								<?php if((Configure::read('aser.aserb')&&in_array($session->read('Auth.Personnel.fonction_id'),array(3,5)))||
									in_array(Configure::read('aser.name'),array('aserb','belair'))	
								):?>
									<li class="rapport"><?php echo $this->Html->link(__('Liste des Clients', true), '/factures/declaration'); ?></li>
								<?php endif;?>
								<?php if(Configure::read('aser.silhouette')):?>
									<li class="rapport"><?php echo $this->Html->link(__('Listes Des Factures', true), '/factures/silhouette'); ?>
										<ul>
											<li class="rapport"><?php echo $this->Html->link(__('Limites', true), '/limits'); ?></li>
										</ul>
									</li>
								<?php endif;?>
								<?php if(in_array(Configure::read('aser.name'),array('aserb','belair'))||Configure::read('aser.silhouette')):?>
									<li class="rapport"><?php echo $this->Html->link(__('Impression Journalière', true), '/factures/show_bills'); ?></li>
								<?php endif;?>	
								<?php if($config['POS']):?>
									<li class="rapport"><?php echo $this->Html->link(__('Factures Débloquées', true), '/ventes/unlocked_bills'); ?></li>
								<? endif;?>	
								<?php if(Configure::read('aser.xls_copy')):?>
									<li class="rapport"><?php echo $this->Html->link(__('Factures envoyées', true), '/factures/aserb_report'); ?></li>
								<? endif;?>	
							</ul>
							
						</li>	
						<?php if($config['proforma']):?>
							<li class="folder"><?php echo $this->Html->link(__('Gestion des Proformas', true), '/proformas/index'); ?></li>
						<?php endif;?>
						<?php if($config['ebenezer']):?>
							<li class="folder"><?php echo $this->Html->link(__('Gestion des Dettes', true), '/dettes/index'); ?></li>
						<?php endif;?>
						
					</ul>
				</li>
				
				<?php if($config['POS']): ?>
				<li><?php 
						if($config['magasin']) echo $html->link(__('Point de Vente', true), '#');
						else  echo $html->link(__('Point de Vente', true), '#');?>
						<ul>
							<?php if(Configure::read('aser.touchscreen')):?>
							<li class="rapport"><?php echo $this->Html->link(__('Interface De Vente', true), '/ventes/touchscreen'); ?></li>
							<?php else : ?>
								<li class="rapport"><?php echo $this->Html->link(__('Interface De Vente', true), '/ventes/index'); ?></li>
							<?php endif; ?>
							<?php if($config['pos_sales_report']):?>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport Des Ventes', true), '/ventes/rapport'); ?></li>
							<?php endif;?>
							<li class="rapport"><?php echo $this->Html->link(__('Rapport Des Consommations', true), '/ventes/consommations'); ?></li>
							<li class="rapport"><?php echo $this->Html->link(__('Rapport Caisse', true), '/ventes/journal'); ?></li>
							<?php if(in_array($session->read('Auth.Personnel.fonction_id'),array(3,5))):?>
								<li class="rapport"><?php echo $this->Html->link(__('Paramétrage des Journaux', true), '/journals/index'); ?></li>
							<?php endif; ?>
							<? if(Configure::read('aser.comptabilite')):?>
								<li class="rapport"><?php echo $this->Html->link(__('Synthèse des ventes journalières', true), '/ventes/syntheseCptableDVente'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Détail des crédits journaliers', true), '/ventes/creditCptable'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Ventes Par Groupes Comptable', true), '/ventes/par_produits_groupe_cptable'); ?></li>
							<? endif;?>
							<?php if($config['bon']):?>
								<li class="rapport"><?php echo $this->Html->link(__('Commandes Non envoyées', true), '/ventes/unprinted_orders'); ?></li>
							<? endif;?>	
							<li class="rapport"><?php echo $this->Html->link(__('Commandes Effacées', true), '/ventes/removed_orders'); ?></li>
							</ul>
				</li>
				<?php endif; ?>
				<?php if($config['hotel']): ?>
				<li><?php echo $html->link(__('Hébergement', true), '#'); ?>
					<ul>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Types de Chambre', true), '/typeChambres/index'); ?></li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Chambres', true), '/chambres/index'); ?>
							<?php if(Configure::read('aser.gouvernance')):?>
							<ul>
								<li class="rapport"><?php echo $this->Html->link(__('Listes des chambres à nettoyer', true), '/reservations/rooms_to_clean'); ?></li>
							</ul>
							<?php endif; ?>
						</li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Réservations', true), '/reservations/tabella'); ?>
							<ul>
								<li class="rapport"><?php echo $this->Html->link(__('Etat d\'Occupation', true), '/reservations/etat_occupation'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Liste des prochains arrivages', true), '/reservations/arrivals'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport Mensuel', true), '/reservations/monthly'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport des Paiements', true), '/paiements/payment'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport des Réservations Par Nationalité', true), '/reservations/hosted'); ?></li>
							</ul>
						</li>
					</ul>
				</li>
				
				<?php endif; ?>
				<?php if($config['conference']): ?>
						<li><?php echo $html->link(__('Salles de Conférence', true), '#'); ?>
							<ul>
								<li class="folder"><?php echo $this->Html->link(__('Gestion des Salles', true), '/salles/index'); ?></li>
								<li class="folder"><?php echo $this->Html->link(__('Gestion des Locations', true), '/locations/tabella'); ?>
									<ul>
										<li class="rapport"><?php echo $this->Html->link(__('Edition de rapport', true), '/locations/rapport'); ?></li>
									</ul>
								</li>
							</ul>
						</li>
						<?php endif; ?>
				<?php if($config['services']): ?>
				<li><?php echo $html->link(__('Services', true), '#'); ?>
					<ul>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Type de Services', true), '/type_services/index'); ?></li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Services', true), '/services/index'); ?>
							<ul>
								<li class="rapport"><?php echo $this->Html->link(__('Edition de rapport', true), '/services/rapport'); ?></li>
							</ul>
						</li>
						
					</ul>
				</li>
				<?php endif; ?>
				
				<?php if(Configure::read('aser.tresorerie')):?>
				<li><?php echo $this->Html->link(__('Trésorerie', true), '#'); ?>
					<ul>
						<li class="money"><?php echo $this->Html->link(__('Gestion des caisses', true), '/caisses/index'); ?>
							<ul>
								<li class="rapport"><?php echo $this->Html->link(__('Mouvements des caisses', true), '/operations/balance/caisses'); ?></li>
							</ul>
						</li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Opérations', true), '/operations/index'); ?>	
						</li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des types d\'Entrées & Dépenses', true), '/types/index'); ?>	
							<ul>
								<li class="rapport"><?php echo $this->Html->link(__('Rapport Dépenses', true), '/operations/depenses'); ?></li>
							</ul>
						</li>
						<li class="rapport"><?php echo $this->Html->link('RESULTAT', '/operations/resultat'); ?></li>
						<!--
							<li class="folder"><?php echo $this->Html->link(__('Gestion des Caisses interdites', true), '/caisse_interdites/index'); ?></li>
						-->
					</ul>
				</li>
				<?php endif;?>
				<?php if(false&&$config['comptabilite']): ?>
				<li><?php echo $this->Html->link(__('Comptabilité', true), '#'); ?>
					<ul>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Comptes', true), '/comptes/index'); ?></li>
						<li class="folder"><?php echo $this->Html->link(__('Gestion des Opérations', true), '/compte_operations/index'); ?>
				      		<ul>
				      			<li class="rapport"><?php echo $this->Html->link(__('Enregistrement des Reports', true), '/compte_operations/index/report'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Grand Livre', true), '/compte_operations/rapport'); ?></li>
								<li class="rapport"><?php echo $this->Html->link(__('Balance', true), '/compte_operations/balance'); ?></li>	
								<li class="rapport"><?php echo $this->Html->link(__('Charges d\'Exploitation', true), '/compte_operations/charges'); ?></li>	
								<li class="rapport"><?php echo $this->Html->link(__('Solde de Gestion', true), '/compte_operations/compte_gestion'); ?></li>		
							</ul>
						</li>
					</ul>
				</li>
				<?php endif; ?>
				<li><?php echo $this->Html->link(__('Configuration', true), '#'); ?>
					<ul>
						<li class="rapport"><?php echo $this->Html->link(__('Paramétrage du logiciel', true), '/configs/index'); ?></li>
						<li class="rapport"><?php echo $this->Html->link(__('Sauvergarder la base de données', true), '/configs/backup'); ?></li>
						<?php if(in_array($session->read('Auth.Personnel.fonction_id'),array(3))):?>
							<li class="rapport"><?php echo $this->Html->link(__('Importer la base de données', true), '/configs/restore_db'); ?></li>
							<li class="rapport"><?php echo $this->Html->link(__('Réparer la base de données', true), '/configs/repair_all_tables'); ?>

							<li class="rapport"><?php echo $this->Html->link(__('Effacer complétement la base de données', true), '/configs/reset_db/complete'); ?></li>
							<li class="rapport"><?php echo $this->Html->link(__('Effacer juste les opérations dans base de données', true), '/configs/reset_db/partial'); ?></li>
						<?php endif;?>
						</li>
					</ul>	
				</li>
				<li><?php echo $this->Html->link(__('Accès', true), '#'); ?>
					<ul>
						<li class="group"><?php echo $this->Html->link(__('Gestion des Fonctions', true), '/fonctions/index'); ?></li>
						<li class="personnel"><?php echo $this->Html->link(__('Gestion du Personnel', true), '/personnels/index'); ?></li>
						<li class="home_min"><?php echo $this->Html->link(__('Page d\'accueil', true), '/'); ?></li>
					</ul>
				</li>
				<li><a href="/manual/Index.html">Aide</a></li>
				<li><?php echo $this->Html->link('Déconnexion ('.$loggedUser.')', '/personnels/logout'); ?></li>
			</ul>
		</div>
		<?php else: ?>
		<div id="menu">
			<ul id="nav1">
				<?php if($config['POS']||$config['stock']): ?>
					<li> Stock </li>
				<?php endif; ?>
				<li> Clients & Fournisseurs </li>
				<?php if($config['POS']): ?>
					<li>Point De Vente </li>
				<?php endif; ?>
				<?php if($config['hotel']): ?>
					<li>Hébergement </li>
				<?php endif; ?>
				<?php if($config['conference']): ?>
					<li>Salles de Conférence </li>
				<?php endif; ?>
				<?php if($config['services']): ?>
					<li>Services </li>
				<?php endif; ?>
				<?php if($config['tresorerie']): ?>
				<li>Trésorerie </li>
				<?php endif; ?>
				<?php if(false&&$config['comptabilite']): ?>
					<li>Comptabilité </li>
				<?php endif; ?>
				<li>Configuration </li>
				<li>Accès </li>
			</ul>
		</div>
	<?php endif; ?>
