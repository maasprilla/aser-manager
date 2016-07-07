<?php
class ProduitsController extends AppController {

	var $name = 'Produits';
	var $failureMsg='Vérifiez s\'il les champs Section, Groupe et Nom sont remplis!';


//* correct tvas
	/*
	function stuff(){
		$this->loadModel("Facture");
		$factures = $this->Facture->find('all',array('fields'=>array('Facture.tva','Facture.montant','Facture.id','Facture.date','Facture.tva_incluse'),
																					'conditions'=>array('Facture.date >='=>'2015-01-01',
																															'Facture.tva != round((Facture.montant)*18/118)',
																															'Facture.tva_incluse'=>1
																															)
																			));
		exit(debug($factures));
		foreach($factures as $facture){
			$facture['Facture']['tva']=round(($facture['Facture']['montant'])*18/118);
			$this->Facture->save($facture);
		}
	}
//*/


//* trim whitespace in product names
	/*
	function stuff(){
		$produits = $this->Produit->find('all',array('fields'=>array('Produit.name','Produit.id'),
																			));
		foreach($produits as $produit){
			$produit['Produit']['name']=trim($produit['Produit']['name']);
			$this->Produit->save($produit);
		}
	}
//*/


	/*
	//correct bills whose total is different from the sum total all its vente.
	function stuff($date1,$date2){
		$this->loadModel('Vente');
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$ventes = $this->Vente->find('all',array('fields'=>array('Facture.original','Facture.montant',
																'sum(Vente.montant) as montant',
																'Facture.id','Facture.reduction','Facture.etat'
																),
													'conditions'=>array('Facture.etat'=>$this->etat_classes,
																		'Facture.date >='=>$date1,
																		'Facture.date <='=>$date2
																		),
													'group'=>array('Vente.facture_id')
													));
		//exit(debug($factures));
		foreach($ventes as $vente){
		    if($vente['Facture']['original']!=$vente['Vente']['montant']){
		    	exit(debug($vente));
				$this->Product->bill_total($vente['Facture']['id'],$vente['Facture']['reduction'],true,$vente['Facture']['etat']);
		    }
		}
		exit('done');
	}
	//*/

	/*
	//this function's job is to remove older bills from the system.

	function stuff(){
		$userId=$this->Auth->user('id');
		if(false) exit('Seul armand peut executer cette fonction!');

		$this->loadModel('Vente');
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$date='2015-01-01';

		$this->Vente->Facture->deleteAll(array('Facture.date <'=>$date));
		$this->Vente->deleteAll(array('Facture.date <'=>$date));
		$this->Vente->Facture->Paiement->deleteAll(array('Facture.date <'=>$date));

		exit("effacement done!");
	}
	//*/

	/*
	//correct exceeding payments. if something went wrong and payments do not match with the bills total.
	//this function will repair it by creating the adjustement needed.

	function stuff(){
		$this->loadModel('Paiement');
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$paiements = $this->Paiement->find('all',array('fields'=>array('Facture.montant','Facture.tier_id',
																	'sum(Paiement.montant) as montant','Facture.etat',
																	'Facture.date','Facture.journal_id','Facture.id'
																	),
													'conditions'=>array('Facture.tier_id'=>array(0,NULL),
																		'Facture.etat'=>array('avance','excedent'),
																		),
													'group'=>array('Paiement.facture_id')
													));
		exit(debug($paiements));
		foreach($paiements as $paiement){
				
		    if($paiement['Facture']['montant']!=$paiement['Paiement']['montant']){
					$diff=$paiement['Facture']['montant']-$paiement['Paiement']['montant'];
					//create a new payment for adjustement.
					$newPyt['id']=NULL;
					$newPyt['montant']=$diff;
					$newPyt['montant_equivalent']=NULL;
					$newPyt['mode_paiement']=($diff>0)?'cash':'remboursement';
					$newPyt['journal_id']=$paiement['Facture']['journal_id'];
					$newPyt['facture_id']=$paiement['Facture']['id'];
					$newPyt['reference']='ajustement';
					$newPyt['date']=$paiement['Facture']['date'];

					if(!$this->Paiement->save(array('Paiement'=>$newPyt))){
						exit(debug($newPyt));
					}
					$paiement['Facture']['etat']='payee';
					if(!$this->Paiement->Facture->save($paiement)){
						exit(debug($paiement));
					}
		    }
		}
	}
	//*/
	
	/* put bills into a given journal when provided a journal id.
	function stuff(){
		$this->loadModel('Facture');
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Facture->find('all',array('fields'=>array('Facture.id','Facture.date','Facture.numero','Facture.operation'),
													'conditions'=>array('Facture.date'=>'2015-02-01',
																							'Facture.operation'=>'Vente'
																		//'Facture.journal_id'=>null
																		),
													'order'=>array('Facture.numero asc')
													));
		//exit(debug($factures));
		foreach($factures as $facture){
			$facture['Facture']['journal_id']=3113;
			if(!$this->Facture->save($facture)){
				exit(debug($facture));			
			}
		}

		$paiements = $this->Paiement->find('all',array('fields'=>array('Paiement.id','Paiement.date','Paiement.numero','Facture.operation'),
													'conditions'=>array('Paiement.date'=>'2015-02-01',
																							'Facture.operation'=>'Vente'
																		//'Paiement.journal_id'=>null
																		),
													));
		//exit(debug($paiements));
		foreach($paiements as $paiement){
			$paiement['Paiement']['journal_id']=3113;
			if(!$this->Paiement->save($paiement)){
				exit(debug($paiement));			
			}
		}
	}
//*/
	
	/*
	//reordering all bills of a given year and given operation/model.
	function stuff(){
		$this->loadModel('Facture');
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Facture->find('all',array('fields'=>array('Facture.id','Facture.date','Facture.numero','Facture.operation'),
													'conditions'=>array('Facture.date >='=>'2015-01-01',
																		'Facture.operation'=>'Location'
																		),
													'order'=>array('Facture.numero asc')
													));
		exit(debug($factures));
		$no=1;
		foreach($factures as $facture){
			$facture['Facture']['numero']=$no;
			if(!$this->Facture->save($facture)){
				exit(debug($facture));			
			}
			$no++;
		}
	}
//*/

