<?php
class VentesController extends AppController {

	var $name = 'Ventes';

	/**
	* generer un rapport de tous les ventes effectuer dans une periode par produits
	* et par groupe comptable.
	*/
	function par_produits_groupe_cptable(){
		$conditions=$ventes=$gpeCptableCond=array();
		$date1=$date2=null;
			
		if(!empty($this->data['Vente']['groupe_comptable_id'])&&($this->data['Vente']['groupe_comptable_id'][0]!=0)) {
			$gpeCptableCond['GroupeComptable.id']=$this->data['Vente']['groupe_comptable_id'];
		}
		$groupeComptables = $this->Vente->Produit->GroupeComptable->find('all',array('fields'=>array('GroupeComptable.id','GroupeComptable.name'),
																																									'conditions'=>$gpeCptableCond,
																																									'order'=>array('GroupeComptable.name')
																																										));

			$conditions['Facture.date >=']=$date1=(!empty($this->data['Facture']['date1']))?
																							$this->data['Facture']['date1']:
																							date('Y-m-d',strtotime(' - 7 days'));
		
			$conditions['Facture.date <=']=$date2=(!empty($this->data['Facture']['date2']))?
																						$this->data['Facture']['date2']:
																						date('Y-m-d');
		
		$conditions['Facture.etat !=']='annulee';
		
		$totals['total']=$totals['quantite']=$totals['ben']=0;	
		foreach ($groupeComptables as $i => $groupeComptable) {
			$total=$quantite=$ben=0;
			$conditions['Produit.groupe_comptable_id']=$groupeComptable['GroupeComptable']['id'];

			$ventes=$this->Vente->find('all',array('fields'=>array(
																	'Produit.name',
																	'Produit.PA',
																	'Produit.id',
																	'sum(Vente.quantite) as quantite',
																	'sum(Vente.montant) as montant',
																	'Vente.PU',
																	'Vente.PA',
																	'Facture.reduction'
																	),
														'conditions'=>$conditions,
														'recursive'=>1,
														'group'=>array('Vente.produit_id','Vente.PU'),
														'order'=>array('Produit.name')
														)
											);
			
			if(!empty($ventes)){
				foreach($ventes as $j=>$vente){
					$total+=$vente['Vente']['montant']-($vente['Vente']['montant']*$vente['Facture']['reduction']/100);
					$quantite+=$vente['Vente']['quantite'];
					$ventes[$j]['Vente']['PA']=($vente['Vente']['PA']==0)?$vente['Produit']['PA']:$vente['Vente']['PA'];
					$ventes[$j]['Vente']['BEN']=($vente['Vente']['PU']-$ventes[$j]['Vente']['PA'])*$vente['Vente']['quantite'];
					$ben+=$ventes[$j]['Vente']['BEN'];
				}
				$groupeComptables[$i]['ventes']=$ventes;
				$groupeComptables[$i]['total']=$total;
				$groupeComptables[$i]['quantite']=$quantite;
				$groupeComptables[$i]['ben']=$ben;

				$totals['total']+=$groupeComptables[$i]['total'];
				$totals['quantite']+=$groupeComptables[$i]['quantite'];
				$totals['ben']+=$groupeComptables[$i]['ben'];
			}
			else {
				unset($groupeComptables[$i]);
			}
		}
		//exit(debug($groupeComptables));
		$this->set(compact('groupeComptables',
											'date1',
											'date2',
											'totals'
											)
						);
	
	}
	
	/**
	 * to find the stock to use
	 * 
	 */
	 
	 function _find_stock(){
	 	if(!Configure::read('aser.multi_resto')){
			$this->loadModel('Stock');
			$stock=Configure::read('aser.default_stock');
			if(empty($stock)||($stock<1)){
				$stockInfo=$this->Stock->find('first',array('fields'=>array('Stock.id')));
				$stock=$stockInfo['Stock']['id'];
			}
			$this->Session->write('resto_stock',$stock);
		}
		else {
			$stock=$this->Session->read('resto_stock'); 
		}
		return $stock;	
	 }
	 /**
	  * this function should be manually called only if for any weird reason
	  * the stock_ingredient() didn't work well and historiques equivalent records have 
	  * not been created
	  */
	  function adjust_sorti_with_historique($test=0){
	  		 //*WHERE `journal_id` = `'.$journalId.'`
	    $this->loadModel('Sorti');
		$sortis=$this->Sorti->find('all',array('fields'=>array('Sorti.* ','Produit.*'),
														'conditions'=>array('OR'=>array(
																				'Sorti.historique_id NOT IN (SELECT `id` FROM `historiques`)',
																				'Sorti.historique_id'=>null
																				),
																			'Produit.id !='=>''
																			),
														));	
													
		if($test) {
			exit(debug($sortis));
		}
		foreach($sortis as $i=>$sorti){
			//si l'ingredient n'a pas ete typee stockable we need to change it before 
			if($sorti['Produit']['type']=='non_stockable'){
				$sorti['Produit']['type']='stockable';
				$this->Sorti->Produit->save($sorti);
			}
			$sorti['Sorti']['historique_id']=null; //otherwise will the delete the previous record
			$this->Product->stock($sorti,'credit',$sorti,true); //reducing the stock without raising an error on lack of qty
			$this->Sorti->save($sorti);
		}
		exit('Done');
	  }
	/**
	 * handling the stock recording of ingredients
	 */
	 function stock_ingredient($journalId,$date,$shift){
	 	$this->autoRender=false;
		$ventes=$this->Produit->Vente->find('all',array('fields'=>array('sum(Vente.quantite) as quantite','Vente.produit_id','Vente.facture_id'),
														'conditions'=>array('Vente.facture_id IN (SELECT `id` FROM `factures` WHERE `journal_id` = `'.$journalId.'`)',
																			),
														
														'group'=>array('Vente.produit_id'),
														//*
														'contain'=>array('Produit'=>array(
																			'fields'=>array('Produit.name'),
																			'EstComposerPar'=>array('fields'=>array('EstComposerPar.*',
																																),
																												)
																							)
																		)	
														//*/
														));	
		//exit(debug($ventes));
		//common sorti data
		$s['Sorti']['date']=$date;
		$s['Sorti']['stock_id']=$this->_find_stock();
		$s['Sorti']['shift']=$shift;
		
		foreach($ventes as $vente){
			foreach($vente['Produit']['EstComposerPar'] as $ing){
				$s['Sorti']['produit_id']=$ing['ingredient_id'];
				$s['Sorti']['quantite']=$ing['qte']*$vente['Vente']['quantite'];
				$s['Sorti']['historique_id']=null; //otherwise will the delete the previous record
				$s['Sorti']['id']=null; // otherwise will update the previous record
				$this->Product->stock($s,'credit',array(),true); //reducing the stock without raising an error on lack of qty
				//saving the sorti of the ingredient
			
				$this->Vente->Produit->Sorti->save($s);
			}
		}
	}
	 
	function getTables($pos){
		$list=Configure::read('bars.'.$pos);
		$limit=$list[1];
		//first loop for the tables numbers between 0 et 1 index, 
		for($i=$list[0] ; $i<=$limit; $i++){
			$tables[$i]=$this->table_state($i,false,true);
		}
		//second loop for additional numbers above 1 index
		foreach($list as $i=>$tableNum){
			if($i>1){
				$tables[$tableNum]=$this->table_state($tableNum,false,true);
			}
		}
		return $tables;
	}
	
	function beforeFilter(){
		if(in_array($this->params['action'],array('print_facture','journal'))){
			$caissiers=$this->Vente->Facture->Personnel->find('list',
															array('conditions'=>
																	array('Personnel.fonction_id'=>array(2,4),
																				'Personnel.actif'=>'oui'
																	)
																));
			$this->set('caissiers',$caissiers);
		}
		parent::beforeFilter();
	} 
	
	function checkCloturation($journal){
		$factures=$this->Vente->Facture->find('all',
											array('conditions'=>array('Journal.personnel_id'=>$journal['personnel_id'],
																	'Facture.etat'=>array('cloturer','en_cours'),
																	'Facture.date <'=>$journal['date']
																	),
												'fields'=>array('Facture.id','Facture.date')
											));
									
		if(!empty($factures)){
			$list='';
			foreach($fatures as $facture){
				$list.=$facture['Facture']['date'].', ';
			}
			exit(json_encode(array('success'=>false,
								'msg'=>'Vérifier ces dates pour des factures non classées : '.$list)));
		}
	}
	
	function _part(&$gpeCptables,$model,$gpeCptable,$key,$cond){
		if($model=='Vente'){
				$cond['Produit.groupe_comptable_id']=$gpeCptable['GroupeComptable']['id'];
			}
			else { 
				$cond['TypeService.groupe_comptable_id']=$gpeCptable['GroupeComptable']['id'];	
			}
			$cond['Facture.operation']=$model;
			$cond['Facture.montant >']=0;
			$cond[$model.'.montant >']=0;
			$controllers=$this->$model->find('all',array('fields'=>array('sum('.$model.'.montant) as montant',
																	'Facture.reduction',
																	'Facture.monnaie',
																	'Facture.etat',
																	'Facture.montant',
																	'Facture.date',
																	'Facture.operation'
															),
												'conditions'=>$cond,
												'group'=>array($model.'.facture_id','Facture.monnaie')
												));
			foreach($controllers as $controller){
				if(isset($gpeCptables[$key][$controller['Facture']['etat']][$controller['Facture']['monnaie']])){
					$gpeCptables[$key][$controller['Facture']['etat']][$controller['Facture']['monnaie']]+=($controller['Facture']['reduction']>0)?
												($controller[$model]['montant']-round($controller[$model]['montant']*$controller['Facture']['reduction']/100)):
												$controller[$model]['montant'];
				}
			}	
	}
	
	function syntheseCptableDVente(){
		//*
		if(!empty($this->data['Facture']['date1']))
			$cond['Facture.date >=']=$this->data['Facture']['date1'];
		else 
			$cond['Facture.date >=']=date('Y-m-d');
		$date1=$cond['Facture.date >='];
		if(!empty($this->data['Facture']['date2']))
			$cond['Facture.date <=']=$this->data['Facture']['date2'];
		else 
			$cond['Facture.date <=']=date('Y-m-d');
		$date2=$cond['Facture.date <='];
		
		$cond['Facture.etat']=array('payee','credit');
		$this->loadModel('Service');
		//*/
		//fetching the gpe comptable in associative array
		$gpeCptables=$this->Vente->Produit->GroupeComptable->find('all',array('fields'=>array('GroupeComptable.name',
																							'GroupeComptable.id'																							
																							)));	
		//creating the total arrays 
		foreach($this->facturationMonnaies as $monnaie){
			$total['credit'][$monnaie]=$total['payee'][$monnaie]=0;
		}	
		/*
		foreach($this->monnaies as $monnaie){
			foreach($this->modePaiements as $mode=>$modePaiement)
				$total['detail'][$monnaie.'_'.$mode]=0;
		}
		//*/
		//array of treated payments
		$pytsList=array();
		foreach($gpeCptables as $key=>$gpeCptable){
			//create the placeholder all currencies
			foreach($this->facturationMonnaies as $monnaie){
					$gpeCptables[$key]['credit'][$monnaie]=$gpeCptables[$key]['payee'][$monnaie]=0;
			}	
			
			//fecthing  ventes
			$this->_part($gpeCptables, 'Vente',$gpeCptable,$key,$cond);
			//fecthing  service
			$this->_part($gpeCptables, 'Service',$gpeCptable,$key,$cond);
			
			//total all credits grouped by currencies
			foreach($this->facturationMonnaies as $monnaie){
				$total['credit'][$monnaie]+=$gpeCptables[$key]['credit'][$monnaie];
				$total['payee'][$monnaie]+=$gpeCptables[$key]['payee'][$monnaie];
			}	
			
			/*
			//preparing the array for detailed payments
			$monnaies=$this->monnaies;
			$modePaiements=$this->modePaiements;
				
			foreach($this->monnaies as $monnaie){
				foreach($this->modePaiements as $mode=>$modePaiement)
					$gpeCptables[$key]['detail'][$monnaie.'_'.$mode]=0;
			}
			//factures ventes
			$ventes=$this->Vente->find('all',array('fields'=>array('Vente.montant','Vente.facture_id','Facture.reduction'),
													'conditions'=>array('Facture.etat'=>array('payee','avance'),
																	'Facture.date'=>$date,
																	'Produit.groupe_comptable_id'=>$gpeCptable['GroupeComptable']['id']
																	)
													));
			foreach($ventes as $vente){
				//paiements
				$pyts=$this->Vente->Facture->Paiement->find('all',array('fields'=>array('Paiement.montant',
																		'Paiement.monnaie',
																		'Paiement.mode_paiement',
																		'Paiement.montant_equivalent',
																		'Facture.monnaie',
																		'Paiement.id'
																		),
													'conditions'=>array('Paiement.facture_id'=>$vente['Vente']['facture_id'])
																	));	
				
				foreach($pyts as $pyt){
					//determination of the portion of the payment that corresponds to the particular item.
					$vente['Vente']['montant']=($vente['Facture']['reduction']>0)?round($vente['Vente']['montant']*$vente['Facture']['reduction']/100):$vente['Vente']['montant'];
					//determination du paiement restant a utiliser
					$pytMontant=(in_array($pyt['Paiement']['id'],array_keys($pytsList)))?$pytsList[$pyt['Paiement']['id']]:$pyt['Paiement']['montant'];
					
					$itemAmount=($vente['Vente']['montant']<=$pytMontant)?$vente['Vente']['montant']:$pytMontant;
					$pytsList[$pyt['Paiement']['id']]=$pytMontant-$itemAmount;
					
					if(!empty($pyt['Paiement']['montant_equivalent'])){
						$itemAmount=round($itemAmount*($pyt['Paiement']['montant_equivalent']/$pyt['Paiement']['montant']));
						$gpeCptables[$key]['detail'][$pyt['Paiement']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
						$total['detail'][$pyt['Paiement']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
					}
					else {
						$gpeCptables[$key]['detail'][$pyt['Facture']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
						$total['detail'][$pyt['Facture']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
					}
				}
			}
			
			//factures services
			$services=$this->Service->find('all',array('fields'=>array('Service.montant','Service.facture_id','Facture.reduction'),
													'conditions'=>array('Facture.etat'=>array('payee','avance'),
																	'Facture.date'=>$date,
																	'TypeService.groupe_comptable_id'=>$gpeCptable['GroupeComptable']['id']
																	)
													));
			foreach($services as $service){
				//paiements
				$pyts=$this->Service->Facture->Paiement->find('all',array('fields'=>array('Paiement.montant',
																		'Paiement.monnaie',
																		'Paiement.mode_paiement',
																		'Paiement.montant_equivalent',
																		'Facture.monnaie',
																		'Paiement.id'
																		),
													'conditions'=>array('Paiement.facture_id'=>$service['Service']['facture_id'])
																	));	
				
				foreach($pyts as $pyt){
					//determination of the portion of the payment that corresponds to the particular item.
					$service['Service']['montant']=($service['Facture']['reduction']>0)?round($service['Service']['montant']*$service['Facture']['reduction']/100):$service['Service']['montant'];
					//determination du paiement restant a utiliser
					$pytMontant=(in_array($pyt['Paiement']['id'],array_keys($pytsList)))?$pytsList[$pyt['Paiement']['id']]:$pyt['Paiement']['montant'];
					
					$itemAmount=($service['Service']['montant']<=$pytMontant)?$service['Service']['montant']:$pytMontant;
					$pytsList[$pyt['Paiement']['id']]=$pytMontant-$itemAmount;
					
					if(!empty($pyt['Paiement']['montant_equivalent'])){
						$itemAmount=round($itemAmount*($pyt['Paiement']['montant_equivalent']/$pyt['Paiement']['montant']));
						$gpeCptables[$key]['detail'][$pyt['Paiement']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
						$total['detail'][$pyt['Paiement']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
					}
					else {
						$gpeCptables[$key]['detail'][$pyt['Facture']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
						$total['detail'][$pyt['Facture']['monnaie'].'_'.$pyt['Paiement']['mode_paiement']]+=$itemAmount;
					}
				}
			}
			//*/
		}	
	//	exit(debug($total));
		$this->set(compact('monnaies','modePaiements','gpeCptables','date1','date2','total'));
	}
	
