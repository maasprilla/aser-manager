/* RESTO TOUCH POS **/
var tableNum=0;
var serveurId=0;
var serveur='';
var payed=1;
var product=0;
var displayed=0;
var sectionId=2;
var ungrouped=0;
var locked=0;

/*
 * this function helps to generate from printing the copy of the bill in aser B
 *  */

function custom_printing(id,redirect_url){
    var generate_number = function(id){
        jQuery.ajax({
        url:getBase()+'factures/generate_aserb_number/'+id,
        dataType:'json',
        success:function(r){ 
            if(r.success){
                jQuery('#displayed_num').text('n° '+r.no).css({'display':'inline'});
                jQuery('#displayed_num').attr('type','aserb');
                if(redirect_url.indexOf("ventes") > -1){
                    document.location.href=getBase()+'factures/view/'+id+'/standard/2';
                    print_facture(id,getBase()+'ventes/print_facture/'+id+'/1');
                }
                else {
                    print_documents('export',redirect_url);
                }
            }
            else {
               alert(r.msg);
            }
        }
    });
    };

    var desired_number = prompt("Entrer le numéro souhaitez :");
    desired_number = (desired_number)? desired_number: 0;
    if(desired_number !== 0){
        jQuery.ajax({
            url:getBase()+'factures/check_aserb_number_availability/'+id+'/'+desired_number,
            dataType:'json',
            success:function(r){ 
                if(r.success){
                    generate_number(id);
                }
                else {
                    alert("Le numéro souhaitez "+desired_number+" n'est pas dispo");
                    custom_printing(id, redirect_url);
                }
            }
        });
    }
    else { generate_number(id);}
        
}
/** Limits javascript code **/
function year_limits(){
    jQuery('#year_limits_boxe').dialog({ modal:true, 
                           show:'slide',
                           hide:'clip',
                           width:390,
                           position:'top',
                           buttons: { "GO": function() {
                                        var annee=jQuery('#umwakaYear').val();
                                        jQuery(this).dialog("close");
                                        document.location.href=getBase()+'limits'+'/index/'+annee;
                                    },
                                    "Annuler": function() { jQuery(this).dialog("close");
                                                         }
                                   }
                       });
}

function generate_monthly_limits(button){
    var month = jQuery(button).attr('month');
    var year = jQuery(button).attr('year');
    var msg = 'Mettez le min et max séparé par une virgule';
    var text = prompt(msg);
    if(text!== null){
        text = text.split(',');
        if(!isNaN(parseInt(text[0])) && !isNaN(parseInt(text[1])) && text.length==2){
          //  alert(text[0]+' '+text[1]);
            jQuery.ajax({
                global:true,
                url:getBase()+'limits/generate_monthly_limits/'+month+'/'+year+'/'+text[0]+'/'+text[1],
                dataType: "json",
                success: function(ans){
                    if(ans.success){
                        document.location.reload();
                    }
                    else alert(ans.msg);
                }
            });
        }
        else {
            alert(msg);
            generate_monthly_limits(button);
        }   
    }  
}
function set_limite_montant(input){
    var date = jQuery(input).attr('date');
    var montant = jQuery(input).val();
    //alert(date+' '+montant);
    jQuery.ajax({
        global:true,
        url:getBase()+'limits/set_montant/'+date+'/'+montant,
        dataType: "json",
        success: function(ans){
            if(ans.success){
                jQuery('img#'+date).show();
                setTimeout(function(){ 
                   jQuery('img#'+date).hide();
                    }, 
                2500);
            }
            else alert(ans.msg);
        }
    });
}

/** end of limits javascripts **/

function set_observation(button){
    var facture_id = jQuery(button).attr('facture');
    var obs = prompt("Mettez l'observation");
    jQuery.ajax({
        global:false,
        url:getBase()+'factures/set_observation/'+facture_id+'/'+obs,
        dataType: "json",
        success: function(ans){
            if(ans.success){
                jQuery('td#'+facture_id).text(obs);
            }
            else alert(ans.msg);
        }
    });
}

function last_reservation(tier_id){
    jQuery.ajax({
        global:false,
        url:getBase()+'reservations/last_reservation/'+tier_id,
        dataType: "json",
        success: function(ans){
                jQuery('textarea#last_reservation').val(ans.text);
        }
    });
}

/**
 * this function job it to fetch the list of all products with detailed 
 * names. cad contenant dans le nom la quantite restante et le PV
 */
function detailed_products_names(){
	jQuery.ajax({
		global:false,
		url:getBase()+'ventes/detailed_products_names',
		dataType: "json",
		success: function(products){
			jQuery.each(products,function(id,name){
				jQuery('select#produits option[value="'+id+'"]').text(name);
			});
		}
	});
}
/**
 * filter the list of clients to only those who are currently in a conference room
 */
function conference(){
	jQuery.ajax({
		url:getBase()+'locations/current_guests',
		dataType:'json',
		success:function(ans){
			var guests = jQuery.map(ans, function(el) { return el; }); //converting the object into an array
			jQuery('select#tierId option').each(function(){
				if(jQuery.inArray(jQuery(this).val(),guests)==-1){
					jQuery(this).hide();
				}
			});
			
		}
	});
}
/**
 * this function is used to calculate automatically
 * the quantity on for conference room proforma
 */
function proforma_qty(item){
	if(jQuery('div#tabella').attr('qty')=='1'){
		var pers = parseInt(jQuery('#pers').val());
		var jours = parseInt(jQuery('#jours').val());
		//set the quantity
		if(jQuery(item).attr('id')=='pers'){
			jQuery("input[class='qty']").each(function(){
				if(jQuery(this).val()!=''){
					jQuery(this).val(pers*jours);	
				}	
			});
		}
		else {
			jQuery(item).parents('td').next().children().children('input').val(pers*jours);
		}
	}
}
/**
 * function that helps to create a new customer/tier 
 * in the box of Modifier client details on the tabella 
 * interface
 */
function create_customer(){
	jQuery("#customer_dialog_boxe")
	.attr("title","Créer un nouveau client")
	.wrapInner('<div class="dialog"></div>');
	
	jQuery("#customer_dialog_boxe")
	.dialog({ modal:true, 
    		width:420,
    		buttons: { 
    			"Enregistrer": function() {
    				tier();
    				jQuery('#fullName').attr('disabled','disabled');
    				jQuery(this).dialog("close");
    			},
    			"Annuler": function() {jQuery(this).dialog("close");},
    			}
 	});
}

