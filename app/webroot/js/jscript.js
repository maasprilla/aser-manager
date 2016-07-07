/**
 * this function is used to modify locations bills. proforma and the final 
 * one if resto consumptions are billed at the reception
 */

function edit_location(reservation_id,type,el){
jQuery('<div id="loca_edit" title="Modification de la location"></div>').insertAfter('body');
   		     		jQuery('#loca_edit').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:1000,
    		               position:'top',
    		               buttons: { "Modifier": function() {
    		               	var clientName=jQuery('#tierId option:selected').html();
    		               	jQuery('form#location-extras input').each(function(){
    								jQuery(this).appendTo('form#loca_form').css('display','none').attr('class','extra');
    							});
    					            jQuery('#loca_form').ajaxSubmit({
							             dataType:'json',
							             data:{'data[Location][PU]':jQuery('#pu1').val(),
							             },
							             success:function(ans){
							             	if(ans.success){
							                 jQuery('#loca_edit').dialog('close');
							                 location.reload();
							                 
    							           	}
    							           	else {   alert(ans.msg); }
    							           
    							         },
    							    });
    							    },
    					            "Fermer": function() { jQuery(this).dialog("close"); }
    					           }
                       });
                       jQuery("#loca_edit").html('<span id="loading">Chargement...</span>');
    		           jQuery('#loca_edit').load(getBase()+"locations/edit/"+reservation_id+'/'+type,function(){date();});
}    		           
function transfer(){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionner une facture!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(:disabled)').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    	
		jQuery('#trans_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Transférer": function() {
    			var caissier=jQuery('form[name="'+nom+'"]').attr('action',getBase()+'ventes/transfer');
    			
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:false,
    				data:{'data[Vente][personnel_id]':jQuery('#caissier option:selected').val(),
    					'data[Vente][date]':jQuery('#DateJournal').val(),
    					'data[Vente][journal_id]':jQuery('#journalId').val(),
    					'data[Vente][paiements]':jQuery('#paiementsBox').val(),
    				},
    				beforeSend:function(){ jQuery('#message_trans').html('<span id="loading">Patientez...</span>');},
					complete:function(){ jQuery('#message_trans').html('');},
					dataType:'json',
					success:function(response){
					//	jQuery('body').html(response)
					//*	
					  jQuery('#trans_boxe').dialog('close');
						jQuery(this).dialog("close");
						document.location.reload();
					//*/
    			}
    		});
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    		}
    	}
   } );
    	
    }
}

function add_row(param){
	if(param=='vip'){
     		var tr='<tr>';
     		tr=tr+'<td><input type="text" name="vip"/></td>';
     		tr=tr+'<td><input type="text" name="groupe"/></td>';
     		tr=tr+'<td><input type="text" name="arrivee"/></td>';
     		tr=tr+'<td><input type="text" name="depart"/></td>';
     		tr=tr+'<td><input type="text" name="chambre"/></td>';
     		tr=tr+'</tr>';
     		jQuery('table#vip').append(tr);
     }
     else {
     	var tr='<tr>';
     		tr=tr+'<td><input type="text" name="conference"/></td>';
     		tr=tr+'<td><input type="text" name="timing"/></td>';
     		tr=tr+'<td><input type="text" name="salle"/></td>';
     		tr=tr+'<td><input type="text" name="invite"/></td>';
     		tr=tr+'<td><input type="text" name="cafe"/></td>';
     		tr=tr+'<td><input type="text" name="dejeuner"/></td>';
     		tr=tr+'<td><input type="text" name="diner"/></td>';
     		tr=tr+'</tr>';
     		jQuery('table#events').append(tr);
     }
}

function stats(days,month,year){
	if(jQuery('tbody#occupation').css('display')=='none'){
		jQuery('td[name="occupation"]').html('<span id="loading">Actualisation...</span>');
		jQuery.ajax({
			type:'GET',
			url:getBase()+'reservations/occupation/'+days+'/'+month+'/'+year,
			cache:false,
			success:function(text){
				jQuery('tbody#occupation').html(text).slideToggle('fade');
			}
		});
	}
	else {
		jQuery('tbody#occupation').hide('fade');
	}
}

function paiement_msg(){
	var obs=prompt('Mettez la raison du manque de paiement : ');
	if((obs!=null)&&(obs!='')){
		return obs;
	}
	else {
		alert('Echec d\'enregistrement! veuillez réessayer.');
		return 0;
	}
}

function state_updater(id,el,state,obs,force,heure){
	 var controller=(jQuery('div#tabella').attr('type')=='reservations')?('reservations'):('locations');
	 var goOn=true;
	 if((state=='partie')&&(force==undefined)){
	 	if(confirm('Voulez vraiment changer l\'etat à partie ? car l\'opération est irréversible!')){
	 		goOn=true;
	 	}
	 	else {
	 		goOn=false;
	 	}
	}
	else if(state=='credit'){
		obs=paiement_msg();
		goOn=(obs!=0)?true:false;
	}
	 if(goOn) { 
	 	jQuery.ajax({
				 		type:'GET',
				 		dataType:'json',
				 		url:getBase()+controller+'/state_updater/'+id+'/'+state+'/'+obs+'/'+force+'/'+heure,
						success:function(ans){
							if(ans.success){
								if(ans.confirm){
									if(confirm(ans.msg)){
										obs=paiement_msg();
										if(obs!=0)
											state_updater(id,el,state,obs,1,heure);
									}
								}
    							jQuery(el).attr('class',ans.state);
    							if((state=='arrivee')&&((jQuery(el).attr('facture')==0)||(jQuery(el).attr('facture')==undefined))){
    								createFactureByRightClick(0,id,el,'facture',state);
    							}
    						}
    						else {
    							alert(ans.msg);
    						}
    					},
    					
				 });
	}		 
}

function binder(){
	jQuery('span#add_facture').unbind('click').bind('click',function(){resto_create(factureId)}).attr('class','boutton');
 	jQuery('span#remove_facture').unbind('click').bind('click',function(){facture_removal(factureId,'facture')}).attr('class','boutton');
 	jQuery('span#remove_conso').unbind('click').bind('click',function(){facture_removal(factureId,consoId)}).attr('class','boutton');
 	jQuery('span#serveur').unbind('click').bind('click',function(){serveur_changer(factureId)}).attr('class','boutton');
 	jQuery('span#table').unbind('click').bind('click',function(){table_changer(factureId)}).attr('class','boutton');
 	jQuery('span#paiement_facture').unbind('click').bind('click',function(){paiement(factureId)}).attr('class','boutton');
 	jQuery('span#paiement_facture_touch').unbind('click').bind('click',function(){paiement_touch(factureId)}).attr('class','boutton');
 	jQuery('span#direct_reduction').unbind('click').bind('click',function(){direct_reduction(factureId)}).attr('class','boutton');
 	jQuery('span#separator').unbind('click').bind('click',function(){separator(factureId)}).attr('class','boutton');
  jQuery('span#change_bill_number').unbind('click').bind('click',function(){bill_number_changer(factureId)}).attr('class','boutton');
}

function serveur_changer(factureId){
	if(factureId!=0){
		var serveur_id=jQuery('#VentePersonnelId option:selected').val();
		var serveur=jQuery('#VentePersonnelId option:selected').html();
		if(serveur==''){
			alert('Sélectionné un serveur !');
		}
		else {
			jQuery.ajax({
 				type:'GET',
 				url:getBase()+'ventes/serveur/'+factureId+'/'+serveur_id,
 				dataType:'json',
 				success:function(response){
 						if(response.success){
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="waiter"]').text(serveur);
 						}
 						else 
 							alert(response.msg);
 				}
 			});
 		}
	}
	else {
		alert('Sélectionné une facture !');
	}
}

function cloturer(id,personnel_id){
	var closed=jQuery('#etat_journal').attr('closed');
	if(id===''){
		alert('Aucun Rapport séléctionné !');
	}
	else if(closed=='1'){
		alert('Le Rapport est déjà clôturé !');
	}
	else {
		var obs=jQuery('#obs').val();
		var shift=(jQuery('#shift').val()!==undefined)?jQuery('#shift').val():1;
		var versement=jQuery('#versement').attr('montant');
	//	var journalData={"journal":{"id":id,"personnel_id":personnel_id,"shift":shift,"observation":obs}};
		
		var journalData =jQuery.parseJSON(jQuery('#journalData').text());
		journalData['params']={"id":id,"personnel_id":personnel_id,"shift":shift,"observation":obs};
		jQuery.ajax({
 			type:'POST',
 			url:getBase()+'ventes/cloturer',
 			dataType:'json',
 			data:{'data':journalData},
 			success:function(response){
 				if(response.success){
 					jQuery('#etat_journal').text('Clôturée').attr('closed',1);
 					jQuery('#obs').attr('disabled','disabled');
 					jQuery('.cacher').remove();
 				}
 				else {
 					alert(response.msg);
 				}
 			}
 		});
 	}
}

function num(){
		var num=prompt('Le numéro de la facture pour impression');
		if((num!='')&&(num!=null))
			jQuery('#displayed_num').text(' n° '+num);
	}
	
function getBase(){
	var test= location.pathname.split('/');
	var name=jQuery('body').attr('name');
	return (test[1]==name)?('/'+name+'/'):('/');
}

