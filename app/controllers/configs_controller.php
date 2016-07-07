<?php
class ConfigsController extends AppController {
	
	function reset_db($type='partial'){
		$userId=$this->Auth->user('id');
		if($userId!=11) {
			$this->Session->setFlash('Seul Armand peut effectué la réinitialisation');
			$this->redirect('/');
		}

		$config_tables = ['produits','groupes','sections','stocks','tiers','chambres','type_chambres','type_services','salles','unites','caisses','types','tarifs'];
		$operation_tables = [
												'reductions',
												'ventes',
												'vente_effaces',
												'order_details',
												'orders',
												'paiements',
												'services',
												'location_extras',
												'locations',
												'reservations',
												'entrees',
												'sortis',
												'pertes',
												'mouvements',
												'historiques',
												'traces',
												'caisse_interdites',
												'operations',
												'ingredients',
												'limits',
												'tarifs',
												'factures',
												'journals',
												];

		$this->loadModel('Produit');
		if($type == 'complete'){
			$tables =  array_merge($operation_tables, $config_tables);
			$this->Produit->query("delete from personnels where id != 11");
		}
		else {
			$tables = $operation_tables;
		}
		$this->loadModel('Produit');
		// exit(debug($tables));
		foreach ($tables as $table) {
			$this->Produit->query("delete from $table");
		}

		$this->Session->setFlash('Opération effectuée');
		$this->redirect('/');
	}

	function repair_all_tables(){
		$this->autoRender=false;
		$db = ConnectionManager::getDataSource('default');
		$tables = $db->listSources();
		foreach($tables as $table){
			$model=Inflector::camelize(Inflector::singularize($table));
			$this->loadModel($model);
		 	$this->$model->query('REPAIR TABLE  '.$table);
		}	
		$this->Session->setFlash(__('Opération effectuée', true));
		$this->redirect('/');
	}
	
	function defaulter() {
		$data=$this->Session->read('defaults');
	//	die(debug($data));
		if(!empty($this->data)) {
			$defaults['Config']=$this->data['Config'];
			$defaults['Config']['date']=$this->data['Config']['date']['year'].'-'.
								$this->data['Config']['date']['month'].'-'.
								$this->data['Config']['date']['day'];
			
			//die(debug($defaults));
			$defaults['Config']['section_id']=$this->data['Produit']['section_id'];
			$defaults['Config']['groupe_id']=$this->data['Produit']['groupe_id'];
			if($this->Session->write('defaults',$defaults)) {
				//$this->Cookie->write('defaults',$defaults,true,'+2 weeks'); //saving into cookies
				$this->Session->setFlash(sprintf(__('Informations enregistrées', true), 'Configuration'));
			}
			else {
				$this->Session->setFlash(sprintf(__('Impossible d\' enregistrer !', true), 'Configuration'));
			}
		}
		$this->loadModel('Caiss');
		$this->loadModel('Tier');
		$this->loadModel('Tier');
		$this->loadModel('Type');
		$this->loadModel('Fonction');
		$this->loadModel('Personnel');
		$this->loadModel('Unite');
		$this->set(array('caisses'=>$this->Caiss->find('list',array('recursive'=>-1)),
						'tiers'=>$this->Tier->find('list',array('fields'=>array('id','name','type'),'recursive'=>-1)),
						'types'=>$this->Type->find('list',array('fields'=>array('id','name','type'),'recursive'=>-1)),
						'fonctions'=>$this->Fonction->find('list',array('recursive'=>-1)),
						'personnels'=>$this->Personnel->find('list',array('recursive'=>-1)),
						'unites'=>$this->Unite->find('list',array('recursive'=>-1)),
							));
				
	}
	function index() {
		$this->autoRender=true;
		if(!empty($this->data)) {
			if(!empty($this->data['Config']['file'])) {
				$formdata[]=$this->data['Config']['file'];
				$result=$this->uploadFiles('img',$formdata,null);
				if(!empty($result['urls'][0])) {
					$this->data['Config']['logo']=$result['urls'][0];
					unset($this->data['Config']['file']);
				}
				else {
					unset($this->data['Config']['file']);
				}
			}
			else {
				unset($this->data['Config']['file']);
			}
			if($this->Conf->save($this->data)) {
				$this->Session->setFlash(sprintf(__('Informations enregistrées', true), 'Configuration'));
			}
		}
		else {
			$this->data=$this->Conf->find('all');
		}
		$this->loadModel('Section');
		$sections=$this->Section->find('list');
		
		$this->loadModel('Fonction');
		$fonctions=$this->Fonction->find('list');
		
		$this->loadModel('Unite');
		$unites=$this->Unite->find('list');
		$config=Configure::read('aser');
		if(!$config['multi_resto']){
			$this->loadModel('Stock');
			$stocks=$this->Stock->find('list');
			
			$this->loadModel('Caiss');
			$caisses=$this->Caiss->find('list');
		}
		$this->set(compact('sections','fonctions','unites','caisses','stocks','config'));
	}
	