function facturation_services(factureId){
	jQuery('#services_boxe').dialog({ modal:true, 
    					width:420,
    					buttons: { 
    						"Enregistrer": function() { 
    							jQuery('#services_form').ajaxSubmit({
    								data:{ 'data[Facture][id]':factureId,
    									'data[Service][description]':jQuery('#descService').val(),
    									},
    								dataType:'json',
    								success:function(ans){
    									if(ans.success){
    										jQuery('#services_boxe').dialog("close");
    										alert('Service Enregistré!');
    										location.reload();
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
}
function service_boxe(action){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_')[1];//to get the model and the controller
	var goOn=false;
	var factureId=0;
	if(action=='edit'){
		if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)!=1) {
    		jQuery('<div id="alert" title="message">Sélectionné un et un seul produit !</div>')
    		.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    		goOn=false;
    	}
    	else {
    		factureId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
    		goOn=true;
    	}
    }
    else {
    	goOn=true;
    }
    if(goOn){
    	jQuery('#serv_boxe').load(getBase()+'services/gerer/'+factureId);
    	date();
		jQuery('#serv_boxe').dialog({
  		 	modal:true, 
    		position:'top',
    		width:380,
    		//	show:'bounce',
    		buttons: { "Enregister": function() {
    			var box=this;
    			jQuery(box).dialog('close');
    			jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    			jQuery("#serv_form").ajaxSubmit({
    				global:true,
    				data:{
    					'data[facture_id]':factureId,
    					'data[tier_id]':jQuery('#tier_id').val(),
    					'data[type_service_id]':jQuery('#type_service_id').val(),
    					'data[date]':jQuery('#Date').val()
    					},
    				dataType:'json',
					success:function(ans){
						alert(ans);
						//	jQuery(responseText).insertAfter('form[name="checkbox"] tr:first');
    					}
    				});
    			},
    			"Fermer": function() { jQuery(this).dialog("close"); 
    				if(action=='edit')
    					jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    			}
    		}
   		});
   	}
}

function service_add_row(){
	var selector='table#serv_table';
	if(jQuery(selector+' input:checked').length==0){
		var serv_description=jQuery('#serv_description').val();
		var serv_montant=jQuery('#serv_montant').val();
		var no=jQuery(selector+' tr:not(:first)').length;
		var row='tr';
    	var tr='<tr>';
    		tr=tr+'<td><input type="checkbox" name="checkbox" value="'+no+'"/></td>';
    	 	tr=tr+'<td><input type="text" name="data[Service]['+no+'][description]" value="'+serv_description+'"/></td>';
    	 	tr=tr+'<td><input type="text" name="data[Service]['+no+'][montant]" value="'+serv_montant+'"/></td>';
    	 	tr=tr+'</tr>';
    	jQuery(selector).append(tr);
  }
  else {
  	jQuery(selector+' input:checked').each(function(){
  		jQuery(this).parents('tr').remove();
  	});
  }
}

function ingredient_boxe(){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_')[1];//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length)!=1) {
    	jQuery('<div id="alert" title="message">Sélectionné un et un seul produit !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var produitId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
    	jQuery('#ing_boxe').load(getBase()+'produits/ingredient/'+produitId);
		jQuery('#ing_boxe').dialog({
  		 	modal:true, 
    		position:'top',
    		width:380,
    		//	show:'bounce',
    		buttons: { "Enregister": function() {
    			var box=this;
    			jQuery(box).dialog('close');
    			jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    			jQuery("#ing_form").ajaxSubmit({
    				global:true,
    				dataType:'json',
    				data:{'data[produit_id]':produitId},
					success:function(ans){
						if(ans.success){
							jQuery('tr[id="'+produitId+'"] td[name="pa"]').text(ans.PA);
							alert(ans.msg);
						}
						else 
							alert(ans.msg);
    					}
    				});
    			},
    			"Fermer": function() { jQuery(this).dialog("close"); 
    				jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    			}
    		}
   		});
   	}
}

function ingredient_add_row(){
	var selector='table#ing_table';
	if(jQuery(selector+' input:checked').length==0){
		var ing_id=jQuery('select#ing_id option:selected').val();
		var ing_name=jQuery('select#ing_id option:selected').text();
		var ing_qte=jQuery('#ing_qte').val();
		var no=jQuery(selector+' tr:not(:first)').length;
		var row='tr';
    	var tr='<tr>';
    		tr=tr+'<td><input type="checkbox" name="checkbox" value="'+no+'"/></td>';
    	 	tr=tr+'<td><select name="data[Ingredient]['+no+'][ingredient_id]"><option value="'+ing_id+'">'+ing_name+'</option></select></td>';
    	 	tr=tr+'<td><input type="text" name="data[Ingredient]['+no+'][qte]" value="'+ing_qte+'"/></td>';
    	 	tr=tr+'</tr>';
    	jQuery(selector).append(tr);
  }
  else {
  	jQuery(selector+' input:checked').each(function(){
  		jQuery(this).parents('tr').remove();
  	});
  }
}
/**
 * 
 */
function tabella_search(){
	reservationId=prompt('mettez le numéro de Confirmation : ');
	jQuery('td[reservation="'+reservationId+'"]').each(function(){
	//	jQuery(this).html('<span style="background-color:yellow;">'+jQuery(this).text()+'</span>');
		jQuery(this).effect("pulsate",{ times:20 }, 1000);
	});
}

/**
 * this function helps admins guys to assign an aserb number to a reservation bill
 * manually
 */

function assign_b_num(){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_')[1];//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)!=1) {
    	jQuery('<div id="alert" title="message">Sélectionné un et un seul élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
		jQuery('#assign_boxe')
		.dialog({
  	 		modal:true, 
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Assigner": function() {
    			var box=this;
    			var id=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').attr('facture');
    			var type=jQuery('#FactureChoix').val();
    			var no=(type=='CASH')?-1:parseInt(jQuery('#FactureNumero').val());
    			if((no>=1)||(type=='CASH')){
    				jQuery(box).dialog('close');
    				jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    				jQuery.ajax({
    					global:true,
    					url:getBase()+'factures/aserb_bill/'+id+'/'+type+'/'+no,
    					dataType:'json',
						success:function(ans){
							if(ans.success)
								jQuery('#'+id).text(ans.numero);
							else 
								alert(ans.msg);
							jQuery('#FactureNumero').val('');
    					},
    				});
    			}
    			else {
    				alert("Donner un numero correcte");
    			}
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    										jQuery('form[name="'+nom+'"] input[type="checkbox"]').removeAttr('checked');
    				}
    			}
   		});
    }
}

/*
 * this function helps to generate from printing the copy of the bill in aser B
 *  */

function impressionB(){
		var id=jQuery('#facture_num').attr('facture');
		var aserb_num=jQuery('#facture_num').attr('aserb_num');
		var type='SEARCH';
		var msg=(parseInt(jQuery('#facture_num').attr('reste'))<=0)?'PAYEE':'CREDIT';
		if(aserb_num==0){
    		jQuery.ajax({
				url:getBase()+'factures/aserb_bill/'+id+'/'+type,
				dataType:'json',
				success:function(r){ 
					if(r.success){
						if(r.numero){
							jQuery('#displayed_num').text('n° '+r.numero).css({'display':'inline'});
							jQuery('#signature_cash').hide();
							jQuery('#signature_credit').show();
							jQuery('#stamp').css({'color':'red'}).text(msg);
						}
						else {
							jQuery('#stamp').css({'color':'green'}).text(msg);
						}
					}
					else {
						// jQuery('#stamp').css({'color':'black','font-size':'20px'}).text('POUR CONSULTATION UNIQUEMENT');
                        jQuery('#stamp').css({'color':'black','font-size':'20px'}).text('');
					}
					print_documents();
					}
				});
		}
		else {
			if(aserb_num>0){
				jQuery('#displayed_num').text('n° '+aserb_num).css({'display':'inline'});
				jQuery('#signature_cash').hide();
				jQuery('#signature_credit').show();
				jQuery('#stamp').css({'color':'red'}).text(msg);
			}
			else {
				jQuery('#displayed_num').hide();
				jQuery('#stamp').css({'color':'green'}).text(msg);
			}
			print_documents();
		}
	
}

/*
 * this function helps to figure out le salaire de base a partir 
 * du salaire net desiree
 *  */

function salaire_net(p){
	var id=jQuery(p).attr('id');
	jQuery.ajax({
		url:getBase()+'salaires/salaire_net/'+id+'/'+jQuery(p).val(),
		dataType:'json',
		success:function(r){ 
				jQuery('#snet-'+id).text(r.sb);
			}
	});
}

/*
 * aserb function copies some bills with their details to another copie of 
 * aser named aser b or belair.
 */

function aserb(){
	var date= new Date();
	date.setMonth(date.getMonth()-1);
	var prev_month = date.getMonth()+1;
		jQuery('#aserb_boxe').dialog({ modal:true, 
    		               width:390,
    		               position:'top',
    		               buttons: { "GO": function() {
    		               				var mois=parseInt(jQuery('#FactureMoisMonth').val());
    		               				var year=jQuery('#FactureAnneeYear').val();
    		               				var montant_desire=parseInt(jQuery('#montant_desire').val());
    		               				var action=jQuery('#action').val(); //la fonction use 0 to confirm copy
    		               				if(montant_desire<=0){
    		               					alert('Mettez un  Montant correct!');
    		               				}
    		               				/*	
    		               				else if(mois!=prev_month){ //because in javascript month are 0 indexed
    		               					alert('Vous pouvez copier les factures seulements pour le mois precedent!');
    		               				}
    		               				//*/
    		               				else {
    		               					jQuery.ajax({
    		               						url:getBase()+'factures'+'/aserb/'+mois+'/'+year+'/'+montant_desire+'/'+action,
    		               						dataType:'json',
    		               						success:function(ans){
    		               							if(action=='copier'){
    		               								alert(ans.msg);
    		               							}
    		               							else {
    		               								if(ans.success){
    		               									alert('Le montant trouvé est : '+ans.total_got);
    		               								}
    		               								else {
    		               									alert(ans.msg);
    		               								}
    		               							}
    		               						}
    		               					});
    							      	}
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            					 }
    					           }
                       });
                       
	
}
/*
 * this function has been created in a night club
 *  
 * in a hurry caused by a big lady daugther of the owner of the hotel
 */

function change_pv(p){
	jQuery.ajax({
		url:getBase()+'produits/change_pv/'+jQuery(p).attr('id')+'/'+jQuery(p).val()+'/'+jQuery(p).attr('bar'),
		dataType:'json',
		success:function(r){ }
	});
}

/*
 * this function handles add,edit and delete 
 * action for abscence management
 */
function abscence(cell,remove){
	var id=(jQuery(cell).attr('id')>0)?jQuery(cell).attr('id'):null;
	var old_type=jQuery('#abscence_type option:selected').val();
	if(remove==undefined){
		if(id){
			jQuery('#abscence_boxe').load(getBase()+'abscences/edit/'+id);
		}
		else {
			jQuery('#abscence_form').find('input,textarea').val('');
			jQuery('#abscence_form').find('select option:first').attr('selected','selected');
		}
		jQuery('#abscence_boxe')
		.dialog({
  	 		modal:true, 
    		position:'top',
    		//	show:'bounce',
    		buttons: { "Enregistrer": function() {
    			 jQuery(this).dialog("close"); 
    			 var type=jQuery('#abscence_type option:selected').val();
    			 var month=jQuery('#abscence_calendar').attr('month');
    			 var year=jQuery('#abscence_calendar').attr('year');
    			 var day=jQuery(cell).attr('day');
    			 var date=year+'-'+formater(month)+'-'+formater(day);
    			 //removing the css
    			 jQuery('.calendar-day').removeClass('selected-day');
    			 
    			jQuery('#abscence_form').ajaxSubmit({
    				global:true,
    				dataType:'json',
    				data:{'data[Abscence][observation]':jQuery('#observation').val(),
    					'data[Abscence][date]':date,
    					'data[Abscence][personnel_id]':jQuery('#personnelId option:selected').val()
    					},
					success:function(ans){
						if(ans.success)
							jQuery(cell).removeClass('day-'+old_type).addClass('day-'+type).attr('id',ans.id);
						else
							alert(ans.msg)
    				},
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    									 //removing the css
    									 jQuery('.calendar-day').removeClass('selected-day');
    									}
    			}
   			});
   		}
   		else {
   			if(confirm('Voulez vous vraiment l\'effacer? ')){
   				jQuery.ajax({
    				global:true,
    				dataType:'json',
    				url:getBase()+'abscences/delete/'+id,
					success:function(ans){
						if(ans.success)
							jQuery(cell).removeClass('day-'+old_type).attr('id',0);
							 //removing the css
    			 			jQuery('.calendar-day').removeClass('selected-day');
    				},
    			});
    		}
   		}	
}

function executerLaPaie(){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_')[1];//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	
		jQuery('#paie')
		.dialog({
  	 		modal:true, 
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Executer": function() {
    			jQuery('#paie select,#paie input').each(function(i){
    				jQuery(this).appendTo('form[name="'+nom+'"]').hide();
    			});
    			var box=this;
    			jQuery(box).dialog('close');
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:true,
    				dataType:'json',
					success:function(ans){
						/*
						jQuery.each(ans.deleted ,function(i,val){
							jQuery('form[name="'+nom+'"] input[value="'+val+'"]:checkbox').parents('tr').;
						})
						//*/
    				},
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    			}
    		}
   		});
    }
}
function update_tables(){
	jQuery.ajax({
		url:getBase()+'ventes/update_tables',
		type:'get',
		dataType:'json',
		global:false,
		success:function(tables){
			jQuery.each(tables,function(i,val){
				jQuery('div#tables div#'+i).attr('class',val.class);
				jQuery('div#tables div#'+i+' span').text(val.serveur);
			});
		}
	});
}

function bonus(){
	var nom='checkbox';
	if(jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length<1) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins une facture!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	var factures= new Array;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		var etat=jQuery(this).parents('tr').children('td[name="etat"]').text();
    		if(etat=='credit'){
    			rows[i]=jQuery(this).parents('tr');
    			factures[i]=jQuery(this).val();
    		}
    	});
    		if(factures.length>0){
    			var obs=prompt('Mettez la raison du changement en bonus ?');
    			if((obs!='')&&(obs!=undefined)){
    				jQuery.ajax({
    				url:getBase()+'factures/bonus',
    				type:'POST',
    				global:true,
    				dataType:'json',
    				data:{'data[factures]':factures,
    					'data[obs]':obs,
    					},
					success:function(response){
						if(response.success){
							jQuery.each(rows ,function(i,val){
								jQuery(val).children('td[name="etat"]').text('bonus')
							});
						}
						else {
							alert(response.msg)
						}
					}
    				});
    			}
    		}
    		else {
    			alert('Séléctionnée une facture dont l\'état est crédit!')
    		}
   	 }
}