/**
 * resto index page ajax stuff
 */
 var factureId=0;
 var consoId=0;
 
 function resto_date(){
 	jQuery('#DateResto').change(function(){
 		var date=jQuery(this).val();
 		jQuery.ajax({
 			type:'GET',
 			url:getBase()+'ventes/index/'+date,
 			success:function(response){
 				jQuery('table#list_factures tr:not(tr:first)').remove();
 				factureId=0;
 				consoId=0;
 				jQuery('table#list_produits tr:not(tr:first)').remove();
 				jQuery(response).insertAfter('table#list_factures tr:first');
 				jQuery('#DateResto').val(date);
 				vente_details('','',0,0,0,'','','','','','','');
 				//disable stuff
 				jQuery('span#add_facture').bind('click',function(){resto_create(factureId);}).attr('class','boutton');
 				jQuery('span#remove_facture').bind('click',function(){facture_removal(factureId,'facture');}).attr('class','boutton');
 				jQuery('span#remove_conso').bind('click',function(){facture_removal(factureId,consoId);}).attr('class','boutton');
 			}
 		});
	});
 }
 
 function indicator(){
	 jQuery("#indicator").bind("ajaxSend", function(){
  		 jQuery(this).show();
 	 }).bind("ajaxComplete", function(){
   		jQuery(this).hide();
 	});
 	jQuery('body').bind("ajaxError",function(e, jqxhr, settings, exception) {
  				alert('Accès Interdit!');
	 });
 }
 
 function parameters(){
 	jQuery('div#parametres')
    .dialog({
    	modal:true, 
    	width:380,
    	position:'center',
    	buttons: { 
    			"Ok": function() {
    					jQuery('form#parametres').ajaxSubmit({
    						dataType:'json',
 							success:function(response){
 								jQuery('div#parametres').dialog("close");
 								document.location.reload();
 								}
 						});
    				},
    			"Annuler": function() { jQuery(this).dialog("close"); }
    	 		}
    	});
 }
 
 function ask(factureId){
 	if(factureId!=0){
 			jQuery('#order')
    		.dialog({modal:true, show:'slide',hide:'clip',width:350,
    			buttons: { "Envoyer": function() { 
    							var msg1=(jQuery('#msg1').val()!='')?jQuery('#msg1').val():'null';
    							var msg2=(jQuery('#msg2').val()!='')?jQuery('#msg2').val():'null';
    							print_bon(factureId,0,msg1,msg2,'');
    							confirm_order(factureId);
    							jQuery(this).dialog("close");
    							jQuery('#msg1').val('');
    							jQuery('#msg2').val('');
    							 },
    					/*
    					"ou toutes.": function(){ 
    							var msg1=(jQuery('#msg1').val()!='')?jQuery('#msg1').val():'null';
    							var msg2=(jQuery('#msg2').val()!='')?jQuery('#msg2').val():'null';
    							print_bon(factureId,1,msg1,msg2,'');
    							confirm_order(factureId);
    								jQuery(this).dialog("close");
    								jQuery('#msg1').val('');
    								jQuery('#msg2').val('');
    							}
    					//*/
    					}
 					});
 	}
 	else {
 		alert('Sélectionné une facture!')
 	}
 }
 function print_bon(factureId,force,msg1,msg2,consoId){
 	//printing bon for barman
 	jQuery.ajax({ //ajax call for testing so that we don't print empty bon'
 		type:'GET',
 		url:getBase()+'ventes/bon_tester/'+factureId+'/boissons/'+force+'/'+true+'/'+msg1+'/'+consoId,
 		dataType:'json',
 		success:function(ans){
 			var timeout=false;
 			if(ans.boissons){
 				//printing bon for barman
 				url=getBase()+'ventes/print_bon/'+factureId+'/boissons/'+force+'/'+false+'/'+msg1+'/'+consoId;
 				print_facture(factureId,url,'_boissons',false,true);
 				
 				timeout=true;
 			}
 			
 			if(ans.plats){
 				//print bon of plats
 				if(timeout){
 					setTimeout(function(){
				//sans le retardement ca va imprimer seulement les plats
 						url=getBase()+'ventes/print_bon/'+factureId+'/plats/'+force+'/'+false+'/'+msg2+'/'+consoId;
 						print_facture(factureId,url,'_plats',false,true);
 					},
					3000
					); 
				}
				else {
					url=getBase()+'ventes/print_bon/'+factureId+'/plats/'+force+'/'+false+'/'+msg2+'/'+consoId;
 					print_facture(factureId,url,'_plats',false,true);
				}
 			}
 			//marking printed to all consos
 			jQuery('table#list_produits tr:not(tr:first)').each(function(i){
 				jQuery(this).attr('printed',1);
 			});	
 		}
 	});
 	
 }
 
 function print_facture(factureId,url,filename,disable,moveOn){
 	if(factureId!=0){
 		url=(url==undefined)?getBase()+'ventes/print_facture/'+factureId:url;
 		disable=(disable==undefined)?true:disable;
 		filename=(filename==undefined)?'_facture':filename;
 		var goOn=true;
 		if((jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed')=='1')
 			&&(filename=='_facture')
 			&&(jQuery('div#pos').attr('printonce')=='1')
 		){
			if((jQuery('div#pos').attr('touch')=='on')&&(moveOn==undefined)){ 	

 				goOn=false;
 			
 			 	jQuery('div#annulation').dialog({
  		 			modal:true, 
    				width:300,
    				position:'top',
	    			buttons: { 
	    				"Annuler": function() { jQuery(this).dialog("close"); 
	    										goOn=false;
	    						}
	    			}
	    		}).attr('action','print');

	    	}
	    	else if(jQuery('div#pos').attr('touch')!='on'){
	    		if(jQuery.inArray(jQuery('div#pos').attr('fonction'),['2','4'])==-1){
	    			goOn=true;
	    		}
	    		else {
	    			goOn=false;
	    			alert("Vous n'avez pas le droit d'imprimer plus d'une fois la même facture!");	
	    		}
	    	}

 		}
 		if((jQuery('#pos').attr('fonction_id')==1)&&(jQuery('#pos').attr('multi_serveur')==0)){
 			var serveur_name=jQuery('table#list_factures tr[id="'+factureId+'"] td[id="waiter"]').text();
 			var current_serveur_name=jQuery('#pos').attr('serveur_name');
 			if(serveur_name!=current_serveur_name){
 				alert("Impossible d'imprimer une facture qui n'est pas la votre!");
 				goOn=false;
 			}
 		}
 		if(goOn){
 			if(jQuery('table#list_factures tr[id="'+factureId+'"]').attr('beneficiaire')=='oui'){
 				disable=false;
 			}
		  	jQuery('#indicator').text('Impression ...').show();
   			
   			jQuery('#printPage').attr('src',url);
   			jQuery('#printPage').bind("load",function() {  
   				frames["printPage"].focus();
   				
   				if(jQuery('#pos').attr('thermal')=='oui'){
    				printFrame(false,80,filename);
    			}
    			else {
    				printFrame(false,70,filename);
    			}
    			setTimeout(function(){
 					jQuery('#indicator').hide();
 					//disabling forbidden actions
 					if(disable){
 						jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 						jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed','1');
 						if(jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name')=='0'){
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text('cloturer');
 						}
 					}
 					jQuery('#indicator').text('Patientez ...');
					},
				500); 
    		});
 		}
 	}
 	else {
 		alert('Sélectionner une facture !');
 	}
 }
 var mode=''; var ref=''; var equi=''; var monnaie='';

 function paiement(factureId,moveOn){
 	if(factureId!=0){
 		var goOn=true;
 		if(moveOn==undefined){
 			if(confirm('Voulez vous vraiment classer cette facture ?')){
 				goOn=true;
 			}
 			else {
 				goOn=false;
 			}
 		}
 		if(goOn&&((payed==1)||(payed==4))&&(moveOn==undefined)){
 			jQuery('#monnaie option[value="EUR"]').show();
 			jQuery('#pyt_boxe').dialog({
  	 			modal:true, 
    			width:400,
    			position:'top',
    			buttons: { 
    				"Enregistrer": function() {
    					goOn=true;
    					 mode=jQuery('#mode').val();
    					 ref=jQuery('#ref').val();
    					 equi=jQuery('#equi').val();
    					 if(equi!=''){
    					 	 monnaie=jQuery('#monnaie').val();
    					 }
     					jQuery(this).dialog('close');
    					paiement(factureId,true);
    				}
    			}
   			});
   			goOn=false;
   		}
 		if(goOn){
 			var newClientName=jQuery('#tierId  option:selected').html();
 			var newClientId=jQuery('#tierId').val();
 		
 			var oldClientId=(jQuery('span#client').attr('name')!=undefined)?jQuery('span#client').attr('name'):'';
 			var oldClientName=jQuery('span#client').text();
 			if((newClientId!=0)&&(newClientId!=oldClientId)){
 				var clientName=newClientName;
 				var clientId=newClientId;
 				var reduction=-1;//means search for reduction
 			}
 			else {
 				var clientName=oldClientName;
 				var clientId=oldClientId;
 				var reduction=jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text();
 			}
	 		
 			var avance=0;
 			var etat='';
 			if(payed==1){ 
 				avance=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text());
 				etat='payee';
 			}
 			else if(payed==2){ 
 				avance=0;
 				jQuery('input#avance').val(0);
 				etat='credit';
 			}
 			else if(payed==3){
 				avance=0;
 				etat='bonus';
 			}
 			else {
 				etat='avance';
  				avance=parseInt(jQuery('input#avance').val());
  			}
  			etat=((avance==0)&&(payed==4))?'credit':etat;
 				jQuery.ajax({
 					type:'POST',
 					url:getBase()+'ventes/paiement',
 					data:{'data[Vente][factureId]':factureId,
 						'data[Vente][avance]':avance,
 						'data[Vente][tier_id]':clientId,
 						'data[Vente][etat]':etat,
 						'data[Vente][montant]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(),
 						'data[Vente][original]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(),
 						'data[Vente][date]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="date"]').text(),
 						'data[Vente][journal_id]':jQuery('table#list_factures tr[id="'+factureId+'"]').attr('journal'),
 						'data[Vente][reduction]':reduction,
 						'data[Paiement][mode_paiement]':mode,
 						'data[Paiement][reference]':ref,
 						'data[Paiement][montant_equivalent]':equi,
 						'data[Paiement][monnaie]':monnaie,
 						},
 					dataType:'JSON',
 					success:function(response){
 		//			jQuery('body').html(response);
 						if(response.success) {
 							//updating the bill row view
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text(response.etat);
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(response.montant);
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(response.original);
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(response.reduction);
 							jQuery('span#montant').text(response.total);
 							jQuery('span#reste').text(response.reste);
 							jQuery('input#avance').val(response.total-response.reste);
 							
 							//disabling some actions
 							jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 							jQuery('span[name="classer"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 							jQuery('span[name="annuler"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 							jQuery('span[name="separator"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 							jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name','1');
 							
 							//updating client
 							jQuery('#client').text(clientName);
 							jQuery('#client').attr('name',response.tierId);
 							
 							//reseting
 							jQuery('#tierId').val(0);
 							mode=equi=monnaie=ref='';
 							jQuery('#monnaie').val('USD').attr('disabled','disabled');
 							jQuery('#mode').val('cash');
 							jQuery('#ref').val('');
 							jQuery('#equi').val('');
 						}
 						else {
 							alert(response.msg)
 						}
 					}
 				})
 			}
 	}
 	else {
 		alert('Sélectionner une facture !');
 	}
 }
 
 function conso_activated(tr){
 	jQuery('table#list_produits td.active').removeClass('active');
 	jQuery(tr).children().attr('class','active');
 	consoId=jQuery(tr).attr('id');
 	var id=jQuery(tr).children('td[name="produit"]').attr('id');
 	jQuery('select#produits  option[value="'+id+'"]').attr('selected','selected');
 	jQuery('select#produits').focus();
 	
 	//checkbox activation if any
 	if(jQuery(tr).children('td[name="check"]').children().attr('checked')){
 		jQuery(tr).children('td[name="check"]').children().removeAttr('checked');
 	}
 	else {
 		jQuery(tr).children('td[name="check"]').children().attr('checked','checked');
 	}
 }
 
 function facture_removal(factureId,consoId,moveOn){
 //*
 	var printed=1;
 	if(jQuery('table#list_produits tr[id="'+consoId+'"]').length==1){
 		printed=parseInt(jQuery('table#list_produits tr[id="'+consoId+'"]').attr('printed'));
 	}
 	var goOn=true;
 	if(factureId==0){
 		alert('Sélectionné une facture!');
 		goOn=false;
 	}
 	else if((moveOn==undefined)&&(consoId=='facture')){
 		goOn=confirm('Voulez vraiement annuler cette facture ?');
 	}
 	
 	if((printed>=1)&&(moveOn==undefined)&&(goOn)){
 		 if(jQuery('div#pos').attr('touch')=='on'){
 		 	
 		 	var action=(consoId=='facture')?'effacer':'effacer_conso';
 		 	goOn=false;
 		 	jQuery('div#annulation').dialog({
  	 			modal:true, 
    			width:300,
    			position:'top',
    			buttons: { 
    				"Annuler": function() { jQuery(this).dialog("close"); 
    										goOn=false;
    									}
    					}
    		}).attr('action',action);
 		 } 
 		 else {
 		 	goOn=true;
 		 }
 	}

 if(goOn){
 		var obs='';
 		if((consoId=='facture')||(printed>0)){ //le motif est requis seuleemnt lors de l'annulation de la facture.
 			obs=prompt('Veuillez mentioné le motif de l\'annulation ');
 		}
 		if(((obs=='')||(obs==undefined)||(obs==null))&&(consoId=='facture')){
 			alert('le motif est obligatoire!');
 		}
 		else {
 			var reduction=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text());
 			var quantite=parseInt(jQuery('#VenteQuantite').val());
 			jQuery.ajax({
 			type:'GET',
 			dataType:'json',
 			url:getBase()+'ventes/removal/'+factureId+'/'+consoId+'/'+quantite+'/'+reduction+'/'+obs,
 			success:function(response){
 			if(response.success){
 				if(consoId=='facture'){
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text('annulee');
 					//disabling forbidden actions
 					jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('span[name="classer"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('span[name="annuler"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name','1');
 					
 					//commande d'annulation'
 				//	print_bon(factureId,1,'COMMANDE ANNULEE','COMMANDE ANNULEE','');
 				
 					//update table state
 					if(jQuery('div#pos').attr('touch')=='on'){
 						table_state(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="table"]').text());
 					}
 					factureId=0;
 					consoId=0;
 					
 					//updating the quantity in produit full name
 					jQuery('table#list_produits tr:not(:first)').each(function(){
 					  		var produit_id=jQuery(this).children('td[name="produit"]').attr('id');
 					  		var quantite=parseInt(jQuery(this).children('td[id="quantite_vente"]').text());
 					  		var full_name=jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 					  		.html();
 					  		if((jQuery('div#pos').attr('touch')=='off')&&(full_name.split('_').length==3)){
 					  			var new_quantite=parseInt(full_name.split('_')[1])+quantite;
 					  			jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  				.html(full_name.split('_')[0]+'_'+new_quantite+'_'+full_name.split('_')[2]);
 				  			}
 				  			
 					});
 					
 					//table state
 					table_state(tableNum);
 				}
 				else if(consoId!=0) {
 						var montant=response.montant;
 						var produit_id=jQuery('table#list_produits tr[id="'+consoId+'"] td[name="produit"]').attr('id');
	 				  	var produitName=jQuery('table#list_produits tr[id="'+consoId+'"] td[name="produit"]').text();
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(response.original);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(montant);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(response.reste);
						jQuery('span#montant').text(montant);
						jQuery('#reste').text(response.reste);
						
						//commande d'annulation'
	 				  	var printedQty=parseInt(jQuery('table#list_produits tr[id="'+consoId+'"]').attr('printed'));
	 				  	var section=jQuery('table#list_produits tr[id="'+consoId+'"]').attr('section');
	 				  	
	 				  	if(printedQty>=quantite){
 					//		print_bon(factureId,0,'COMMANDE ANNULEE','COMMANDE ANNULEE',quantite+'_'+produitName+'_'+section);
 						}
 						
 						
						if(response.quantite==0){
							jQuery('table#list_produits tr[id="'+consoId+'"]').remove();
						}
						else {
							jQuery('table#list_produits tr[id="'+consoId+'"] td[id="quantite_vente"]').text(response.quantite);
							jQuery('table#list_produits tr[id="'+consoId+'"] td[id="prix"]').text(response.PT);
						}
						jQuery('#VenteQuantite').val(1);
						//updating the quantity in produit full name
 				  		var full_name=jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  		.html();
 				  		if((full_name!=undefined)&&(full_name.split('_').length==3)){
 				  			var new_quantite=parseInt(full_name.split('_')[1])+quantite;
 				  			jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  			.html(full_name.split('_')[0]+'_'+new_quantite+'_'+full_name.split('_')[2]);
 				  		}
 				}
 				else {
 					alert('Sélectionner un produit !');
 				}
 			}
 			else { alert(response.msg);}
 			}
	 		});
	 	}
	}
 }
 
 function activated(tr){
 	jQuery('.active').removeClass('active');
 	jQuery(tr).children().attr('class','active');
 	factureId=jQuery(tr).attr('id');
 	consoId=0;
 	ungrouped=(parseInt(factureId)!=ungrouped)?0:ungrouped;
 	
 	var classee=jQuery(tr).attr('name');
 	var printed=jQuery(tr).attr('printed');
 	var beneficiaire=jQuery(tr).attr('beneficiaire');
 	if(beneficiaire!=''){
 		jQuery('span#ben').text(beneficiaire);
 		jQuery('#VentePourcentage').removeAttr('disabled');
 	}
 	else {
 		jQuery('span#ben').text('');
 		jQuery('#VenteBeneficaire').val('');
 		jQuery('#VentePourcentage').attr('disabled','disabled');
 	}
 	if(classee=='1'){
 		jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 		jQuery('span[name="classer"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 		jQuery('span[name="annuler"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 	}
 	else if(printed=='1'){
 		jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 		jQuery('span#paiement_facture').unbind('click').bind('click',function(){paiement(factureId);}).attr('class','boutton');
 		//jQuery('span#separator').unbind('click').bind('click',function(){separator(factureId);}).attr('class','boutton');
 		//jQuery('span#separator').attr('class','boutton_disabled').unbind('click');
 		jQuery('span#paiement_facture_touch').unbind('click').bind('click',function(){paiement_touch(factureId);}).attr('class','boutton');
 		jQuery('span#remove_facture').unbind('click').bind('click',function(){facture_removal(factureId,'facture');}).attr('class','boutton');
 	}
 	else {
 		binder();
 	}

 	//list produit fetching ...
 	jQuery.ajax({
 		type:'GET',
 		url:getBase()+'ventes/list_produits/'+factureId,
 		success:function(response){
 			jQuery('table#list_produits tr:not(tr:first)').remove();
 			jQuery(response).insertAfter('table#list_produits tr:first');
 		}
 	});
 	
 	//updating facture details
 	jQuery.ajax({
 		type:'GET',
 		url:getBase()+'ventes/activated/'+factureId,
 		dataType:'json',
 		success:function(ans){
 			vente_details(factureId,
 						ans.Facture.numero,
						ans.Facture.montant,
						ans.Facture.reste,
						ans.avance,
						ans.Facture.observation,
						ans.Facture.beneficiaire,
						ans.Tier.id,
						ans.Tier.name,
						ans.Facture.matricule,
						ans.Facture.liasse,
						ans.Facture.employeur
					);
 			//updating facture montant necessary in sama mode
 			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(ans.Facture.montant);
 			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(ans.Facture.original);
 			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(ans.Facture.reduction);
 			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(ans.Facture.reste);
 			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text(ans.Facture.etat);
 			jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name',ans.Facture.classee);
 			jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed',ans.Facture.printed);
 		}
 	});
 	
 
 }
 
 
 
 function addFacture(id,factureNum,journal,beneficiaire,table,original,reduction,montant,reste,etat,serveur,date){
 	if(jQuery('table#list_factures tr[id="'+id+'"]').length==0){
 		var tr='<tr id="'+id+'"  onclick="activated(this)" name="0" beneficiaire="'+beneficiaire+'" journal="'+journal+'">';
 		tr=tr+'<td id="num">'+factureNum+'</td>';
 		if(jQuery('#pos').attr('magasin')==0){
 			tr=tr+'<td id="table">'+table+'</td>';
 		}
 		tr=tr+'<td id="original">'+original+'</td>';
 		tr=tr+'<td id="reduction">'+reduction+'</td>';
 		tr=tr+'<td id="montant">'+montant+'</td>';
	
	 	if(jQuery('#pos').attr('touch')=='on'){
	 		tr=tr+'<td id="reste">'+reste+'</td>';
	 	}
 		tr=tr+'<td id="etat">'+etat+'</td>';
 		if(jQuery('#pos').attr('magasin')==0){
 			tr=tr+'<td id="waiter">'+serveur+'</td>';
 		}
 		tr=tr+'<td id="date">'+date+'</td>';
 		tr=tr+'</tr>';
 		jQuery('.active').removeClass('active');
 		jQuery(tr).insertAfter('table#list_factures tr:first').children().attr('class','active');
 		ungrouped = 0;
 	}
 }
 
 function facture_row(id,factureNum,journal,beneficiaire,table,original,reduction,montant,reste,etat,serveur,date_mysql){
	//cleaning produit table
	jQuery('table#list_produits tr:not(tr:first)').remove();
	
	//adding facture
 	addFacture(id,factureNum,journal,beneficiaire,table,original,reduction,montant,reste,etat,serveur,date_mysql)
 	
 	//registering selected facture
 	factureId=id;
 	
 	//binding things
 	binder();
 }
 
 function vente_row(pourcentage,produit_id,produit,quantite,PU,PT,venteId,printed){
 	var tr='<tr id="'+venteId+'" onclick="conso_activated(this)" printed="'+printed+'">';
	tr=tr+'<td name="check"><input type="checkbox" name="case" value="'+venteId+'"/></td>';
 	if(jQuery('#pos').attr('beneficiaires')=='1'){
 		tr=tr+'<td>'+pourcentage+'</td>';
 	}
 	
 	tr=tr+'<td name="produit" id="'+produit_id+'">'+produit+'</td>';
 	tr=tr+'<td id="quantite_vente">'+quantite+'</td>';
 	tr=tr+'<td>'+PU+'</td>';
 	tr=tr+'<td id="prix">'+PT+'</td>';
  tr=tr+'<td id="created_time"></td>';
 	tr=tr+'</tr>';
 	if(jQuery('table#list_produits tr[id="'+venteId+'"]').length!=0){
 		var quantite_vente=parseInt(jQuery('table#list_produits tr[id="'+venteId+'"] td[id="quantite_vente"]').text());
 		var total=parseInt(jQuery('table#list_produits tr[id="'+venteId+'"] td[id="prix"]').text());
 		jQuery('table#list_produits tr[id="'+venteId+'"] td[id="quantite_vente"]').text(parseInt(quantite)+quantite_vente);
 		
 		jQuery('table#list_produits tr[id="'+venteId+'"] td[id="prix"]').text(total+PT);
 		jQuery('table#list_produits tr[id="'+venteId+'"]').attr('printed',printed);
 	}
 	else {
 		jQuery(tr).insertAfter('table#list_produits tr:first');
 	}
 }
 
 function vente_details(factureId,factureNum,montant,reste,avance,observation,beneficiaire,clientId,client,matricule,liasse,employeur){
 	jQuery('#client').text(client);
 	jQuery('#client').attr('name',clientId);
 	jQuery('#facture').html('<a href="'+getBase()+'factures/view/'+factureId+'">'+factureNum+'</a>');
 	jQuery('span#ben').text(beneficiaire);
 	jQuery('span#matricule').text(matricule);
 	jQuery('span#liasse').text(liasse);
 	jQuery('span#employeur').text(employeur);
 	jQuery('span#montant').text(montant);
 	jQuery('input#avance').val(avance);
 	jQuery('span#reste').text(reste);
 	jQuery('#VenteObservation').val(observation);
 }
 
 function resto_create(factureId){
 	var quantite=jQuery('#VenteQuantite').val();
 	var pourcentage=jQuery('#VentePourcentage').val();
 	var produit_id=jQuery('select#produits  option:selected').val();
 	var produit=jQuery('select#produits  option:selected').html().split('_')[0];
 	//accompagnement
 	
 	var garnishId=jQuery('select#VenteGarnish option:selected').val();
 	var garnishName=jQuery('select#VenteGarnish option:selected').html();
 	if((garnishName!=null)&&(garnishName!='')){
 		produit=produit+' ('+garnishName+')';
 	}
 	// detailed beneficiaire
 	
 	var matricule=jQuery('#VenteMatricule').val();
 	var liasse=jQuery('#VenteLiasse').val();
 	var employeur=jQuery('#VenteEmployeur').val();
 	
 	var PU='';
  var facture_numero = null;
 	var clientId=null;
 	var client='';
 	var table=null;
 	var serveur='';
 	var serveurId=null;
	if(factureId=='creation'){
 		clientId=(jQuery('#tierId').val()!=0)?jQuery('#tierId').val():null;
 		client=jQuery('#tierId option:selected').html();
 		serveur=jQuery('#VentePersonnelId  option:selected').html();
 		serveurId=jQuery('select#VentePersonnelId option:selected').val();
 		table=jQuery('#VenteTable').val();
 		PU=jQuery('#VentePU').val();
    facture_numero=jQuery('#facture_numero').val();
 		var reduction=-1;
 		var beneficiaire=jQuery('#VenteBeneficiaire').val();
 	}
 	else {
 		//alert(jQuery('span#client').attr('name'));
 		clientId=(jQuery('span#client').attr('name')!=undefined)?parseInt(jQuery('span#client').attr('name')):null;
 		PU=jQuery('#VentePU').val();
 		var beneficiaire=jQuery('span#ben').text();
 		var reduction=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text());
 	}
 	if((beneficiaire!='')&&(pourcentage=='')){
 		alert('Le Pourcentage n\'est pas spécifié !');
 	}
 	else if(quantite==''){
 		alert('La quantité n\'est pas spécifiée !');
 	}
 	else if((table=='')&&(factureId=='creation')){
 		alert('La table n\'est pas spécifiée !');
 	}
 	else if((serveurId==0)&&(factureId=='creation')){
 		alert('Le serveur n\'est pas spécifié !');
 	}
 	else {
 	//	alert(clientId);
 		jQuery('#vente_add').ajaxSubmit({
			data:{ 'data[Vente][quantite]':quantite,
				'data[Vente][tier_id]':clientId,
				'data[Vente][serveur_id]':serveurId,
				'data[Vente][table]':table,
				'data[Vente][factureId]':factureId,
				'data[Vente][reduction]':reduction,
				'data[Vente][PU]':PU,
				'data[Vente][beneficiaire]':beneficiaire,
				'data[Vente][pourcentage]':pourcentage,
				'data[Vente][acc_id]':garnishId,
				'data[Vente][matricule]':matricule,
				'data[Vente][liasse]':liasse,
				'data[Vente][employeur]':employeur,
        'data[Vente][facture_numero]':facture_numero
			},
			dataType:'json',
			success:function(r){
				if(r.success){
					if(beneficiaire==''){
						jQuery('#VentePourcentage').attr('disabled','disabled');
					}
					if(factureId=='creation'){
						facture_row(r.factureId,r.factureNum,r.journal,beneficiaire,table,r.original,r.reduction,r.montant,0,'en_cours',serveur,r.date);
					    vente_row(pourcentage,produit_id,produit,quantite,r.PU,r.PT,r.consoId,r.printed);
						vente_details(r.factureId,r.factureNum,r.montant,r.reste,r.avance,'',beneficiaire,clientId,client,matricule,liasse,employeur);
						consoId=0;
						// resetting the serveur 
						jQuery('#VentePersonnelId option[value="0"]').attr('selected','selected');
						jQuery('#tierId option[value="0"]').attr('selected','selected');
						jQuery('#VenteBeneficiaire').val('');
						jQuery('#VenteMatricule').val('');
						jQuery('#VenteLiasse').val('');
						jQuery('#VenteEmployeur').val('');
					}
					else {
						vente_row(pourcentage,produit_id,produit,quantite,r.PU,r.PT,r.consoId,r.printed);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(r.montant);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(r.original);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(r.reduction);
						jQuery('span#montant').text(r.montant);
						jQuery('input#avance').val(r.avance);
 	        	        jQuery('#reste').text(r.reste);
 	        	        consoId=0;
					}
					//resetting the form
 					jQuery('#VenteQuantite').val(1);
 					jQuery('#VentePourcentage').val('');
 					jQuery('#VenteGarnish').val(0);
 					
 					//updating the quantity in produit full name
 					var full_name=jQuery('select#produits  option:selected').html();
 					if(full_name.split('_').length==3){
 					  	var new_quantite=parseInt(full_name.split('_')[1])-parseInt(quantite);
 					  	new_quantite=(new_quantite<0)?0:new_quantite;
 					  	jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 					  	.html(full_name.split('_')[0]+'_'+new_quantite+'_'+full_name.split('_')[2]);
 					}
				}
				else if(r.choix!=undefined){
					journal_msg(r);
				}
				else {
					alert(r.msg)
				}
    		},
  		});
  	}
 }
 /* end of resto ajax */
 
  function printFrame(shrink,scale,filename) {
  //print size 
	jsPrintSetup.setOption('shrinkToFit',shrink);
	jsPrintSetup.setOption('scaling',scale);

	// set printer 
	var first=jQuery('div#printers').attr('first');
	var second=jQuery('div#printers').attr('second');
	var third=jQuery('div#printers').attr('third');
	var list=jsPrintSetup.getPrintersList().split(',');
	
	if(!((first==second)&&(second==third))){
		if(filename!='_facture'){
			if(second!=third){
				if(filename=='_boissons'){
					var barmanList=jQuery('div#printers').attr('second').split(',');
					jQuery.each(barmanList,function(i,val){
						if(jQuery.inArray(val,list)>-1){
							jsPrintSetup.setPrinter(val);
						}
		 			});
				}
				else {
					var cuisinierList=jQuery('div#printers').attr('third').split(',');
					jQuery.each(cuisinierList,function(i,val){
						if(jQuery.inArray(val,list)>-1){
							jsPrintSetup.setPrinter(val);
						}
		 			});
				}
			}
			else {
				jsPrintSetup.setPrinter(jQuery('div#printers').attr('second'));	
			}
		}	
		else {
			var caissierList=jQuery('div#printers').attr('first').split(',');
					jQuery.each(caissierList,function(i,val){
						if(jQuery.inArray(val,list)>-1){
							jsPrintSetup.setPrinter(val);
						}
		 			});
		}
	}
   // set page header
   var ran=Math.random();
   jsPrintSetup.setOption('headerStrLeft', '');
   jsPrintSetup.setOption('headerStrCenter', '');
   jsPrintSetup.setOption('headerStrRight', '');
   // set empty page footer
   jsPrintSetup.setOption('footerStrLeft', '');
   jsPrintSetup.setOption('footerStrCenter', '');
   jsPrintSetup.setOption('footerStrRight', '');
   jsPrintSetup.setOption('outputFormat', 'kOutputFormatPDF');
   jsPrintSetup.setOption('toFileName', '/home/mugabo/Desktop/facture'+filename+'_'+ran+'.pdf');
   // clears user preferences always silent print value
   // to enable using 'printSilent' option
   jsPrintSetup.clearSilentPrint();
   // Suppress print dialog (for this context only)
   jsPrintSetup.setOption('printSilent', 1);
   jsPrintSetup. setShowPrintProgress(false);
   // Do Print 
   // When print is submitted it is executed asynchronous and
   // script flow continues after print independently of completetion of print process! 
   // next commands

	// print desired frame
   jsPrintSetup.printWindow(window.frames["printPage"]);
}

function aserPrint(shrink,scale,printer){   
	
	//print size 
	jsPrintSetup.setOption('shrinkToFit',shrink);
	jsPrintSetup.setOption('scaling',scale);
	
	// set portrait orientation
 //*
   jsPrintSetup.setOption('orientation', 'kLandscapeOrientation');
   // set top margins in millimeters
   jsPrintSetup.setOption('marginTop', 10);
   jsPrintSetup.setOption('marginBottom', 10);
   jsPrintSetup.setOption('marginLeft', 0);
   jsPrintSetup.setOption('marginRight', 0);
   
  //*/
   // set page header
   jsPrintSetup.setOption('headerStrLeft', '');
   jsPrintSetup.setOption('headerStrCenter', '');
   jsPrintSetup.setOption('headerStrRight', '');
   // set empty page footer
   jsPrintSetup.setOption('footerStrLeft', '');
   jsPrintSetup.setOption('footerStrCenter', '');
   jsPrintSetup.setOption('footerStrRight', '');
   jsPrintSetup.setOption('outputFormat', 'kOutputFormatPDF');
   jsPrintSetup.setOption('toFileName', '/home/mugabo/Desktop/document.pdf');
   // clears user preferences always silent print value
   // to enable using 'printSilent' option
   jsPrintSetup.clearSilentPrint();
   // Suppress print dialog (for this context only)
   jsPrintSetup.setOption('printSilent', 1);
   jsPrintSetup. setShowPrintProgress(false);
   // Do Print 
   // When print is submitted it is executed asynchronous and
   // script flow continues after print independently of completetion of print process! 
   jsPrintSetup.print();
   // next commands
}

function facturation(form){
	if((jQuery('form[name="'+form+'"] input[type="checkbox"]:checked').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
		var	info=jQuery('form[name="'+form+'"]').attr('id');
		info=info.split('_');//to get the model and the controller
    	var url=getBase()+info[1]+'/facturation';
   		jQuery('form[name="'+form+'"]').ajaxSubmit({
			beforeSend:function(){ jQuery('#facturation').show('fade');
						},
			complete:function(){ jQuery('#facturation').hide('fade');},
			url:url,
			success:function(response){ var content=jQuery('body').html();
										jQuery('body').html(response);
										jQuery('body').attr('class','body_printing');
										jQuery('#print').click(function(){
											jQuery('button').remove();
											aserPrint();
											jQuery('body').html(content);
										});
										jQuery('#cancel_print').click(function(){
											jQuery('body').html(content);
										});
										}
		});
	}
	
}
/* stuff regarding resizing ! */
function resizer(){
	var state=0;
	var step=0;
	var reservation_id=0;
	var position=0;
	var row=0;
	var cellTotal=0;
	var counter=0;
    var month=0
    var year=0
    var days=0
	jQuery('td.confirmee,td.en_attente,td.arrivee')
	.resizable({
		handles: 'e',
		ghost:true,
		start:function(event,ui){
		   reservation_id=jQuery(this).attr('reservation');
           position=parseInt(jQuery(this).attr('position'));
     	   row=jQuery(this).parents('tr').attr('name');
	       cellTotal=jQuery(this).attr('colspan');	
		   state=jQuery(this).width();
		   step=Math.round(state/cellTotal);
           month=parseInt(jQuery('#title').attr('month'));
           year=jQuery('#title').attr('year');
           days=parseInt(jQuery('#title').attr('days'));
		},
		resize:function(event,ui){
			if(ui.size.width>(state+step)){
				if(jQuery('tr[name="'+row+'"] td[position="'+(position+cellTotal)+'"]').attr('class')==''){
					jQuery('#taille').text('a step forward ... ('+counter+')')
					jQuery('tr[name="'+row+'"] td[position="'+(position+cellTotal)+'"]').remove();
					jQuery(this).attr('colspan',cellTotal+1)
				}
				else { jQuery(this).resizable( "option", "maxWidth", jQuery(this).width());
				//	alert(jQuery('tr[name="'+row+'"] td[position="'+(position+cellTotal)+'"]').length)
				}
				counter++;
				cellTotal++;
				state=state+step-counter;
			}
			if(ui.size.width<(state-step)) {
				jQuery(this).attr('colspan',cellTotal-1)
				jQuery('#taille').text('a step forward ... ('+counter+')');
				jQuery('<td onmouseover="mouseover(this)" onmousedown="mousedown(this)" numero="'+(position+cellTotal-1)+'" position="'+(position+cellTotal-1)+'">&nbsp;</td>')
    			.insertAfter(this);
				state=state-step+1;
				counter--;
				cellTotal--;
				jQuery('#taille').text('a step backward ... ('+counter+')')
			}
			
		},
		stop:function(event,ui){
			jQuery(this).width(10)
			var updateBooking='yes';
			var end=parseInt(jQuery(this).attr('colspan'))+position;
			var depart=year+'-'+formater(month)+'-'+formater(end);
			var tier=jQuery(this).text();
			cell=this;
			jQuery.ajax({
				type:'GET',
		 		url:getBase()+'reservations/departure_changer/'+reservation_id+'/'+depart+'/'+row+'/'+updateBooking,
		 		beforeSend:function(){ jQuery(cell).text('Actualisation...')},
		 		complete:function(){ jQuery(cell).text(tier)},
		 		dataType:'json',
				success:function(response){
				//	jQuery('body').html(response)
					if(response.success){
						jQuery('td[name="occupation"]').html('<span id="loading">Actualisation...</span>');
    		    	    jQuery('tbody#occupation').load(getBase()+'reservations/occupation/'+days+'/'+month+'/'+year);
    		    	}
    		    	else {
    		    		alert(response.msg);
    		    	}
   				}
		 	})
		}
	})
}


/*Stuff regarding Dragging 'n dropping ! cool stuff :D 
var dropped=false;
function dragger(){
	
	jQuery( 'td.confirmee,td.en_attente,td.arrivee')
	.draggable({disabled: false ,
				addClasses:false,
				cursor:'move',
				helper:'clone',
				opacity: 0.35,
				revert: 'invalid',
				zIndex: 12700,
				snap: false,
				distance:30,
				start:function(event,ui){
				
					dropped=false;
					var position=parseInt(jQuery(ui.helper).attr('position'));
					var cellTotal=jQuery(ui.helper).attr('colspan');
					var row=jQuery(ui.helper).parents('tr').attr('name');
					var type_row=jQuery(ui.helper).parents('tr').attr('type');
					jQuery('tr[type="'+type_row+'"]').each(function(){
						var test=true;
						var current_row=jQuery(this).attr('name');
						if((current_row!='other')&&(current_row!=row)){
    							for(var i=position; i<position+cellTotal; i++){
    								alert(jQuery('tr[name="'+current_row+'"] td[numero="'+i+'"]').attr('class'))
    								if(jQuery('tr[name="'+current_row+'"] td[numero="'+i+'"]').attr('class')!=''){
    									test=false;
    									break;
    									alert('test false')
    								}
    							}  
							
  					  	
  						  	if(test){
    							for(var i=position+1; i<position+cellTotal; i++){
    								jQuery('tr[name="'+current_row+'"] td[numero="'+i+'"]').remove();
    							}  
    							jQuery('tr[name="'+current_row+'"] td[numero="'+position+'"]')
    							.attr('colspan',cellTotal)
    							.attr('class','selection')
    							.attr('position',position)
  					  		}
  					  }
									 		
					})
					dropper();
				},
				stop:function(event,ui){ 
						if(!dropped){
 							  	jQuery('td.selection').each(function(){ 
									var position=parseInt(jQuery(this).attr('position'))-1;
									var selection_row=jQuery(this).parents('tr').attr('name');
									var cellTotal=jQuery(this).attr('colspan');
    								jQuery(this).remove();
    								for(i=0;i<cellTotal;i++){
    									jQuery('<td onmouseover="mouseover(this)" onmousedown="mousedown(this)" numero="'+(position+1)+'">&nbsp;</td>')
    									.insertAfter('tr[name="'+selection_row+'"] td[numero="'+position+'"]');
    									position++;
    								}
    							})
						}
				}
	});
}
function dropper(){
	jQuery( 'td.selection' )
	.droppable({disabled: false ,
				addClasses:false,
				accept: 'td.confirmee,td.en_attente,td.arrivee',
			//	activeClass:'selection',
				greedy:true,
				hoverClass:'hovered',
				tolerance:'intersect',
				drop:function(event,ui){
					dropped=true;
					var reservation_id=jQuery(ui.draggable).attr('reservation');
					var position=parseInt(jQuery(ui.draggable).attr('position'))-1;
					var old_row=jQuery(ui.draggable).parents('tr').attr('name');
					var new_row=jQuery(this).parents('tr').attr('name');
					var cellTotal=jQuery(ui.draggable).attr('colspan');
					var droppable=this;
													
		 		 	jQuery.ajax({
						type:'GET',
						url:getBase()+'reservations/room_changer/'+reservation_id+'/'+old_row+'/'+new_row,
						dataType:'json',
						success:function(ans){
							if(ans.success){
								jQuery(droppable).replaceWith(ui.draggable)
   								for(i=0;i<cellTotal;i++){
    								jQuery('<td onmouseover="mouseover(this)" onmousedown="mousedown(this)" numero="'+(position+1)+'">&nbsp;</td>')
    								.insertAfter('tr[name="'+old_row+'"] td[numero="'+position+'"]');
    			 				   	position++;
  							  	}
 							  	jQuery('td.selection').each(function(){
									var position=parseInt(jQuery(this).attr('position'))-1;
									var selection_row=jQuery(this).parents('tr').attr('name');
									var cellTotal=jQuery(this).attr('colspan');
    								jQuery(this).remove();
    								for(i=0;i<cellTotal;i++){
    									jQuery('<td onmouseover="mouseover(this)" onmousedown="mousedown(this)" numero="'+(position+1)+'">&nbsp;</td>')
    									.insertAfter('tr[name="'+selection_row+'"] td[numero="'+position+'"]');
    									position++;
    								}
    							})
    						}
    				}
			})
		}
	});
}
/*
 * Stuff regarding interactive display on the occupation table of bookings (Hotel module)
 */
var isdown = 0;
var times = 0;
var row;
var startingPoint;
var cellClass;
var class_regex = /^(confirmee|en_attente|partie|arrivee|changee|credit)$/; //I really love regex :D

function rightClick(){
	selector='td.confirmee,td.partie,td.en_attente,td.arrivee,td.changee,td.credit';
	jQuery(selector).contextMenu(
		{ menu: 'myMenu'},
		function(action, el, pos) {
			var reservation_id=jQuery(el).attr('reservation');
			var facture_id=jQuery(el).attr('facture');
			var tier_id=jQuery(el).attr('tier');
			var cellTotal=jQuery(el).attr('colspan');
			var state=jQuery(el).attr('class'); //state=class!
			var position=parseInt(jQuery(el).attr('position'))-1;
			var row=jQuery(el).parents('tr').attr('name');
    		var month=parseInt(jQuery('#title').attr('month'));
        	var year=jQuery('#title').attr('year');
        	var days=parseInt(jQuery('#title').attr('days'));
			switch(action){
				case 'demi':
					jQuery.ajax({
				 		type:'GET',
				 		dataType:'json',
				 		url:getBase()+'reservations/demi/'+reservation_id,
						success:function(ans){
							if(!ans.success){
    							jQuery('#demi_boxe').dialog({ modal:true, 	
    		             		  width:390,
    		             		  position:'top',
    		             		  buttons: { "Valider": function() {
    		             		  	 jQuery(this).dialog("close");
    		               				var tauxDemi=jQuery('#tauxDemi').val();
    		               				jQuery.ajax({
				 							type:'GET',
				 							dataType:'json',
				 							url:getBase()+'reservations/demi/'+reservation_id+'/'+0+'/'+tauxDemi,
											success:function(ans){
									 			alert(ans.msg);
									 		}
									 	});
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            					 }
    					           }
                       			});
    						}
    						else {
    							alert(ans.msg);	
    						}
    					},
				 	});
					break;
				case 'trace':
						if(jQuery('div#tabella').attr('type')!='locations')
							document.location=getBase()+'traces/index/'+reservation_id+'/Reservation';
						else 
							document.location=getBase()+'traces/index/'+reservation_id+'/Location';
					break;
				case 'rappels':
							document.location=getBase()+'rappels/index/'+reservation_id;
					break;
				case 'global':
					if((facture_id=='0')||(facture_id==undefined)){
						alert("La facture n'est pas encore créée !");
					}
					else {
						if(jQuery('div#tabella').attr('type')!='locations')
							document.location=getBase()+'reservations/facture_globale/'+facture_id;
						else 
							document.location=getBase()+'factures/view/'+facture_id+'/globale';
					}
					break;
				case 'proforma':
					document.location=getBase()+'factures/view/'+facture_id+'/proforma';
					break;
				
				case 'fact_loca':
					document.location=getBase()+'factures/view/'+facture_id;
					break;
				
				case 'PU':
					
				jQuery('#PU_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:390,
    		               position:'top',
    		               buttons: { "Changer": function() {
    		               			//validation stuff
    								var test=jQuery("#PU_form").validate({
 												rules:{
 												   	 'data[Reservation][pu]': {required:true,number:true},
 													}
											});
									if(test.form()){
    		               				var pu=jQuery('#pu').val();
    		               				var monnaie=jQuery('#currency').val();
    		               				jQuery.ajax({
											type:'GET',
								 			url:getBase()+'reservations/price_updater/'+reservation_id+'/'+pu+'/'+monnaie,
									 		beforeSend:function(){ jQuery('#message_PU').html('<span class="loading">Enregistrement ...</span>')},
									 		complete:function(){ jQuery('#message_PU').html('')},
											dataType:'json',
											success:function(response){
												//*
													jQuery('#PU_boxe').dialog('close');
													alert(response.msg);
											
											//	*/
   											},
									 	});
									 }
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            						jQuery('#message_PU').html('');
    					            					 }
    					           }
                       });
					
				 	break;
				case 'confirmation':
					document.location=getBase()+'reservations/confirmation/'+reservation_id+'/1';
					break;
				case 'change':
					jQuery.ajax({
						type:'get',
						url:getBase()+'reservations/availability/'+reservation_id,
						dataType:'json',
						success:function(ans){
							jQuery.each(ans.freeRooms, function(i, val) {
     							jQuery("select#rooms").prepend('<option value="'+i+'">'+val+'</option>');
       						 });
       						 jQuery('#room_boxe').dialog({ modal:true, 
    		              		 hide:'clip',
    		              		 width:230,
    		              		 position:'top',
    		              		 buttons: { "Changer": function() {
    		               				var new_room=jQuery('#rooms').val();
    		               				var date=jQuery('#DateCheckIn').val();
    		               				var pu=(jQuery('#autre_prix').length>0)?jQuery('#autre_prix').val():'';
    		               				jQuery.ajax({
											type:'GET',
								 			url:getBase()+'reservations/room_changer/'+reservation_id+'/'+row+'/'+new_room+'/'+date+'/'+pu,
											dataType:'json',
											success:function(ans){
												//*
													if(ans.success){
														jQuery('#room_boxe').dialog('close');
														location.reload();
													}
													else {
														alert(ans.msg);
													}
													
											
											//	*/
   											},
									 	});
									 },
									  "Annuler": function() { jQuery(this).dialog("close");
    					            						jQuery('#message_PU').html('');
    					            					 }
    							    },
    					           
    					         });
                      		 }
					});
					break;
				case 'departure':
				
				jQuery('#depart_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:390,
    		               position:'top',
    		               buttons: { "Changer": function() {
    		               				var depart=jQuery('#DateOfDeparture').val();
    		               				var arrival=jQuery('#DateOfArrival').val();
    		               				var test=jQuery("#departure_form").validate({
 											rules:{
 												 'data[Reservation][departure]': {required:true,date:true},
 												  'data[Reservation][arrival]': {required:false,date:true},
 												}
											});
										if(test.form()){
    		               					jQuery.ajax({
												type:'GET',
								 				url:getBase()+'reservations/departure_changer/'+reservation_id+'/'+depart+'/'+row+'/yes'+'/'+arrival, 
												dataType:'json',
												success:function(response){
													//*
													if(response.success){
														jQuery('#message_depart').html('<span class="loading">Actualisation ...</span>');
														document.location.reload();
													}
													else {
														alert(response.msg);
													}	
											//	*/
   												}
									 		});
									 	}
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            						jQuery('#message_depart').html('');
    					            					 }
    					           }
                       });
                       
					break;
				case 'edit_location_bill':
					edit_location(reservation_id,'bill',el);
					break;
				case 'edit_location_proforma':
					edit_location(reservation_id,'proforma',el);
					break;
				case 'client' :
					var controller=(jQuery('div#tabella').attr('type')=='reservations')?('reservations'):('locations');
           			jQuery('<div id="client_edit" title="Affichage/Modification des informations du client"></div>').insertAfter('body');
   		     		jQuery('#client_edit').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:400,
    		               position:'top',
    		               buttons: { "Modifier": function() {
    		             		  			if((jQuery('#clientList').val()!=0)
    		             		  			&&(jQuery('#clientList').val()!=undefined)){
    		             		  				var nom=jQuery('#clientList option:selected').text();
    		             		  				tier_id=jQuery('#clientList').val();
    		             		  				jQuery(el).attr('tier',tier_id);
    		             		  			}
    		             		  			else {
    		             		  				var nom=jQuery('#fullName').val();
    		             		  			}
    		             		  			
    		             		  	jQuery('#res_fields input, #res_fields select').each(function(i){
    										jQuery(this).hide();
    										jQuery(this).appendTo('form#edit_form');
    									});
    					            jQuery('#edit_form').attr('action',getBase()+'reservations/client').ajaxSubmit({
							            dataType:'json',
							            data:{'data[Tier][id]':tier_id},
							             success:function(ans){
							             	if(ans.success){
							                  jQuery('#client_edit').dialog("close");
							                  jQuery('td[tier="'+tier_id+'"]').each(function(){
							                  	jQuery(this).text(nom);
							                  });
    	                                     	alert(ans.msg);
    							           	}
    							           	else { 
    							           			alert(ans.msg);
    							           		 }
    							           
    							         }
    							    });
    							    },
    					            "Fermer": function() { jQuery(this).dialog("close"); }
    					           }
                       });
                       jQuery("#client_edit").html('<span id="loading">Chargement...</span>');
    		           jQuery('#client_edit').load(getBase()+controller+"/client/"+tier_id+"/"+reservation_id,function(){date();});
				 	break;
				 case 'state':
				 	jQuery('#state_boxe').dialog({ modal:true, 
    		               width:230,
    		               position:'top',
    		               buttons: { "Changer": function() {
    		               				var state=jQuery('#stateSelect').val();
    		               				if(state=='partie'){
    		               					var heure=jQuery('#departureTimeHour').val()+'.'+
    		               							jQuery('#departureTimeMinute').val()+'.'+
    		               							jQuery('#departureTimeMeridian').val();
    		               				}
    		               				jQuery(this).dialog('close');
										state_updater(reservation_id,el,state,'',0,heure);
										},
									"Annuler":function(){ jQuery(this).dialog('close');}
									}
							});
					break;
				 case 'facture':
				 	createFactureByRightClick(facture_id,reservation_id,el,action,state);
				 	break;
				 case 'services':
				 	jQuery('#services_boxe').dialog({ modal:true, 
    					width:420,
    					buttons: { 
    						"Enregistrer": function() { 
    							jQuery('#services_form').ajaxSubmit({
    								data:{ 'data[Service][tier_id]':tier_id,
    									'data[Service][description]':jQuery('#descService').val(),
    									},
    								dataType:'json',
    								success:function(ans){
    									if(ans.success){
    										jQuery('#services_boxe').dialog("close");
    										alert('Service Enregistré!');
    									}
    									else {
    										alert(ans.msg);
    									}
    								}
    							});
    						},
    						"Annuler": function() { jQuery(this).dialog("close"); }
    						 }
    				});
    				break;
				 case 'annulee':
				 var type=(jQuery('div#tabella').attr('type')=='reservations')?('réservation'):('location');
				 var controller=(jQuery('div#tabella').attr('type')=='reservations')?('reservations'):('locations');
				 if(confirm('Voulez vous vraiement annuler cette '+type+' ?')){
				 	var goOn=true;
				 	var motif='';
				 	if(facture_id!=0){
				 		motif=prompt('Motif de l\'annulation : ');
				 		goOn=((motif!=null)&&(motif!=''))?true:false;
				 	}
				 	if(goOn){
						jQuery.ajax({
				 			type:'GET',
				 			dataType:'json',
				 			url:getBase()+controller+'/state_updater/'+reservation_id+'/annulee/'+motif,
							success:function(ans){
								if(ans.success){
									location.reload();
    							}
    							else {
    								alert(ans.msg);
    							}
    						}
				 		});
				 	}
				 }
				 	break;
				 case 'details':
				 	details(el);
				 	break;
			}
			resizer();
		}
		
	);
}