	/*
	//setting the quantite field of all product to zero.
	function stuff(){
				$this->loadModel('Produit');
set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Produit->find('all',array('fields'=>array('Produit.id','Produit.quantite'),
													'conditions'=>array('Produit.quantite'=>null)
													));
			exit(debug($factures));
		foreach($factures as $facture){
			$facture['Produit']['quantite']=0;
			if(!$this->Produit->save($facture)){
				exit(debug($facture));			
			}
		}
	}
//*/
	/*
	//changing all "Approvisionement" in historique libelle field to Entree
	function stuff(){
				$this->loadModel('Historique');
set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Historique->find('all',array('fields'=>array('Historique.id','Historique.libelle'),
													'conditions'=>array('Historique.libelle like'=>'%Appro%')
													));
		//	exit(debug($factures));
		foreach($factures as $facture){
			$facture['Historique']['libelle']='Entree';
			if(!$this->Historique->save($facture)){
				exit(debug($facture));			
			}
		}
	}
//*/
	/*
	//changing all recette to vente  in type type field.
	function stuff(){
			$this->loadModel('Type');
set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Type->find('all',array('fields'=>array('Type.id','Type.type'),
													'conditions'=>array('Type.type'=>'recette')
													));
		foreach($factures as $facture){
			$facture['Type']['type']='vente';
			$this->Type->save($facture);
		}
		exit('done');
	}
	//*/
/*
//changing the old Recette model to Vente in facture's operation field.
function stuff(){
		$this->loadModel('Facture');
set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		$factures = $this->Facture->find('all',array('fields'=>array('Facture.id','Facture.operation'),
													'conditions'=>array('Facture.operation'=>'Recette')
													));
		foreach($factures as $facture){
			$facture['Facture']['operation']='Vente';
			$this->Facture->save($facture);
		}
		exit('done');
	}
//*/

	/*
	//updating the produit type field according to the new version of aser.
	function stuff(){
		$produits=$this->Produit->find('all',array('fields'=>array('Produit.name','Produit.id','Produit.type'),
												
													));
		foreach($produits as $produit){
			switch($produit['Produit']['type']){
				case 'simple':
					$produit['Produit']['type']='stockable';
					break;
				case 'figuratif':
					$produit['Produit']['type']='non_stockable';
					break;
				case 'simple,composant':
					$produit['Produit']['type']='stockable';
					break;
				case 'paquet_II':
					$produit['Produit']['type']='composer';
					break;
				case 'default':
					$produit['Produit']['type']='stockable';
					break;
			}
			$this->Produit->save($produit);
		}
	}
	//*/
	//*
	
	/**
	 * this function loops through all products
	 * and foreach product set its total qty to the 
	 * quantity field
	 */
	 function set_total_qty(){
	 	$this->autoRender=false;
	 	$produits = $this->Produit->find('all',array('fields'=>array('Produit.id')
													));
		//exit(debug($produits));											
		foreach($produits as $produit){
			$this->Produit->Historique->solde($produit['Produit']['id']);
	 	}
	 }
	
	/**
	 * this function exposes all the products of the restaurant menu 
	 * in json format for the online food delivery web app to get 
	 * access to it.
	 */
	 
	function expose_menu(){
		$produits=$this->Produit->find('all',array('fields'=>array('Produit.name',
																	'Produit.PV',
																	'Produit.groupe_id',
																	'Groupe.name',
																	'Groupe.id',
																	),
												 'conditions'=>array('Produit.actif'=>'oui'),
											     'order'=>array('Groupe.name','Produit.name')
												 )
											);	
		$this->set(compact('produits'));		
	}
	
	function conso_theorique(){
		$this->loadModel('Ingredient');
		$date1=(!empty($this->data['Produit']['date1']))?$this->data['Produit']['date1']:date('Y-m-d');
		$date2=(!empty($this->data['Produit']['date2']))?$this->data['Produit']['date2']:date('Y-m-d');
		
		$produits=$this->Produit->find('all',array('fields'=>array('Produit.id','Produit.name','Unite.name'),
												'conditions'=>array('Produit.type'=>'stockable')
												));	
		foreach($produits as $key=>$produit){
			$ventes=$this->Produit->Vente->find('all',array('fields'=>array('sum(Vente.quantite) as quantite'),
														'conditions'=>array('Vente.produit_id'=>$produit['Produit']['id'],
																			'Facture.date >='=>$date1,
																			'Facture.date <='=>$date2,
																			)	
															));	
			$produits[$key]['Produit']['quantite']=(isset($ventes[0]))?$ventes[0]['Vente']['quantite']:0;
			$ingredients=$this->Ingredient->find('all',array('fields'=>array('sum(Ingredient.qte) as qte',
																				'Ingredient.produit_id'
																				),
																	'conditions'=>array('Ingredient.ingredient_id'=>$produit['Produit']['id']),
																	'group'=>array('Ingredient.produit_id')
																		));
			foreach($ingredients as $ing){
				$ventes=$this->Produit->Vente->find('all',array('fields'=>array('sum(Vente.quantite) as quantite'),
														'conditions'=>array('Vente.produit_id'=>$ing['Ingredient']['produit_id'],
																			'Facture.date >='=>$date1,
																			'Facture.date <='=>$date2,
																			)	
															));
				$produits[$key]['Produit']['quantite']+=(isset($ventes[0]))?$ventes[0]['Vente']['quantite']*$ing['Ingredient']['qte']:0;
			}
			
		}
		$this->set(compact('produits','date1','date2'));
	}
	
	function shifts(){
		$operations=$ants=$produits=array();
		if(!empty($this->data['Historique']['date1'])){
			$conditions['Historique.date']=$date=$this->data['Historique']['date'];
		}
		else {
			$conditions['Historique.date']=$date=date('Y-m-d');
		}
		if(!empty($this->data['Historique']['stock_id'])){
			$stockIds=$this->data['Historique']['stock_id'];
		}
		else  {
			$stockIds=array_keys($this->stocks);
		}
		$choix='mvt';
		if(!empty($this->data['Historique']['choix'])){
			switch ($this->data['Historique']['choix']) {
				case 'e':
					$conditions['Historique.debit >']=0;
					$conditions['Historique.credit']=null;
					break;
				case 's':
					$conditions['Historique.libelle']=array('Sorti','Vente','Mouvement');
					$conditions['Historique.debit']=null;
					break;
				case 'p':
					$conditions['Historique.libelle']=array('Perte');
					$conditions['Historique.debit']=null;
					break;
				default:
					break;
			}
			$choix=$this->data['Historique']['choix'];
		}
			$stockNames=$this->Produit->Historique->Stock->find('all',array('fields'=>array('Stock.name'),
																		'conditions'=>array('Stock.id'=>$stockIds),
																		'recursive'=>0
																		));
			
			$produits=$this->Produit->find('all',array('fields'=>array('Produit.id',
																		'Produit.name',
																		),
																	'conditions'=>array('Produit.type'=>'stockable'),
																	'order'=>array('Produit.name')
																	));
			foreach($produits as $key=>$produit){
				$total=$debits=$credits=0;
				foreach($stockNames as $s=>$stockName){
					$debit=$credit=$solde=$report=0;
					if(!is_null($date)){
						$ants=$this->Produit->Historique->find('all',array('fields'=>array('sum(Historique.debit) as debit',
																				'sum(Historique.credit) as credit',
						                        								),
						                        				'conditions'=>array('Historique.produit_id'=>$produit['Produit']['id'],
						                        									'Historique.date <'=>$date,
						                        									'Historique.stock_id'=>$stockName['Stock']['id']
																					)
																	));
					$solde=$ants[0]['Historique']['solde']=$ants[0]['Historique']['debit']-$ants[0]['Historique']['credit'];
					$stockNames[$s]['ants']=$ants;
					}
					$conditions['Historique.produit_id']=$produit['Produit']['id'];
					$conditions['Historique.stock_id']=$stockName['Stock']['id'];
					
					$operations=$this->Produit->Historique->find('all',array('conditions'=>$conditions,
																'fields'=>array('sum(Historique.debit) as debit',
																				'sum(Historique.credit) as credit',
																				'Historique.shift',
																					),
																	'order'=>array('Historique.shift asc',
																				),
																	'group'=>array('Historique.shift')
																	));
					foreach($operations as $i=>$operation){
						$debit+=$operation['Historique']['debit'];	
						$credit+=$operation['Historique']['credit'];
						$solde=$operations[$i]['Historique']['solde']=$solde-$operation['Historique']['credit']+$operation['Historique']['debit'];	
					}	
					$total+=$solde;
					$stockNames[$s]['op']=$operations;
					$stockNames[$s]['debit']=$debit;
					$stockNames[$s]['credit']=$credit;
					$stockNames[$s]['solde']=$solde;
					$debits+=$debit;
					$credits+=$credit;
				}
				$produits[$key]['stocks']=$stockNames;
				$produits[$key]['total']=$total;
				$produits[$key]['debits']=$debits;
				$produits[$key]['credits']=$credits;
			//	exit(debug($produits));
			}
		$referer=$this->referer();
		$this->set(compact('produits','stockNames','stockIds','referer','choix','date'));
	}
	function monthly(){
		$stock_id=0;
		if(!empty($this->data)){
			if($this->data['Historique']['stock_id']==0){
				$this->data['Historique']['stock_id']=array_keys($this->stocks);
				$stock='tous';
			}
			else {
				$stock=$this->stocks[$this->data['Historique']['stock_id']];
			}
			$produits=$this->view($id=null,null,$this->data);
			$date1=$this->data['Historique']['date1'];
			$date2=$this->data['Historique']['date2'];
			$days=$this->Product->diff($date1,$date2);
			$choix=$this->data['Historique']['choix'];
//			exit(debug($produits));
			$this->set(compact('days','date1','date2','produits','choix','stock'));	
		}
	}
	