	function creditCptable(){
		$cond['Facture.etat']=array('credit');
		if(!empty($this->data['Facture']['date']))
			$cond['Facture.date']=$date=$this->data['Facture']['date'];
		else 
			$cond['Facture.date']=$date=date('Y-m-d');
		
		//to hold all the data grouped by monnaiee
		$monnaies=array();
		foreach($this->monnaies as $key=>$monnaie){
			//creating the data array
			$tiers=$this->tiers;
			$groupeComptables=$this->groupeComptables;
			foreach($tiers as $idClient=>$nameClient){
				$data[$idClient.'_total']=0;
				foreach($this->groupeComptables as $idGroupeComptable=>$nameGroupeComptable){
					$data[$idClient.'_'.$idGroupeComptable]=0;
				}
			}
			$cond['Facture.monnaie']=$monnaie;
			//fetching sales data
			$ventes=$this->Vente->find('all',array('fields'=>array('sum(Vente.montant) as montant',
																'Produit.groupe_comptable_id',
																'Facture.tier_id',
																'Facture.reduction'
																),
													'conditions'=>$cond,
													'group'=>array('Facture.tier_id','Produit.groupe_comptable_id')
													));
			//putting the sales in the data array
			foreach($ventes as $vente){
				$data[$vente['Facture']['tier_id'].'_'.$vente['Produit']['groupe_comptable_id']]+=($vente['Facture']['reduction']>0)?
																								round($vente['Vente']['montant']*$vente['Facture']['reduction']/100):
																								$vente['Vente']['montant'];
				$clientsToShow[]=$vente['Facture']['tier_id'];
				$gpeCptableToShow[]=$vente['Produit']['groupe_comptable_id'];
			}
			
			//fetching services data
			$this->loadModel('Service');
			$services=$this->Service->find('all',array('fields'=>array('sum(Service.montant) as montant',
																'TypeService.groupe_comptable_id',
																'Facture.tier_id',
																'Facture.reduction'
																),
													'conditions'=>$cond,
													'group'=>array('Facture.tier_id','TypeService.groupe_comptable_id',)
													));
			//putting the sales in the data array
			//*
			foreach($services as $service){
				$data[$service['Facture']['tier_id'].'_'.$service['TypeService']['groupe_comptable_id']]+=($service['Facture']['reduction']>0)?
																										round($service['Service']['montant']*$service['Facture']['reduction']/100):
																									$service['Service']['montant'];
				$clientsToShow[]=$service['Facture']['tier_id'];
				$gpeCptableToShow[]=$service['TypeService']['groupe_comptable_id'];
			}
			//*/
			//calculting totals
			$total=0;
			if(!empty($ventes)||!empty($services))
				foreach($tiers as $tierId=>$tier)
					if(in_array($tierId,$clientsToShow))
						foreach($groupeComptables as $gpeCptableId=>$groupeComptable)
							if(in_array($gpeCptableId,$gpeCptableToShow)){
								$data[$tierId.'_total']+=$data[$tierId.'_'.$gpeCptableId];
								$total+=$data[$tierId.'_'.$gpeCptableId];
							}
			$tva=$this->Product->tva($total);
			
			$monnaies[$key]['data']=$data;
			$monnaies[$key]['total']=$total;
			$monnaies[$key]['tva']=$tva;
			$monnaies[$key]['clientsToShow']=(isset($clientsToShow))?$clientsToShow:null;
			$monnaies[$key]['gpeCptableToShow']=(isset($gpeCptableToShow))?$gpeCptableToShow:null;
		}
		
		$this->set(compact('monnaies','date','data','tiers','groupeComptables','clientsToShow','gpeCptableToShow','total','tva'));
	}
		
	function update_tables(){
		$tables=Configure::read('aser.tables');
		$tableStates=array();
		for($i=1;$i<=$tables;$i++){
			$tableStates[$i]=$this->table_state($i,false,true);
		}
		exit(json_encode($tableStates));
	}
	
	function table_state($table,$exit=true,$return=false){
		//*
		$search=$this->Vente->Facture->find('first',array('fields'=>array('Facture.table','Personnel.name'),
														//*
															'conditions'=>array('Facture.table'=>$table,
																				'Facture.etat'=>array('cloturer','en_cours','confirmee'),
																				'Facture.operation'=>'Vente',
																				'Facture.date'=>date('Y-m-d')
																				),
														//	'cache' => 'table_state'.$table, 'cacheConfig' => 'short'
														 //*/
													)
											);
		if(!empty($search)){
			if(!$return&&$this->RequestHandler->isAjax()){
				exit(json_encode(array('success'=>false,
									'msg'=>'Cette table : '.$table.' est déjà ouverte!',
									'state'=>'table_red',
									'serveur'=>$search['Personnel']['name']
									)));
			}
			else {
		//	exit(debug($search));
				return array('class'=>'table_red','serveur'=>$search['Personnel']['name']);
			}
		}
		else {
			if(!$return&&$this->RequestHandler->isAjax()){
				if($exit)
					exit(json_encode(array('success'=>true,'msg'=>'Cette table : '.$table.' est free!','state'=>'table_green','serveur'=>'')));
			}
			else {
				return array('class'=>'table_green','serveur'=>'');
			}
		}
		//*/
	}
	