function formater(value){
	if(value<10){
		return '0'+value;
	}
	else {
		return value;
	}
}

function mousedown(c) {
        row=jQuery(c).parents('tr').attr('name');
        cellClass=jQuery(c).attr('class').split(' ')[0];
        if((row!='other')&&(!class_regex.test(cellClass))){
        	c.className = "selection";
        	numero=parseInt(jQuery(c).attr('numero'));
        	startingPoint=numero+1;
        	isdown = 1;
        	times++;
		}
}
var facture_res=0; 
var res_number=0;
var el_created;
var endPoint;
    	var month;
        var year;
        var days;
        var arrivee;
        var depart;
        
function mouseup(){
	
jQuery('*').mouseup(function() {
	
    if(isdown&&(times>0)){
    	 endPoint=startingPoint+parseInt(times)-1;
    	 month=parseInt(jQuery('#title').attr('month'));
         year=jQuery('#title').attr('year');
         days=parseInt(jQuery('#title').attr('days'));
         arrivee=year+'-'+formater(month)+'-'+formater(startingPoint);
         depart=year+'-'+formater(month)+'-'+formater(endPoint);
        jQuery('.details').remove();
        //removing certains fields
        jQuery('#multi').hide();
        var div=(jQuery('div#tabella').attr('type')=='reservations')?('#booking_boxe'):('#location_boxe');
        var longueur=(jQuery('div#tabella').attr('type')=='reservations')?(1120):(1200);  
        //entering in the location creation box the number of jours
        if(jQuery('div#tabella').attr('type')=='locations'){
        	jQuery('#jours').val(endPoint-startingPoint+1);
        }
  			 jQuery(div).dialog({
  	 		 	modal:true, 
    			width:longueur,
    			position:'top',
    			//	show:'bounce',
    			buttons: { 
    					"Fermer": function() { jQuery(this).dialog("close");
    											jQuery('td.selection').attr('class','');
    											jQuery('#message_res').html('');
    											jQuery('#id').val('') ;
    										}
    				  				
    				  	}
    		} );
		    
       jQuery('#room_number').text(row);
       jQuery('#arrivee').text(arrivee);
       jQuery('#depart').text(depart);
        times=0;
        isdown = 0;
    }
    else {
    		jQuery('td.selection').attr('class','');
    		times=0;
    		isdown = 0;
    }
});
}
function resAdd(){
	var tier=jQuery('select[id="principal"] option:selected').html();
    var tier_id=jQuery('select[id="principal"] option:selected').val();
    var state=jQuery('select[id="ReservationEtat"] option:selected').val();
					
	jQuery('#resAdd').ajaxSubmit({
		data:{ 'data[Reservation][arrivee]':arrivee,
			'data[Reservation][depart]':depart,
			'data[Reservation][room]':row,
			'data[type]':'single',
			'data[Reservation][personne_contact]':jQuery('#pers_contact').val()
		},
		dataType:'json',
		success:function(response){
			if(response.success){
    			for(var i=startingPoint; i<endPoint; i++){
    				jQuery('tr[name="'+row+'"] td[numero="'+i+'"]').remove();
    			}  
    			el_created=jQuery('tr[name="'+row+'"] td[numero="'+(startingPoint-1)+'"]');
    			res_number=response.id;
    			jQuery('tr[name="'+row+'"] td[numero="'+(startingPoint-1)+'"]')
    			.attr('colspan',(endPoint-startingPoint+1))
    			.attr('class',state)
    			.attr('reservation',response.id)
    			.attr('position',(startingPoint-1))
    			.attr('tier',tier_id)
    			.text(tier)
    			.css({'color':'white'});
				rightClick();
				jQuery('td.selection').removeAttr('class');
    			jQuery('#booking_boxe strong').remove();
    			alert('Réservation créée');
    			//affiche of facture div
    			if(state=='arrivee'){
    				jQuery('#factureDiv').show();								
    			}
    			else {
    				jQuery('#booking_boxe').dialog("close");
    				if(jQuery('#DateAutre').val()!=''){
    					location.reload();
    				}
    			}
    			//fill the facture date with arrivee date
    			jQuery('#DateFact').val(arrivee);
    		}
    		else { alert(response.msg);
    		}
   		}
	});
}	