	function inventaire(){
	$userId=$this->Auth->user('id');
		if($userId!=11) exit('Seul armand peut executer cette fonction!');
		 
		$this->loadModel('Historique');
		$ants=$this->Historique->find('all',array('fields'=>array('sum(Historique.debit) as debit',
																'sum(Historique.credit) as credit',
																'Historique.date_expiration',
																'Historique.produit_id',
																'Historique.stock_id',
						                        				),
						                        'group'=>array('Historique.stock_id',
						                        				'Historique.produit_id',
						                        				'Historique.date_expiration'
																),
												));
		foreach($ants as $key=>$ant){
			$qty=$ant['Historique']['debit']-$ant['Historique']['credit'];
			$adjust=$ant;
			$adjust['Historique']['id']=null; //make sure we create a new record
			$adjust['Historique']['date']=date('Y-m-d');
			$adjust['Historique']['libelle']='ADJUSTEMENT INVENTAIRE';

			if($qty>0){
				$adjust['Historique']['debit']=null;
				$adjust['Historique']['credit']=$qty;
			}
			else if($qty<0){
				$adjust['Historique']['debit']=abs($qty);
				$adjust['Historique']['credit']=null;
			}
			$this->Historique->save($adjust);
		}
		$this->Session->setFlash('Remise à zéro effectuée!');
		$this->redirect($this->referer());
	}
	
