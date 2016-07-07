<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.phppu
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */
/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
 
 /**
  * The following lines is for app modules settings.
  * Will be set parameters to determine which modules to show or hide
  * 
  */
	Configure::write('aser',array(
								'hotel'=>0, //activer seulement s'ils ont besion du module d'hebergement
								 'POS'=>1, // Point of Sale, a activer s'ils ont besion du module de point de vente 
								 'services'=>1, //a activer seulement pour les hotels
								 'sama'=>0,//sama café mode when serveur get access to computer,
								 'bon'=>1, //impression des bons par l'ordinateur
								 'magasin'=>1, // a activer si c'est pas un restaurant
								 'connexion'=>0, // a activer s'ils desirent une synchronisation entre le point de vente et le stock
								 'multi_resto'=>0, //a activer pour les restaurants avec plusieurs point de vente
								 'company_info'=>1, // toujours activer
								 'bon_num'=>0, // a toujours desactiver
								 'TTC'=>0, // a activer s'il la direction desire afficher le "TTC" sur les factures resto (toutes taxes comprises)
								 'beneficiaires'=>0, // a activer si l'entreprise accepte que des clients prennent des produits sur le compte des autres 
								 'detailed_ben'=>0, // a activer pour les pharmacies qui travaillent les mutuelles d'assurance 
								 'extras'=>0, //toujours desactiver
								 'conference'=>0, //a activer pour le module de gestion des salles de conference
								 'tva'=>1, // a activer si l'entreprise est soumise a la tva et mettre son taux.
								 'name'=>'aser', // nom du dossier qui contient le logiciel ,
								 'upload'=>0, // a activer pour ceux qui ulitise la version touchscreen du point de vente
								 'swipe'=>0, // a activer si on ulitilse les cartes magnetiques pour la connexion
								 'touchscreen'=>0, // a activer pour ceux qui ulitise la version touchscreen du point de vente
								 'alerts'=>1, //  a activer pour les pharmacies ou autres entreprises qui desirent des alertes
								 'tresorerie'=>1, // a activer s'ils utilisent le module tresorerie
								 'stock'=>1,  // a activer s'ils utilisent le module stock
								 'client_auto_creation'=>1,  // a activer s'ils les caissier ont le droit de creer de nouveau client
								 'comptabilite'=>0,
								 'advanced_stock'=>1, //for accompagnement stuff to be activated
								 'bonus'=>0, // a activer s'ils l'entreprise donne des bonus
								 'stock_option_caisse'=>1, // a activer s'ils les caissiers ont le droit de creer et de modifier de nouveau produits
								 'link'=>0,
								 'facturation_cyclique'=>1,
								 'display_bill_number'=>1,
								 'billetage'=>0,
								 'caissier_serveur'=>0, // a activer les serveurs jouent aussi le role des caissiers
								 'tables'=>22, // le nombre total de table dont dispose leur restaurant
								 'cloturer'=>1, // if on cloture automatique le journal at the beginning of each day
								 'multi_pv'=>0, // dsi le produit peut avoir plusieurs prix differents,
								 'pharmacie'=>0, // si l'entreprise est une pharmacie active les batch number & date d'expiration
								 'default_currency'=>'BIF',
								 'PU'=>1,
								 'proforma'=>0,
								 'one_time_printing'=>0, // a activer s'ils veulent que les factures tickets soient imprimes une seule fois au maximum
								 'pos_sales_report'=>0,
								 'auto_logout'=>0, // a activer s'ils desirent une deconnexion automatique apres 30 secondes d'inactivites
								 'database'=>'aser',
								 'aserb'=>0, // a activer pour belair seulement
								 'silhouette'=>1, // a activer pour silhouette seulement,
								 'impression_par_serveur'=>0, //wether to allow a waiter to print a bill
								 'multi_serveur'=>0, // multi waiter working on the same bill
								 'ingredient'=>1, // a activer si le client desire mettre la composition de chaque plat ou produit et ainsi connaitre le PA des produits composes
								 'modele_signature'=>1,
								 'shifts'=>0, //1 si les shifts de travail pour le stock sont actives
								 'table_show_waiter'=>1 ,// 1 si on veut afficher sur chaque table le nom du serveur en question
								 'annulee'=>array(), // annulee contient la liste des id des personnes qui peuvent annuler les factures exclusivement
								 'chg_num'=>0,
								 'mahanaim'=>0, //option speciale pour les services d'entretien de machine,
								 'choix_tva'=>1, //option to choose between tva incluse ou non incluse,
								 'reduction_on_res_bill'=>1, // yes if they want to add a line of the reduction made on the facture global of a booking
								 'caisse_auto_copy'=>1, //if they want the money from the rapport caisse or journal to be copied automaticaly into the chosen caisse 
								 'default_stock'=>1, //default stock id if multi stock is disabled but connexion is enabled,
								 'conference-manual'=>1, // if on le calcul des quantites est manuel lors de la creation de la facture proforma/location. au liu detre un multiple des jours et du nbre de pers	
								 'conference-resto-reception'=>0, // if on les conso resto de la conference sont factures a la reception,
								 'caisse_interdite'=>0,
								 'ebenezer'=>0, //if on enables features of pharmacie eben ezer
								 'gouvernance'=>1, //if on enables features related to housekeeping
								 'groupes_on_index'=>1, //if on enables the grouping of products on the vente index
								 'disable_transfer'=>1, //to disable transfer of bills from on journal to another.
								 'deny_caishier_to_make_credit'=>1,
								 'disable_nembeteplus'=>0, //when managers want to force the closing of the report.
								 'export_bills'=>1, //option payant of exporting bills to xls
								 'belair'=>1,
									'gestion_reduction'=>1,
								//	'kcc'=>0, // to enable/disable Kings conference specific features
									'xls_copy'=>1,
									'all_company_info'=>1,
									'manual_bill_numbering'=>1 //on if user can use their own numbers for bills.
								 )
					); 
	// setup for the options needed by the function that copies the journal money into the caisse
	Configure::write('caisse',array('caisse_id'=>26,
									'type_id'=>14
									));				
	//setup the options for late checkout or demi
	Configure::write('demis',array(50=>'50 %',75=>'75 %'));
	
	
	//logo details specifique to each company/hotel  
	Configure::write('logo',array('width'=>226,
								  'height'=>131,
								  'name'=>'logo-silhouette.jpg',
								  )
					);
					
					
	//printers settings
	Configure::write('printer',array('caissier'=>'caissier',
								  'barman'=>'barman,caissier',
								  'cuisine'=>'cuisine'
								  )
					);
	//ventes to ignore in journal
	Configure::write('ventes',array(1,2,8));
	Configure::write('models',array('Vente'=>1,'Reservation'=>2,'Location'=>8));
	
	//multi bar config
		//multi bar config
	Configure::write('bars',array('zone_1'=>array(1,15,100,100,110),
								'zone_2'=>array(16,30),
								)
					);
	/**
	 * shift configuration
	 */
	 Configure::write('shifts',array(1=>'Shift 1',
	 								2=>'Shift 2',
	 								3=>'Shift 3'
	 								)
					);		
					
	/**
	* categeories de depenses.
	*/

	Configure::write('categories',array(0=>'',1=>'Intrants',2=>"Produits d'entretien",3=>"Frais de fonctionement", 4=>"Investissement", 5=>"Stock et Autres"));
	/**
	* List of countries
	*/				
	include('countries.php');
	Configure::write('countries',$countries);
/**
 * Here is the settings for server restrictions
 */
 
 Configure::write('license',array('server_mac_address'=>'00:26:6c:7d:dc:5d',
 								'server_ip_address'=>'',
 								'server_name'=>'',
 								)
				);
	//acl plugin by alaxos
	require_once(APP . DS . 'plugins' . DS . 'acl' . DS . 'config' . DS . 'bootstrap.php');