function locationAdd(){
    								var tier=jQuery('select[id="principal"] option:selected').html();
    								var tier_id=jQuery('select[id="principal"] option:selected').val();

    								var autre_date_depart=jQuery('#Date_autre_depart').val();
    								var state='en_attente';
    								
    							jQuery('form#location-extras input').each(function(){
    								jQuery(this).appendTo('form#locationAdd').css('display','none').attr('class','extra');
    							});
    					           jQuery('form#locationAdd').ajaxSubmit({
							             beforeSend:function(){ jQuery('#message_res').html('<span id="loading">Enregistrement...</span>');},
							             data:{ 'data[Location][arrivee]':arrivee,
							             		'data[Location][depart]':depart,
							             		'data[Location][salle]':row,
							             		'data[Location][message]':jQuery('#LocationMessage').val(),
							             		'data[Location][personne_contact]':jQuery('#pers_contact').val()
							             	},
							             dataType:'json',
							             success:function(response){
							            // 	jQuery('body').html(response);
							            //*
							             	if(response.success){
							            //      jQuery('#booking_boxe').dialog("close");
    							          	  for(var i=startingPoint; i<endPoint; i++){
    											jQuery('tr[name="'+row+'"] td[numero="'+i+'"]').remove();
    										  }  
    										  el_created=jQuery('tr[name="'+row+'"] td[numero="'+(startingPoint-1)+'"]');
    										   res_number=response.id;
    										  jQuery('tr[name="'+row+'"] td[numero="'+(startingPoint-1)+'"]')
    										 .attr('colspan',(endPoint-startingPoint+1))
    										 .attr('class',state)
    										 .attr('reservation',response.id)
    										 .attr('position',(startingPoint-1))
    										 .attr('tier',tier_id)
    										 .attr('facture',response.facture_id)
    										 .text(tier);
											rightClick();
											jQuery('td.selection').removeAttr('class');
    											jQuery('#location_boxe strong').remove();
    											jQuery('#id').val('') ;
    										jQuery('#message_res').html('');
    										//removing extra fields
    										jQuery('form#locationAdd input.extra').each(function(){
    											jQuery(this).remove();
    										});
    										
    										alert('Location créée');
    										jQuery('#location_boxe').dialog('close');
    										if(autre_date_depart != '' && autre_date_depart != undefined){
    											document.location.reload();
    										}
    							   	}
    							    else { 
    							    	alert(response.msg);
    							    	jQuery('#message_res').html('<strong>'+response.msg+'</br></br></strong>');
    							    }
    							           	//*/
    							           
    							         },
    							        error:function(jqXHR, textStatus, errorThrown){
    							        	jQuery('body').html(errorThrown);
    							        //	alert(textStatus+'<br>'+errorThrown);
    							        	jQuery('td.selection').removeAttr('class');
    											jQuery('#message_res').html('');
    											jQuery('#id').val('') ;
    							        }
    							          
    							        });
  }	
  