	function merge(){
		if(empty($this->data)){
			exit(json_encode(array('success'=>false,'msg'=>'Aucun Produit séléctionné!')));
		}

		$this->loadModel('Historique');
		$this->loadModel('Entree');
		$this->loadModel('Sorti');
		$this->loadModel('Perte');
		$this->loadModel('Mouvement');
		$this->loadModel('Vente');
		
		$message='Failed to save : ';
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');
		
		$id=$this->data['rowId'];
			$ids=array();
			foreach($this->data['Id'] as $value){
				if(($value!=0)&&($value!=$id)) {
					$ids[]=$value;
				}
			}
	 	
		
			$names=$this->Produit->find('all',array('fields'=>array('Produit.id'),
													'conditions'=>array('Produit.id'=>$ids)
													));
			foreach($names as $name){
				$historiques=$this->Historique->find('all',array('fields'=>array('Historique.*'),
																'conditions'=>array('Historique.produit_id'=>$name['Produit']['id'])
																));
				foreach($historiques as $historique){
					$historique['Historique']['produit_id']=$id;
				//	exit(debug($historique));
					if(!$this->Historique->save($historique)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Historique')));
					}
				}
				$entrees=$this->Entree->find('all',array('fields'=>array('Entree.*'),
																'conditions'=>array('Entree.produit_id'=>$name['Produit']['id'])
																));
				foreach($entrees as $entree){
					$entree['Entree']['produit_id']=$id;
					if(!$this->Entree->save($entree)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Entree')));
					}
				}
				
				$sortis=$this->Sorti->find('all',array('fields'=>array('Sorti.*'),
																'conditions'=>array('Sorti.produit_id'=>$name['Produit']['id'])
																));
				foreach($sortis as $sorti){
					$sorti['Sorti']['produit_id']=$id;
					if(!$this->Sorti->save($sorti)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Sorti')));
					}
				}
				$pertes=$this->Perte->find('all',array('fields'=>array('Perte.*'),
																'conditions'=>array('Perte.produit_id'=>$name['Produit']['id'])
																));
				foreach($pertes as $perte){
					$perte['Perte']['produit_id']=$id;
					if(!$this->Perte->save($perte)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Perte')));
					}
				}
				$mouvements=$this->Mouvement->find('all',array('fields'=>array('Mouvement.*'),
																'conditions'=>array('Mouvement.produit_id'=>$name['Produit']['id'])
																));
				foreach($mouvements as $mouvement){
					$mouvement['Mouvement']['produit_id']=$id;
					if(!$this->Mouvement->save($mouvement)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Mouvement')));
					}
				}
				$ventes=$this->Vente->find('all',array('fields'=>array('Vente.*'),
																'conditions'=>array('Vente.produit_id'=>$name['Produit']['id'])
																));
				foreach($ventes as $vente){
					$vente['Vente']['produit_id']=$id;
					if(!$this->Vente->save($vente)){
						exit(json_encode(array('success'=>false,'msg'=>$message.' Vente')));
					}
				}
				if($name['Produit']['id']!=$id){
					$this->Produit->delete($name['Produit']['id']);
				}
			}
			if(!empty($this->data['name'])){
				if(!$this->Produit->save(array('Produit'=>array('id'=>$id,'name'=>$this->Product->name($this->data['name'])))))
					exit(json_encode(array('success'=>false,'msg'=>__('Enregistrement en double!'))));
			}
			exit(json_encode(array('success'=>true)));
		}
	//*/
	//*
	function beforeFilter(){
		$bars=array();
		foreach(Configure::read('bars') as $bar=>$barTables)
			$bars[$bar]=$this->Conf->find($bar);
		
		$unites = $this->Produit->Unite->find('list');
		$unites[0]='';
		$choixs=array('mvt'=>'Mouvements',
					'e'=>'Entrées',
					's'=>'Sorties',
					'p'=>'Pertes'
					);
		$this->set(compact('unites','choixs','bars'));
		parent::beforeFilter();	
	}
	//*/
	function balance(){
		$produits=array();
		$out['Vente']=$out['Sorti']=$out['Perte']=0;
		$debit=$credit=$solde=$report=$pa=0;
		$date1=$date2=null;
		$model='Produit';
		$stockId=null;
		$stockInfo=array();
		$conditions=array();
		$cond['Produit.type']='stockable';
		$cond['Produit.actif']='oui';
		
		if(!empty($this->data)){
			if($this->data['Produit']['produit_id'][0]!=0){
				$cond['Produit.id']=$this->data['Produit']['produit_id'];
			}
			
			if($this->data['Produit']['groupe_id']!=0){
					$cond['Produit.groupe_id']=$this->data['Produit']['groupe_id'];
			}
			else if($this->data['Produit']['section_id']!=0) {
				$cond['Produit.groupe_id']=$this->Produit->Groupe->find('list',array('fields'=>array('Groupe.id','Groupe.id'),
																					'conditions'=>
																							array('Groupe.section_id'=>$this->data['Produit']['section_id'])
																					)
																		);
			}
			if($this->data['Produit']['date1']!=''){
				$conditions['Historique.date >=']=$date1=$this->data['Produit']['date1'];
			}
			if($this->data['Produit']['date2']!=''){
				$conditions['Historique.date <=']=$date2=$this->data['Produit']['date2'];
			}
			$conditions['Historique.stock_id']=$stockId=$this->data['Produit']['stock_id'];
			
			$stockInfo=$this->Produit->Historique->Stock->find('first',array('fields'=>array('Stock.name'),
																'conditions'=>array('Stock.id'=>$this->data['Produit']['stock_id']),
																));
																
			$produits=$this->Produit->find('all',array('fields'=>array('Produit.name','Produit.id','Produit.PA'),
																'conditions'=>$cond,
																'order'=>array('Produit.name')
																));
			
			foreach($produits as $key=>$compte){
				if(!is_null($date1)){
					$ants=$this->Produit->Historique->find('all',array('fields'=>array('sum(Historique.debit) as debit',
																			'sum(Historique.credit) as credit',
						                        							),
						                        				'conditions'=>array('Historique.produit_id'=>$compte[$model]['id'],
						                        									'Historique.date <'=>$date1,
						                        									'Historique.stock_id'=>	$this->data['Produit']['stock_id'],
																					)
																	));
					$report+=$produits[$key]['report']=$ants[0]['Historique']['debit']-$ants[0]['Historique']['credit'];
				}
				else {
					$produits[$key]['report']=0;
				}
				$conditions['Historique.produit_id']=$compte[$model]['id'];
	
				$historiques=$this->Produit->Historique->find('all',array('conditions'=>$conditions,
																'fields'=>array('Historique.libelle',
																				'Produit.name',
																				'sum(Historique.debit) as debit',
																				'sum(Historique.credit) as credit',
																				),
																'order'=>array('Historique.date asc'),
																'group'=>array('Historique.produit_id','Historique.libelle')
																));
			if(!empty($historiques)||($produits[$key]['report']!=0)){	
				$produits[$key]['Entree']=$produits[$key]['Vente']=$produits[$key]['Sorti']=$produits[$key]['Perte']=$c=0;								
				foreach($historiques as $historique){
					$produits[$key]['Entree']+=$historique['Historique']['debit'];
					$c+=$historique['Historique']['credit'];
					
					if(in_array($historique['Historique']['libelle'],array('Vente','Sorti','Perte'))){
						if(!is_null($historique['Historique']['credit'])){
							$produits[$key][$historique['Historique']['libelle']]+=$historique['Historique']['credit'];
						}
					}
					if(($historique['Historique']['libelle']=='Mouvement')&&(!is_null($historique['Historique']['credit']))){
						$produits[$key]['Sorti']+=$historique['Historique']['credit'];
						$out['Sorti']+=$historique['Historique']['credit'];
					}
					if(in_array($historique['Historique']['libelle'],array('Vente','Sorti','Perte'))){
						$out[$historique['Historique']['libelle']]+=$historique['Historique']['credit'];
					}
					
				}
			
				$debit+=$produits[$key]['Entree'];
				$credit+=$c;
				$diff=$produits[$key]['Entree']-$c;
				$solde+=$produits[$key]['solde']=$produits[$key]['report']+$diff;
				$produits[$key]['produit']=$compte[$model]['name'];
				$pa+=$produits[$key]['total_pa']=$produits[$key]['solde']*$compte['Produit']['PA'];
			}
			else {
				unset($produits[$key]);
			}
			}
		}	
			$list=$this->Produit->find('list',array('conditions'=>array('Produit.actif'=>'oui',
																		'Produit.type like'=>'%stockable%'
																		),
													'order'=>array('Produit.name')
														));
			$list[0]=' ';
			if(!empty($this->data['Produit']['export'])&&($this->data['Produit']['export']==1)){
				$headers[]='produit';
				$headers[]='report';
				$headers[]='Entree';
				$headers[]='Vente';
				$headers[]='Sorti';
				$headers[]='Perte';
				$headers[]='solde';
				$headers[]='total_pa';
				$filename=$this->Product->excel($produits,$headers,'mvts_des_produits');
				$this->redirect('/files/'.$filename);
			}
			else {
				$this->set(compact('stockId','pa','produits','date1','date2','debit','credit','solde','list','model','report','produit','out','stockInfo'));
			}
	}