	function unlock($factureId,$old_state){
		//delete the payments
		$this->Vente->Facture->Paiement->deleteAll(array('Paiement.facture_id'=>$factureId));
		$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.montant','Facture.id','Facture.numero'),
		                                                   'conditions'=>array('Facture.id'=>$factureId)
		                                                    ));
		
		$this->Vente->Facture->save(array('Facture'=>array('id'=>$factureId,
															'etat'=>'en_cours',
															'reste'=>$facture['Facture']['montant'],
															'printed'=>0,
															'debloquer'=>$this->Auth->user('id'),
															'classee'=>0,
															'observation'=>''
															)));
		//trace stuff
		$trace['Trace']['model_id']=$factureId;
		$trace['Trace']['model']='Facture';
		$trace['Trace']['operation']='Déblocage de la facture '.$facture['Facture']['numero'].'. De l\'etat "'.$old_state.'" à "en_cours".';
		$trace['Trace']['operation'].=' Ancien montant : '.$facture['Facture']['montant'];
		//look for the items contained in the bill .
		$ventes = $this->Vente->find('all',array('fields'=>array('Produit.name'),
												'conditions'=>array('Vente.facture_id'=>$factureId)
												));
		$trace['Trace']['operation'].=" Liste des produits : ";
		foreach($ventes as $vente){
			$trace['Trace']['operation'].= $vente['Produit']['name'].', ';
		}	
		$this->Vente->Facture->Trace->save($trace);
		
		exit(json_encode(array('success'=>true,'msg'=>'ok')));
	}

	

	function unprinted_orders(){
		$date1= (!empty($this->data['Vente']['date1']))?$this->data['Vente']['date1']:date('Y-m').'-01';
		$date2= (!empty($this->data['Vente']['date2']))?$this->data['Vente']['date2']:date('Y-m').'-31';
		$ventes = $this->Vente->find('all',array('fields'=>array('Facture.date','Vente.quantite',
																													'Vente.printed','Facture.id','Facture.numero',
																													'Produit.name'
																													),
																						'conditions'=>array('date(Vente.created) >=' => $date1,
																																'date(Vente.created) <=' => $date2,
																																'Vente.quantite > Vente.printed'
																															),
																						'order'=>array('Facture.date')
																						));
		$this->set(compact('ventes','date2','date1'));
	}

	function unlocked_bills(){
		$date1= (!empty($this->data['Vente']['date1']))?$this->data['Vente']['date1']:date('Y-m').'-01';
		$date2= (!empty($this->data['Vente']['date2']))?$this->data['Vente']['date2']:date('Y-m').'-31';

		$factures = $this->Vente->Facture->find('all',array('fields'=>array('Facture.date','Facture.id','Facture.montant',
																															'Facture.reste','Facture.numero','Facture.etat',
																															'Personnel.name','Facture.monnaie','Facture.debloquer'
																															),
																								'conditions'=>array('Facture.operation' => 'Vente',
																																	'Facture.debloquer >=' => 1,
																																	'Facture.date >=' => $date1,
																																	'Facture.date <=' => $date2 
																																	),
																								'order'=>array("Facture.date")
																								));
		$personnels = $this->Vente->Personnel->find('list');
		$this->set(compact('factures','date1','date2','personnels'));
	}

	function direct_reduction($new,$old,$id){
		$facture['reduction']=round(100*($old-$new)/$old,3);
		$facture['original']=$old;
		$this->Vente->Facture->reduction($facture);
		$facture['direct_reduction']=1;
		$facture['id']=$id;
		if($this->Vente->Facture->save(array('Facture'=>$facture)))
			exit(json_encode(array('success'=>true,'reduction'=>$facture['reduction'])));
		else 
			exit(json_encode(array('success'=>false,'msg'=>'Failed to save the reduction.')));

	}
	
	function transfer(){
	//	exit(debug($this->data));	
		$this->data['Vente']['date']=date('Y-m-d');
		$journal=$this->Product->journal($this->data['Vente']['personnel_id'],$this->data['Vente']['date']);
		$facturesId=array();
		foreach($this->data['Id'] as $value){
			if($value!=0) {
				$facturesId[]=$value;
				$facture['Facture']['journal_id']=$journal['id'];
				$facture['Facture']['date']=$journal['date'];
				$facture['Facture']['id']=$value;
				$this->Vente->Facture->save($facture);
				
				//historique du stock
				$historiques=$this->Vente->find('list',array('fields'=>array('Vente.historique_id','Vente.historique_id'),
												'conditions'=>array('Vente.facture_id'=>$value),
												));
				unset($update);
				foreach($historiques as $historiqueId){
					$update['Historique']['id']=$historiqueId;
					$update['Historique']['date']=$journal['date'];
					$this->Vente->Historique->save($update);
				}
				//paiement des factures
				$paiements=$this->Vente->Facture->Paiement->find('list',array('fields'=>array('Paiement.id','Paiement.id'),
												'conditions'=>array('Paiement.facture_id'=>$value,
																	),
												));
				unset($update);
				foreach($paiements as $paiementId){
					$update['Paiement']['id']=$paiementId;
					$update['Paiement']['journal_id']=$journal['id'];
					$update['Paiement']['date']=$journal['date'];
					$this->Vente->Facture->Paiement->save($update);
				}
												
			}
		}
	//	if($this->data['Vente']['paiements']=='oui'){
		if(false){ //temporary disabled
		//	exit(debug($this->data));
			//paiement du stock
			$paiements=$this->Vente->Facture->Paiement->find('list',array('fields'=>array('Paiement.id','Paiement.id'),
																	'conditions'=>array('NOT'=>array('Paiement.facture_id'=>$facturesId),
																						'Paiement.journal_id'=>$this->data['Vente']['journal_id']
																						),
																		));
			unset($update);
			foreach($paiements as $paiementId){
				$update['Paiement']['id']=$paiementId;
				$update['Paiement']['journal_id']=$journal['id'];
				$update['Paiement']['date']=$journal['date'];
				$this->Vente->Facture->Paiement->save($update);
			}
		}
		exit(json_encode(array('success'=>true)));
	}
	
	function ungroup($factureId){
		$facture = $this->Vente->Facture->find('first',array('fields'=>array('Facture.printed'),
																								'conditions'=>array('Facture.id'=>$factureId),
																								'recursive'=>-1
																			));
		if($facture['Facture']['printed']==0){
			$ventes=$this->Vente->find('all',array(
												'conditions'=>array('Vente.facture_id'=>$factureId),
												'fields'=>array('Vente.*','Facture.date')
												));	
			$this->loadModel('Historique');
			foreach($ventes as $vente){
				if($vente['Vente']['quantite']>1){
					//deleting the history and the old vente
					if(!empty($vente['Vente']['historique_id'])){
						if(!$this->Product->productHistoryDelete($vente['Vente']['historique_id'],'Historique'))
							exit(json_encode(array('success'=>false,'msg'=>"Problème avec l'effacement du stock.")));
					}
					if(!$this->Vente->delete($vente['Vente']['id']))
						exit(json_encode(array('success'=>false,'msg'=>"Problème avec l'effacement de l'un des produits")));
					
					$qty=$vente['Vente']['quantite']; 
					for($i=0; $i<$qty;$i++){
						//setting up the new values
						$vente['Vente']['id']=$vente['Vente']['historique_id']=null;
						$vente['Vente']['quantite']=1;
						$vente['Vente']['printed']=($vente['Vente']['printed']>0)?1:0;
						$vente['Vente']['montant']=$vente['Vente']['quantite']*$vente['Vente']['PU'];
						//stock recording
						if(!empty($vente['Vente']['historique_id'])){
							$return=$this->Product->stock($vente,'credit');
							if(!$return['success'])
								exit(json_encode(array('success'=>false,'msg'=>$return["msg"])));
						}   
						else //this means that the stock is was not activated at the creation time. so remove this field.
							unset($vente['Vente']['historique_id']);
						//saving
						if(!$this->Vente->save($vente))
							exit(json_encode(array('success'=>false,'msg'=>"un produit n'a pas été enregistré")));
					}
				}
			}
			exit(json_encode(array('success'=>true)));
		}	
		else {
			exit(json_encode(array('success'=>false,'msg'=>'Impossible de séparer la facture car elle déjà imprimée')));
		}
	}
	
	function separator($factureId,$list){
		//searching the bill details
		$facture=$this->Vente->Facture->find('first',array('conditions'=>array('Facture.id'=>$factureId),
																		'fields'=>array('Facture.*',
																				),
																		'recursive'=> -1
																));
		//update the old bill by putting the linked id
		 $facture['Facture']['linked']=$factureId;
		 if(!$this->Vente->Facture->save($facture))
		 	exit(json_encode(array('success'=>false,'msg'=>"L'ancienne facture n'a pas été enregistrée.")));
		unset($this->Vente->Facture->id);
		
		//creating the new bill
		$newFacture=$facture;
		$newFacture['Facture']['id']=null;
		$newFacture['Facture']['linked']=$factureId;
		$newFacture['Facture']['original']=$newFacture['Facture']['montant']=0;
		$newFacture['Facture']['reste']=$newFacture['Facture']['tva']=$newFacture['Facture']['pyts']=0;
		if(!$this->Vente->Facture->save($newFacture))
			exit(json_encode(array('success'=>false,'msg'=>"La nouvelle facture n'a pas été enregistrée.")));
		
		$newFactureId=$this->Vente->Facture->id;
		unset($this->Vente->Facture->id);

		//setting up the display number
		$newfacture['Facture']['numero']=$this->Product->facture_number($newFactureId,'Vente',$newFacture['Facture']['date']);
			
		//moving consos to the new bill
		$list=explode(',',$list);
		$fields= array('Vente.*');
		$ventes=$this->Vente->find('all',array('recursive'=>-1,
											'conditions'=>array('Vente.id'=>$list),
											'fields'=> $fields
											));
		$failureMsg="Un des produits n'a pas été enregistré.";
		foreach($ventes as $vente){
			$vente['Vente']['facture_id']=$newFactureId;
			if($vente['Vente']['acc']){
				$acc = $this->Vente->find('first',array('recursive'=>-1,
											'conditions'=>array('Vente.id'=>$vente['Vente']['acc']),
											'fields'=>$fields
											));
				$acc['Vente']['facture_id']=$newFactureId;
				if(!$this->Vente->save($acc))
					exit(json_encode(array('success'=>false,'msg'=>"Un des accompagnement n'a pas été enregistré.")));
			}
			if(!$this->Vente->save($vente))
				exit(json_encode(array('success'=>false,'msg'=>$failureMsg)));
		}
		//updating the old bill for the new bill
		$this->Vente->Facture->updateMontant($facture['Facture']);

		//fetching the info of the old bill
		$oldFacture=$this->Vente->Facture->find('first',array('conditions'=>array('Facture.id'=>$factureId),
																		'fields'=>array('Facture.original',
																						'Facture.montant',
																						'Facture.reste'
																				),
																		'recursive'=> -1
																));
		
		//$old_totals=$this->Product->bill_total($factureId, $newfacture['Facture']['reduction']);
		//fetching the info of the new bill
		$newFacture=$this->Vente->Facture->find('first',array('conditions'=>array('Facture.id'=>$newFactureId),
																		'fields'=>array('Facture.*',
																				),
																		'recursive'=> -1
																));
		
		exit(json_encode(array('success'=>true,'Facture'=>$newFacture['Facture'],'old'=>$oldFacture['Facture'])));
	}
	
	function detailed_products_names(){
		$cond['Produit.actif']='oui';
   		$produits = $this->Vente->Produit->find('all',array( 'fields'=>array('Produit.id',
   																			'Produit.name',
   																			'Produit.PV',
   																			'Produit.type'
																			),
															'conditions'=>$cond,
															'order'=>array('Produit.name asc')
															)
													);
		$produits=$this->_product_list($produits);
		exit(json_encode($produits));
	}
	
	function _product_list($produits){
		$list=array();
		$pos=(Configure::read('aser.multi_pv'))?$this->Session->read('pos'):'';
		$stockId=$this->Session->read('resto_stock');
		$connexion=Configure::read('aser.connexion');
		foreach($produits as $produit){
			$produit['Produit']['PV']=$this->Product->productPrice($produit['Produit']['id'],$produit['Produit']['PV'],$pos);
			$produit['Produit']['quantite']=(Configure::read('aser.default_stock')>0)? //if using one stock the qty is already set
											$produit['Produit']['PV']:
											$this->Product->productQty($produit['Produit']['id'],$stockId);
			$list[$produit['Produit']['id']]=(($produit['Produit']['type']=='stockable')&&$connexion)?
											ucwords($produit['Produit']['name']).'_'.$produit['Produit']['quantite'].'_'.$produit['Produit']['PV']:
											ucwords($produit['Produit']['name']).'_'.$produit['Produit']['PV'];
		}
		return $list;
	}

	function serveur($factureId,$personnel_id){
		$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.id'),
													'conditions'=>array('Facture.id'=>$factureId)
													)
												);
			
		$facture['Facture']['personnel_id']=$personnel_id;
		$this->Vente->Facture->save($facture);
		exit(json_encode(array('success'=>true,'msg'=>'ok')));
	}
	
	function table($factureId,$table){
		$found=false;
		if(Configure::read('aser.touchscreen')){
			if(Configure::read('aser.multi_resto')){
				foreach(Configure::read('bars') as $pos=>$barTables){
					if(in_array($table, array_keys($this->getTables($pos)))){
						$found=true;
						break;
					}
				}
			}
			else {
				$tablesMax=Configure::read('aser.tables');
				$found=(($table>=1)&&($table<=$tablesMax))?true:false;
			}
		}
		else {
			$found=true;
		}
		if(!$found){
			exit(json_encode(array('success'=>false,'msg'=>'Cette table n\'existe pas!')));
		}
		else {
			$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.id'),
													'conditions'=>array('Facture.id'=>$factureId)
													)
												);
			
			$facture['Facture']['table']=$table;
			$this->Vente->Facture->save($facture);
			exit(json_encode(array('success'=>true,'msg'=>'ok')));

		}
	}
	
	
	function journal($id=null,$data=array(),$mensuelle=false){
		if($id!=null){
			$data=$this->Vente->Facture->Journal->find('first',array('fields'=>array('Journal.numero',
																			'Journal.personnel_id',
																			'Journal.date'
																			),
															'conditions'=>array('Journal.id'=>$id),
															'recursive'=>-1
															));
		}
		$this->autoRender=false;
		$this->data=(empty($data))?($this->data):($data);
		$factureConds=$pytConds=$caisseConds=$journals=array();
		$global='non';
		$date=$date1='';
		if(!empty($this->data)){
			if($mensuelle){
				$date=$factureConds['Journal.date >=']=$pytConds['Journal.date >=']=$caisseConds['Journal.date >=']=$this->data['Journal']['date'];
				$date1=$factureConds['Journal.date <=']=$pytConds['Journal.date <=']=$caisseConds['Journal.date <=']=$this->data['Journal']['date1'];
			}
			else {
				$date=$journalConds['Journal.date']=$factureConds['Journal.date']=$pytConds['Journal.date']=$caisseConds['Journal.date']=$this->data['Journal']['date'];
				$journalConds['Journal.numero']=$factureConds['Journal.numero']=$pytConds['Journal.numero']=$caisseConds['Journal.numero']=$this->data['Journal']['numero'];
				$journalConds['Journal.personnel_id']=$factureConds['Journal.personnel_id']=$pytConds['Journal.personnel_id']=$caisseConds['Journal.personnel_id']=$this->data['Journal']['personnel_id'];
			}
		}
		else {
			$date=$journalConds['Journal.date']=$factureConds['Journal.date']=$pytConds['Journal.date']=$caisseConds['Journal.date']=date('Y-m-d');
		//	$journalConds['Journal.numero']=$factureConds['Journal.numero']=$pytConds['Journal.numero']=$caisseConds['Journal.numero']=1; //commented to allow the fetching of last journal
			$journalConds['Journal.personnel_id']=$factureConds['Journal.personnel_id']=$pytConds['Journal.personnel_id']=$caisseConds['Journal.personnel_id']=$this->Auth->user('id');
		}
		
		
		
		if(!$mensuelle){
			$journalInfo=$this->Vente->Facture->Journal->find('first',array('fields'=>array('Journal.*','Personnel.name','Personnel.id'),
																			'conditions'=>$journalConds,
																			'order'=>array('Journal.id desc') //fetching the last one
																			)
															);
			if(!empty($journalInfo)){
				$factureConds['Journal.id']=$journalInfo['Journal']['id'];
				$pytConds['Journal.id']=$journalInfo['Journal']['id'];
				$caisseConds['Journal.id']=$journalInfo['Journal']['id'];
			}
			$journals=$this->Vente->Facture->Journal->find('all',array('fields'=>array('Journal.*'),
																			'conditions'=>array('Journal.personnel_id'=>$journalConds['Journal.personnel_id'],
																								'Journal.date'=>$journalConds['Journal.date'],
																								)
																			)
															);
		}
		$factures=$this->Vente->Facture->find('all',array('fields'=>array('Facture.*','Tier.name','Personnel.name'),
												'conditions'=>$factureConds,
												)
									);
		//	die(debug($factures));
		$total_factures['resto']=$total_credits['resto']=$total_cash['resto']=0;
		//this variable holds the total of pyts not made by the person to whom belong this journal.
		$other_people_pyts['total']=0; 
		$other_people_pyts['pyts']=array();
		$bonus=0;
		$facturesIds=array();
		
			foreach($factures as $facture){
				
				if(!in_array($facture['Facture']['etat'],array('annulee'))){
						$total_factures['resto']+=$facture['Facture']['montant'];
						$total_credits['resto']+=$facture['Facture']['reste'];
						
						if($facture['Facture']['etat']=='bonus'){
							$bonus+=$facture['Facture']['montant'];
						}
					$facturesIds[]=$facture['Facture']['id'];
				}
			}
		//	if(!empty($journalInfo)&&($journalInfo['Journal']['closed']==1)){
			if(!empty($journalInfo)){
				$pytConds['Paiement.facture_id']=$facturesIds;
				$pyts=$this->Vente->Facture->Paiement->find('all',array('fields'=>array('Paiement.*',
																						'Facture.operation',
																						'Facture.date',
																						'Facture.numero',
																						'Facture.id',
																						'Facture.monnaie',
																						'Personnel.id',
																						'Personnel.name'

																						),
																		'conditions'=>$pytConds
																		)
																);
				
				foreach($pyts as $key=>$pyt){
					if($pyt['Paiement']['personnel_id']==$journalInfo['Personnel']['id']){
						$total_cash['resto']+=$pyt['Paiement']['montant'];
					}
					else {
						$other_people_pyts['total']+=$pyt['Paiement']['montant'];
						$other_people_pyts['pyts'][]=$pyt;
					}	
					
				}
				$total_credits['resto']=$total_factures['resto']-$total_cash['resto']-$bonus;
			}
			else {
				$total_cash['resto']=$total_factures['resto']-$total_credits['resto']-$bonus;
			}												

			unset($pytConds['Paiement.facture_id']);
			$pytConds['Facture.etat !=']='annulee';
			$pytConds['NOT']=array('Paiement.facture_id'=>$facturesIds);
		
			$pyts=$this->Vente->Facture->Paiement->find('all',array('fields'=>array('Paiement.*',
																					'Facture.id',
																					'Facture.numero',
																					'Facture.monnaie',
																					'Facture.operation',
																					'Facture.date',
																					'Facture.tier_id',
																					'Personnel.name'
																					),
																		'conditions'=>$pytConds
																		)
															);
			
			$total_pyts['resto']=0;
			foreach($pyts as $key=>$pyt){
					$total_pyts['resto']+=$pyt['Paiement']['montant'];
					$tierInfo=$this->Vente->Facture->Tier->find('first',array('conditions'=>array('Tier.id'=>$pyt['Facture']['tier_id']),
															'fields'=>array('Tier.id','Tier.name'),
															'recursive'=>-1
															)
												);
					$pyts[$key]['Tier']=$tierInfo['Tier'];
			}
			if($mensuelle&&Configure::read('aser.comptabilite')&&($this->data['Journal']['compta']=='oui')){
				$this->loadModel('CompteOperation');
				$caisseConds['Compte.numero >=']=56000000;
				$caisseConds['Compte.numero <']=5800000;
				$caisseConds['CompteOperation.date >=']=$this->data['Journal']['date'];
				$caisseConds['CompteOperation.date <=']=$this->data['Journal']['date1'];
				unset($caisseConds['Operation.date <=']);
				unset($caisseConds['Operation.date >=']);
				$model1='CompteOperation';
				$model2='Compte';
				
			}
			else {
				$this->loadModel('Operation');
				if(Configure::read('caisse.caisse_id')){
					$caisseConds['Operation.element_id']=Configure::read('caisse.caisse_id');
				}
				$caisseConds['Operation.model']='Caiss';
				$caisseConds['Operation.credit >']=0;
				$caisseConds['Operation.credit >']=0;
				$caisseConds['Operation.monnaie']='BIF';
				$model1='Operation';
				$model2='Caiss';
				
			}
			$retraits=$this->$model1->find('all',array('fields'=>array($model1.'.*',$model2.'.name'),
																		'conditions'=>$caisseConds
																		)
																	);
			$total_depenses=0;
			foreach($retraits as $retrait){
					$total_depenses+=$retrait[$model1]['credit'];
				
			}			
			if($mensuelle&&Configure::read('aser.comptabilite')&&($this->data['Journal']['compta']=='oui')){
				$caisseConds['Compte.numero >=']=56000000;
				$caisseConds['Compte.numero <']=58000000;
			}
			else {
				$caisseConds['Operation.ignore']=0;
				$caisseConds['Operation.auto']=0;
				$caisseConds['Operation.libelle !=']='Report';
				unset($caisseConds['Operation.credit >']);
				$caisseConds['Operation.debit >']=0;
			}
		//	exit(debug($caisseConds));
			$ajouts=$this->$model1->find('all',array('fields'=>array($model1.'.*',$model2.'.name'),
																		'conditions'=>$caisseConds
																		)
																	);
			$total_ajouts=0;
			foreach($ajouts as $ajout){
					$total_ajouts+=$ajout[$model1]['debit'];
			}	
			
									
			$versement=$total_factures['resto']
						+$total_ajouts
						+$total_pyts['resto']
						
						-$bonus
						-$total_credits['resto']
						-$total_depenses;
						
			
			$resultat=$total_factures['resto']+$total_ajouts-$total_depenses-$bonus;
			//setting the array of data to save in the journal at the closing moment
			$journalData['Journal']['versement']=$versement;
			$journalData['Journal']['ventes']=$total_factures['resto'];
			$journalData['Journal']['cash']=$total_cash['resto'];
			$journalData['Journal']['ajouts']=$total_ajouts;
			$journalData['Journal']['bonus']=$bonus;
			$journalData['Journal']['credit']=$total_credits['resto'];
			$journalData['Journal']['depenses']=$total_depenses;
			$journalData['Journal']['paiements']=+$total_pyts['resto'];
			$journalData['Journal']['montant_for_caisse']=$versement+$total_depenses-$total_ajouts;
			
			
			$condPers['Personnel.fonction_id']=array(2);
			$condPers['Personnel.actif']='oui';
			if($this->Auth->user('fonction_id')==2){
			//	$condPers['Personnel.id']=$this->Auth->user('id');
			}
			$personnels = $this->Vente->Personnel->find('list',array('conditions'=>$condPers,
																	'order'=>array('Personnel.fonction_id','Personnel.name')
																	));
			$this->set(compact('total_depenses',
								'personnels',
								'total_credits',
								'total_factures',
								'factures',
								'versement',
								'total_pyts',
								'pyts',
								'retraits',
								'journalInfo',
								'ajouts',
								'total_ajouts',
								'mensuelle',
								'date1',
								'date',
								'ventes',
								'total_cash',
								'global',
								'resultat',
								'journals',
								'bonus',
								'model1',
								'model2',
								'sums',
								'journalData',
								'other_people_pyts'
								)
						);
		if($mensuelle){
			$this->render('rapport');
		}
		else {
			$this->render('journal');
		}
	}
	function _caisse_auto_copy($date,$montant){
			$data['date'] = $date;
            $data['ordre'] = 1;
            $data['model1'] = 'ventes';
            $data['montant'] =$montant;
            $data['libelle'] = 'VENTES JOURNALIERES';
            $data['monnaie'] = Configure::read('aser.default_currency');
            $data['mode_paiement'] ='cash';
            $data['model2'] = 'caisses';
            $data['element2'] =Configure::read('caisse.caisse_id');
            $data['mode'] = 'index';
            $data['id1'] =null;
            $data['id2'] =null;
            $data['element1'] = Configure::read('caisse.type_id');
			$this->Product->add_caisse_op(array('Operation'=>$data),false);
	}
	
	function cloturer(){
		$id=$this->data['params']['id'];
		$personnel_id=$this->data['params']['personnel_id'];
		$shift=$this->data['params']['shift'];
		
		$this->autoRender=false;
		$journal=$this->Vente->Facture->Journal->find('first',array('fields'=>array('Journal.*'),
															'conditions'=>array('Journal.id'=>$id),
															)
										);
		if($journal['Journal']['closed']==0){
			if($this->Auth->user('id')!=$personnel_id)
				exit(json_encode(array('success'=>false,'msg'=>"Vous n 'êtes pas celui qui a crée le journal ")));
			$factures=$this->Vente->Facture->find('all',array('fields'=>array('Facture.*'),
															'conditions'=>array('Facture.journal_id'=>$id,
																			),
															)
										);
		//checking first
			$factureIds=array();
			foreach($factures as $facture){
				if(in_array($facture['Facture']['etat'],array('en_cours','cloturer','confirmee'))){
					exit(json_encode(array('success'=>false,'msg'=>'la facture n° '.$facture['Facture']['numero'].' n\'est pas classée !')));
				}
				else if(($facture['Facture']['reste']>=$facture['Facture']['montant'])&&
						(!in_array($facture['Facture']['etat'],array('credit','annulee')))
						&&
						($facture['Facture']['montant']!=0)
						){
					exit(json_encode(array('success'=>false,'msg'=>'la facture n° '.$facture['Facture']['numero'].' n\'est pas bien classée !')));
				}
				else if(!in_array($facture['Facture']['etat'],array('annulee','bonus'))){
					$pyts=$this->Vente->Facture->pyts($facture['Facture']['id']);
				//	exit(debug($pyts));
					if(($facture['Facture']['montant']-$facture['Facture']['reste'])!=$pyts){
						exit(json_encode(array('success'=>false,'msg'=>'Vérifier les paiements de la facture n° '.$facture['Facture']['numero'])));		
					}
				} 
				$factureIds[]=$facture['Facture']['id'];
			}
			//saving the shift for this journal 
			$ventes=$this->Vente->find('list',array('fields'=>array('Vente.id','Vente.historique_id'),
												'conditions'=>array('Vente.facture_id'=>$factureIds)
												));
			$historiques=$this->Vente->Historique->find('all',array('fields'=>array('Historique.id'),
													'conditions'=>array('Historique.id'=>$ventes)
													));			
			foreach($historiques as $historique){
				$historique['Historique']['shift']=$shift;
				$this->Vente->Historique->save($historique);
			}		
		}
		$journalData['Journal']=$this->data['Journal'];
		$journalData['Journal']['closed']=1;
		$journalData['Journal']['observation']=$this->data['params']['observation'];
		$journalData['Journal']['id']=$id;
		
		//copying the cash made to the defined caisse in tresorerie
		if(Configure::read('aser.caisse_auto_copy')){
			$this->_caisse_auto_copy($journal['Journal']['date'], $journalData['Journal']['montant_for_caisse']);
		}	
		
		//auto copy in sorti of all ingredients corresponding to the sales included in this journal
		if(Configure::read('aser.connexion')&&Configure::read('aser.ingredient')){
			$this->stock_ingredient($journal['Journal']['id'],$journal['Journal']['date'],$shift);
		}
		
		if($this->Vente->Facture->Journal->save($journalData))
			exit(json_encode(array('success'=>true,'msg'=>'succès !')));
		else  exit(json_encode(array('success'=>false,'msg'=>"Impossible d'enregistrer")));
	}

	function rapport(){
		$bonus=0;
		$total_factures['resto']=0;
		$total_cash['resto']=0;
		$total_credits['resto']=0;
		$total_pyts['resto']=0;
		$total_ajouts=$total_depenses=$versement=0;
		$date=$date1=null;
		$pyts=$ajouts=$retraits=array();
		$global='non';
		$resultat=0;
		if(!empty($this->data)){
		$this->journal(null,$this->data,true);
		}
		else {
			$this->set(compact('total_factures',
							'total_credits',
							'total_pyts',
							'total_ajouts',
							'total_cash',
							'total_depenses',
							'versement',
							'pyts',
							'ajouts',
							'retraits',
							'date',
							'date1',
							'global',
							'resultat',
							'bonus'
							));
		}
	}
	
	/*
	function old_rapport(){ 
		$conditions=$factures=array();
		$total=$reste=0;
		$date1=$date2=date('Y-m-d');
		if(!empty($this->data)){
			if($this->data['Facture']['tier_id']!=0) {
				$conditions['Facture.tier_id']=$this->data['Facture']['tier_id'];
			}
			if($this->data['Facture']['personnel_id']!=0) {
				$conditions['Facture.personnel_id']=$this->data['Facture']['personnel_id'];
			}
		
			if($this->data['Facture']['personnel_id']!=0) {
				$conditions['Facture.personnel_id']=$this->data['Facture']['personnel_id'];
			}
	
			if($this->data['Facture']['numero']!='toutes') {
				$conditions['Facture.numero']=$this->data['Facture']['numero'];
			}
			if($this->data['Facture']['etat']!='toutes') 
				$conditions['Facture.etat']=$this->data['Facture']['etat'];
			else 
				$conditions['Facture.etat !=']='annulee';
				
			if(!empty($this->data['Facture']['date1'])) {
				$conditions['Facture.date >=']=$date1=$this->data['Facture']['date1'];
			}
			if(!empty($this->data['Facture']['date2'])) {
				$conditions['Facture.date <=']=$date2=$this->data['Facture']['date2'];
			}
		
				
											
		$factures=$this->Vente->Facture->find('all',array('fields'=>array('Facture.*',
																'Tier.name',
																'Personnel.name',
																),
													'conditions'=>$conditions,
													)
										);
		$total=$reste=0;
		foreach($factures as $facture){
			$total+=$facture['Facture']['montant'];
			$reste+=$facture['Facture']['reste'];
		}
		
	}
		$tiers = $this->Vente->Facture->Tier->find('list',array('conditions'=>array('Tier.type'=>array('client','polyvalent'),
																			'Tier.actif'=>1
																			)
																));
		$tiers[0]='toutes';
		$caissiers=$this->Conf->find('caissiers');
		$personnels = $this->Vente->Personnel->find('list',array('conditions'=>array('Personnel.fonction_id'=>$caissiers)));
		$personnels[0]='toutes';
		$serverGpe=$this->Conf->find('serveurs');
		$serveurs = $this->Vente->Personnel->find('list',array('conditions'=>array('Personnel.fonction_id'=>$serverGpe),
																'order'=>'Personnel.name'
																));
		$serveurs[0]='toutes';
		$produits = $this->Vente->Produit->find('list',array(
																'order'=>'Produit.name'
																));
		$produits[0]='toutes';
		$this->set(compact('tiers','factures','date1','date2','total','personnels','serveurs','reste','produits'));
		
	}
	 */
	/**
	 * this function is asked by buja cafe
	 * it exports all the consos of the past day 
	 * 
	 */
	 
	function auto_report($type,$sectionId=null){
		$date=date('Y-m-d',strtotime('-1 day'));
		if($type=='conso'){
			$data['Produit']['section_id']=$sectionId;
			$data['Facture']['date1']=$date;
			$data['Facture']['date2']=$date;
			$data['Vente']['xls']=1;
			$sectionInfo=$this->Vente->Produit->Groupe->Section->find('first',array('fields'=>array('Section.name'),
																					'conditions'=>array('Section.id'=>$sectionId)
																					));
			if(!empty($sectionInfo))
				$data['name']=$sectionInfo['Section']['name'];
			
			$this->consommations(null,null,null,$data);
		}
		else {
			$journals=$this->Vente->Facture->Journal->find('all',array('fields'=>array('Journal.*','Personnel.name'),
														'conditions'=>array('Journal.date'=>$date,
																			'Journal.closed'=>1
																			),
														'order'=>array('Personnel.name','Journal.numero')
														));
			$data=array();
			foreach($journals as $key=>$journal){
				$data[$key]['Caissier']=$journal['Personnel']['name'];
				$data[$key]['Numero']=$journal['Journal']['numero'];
				$data[$key]['Ventes']=$journal['Journal']['ventes'];
				$data[$key]['Cash']=$journal['Journal']['cash'];
				$data[$key]['Credit']=$journal['Journal']['credit'];
				$data[$key]['Bonus']=$journal['Journal']['bonus'];
				$data[$key]['Paiements']=$journal['Journal']['paiements'];
				$data[$key]['Ajouts']=$journal['Journal']['ajouts'];
				$data[$key]['Depenses']=$journal['Journal']['depenses'];
				$data[$key]['Versement']=$journal['Journal']['versement'];
				$data[$key]['Observation']=$journal['Journal']['observation'];
			}
			$name='des_caissiers';
			$this->set(compact('data','name'));
			$this->layout='ajax';
		}
	}
	
	function consommations($sectionId=null,$date1=null,$date2=null,$data=null){
		$conditions=$ventes=$jourCond=array();
		$total=$quantite=$totalReduit=$ben=$pa=0;
		$caissier=null;
		$group=array('Vente.produit_id','Vente.PU');
		$order=array('Produit.name');
		$obs=false;
		if($sectionId){
			$this->data['Produit']['section_id']=$sectionId;
			$this->data['Facture']['date1']=$date1;
			$this->data['Facture']['date2']=$date2;
			$this->data['Vente']['bonus']=1;
		}
		else if(!empty($data)){
			$this->data=$data;
		}
		if(!empty($this->data)){
			
			if(isset($this->data['Vente']['stock_id'])&&($this->data['Vente']['stock_id']!=0)){
				$conditions['Vente.stock_id']=$this->data['Vente']['stock_id'];
			}
			if(isset($this->data['Vente']['produit_id'])&&($this->data['Vente']['produit_id']!=0)){
				$conditions['Vente.produit_id']=$this->data['Vente']['produit_id'];
			}
			if(isset($this->data['Produit']['groupe_id'])&&($this->data['Produit']['groupe_id']!=0)) {
				$conditions['Produit.groupe_id']=$this->data['Produit']['groupe_id'];
			}
			else if(isset($this->data['Produit']['section_id'])&&($this->data['Produit']['section_id']!=0)){
				$conditions['Produit.groupe_id']=$this->Vente->Produit->Groupe->find('list',array('fields'=>array('Groupe.id','Groupe.id'),
																						'conditions'=>
																								array('Groupe.section_id'=>$this->data['Produit']['section_id'])
																						)
																			);
			}
			if(isset($this->data['Produit']['groupe_comptable_id'])&&($this->data['Produit']['groupe_comptable_id'][0]!=0)) {
				$conditions['Produit.groupe_comptable_id']=$this->data['Produit']['groupe_comptable_id'];
			}
			if(!empty($this->data['Facture']['date1'])) {
				$conditions['Facture.date >=']=$date1=$this->data['Facture']['date1'];
				$jourCond['Journal.date >=']=$date1;
			}
			if(!empty($this->data['Facture']['date2'])) {
				$conditions['Facture.date <=']=$date2=$this->data['Facture']['date2'];
				$jourCond['Journal.date <=']=$date2;
			}
			$conditions['Facture.etat !=']='annulee';
			
			if(isset($this->data['Vente']['personnel_id'])&&($this->data['Vente']['personnel_id']!=0)) {
				$search=$this->Vente->Facture->Personnel->find('first',array('fields'=>array('Personnel.id','Personnel.name'),
																			'conditions'=>array('Personnel.id'=>$this->data['Vente']['personnel_id'])
																			)
																);
				$caissier=$search['Personnel']['name'];
				//search by caissier means search par journal
				$jourCond['Journal.personnel_id']=$this->data['Vente']['personnel_id'];
				$journals=$this->Vente->Facture->Journal->find('list',array('fields'=>array('Journal.id','Journal.id'),
																			'conditions'=>$jourCond,
																			)
																);
				$conditions['Facture.journal_id']=$journals;
			}
			if(!empty($this->data['Facture']['etat'])){
				$conditions['Facture.etat']=$this->data['Facture']['etat'];
			}	
			else {
				$conditions['Facture.etat !=']='annulee';
			}
			$ventes=$this->Vente->find('all',array('fields'=>array(
																	'Produit.name',
																	'Produit.PA',
																	'Produit.id',
																	'sum(Vente.quantite) as quantite',
																	'sum(Vente.montant) as montant',
																	'Vente.PU',
																	'Vente.PA',
																	),
														'conditions'=>$conditions,
														'recursive'=>1,
														'group'=>$group,
														'order'=>$order
														)
											);
			
			foreach($ventes as $key=>$vente){
				$total+=$vente['Vente']['montant'];
				$quantite+=$vente['Vente']['quantite'];
				$ventes[$key]['Vente']['PA']=($vente['Vente']['PA']==0)?$vente['Produit']['PA']:$vente['Vente']['PA'];
				$ventes[$key]['Vente']['BEN']=($vente['Vente']['PU']-$ventes[$key]['Vente']['PA'])*$vente['Vente']['quantite'];
				$ben+=$ventes[$key]['Vente']['BEN'];
				$pa+=$ventes[$key]['Vente']['PA'];
			}
		
			//*
			//total incluant les reductions
			$consos=$this->Vente->find('all',array('fields'=>array('Facture.reduction',
																	'sum(Vente.montant) as montant'
																	),
														'conditions'=>$conditions,
														'group'=>array('Facture.id')
													));
											
			foreach($consos as $conso){
				$totalReduit+=$conso['Vente']['montant']-($conso['Vente']['montant']*$conso['Facture']['reduction']/100);
			}
		//*/
		}
		$personnels = $this->Vente->Personnel->find('list',array('conditions'=>array('Personnel.fonction_id'=>array(2,4),'Personnel.actif'=>'oui')));
		$personnels[0]='';
		$produits = $this->Vente->Produit->find('list',array('conditions'=>array('Produit.actif'=>'oui'),
														'order'=>array('Produit.name')
														));
		$produits1=array('')+$produits;
		$this->set(compact('totalReduit',
						'caissier',
						'ventes',
						'date1',
						'date2',
						'total',
						'personnels',
						'quantite',
						'ben',
						'pa',
						'serveurs',
						'produits1')
						);
	//	exit(debug($this->data));
		if(!empty($this->data['Vente']['xls'])&& ($this->data['Vente']['xls']==1)){
			$data=array();
			foreach($ventes as $key=>$vente){
				$data[$key]['Produit']=$vente['Produit']['name'];
				$data[$key]['Quantité']=$vente['Vente']['quantite'];
				$data[$key]['PU']=$vente['Vente']['PU'];
				$data[$key]['PT']=$vente['Vente']['montant'];
			}
			$name=(!empty($this->data['name']))?$this->data['name']:'consommations';
			$filename=$this->Product->excel($data,array(),$name);
			$this->redirect('/files/'.$filename);
		}		
		
	}
	
	function lastFactureId(){
		$this->autoRender=false;
		$vente=$this->Vente->find('first',array('fields'=>array('Vente.facture_id',
																),
													'conditions'=>array('Vente.printed'=>0,
																		'Facture.etat'=>'confirmee',
																		'Facture.fetched'=>0,
																		),
													'order'=>array('Vente.id asc')
													)
												);
		
		if(empty($vente)){
			$facture['Facture']['found']='no';
		}
		else {
			$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.id',
																			'Facture.numero',
																			'Facture.beneficiaire',
																			'Facture.original',
																			'Facture.montant',
																			'Facture.reste',
																			'Facture.reduction',
																			'Facture.table',
																			'Facture.etat',
																			'Pesonnel.name'
																),
													'conditions'=>array('Facture.id'=>$vente['Vente']['facture_id'],
																		),
													)
												);
			//journals stuff 
			$journal=$this->Product->journal();
			
			$test=$this->Session->read('ignorer');
			if(($journal['date']!=date('Y-m-d'))&&(!$test)){
				$facture['Facture']['found']='journal_erreur';
				$facture['Facture']['msg']='Votre Journal a une ancienne date. Vérifié si vous n\'avez pas oublié de clôturer le journal précédent.';
				$this->Session->write('ignorer',true);
				exit(json_encode($facture['Facture']));
			}
			
			$update['Facture']['journal_id']=$journal['id'];
			$update['Facture']['date']=$journal['date'];
			$update['Facture']['id']=$facture['Facture']['id'];
			$update['Facture']['fetched']=1;
			$this->Vente->Facture->save($update);
			
			$facture['Facture']['found']='yes';
			$facture['Facture']['date']=$update['Facture']['date'];
			$facture['Facture']['serveur']=$facture['Personnel']['name'];
		}	
		exit(json_encode($facture['Facture']));
	}
	
	function detail_index($venteId){
		$this->loadModel('Detail');
		$details=$this->Detail->find('all',array('conditions'=>array('Detail.model_id'=>$venteId,
																	'Detail.model'=>'Vente'
																	)));
		$total=0;
		foreach($details as $key=>$detail){
			$details[$key]['Detail']['total']=$detail['Detail']['PA']*$detail['Detail']['quantite'];
			$total+=$details[$key]['Detail']['total'];
		}
		$this->set(compact('details', 'total'));
		$this->layout='ajax';
	}
	function vente(){
		$venteConditions=$this->Session->read('venteConditions');
		if((empty($this->data))&&(empty($venteConditions))) {
			$this->set('ventes', $this->paginate());
		}
		elseif(!empty($this->data)) {
		//building conditions
			if($this->data['Produit']['name']!='toutes') {
				$venteConditions['Produit.name LIKE']='%'.$this->data['Produit']['name'].'%';
			}
			$this->set('ventes', $this->paginate($venteConditions));
			$this->Session->write('venteConditions',$venteConditions);
		}
		else {
			$this->set('ventes', $this->paginate($venteConditions));
		}
		$caisses=$this->Vente->Facture->Paiement->Caiss->find('list',array('conditions'=>array('Caiss.actif'=>'oui'
																			)
																));
		
		$tiers = $this->Vente->Facture->Tier->find('list',array('conditions'=>array('Tier.actif'=>1,
																			'Tier.type'=>array('client','polyvalent')
																			)
													)
										);
		$tiers[0]='toutes';
		$unites = $this->Vente->Unite->find('list');
		$this->set(compact('tiers','unites','caisses'));
	}
	function confirm_order($factureId){
		$this->Vente->Facture->save(array('Facture'=>array('id'=>$factureId,'etat'=>'confirmee','printed'=>0)));
		exit(json_encode(array('success'=>true,'msg'=>'OK')));
	}
	
	function touchscreen($table=1,$date=null){
		$date=(is_null($date))?(date('Y-m-d')):($date);
		$config=Configure::read('aser');
		$fonction=$this->Auth->user('fonction_id');
		//to set the stock to use
		$stock=$this->_find_stock();
		
		//sama mode stuff
		$serverGpe=$this->Conf->find('serveurs');
		
			if($config['sama']){
				$mode=($fonction==$serverGpe)?('serveur'):('caissier');
				if($mode=='serveur'){
					$serveurId=$this->Auth->user('id');
					$conditions['Facture.personnel_id']=$serveurId;
				}
			}
			else {
				$mode='undefined';
			}	
			
		if($fonction==2){
		//	$conditions['Journal.personnel_id']=$this->Auth->user('id');
		}	
		$condFact['Facture.operation']='Vente';
		if(!Configure::read('aser.magasin')){
			$condFact['Facture.table']=$table;
		}
		$condFact['Facture.etat']=array('confirmee','en_cours','cloturer');
		$condFact['Facture.date']=$date;
		$factures=$this->Vente->Facture->find('all',array('fields'=>array('Facture.*','Personnel.name'
																),
													'conditions'=>$condFact,
													'order'=>'Facture.id desc'
													)
										);
		if($this->RequestHandler->isAjax()){
			$this->set(compact('factures','mode'));
			$this->layout="ajax";
			$this->render('list_factures');
		}
		else {
			$serveurs = $this->Vente->Personnel->find('list',array('conditions'=>array('Personnel.fonction_id'=>1,
																						'Personnel.actif'=>'oui',
																						),
																	'order'=>array('Personnel.name asc')
																	)
														);
			$conditions=array();
			$conditions['Produit.actif !=']='non';
   			$produits = $this->Vente->Produit->find('all',array( 'fields'=>array('Produit.id',
   																					'Groupe.section_id',
   																					'Groupe.id',
   																					'Produit.name',
   																					'Produit.PV',
   																					'Produit.acc',
																					),
																	'conditions'=>$conditions,
																	'order'=>array('Produit.name asc')
																	)
													);
			$sections=$this->Vente->Produit->Groupe->Section->find('list');
			$groupes=$this->Vente->Produit->Groupe->find('all',array('recursive'=>-1,'conditions'=>array('Groupe.actif'=>'oui')));
			$thermal=$this->Conf->find('thermal');
			$change=$this->Conf->find('change');
		//table status
			$tiers=$this->Vente->Facture->Tier->find('list',array('conditions'=>array('Tier.actif'=>1,'Tier.type'=>'client'),
														'order'=>array('Tier.name')
			));
		//	exit(debug($tiers));
			$tiers[0]='';
			$numbers=$tables=array();
			
			if(Configure::read('aser.multi_resto')){
				
				//filling the bars array needed in multi resto to select which place to work on
					foreach(Configure::read('bars') as $bar=>$barTables)
						$bars[$bar]=$this->Conf->find($bar);
				//place
				$pos=$this->Session->read('pos');
				if(!empty($pos)){	
					$tables=$this->getTables($pos);
				}
			}
			else {
				for($i=1; $i<=Configure::read('aser.tables');$i++){
					$tables[$i]=$this->table_state($i);	
				}
			}
			//parametres
			$this->set(compact('produits',
							'serveurs',
							'factures',
							'caisses',
							'sections',
							'stock',
							'date',
							'mode',
							'thermal',
							'change',
							'tables',
							'groupes',
							'sections',
							'fonction',
							'tiers',
							'bars'
							));
		}
	}
	
	//*/
	function index($date=null) {
		$date=(is_null($date))?(date('Y-m-d')):($date);
		$fonction=$this->Auth->user('fonction_id');
		//fetching the bills
		if(in_array($fonction,array(2,4))){
			$cond1['Journal.personnel_id']=$this->Auth->user('id');
		}
		$cond1['Facture.operation']='Vente';
		$cond1['Facture.date']=$date;
	
		$factures=$this->Vente->Facture->find('all',array('fields'=>array('Facture.*','Personnel.name'
																),
													'conditions'=>$cond1,
													'order'=>'Facture.id desc'
													)
										);
		if($this->RequestHandler->isAjax()){
			$this->set(compact('factures'));
			$this->layout="ajax";
			$this->render('list_factures');
		}
		else {
			$cond2['Groupe.afficher']='oui';
			$cond2['Groupe.actif']='oui';
			$selectedSections=$this->Session->read('resto_sections');
			if(!empty($selectedSections)){
				$cond2['Groupe.section_id']=$selectedSections;
				$this->data['Produit']['section_id']=$selectedSections;
			}
			
			
			$groupes=$this->Vente->Produit->Groupe->find('list',array('order'=>array('Groupe.name asc'),
																	'conditions'=>$cond2,
																	'recursive'=>-1,
																	)
														);
			
			$groupeIds=array_keys($groupes);
			$cond3['Produit.actif']='oui';
			if(Configure::read('aser.groupes_on_index')){
				$cond3['Produit.groupe_id']=$groupeIds[0];
			}
			if(Configure::read('aser.multi_pv')||(Configure::read('aser.default_stock')<1)){
				$produits = $this->Vente->Produit->find('all',array( 'fields'=>array('Produit.id',
   																				'Produit.name',
   																				'Produit.PV',
   																				'Produit.type'
																					),
																	'conditions'=>$cond3,
																	'order'=>array('Produit.name asc')
																	)
													);
				$produits=$this->_product_list($produits);
			}
			else {
				$produits = $this->Vente->Produit->find('list',array( 'fields'=>array('Produit.id','Produit.fullname'),
																	'conditions'=>$cond3,
																	'order'=>array('Produit.name asc'),
																	'cache' => 'produits_list'
																	)
													);
			}
			$thermal=$this->Conf->find('thermal');
			$change=$this->Conf->find('change');
			if(Configure::read('aser.multi_resto')){
				foreach(Configure::read('bars') as $name=>$plage){
					$bars[$name]=$name;
				}
			} 
			
			$stock=$this->_find_stock(); //to set the stock to use
			
			$personnels = $this->Vente->Personnel->find('list',array('conditions'=>array('Personnel.fonction_id'=>1,//serveur fonction
																						'Personnel.actif'=>'oui',
																						),
																	'order'=>array('Personnel.name asc')
																	)
														);
			
			$personnels[0]='';
			$garnishList=array();
			if(!Configure::read('aser.magasin')){
				$garnishs=$this->Vente->Produit->find('all',array('conditions'=>array('Groupe.accompagnement'=>'oui'),
																'fields'=>array('Produit.id','Produit.name')
																));
				foreach($garnishs as $garnish){
					$garnishList[$garnish['Produit']['id']]=$garnish['Produit']['name'];
				}
				$garnishList[0]='';
			}

			$this->set(compact('produits',
								'personnels',
								'factures',
								'sections',
								'stock',
								'date',
								'thermal',
								'change',
								'tiers',
								'fonction',
								'bars',
								'groupes',
								'garnishList'
								));
		}
	}
	
	function combobox(){
		$conditions['Produit.groupe_id']=$this->data['Vente']['groupe_id'];
		$conditions['Produit.actif !=']='non';
		if(Configure::read('aser.multi_pv')||(Configure::read('aser.default_stock')<1)){
			$produits = $this->Vente->Produit->find('all',array('conditions'=>$conditions,
																'order'=>array('Produit.name asc'),
																'fields'=>array('Produit.id',
																				'Produit.name',
   																				'Produit.PV',
   																				'Produit.type'
																				),
																)					
													);
			$produits=$this->_product_list($produits);
		}
		else {
			$produits = $this->Vente->Produit->find('list',array('conditions'=>$conditions,
																'order'=>array('Produit.name asc'),
																'fields'=>array('Produit.id',
																				'Produit.fullname',
																				),
																)					
													);
		}
		$this->set(compact('produits'));
		$this->layout="ajax";
		$this->render('update_produits');
	}
	
	function update_produits(){
		//saving to session the selected parameters
		$this->Session->write('resto_stock',$this->data['Vente']['stock_id']);
		$this->Session->write('pos',$this->data['Vente']['pos']);
		if(isset($this->data['Produit']['section_id'])){
			$this->Session->write('resto_sections',$this->data['Produit']['section_id']);
		}
		if(isset($this->data['Vente']['mode_restaurant'])){
			$this->Session->write('mode_restaurant',$this->data['Vente']['mode_restaurant']);
		}
		exit(json_encode(array('success'=>true)));
	}
	
	function _lister($ventes_old){
		$j=0;
	//	exit(debug($ventes_old));
		$ventes=$skip=array();
		foreach($ventes_old as $i=>$vente){
			if(!in_array($i,$skip)){
				if($vente['Vente']['acc']>0){
					$ventes[$j]['Produit']['name']=(!empty($ventes_old[$i+1]['Produit']['name']))?
												$vente['Produit']['name'].' ('.$ventes_old[$i+1]['Produit']['name'].')':
												$vente['Produit']['name'];
					$ventes[$j]['Vente']=$vente['Vente'];
					$ventes[$j]['Produit']['id']=$vente['Produit']['id'];
					$skip[]=$i+1;
				}
				else {
					$ventes[$j]=$vente;
				}
				$j++;
			}
		}
		return $ventes;
	}
	
	function _saveOrder($facture,$bon,$msg,$ventes) {
	if(!empty($ventes)){
		//save order
		$order['Order']=$facture['Facture'];
		$order['Order']['facture_id']=$facture['Facture']['id'];
		$order['Order']['type']=$bon;
		$order['Order']['msg']=$msg;
		$order['Order']['heure']=date('H:i:s');
		$order['Order']['date']=date('Y-m-d');
		$order['Order']['id']=null;
		$this->loadModel('Order');
		$this->Order->save($order);
		
		//save order details
		foreach($ventes as $vente){
			$orderDetails['OrderDetail']['produit_id']=$vente['Produit']['id'];
			$orderDetails['OrderDetail']['quantite']=$vente['Vente']['print_qty'];
			$orderDetails['OrderDetail']['order_id']=$this->Order->id;
			$orderDetails['OrderDetail']['id']=null;
			$this->Order->OrderDetail->save($orderDetails);
		}
	}
	}
	
	function bon_tester($factureId,$bon,$force,$test=false,$msg='',$consoId=''){
		$data['boissons']=$this->print_bon($factureId,'boissons',$force,$test,$msg,$consoId);
		$data['plats']=$this->print_bon($factureId,'plats',$force,$test,$msg,$consoId);
		exit(json_encode($data));
	}
					
	function print_bon($factureId,$bon,$force,$test=false,$msg='',$consoId=''){
		$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.date',
																			'Facture.table',
																			'Facture.pos',
																			'Facture.numero',
																			'Personnel.name',
																			'Facture.personnel_id',
																			'Facture.id',
																			),
															'conditions'=>array('Facture.id'=>$factureId)
															)
													);
		//first print boissons
		
			$section=($bon=='boissons')?($this->Conf->find('boissons_section')):($this->Conf->find('plats_section'));
		if($consoId==''){
			$conditions['Vente.facture_id']=$factureId;
			$groupes=$this->Vente->Produit->Groupe->find('list',array('fields'=>array('Groupe.id','Groupe.id'),
																	'conditions'=>array('Groupe.section_id'=>$section)
																	));
			$conditions['Produit.groupe_id']=$groupes;
			$ventes_old=$this->Vente->find('all',array('fields'=>array(
																'Produit.id',
																'Produit.name',
																'Produit.acc',
																'Vente.quantite',
																'Vente.printed',
																'Vente.acc',
																'Vente.id',
																'Vente.facture_id',
																'Vente.produit_id'
																),
													'conditions'=>$conditions,
													'order'=>array('Vente.id desc')
													)
										);
			$printed=array();
			foreach($ventes_old as $key=>$vente){
				$diff=$vente['Vente']['quantite']-$vente['Vente']['printed'];
				if($force==0){
						$vente['Vente']['print_qty']=$diff;
				}
				else {
					$vente['Vente']['print_qty']=($msg=='COMMANDE ANNULEE')?
													(($vente['Vente']['printed']>$vente['Vente']['quantite'])?
													$vente['Vente']['quantite']:$vente['Vente']['printed']):
													$vente['Vente']['quantite'];
				}
				if(($test=='false')&&($msg!='COMMANDE ANNULEE')){
					$vente['Vente']['printed']+=$diff;
					if(!$this->Vente->save($vente))
						exit(json_encode(array('success'=>false,'msg'=>'Probleme avec l enregistrement des quantites imprimes')));
				}
				if($vente['Vente']['print_qty']>0){
					$printed[$key]=$vente;
				}
			}
			$ventes=$this->_lister($printed);
		}
		else {
			$parts=explode('_',$consoId);
			if($parts[2]==$section){
				$ventes[0]['Vente']['print_qty']=$parts[0];
				$ventes[0]['Produit']['name']=$parts[1];
			}
		}
		if($test=='true'){
			if(empty($ventes))
				return false;
			else 
				return true;
		}
		$thermal=$this->Conf->find('thermal');
		//saving each order sent to the kitchen in the database.
		$this->_saveOrder($facture, $bon, $msg, $ventes);
		
		$this->set(compact('facture','bon','thermal','ventes','msg'));
		$this->layout='printing';
	}
	
	function showOrders($factureId){
		$this->loadModel('Order');
		$orders=$this->Order->find('all',array('conditions'=>array('Order.facture_id'=>$factureId),
											'order'=>array('Order.id asc'),
											'contain'=>array('Personnel'=>array('fields'=>array('Personnel.name')),
															'Facture'=>array('fields'=>array('Facture.table','Facture.numero')),
															'OrderDetail'=>array(
																			'order'=>array('OrderDetail.id asc'),
																			'Produit'=>array('fields'=>array('Produit.name'))))
											));
	//	exit(debug($orders));
		$thermal=$this->Conf->find('thermal');
		$this->set(compact('orders','thermal','factureId'));
	}
	
	function print_facture($factureId,$show_aserb_num=0){
	//*
		$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.*',
																		'Tier.name','Tier.telephone',
																		'Personnel.name',
																		'Personnel.name',
																		'Journal.personnel_id'
																		),
															'conditions'=>array('Facture.id'=>$factureId)
															)
													);
	//	die(debug($facture));
		$ventes_old=$this->Vente->find('all',array('fields'=>array(
																'Produit.id',
																'Produit.name',
																'Vente.PU',
																'Vente.acc',
																'Vente.quantite',
																'Vente.montant',
																),
													'conditions'=>array('Vente.facture_id'=>$factureId),
														'order'=>array('Vente.id desc')
													
													)
										);