function mouseover(c) {
    if (isdown == 1) {
    	var numYaCell=parseInt(jQuery(c).attr('numero'));
    	var diff=numYaCell-numero;
    	cellClass=jQuery(c).attr('class');
    	if((jQuery(c).parents('tr').attr('name')==row)&&(diff==1)&&(!class_regex.test(cellClass))&&(cellClass!='selection')){
    	
        	jQuery(c).attr('class','selection')
        	times++
        	numero=numYaCell;
       
    	}
    	else if((jQuery(c).parents('tr').attr('name')==row)&&(diff==-1)&&(!class_regex.test(cellClass))){
    		jQuery('tr[name="'+row+'"] td[numero="'+numero+'"]').attr('class','');
        	times--
        	numero=numYaCell;
    	}
    }
    else {
        return false;
    }
}

function mouseout(c) {
    if (isdown == 1) {
    	var numYaCell=parseInt(jQuery(c).attr('numero'));
    	var diff=numYaCell-numero;
    	cellClass=jQuery(c).attr('class');
    	if((jQuery(c).parents('tr').attr('name')==row)&&(!class_regex.test(cellClass))&&(diff<=0)){
    	//	alert(diff)
        	jQuery(c).attr('class','')
        	times--
        	numero=numYaCell;
       
    	}
    	else {
    		
    	}
    }
    else {
        return false;
    }
}

function remove_facture(){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    	jQuery('form[name="'+nom+'"]').ajaxSubmit({
			success:function(response){
				jQuery.each(rows ,function(i,val){
					jQuery(val).remove();
				})
    		}
    	});
    }
}

function goTo(){
	jQuery('#goto_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:390,
    		               position:'top',
    		               buttons: { "GO": function() {
    		               				var mois=jQuery('#ukweziMonth').val();
    		               				var annee=jQuery('#umwakaYear').val();
    		               				jQuery(this).dialog("close");
    		               				var controller=jQuery('div#tabella').attr('type');
    		               				document.location.href=getBase()+controller+'/tabella/'+mois+'/'+annee;
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            					 }
    					           }
                       });
}

function facture_global(tierId){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)!=1) {
    	jQuery('<div id="alert" title="message">Sélectionné un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var factureId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').val();
    	document.location.href=getBase()+'reservations/facture_globale/'+factureId;
    }

}
function remove_pyt(){
	var nom='pyts';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins un Paiment!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    	jQuery('form[name="'+nom+'"]').ajaxSubmit({
    		dataType:'json',
    		global:false,
			success:function(response){
				if(response.success){
						location.reload();
				}
				else {
					alert(response.msg)
				}
    		},
    	error:function(a,b,c){
    		alert('Vous n\'avez pas le droit d\'accèder à cette fonction!')
    	}
    	});
    }
}

function view_pyt(){
	var nom='pyts';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins un Paiement !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	
    	var ids='';
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 ids=ids+jQuery(this).val()+',';
    	});
    	var clientId=jQuery('#clientDetails').attr('clientId');
    	document.location.href=getBase()+'paiements/recu/'+ids+'/'+clientId;
    }
}

function mass_pyt(reservation){
	var nom='checkbox';
	if((reservation=='off')&&(jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length==0)) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	var montant=0;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    		 montant+=parseInt(jQuery(this).parents('tr').children('td[name="reste"]').text().replace(/[^0-9]/gi, ''));
    	});
    	var factureResId=null;
    	if(rows.length>1){
			jQuery('#taux_div').show();
		} 
		if(rows.length==0){
			factureResId=jQuery('#facture_num').attr('facture');
			jQuery('#PaiementMontant').val(jQuery('#facture_reste').text().replace(/[^0-9]/gi, ''));
		}
		else {
			jQuery('#PaiementMontant').val(montant);
		}
		jQuery('#pyt_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    		buttons: { "Créer": function() {
					//validation stuff
    			var test=jQuery("#pytAdd").validate({
 					rules:{
 						 'data[Paiement][montant]': {required:true,number:true},
 						 'data[Paiement][montant_equivalent]': {number:true},
 						 'data[Paiement][date]': {required:true,date:true},
 					}
				});
				if(test.form()){
				
					if(rows.length==0){
						nom='pytAdd';
					}
					if(rows.length>0){
						jQuery('#pytAdd select, #pytAdd input').appendTo('form[name="'+nom+'"]').hide();
					}	
					
    				jQuery('form[name="'+nom+'"]').ajaxSubmit({
    					global:false,
    					dataType:'json',
    					data:{'data[reservation]':reservation,
    						'data[Id][0]':factureResId,
    						'data[rows]':rows.length,
    						'data[Paiement][type]':'pyt',
    						},
						success:function(ans){
								if(ans.success){
									jQuery('#pyt_boxe').dialog("close");
									location.reload();
								}
								else {
									alert(ans.msg)
								}
    					}
    				});
    			}
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    		}
    	}
   } );
    	
    }
}

function pyt(factureId,type){
  var action = getBase()+'paiements/add';
	if(factureId==undefined){
		if(jQuery('form[name="checkbox"] input[type="checkbox"]:checked:not(input[name="master"])').length==1){
			factureId=jQuery('form[name="checkbox"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		}
		else {
			factureId=jQuery('#facture_num').attr('facture');
		}
	}
  // alert(type)
	if(type==undefined || type=='standard' || type =='globale'){
		var boxe='#pyt_boxe';
		var form='#pytAdd';
    if(type=='globale'){
      action = getBase()+'paiements/globale_bill_pyt'
    }
    else {
      type='pyt';
    }
	}
	else if(type=='transfer') {
		var boxe='#transfer_boxe';
		var form='#transferAdd';
		type='transfer';
	}
	else if(type=='remboursement'){
		var boxe='#remb_boxe';
		var form='#rembAdd';
		type='remboursement';
	}
	jQuery(boxe).dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer": function() {
    			
    			//validation stuff
    			var test=jQuery(form).validate({
 					rules:{
 						 'data[Paiement][montant]': {required:true,number:true},
 						 'data[Paiement][montant_equivalent]': {number:true},
 						 'data[Paiement][date]': {required:true,date:true},
 						 'data[Paiement][facture_id]': {number:true,required:true},
 						 'data[Paiement][date_facture]': {required:true,date:true},
 					}
				});
				if(test.form()){
    				jQuery(form).
    				attr('action',action).
    				ajaxSubmit({
						data:{'data[Paiement][facture_id]':factureId,
							'data[Paiement][type]':type,
							},
						global:false,
						dataType:'json',
						success:function(ans){
							if(ans.success){
								location.reload();
							}
							else {
								alert(ans.msg)
							}
    					},
    							          
    				});
    			}
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    		}
    				  				
    	}
   } );
   
}
function recherche(){
	jQuery('#recherche_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Lancer !": function() {
    			jQuery('form#recherche').submit();
    			jQuery(this).dialog("close");
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    		}
    				  				
    	}
   } );
}
function availability(){
jQuery('#availability_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Vérifier": function() {
    			var test=jQuery("form#dispo").validate({
 					rules:{
 						 'data[Reservation][arrivee]': {required:true,date:true},
 						 'data[Reservation][depart]': {required:true,date:true},
 					}
				});
				
				if(test.form()){
					/*jsonp request 
    				jQuery.ajax({
    				//	crossOrigin:true,
    					url:'http://197.157.193.107/aser/reservations/availability/1',
    					type:'GET',
    					dataType:'jsonp',
    					timeout:5000,
    					success:function(response){alert(response.id);},
    					error:function(xhr,status,ethrown){
    						alert(status);
    					}
    				})
    				//*/
    				var cat=jQuery('#categorie option:selected').html();
    				var etage=jQuery('#etage').val();
    				jQuery('form#dispo').ajaxSubmit({
    					dataType:'json',
						success:function(response){
							if(response.success){
								jQuery('#availability_boxe').dialog('close');
								jQuery('div#alert').remove();
								 jQuery('<div id="alert" title="Résultats de la recherche"></div>')
    	        	                              .dialog({modal:true, show:'slide',hide:'clip',position:'center',width:350,
    							                       	buttons: { "Ok": function() { jQuery(this).dialog("close");
								             						}
								             				}
    								                     });
    							var msg=(cat!='')?' de type '+cat:'';
    							msg=(etage!='')?msg+' à l\'étage N°'+etage:msg+'';
								jQuery('#alert').append('<div class="message">'+response.available+' chambre(s) '+msg+' sont disponibles.</div>');
								jQuery('div.message').css({'margin-bottom':'10px','z-index':0});
								jQuery('#alert').append('<label>Liste</label><select id="chambres_dispo"></select>');
								jQuery.each(response.freeRooms, function(i, val) {
     								jQuery("select#chambres_dispo").prepend('<option value="'+i+'">'+val+'</option>');
       							 });
       							 
    						}
    						else { 
    							 jQuery('#availability_boxe').dialog("close");
    								alert(response.msg);
    						}
    								           
    					},
    								          
    				});
    			}
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    						}
    				  				
    		}
    });
}	

function annuler_facture(text){
	var model=text.split('_')[0];
	var factureId=text.split('_')[1];
	if(jQuery('span#etat').text()=="annulee"){
		alert('Facture déjà annulée !');
	}
	else if(confirm('Voulez vraiment annuler cette facture ??')){
		var obs=prompt('Motif de l\'annulation : ');
		if((obs!='')&&(obs!=null)){
			jQuery.ajax({
    			url:getBase()+'factures/remove_facture/'+factureId+'/'+model+'/'+obs,
    			global:true,
    			dataType:'json',
				success:function(ans){
					if(ans.success){
						location.reload();
    				}
    				
   		 		}
    		});
    	}
    	else {
    		alert('Motif obligatoire !');
    	}
   }
}