	function view($id,$stockIds=null,$data=array()){
		$operations=$ants=$produits=array();
		if(empty($data)){
			$produitCond['Produit.id']=$id;
			$group=array('Historique.produit_id','Historique.date','Historique.shift','Historique.date_expiration','Historique.libelle');
		}
		else {
			$this->data=$data;
			$produitCond['Produit.type']='stockable';
			$group=array('Historique.produit_id','Historique.date');
		}	
		if(!empty($this->data['Historique']['date1'])){
			$conditions['Historique.date >=']=$date1=$this->data['Historique']['date1'];
		}
		else {
			$conditions['Historique.date >=']=$date1=date('Y-m').'-01';
		}
		if(!empty($this->data['Historique']['date2'])){
			$conditions['Historique.date <=']=$date2=$this->data['Historique']['date2'];
		}
		else {
			$conditions['Historique.date <=']=$date2=date('Y-m').'-31';
		}
		if(!empty($this->data['Historique']['stock_id'])){
			$conditions['Historique.stock_id']=$stockIds=$this->data['Historique']['stock_id'];
		}
		else  {
			$stockIds=($stockIds==0)?array_keys($this->stocks):$stockIds;
			$conditions['Historique.stock_id']=$stockIds;
		}
		if(!empty($this->data['Historique']['choix'])){
			switch ($this->data['Historique']['choix']) {
				case 'e':
					$conditions['Historique.debit >']=0;
					$conditions['Historique.credit']=null;
					break;
				case 's':
					$conditions['Historique.libelle']=array('Sorti','Vente','Mouvement');
					$conditions['Historique.debit']=null;
					break;
				case 'p':
					$conditions['Historique.libelle']=array('Perte');
					$conditions['Historique.debit']=null;
					break;
				default:
					break;
			}
		}
			$stockNames=$this->Produit->Historique->Stock->find('all',array('fields'=>array('Stock.name'),
																		'conditions'=>array('Stock.id'=>$stockIds)
																		));
			
		

			$valeur_total=0;
		//	foreach($produits as $key=>$produit){
				$debit=$credit=$solde=$report=0;
				if(!is_null($date1)){
					$ants=$this->Produit->Historique->find('all',array('fields'=>array('sum(Historique.debit) as debit',
																																						'sum(Historique.credit) as credit',
																																						'Produit.id','Produit.name','Produit.unite_id',
																																						'Produit.PV','Produit.PA'
						                        																				),
														                        				'conditions'=>array(
														                        									//'Historique.produit_id'=>$produit['Produit']['id'],
														                        									'Historique.date <'=>$date1,
														                        									'Historique.stock_id'=>$stockIds
														                        									)+$produitCond,
														                        				'group'=>array('Historique.produit_id'),
														                        				'order'=>array('Produit.name','Historique.date','Historique.id')
																					
																								));
					foreach($ants as $i => $ant){
						$produits[$ant['Produit']['id']]['info']=$ant['Produit'];
						$produits[$ant['Produit']['id']]['ant']=$ant;
						$produits[$ant['Produit']['id']]['SI']=$ant['Historique']['debit']-$ant['Historique']['credit'];
					}
					
				}
				///exit(debug($produits));
//				$conditions['Historique.produit_id']=$produit['Produit']['id'];
				$operations=$this->Produit->Historique->find('all',array('conditions'=>$conditions+$produitCond,
																'fields'=>array('sum(Historique.debit) as debit',
																				'sum(Historique.credit) as credit',
																				'Historique.*',
																				'Produit.id','Produit.name',
																				'Produit.PV','Produit.PA','Produit.unite_id'
																					),
																	'order'=>array('Produit.name','Historique.date asc',
																				'Historique.shift',
																				'Historique.libelle asc',
																				'Historique.id asc'
																				),
																	'group'=>$group
																	));

		//	exit(debug($operations));
				//reset variables
				$i=$counter=0;
				$current_product_id=0;
				$debit=$credit=0;
				$solde=0;
				foreach($operations as $i=>$operation){
					//totals for the previous item.
					if(($counter!=0)&&($current_product_id!=$operation['Produit']['id'])){
						$produits[$current_product_id]['debit']=$debit;
						$produits[$current_product_id]['credit']=$credit;
						$produits[$current_product_id]['solde']=$solde;
						$produits[$current_product_id]['valeur']=$produits[$current_product_id]['solde']*$produits[$current_product_id]['info']['PA'];
						$valeur_total += $produits[$current_product_id]['valeur'];
					}
					if($current_product_id!=$operation['Produit']['id']){
						$current_product_id=$operation['Produit']['id'];
						$produits[$current_product_id]['info']=$operation['Produit'];
						$solde=(isset($produits[$current_product_id]['SI']))?$produits[$current_product_id]['SI']:0;
					}
					$debit+=$operation['Historique']['debit'];	
					$credit+=$operation['Historique']['credit'];
					$solde=$operation['Historique']['solde']=$solde-$operation['Historique']['credit']+$operation['Historique']['debit'];	
					$produits[$current_product_id]['op'][$i]=$operation;
					$counter++;
				}
				//totals for the last item.
				if($current_product_id>0){
					$produits[$current_product_id]['debit']=$debit;
					$produits[$current_product_id]['credit']=$credit;
					$produits[$current_product_id]['solde']=$solde;
					$produits[$current_product_id]['valeur']=$produits[$current_product_id]['solde']*$produits[$current_product_id]['info']['PA'];
					$valeur_total += $produits[$current_product_id]['valeur'];
				}
				// $produits[$key]['op']=$operations;
				// $produits[$key]['debit']=$debit;
				// $produits[$key]['credit']=$credit;
				// $produits[$key]['solde']=$solde;
				// $produits[$key]['valeur']=$solde*$produits[$key]["Produit"]['PA'];
				// $valeur_total += $produits[$key]['valeur'];
			//	exit(debug($produits));
			//}
			$referer=$this->referer();
			$this->set(compact('produits','date1','date2','id','stockNames','referer','valeur_total'));

		if(!empty($data)){
			return $produits;
		}
		
		
	}

	function stock() {
		$this->autoRender=false;
		$this->Product->stock($this->data);
	}
	
	function autoComplete($filter=''){
		$this->Product->autoComplete($this->data,$filter);
	}
	
	function updateProduit() {
		//let me know if it comes from an advanced form
		$this->autoRender=true;
    	$stockId = $this->data['Produit']['stock_id'];
    	if ((!empty( $stockId ))&&($stockId!=0)) {
      		$produits =$this->Produit->find('list',array('fields'=>array('Produit.id','Produit.name'),
      															'conditions'=>array('Produit.stock_id'=>$stockId,
																					'Produit.actif'=>'oui',
																					'Produit.type like'=>'%stockable%',
																					),
																'order'=>array('Produit.name')
																					
																));
    	}
    	else { 
    		$produits =$this->Produit->find('list',array('fields'=>array('Produit.id','Produit.name'),
																'conditions'=>array('Produit.actif'=>'oui',
																					'Produit.type like'=>'%stockable%',
																				),
																'order'=>array('Produit.name')
																));
		}
    	$produits[0]='---';
		$this->set(compact('produits'));
  	}
	
	function updateGroupe($n°,$advanced=null) {
		$this->Product->updateGroupe($n°,$advanced,$this->data);
  	}
	