function copier_bills_dans_b(action){
	var nom='checkbox';
	if(jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length<1) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins une facture!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	var factures= new Array;
        var ids = new Array; //for bills ids only
        var dates= new Array; //will hold the date of the first selected bill. needed when reordering.
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').each(function(i){
    			rows[i]=jQuery(this).parents('tr');
    			factures[i]=[jQuery(this).val(),jQuery(this).parents('tr').children('td[name="operation"]').text()];
                ids[i] = jQuery(this).val();
                dates[i] = jQuery(this).parents('tr').children('td[name="date"]').attr("value");
    	});
    		if(factures.length>=0){
    				jQuery.ajax({
    				url:getBase()+'factures/inclure',
    				type:'POST',
    				global:true,
    				dataType:'json',
    				data:{'data[factures]':factures,
                        'data[ids]': ids,
    					'data[action]':action,
                        'data[dates]':dates
    					},
					success:function(response){
						if(response.success){
							jQuery.each(rows ,function(i,val){
								jQuery(val).children('td[name="inclure"]').text(action);
							});
                            var actionTitle=(action==1)?"Copié(s)":"Enlevé(s)";
                            alert("Facture(s) "+actionTitle);
						}
						else {
							alert(response.msg);
						}
					}
    				});
    		}
   	 }
}

function chartClose(){
	jQuery('#chartDiv').html('').hide();
	jQuery('#home').show().css({'width':'98%'});
	jQuery('#fermer').hide();
}

function chartSearch(){
	jQuery('#chart_form').ajaxSubmit({
    				global:true,
    				dataType:'json',
					success:function(ans){
						if(ans.success){
							jQuery('#home').hide();
							jQuery('#right').css({'padding':'20px'});
							jQuery('#chartDiv').html('');
							jQuery('#fermer').show();
							jQuery.jqplot.sprintf.thousandsSeparator = ' ';
							jQuery.jqplot.config.enablePlugins = true;
							var plot1b = jQuery.jqplot('chartDiv',ans.series,{
        					title: {
        						text:ans.title,
					            fontSize: '14pt',
    					    },
    					    seriesDefaults: {
        						showMarker:true,
        						pointLabels: {
     								show: true,
        						  	edgeTolerance: 5,
        						  	fontSize:'11pt'
     							  },
     							trendline: {
            						show: true,         // show the trend line
            						color: 'red',   // CSS color spec for the trend line.
  						      	}
      						},
      						axes: {
     					     	xaxis: {
               						renderer: jQuery.jqplot.CategoryAxisRenderer,
              						ticks: ans.Xaxis,
              						tickOptions: {fontSize: '11pt'},
           						}, 
           						yaxis : {
      								tickOptions: {formatString: "%'i",fontSize: '11pt'},
    								min : 0
  								}
       						},
        					legend: {
        			    		show: true,
            					location: 'se',
            					placement : "outsideGrid",
            					labels:ans.labels
        					}
    						});
    						jQuery('#chart_boxe').dialog('close');
						}
						else {
							alert(ans.msg);
						}
					}
    			});
}