function edit_facture(factureId){
	jQuery('#edit_facture_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Modifier": function() {
    								
    								
    			jQuery('#edit_facture_form').ajaxSubmit({
					dataType:'json',
					data:{'data[Facture][observation]':jQuery('#FactObs').val()},
					success:function(response){
						if(response.success){
							jQuery('#edit_facture_boxe').dialog("close");
							location.reload();
    					}
    					else {
    						alert(response.msg);
    					}
    				}		          
    			});
    		},
    		"Annuler": function() { jQuery(this).dialog("close");}
    				  				
    		}
    	});
}
function createFactureByRightClick(facture_id,reservation_id,el,action,state){
	if((facture_id!='0')&&(facture_id!=undefined)){
		alert('Facture déjà créée!');
	}
	else if(!state.match('arrivee')){
		alert('On peut créer la facture seulement si le client est déjà arrivée!');
	}
	else {
		
			var day=parseInt(jQuery(el).attr('position'))+1;
    		var month=parseInt(jQuery('#title').attr('month'));
        	var year=jQuery('#title').attr('year');
        	jQuery('#FactureDate').val(year+'-'+formater(month)+'-'+formater(day));
        	
		jQuery('#facture').dialog({ modal:true, 
    		position:'top',
    		width:390,
    		buttons: { "Enregister": function() { 		
			//validation stuff
    		var test=jQuery("#facture_form").validate({
 				rules:{
 				'data[Facture][date]': {required:true,date:true},
 				'data[Facture][echeance]': {date:true},
 				'data[Paiement][montant]': {required:true,number:true},
 				'data[Paiement][date]': {required:true,date:true},
 				}
			});
			if(test.form()){
	    		jQuery('#facture_form').ajaxSubmit({
					beforeSend:function(){ jQuery('#message').text('Enregistrement...');},
					complete:function(){ jQuery('#message').text('');},
					dataType:'JSON',
					data:{'data[Id][0]':reservation_id},
					success:function(response){
						jQuery(el).attr('facture',response.facture_id);
						jQuery('#'+action).dialog("close");
    	                alert('Facture enregistrée!');
    				},
    			});
    		}
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); }
    	}
    	});
	}
}

function facture_booking(){
	var test=jQuery("#facture_form_fact")
			.validate({
 				rules:{
 					'data[Facture][date]': {required:true,date:true},
 					'data[Paiement][montant]': {required:true,number:true},
 				}
			});
	if(test.form()){
		jQuery('#facture_form_fact')
		.ajaxSubmit({
			dataType:'JSON',
			data:{'data[Id][0]':res_number},
			success:function(response){
				jQuery(el_created).attr('facture',response.facture_id);
    	        alert(response.msg);
    			jQuery('#booking_boxe').dialog("close");
    			jQuery('td.selection').removeAttr('class');
    			jQuery('#id').val('') ;
    			jQuery('#factureDiv').hide();
    			
    			if(jQuery('#DateAutre').val()!=''){
    					location.reload();
    				}
    		},
   		});
    }
}

function resto_tier(){
	jQuery('#add_client').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer": function() {
    			if(tier())
    				jQuery('#add_client').dialog("close"); 
    		},
    		"Annuler": function() { jQuery(this).dialog("close"); 
    		}
    	}
   	});
   
}

function tier(){
	var ok=true;
    			//validation stuff
    			var test=jQuery("#tierAdd").validate({
 					rules:{
 						 'data[Tier][nom]': {required:true},
 					//	 'data[Tier][prenom]': {required:true},
 						 'data[Tier][email]': {email:true},
 						 'data[Tier][date_naissance]': {date:true},
 					}
				});
				if(test.form()){
    				var nom =jQuery('#clientNom').val()+' '+jQuery('#prenom').val();
    				jQuery('#tierAdd').ajaxSubmit({
    					data:{'data[Tier][actif]':1,
    						'data[Tier][booking]':1,
    						'data[Tier][type]':'client',
    						'data[Tier][pers_contact]':jQuery("#pers_contact").val()
    						},
    					dataType:'json',
    					global:true,
						success:function(response){
							if(response.success){
								 jQuery('<option value='+response.id+' selected>'+nom+'</option>').prependTo('#principal');
								 jQuery('<option value='+response.id+' selected >'+nom+'</option>').prependTo('.occupants');
								 jQuery('<option value='+response.id+' selected>'+nom+'</option>').prependTo('#tierId');
								 jQuery('<option value='+response.id+' selected>'+nom+'</option>').prependTo('#principal1');
								 jQuery('<option value='+response.id+' selected>'+nom+'</option>').prependTo('#clientList');
							//	 jQuery('<option value='+response.id+' >'+nom+'</option>').prependTo('#occupants1');
								alert('Client '+nom+' enregistré !');
								jQuery("form#tierAdd")[0].reset();
    						}
    						else { 
    							alert(response.msg);
    							ok=false;
    						}
    								           
    					},
    					 error:function(jqXHR, textStatus, errorThrown){
    						alert(errorThrown);
    					}
    				});
    				}
    return ok;
}

function edit_produit(){
   	var produit_id=jQuery('select[id="produits"] option:selected').val();
	if(produit_id!=undefined){
	   	var produit_name=jQuery('select[id="produits"] option:selected').html().split('_')[0];
	  	 if(jQuery('select[id="produits"] option:selected').html().split('_').length==3){
	   		var produit_qte='_'+jQuery('select[id="produits"] option:selected').html().split('_')[1];
	   		var produit_pv=jQuery('select[id="produits"] option:selected').html().split('_')[2];
	   	}
	   	else {
	   		var produit_pv=jQuery('select[id="produits"] option:selected').html().split('_')[1];
	   		var produit_qte='';
	   	}
	   	jQuery('input#nom').val(produit_name);
	   	jQuery('input#pv').val(produit_pv);
		jQuery('#edit_boxe').dialog({
	  			modal:true, 
	    		width:390,
	    		position:'top',
	    		buttons: { "Enregistrer": function() {
	    			var actif=jQuery('select#actif').val();	
	    			var groupe1=jQuery('#GroupeId').val();
	    			var groupe2=jQuery('#groupe').val();
	    			jQuery('#edit_form').ajaxSubmit({
	    				dataType:'json',
					    success:function(res){
							if(res.success){
							jQuery('#edit_boxe').dialog("close");
							if(actif=='non'){
								jQuery('select[id="produits"] option[value="'+produit_id+'"]').remove();
							}
							else {
								var new_name=jQuery('input#nom').val()+produit_qte+'_'+jQuery('input#pv').val();
								jQuery('select[id="produits"] option[value="'+produit_id+'"]').html(new_name);
    						}
    					}
    					else 
    					alert(res.msg);
    				},
	    		});
	    	},
	    	"Annuler": function() { jQuery(this).dialog("close"); 
	    							}
	    	}
	    });
	   	jQuery('#edit_boxe').load(getBase()+"produits/edit/"+produit_id,function(){date();});
	}
	else {
		alert('Sélectionné un produit!');
	}
}

function add_produit(){
	jQuery('#add_produit').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer ": function() {
    							
    			jQuery('form#add_produit').ajaxSubmit({
    					 beforeSend:function(){ jQuery('#message_produit').html('<span id="loading">Enregistrement...</span>')},
						complete:function(){ jQuery('#message_produit').html('')},
						data:{'data[Produit][vente]':true,
								'data[Produit][PA]':0,
								'data[Produit][min]':0,
								'data[Produit][monnaie]':'BIF',
							},
						dataType:'json',
							             success:function(res){
							            	if(res.success){
							                  jQuery('#add_produit').dialog("close");
							                   jQuery('<option value='+res.id+' selected>'+res.string+'</option>').prependTo('select#produits');
    							           	}
    							           	else { alert(res.msg)
    							           	}
    							           
    							        },
    							        });
    							     },
    					"Annuler": function() { jQuery(this).dialog("close"); 
    							}
    				  				
    				  	}
    				  	
    	} );
}
function details(td){
				jQuery('.details').remove();
				var details="";
     			var room =jQuery(td).parents('tr').attr('name');
      		 	var id =jQuery(td).attr('reservation');

				jQuery.ajax({
				 type:'GET',
				 url:getBase()+'reservations/details/'+id+'/'+room,
				 beforeSend:function(){},
				 success:function(response){ details=response; 
		 	         if((Math.round(jQuery(td).offset().left)+200)>jQuery(document).width()){
		 	         	jQuery('<div id="flipped" class="details">'+details+'</div>').insertAfter('body');
		                 var pos= Math.round(jQuery(td).offset().left)-150; //10 pr ajuster
		            }
		             else {
		             	jQuery('<div id="not_flipped" class="details">'+details+'</div>').insertAfter('body');
		               var pos=	Math.round(jQuery(td).offset().left)-10;
		             }
		             
	                 jQuery('.details').offset({
		                              top:Math.round(jQuery(td).offset().top)-130,
		                               left:pos
	                                  })
	                             	}
				})
}
//End of the stuff above.

function guest(model){
	var room=jQuery('#chambre').val()
	jQuery.ajax({
		 type:'GET',
		 url:getBase()+'reservations/guest/'+room+'/'+model,
		 beforeSend:function(){ jQuery('#guests').html('<span id="loading">Recherche...</span>')},
		 success:function(guests){ jQuery('#tier_hotel').remove();
		 						   jQuery('#guests').html(guests);
		 						}
	})
}
function color(){
	/*
	//hover stuff
	jQuery('tr').each(function(i){
		jQuery(this).hover(
			function(){jQuery(this).attr('class','on')},function(){jQuery(this).attr('class','out')}
			);
		jQuery(this).toggle(
			function(){
				jQuery(this).children('td:first').children().children(':checkbox').attr('checked','checked')}
			,
			function(){
				jQuery(this).children('td:first').children().children(':checkbox').removeAttr('checked')}
		);
	});
	*/
}
function inserter(params,row){
	 for(var i=params.start; i<params.end; i++){
    	jQuery('tr[name="'+row+'"] td[numero="'+i+'"]').remove();
     }  
   jQuery('tr[name="'+row+'"] td[numero="'+(params.start-1)+'"]')
   .attr('colspan',(params.end-params.start+1))
   .attr('class',params.state)
   .attr('reservation',params.id)
   .attr('position',(params.start-1))
   .attr('tier',params.tier_id)
   .text(params.tier);
   jQuery('td[name="occupation"]').html('<span id="loading">Actualisation...</span>');
   jQuery('tbody#occupation').load(getBase()+'reservations/occupation/'+params.days+'/'+params.month+'/'+params.year);
   rightClick();
	resizer();
}

function affectation(params){
	jQuery('#aff_boxe').dialog({
  	 		 modal:true, 
    		width:300,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer": function() {
    			var row=jQuery('select[id="chambre"] option:selected').html();
    			
    			jQuery('form#aff').ajaxSubmit({
					beforeSend:function(){ jQuery('#message_aff').html('<span id="loading">Enregistrement...</span>')},
					complete:function(){ jQuery('#message_aff').html('')},
					dataType:'json',
					data:{
						'data[Reservation][arrivee]':params.arrivee,
						'data[Reservation][depart]':params.depart,
						'data[TypeChambre][capacite]':params.capacite,
						'data[Reservation][id]':params.id,
					},
				    success:function(response){
				    	if(response.success){
				    		params.nombre--;
				    		inserter(params,row);
        					jQuery('#message_aff').html();
    						jQuery('select[id="chambre"] option:selected').remove();
    						jQuery('#clients #occupants option:selected').remove();
        				
        					if(params.nombre==0){
        						jQuery('#aff_boxe').dialog('close');
        					}
        				}
    				},
    				error:function(jqXHR, textStatus, errorThrown){
    				      jQuery('body').html(errorThrown);
    				}
    							          
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    					}
    				  				
    		}
    	} );
}


function single_add(){
	
	jQuery('#booking_boxe1').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer": function() {
    			//validation stuff
				jQuery.validator.addMethod(
					"depart",
					function(value, element) {
						var arri=jQuery('form#resAdd1 input[name="data[Reservation][arrivee]"]').val();
						if(value>=arri){
							return true
						}
						else {
							return false
						}
					}, 
					"Date de départ inférieure à la date d'arriveé !"
					);
    			    var test=jQuery("form#resAdd1").validate({
 							rules:{
 								'data[Reservation][adultes]': {required:true,number:true},
 								'data[Reservation][enfants]': {required:true,number:true},
 								'data[Reservation][arrivee]': {required:true,date:true},
 								'data[Reservation][depart]': {required:true,date:true,depart:true},
 								'data[Reservation][PU]': {number:true},
 							}
					});
					var occ=jQuery('#occupants1 option:selected').length;
					if(occ==0){
						alert('Sélectionné les occupants !');
					}
					else if(test.form()){
    			var tier=jQuery('select[id="principal1"] option:selected').html();
    			var tier_id=jQuery('select[id="principal1"] option:selected').val();
    			var row=jQuery('select[id="chambre1"] option:selected').html();
    			var arrivee=jQuery('#DateArrivee1').val();
    			var start=parseInt(arrivee.split('-')[2]);
    			var depart=jQuery('#DateDepart1').val();
    			var depart_month=parseInt(depart.split('-')[1]);
    			var month=parseInt(jQuery('#title').attr('month'));
    			var days=parseInt(jQuery('#title').attr('days'));
    			var end=(depart_month>month)?(days):(parseInt(depart.split('-')[2]));
    			var state=jQuery('select[id="etat1"] option:selected').html();
    			jQuery('#resAdd1').ajaxSubmit({
					beforeSend:function(){ jQuery('#message_res1').html('<span id="loading">Enregistrement...</span>')},
					complete:function(){ jQuery('#message_res1').html('')},
					data:{
						'data[Reservation][arrivee]':arrivee,
						'data[Reservation][depart]':depart,
						'data[Reservation][room]':row,
						'data[type]':'single',
					},
					dataType:'json',
				    success:function(response){
				    	if(response.success){
							var params={
										'tier':tier,
										'tier_id':tier_id,
										'start':start,
										'end':end,
										'arrivee':arrivee,
										'depart':depart,
										'id':response.id,
										'month':month,
      									'year':jQuery('#title').attr('year'),
        							    'days':days,
        							    'state':state,
							};
							
       						 jQuery('#booking_boxe1').dialog('close');
							//inserting the booking
							inserter(params,row);
        				}
        				else {
        					jQuery('#booking_boxe1').dialog('close');
        					alert(response.msg);
        				}
    				},
    				error:function(jqXHR, textStatus, errorThrown){
    				      jQuery('body').html(errorThrown);
    				}
    							          
    			});
    	}
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    					}
    				  				
    		}
    	} );
	
}
/*
function multi_add(){
	jQuery('#single').hide();
	var title=jQuery('#booking_boxe').attr('title');
	jQuery('#booking_boxe').attr('title','Reservation pour plusieurs personnes');
	jQuery('#booking_boxe').dialog({
  	 		 modal:true, 
    		width:390,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Créer": function() {
    			var tier=jQuery('select[id="principal"] option:selected').html();
    			var tier_id=jQuery('select[id="principal"] option:selected').val();
    			var nombre=parseInt(jQuery('#nombre').val());
    			var arrivee=jQuery('#DateArrivee').val();
    			var depart=jQuery('#DateDepart').val()
    			var start=parseInt(arrivee.split('-')[2]);
    			var end=parseInt(depart.split('-')[2]);
    			var state=jQuery('select[id="ReservationEtat"] option:selected').html();
    			jQuery('#resAdd').ajaxSubmit({
					beforeSend:function(){ jQuery('#message_res').html('<span id="loading">Enregistrement...</span>')},
					complete:function(){ jQuery('#message_res').html('')},
					data:{
						'data[type]':'multi'
					},
					dataType:'json',
				    success:function(response){
				    	if(response.success){
							var rooms=response.rooms;
							var params={'capacite':response.capacite,
										'tier':tier,
										'tier_id':tier_id,
										'start':start,
										'end':end,
										'arrivee':arrivee,
										'depart':depart,
										'id':response.id,
										'month':parseInt(jQuery('#title').attr('month')),
      									'year':jQuery('#title').attr('year'),
        							    'days':parseInt(jQuery('#title').attr('days')),
        							    'state':state,
        							    'nombre':nombre
							}
							jQuery.each(rooms, function(i, val) {
     							jQuery("#chambre").prepend('<option value="'+i+'">'+val+'</option>');
       						 });
       						 jQuery('#clients').html(jQuery('#list_clients').html());
       						 jQuery('#booking_boxe').dialog('close');
							//creating assignments
						
							 affectation(params);
        				}
        				else {
        					jQuery('#booking_boxe').dialog('close');
        					alert(response.msg);
        				}
    				},
    				error:function(jqXHR, textStatus, errorThrown){
    				      jQuery('body').html(errorThrown);
    				}
    							          
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    					}
    				  				
    		}
    	} );
}
*/
function date() {
     jQuery('input[id*="Date"]').datepicker({dateFormat: 'yy-mm-dd'});
}