	function _conditions($data){
		$conditions=array();
		if($data['Produit']['name']!='') {
			$conditions['Produit.name LIKE']=$data['Produit']['name'].'%';
		}
		if($data['Produit']['groupe_id']!=0) {
			$conditions['Produit.groupe_id']=$data['Produit']['groupe_id'];
		}
		else if($data['Produit']['section_id']!=0) {
			$conditions['Produit.groupe_id']=$this->Produit->Groupe->find('list',array('fields'=>array('Groupe.id','Groupe.id'),
																					'conditions'=>
																							array('Groupe.section_id'=>$data['Produit']['section_id'])
																					)
																		);
		}
		if($data['Produit']['type']!='') {
		 	$conditions['Produit.type']=$data['Produit']['type'];
		}
		if($data['Produit']['actif']!='') {
		 	$conditions['Produit.actif']=$data['Produit']['actif'];
		}
		if(Configure::read('aser.comptabilite')){
			if($data['Produit']['groupe_comptable_id']>0) {
				$conditions['Produit.groupe_comptable_id']=$data['Produit']['groupe_comptable_id'];
			}
			elseif($data['Produit']['groupe_comptable_id']==-1) {
				$conditions['NOT']=array('Produit.groupe_comptable_id'=>array_keys($this->groupeComptables));
			}
		}
		//exit(debug($conditions));
		return $conditions;
	}
	function rapport() {
		$datas=array();
		$total=$sum=$pvs=0;
		if(!empty($this->data)) {
		//Building conditions
			$conditions=$this->_conditions($this->data);
			$produits=$this->Produit->find('all',array('fields'=>array('Produit.*',
																	'Groupe.name',
																	'Groupe.section_id',
																	'Unite.name'
																	),
														'conditions'=>$conditions,
														'order'=>array('Produit.name')
														));
			foreach($produits as $key=>$produit){
				$quantite=$this->Product->productQty($produit['Produit']['id'],$this->data['Produit']['stock_id']);
				if(($this->data['Produit']['quantite']=='toutes')||(($this->data['Produit']['quantite']==' > 0')&&($quantite>0))){
					$datas[$key]['Produit']=$produit['Produit']['name'];
					$datas[$key]['Type']=$produit['Produit']['type'];
					$datas[$key]['Section']=$this->sections[$produit['Groupe']['section_id']];
					$datas[$key]['Groupe']=$produit['Groupe']['name'];
					$datas[$key]['Quantité']=$quantite;
					$datas[$key]['PA']=$produit['Produit']['PA'];
					$datas[$key]['Unité']=$produit['Unite']['name'];
					$pvs+=$datas[$key]['Total']=$datas[$key]['Quantité']*$datas[$key]['PA'];
				}
			}
		}
		$stock=$this->Produit->Historique->Stock->find('first',array('fields'=>array('Stock.name'),
																	'conditions'=>array('Stock.id'=>$this->data['Produit']['stock_id'])
																	));
		$this->set(compact('datas','total','pvs','stock'));
		if(!empty($this->data['Produit']['export'])&&($this->data['Produit']['export']==1)){
			$filename=$this->Product->excel($datas,array(),'stock_actuel');
			$this->redirect('/files/'.$filename);
		}
	}

	
	function index() {
		$this->paginate['order']=array('Produit.id desc');
		$produitConditions=$this->Session->read('produitConditions');
		$show=$this->Session->read('showProd');
		if((empty($this->data))&&(empty($produitConditions))) {
			$produits=$this->paginate();
		}
		elseif(!empty($this->data)) {
		//building conditions
			$produitConditions=$this->_conditions($this->data);
			if($this->data['Produit']['show']=='all'){
				$this->paginate['show']='all';
			}
			else {
				$this->paginate['limit']=$this->data['Produit']['show'];
			}
		 	$produitConditions['Produit.id !=']=0; //to get the pagination always working
			$show['Produit.show']=$this->data['Produit']['show'];
			
			$produits=$this->paginate($produitConditions);
			
			$this->Session->write('produitConditions',$produitConditions);
			$this->Session->write('showProd',$show);
		}
		else {
			if($show['Produit.show']=='all'){
				$this->paginate['show']='all';
			}
			else {
				$this->paginate['limit']=$show['Produit.show'];
			}
			$produits=$this->paginate($produitConditions);
			
		}
		$serveur=$this->Conf->find('serveurs');
		$caissier=$this->Conf->find('caissiers');
		$fonction=$this->Auth->user('fonction_id');
		
		//for multi pv
		$this->_setTarifs($produits);
	//	exit(debug($produits));
		$this->set(compact('produits','serveur','bars','caissier','fonction'));
	}
	
	function tarif_index($produitId){
		$tarifs=$this->Produit->Tarif->find('all',array('conditions'=>array('Tarif.produit_id'=>$produitId)));
		$this->set('tarifs', $tarifs);
		$this->layout='ajax';
	}
	
	function tarif_add(){
		$this->autoRender=false;
		$this->Produit->Tarif->save($this->data);
		exit(json_encode(array('success'=>true))); 
	}
	
	function tarif_delete($produitId,$tarifId) {
		$this->autoRender=false;
		$this->Produit->Tarif->delete($tarifId);
		exit(json_encode(array('success'=>true))); 
	}
	
	function quantites($produitId){
		$this->set('quantites',$this->Produit->Historique->find('all',array('conditions'=>array('Historique.produit_id'=>$produitId,
																																														'Historique.date <=' => date('Y-m-d')
																																														),
																																				
															'fields'=>array('sum(Historique.debit) as debit',
																			'sum(Historique.credit) as credit',
																			'Stock.name'
																			),
															'group'=>array('Historique.stock_id')
															)));
		$this->layout='ajax';
	}
	function historique($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Id pour %s incorrecte !', true), 'produit'));
			$this->redirect(array('action' => 'index'));
		}
		$produit=$this->Produit->read(null, $id);
		$precision=($produit['Produit']['expiration'])?("d'expiration"):("d'achat");
		$this->loadModel('Relation');
		$relations=$this->Relation->find('all',array('conditions'=>array(
																	'OR'=>array('Relation.premier_produit_id'=>$id,
																				'Relation.deuxieme_produit_id'=>$id
																				)
																		),
													'fields'=>array('Relation.*',
																	'Personnel.name',
																	'Personnel.id',
																	'PremierProduit.id',
																	'PremierProduit.name',
																	'DeuxiemeProduit.id',
																	'DeuxiemeProduit.name',
																	'Stock.id',
																	'Stock.name',
																	'Unite.name',
																	'Unite.id'
																	)
													)
										);
		$details=$this->Produit->ProduitDetail->find('all',array('conditions'=>array('ProduitDetail.produit_id'=>$id),
																'recursive'=>-1,
																'order'=>'ProduitDetail.date asc'
													));
																