	/* uploads files to the server
 * @params:
 *		$folder 	= the folder to upload the files e.g. 'img/files'
 *		$formdata 	= the array containing the form files
 *		$itemId 	= id of the item (optional) will create a new sub folder
 * @return:
 *		will return an array with the success of each file upload
 */
function uploadFiles($folder, $formdata, $itemId = null) {
	// setup dir names absolute and relative
	$folder_url = WWW_ROOT.$folder;
	$rel_url = $folder;
	
	// create the folder if it does not exist
	if(!is_dir($folder_url)) {
		mkdir($folder_url);
	}
		
	// if itemId is set create an item folder
	if($itemId) {
		// set new absolute folder
		$folder_url = WWW_ROOT.$folder.'/'.$itemId; 
		// set new relative folder
		$rel_url = $folder.'/'.$itemId;
		// create directory
		if(!is_dir($folder_url)) {
			mkdir($folder_url);
		}
	}
	
	// list of permitted file types, this is only images but documents can be added
	$permitted = array('image/jpeg');

	// loop through and deal with the files
	foreach($formdata as $file) {
		// replace spaces with underscores
	//	$filename = str_replace(' ', '_', $file['name']); i wanna give logo.jpg for the filename
		$filename='logo.jpg';
		// assume filetype is false
		$typeOK = false;
		// check filetype is ok
		foreach($permitted as $type) {
			if($type == $file['type']) {
				$typeOK = true;
				break;
			}
		}
		
		// if file type ok upload the file
		if($typeOK) {
			// switch based on error code
			switch($file['error']) {
				case 0:
					// check filename already exists
					if(!file_exists($folder_url.'/'.$filename)) {
						// create full filename
						$full_url = $folder_url.'/'.$filename;
						$url = $rel_url.'/'.$filename;
						// upload the file
						$success = move_uploaded_file($file['tmp_name'], $url);
					} else {
						// create unique filename and upload file
						ini_set('date.timezone', 'Africa/Harare');
						$now = date('Y-m-d-His');
						$full_url = $folder_url.'/'.$now.$filename;
						$url = $rel_url.'/'.$now.'_'.$filename;
						$success = move_uploaded_file($file['tmp_name'], $url);
					}
					// if upload was successful
					if($success) {
					//remove the first folder img from the url
						$tmp=explode('/',$url);
						unset($tmp[0]);
						$url=implode('/',$tmp);
						// save the url of the file
						$result['urls'][] = $url;
					} else {
						$result['errors'][] = "Error uploaded $filename. Please try again.";
					}
					break;
				case 3:
					// an error occured
					$result['errors'][] = "Error uploading $filename. Please try again.";
					break;
				default:
					// an error occured
					$result['errors'][] = "System error uploading $filename. Contact webmaster.";
					break;
			}
		} elseif($file['error'] == 4) {
			// no file was selected for upload
			$result['nofiles'][] = "No file Selected";
		} else {
			// unacceptable file type
			$result['errors'][] = "$filename cannot be uploaded. Acceptable file types: gif, jpg, png.";
		}
	}
return $result;
}

	function restore_db(){
		$fonction_id = $this->Auth->user('fonction_id');
		if($fonction_id != 3){
			$this->Session->setFlash("Vous n'avez pas accès à cette fonction!");
			$this->redirect('/');
		}

		if(!empty($this->data)){
			//exit(debug($this->data));
			if(!empty($this->data['Config']['file'])){
				$file = $this->data['Config']['file']['tmp_name'];

				Configure::write('debug',0);
				App::import('Core', 'ConnectionManager');
				$dataSource = ConnectionManager::getDataSource('default');
				$host=$dataSource->config['host'];
				$root=$dataSource->config['login'];
				$passwd='';
				if(!empty($dataSource->config['password'])) {
					$passwd='-p'.$dataSource->config['password'];
				}
				$db=$dataSource->config['database'];
				$command=$this->Conf->find('command');
				$cmd_result=shell_exec("mysql -h$host -u$root $passwd $db < $file");
				if($cmd_result == ''){
					$this->Session->setFlash("La base de données a été restaurée!");
					$this->redirect('/');
				}
				else {
					$this->Session->setFlash("Un problème est survenue lors de la restauration. $cmd_result");
				}
			}
			else {
				$this->Session->setFlash("Aucun fichier n'a été importé");
			}
		}
	}

	function backup() {
		$this->autoRender=true;
		Configure::write('debug',0);
		App::import('Core', 'ConnectionManager');
		$dataSource = ConnectionManager::getDataSource('default');
		$host=$dataSource->config['host'];
		$root=$dataSource->config['login'];
		$passwd='';
		if(!empty($dataSource->config['password'])) {
			$passwd='-p'.$dataSource->config['password'];
		}
		$db=$dataSource->config['database'];
		$command=$this->Conf->find('command');
		$backup=shell_exec("mysqldump -h$host -u$root $passwd $db ");
		$db=$db.'_';
		$this->set(compact('backup','db'));
		$this->layout='backup_layout';
		}
}