function message(){
	if(jQuery('#flashMessage, #authMessage').length!=0) {
		setTimeout(function(){jQuery('#flashMessage, #authMessage').hide('fade')},
										5000);
	}
}

function defaulter(){
		if(jQuery('legend.edit').length==0) {
			jQuery('ol#defaulter li').each(function(i){
				var name=jQuery(this).attr('name');
				var value=jQuery(this).text();
				jQuery('select[id$="'+name+'"] option[value="'+value+'"]').attr('selected','selected');
				jQuery('input[id$="'+name+'"]:not(:checkbox)').attr('value',value);
				if((name=="Restrict")&&(value==1)) {
					jQuery('input[id$="'+name+'"]').attr('checked','checked');
				}
			});
		}
}

function actions(nom,action,affectationForm) {
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var id=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
	switch(action){
		case 'trace':
						document.location=getBase()+'traces/index/'+id+'/'+info[0];
					break;
		
		case 'cancel_aserb_bill':
						if(confirm('Voulez vraiment supprimer la copie dans B?'))
							document.location=getBase()+'factures/cancel_aserb_bill/'+id;
					break;
		case 'delete':
    	jQuery('<div id="effacer" title="Message">Voulez vous vraiment effacer l\'enregistrement ?</div>')
    	.dialog({ modal:true, 
    							show:'slide',
    							hide:'clip',
    							buttons: { "Effacer": function() { document.location.href=getBase()+info[1]+"/"+action+"/"+id; },
    										"Annuler": function() { jQuery(this).dialog("close"); }
    									  }
    							} );
		break;
		case 'affectation_index':
            jQuery("#affectation").html('<span id="loading">Chargement...</span>')
    		jQuery('#affectation').load(getBase()+'reservations/affectation_index/'+id);
    		jQuery('#actions').show();
			break;
			
		case 'historique':
   				 jQuery('#historique_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:380,
    		               position:'top',
    		               buttons: { "Go": function() {
    		               				jQuery('#historique_form').attr('action',getBase()+'produits/view/'+id).submit();
    							      },
    					             "Annuler": function() { jQuery(this).dialog("close"); }
    				                }
                       });
    		break;
		default:
			document.location.href=getBase()+info[1]+"/"+action+"/"+id;
			break;
		}
	}
	else {
		jQuery(document).ready(function(){
    	jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	});
	}
}

/* Gestion des produit details */
var produitId=0; // keeping an eye on the produit selected

function produit_tarifs(nom,action,tarifs_form) {
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var produitId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		var tr =jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked').parents('tr');
		
	switch(action){
		case 'index':
            //hiding or showing other rows
    		jQuery('tr:not(tr[id="first"],div#quick_add tr, tr[id="'+jQuery(tr).attr('id')+'"])').toggle('fade');
    		jQuery('div#pagination').toggle('fade');
    		jQuery("#produit_tarifs").toggle('fade')
            jQuery("#produit_tarifs").html('<span id="loading">Chargement...</span>')
    		jQuery('#produit_tarifs').load(getBase()+'produits/tarif_index/'+produitId);
    		jQuery('#produit_tarifs_links').slideToggle();
    		
			break;
			
		case 'add':
			jQuery('#tarif_boxe').dialog({
  	 			 modal:true, 
    			width:390,
    			position:'top',
    		//	show:'bounce',
    			buttons: { "Créer": function() {
    				//validation stuff
    			var test=jQuery("#tarifAdd").validate({
 					rules:{
 						 'data[Tarif][PV]': {number:true,required:true},
 					}
				});
				if(test.form()){
    				var pv=parseFloat(jQuery('#pu').val());
    				var name=jQuery('#place').val();
    				jQuery('#tarifAdd').ajaxSubmit({
						beforeSend:function(){ jQuery('#message_tarif').html('<span id="loading">Création ...</span>')},
						complete:function(){ jQuery('#message_tarif').html('')},
						data:{
							'data[Tarif][PV]':pv,
							'data[Tarif][name]':name,
							'data[Tarif][produit_id]':produitId,
								},
						global:false,
						dataType:'json',
						success:function(ans){
    						jQuery('#tarif_boxe').dialog('close');
    						jQuery('#produit_tarifs').load(getBase()+'produits/tarif_index/'+produitId);
	    				},
    					error:function(jqXHR, textStatus, errorThrown){
    						alert(textStatus+'<br>'+errorThrown);
	    					jQuery('#tarif_boxe').dialog('close');
    					}
    				});
    			}
    					},
    					"Annuler": function() { jQuery(this).dialog("close"); }
    			}
		   });
	   		break;
    	case 'delete':
			if((jQuery('form[name="produit_tarifs_form"] input[type="checkbox"]:checked').length==1)) {
				var tarifId=jQuery('form[name="produit_tarifs_form"] input[type="checkbox"]:checked').val();
				var row =jQuery('form[name="produit_tarifs_form"] input[type="checkbox"]:checked').parents('tr');
            	jQuery('<div id="effacer" title="Message">Voulez vous vraiment effacer le tarif ?</div>').insertAfter('body')
     			jQuery('#effacer').dialog({ modal:true, 
    					show:'slide',
    					hide:'clip',
    					buttons: { "Effacer": function() { 
    							jQuery.ajax({
    							type:'GET',
    							url:getBase()+'produits/tarif_delete/'+produitId+'/'+tarifId,
    							beforeSend:function(){jQuery('#effacer').html('<span id="loading">Effacement...</span>')},
    						//	complete:function(){jQuery('#effacer').html('')},
    							dataType:'json',
    							error:function(xhr,status,error){jQuery('body').html(error)},
    							success:function(ans){
    								jQuery('#effacer').dialog('close');
    								jQuery('#effacer').remove();
    								jQuery(row).hide('fade')
    						        jQuery(row).remove();
    	                               
	    							}
    							})
    							},
    							"Annuler": function() { jQuery(this).dialog("close"); }
    							  }
    				} );
			}
	        else {
		        jQuery(document).ready(function(){
    	        jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément dans la liste des tarifs !</div>')
    	        .dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	               });
	        }
			break;
		}
	}
	else {
		jQuery(document).ready(function(){
    	jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	});
	}
}

/* listage des details d'une model */
var modelId=0; // keeping an eye on the model selected

function model_details(nom,action) {
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var modelId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		var tr =jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked').parents('tr');
		
		switch(action){
			case 'index':
        	    //hiding or showing other rows
    			jQuery('tr:not(tr[id="first"], tr[id="'+jQuery(tr).attr('id')+'"])').toggle('fade');
    			jQuery('div#pagination').toggle('fade');
    			jQuery("#model_details").toggle('fade')
        	    jQuery("#model_details").html('<span id="loading">Chargement...</span>')
    			jQuery('#model_details').load(getBase()+'ventes/detail_index/'+modelId);
				break;
		}
	}
	else {
		jQuery(document).ready(function(){
    	jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	});
	}
}


/* Gestion des locations des salles */

var locationId=0; // keeping an eye on the location selected