		$this->set(compact('produit','relations','precision','details'));
	}
	
	function _tarif(&$produit){
		if(Configure::read('aser.multi_pv'))
			foreach(Configure::read('bars') as $bar=>$barTables)
				$produit['Produit'][$bar]=$this->Product->productPrice($produit['Produit']['id'],0,$bar);
		return $produit;
	}
	
	function _setTarifs(&$produits){
		foreach($produits as $key=>$produit){
			$produits[$key]=$this->_tarif($produit);
		}
	}
	
	function _show($id){
		$produit=$this->Produit->find('first',array('fields'=>array(
 																		'Groupe.name','Groupe.id','Groupe.section_id',
    																	'Unite.name','Unite.id',
    																	'Produit.*',
    																	'GroupeComptable.id','GroupeComptable.name'
    															),
    															'conditions'=>array('Produit.id'=>$id)
														));
		$this->_tarif($produit);
		$this->set(compact('produit',$produit));
		$this->layout="ajax";
		$this->render('add');
	}
	
	function _logic(&$data,$action){
		$champs="Section, Groupe, Nom";
		if(Configure::read('aser.comptabilite')){
			$champs.=", Groupe Comptable";
		}
		$failureMsg='Vérifiez s\'il les champs '.$champs.' sont remplis!';
		$this->Produit->set($data);
		if(($data['Produit']['groupe_id']==0)
		||($data['Produit']['section_id']==0)
		||($data['Produit']['name']=='')
		||(Configure::read('aser.comptabilite')&&($data['Produit']['groupe_comptable_id']==0))
		){
			if(isset($data['Produit']['vente'])||($action=='edit'))
				exit(json_encode(array('success'=>false,'msg'=>$failureMsg)));
			else 
				exit('failure_'.$failureMsg);
		}
		//removing any special character in the name
		$data['Produit']['name']=trim($this->Product->name($data['Produit']['name']));
		
		$cond['Produit.name']=$data['Produit']['name'];
		if($action=='edit'){
			$cond['Produit.id !=']=$data['Produit']['id'];
		}
		//preventing any double name
		$result=$this->Produit->find('all',array('conditions'=>$cond,
												'fields'=>array('Produit.id'),
												'recursive'=>-1
												)
										);
										
		$doubleNameMsg='Ce Produit existe déjà!';
		if(!empty($result)){
			if(isset($data['Produit']['vente'])||($action=='edit'))
				exit(json_encode(array('success'=>false,'msg'=>$doubleNameMsg)));
			else 
				exit('failure_'.$doubleNameMsg);
		}
		//saving
		$this->Produit->save($this->data);
		
		//trace stuff
		if(($action=='add')||(($action=='edit')&&($data['Produit']['name']!=$data['Produit']['old_name']))){
			$trace['Trace']['model_id']=$this->Produit->id;
			$trace['Trace']['model']='Produit';
			$trace['Trace']['operation']=($action=='add')?'Création du Produit avec le nom "'.$data['Produit']['name'].'".':
										'Changement de nom. De "'.$data['Produit']['old_name'].'" à "'.$data['Produit']['name'].'"';
			$this->Produit->Trace->save($trace);
		}
	}

	function add($advanced=null) {
		if (!empty($this->data)) {
			$this->_logic($this->data, 'add');

			//setting the special return name
			if(isset($this->data['Produit']['vente'])){
				$this->data['Produit']['PV']=$this->Product->productPrice($this->Produit->id,$this->data['Produit']['PV']);
				$string=(($this->data['Produit']['type']=='stockable')&&Configure::read('aser.connexion'))?
						($this->data['Produit']['name'].'_0_'.$this->data['Produit']['PV']):
						($this->data['Produit']['name'].'_'.$this->data['Produit']['PV']);
				exit(json_encode(array('success'=>true,
									'id'=>$this->Produit->id,
									'string'=>$string
									)
								)
					);
			}
			else {
    			$this->_show($this->Produit->id);
			}
  		} 
	}
	
	function edit($id = null,$edit='yes') {
		if($edit=='yes'){
			if (!empty($this->data)) {
				$this->_logic($this->data,'edit');
				exit(json_encode(array('success'=>true,'msg'=>'Modification Enregistré')));
			}
			else {
				$this->data = $this->Produit->find('first',array('fields'=>array('Produit.*'),
																'conditions'=>array('Produit.id'=>$id)
																));
				$this->data['Produit']['old_name']=$this->data['Produit']['name'];
				$section=$this->Produit->Groupe->find('first',array('fields'=>array('Groupe.section_id'),
																'conditions'=>array('Groupe.id'=>$this->data['Produit']['groupe_id'])
																));
				$this->data['Produit']['section_id']=$section['Groupe']['section_id'];
			}
		}
		else {
			$this->_show($id);
		}
	}	

	function change_pv($id, $pv,$bar=''){
		if(Configure::read('aser.multi_pv')){
			$search=$this->Produit->Tarif->find('first',array('fields'=>array('Tarif.id'),
															'conditions'=>array('Tarif.name'=>$bar,
																				'Tarif.produit_id'=>$id
																				)	
																			));
			$tarif['Tarif']['produit_id']=$id;
			$tarif['Tarif']['name']=$bar;
			$tarif['Tarif']['PV']=$pv;
			if(empty($search)){
				$tarif['Tarif']['id']=null;
			}
			else 
				$tarif['Tarif']['id']=$search['Tarif']['id'];
			$this->Produit->Tarif->save($tarif);
		}
		else {
			$produit=$this->Produit->find('first',array('fields'=>array('Produit.*'),
													'conditions'=>array('Produit.id'=>$id
																				),
													'recursive'=>-1	
													)); 
			$produit['Produit']['PV']=$pv;
			if(!$this->Produit->save($produit)){
				exit(json_encode(array('success'=>false)));
			}
		}
		exit(json_encode(array('success'=>true)));
	}
	
	function delete($id = null) {
		$notDeleted=0;
		$deleted=array();
		foreach($this->data['Id'] as $id){
			if($id!=0) {
				$test1=$this->Produit->Vente->find('first',array('conditions'=>array('Vente.produit_id'=>$id),
																'recursive'=>-1
												));
				$test2=$this->Produit->Sorti->find('first',array('conditions'=>array('Sorti.produit_id'=>$id),
																'recursive'=>-1
												));
				$test3=$this->Produit->Entree->find('first',array('conditions'=>array('Entree.produit_id'=>$id),
																			'recursive'=>-1
												));				
				$test4=$this->Produit->Mouvement->find('first',array('conditions'=>array('Mouvement.produit_id'=>$id),
																	'recursive'=>-1
												));									
				if ((!empty($test1))||(!empty($test2))||(!empty($test3))||(!empty($test4))){
					$notDeleted++;
				}
				else {
					$this->Produit->delete($id);
					$deleted[]=$id;
				}
			}
		}
		$msg="Vérifié s'il n'y a pas d'entrees, de ventes ou de sorties liés à ";
		$msg=($notDeleted>1)?$msg.'ces produits.':$msg.'ce produit.';
		exit(json_encode(array('success'=>true,
							'deleted'=>$deleted,
							'notDeleted'=>$notDeleted,
							'msg'=>$msg
							)));
	}

	function mass_modification() {
		$this->autoRender=false;
		if (!empty($this->data)) {
			$i=0;
			foreach($this->data['Id'] as $value){
				if($value!=0) {
					$produitInfo=$this->Produit->find('first',array('fields'=>array('Produit.id'),
															'conditions'=>array('Produit.id'=>$value),
															'recursive'=>-1
															)
											);
					if($this->data['Produit']['section_id']!=0){
						$produitInfo['Produit']['section_id']=$this->data['Produit']['section_id'];
					}
					if($this->data['Produit']['groupe_id']!=0){
						$produitInfo['Produit']['groupe_id']=$this->data['Produit']['groupe_id'];
					}
					
					if(!empty($this->data['Produit']['acc'])&&($this->data['Produit']['acc']!='')){
						$produitInfo['Produit']['acc']=$this->data['Produit']['acc'];
					}
					if($this->data['Produit']['actif']!=''){
						$produitInfo['Produit']['actif']=$this->data['Produit']['actif'];
					}
					if($this->data['Produit']['type']!=''){
						$produitInfo['Produit']['type']=$this->data['Produit']['type'];
					}
					if($this->data['Produit']['unite_id']!=0){
						$produitInfo['Produit']['unite_id']=$this->data['Produit']['unite_id'];
					}
					if($this->data['Produit']['groupe_comptable_id']!=0){
						$produitInfo['Produit']['groupe_comptable_id']=$this->data['Produit']['groupe_comptable_id'];
					}
					$this->Produit->save($produitInfo);
					
					$this->Produit->logIt($this->Produit->id,'MODIFICATION');
					
					unset($this->Produit->id);
					$i++;
				}
			}
			$this->Session->setFlash($i.' produits ont été modifiés');
			$this->redirect($this->referer());
		}
	}
	
	
	function _section($name){
		$find=$this->Produit->Groupe->Section->find('first',array('conditions'=>array('Section.name'=>$name,
																				),
															'recursive'=>-1,
															'fields'=>array('Section.id','Section.name')
															)
											);
			if(!empty($find)){
				return $find['Section']['id'];
			}
			else {
				$sectionDetails['Section']['name']=$name;
				$this->Produit->Groupe->Section->save($sectionDetails);
				$id=$this->Produit->Groupe->Section->id;
				unset($this->Produit->Groupe->Section->id);
				return $id;
			}
	}

	function _unite($name){
    $find=$this->Produit->Unite->find('first',array('conditions'=>array('Unite.name'=>$name,
                                        ),
                              'recursive'=>-1,
                              'fields'=>array('Unite.id','Unite.name')
                              )
                      );
      if(!empty($find)){
        return $find['Unite']['id'];
      }
      else {
        $uniteDetails['Unite']['name']=$name;
        $this->Produit->Unite->save($uniteDetails);
        $id=$this->Produit->Unite->id;
        unset($this->Produit->Unite->id);
        return $id;
      }
  }

	function _groupe($section,$name){
		$find=$this->Produit->Groupe->find('first',array('conditions'=>array('Groupe.name'=>$name,
																				),
															'recursive'=>-1,
															'fields'=>array('Groupe.id','Groupe.name')
															)
											);
			if(!empty($find)){
				return $find['Groupe']['id'];
			}
			else {
				$groupeDetails['Groupe']['name']=$name;
				$groupeDetails['Groupe']['section_id']=$section;
				$this->Produit->Groupe->save($groupeDetails);
				$id=$this->Produit->Groupe->id;
				unset($this->Produit->Groupe->id);
				return $id;
			}
	}

	function upload_xls() {
		set_time_limit(240);    //4minutes
		ini_set('memory_limit', '64M');

		App::import('Vendor', 'Spreadsheet_Excel_Reader', array('file' => 'excelreader/reader.php'));
    	$data = new Spreadsheet_Excel_Reader();
    	 // Set output Encoding.
    	$data->setOutputEncoding('CP1251');
		if(!empty($this->data)) {
    	//exit(debug($this->data));
			//emptying  tables
		/*
			$this->Produit->Section->query('truncate table sections');
			$this->Produit->Groupe->query('truncate table groupes');
			$this->Produit->query('truncate table produits');
			$this->Produit->query('truncate table produit_details');
			$this->Produit->Vente->query('truncate table ventes');
			$this->Produit->Vente->Facture->query('truncate table factures');
			$this->Produit->Vente->Facture->Journal->query('truncate table journals');
			$this->Produit->Vente->Facture->Paiement->query('truncate table paiements');
		//*/	

    		$data->read($this->data['Produit']['file']['tmp_name']);
    	//	die(debug($data->sheets[0]['cells']));
			$i=0;
			foreach($data->sheets[0]['cells'] as $row){
				if(count($row)>=7){
						// exit(debug($row));
					$produit['section_id']=$this->_section($row[1]);
					$produit['groupe_id']=$this->_groupe($produit['section_id'],$row[2]);
					$produit['name']=$row[3];
					$produit['PA']= (!empty($row[4])) ? round($row[4]) : 0;
					$produit['PV']=(!empty($row[5])) ? round($row[5]) : 0;
					$produit['unite_id']= (!empty($row[6])) ? $this->_unite($row[6]): $this->_unite("pièce");
					$produit['type']=	(!empty($row[7])) ? $row[7]: 'non_stockable';
					$produit['description']=	(!empty($row[8])) ? $row[8]: '';
					$this->Produit->save(array('Produit'=>$produit));
					unset($this->Produit->id);
				}
				$i++;
			}
			$this->Session->setFlash(sprintf(__('tImportation réussie !', true), 'Produit'));
			$this->redirect(array('action' => 'index'));
		}
		
	}
	function ingredient($produitId=null){
		$this->loadModel('Ingredient');
	//	exit(debug($this->data));
		if(!empty($this->data)){
			//deleting the data already stored first
			$this->Ingredient->deleteAll(array('Ingredient.produit_id'=>$this->data['produit_id']));
		}
		if(!empty($this->data['Ingredient'])){
			//saving now the new data
			$PA=0;
			foreach($this->data['Ingredient'] as $ingredient){
				$ing['EstComposerPar']['produit_id']=$this->data['produit_id'];
				$ing['EstComposerPar']['ingredient_id']=$ingredient['ingredient_id'];
				$ing['EstComposerPar']['qte']=$ingredient['qte'];
				$this->Produit->EstComposerPar->save($ing);
				unset($this->Produit->EstComposerPar->id);
				$produitInfo=$this->Produit->find('first',array('fields'=>array('Produit.PA'),
																'conditions'=>array('Produit.id'=>$ingredient['ingredient_id'])
																));
				$PA+=$produitInfo['Produit']['PA']*$ingredient['qte']; //calcul du cout total de production du produit
			}
			$updateProduit['PA']=$PA;
			$updateProduit['id']=$this->data['produit_id'];
			$this->Produit->save(array('Produit'=>$updateProduit)); //updating the main product with the new PA
			
			exit(json_encode(array('success'=>true,'msg'=>'Engregistré Correctement!','PA'=>$PA)));
		}
		else if(empty($this->data)) {
			$ingList=$this->Produit->find('list',array(
													'conditions'=>array(
													'Produit.type'=>'stockable'
																		),
													'order'=>array('Produit.name')
														));
			$ingredients=$this->Ingredient->find('all',array('conditions'=>array('Ingredient.produit_id'=>$produitId),
															'fields'=>array('Ingredient.*','Compose.name'),
															));
			$this->set(compact('ingList','ingredients'));
		}
		else {
			exit(json_encode(array('success'=>true,'msg'=>'Ingredients effacés!')));
		} 	
	}
}
?>