//		exit(debug($ventes_old));
		$ventes=$this->_lister($ventes_old);
		
		$reduction=$facture['Facture']['reduction'];
		if($facture['Facture']['classee']==0){
			$this->Vente->Facture->save(array('Facture'=>array('id'=>$factureId,
																'printed'=>1,
																'etat'=>'cloturer'
																)
														));
		}
		else {
			$total=$facture['Facture']['montant'];
		}
		$thermal=$this->Conf->find('thermal');
		$header=$this->Conf->find('header');
		$footer=$this->Conf->find('footer');
		$tel=$this->Conf->find('tel');
		$web=$this->Conf->find('web');
		$this->set(compact(
						'ventes',
						'facture',
						'original',
						'thermal',
						'footer',
						'header',
						'tel',
						'web',
						'show_aserb_num'
						));
		$this->Product->company_info();
		$this->layout='printing';
	//*/
	}
	
	function _checkTier($data){
		if(empty($data['tier_id'])){
			die(json_encode(array('success'=>false,
								'msg'=>'Spécifiez le client à qui appartient cette facture'
						)));
		}		
	}
	function _checkJournal(&$factureInfo){
		$journal=array();
		if(in_array($this->Auth->user('fonction_id'),array(2))){
			$journal=$this->Product->journal();
			
			if($factureInfo['Facture']['journal_id']!=$journal['id']){
				$factureInfo['Facture']['journal_id']=$journal['id'];
				//$factureInfo['Facture']['date']=$journal['date'];
			}
		}
		else if(empty($factureInfo['Facture']['journal_id'])){
				die(json_encode(array('success'=>false,
								'msg'=>'Impossible de classer cette facture. Seul un caissier peut le faire!'
						)));
		}
		return $journal;
	}
	function _checkMaxDette($tierInfo,$reste,$data){
		//make sure we have a client/tier first.
		$this->_checkTier($data); 

		if($tierInfo['max_dette']>0){
			$sum=$this->Vente->Facture->find('all',array('fields'=>array('sum(Facture.reste) as reste'),
													'conditions'=>array('Facture.tier_id'=>$data['tier_id'])
													));
			$totalCredit=(isset($sum[0]['Facture']['reste'])) ? $sum[0]['Facture']['reste']+$reste:0;		

			if($totalCredit>$tierInfo['max_dette']){
				die(json_encode(array('success'=>false,
									'msg'=>'La Dette maximale de '.$tierInfo['max_dette'].' est dépassée!'
						)));
			}
		}
	}
	function _createPaiement($data,$amountPaid,$journal,$factureInfo){
		if($amountPaid){
			//in case the paiement box is not shown like in touchscreen mode
			if(!isset($data['Paiement'])){
				$data['Paiement']['mode_paiement']='cash';
			}
			else {
				if($data['Paiement']['montant_equivalent']<=0){
					$data['Paiement']['montant_equivalent']=NULL;
					$data['Paiement']['monnaie']=NULL;
				}
				if($data['Paiement']['monnaie']==Configure::read('aser.default_currency')){
					die(json_encode(array('success'=>false,
									'msg'=>'Le montant equivalent ne peut pas etre en '.Configure::read('aser.default_currency')
						)));
				}
			}
			$data['Paiement']['montant']=$amountPaid;
			//*
			//journal for paiement 
			if(in_array($this->Auth->user('fonction_id'),array(2,4))){
				$data['Paiement']['journal_id']=$journal['id'];
				$data['Paiement']['date']=$journal['date'];
			}
			else {
				// dans le cas ou celui qui classe la facture n'est ni le caissier ou le receptioniste on bestion de la date de la facture et de l'id du journal du caissier d'origine
				$data['Paiement']['journal_id']=$factureInfo['Facture']['journal_id'];
				$data['Paiement']['date']=$factureInfo['Facture']['date'];
			}
			//*/
			
			// saving the paiement
			$data['Paiement']['facture_id']=$data['Vente']['factureId'];

			if(!$this->Vente->Facture->Paiement->save($data))
				exit(json_encode(array('success'=>false,'msg'=>"Le paiement a echoué! vérifié si la facture n'a pas d'autre paiements.")));
		}
		else exit(json_encode(array('success'=>false,'msg'=>"Montant de Paiement incorrecte: $amountPaid")));
	}
	function _reduction($data,&$factureInfo){
		$tierInfo=$this->_tierInfo($data['tier_id']);
		$factureInfo['Facture']['reduction']=($data['reduction']==-1)?$tierInfo['reduction']:$data['reduction'];
		$factureInfo['Facture']=$this->Vente->Facture->reduction($factureInfo['Facture']);
		return $tierInfo;
	}

	function _closeAndSaveTheBill(&$factureInfo,$saveTheBill=true){
		//etat and reste for some cases like payee/avance is not saved at this stage but after the payement is actually done.
		if($saveTheBill) {
			if(!$this->Vente->Facture->save($factureInfo)) exit(json_encode(array('success'=>false,'msg'=>'Bill failed to save!')));
			
		}
		else  {
			$factureInfo=$this->Vente->Facture->find('first',array('fields'=>array('Facture.id','Facture.original',
																			'Facture.date','Facture.journal_id','Facture.montant',
																			'Facture.reste','Facture.etat','Facture.reduction'
																			),
													'conditions'=>array('Facture.id'=>$factureInfo['Facture']['id'])
													));
		}
			//trace stuff
		$trace['Trace']['model_id']=$factureInfo['Facture']['id'];
		$trace['Trace']['model']='Facture';
		$trace['Trace']['operation']='Changement l\'état de "en_cours" à "'.$factureInfo['Facture']['etat'].'"';
		$this->Vente->Facture->Trace->save($trace);

		exit(json_encode(array('success'=>true)+$factureInfo['Facture']));		
	}

	function paiement(){
		$this->autoRender=false; //no view or layout.
		//get the bill correct info 
		$factureInfo=$this->Vente->Facture->find('first',array('fields'=>array('Facture.id','Facture.original',
																			'Facture.date','Facture.journal_id','Facture.tva'
																			),
													'conditions'=>array('Facture.id'=>$this->data['Vente']['factureId'])
													));
		//setting the customer.
		$factureInfo['Facture']['tier_id']=(!empty($this->data['Vente']['tier_id']))?$this->data['Vente']['tier_id']:null;

		$journal=$this->_checkJournal($factureInfo);
		
		// calculating reduction
		$tierInfo=$this->_reduction($this->data['Vente'],$factureInfo);
		// marking the facture as classee
		$factureInfo['Facture']['classee']=1;

		//starting treatement based on status.
		if(in_array($this->data['Vente']['etat'],array('payee','avance'))){
			$amountPaid=$factureInfo['Facture']['montant'];
			if($this->data['Vente']['etat']=='avance'){
				$amountPaid=($factureInfo['Facture']['montant']>$this->data['Vente']['avance'])?$this->data['Vente']['avance']:$factureInfo['Facture']['montant'];
				$reste=$factureInfo['Facture']['montant']-$amountPaid;
				$this->_checkMaxDette($tierInfo,$reste,$this->data['Vente']);
			}
			//saving to modification made to the bill like the reduction/discount.
			if(!$this->Vente->Facture->save($factureInfo)) exit(json_encode(array('success'=>false,'msg'=>'Bill failed to save!')));

			$this->_createPaiement($this->data,$amountPaid,$journal,$factureInfo);

			$this->_closeAndSaveTheBill($factureInfo,false);
		}
		elseif(in_array($this->data['Vente']['etat'],array('credit','bonus'))) {
			//make sure we have a client/tier first.
			$this->_checkTier($this->data['Vente']); 
			if($this->data['Vente']['etat']=='credit'){
				$this->_checkMaxDette($tierInfo,$factureInfo['Facture']['reste'],$this->data['Vente']);
			}
			else {
				$factureInfo['Facture']['reste']=0;
			}
			//putting the state of the bill
			$factureInfo['Facture']['etat']=$this->data['Vente']['etat'];
			$this->_closeAndSaveTheBill($factureInfo);
		}
		
	}

	/*
	function old_paiement(){
	//	exit(debug($this->data));
		//gettin tier info if any
		$tierInfo=$this->_tierInfo($this->data['Vente']['tier_id']);
		
		//reduction treatment
		$facture['Facture']['reduction']=($this->data['Vente']['reduction']==-1)?$tierInfo['reduction']:$this->data['Vente']['reduction'];
		//recalcute the total of the bill.
		$totals=$this->Product->bill_total($this->data['Vente']['factureId'],$facture['Facture']['reduction']);
		$facture['Facture']['original']=$totals['original'];
		$facture['Facture']['montant']=$totals['montant'];

		$facture['Facture']['avance']=($facture['Facture']['montant']>$this->data['Vente']['avance'])?$this->data['Vente']['avance']:$facture['Facture']['montant'];
		
		//determining the reste
		$facture['Facture']['etat']=$this->data['Vente']['etat'];
		if(in_array($facture['Facture']['etat'],array('payee','bonus'))){
			$facture['Facture']['reste']=0;
		}
		elseif($facture['Facture']['etat']=='credit'){
			$facture['Facture']['reste']=$facture['Facture']['montant'];
		}
		elseif($this->data['Vente']['etat']=='avance'){
			$facture['Facture']['reste']=$facture['Facture']['montant']-$facture['Facture']['avance'];
		}
	
		//make sure the client is set if the bill is not fully paid
		if(in_array($facture['Facture']['etat'],array('credit','avance'))&&empty($this->data['Vente']['tier_id'])){
			die(json_encode(array('success'=>false,
								'msg'=>'Spécifié le client. Car la facture n\'est pas entièrement payée!'
						)));
		}		
		//setting the facture array
		$facture['Facture']['id']=$this->data['Vente']['factureId'];
		$facture['Facture']['tier_id']=$this->data['Vente']['tier_id'];
		$facture['Facture']['classee']=1;

		//search for journal data si cet une caissier qui classe la facture
		
		if(in_array($this->Auth->user('fonction_id'),array(2))){
			$journal=$this->Product->journal();
			$oldJournal=$this->Vente->Facture->Journal->find('first',array('fields'=>array('Journal.personnel_id'),
															'conditions'=>array('Journal.id'=>$this->data['Vente']['journal_id']),
															'recursive'=>-1
																));
			if($oldJournal['Journal']['personnel_id']!=$journal['personnel_id']){
				$facture['Facture']['journal_id']=$journal['id'];
				$facture['Facture']['date']=$journal['date'];
			}
		}
		else if(empty($this->data['Vente']['journal_id'])||($this->data['Vente']['journal_id']=='null')){
				die(json_encode(array('success'=>false,
								'msg'=>'Impossible de classer cette facture. Seul un caissier peut le faire!'
						)));
		}
		
		

		//needed on bills for touchscreen stuff
		if(isset($this->data['Vente']['cash'])){
			$facture['Facture']['cash']=$this->data['Vente']['cash'];
		}
		
		//tva stuff
		$facture['Facture']['tva']=$this->Product->tva($facture['Facture']['montant']);

		//max dette checking
		if((in_array($facture['Facture']['etat'],array('credit','avance')))&&($tierInfo['max_dette']>0)){
			$sum=$this->Vente->Facture->find('all',array('fields'=>array('sum(Facture.reste) as reste'),
													'conditions'=>array('Facture.tier_id'=>$facture['Facture']['tier_id'])
													));
			$totalCredit=(isset($sum[0]['Facture']['reste']))?$sum[0]['Facture']['reste']+$facture['Facture']['reste']:0;										
			if($totalCredit>$tierInfo['max_dette']){
				die(json_encode(array('success'=>false,
									'msg'=>'La Dette maximale de '.$tierInfo['max_dette'].' est dépassée!'
						)));
			}
		}
										
		// Paiement stuff
		if(($facture['Facture']['avance']>0)&&in_array($facture['Facture']['etat'],array('payee','avance','excedent'))){
			if(!isset($this->data['Paiement'])){
				$this->data['Paiement']['mode_paiement']='cash';
			}//else in case the paiement box is not shown like in touchscreen mode
			else {
				if($this->data['Paiement']['montant_equivalent']<=0){
					$this->data['Paiement']['montant_equivalent']=NULL;
					$this->data['Paiement']['monnaie']=NULL;
				}
				if($this->data['Paiement']['monnaie']==Configure::read('aser.default_currency')){
					die(json_encode(array('success'=>false,
									'msg'=>'Le montant equivalent ne peut pas etre en '.Configure::read('aser.default_currency')
						)));
				}
			}
			$data['Paiement']=$this->data['Paiement'];
			$data['Paiement']['montant']=$facture['Facture']['avance'];
			//*
			//journal for paiement 
			if(in_array($this->Auth->user('fonction_id'),array(2,4))){
				$data['Paiement']['journal_id']=$journal['id'];
				$data['Paiement']['date']=$journal['date'];
			}
			else {
				// dans le cas ou celui qui classe la facture n'est ni le caissier ou le receptioniste on bestion de la date de la facture et de l'id du journal du caissier d'origine
				$factureInfo=$this->Vente->Facture->find('first',array('fields'=>array('Facture.date','Facture.journal_id'),
																'conditions'=>array('Facture.id'=>$this->data['Vente']['factureId']),
																'recursive'=>-1
																));
				$data['Paiement']['journal_id']=$factureInfo['Facture']['journal_id'];
				$data['Paiement']['date']=$factureInfo['Facture']['date'];
			}
			
			
			//checking if the paiement do not exceed the remaining amount to be paid
			//first we look for any already made paiement.
			$alreadyMadePyts=$this->Vente->Facture->Paiement->find('all',array('fields'=>array('sum(Paiement.montant) as montant'),
															'conditions'=>array('Paiement.facture_id'=>$this->data['Vente']['factureId'])	
															));
			$currentPyts=(isset($alreadyMadePyts[0]))? $alreadyMadePyts[0]['Paiement']['montant']:0;
			$currentReste=$facture['Facture']['montant']-$currentPyts;
			if($data['Paiement']['montant']>$currentReste){
				exit(json_encode(array('success'=>false,'msg'=>'Deja payee '.$currentPyts)));
			}
			// saving the paiement
			$data['Paiement']['facture_id']=$this->data['Vente']['factureId'];
			$this->Vente->Facture->Paiement->save($data);
		}
		//saving the bill
		$this->Vente->Facture->save($facture);
		
		//trace stuff
		$trace['Trace']['model_id']=$facture['Facture']['id'];
		$trace['Trace']['model']='Facture';
		$trace['Trace']['operation']='Changement l\'état de "en_cours" à "'.$facture['Facture']['etat'].'"';
		$this->Vente->Facture->Trace->save($trace);

		exit(json_encode(array('success'=>true,
							'TierId'=>$this->data['Vente']['tier_id'],
							'total'=>$facture['Facture']['montant'],
							'original'=>$facture['Facture']['original'],
							'reduction'=>$facture['Facture']['reduction'],
							'reste'=>$facture['Facture']['reste'],
							'etat'=>$facture['Facture']['etat']
							)
				));
	}
	*/
	function removal($factureId,$consoId,$quantite,$reduction,$obs=''){
		$stockFailureMsg="Erreur de stock";
		if($consoId=='facture'){
			$ventes=$this->Vente->find('all',array('fields'=>array('Vente.historique_id','Vente.acc'),
													'conditions'=>array('Vente.facture_id'=>$factureId)
													)
												);
			//product stuff
			foreach($ventes as $vente){
				if(Configure::read('aser.connexion')){
					if(!empty($vente['Vente']['historique_id'])){
						if(!$this->Product->productHistoryDelete($vente['Vente']['historique_id'],'Historique'))
							exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));

						if(!$this->Product->productHistoryDelete($vente['Vente']['acc'],'Vente'))
							exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));
					}
				}
			}
			//annulation de la facture 
			if(!$this->Vente->Facture->save(array('Facture'=>array('id'=>$factureId,
																'observation'=>$obs,
																'classee'=>1,
																'etat'=>'annulee'
																)
										)))
			{ 
				exit(json_encode(array('success'=>false,'msg'=>'Problème avec l\'enregistrement de la facture!')));
			}

			//trace stuff
			$trace['Trace']['model_id']=$factureId;
			$trace['Trace']['model']='Facture';
			$trace['Trace']['operation']='Annulation de la facture. Motif : "'.$obs.'"';
			$this->Vente->Facture->Trace->save($trace);
			
			exit(json_encode(array('success'=>true,
								)
						)
			);
		}
		elseif($consoId!=0) {
			$fields=array('Vente.*',
						'Facture.date'
						);
			$vente=$this->Vente->find('first',array('fields'=>$fields,
													'conditions'=>array('Vente.id'=>$consoId)
													)
												);
			$old_quantite=$vente['Vente']['quantite'];
			if($old_quantite<$quantite){
				exit(json_encode(array('success'=>false,'msg'=>'Impossible quantitée trop élevée !')));
			}
			//useful to track orders deleted after being sent to the kitchen.
			if($vente['Vente']['quantite']<$vente['Vente']['printed']){
				$this->_create_vente_efface($vente,$quantite,$obs);
			}

			$vente['Vente']['quantite']-=$quantite;
			$vente['Vente']['montant']=$vente['Vente']['quantite']*$vente['Vente']['PU'];
			
			//useful to track orders deleted after being sent to the kitchen.
			if($vente['Vente']['quantite']<$vente['Vente']['printed']){
				$old_printed=$vente['Vente']['printed'];
				$vente['Vente']['printed']=$vente['Vente']['quantite'];
				$qte_printed_removed=	$old_printed-$vente['Vente']['printed'];
				$this->_create_vente_efface($vente,$qte_printed_removed,$obs);
			}
	
			if(($old_quantite-$quantite)>0){
				if(Configure::read('aser.connexion')){		
					//stock part
					$return=$this->Product->stock($vente,'credit');
					if(!$return['success'])
						exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));

					//acc
					if(!empty($vente['Vente']['acc'])){
						$acc=$this->Vente->find('first',array('fields'=>$fields,
															'conditions'=>array('Vente.id'=>$vente['Vente']['acc'])
															)
													);
						if(!empty($acc)){
							$return=$this->Product->stock($acc,'credit');
							if(!$return['success'])
								exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));
						}
					}
				}
					//vente part
				if(!$this->Vente->save($vente))
					exit(json_encode(array('success'=>false,'msg'=>"Problème avec l'enregistrement du produit")));
			}
			else {
				if(Configure::read('aser.connexion')){
					if(!empty($vente['Vente']['historique_id'])){
						if(!$this->Product->productHistoryDelete($vente['Vente']['historique_id'],'Historique'))
							exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));

						if(!$this->Product->productHistoryDelete($vente['Vente']['acc'],'Vente'))
							exit(json_encode(array('success'=>false,'msg'=>$stockFailureMsg)));
					}
				}
				$deleteMsg="Problème avec l'effacement du produit.";
				//vente part
				if(!$this->Vente->delete($consoId))
					exit(json_encode(array('success'=>false,'msg'=>$deleteMsg)));		
				//gestion des accompagnents
				if(!is_null($vente['Vente']['acc'])){
					if(!$this->Vente->delete($vente['Vente']['acc']))
						exit(json_encode(array('success'=>false,'msg'=>$deleteMsg)));	
				}
			}
			//$sum=$this->Product->bill_total($factureId, $reduction);
			//fetching the info of the updated bill
			$facture=$this->Vente->Facture->find('first',array('conditions'=>array('Facture.id'=>$factureId),
																		'fields'=>array('Facture.original',
																						'Facture.montant',
																						'Facture.reste',
																						'Facture.avance_beneficiaire as avance'
																				),
																		'recursive'=> -1
																));
			
			exit(json_encode(array('success'=>true,
									'quantite'=>$vente['Vente']['quantite'],
									'PT'=>$vente['Vente']['montant'],
									)
									+
							$facture['Facture']
						)
			);
		}
		else {
				exit(json_encode(array('success'=>false,
										'msg'=>'Cliquez sur la consommation pour la sélectionnée.'
								)
						)
			);
		}
	}
	
	/*le role de cette fonction est d'enregistrer une historique de toutes les ventes/commandes
	effacer apres impression/envoie des commandes a la cuisine.
	*/

	function _create_vente_efface($vente,$qte_enlevee,$obs=''){
		$vente_efface=$vente['Vente'];
		$vente_efface['id']=null;
		$vente_efface['quantite']=$qte_enlevee;
		$vente_efface['personnel_id']=$this->Auth->user('id');
		$vente_efface['montant']=$vente_efface['quantite']*$vente_efface['PU'];
		$vente_efface['date']=date('Y-m-d');
		$vente_efface['observation']=$obs;
		$this->loadModel('VenteEfface');
		$this->VenteEfface->save(array('VenteEfface'=>$vente_efface));
	}
	
	function removed_orders(){
		$date1= (!empty($this->data['Vente']['date1']))?$this->data['Vente']['date1']:date('Y-m').'-01';
		$date2= (!empty($this->data['Vente']['date2']))?$this->data['Vente']['date2']:date('Y-m').'-31';
		$this->loadModel('VenteEfface');
		$vente_effaces = $this->VenteEfface->find('all',array('fields'=>array('VenteEfface.*',
																																				'Facture.id','Facture.numero',
																																				'Produit.name',
																																				'Personnel.name'
																																					),
																													'conditions'=>array('VenteEfface.date >=' => $date1,
																																							'VenteEfface.date <=' => $date2,
																																						),
																													'order'=>array('VenteEfface.date')
																							));
		$this->set(compact('vente_effaces','date2','date1'));
	}

	function list_produits($factureId){
		$ventes_old=$this->Vente->find('all',array('fields'=>array('Produit.name',
																'Produit.id',
																'Vente.PU',
																'Vente.quantite',
																'Vente.montant',
																'Vente.pourcentage',
																'Vente.id',
																'Vente.acc',
																'Vente.printed',
																'Vente.created'
																),
													'conditions'=>array('Vente.facture_id'=>$factureId),
													'order'=>array('Vente.id desc'),
													)
										);
										
		$produits=$this->_lister($ventes_old);
		
		$this->set(compact('produits'));
		$this->layout="ajax";
	}

	function activated($factureId){
		$facture=$this->Vente->Facture->find('first',array('fields'=>array(
																'Facture.*',
																'Tier.id','Tier.name',
																'Personnel.id','Personnel.name',
																),
													'conditions'=>array('Facture.id'=>$factureId),
													)
										);

		$facture['Tier']['name']=(empty($facture['Tier']['name']))?'':$facture['Tier']['name'];
		if(($facture['Facture']['classee']==0)&&Configure::read('aser.beneficiaires')){
			$facture['avance']=$facture['Facture']['avance_beneficiaire'];
		}
		else {
			$facture['avance']=$facture['Facture']['montant']-$facture['Facture']['reste'];
		}
		exit(json_encode($facture));
	}
	
	function chambre($chambre) {
		exit(json_encode(array('success'=>true,'id'=>$this->Product->chambre($chambre))));
  	}
	
	function _tierInfo($id,$returnReductionOnly=false){
		$info['reduction']=$info['max_dette']=0;
		if($id!=null){
			$find=$this->Vente->Facture->Tier->find('first',array('conditions'=>array('Tier.id'=>$id),
														'recursive'=>-1,
														'fields'=>array('Tier.reduction',
																		'Tier.type_reduction',
																		'Tier.max_dette'
																		)
														)
											);
			$info['reduction']=($find['Tier']['type_reduction']=='Sur le total')?($find['Tier']['reduction']):(0);
			$info['max_dette']=$find['Tier']['max_dette'];
		}
		if($returnReductionOnly) return $info['reduction'];
		else return $info;
	}

	function setProposedBillNumber(){
		if(!empty($this->data['Vente']['facture_numero'])){

			$this->_checkBillNumberAvailability($this->data['Vente']['facture_numero']);
			$this->Vente->Facture->id = $this->data['Vente']['facture_id'];
			$this->Vente->Facture->saveField('numero',$this->data['Vente']['facture_numero']);
			
			exit(json_encode(array('success'=>true)));	
		}
		exit(json_encode(array('success'=>false, 'msg'=>'Le Numero est requis!')));
	}


	//check if the proposed bill number is already used
	function _checkBillNumberAvailability($numero = null, $exit_on_failure=true){
		if(Configure::read('aser.manual_bill_numbering')&&!empty($numero)){
			$search = $this->Vente->Facture->find('first',array('fields'=>array("Facture.numero"),
																							'conditions'=>array('Facture.numero'=>$numero)
																	));
			if(!empty($search['Facture']['numero'])){
				if($exit_on_failure){
					exit(json_encode(array('success'=>false,
				'msg'=>'Le numéro de facture proposé : '.$numero.' est déjà pris! Veuillez choisir un autre.')));	
				}
				else {
					return false;
				}
			}
		}
	}
	
	function _addCheckings(&$data){
		
		//stop if the proposed bill number is already used
		if($data['Vente']['factureId']=='creation'){
			$this->_checkBillNumberAvailability($data['Vente']['facture_numero']);
		}
		//stop add users from creating bills except caissiers and serveurs.
		if(!in_array($this->Auth->user('fonction_id'),array(1,2))&&($data['Vente']['factureId']=='creation')){
			exit(json_encode(array('success'=>false,
			'msg'=>'Seul les caissiers et les serveurs peuvent créer une facture!')));	
		}
		//preventing any wrong call to add without the right parameters.
		if(!isset($data['Vente']['factureId'])){
			exit(json_encode(array('success'=>false,
			'msg'=>'N° de la facture non spécifié!')));
			
		}
		//checking if the table specified is correct.
		if(($data['Vente']['factureId']=='creation')&&(!empty($data['Vente']['table']))){
			if(!Configure::read('aser.touchscreen'))
				$this->table_state($data['Vente']['table'],false);
		}
		//checking if the stock to use has been provided.
		$data['Vente']['stock_id']=$stockId=$this->Session->read('resto_stock');
		if(empty($data['Vente']['stock_id'])){
			exit(json_encode(array('success'=>false,'msg'=>'Stock non spécifié!')));
		}
	}
	function _addJournalHandler($nembeteplus,$data){
		
		$journal['date']=date('Y-m-d'); //default initialization.
		//handle this only when creating a new bill.
		if($data['Vente']['factureId']=='creation'){
			//if it is a cassier creating the bill.
			if($this->Auth->user('fonction_id')==2){
				//the nembeteplus variable is true if the user has chosen to use an old journal/report.
				//exit(debug($nembeteplus));
				if($nembeteplus==1){
					$this->Session->write('nembeteplus',1);
					exit(json_encode(array('success'=>true,
					'msg'=>'ok!')));
				}
				//fetching the journal/report to use.
				$journal=$this->Product->journal();
				$nembeteplus=$this->Session->read('nembeteplus');
				//warning the user that he is about to use an old journal. 
				if(($journal['date']!=date('Y-m-d'))&&(!$nembeteplus)){
					$msg='Votre Journal a une date du ';
					$msg.=$this->Product->tofrench($journal['date']).'. ';
					$msg.="Si vous avez oublié de le clôturer, veuillez le faire avant de poursuivre.";
					$ans=array('success'=>false,'choix'=>true,'msg'=>$msg);
					if(Configure::read('aser.disable_nembeteplus')) $ans['disable_nembeteplus']=true; 
					exit(json_encode($ans));
				}
			}
			//if it is a serveur creating the bill.	
			else {
				$journal['id']=null;
			}
		}
		return $journal;
	}

	
	function add($nembeteplus=0){
		//exit(debug($this->data));
		//journal/report handling
		$journal=$this->_addJournalHandler($nembeteplus,$this->data);

		//basic checkings.
		$this->_addCheckings($this->data);

		//initializing some variables.
		$failToSaveMsg="Fail to save this vente.";
		//find the Point of sale we are working on.
		$pos=(!empty($pos))?($pos):null;
		//date for beneficiaire
		$this->data['Vente']['date']=(Configure::read('aser.beneficiaires'))?$journal['date']:null;
		//setting up the reduction in vente array. this will force the bill when updated 
		//on aftersave callback to recalculate the reduction
		$this->data['Vente']['reduction']=($this->data['Vente']['factureId']=='creation')? 
										$this->_tierInfo($this->data['Vente']['tier_id'],true):
										$this->data['Vente']['reduction'];
		//getting the PV to use
		$produitInfo=$this->Vente->Produit->find('first',array('fields'=>array(
																			'Produit.type',
																			'Produit.PV',
																			'Produit.PA',
																			'Produit.id'
																			),
																	'conditions'=>array('Produit.id'=>$this->data['Vente']['produit_id'])
																	)
													);
		if(empty($this->data['Vente']['PU'])){	
			if(Configure::read('aser.gestion_reduction')){
					$this->loadModel('Reduction');
					$search = $this->Reduction->find('first',array('conditions'=>array('Reduction.tier_id'=>$this->data['Vente']['tier_id'],
																																			'Reduction.produit_id'=>$this->data['Vente']['produit_id'],
																																			),
																									'recursive'=>-1
																									));
				$this->data['Vente']['PU']=(!empty($search))?$search['Reduction']['PU']:null;	
			}	
			if(empty($this->data['Vente']['PU'])){									
				$this->data['Vente']['PU']=$this->Product->productPrice($produitInfo['Produit']['id'],$produitInfo['Produit']['PV'],$pos);
			}
			//	exit(debug($this->data['Vente']['PU']));
		}
		$montant=$this->data['Vente']['montant']=$this->data['Vente']['PU']*$this->data['Vente']['quantite'];
		$this->data['Vente']['PA']=$produitInfo['Produit']['PA']; //saving the current PA.helpful when calculating the benefice per item
		
		//look for a previous vente to add up to. or create a new if none else with same details (produit_id, PU) is found.
		$found=false;
		if(($this->data['Vente']['factureId']!='creation')&&(empty($this->data['Vente']['acc_id'])||($this->data['Vente']['acc_id']=='null'))){
			$factureId=$this->data['Vente']['factureId'];
			$conditions['Vente.facture_id']=$factureId;
			$conditions['Vente.produit_id']=$this->data['Vente']['produit_id'];
			if(!empty($this->data['Vente']['acc_id'])){
				$conditions['Vente.acc_id']=$this->data['Vente']['acc_id'];
			}
			else {
				$conditions['Vente.acc']=null;
			}
		
			$search=$this->Vente->find('first',array('fields'=>array('Vente.id',
																	'Vente.quantite',
																	'Vente.printed',
																	'Vente.produit_id',
																	'Vente.montant',
																	'Vente.PU',
																	'Vente.pourcentage',
																	'Vente.historique_id',
																	'Facture.date'
																	),
														'conditions'=>$conditions,
														)
										);
										
			if(!empty($search)){
				$found=true;
				$printed=$search['Vente']['printed'];
				//j'essaie d'empecher qu'on ajoute un produit avec un PU different du premier
				if($this->data['Vente']['PU']!=$search['Vente']['PU']){
					exit(json_encode(array('success'=>false,'msg'=>'Le PU doit être le même que celui d\'avant!')));
				}
				// stop different pourcentage for the same product
				if(isset($this->data['Vente']['pourcentage'])&&($this->data['Vente']['pourcentage']!=$search['Vente']['pourcentage'])){
					exit(json_encode(array('success'=>false,'msg'=>'Le pourcentage doit être le même que celui d\'avant!')));
				}
				$this->data['Vente']['id']=$consoId=$search['Vente']['id'];
				$this->data['Vente']['quantite']+=$search['Vente']['quantite'];
				$this->data['Vente']['montant']+=$search['Vente']['montant'];
				//info only need the stock function.
				if(Configure::read('aser.connexion')){
					$this->data['Vente']['historique_id']=$search['Vente']['historique_id'];
					$this->data['Facture']['date']=$search['Facture']['date'];
				}
			}
														
		}
	//	*/
		//if no similar vente is found.
		if(!$found){
			$consoId=null;
			$printed=0;
			//info only need the stock function.
			if(Configure::read('aser.connexion')) $this->data['Facture']['date']=$journal['date'];
		}

		//decreasing now the stock
		if(Configure::read('aser.connexion')){
			//exit(debug($this->data));
			$return=$this->Product->stock($this->data,'credit',$produitInfo);
			if(!$return['success']) exit(json_encode($return));
		}

		//facture stuff
		$factureNum=null;
		if($this->data['Vente']['factureId']=='creation'){
			$facture['Facture']['journal_id']=$journal['id'];
			$facture['Facture']['etat']='en_cours';
			$facture['Facture']['tva_incluse']=1;
			$facture['Facture']['date']=$journal['date'];
			$facture['Facture']['tier_id']=($this->data['Vente']['tier_id']=='null')?NULL:$this->data['Vente']['tier_id'];
			$facture['Facture']['reduction']=$this->data['Vente']['reduction'];
			$facture['Facture']['monnaie']=Configure::read('aser.default_currency');
			$facture['Facture']['operation']='Vente';
			$facture['Facture']['pos']=$pos;
			if(!Configure::read('aser.magasin')){
				$facture['Facture']['table']=$this->data['Vente']['table'];
				$facture['Facture']['personnel_id']=$this->data['Vente']['personnel_id'];
			}
			if(Configure::read('aser.beneficiaires')&&!Configure::read('aser.touchscreen')){
				$facture['Facture']['beneficiaire']=$this->data['Vente']['beneficiaire'];
				if(Configure::read('aser.detailed_ben')){
					$facture['Facture']['matricule']=$this->data['Vente']['matricule'];
					$facture['Facture']['liasse']=$this->data['Vente']['liasse'];
					$facture['Facture']['employeur']=$this->data['Vente']['employeur'];
				}
			}
			if(!$this->Vente->Facture->save($facture)) exit(json_encode(array('success'=>false,'msg'=>"Vente Add F(x) : Fail to create the bill.")));
			$factureId=$this->Vente->Facture->id;
			//determining the facture's display number
			if(Configure::read('aser.manual_bill_numbering')&&!empty($this->data['Vente']['facture_numero'])) {
				$facture['Facture']['numero'] = $factureNum = $this->data['Vente']['facture_numero'];
				$facture['Facture']['id'] =$factureId;
				$this->Vente->Facture->save($facture);
			}
			else {
				$factureNum = $this->Product->facture_number($factureId,'Vente');
			}

			
			//trace stuff
			$trace['Trace']['model_id']=$factureId;
			$trace['Trace']['model']='Facture';
			$trace['Trace']['operation']='Création de la facture avec l\'état : en_cours';
			$this->Vente->Facture->Trace->save($trace);
		}
		else {
			$factureId=$this->data['Vente']['factureId'];
		}	
		$this->data['Vente']['facture_id']=$factureId;
	
		//gestion des accompagnements
		if(!$found&&!empty($this->data['Vente']['acc_id'])&&($this->data['Vente']['acc_id']!='null')){
			unset($this->Vente->id);
			$acc=$this->data;
			$acc['Vente']['produit_id']=$this->data['Vente']['acc_id'];
			$acc['Vente']['PU']=0;
			$acc['Vente']['montant']=0;
			$acc['Vente']['id']=null;
			if(!$this->Vente->save($acc)) exit(json_encode(array('success'=>false,'msg'=>$failToSaveMsg)));
			$this->data['Vente']['acc']=$this->Vente->id;
			if(Configure::read('aser.connexion')){
				$this->Product->stock($acc,'credit');
			}
		}
		$this->data['Vente']['id']=$consoId; //quand on ajoute to an existing vente
		if(!$this->Vente->save($this->data)) exit(json_encode(array('success'=>false,'msg'=>$failToSaveMsg)));

		$consoId=$this->Vente->id; //quand on cree un nouveau
		

		//fetching the bill with updated totals for display.
		$facture=$this->Vente->Facture->find('first',array('fields'=>array('Facture.original','Facture.montant','Facture.reduction',
																		'Facture.reste','Facture.avance_beneficiaire'
																			),
															'conditions'=>array('Facture.id'=>$factureId)
															));
		//json response array
		$response=array('success'=>true,
						'factureId'=>$factureId,
						'factureNum'=>$factureNum,
						'PU'=>$this->data['Vente']['PU'],
						'PT'=>$montant,
						'montant'=>$facture['Facture']['montant'],
						'reste'=>$facture['Facture']['reste'],
						'date'=>$this->Product->formatDate($journal['date']),
						'consoId'=>$consoId,
						'original'=>$facture['Facture']['original'],
						'reduction'=>$facture['Facture']['reduction'],
						'avance'=>$facture['Facture']['avance_beneficiaire'],
						'printed'=>$printed,
						);
		if($this->data['Vente']['factureId']=='creation'){
			$response['journal']=$journal['id'];
		}
		exit(json_encode($response));
	}

	

}