function location_extras(nom,action,extras_form) {
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var locationId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		var tr =jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked').parents('tr');
	switch(action){
		case 'index':
            jQuery("#extras").html('<span id="loading">Chargement...</span>')
    		jQuery('#extras').load(getBase()+'locations/extra_index/'+locationId);
    		jQuery('#extras_links').slideToggle();
			break;
			
		case 'add':
			jQuery('#extra_boxe').dialog({
  	 			 modal:true, 
    			width:390,
    			position:'top',
    		//	show:'bounce',
    			buttons: { "Créer": function() {
    				var extras=parseInt(jQuery(tr).children('td[name="extras"]').text());
    				var total=parseInt(jQuery(tr).children('td[name="total"]').text());
    				var libel=jQuery('#libelle').val();
    				var pu=parseInt(jQuery('#pu').val());
    				var quan=parseInt(jQuery('#quan').val());
    				jQuery('#extraAdd').ajaxSubmit({
						beforeSend:function(){ jQuery('#message_extra').html('<span id="loading">Création ...</span>')},
						complete:function(){ jQuery('#message_extra').html('')},
						data:{'data[LocationExtra][location_id]':locationId,
							'data[LocationExtra][name]':libel
								},
						global:false,
						success:function(response){
							var montant=pu*quan;
							jQuery(tr).children('td[name="total"]').text(total+montant);
							jQuery(tr).children('td[name="extras"]').text(extras+montant);
							jQuery('#extra_boxe').dialog('close');
							jQuery("#extras").html('<span id="loading">Chargement...</span>')
    						jQuery('#extras').load(getBase()+'locations/extra_index/'+locationId);
	    				},
    					error:function(jqXHR, textStatus, errorThrown){
    						alert(textStatus+'<br>'+errorThrown);
	    					jQuery('#extra_boxe').dialog('close');
    					}
    				});
    					},
    					"Annuler": function() { jQuery(this).dialog("close"); }
    			}
		   });
	   		break;
    	case 'delete':
			if((jQuery('form[name="extras_form"] input[type="checkbox"]:checked').length==1)) {
				var extraId=jQuery('form[name="extras_form"] input[type="checkbox"]:checked').val();
				var row =jQuery('form[name="extras_form"] input[type="checkbox"]:checked').parents('tr');
            	jQuery('<div id="effacer" title="Message">Voulez vous vraiment effacer l\'extra ?</div>').insertAfter('body')
     			jQuery('#effacer').dialog({ modal:true, 
    					show:'slide',
    					hide:'clip',
    					buttons: { "Effacer": function() { 
    						var extras=parseInt(jQuery(tr).children('td[name="extras"]').text());
    				    	var total=parseInt(jQuery(tr).children('td[name="total"]').text());
    				    	var montant=parseInt(jQuery(row).children('td[name="montant"]').text());
    							jQuery.ajax({
    							type:'GET',
    							url:getBase()+'locations/extra_delete/'+locationId+'/'+extraId,
    							beforeSend:function(){jQuery('#effacer').html('<span id="loading">Effacement...</span>')},
    						//	complete:function(){jQuery('#effacer').html('')},
    							error:function(xhr,status,error){alert(status)},
    							success:function(responseText){
    							//	jQuery('body').html(responseText)
    								jQuery(tr).children('td[name="total"]').text(total-montant);
								    jQuery(tr).children('td[name="extras"]').text(extras-montant);
    									jQuery('#effacer').dialog('close');
    									jQuery('#effacer').remove();
    								jQuery(row).hide('fade')
    						        jQuery(row).remove();
    	                                jQuery('<div id="alert" title="Message">Extra effacé !</div>')
    	                                .dialog({modal:true, show:'slide',hide:'clip',
    						                      	buttons: { "Ok": function() { jQuery(this).dialog("close");
    		                                      		 } }
    							             });
    							}
    							})
    							},
    							"Annuler": function() { jQuery(this).dialog("close"); }
    							  }
    				} );
			}
	        else {
		        jQuery(document).ready(function(){
    	        jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément dans la liste des extras !</div>')
    	        .dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	               });
	        }
			break;
		}
	}
	else {
		jQuery(document).ready(function(){
    	jQuery('<div id="alert" title="Message">Sélectionné un et un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	});
	}
}

function hider() {
	jQuery(document).ready(function() { 
	if(jQuery('#separator').attr('title')=='Cacher Le Menu') {
		jQuery('div.actions').hide();
		jQuery('div.index,div.vente,div.tabella').css('width','96%');
		jQuery('#separator').attr('class','next')
		jQuery('#separator').attr('title','Afficher Le Menu');
	}
	else {
		jQuery('div.actions').show();
		jQuery('div.index,div.vente,div.tabella').css('width','80%');
		jQuery('#separator').attr('class','back');
		jQuery('#separator').attr('title','Cacher Le Menu');
	}
	});
}

function validator(){
	names= new Array;
	jQuery.validator.addMethod(
	"expirationDetails",
	function(value, element) {
		var check=false;
		var regex = /^\d{4}-\d{2}-\d{2}:\d+(; \d{4}-\d{2}-\d{2}:\d+)*$/;
		if( regex.test(value)){
			var rows= value.split('; ');
			for(var i=0; i<rows.length; i++){
				var cells=rows[i].split(':');
				var date_details=cells[0].split('-');
    			if(((date_details[1]>='01')&&(date_details[1]<='12'))&&((date_details[2]>='01')&&(date_details[2]<='31')))
    				 check=true;
    			else {
    				check=false;
    				break;
    			}
			}	
		}
		if(value==''){
			check=true;
		}
		return check;
	}, 
	"La syntaxe et/ou les dates ne sont pas correctes !"
	);
	app_rules ={
	         'name': "required",
	        //  'data[Produit][name]': "required",
	          'PA': {required:true,number:true},
	          'PV': {required:true,number:true},
	          'marge': {required:true,number:true},
	          'min': {required:false,number:true},
	          'max': {required:false,number:true},
	          'date_limite': {required:false,date:true},
	          'total':{required:true,number:true},
	          'montant_equivalent':{required:false,number:true},
	          'montant':{required:true,number:true},
	          'quantite':{required:true,number:true},
	          'email':{email:true},
	          'date':{required:true,date:true},
	          'voyage':{number:true},
	          'carburant':{required:true,number:true},
	          'PU':{number:true},
	          'poids':{number:true},
	          'depenses':{number:true},
	          'location':{number:true},
	          'date_depart':{date:true},
	          'date_arrivee':{date:true},
	          'date1':{date:true},
	          'duree':{required:true},
	          'montant_location':{number:true},
	          'montant_transport':{number:true},
	          'avance_location':{number:true},
	          'avance_transport':{number:true},
	          'compte_num':{number:true},
	          'mot_passe':{required:true},
   	 		  'confirmer': {equalTo: "#mot_passe"}
	          
	     };
		
		if(jQuery('div#quick_add').length!=0){
			jQuery('tr:not(:first)').each(function(i){
				jQuery("tr[name='"+i+"'] input:not(:hidden,[type='submit'],[type='reset'],[name='data[Produit][name]']),tr[name='"+i+"'] textarea").each(function(j){
					names[j]=jQuery(this).attr('name');
					var tmp=jQuery(this).attr('name');
					tmp=tmp.split(']'); 
					jQuery(this).attr('name',tmp[1].substr(1,1000));//remove the begining [
				});
	    		jQuery("tr[name='"+i+"'] form").validate({ 
	    	 		ignoreTitle:true,
	        	    rules: app_rules,
	      			submitHandler: function(form) {
						jQuery("tr[name='"+i+"'] input:not(:hidden,[type='submit'],[type='reset'],[name='data[Produit][name]']),tr[name='"+i+"'] textarea").each(function(j){
							jQuery(this).attr('name',names[j]);
  						});
  						
						jQuery("tr[name='"+i+"'] form").ajaxSubmit({
							beforeSend:function(){ jQuery('#loading'+i).show();},
							complete:function(){ jQuery('#loading'+i).hide();},
							data:{'data[Produit][groupe_id]':jQuery("tr[name='"+i+"'] select[name='data[Produit][groupe_id]']").val(),
								'data[Operation][element1]':jQuery("tr[name='"+i+"'] select[name='data[Operation][element1]']").val(),
								'data[Operation][element2]':jQuery("tr[name='"+i+"'] select[name='data[Operation][element2]']").val(),
								'data[Operation][piece]':jQuery("tr[name='"+i+"'] #piece").val(),
								'data[Operation][piece_type]':jQuery("tr[name='"+i+"'] #piece_type").val(),
								'data[Produit][sous_groupe_id]':jQuery("tr[name='"+i+"'] select[name='data[Produit][sous_groupe_id]']").val(),
								'data[Facture][tier_id]':jQuery("tr[name='"+i+"'] select[name='data[Facture][tier_id]']").val()
							},
							success:function(responseText){
								if(responseText.match('failure')) {
									//	jQuery('#loading'+i).text('Erreur !');
									alert(responseText.split('_')[1]);
								}
								else {
								
								//	jQuery('#loading'+i).text('Succès !');
								//	setTimeout(function(){jQuery('#loading'+i).text('');},
								//		50000);
			                 //       jQuery("tr[name='"+i+"'] form")[0].reset();
			                        defaulter();
			                       // dbclick();
			                        
			                         if(jQuery('div#accounting').length>0){
			                         	var journal=jQuery('div#accounting').attr('journal');
			                         	var compteId=jQuery('#compteId').val();
			                         	if(journal!='0'){
			                         		if(journal!=compteId){
			                         			jQuery('select#compteId option[value="'+journal+'"]').show().attr('selected','selected');   
			                         		}
			                         		else {
			                         			jQuery('select#compteId option:first').attr('selected','selected'); 
			                         			jQuery('select#compteId option[value="'+journal+'"]').hide();
			                         		}
			                         		var date1=jQuery('div#accounting').attr('date1');
			                         		var date2=jQuery('div#accounting').attr('date2');
			                         		
			                         		jQuery.ajax({
 												type:'get',
 												url:getBase()+'compte_operations/solde/'+journal,
 												dataType:'json',
 												success:function(a){
 													jQuery('span#solde').text(a.solde);
 													
 												}
 											});
			                         	}
			                         	// inversion des debit/credit
			                         	if(jQuery('input#debit').val()!=''){
			                         		jQuery('input#credit').val(jQuery('input#debit').val());
			                         		jQuery('input#debit').val('');
			                         	}
			                         	else {
			                         		jQuery('input#debit').val(jQuery('input#credit').val());
			                         		jQuery('input#credit').val('');
			                         	}
			                         	
			                         	var id=jQuery('input#id').val();
			                         	if(id!=''){
			                         	 	jQuery('tr#'+id).replaceWith(responseText);
			                         	}
			                         	else {
			                         		jQuery(responseText).insertAfter('form[name="checkbox"] tr:first');
			                         	}
			                         }
			                         else if(jQuery('table#billetage').length>0) {
			                         	 jQuery(responseText).insertAfter('table#billetage tr:first');
			                         	 total_billet(jQuery('#journal_id').text());
			                         }
			                         else {
			                         	 jQuery(responseText).insertAfter('form[name="checkbox"] tr:first');
			                         }
			                      
								}
							}
   						});
	      			}
	          });
			});
		}
		else{
		if(jQuery('form').length>=1){
		jQuery("input:not(:hidden:checkbox,[type='submit'],[type='reset'],[name='data[Produit][name]']),textarea").each(function(i){
			names[i]=jQuery(this).attr('name');
			var tmp=jQuery(this).attr('name');
			if((tmp!=undefined)&&(tmp.split(']').length>=2)){
				tmp=tmp.split(']'); 
				jQuery(this).attr('name',tmp[1].substr(1,1000));//remove the begining [
			}
		});
	     jQuery("form").validate({ 
	     	ignoreTitle:true,
	        rules: app_rules,
	      	submitHandler: function(form) {
					jQuery("input:not(:hidden,[type='submit'],[type='reset'],[name='data[Produit][name]']),textarea").each(function(i){
						jQuery(this).attr('name',names[i]);
  					});
  					
  				
			jQuery(document).ready(function() { 
  				if(jQuery('form').attr('id')=='ajax'){
					jQuery('#ajax').ajaxSubmit({
							beforeSend:function(){ jQuery('#loading').show();},
							complete:function(){ jQuery('#loading').hide();},
							success:function(responseText){ 
										if(responseText=='success') {
											document.location.href=getBase();
										}
										else {
											jQuery('#login fieldset').css('border','1px solid red');
											jQuery('#login fieldset').effect('bounce','fast');
											jQuery('<div class="message">Désolé Indentifiants incorrectes!</div>')
											.insertBefore('#login');
											
									setTimeout(function(){jQuery('div.message').hide('fade');
									                      jQuery('#login fieldset').css('border','1px solid #ccc');},
										2500);
										}
									}
   					});
   				}
   				else {
   					form.submit();
   				}
   			});
   			}
		});
	} 
	}

}

function effacer(nom) { 
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	jQuery('form[name="'+nom+'"]').attr('action',getBase()+''+info[1]+'/deleteAll/'+info[0]);
    if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="Message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	jQuery('<div id="effacer" title="Message">Voulez vous vraiment effacer les enregistrements ?</div>')
    	.dialog({ modal:true, 
    							show:'slide',
    							hide:'clip',
    							buttons: { "Effacer": function() { jQuery('form[name="'+nom+'"]').submit(); },
    										"Annuler": function() { jQuery(this).dialog("close"); }
    									  }
    							} );
   }
}
function print_documents(action,redirect_url){
 // alert("action = "+action+" , url = "+url+", "+getBase()+redirect_url);
	if((action!='')&&(action=='0')){
		alert(" clôturer d'abord le journal !");
	}
	else {
		if(jQuery.browser.mozilla){
			
			var list=jsPrintSetup.getPrintersList().split(',');
      var xls_copy=jQuery('span#displayed_num').attr('xls_copy')
      var type = jQuery('span#displayed_num').attr('type');
			var options='';
		 	jQuery.each(list,function(i,val){
		 		
		 			options=options+'<option value="'+val+'">'+val+'</option>';
		 	});
			jQuery('<div id="effacer" title="Choix de l\'imprimante"><br/><select id="printing">'+options+'</select></div>')
			.dialog({
			 	modal:true, 
    		 	hide:'clip',
    		    width:230,
    		    position:'top',
    		    buttons: { "GO": function() {
    		    					jsPrintSetup.setPrinter(jQuery('#printing').val());
    		   
                //  alert(url)
                  if(xls_copy=='1' && type!= 'aserb'){
                    jQuery('span#displayed_num').hide();
                    jQuery('div#client_details').hide();
                  }
                  
									jQuery('body').html(jQuery('#view').html()).removeClass('body');
		
									jQuery('.document').css({'float':'none','width':'1300px','margin-right':0,'margin':'0px auto','border':'none'});
									jQuery('div.gouv').css({'float':'none','width':'1300px','margin-right':0,'margin':'0px auto'});
									jQuery('#facture_details').hide();
									//increasing size 
									//changing the fontsize depending of the value of closed variable.
									var doc_fontsize=20;
									var th_fontsize=25;
									//the closed variable contains the percentage by which to reduce the standard fontsize.
									if((closed!=undefined)&&(closed>0)){
										doc_fontsize=doc_fontsize - Math.round(doc_fontsize*(closed/100));
										th_fontsize=th_fontsize - Math.round(th_fontsize*(closed/100));
									}
								//	alert(doc_fontsize+' '+th_fontsize);
									jQuery('.document,#lettre').css({'font-weight':'bold',
									'font-size':doc_fontsize+'px',
									});	
									jQuery('#lettre').css({'font-weight':'bold',
									'font-size':'21px',
									'line-height':'30px'
									});	
									jQuery('div.document .left .text').css({'width':'400px'	
												});
									jQuery('span.titre').css({'font-weight':'bold',
									'font-size':'30px'
									});
									jQuery('th').css({'font-weight':'bold',
									'font-size':th_fontsize+'px',
									'border':'1px solid black'
									});

									jQuery('.document td').css({'border':'1px solid black'
										});		
									jQuery('#dateId').css({'font-size':'25px'
									});				
									jQuery('div.document .left img').css({'width':'350px',
												'height':'180px'
												});		
									aserPrint(false,55);
									jQuery('<div id="indicator">Impression ...</div>').prependTo('body').show();
								
  								setTimeout(function(){
                    if(action=='export' && redirect_url!= undefined && redirect_url!= null){
   									  document.location.href=getBase()+redirect_url;
                      // alert(document.location.href)
                      setTimeout(function(){
                        document.location.reload();
                      },5000)
                    }
                    else {
                       document.location.reload();
                    }
   								},
  								500);
  							
						},
						"annuler":function(){jQuery(this).dialog('close');}
					}
				});
		}
		else {
			var url=document.location.href;
			jQuery('body').html(jQuery('#view').html()).removeClass('body');
		
			jQuery('.document').css({'float':'none','width':'1200px','margin-right':0,'margin':'0px auto','border':'none'});
			jQuery('div.gouv').css({'float':'none','width':'1050px','margin-right':0,'margin':'0px auto'});
			jQuery('#facture_details').hide();
			window.print();
			setTimeout(function(){
 							document.location.reload();
 						},
					0);
		}
	}
}

function remove_docs(nom,action){
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)!=1) {
    	jQuery('<div id="alert" title="message">Sélectionné un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var row=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').parents('tr');
    	var actionId=jQuery(row).children('td[name="'+action+'"]').attr('valeur');
    	if((actionId=='')||(actionId==undefined)){
    		jQuery('<div id="alert" title="message">N° '+action+' non défini !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    	}
    	else {
    		var	info=jQuery('form[name="'+nom+'"]').attr('id');
			info=info.split('_');//to get the model and the controller
    		jQuery.ajax({
    			type:'GET',
    			url:getBase()+''+action+'s'+'/remove_'+action+'/'+actionId+'/'+info[0],
    			dataType:'json',
    			success:function (response){
    				if(response.success){
    				document.location.href=getBase()+info[1]+"/index";
    				}
    				else {
    					alert(response.msg);
    				}
    			}
    		})
    	}
    /*	jQuery('form[name="'+nom+'"]').attr('action','/'+info[1]+'/remove_/'+action);
    	jQuery('form[name="'+nom+'"]').ajaxSubmit({
							             beforeSend:function(){ jQuery('#'+action).html('sending...')},
							             complete:function(){ jQuery('#'+action).html('Completed !')},
							             dataType:'JSON',
							             success:function(response){
							             	jQuery('#'+action).dialog("close");
    	                                      jQuery('<div id="alert" title="Message">'+response.message+'</div>')
    	                                      .dialog({modal:true, show:'slide',hide:'clip',position:'center',
    						                       	buttons: { "Ok": function() { jQuery(this).dialog("close");
							             							document.location.href=getBase()+info[1]+"/index";
							             						}
							             				}
    							                     });
    							        },
    							        error:function(jqXHR, textStatus, errorThrown){
    	   										 jQuery('body').html(errorThrown);
    									}
    							          
    							        });*/
    	
    }
	
}
function cleaner(etat){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:true,
    				data:{'data[Chambre][propre]':etat,
    				},
					success:function(response){
						jQuery.each(rows ,function(i,val){
							jQuery(val).children('td[name="propre"]').text(etat);
					})
    			}
    		});
    }
}


function disable(action){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    	jQuery('form[name="'+nom+'"]').attr('action',getBase()+action)
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:true,
    				dataType:'json',
					success:function(response){
						if(response.success){
							jQuery.each(rows ,function(i,val){
								
								if(jQuery(val).children('td[name="actif"]').text()==1){
									jQuery(val).children('td[name="actif"]').text(0);
								}
								else {
									jQuery(val).children('td[name="actif"]').text(1)
								}
							})
						}
    				}
    			});
   	 }
}
function msg_gouvernante(){
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 rows[i]=jQuery(this).parents('tr');
    	});
    	
    	jQuery('div#msg_gouvernante').dialog({
  	 		 modal:true, 
    		width:300,
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Enregistrer": function() {
    			var message=jQuery('#ChambreMessage').val();
    			jQuery('form[name="'+nom+'"]').attr('action',getBase()+'chambres/message')
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
					beforeSend:function(){ jQuery('#action_msg').html('<span id="loading">Enregistrement...</span>')},
					complete:function(){ jQuery('#action_msg').html('')},
					global:false,
					data:{
						'data[Chambre][message]':message
					},
				    success:function(response){
						jQuery.each(rows ,function(i,val){
							jQuery(val).children('td[name="message"]').text(message);
						})
						jQuery('div#msg_gouvernante').dialog('close');
    				},
    				error:function(jqXHR, textStatus, errorThrown){
    				      jQuery('body').html(errorThrown);
    				}
    							          
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    					}
    				  				
    		}
    	} );
    }
}

function documents(nom,action){
	
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var stop=false;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').each(function(){
    		var row=jQuery(this).parents('tr');
    		var actionId=jQuery(row).children('td[name="'+action+'"]').attr('valeur');
    		if((actionId!='')&&(actionId!=undefined)){
    			stop=true;
    		}
    	});
    		
    	if(stop){
    		jQuery('<div id="alert" title="message"> '+action+' déjà Créé !</div>')
    		.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    	}
    	else {
			var	info=jQuery('form[name="'+nom+'"]').attr('id');
			info=info.split('_');//to get the model and the controller
    		jQuery('#'+action).dialog({ modal:true, 
    							//show:'slide',
    							//hide:'clip',
    							position:'top',
    							width:390,
    							buttons: { "Enregister": function() { 		
    								//*
    								//validation stuff
    								var test=jQuery('#'+action+'_form').validate({
 												rules:{
 													  'data[Facture][date_expedition]': {date:true},
 													 'data[Facture][date]': {required:true,date:true},
 													 'data[Commande][date]': {required:true,date:true},
 													 'data[Facture][echeance]': {date:true},
 													 'data[Paiement][montant]': {required:true,number:true},
 												   	 'data[Paiement][date]': {required:true,date:true},
 													}
											});
									//*/
									if(test.form()){
										jQuery('#'+action+'  input, #'+action+'  select').each(function(i){
    										jQuery(this).hide();
    										jQuery(this).appendTo('form[name="'+nom+'"]');
    									});
    									jQuery('form[name="'+nom+'"]').attr('action',getBase()+action+'s'+'/create_'+action);
   									    //jQuery('form[name="'+nom+'"]').submit();
   	 										    
    						            jQuery('form[name="'+nom+'"]').ajaxSubmit({
    						            	 beforeSend:function(){ jQuery('#message').text('Enregistrement...')},
								             complete:function(){ jQuery('#message').text('')},
								             dataType:'JSON',
								             success:function(response){
								             //	jQuery('body').html(response)
								             	jQuery('#'+action).dialog("close");
    	                        	              jQuery('<div id="alert" title="Message">'+response.msg+'</div>')
    	                            	          .dialog({modal:true, show:'slide',hide:'clip',position:'center',
    						            	           	buttons: { "Ok": function() { jQuery(this).dialog("close");
							             								document.location.href=getBase()+info[1]+"/index";
							             							}
							             					}
    							         	            });
    							      		 },	
    							   		   error:function(jqXHR, textStatus, errorThrown){
    	   									 jQuery('body').html(errorThrown);
    										}
    							          
    							    	    });
    							    	  }
    											//jQuery(this).dialog("close");
    										},
    										"Annuler": function() { jQuery(this).dialog("close"); }
    									}
    								});
    	}
	}
}

function mass_modification(controller) { 
	controller = (controller==undefined)?'produits':controller;
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    		jQuery('#mass_modification').dialog({ modal:true, 
    							width:380,
    							buttons: { "GO": function() { 
										jQuery('#mass_modification select,#mass_modification input').each(function(i){
    														jQuery(this).appendTo('form[name="'+nom+'"]').hide();
    													});
    													jQuery('form[name="'+nom+'"]').attr('action',getBase()+controller+'/mass_modification')
    											        jQuery('form[name="'+nom+'"]').submit();
    											        jQuery(this).dialog("close");
    											      	},
    										"Annuler": function() { jQuery(this).dialog("close"); }
    							}
    							} );
	}
}


// function jquery pour ...
function checkAll(field){
	if(field.master.checked== true){
		for(var i=0; i < field.length; i++){
			field[i].checked=true;
		}
	}
	else {
		for(var i=0; i < field.length; i++){
			field[i].checked=false;
		}
	}
}

	
// end -->