function chart(defaults){
	if(defaults==undefined){
		jQuery('#chart_boxe').dialog({modal:true,
    		width:390,
    		position:'top',
    		buttons: { "GO": function() {chartSearch();},
    			"Annuler": function() { jQuery(this).dialog("close");}
    		}
    });
   }
   else {
   		chartSearch();
   }
}
function merge(controller){
	var nom='checkbox';
	if(jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length<2) {
    	jQuery('<div id="alert" title="message">Sélectionné au moins 2 enregistrements!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
    	var rows=new Array;
    	var id=null;
    	var clientRow=null;
    	jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').each(function(i){
    		 if(id!=null)
    		 	rows[i]=jQuery(this).parents('tr');
    		 else {
    			 id=jQuery(this).val();
    			 clientRow=jQuery(this).parents('tr');
    		}
    	});
    	jQuery('#merge_boxe').dialog({ modal:true, 
    		width:390,
    		position:'top',
    		buttons: { "GO": function() {
    		    var name=jQuery('#merge_name').val();
    			jQuery('form[name="'+nom+'"]').attr('action',getBase()+controller+'/merge');
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:true,
    				dataType:'json',
    				data:{'data[rowId]':id,
    					'data[name]':name
    					},
					success:function(response){
						if(response.success){
							jQuery.each(rows ,function(i,val){
								jQuery(val).remove();
							});
							jQuery(clientRow).children('td[name="nom"]').text(name);
							jQuery('#merge_boxe').dialog('close');
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
}

function rappel(){
	//searching for a new order
	jQuery.ajax({
 		type:'GET',
 		url:getBase()+'rappels/check',
 		dataType:'json',
 		global:false,
 		success:function(ans){
 			if(ans.success){
 				alert('test')
 			}
 		}	
 	})
 }
function extra_row(){
	if(jQuery('table#extras_table input:checked').length==0){
		var no=jQuery('table#extras_table tr:not(:first)').length;
		var row='tr';
    	var tr='<tr>';
    		tr=tr+'<td><input type="checkbox" name="checkbox" value="'+no+'"/></td>';
    	 	tr=tr+'<td><input type="text" name="data[extras]['+no+'][name]"/></td>';
    	 	tr=tr+'<td><input type="text" name="data[extras]['+no+'][qte]"/></td>';
    	 	tr=tr+'<td><input type="text" name="data[extras]['+no+'][prix]"/></td>';
    	 	tr=tr+'</tr>';
    	jQuery('table#extras_table').append(tr);
  }
  else {
  	jQuery('table#extras_table input:checked').each(function(){
  		jQuery(this).parents('tr').remove();
  	});
  }
}
function quantites() {
	var nom='checkbox';
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var produitId=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
            //hiding or showing other rows
    		jQuery('tr:not(tr[id="first"],div#quick_add tr, tr[id="'+produitId+'"])').toggle('fade');
    		jQuery('div#pagination').toggle('fade');
    		jQuery("#quantites").toggle('fade');
            jQuery("#quantites").html('<span id="loading">Chargement...</span>');
    		jQuery('#quantites').load(getBase()+'produits/quantites/'+produitId);
	}
	else {
		jQuery(document).ready(function(){
    	jQuery('<div id="alert" title="Message">Sélectionné un et un seul Produit!</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	});
	}
}

function edit(){
	var nom='checkbox';
	//to get the model and the controller
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	controller=info.split('_')[1];
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var id=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		var tr =jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked').parents('tr');
		jQuery('<div id="edit_boxe" title="Formulaire de Modification"></div>').insertAfter('body');
   		     		jQuery('#edit_boxe').dialog({ modal:true, 
    		               width:400,
    		               position:'center',
    		               global:false,
    		               buttons: { "Modifier": function() {
    					            jQuery('#edit_form').ajaxSubmit({
							            dataType:'json',
							             success:function(ans){
							             	if(ans.success){
							             		jQuery.ajax({
							             			type:'get',
							             			url:getBase()+controller+"/edit/"+id+'/no',
							             			success:function(ans){
							             				jQuery(tr).replaceWith(ans);
							             				jQuery('#edit_boxe').dialog("close");
							             			}
							             		});
							             		
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
    		           jQuery('#edit_boxe').load(getBase()+controller+"/edit/"+id,function(){date();});
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

function chambre(controller,chambre){
	controller=(controller==undefined)?'ventes':controller;controller=(controller==undefined)?'ventes':controller;
	chambre=(chambre==undefined)?jQuery('#chambre').val():chambre;
	jQuery.ajax({
		type:'get',
		dataType:'json',
		url:getBase()+controller+'/chambre/'+chambre,
		success:function(r){
			jQuery('#tierId option[value='+r.id+']').attr('selected','selected');
			if(r.id==0) {
				alert('Aucun client trouvé! vérifié avec la réception.');
			}
			if(controller=='ventes')
				jQuery('#chambre').val('');
		}
	});	
}

function perte(p){
	var no=jQuery(p).attr('numero');
	var sf=parseInt(jQuery(p).attr('old_value'));
	var sr=parseInt(jQuery(p).val());
    var perte=sf-sr;
    jQuery('#PerteQuantite').val(perte);
	jQuery('#perte_boxe').dialog({ modal:true, 
    					show:'slide',
    					width:420,
    					hide:'clip',
    					buttons: { "Enregistrer": function() { 
    							if(perte>0){
    								jQuery('#edit_form').attr('action',getBase()+'pertes/add').ajaxSubmit({
    								data:{'data[Perte][produit_id]':jQuery(p).attr('id'),
    									'data[Perte][stock_id]':jQuery('#stock').attr('stock'),
    									'data[Perte][description]':jQuery('#description').val(),
    									'data[quick_form]':true
    									},
    								dataType:'json',
    								success:function(ans){
    									if(ans.success){
											jQuery('#perte'+no).text(parseInt(jQuery('#perte'+no).text())+parseInt(jQuery('#PerteQuantite').val()));
											 perte=perte-parseInt(jQuery('#PerteQuantite').val());
											if(perte==0){
												jQuery(p).attr('old_value',sr);
												 jQuery('#perte_boxe').dialog('close');
											}
											jQuery('#PerteQuantite').val(perte);
											
											// total des pertes & stock finale
											var total_perte=0;
											var total_sf=0;
											var total_pv=0;
											
											jQuery('td[name="perte"]').each(function(i){
												total_perte+=parseInt(jQuery(this).text());
											})
											jQuery('#total_perte').text(total_perte)
											
											var pvu=parseInt(jQuery(p).parents('tr').children('td[name="pv"]').attr('pv'));
											jQuery(p).parents('tr').children('td[name="pv"]').text(jQuery(p).val()*pvu);
											
											
											jQuery('input[nom="reel"]').each(function(i){
												total_sf+=parseInt(jQuery(this).val());
												total_pv+=parseInt(jQuery(this).parents('tr').children('td[name="pv"]').text())
											})
											jQuery('#total_sf').text(total_sf)
											jQuery('#total_pv').text(total_pv)
											
    									}
    									else {
    										alert(ans.msg)
    									}
    								}
    								})
    							}
    							else {
    								alert('Quantitée trop élevée!')
    							}
    							},
    							"Annuler": function() { jQuery(this).dialog("close"); }
    							  }
    				});
}

function name(name){
	var regex = /^\d|chambre/i;
	var goOn=true;
	if(regex.test(name)||((name.length>0)&&(name.length<3))){
		alert('"'+name+'" n\'est pas valide comme nom de client! Mettez un nom qui ne contient pas le mot "chambre", qui ne contient pas de chiffres et qui a plus de 2 charactères.')
		return false;
	}
	else {
		return true;
	}
	
}

function total_billet(id){
	jQuery('#total').load(getBase()+'ventes/total_billet/'+id);
}

function delete_billet(id){
	jQuery.ajax({
 				type:'GET',
 				url:getBase()+'ventes/delete_billet/'+id,
 				dataType:'json',
 				success:function(response){
 						if(response.success){
 							jQuery('tr#'+id).remove();
 							total_billet(jQuery('#journal_id').text());
 						}
 						else 
 							alert(response.msg);
 				}
 		});
}

function table_changer(factureId){
	if(factureId!=0){
		if(jQuery('div#pos').attr('touch')=='on'){
			var table=prompt('Numéro de la table : ');
		}
		else {
			var table=jQuery('#VenteTable').val();
		}
		if(table==''){
			alert('Remplissez le champ table!');
		}
		else if(table!=null) {
			jQuery.ajax({
 				type:'GET',
 				url:getBase()+'ventes/table/'+factureId+'/'+table,
 				dataType:'json',
 				success:function(response){
 						if(response.success){
 							if(jQuery('div#pos').attr('touch')=='on'){
 								location.reload();
 							}
 							else {
 								jQuery('table#list_factures tr[id="'+factureId+'"] td[id="table"]').text(table);
 							}
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

function bill_number_changer(factureId){
    if(factureId!==0){
        var numero=prompt('Numéro de la facture souhaitez : ');
        
        if(numero ===''|| numero === undefined || numero === null){
            alert('Le numéro est obligatoire!');
        }
        else {
            jQuery.ajax({
                type:'POST',
                url:getBase()+'ventes/setProposedBillNumber',
                data:{'data[Vente][facture_numero]':numero,
                        'data[Vente][facture_id]':factureId
                    },
                dataType:'json',
                success:function(response){
                    if(response.success){
                        jQuery('table#list_factures tr[id="'+factureId+'"] td[id="num"]').text(numero);
                    }
                    else { 
                        alert(response.msg);
                    }
                }
            });
        }
    }
    else {
        alert('Sélectionné une facture !');
    }
}

function mass_delete(){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_')[1];//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"]):not(input[name="master"])').length)==0) {
    	jQuery('<div id="alert" title="message">Sélectionné un élément !</div>')
    	.dialog({modal:true, show:'slide',hide:'clip',buttons: { "Ok": function() { jQuery(this).dialog("close"); } }});
    }
    else {
		jQuery('<div id="effacer" title="Message">Voulez vous vraiment effacer les enregistrements ?</div>')
		.dialog({
  	 		modal:true, 
    		position:'top',
    	//	show:'bounce',
    		buttons: { "Effacer": function() {
    			var box=this;
    			jQuery('form[name="'+nom+'"]').attr('action',getBase()+info+'/delete');
    			jQuery(box).dialog('close');
    			jQuery('form[name="'+nom+'"]').ajaxSubmit({
    				global:true,
    				dataType:'json',
					success:function(ans){
						jQuery.each(ans.deleted ,function(i,val){
							jQuery('form[name="'+nom+'"] input[value="'+val+'"]:checkbox').parents('tr').remove();
						});
						if(ans.notDeleted==1)
							alert(ans.notDeleted+ ' enregistrement n\'a pas été éffacé! '+ans.msg);
						else if(ans.notDeleted>1)
							alert(ans.notDeleted+ ' enregistrements n\'ont pas été éffacés! '+ans.msg);
    				},
    			});
    			},
    			"Annuler": function() { jQuery(this).dialog("close"); 
    			}
    		}
   		});
    }
}
function journal_comptable(journal){
		jQuery('#journal_comptable').dialog({ modal:true, 
    		               width:390,
    		               position:'center',
    		               buttons: { "GO": function() {
    		               				var mois=jQuery('#ukweziMonth').val();
    		               				var journal=jQuery('#journal').val();
    		               				alert(getBase()+'compte_operations'+'/index/'+journal+'/'+mois)
    		               				jQuery(this).dialog("close");
    		               				document.location.href=getBase()+'compte_operations'+'/index/index/'+journal+'/'+mois;
    							    },
    					            "Annuler":function(){
    					            				jQuery(this).dialog("close");
    					            			}
    					           }
                       });
}

function quick_edit(tr){
	var i=0;
	var id=jQuery(tr).attr('id');
	jQuery.ajax({
 												type:'get',
 												url:getBase()+'compte_operations/quick_edit/'+id,
 												dataType:'json',
 												success:function(a){
 													jQuery('input#piece').val(a.piece);
 													jQuery('input#debit').val(a.debit);
 													jQuery('input#credit').val(a.credit);
 													jQuery('input#Date').val(a.date);
 													jQuery('textarea#libelle').val(a.libelle);
 													jQuery('select#compteId').val(a.compte_id);
 													jQuery('input#id').val(a.id);
 												}
 											});
	
}

/*
function dbclick(submit){
	
	if(submit==undefined) {
		jQuery('tr.dbclick').dblclick(function(){
			var id=jQuery(this).children('td:first').attr('id');
			jQuery('div#quick_add').load(getBase()+'operations/quick_edit/'+id);
			jQuery('div#quick_add').attr('op',jQuery(this).attr('op'));
			date();
		})
	}
	else {
		 jQuery('div#quick_add').load(getBase()+'operations/quick_edit/'+0);
		 date();
		var i=0;
		jQuery(" div#quick_add input,div#quick_add select,div#quick_add textarea").appendTo(" div#quick_add tr[name='"+i+"'] form");
		//*
		
		jQuery(" div#quick_add tr[name='"+i+"'] form").ajaxSubmit({
							beforeSend:function(){ jQuery('#loading'+i).show()},
							complete:function(){ jQuery('#loading'+i).hide()},
							success:function(responseText){
								if(responseText.match('^failure')) {
									alert(responseText.split('_')[1])
								}
								else {
			                        if(jQuery('div#quick_add').attr('op')!='0'){
			                        	jQuery('tr[op="'+jQuery('div#quick_add').attr('op')+'"]').remove();
			                        }
			                        jQuery(responseText).insertAfter('form[name="checkbox"] tr:first');
			                         defaulter();
			                         dbclick();
								}
							}
   						});
   				}
   				
}
//*/
function journal_msg(ans){
    var buttons = { "Ok, je vais le clôturer": function() { jQuery(this).dialog("close");}}
    if(!ans.disable_nembeteplus){
        buttons["Ne m'embêté plus!"]= function(){ 
                                        jQuery(this).dialog("close");
                                        jQuery.ajax({
                                            type:'post',
                                            data: {
                                                'data[Vente][factureId]':'creation'
                                            },
                                            url:getBase()+'ventes/add/'+1,
                                        });
                                    }
    }
	jQuery('<div id="alert" title="message">'+ans.msg+'</div>')
    .dialog({modal:true, show:'slide',hide:'clip',width:420,
    	buttons: buttons
    });
}

function connexion(action){
		action=(action=='Sans')?('login'):('swipe')
		document.location.href=getBase()+'personnels/'+action;
	}
	
	
function bill_cleaner(factureId){
	if(factureId==0){
		alert('Sélectionné une facture !')
	}
	else {
		if(jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name')=='1'){
			jQuery('table#list_factures tr[id="'+factureId+'"]').remove(); 
			jQuery('table#list_produits tr:not(tr:first)').remove();
			zero();
		}
		else {
			alert('La facture Sélectionné n\'est pas classée !');
		}
	}
}

function unlock(factureId,moveon){
	if(factureId>0){
		var goOn=true;
		if((jQuery('div#pos').attr('touch')=='on')&&(moveon==undefined)){
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
    		}).attr('action','unlock');
 		 } 
 		 else {
 		 	goOn=true;
 		 }
 		if(goOn){
			jQuery.ajax({
 				type:'get',
 				url:getBase()+'ventes/unlock/'+factureId+'/'+jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text(),
 				dataType:'json',
 				success:function(ans){
 					activated(jQuery('table#list_factures tr[id="'+factureId+'"]'))
                    binder();
 				},
	 		});
	 	}
	}
	else  {
		alert('Aucune facture séléctionné !');
	}
}

function separator(factureId){
	var list='';
	if(ungrouped==0){
		jQuery.ajax({
 			type:'get',
 			url:getBase()+'ventes/ungroup/'+factureId,
 			dataType:'json',
 			success:function(ans){
 				if(ans.success){
 					ungrouped=factureId;
 				}
                else {
                    ungrouped=0;
                    alert(ans.msg);
                }
                activated(jQuery('table#list_factures tr[id="'+factureId+'"]'));
 			}
 		});
	}
	else if(jQuery('table#list_produits input[type="checkbox"]:checked').length>0){
		
		jQuery('table#list_produits input[type="checkbox"]:checked').each(function(i){
            if(i>0)
			 list=list+','+jQuery(this).val();	
            else 
              list=jQuery(this).val();  
		});
		jQuery.ajax({
 		type:'get',
 		url:getBase()+'ventes/separator/'+factureId+'/'+list,
 		dataType:'json',
 		success:function(ans){
            ungrouped=0;
            if(ans.success){
     		//	jQuery('body').html(ans);
     			
     			jQuery('table#list_produits input[type="checkbox"]:checked').each(function(i){
    				jQuery(this).parents('tr').remove();	
    			});
    			addFacture(ans.Facture.id,
    						ans.Facture.numero,
    						ans.Facture.journal_id,
    						ans.Facture.beneficiaire,
    						ans.Facture.table,
    						ans.Facture.original,
    						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(),
    						ans.Facture.montant,
    						ans.Facture.reste,
    						ans.Facture.etat,
    						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="waiter"]').text(),
    						ans.Facture.date
    						);
    			
    			//update the old bill
    			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(ans.old.montant);
     			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(ans.old.original);
     			jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(ans.old.reste);
     			jQuery('.active').removeClass('active');
     			jQuery('table#list_factures tr[id="'+factureId+'"]').children().attr('class','active');
            }
            else {
                alert(ans.msg);
            }
 		},
 		error:function(a,b,c){
 			jQuery('body').html(c);
 		}
 	});
	}
	else  {
		alert('Aucune Consommation séléctionnée !');
	}
}

function direct_reduction(factureId,moveon){
	if(factureId>0){
		var goOn=true;
		if((jQuery('div#pos').attr('touch')=='on')&&(moveon==undefined)){
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
    		}).attr('action','direct_reduction');;
 		 } 
 		 else {
 		 	goOn=true;
 		 }
 		if(goOn){
			var old_montant=parseFloat(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text());
			var new_montant=prompt('Saisissez le nouveau montant : ');
			if(parseFloat(new_montant)==new_montant){
				if(new_montant>old_montant){
					alert('Le montant saisie est supérieur au précédent!');
				}
				else {
					jQuery.ajax({	
 						type:'get',
 						url:getBase()+'ventes/direct_reduction/'+new_montant+'/'+old_montant+'/'+factureId,
 						dataType:'json',
 						success:function(r){
							//update the  bill
							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(new_montant);
							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(new_montant);
 							jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(r.reduction);
 							jQuery('span#montant').text(new_montant);
 							jQuery('span#reste').text(new_montant);
 						},
 					})
 				}
 			}
 		}
	}
	else  {
		alert('Sélectionné une facture !')
	}
}
function zero(){
	factureId=0;
}

function show_acc(){
	//produit
	jQuery('fieldset#produit_select div').show();
	jQuery('fieldset#produit_select div:not(fieldset#produit_select div[type="acc"])').hide();
}

function switcher(s){
	payed=parseInt(jQuery(s).attr('value'));
	jQuery('span[name="payment"]').attr('class','boutton');
	jQuery(s).attr('class','boutton_selected');
}

function confirm_order(factureId){
	if((jQuery('#pos').attr('sama')==1)&&(jQuery('#pos').attr('mode')=='serveur')){
		jQuery.ajax({
 			type:'GET',
 			url:getBase()+'ventes/confirm_order/'+factureId,
 			dataType:'JSON',
 			success:function(response){
 				jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text('confirmee');
				//disabling some action
				jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
			 //	jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed','1');
			 	//knocking out the waiter or waitress
				
 			}
 		})
 	}
}


function groupes_show(){
	jQuery('span#groupes').show();
	jQuery('span#sous_groupes').hide();
	jQuery('fieldset#produit_select div').hide();
}

function sections_show(){
	jQuery('span#sections').show();
	jQuery('span#groupes').hide();
	jQuery('span#sous_groupes').hide();
	jQuery('fieldset#produit_select div').hide();
}

function input_filtering(produit){
	if(produit==''){
		jQuery( " fieldset#produit_select div" ).hide();
		jQuery('span#groupes').show();
	}	
	else {	
		jQuery( " fieldset#produit_select div[name^='"+produit+"']" ).show();
		jQuery( " fieldset#produit_select div:not([name^='"+produit+"'])" ).hide();
		
		jQuery('span#groupes').hide()
	}
}

function s_filtering(s){
	sectionId=jQuery(s).attr('id');

	jQuery('span#sections').hide();
	jQuery('span#groupes').show();
	jQuery('span#sous_groupes').hide();
	//groupe 
	jQuery('fieldset#groupes_select span').show();
	jQuery('fieldset#groupes_select span:not(fieldset#groupes_select span[section="'+sectionId+'"])').hide();
	//produit
	
	jQuery('fieldset#produit_select div').hide();
	/*
	jQuery('fieldset#produit_select div').show();
	jQuery('fieldset#produit_select div:not(fieldset#produit_select div[section="'+sectionId+'"])').hide();
	*/
	
	if(sectionId=='2'){
		jQuery('div[name="section"][id="2"]').hide();
		jQuery('div[name="section"][id="1"]').show();
	}
	else {
		jQuery('div[name="section"][id="1"]').hide();
		jQuery('div[name="section"][id="2"]').show();
	}
}

function g_filtering(g){
	groupeId=jQuery(g).attr('id');
	
	jQuery('span#groupes').hide();
	jQuery('span#sous_groupes').show();
	
	//sous_groupe 
	jQuery('fieldset#sous_groupes_select span').show();
	jQuery('fieldset#sous_groupes_select span:not(fieldset#sous_groupes_select span[sous="'+groupeId+'"])').hide();
	//produit
	//*
	jQuery('fieldset#produit_select div').show();
	jQuery('fieldset#produit_select div:not(fieldset#produit_select div[groupe="'+groupeId+'"])').hide();
	//*/
	if(sectionId=='2'){
		jQuery('div[name="section"][id="2"]').hide();
		jQuery('div[name="section"][id="1"]').show();
	}
	else {
		jQuery('div[name="section"][id="1"]').hide();
		jQuery('div[name="section"][id="2"]').show();
	}
}

function serveur_touch(s){
	serveurId=jQuery(s).attr('id');
	serveur=jQuery(s).text();
	jQuery('fieldset#serveurs_select span.boutton_selected').attr('class','boutton');
	jQuery(s).attr('class','boutton_selected');
}

function show_tables(){
	jQuery('div#tables').toggle();
	jQuery('div#commandes').toggle();
}

function table_state(table){
		jQuery.ajax({
			url:getBase()+'ventes/table_state/'+table,
			dataType:'json',
			success:function(ans){
				jQuery('div#tables div#'+table).attr('class',ans.state);
				jQuery('div#tables div#'+table+' span').text(ans.serveur);
			}
			
		});
		
}

function paiement_touch(factureId,moveOn){
 	if(factureId!=0){
 		if(displayed==0){
			displayed=1;
			jQuery('span[name="payment"]').attr('class','boutton'); //removing the selected button
			jQuery('span[value="'+payed+'"]').attr('class','boutton_selected'); //assigning the payed button the selected class
		
			//reset list of clients
			jQuery('select#tierId option').show();
			jQuery('input#avance').val(parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text()));
			jQuery('#paiement_form').show();
			var goOn=false;
		}
		else {
			var goOn=true;
			displayed=0;
		}

 		var tier_id=jQuery('#tierId').val();
 		var cash=parseInt(jQuery('input#avance').val());
 		var reduction=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text());
 		reduction=(reduction>0)?reduction:-1;
 		var etat='credit';
 		
 		
 	if((moveOn==undefined)){
 		if((payed==2)||(payed==3)){
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
    		}).attr('action','paiement');;
 		}
 		else if(payed==1){
 			var avance=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text());
 			var etat='payee';
 		}
 		else {
  			var avance=parseInt(jQuery('input#avance').val());
  			var montant=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text());
  			var etat='avance';
  		}
 		
 		
 		
 		if((jQuery('input#avance').val()=='')&&(payed==1)){
 			
 			alert('Le champ cash n\'est pas rempli!');
 			goOn=false;
 		}
 		else if((parseInt(jQuery('input#change').val())<0)&&(payed==1)){
 			
 			alert('Cash insuffisant!');
 			goOn=false;
 		}
 		else if((payed==4)&&!((avance<montant)&&(avance>0))){
 			alert('ce montant n\'est pas une avance correcte !');
 			goOn=false;
 		}
 	}
 	else {
 		if(payed==2){
 			jQuery('input#avance').val(0);
 			var avance=0;
 			var etat='credit';
 		}
 		else if(payed==3){
 			var avance=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text());
 			var etat='bonus';
 		}
 		goOn=true;
 	}
 		if(goOn){
 			jQuery.ajax({
 			type:'POST',
 			url:getBase()+'ventes/paiement/',
 			data:{'data[Vente][factureId]':factureId,
 				'data[Vente][avance]':avance,
 				'data[Vente][tier_id]':tier_id,
 				'data[Vente][reduction]':reduction,
 				'data[Vente][cash]':cash,
 				'data[Vente][etat]':etat,
 				'data[Vente][montant]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(),
 				'data[Vente][original]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(),
 				'data[Vente][date]':jQuery('table#list_factures tr[id="'+factureId+'"] td[id="date"]').text(),
 				'data[Vente][journal_id]':jQuery('table#list_factures tr[id="'+factureId+'"]').attr('journal'),
 				},
 			dataType:'JSON',
 			success:function(response){
 				if(response.success) {
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text(response.etat);
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(response.montant);
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(response.original);
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text(response.reduction);
 					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(response.reste);
 					jQuery('input#avance').val(0);
 					//disabling some actions
 					jQuery('span[name="disable"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('span[name="classer"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('span[name="annuler"]').attr('class','boutton_disabled').removeAttr('onclick').unbind('click');
 					jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name','1');
 					
 					//table stuff
					table_state(tableNum);
					
					jQuery('input#avance').val('');
					jQuery('input#change').val('');
					jQuery('span#paiement_form').hide();
					jQuery('#tierId').val(0);
				//	print_facture(factureId,undefined,undefined,undefined,true)
					payed=1;
					jQuery('span[name="payment"] [value="'+payed+'"]').attr('class','boutton_selected');
					displayed=0;
					
					
 				}
 				else {
 					alert(response.msg);
 				}
 			},
 			error:function(a,b,c){
 				alert(jQuery('body').html(c));
 			}
 		});
 		}
 	
 	}
 	else {
 		alert('Sélectionner une facture !');
 	}
 }

function table(t){
	tableNum=jQuery(t).attr('id');
	var state=jQuery(t).attr('class');
	jQuery(t).attr('class','table_orange');
	//fetching table corresponding bills
	//*
	jQuery.ajax({
 			type:'GET',
 			url:getBase()+'ventes/touchscreen/'+tableNum,
 			success:function(response){
 				jQuery('table#list_factures tr:not(tr:first)').remove();
 				jQuery('table#list_produits tr:not(tr:first)').remove();
 				factureId=0;
 			    consoId=0;
 				
 				if(!response.match(/^\s*$/)){ //this regex test to see if the response contains nothing or just whitespace
 			    	//*
 					jQuery('table#list_factures tr:not(tr:first)').remove();
 					jQuery('table#list_produits tr:not(tr:first)').remove();
 					jQuery(response).insertAfter('table#list_factures tr:first');
				
					if(jQuery('table#list_factures tr:nth-child(2)').length==1){
						activated(jQuery('table#list_factures tr:nth-child(2)'));
					}
					//*/
 				}
 				
 				//displaying the commande interface
 				jQuery('div#tables').slideUp('slow');
				jQuery('div#commandes').toggle();
				jQuery(t).attr('class',state);
				
				
 			}
 	});
 	//*/
		
}
function resto_touch_create(el){
	if(jQuery(el).attr('type')=='avec'){
		product=el;
		show_acc();
		var goOn=false;
	}
	else if((jQuery(el).attr('type')=='acc')&&(product!=0)) {
			var acc_id=jQuery(el).attr('id');
		 	var produit_id=jQuery(product).attr('id')
 			var produit=jQuery(product).attr('name')+' ('+jQuery(el).attr('name')+')';
 			produit=produit.toUpperCase();
 			var goOn=true;
	}
	else {
		var produit_id=jQuery(el).attr('id');
 		var produit=jQuery(el).attr('name').toUpperCase();
 		var goOn=true;
 		var acc_id=null;
	}
if(goOn&&!locked){  // block new items to be added before the first order has return.
 	var table=tableNum;
 	var PU =(jQuery('#autrePU').val()!='')?jQuery('#autrePU').val():'';
 	var quantite=parseInt(jQuery('#VenteQuantite').val());
 	var total=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text());
 	factureId=(factureId==0)?('creation'):(factureId);
 	var goOn=true;
 	if(factureId!='creation'){
 		var classee=jQuery('table#list_factures tr[id="'+factureId+'"]').attr('name');
 		var printed=jQuery('table#list_factures tr[id="'+factureId+'"]').attr('printed');
 		if((classee=='1')||(printed=='1')){
 			factureId='creation';
 		}
 	}
 //*
 if((serveurId==0)&&(factureId=='creation')){
 	alert('Sélectionné un serveur!');
 	goOn=false;
 }
 else if((factureId!='creation')&&(jQuery('#pos').attr('multi_serveur')==0)){
 	var currentServeur=jQuery('table#list_factures tr[id="'+factureId+'"] td[id="waiter"]').text();
 	if((currentServeur!='')&&(serveur!=currentServeur)){
 		alert('Le serveur doit être le même que celui qui a crée la facture!');
 		goOn=false;
 	}
 }
 
 if(goOn){
 	jQuery.ajax({
	//	beforeSend:function(){ jQuery('#message_res').html('<span id="loading">Enregistrement...</span>')},
//	    complete:function(){ jQuery('#message_res').html('')},
		type:'POST',
		global:false,
		url:getBase()+'ventes/add',
		data:{ 'data[Vente][quantite]':quantite,
			'data[Vente][produit_id]':produit_id,
			'data[Vente][personnel_id]':serveurId,
				'data[Vente][tier_id]':null,
				'data[Vente][table]':table,
				'data[Vente][factureId]':factureId,
				'data[total]':total,
				'data[Vente][reduction]':parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text()),
				'data[Vente][beneficiaire]':'',
				'data[Vente][acc_id]':acc_id,
				'data[Vente][PU]':PU
		},
		dataType:'json',
		beforeSend:function(){ locked=1;},
		complete:function(){ locked=0;},
		success:function(r){
			if(r.success){
				if(factureId=='creation'){
					facture_row(r.factureId,r.factureNum,r.journal,'',table,r.original,r.reduction,r.montant,r.montant,'en_cours',serveur,r.date);
				    vente_row('',produit_id,produit,quantite,r.PU,r.PT,r.consoId,r.printed);
					consoId=0;
					jQuery('input#avance').text('testing');
					
					//table stuff
					jQuery('div#'+tableNum).attr('class','table_red');
				}
				else {
					vente_row('',produit_id,produit,quantite,r.PU,r.PT,r.consoId,r.printed);
					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(r.montant);
					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reste"]').text(r.montant);
					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(r.original);
					jQuery('table#list_factures tr[id="'+factureId+'"] td[id="etat"]').text('en_cours');
 	                consoId=0;
				}
				//reseting the waiter
				jQuery('span.boutton_selected').attr('class','boutton');
				product=0;
 				jQuery('#VenteQuantite').val(1);
 				jQuery('#autrePU').val('');
				if(jQuery('#pos').attr('serveur_id')==''){
					serveurId=0;
				}
			}
			else if(r.choix!=undefined){
				journal_msg(r);
			}
			else{
				alert(r.msg);
			}
    	},
        error:function(jqXHR, textStatus, errorThrown){
    	    jQuery('body').html(errorThrown);
    	}
  	});
	}
}
 }
/** END OF TOUCH POS **/
function global_bill(){
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var id=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		jQuery('#global_bill_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:390,
    		               position:'top',
    		               buttons: { "GO": function() {
    		               				var date1=jQuery('#Date1').val();
    		               				var date2=jQuery('#Date2').val();
    		               				var xls=jQuery('#xls:checked').length;
    		               				jQuery(this).dialog("close");
    		               				var controller=jQuery('div#tabella').attr('type');
    		               				document.location.href=getBase()+'tiers'+'/global_bill/'+id+'/'+date1+'/'+date2+'/'+xls;
    		               				
    							    },
    					            "Annuler": function() { jQuery(this).dialog("close");
    					            					 }
    					           }
                       });
	}
	else {
		        jQuery(document).ready(function(){
    	        jQuery('<div id="alert" title="Message">Sélectionné un et un seul client!</div>')
    	        .dialog({modal:true, show:'slide',hide:'clip',
    							buttons: { "Ok": function() { jQuery(this).dialog("close"); } }
    							});
    	               });
	}
}



function upload(){
	jQuery('#file').trigger('click');
	var nom='checkbox';
	var	info=jQuery('form[name="'+nom+'"]').attr('id');
	info=info.split('_');//to get the model and the controller
	if((jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').length==1)) {
		var id=jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').val();
		jQuery('#upload_boxe').dialog({ modal:true, 
    					   show:'slide',
    		               hide:'clip',
    		               width:390,
    		               position:'top',
    		               buttons: { "GO": function() {
								jQuery('#upload').ajaxSubmit({
									data:{'data[Groupe][id]':id},
									dataType:'json',
									success:function(ans){
										if(ans.success){
											jQuery('form[name="'+nom+'"] input[type="checkbox"]:checked:not(input[name="master"])').removeAttr('checked');
											jQuery('#upload_boxe').dialog("close");
										}
										else {
											alert(ans.msg)
										}
									},
								
								})
							},
							"Annuler": function() { jQuery(this).dialog("close");
    					           					 }
    					     }
                       });
	}
	else {
		alert('Séléctionné un et un seul élèment !')
	}
}
/*
function facture_removal(factureId,consoId,moveOn){
   
 
 	var goOn=true;
  if(((factureId!=0)&&(consoId=='facture'))&&(moveOn==undefined)){
 	if(confirm('Voulez vous vraiment annuler cette facture ?')){
 		 if(jQuery('div#pos').attr('touch')=='on'){
 		 	goOn=false;
 		 	jQuery('div#annulation').dialog({
  	 		 modal:true, 
    		width:300,
    		position:'top',
    	//	show:'bounce',
    		buttons: { 
    			"Annuler": function() { jQuery(this).dialog("close"); 
    									goOn=false;
    					}
    		}
    	} );
 		 } 
 		 else {
 		 	goOn=true;
 		 }
 	}
 	else {
 		 goOn=false;
 	}
 }
 else if(((factureId!=0)&&(consoId!='facture')&&(consoId!=0))&&(moveOn==undefined)){
 					var printed=jQuery('table#list_produits tr[id="'+consoId+'"]').attr('printed');
 					 if((jQuery('div#pos').attr('touch')=='on')&&(printed=='1')&&(moveOn==undefined)){
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
    					}).attr('action','effacer_conso');
 					 }
 }
 else {
 	goOn=true;
 }
 if(goOn){
 	if(factureId!=0){
 		var obs=jQuery('#VenteObservation').val();
 		if((obs=='')&&(consoId=='facture')){
 			alert('Veuillez mentioné le motif de l\'annulation dans le champ observation !');
 		}
 		else {
 			var reduction=parseInt(jQuery('table#list_factures tr[id="'+factureId+'"] td[id="reduction"]').text());
 			var quantite=parseInt(jQuery('#VenteQuantite').val());
 			jQuery.ajax({
 			type:'GET',
 			dataType:'json',
 			url:getBase()+'ventes/removal/'+factureId+'/'+consoId+'/'+quantite+'/'+obs+'/'+reduction,
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
 			//		print_bon(factureId,1,'COMMANDE ANNULEE','COMMANDE ANNULEE','');
 					
 					factureId=0;
 					consoId=0;
 					
 					//updating the quantity in produit full name
 					jQuery('table#list_produits tr').each(function(){
 					  	if(jQuery('#pos').attr('magasin')==1){
 					  		var produit_id=jQuery(this).children('td[name="produit"]').attr('id');
 					  		var quantite=parseInt(jQuery(this).children('td[id="quantite_vente"]').text());
 					  		var full_name=jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 					  		.html();
 					  		if(full_name!=null){
 					  			var new_quantite=parseInt(full_name.split('_')[1])+quantite;
 					  			jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  				.html(full_name.split('_')[0]+'_'+new_quantite+'_'+full_name.split('_')[2]);
 				  			}
 				  			
 				  		}
 					});
 					
 					//table state
 					table_state(tableNum);
 				}
 				else if(consoId!=0) {
 						var montant=response.montant;
 						var produit_id=jQuery('table#list_produits tr[id="'+consoId+'"] td[name="produit"]').attr('id');
	 				  	var produitName=jQuery('table#list_produits tr[id="'+consoId+'"] td[name="produit"]').text();
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="montant"]').text(montant);
						jQuery('table#list_factures tr[id="'+factureId+'"] td[id="original"]').text(response.original);
						jQuery('span#montant').text(montant);
						jQuery('#reste').text(montant);
						if(response.quantite==0){
							jQuery('table#list_produits tr[id="'+consoId+'"]').remove();
						}
						else {
							jQuery('table#list_produits tr[id="'+consoId+'"] td[id="quantite_vente"]').text(response.quantite);
							jQuery('table#list_produits tr[id="'+consoId+'"] td[id="prix"]').text(response.PT);
						}
						jQuery('#VenteQuantite').val(1);
						//updating the quantity in produit full name
 					  	if(jQuery('#pos').attr('magasin')==1){
 				  			var full_name=jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  			.html();
 				  			if(full_name!=null){
 				  				var new_quantite=parseInt(full_name.split('_')[1])+quantite;
 				  				jQuery('select[id="produits"] option[value="'+produit_id+'"]')
 				  				.html(full_name.split('_')[0]+'_'+new_quantite+'_'+full_name.split('_')[2]);
 				  			}
	 				  	}
	 				  	//commande d'annulation'
 				//		print_bon(factureId,1,'COMMANDE ANNULEE','COMMANDE ANNULEE',quantite+'_'+produitName);
 					
 				}
 				else {
 					alert('Sélectionner une consommation !')
 				}
 			}
 			else { alert(response.msg)}
 			}
	 		})
	 	}
 	}
 	else {
 		alert('Sélectionner une facture !');
 	}
 	}
 }
 //*/