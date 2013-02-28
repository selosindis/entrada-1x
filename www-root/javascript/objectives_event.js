	var mapped = [];
	var listed = [];
	jQuery(document).ready(function($){
		jQuery('.objectives').hide();
		/**
		* @todo fix bugs in draggable/droppable when version control added
		* bugs include: dropping doesn't work when list expanded, artifacts display from draggable elements after drop
		*/
		// jQuery('.draggable').draggable({
		// 	revert:true
		// });

		// jQuery('.droppable').droppable({
		// 	drop: function(event,ui){										
		// 		var id = jQuery(ui.draggable[0]).attr('data-id');
		// 		var ismapped = jQuery.inArray(id,mapped);
		// 		if(ismapped == -1){
		// 			var title = jQuery('#objective_title_'+id).attr('data-title');
		// 			var description = jQuery('#objective_'+id).attr('data-description');										
		// 			var list = jQuery('#objective_'+id).parents('.objective-set').attr('data-list');
		// 			jQuery('#check_objective_'+id).attr('checked','checked');
		// 			mapObjective(id,title,description,list,true);
		// 		}
		// 		jQuery(this).removeClass('hover');											
		// 	},
		// 	over:function(event,ui){
		// 		jQuery(this).addClass('hover');
		// 	},
		// 	out: function(event,ui){
		// 		jQuery(this).removeClass('hover');	
		// 	}
		// });

		jQuery('.objective-remove').live('click',function(){
			console.log($(this));
			var id = jQuery(this).attr('data-id');
			var list = jQuery('#mapped_objective_'+id).parent().attr('data-importance');
			var importance = 'checked';
			if(list == "flat"){
				importance = 'clinical';
			}			
			unmapObjective(id,list,importance);	
			return false;						
		});

		jQuery('.checked-objective').live('change',function(){
			var id = jQuery(this).val();
			// parents will return all sets above that objective, which for anything other than curriculum objectives will be an array
			// this grabs all parents above the object and then fetches the list from the immediate (last) parent
			var sets_above = jQuery(this).parents('.objective-set');
			var list = jQuery(sets_above[sets_above.length-1]).attr('data-list');

			var title = jQuery('#objective_title_'+id).attr('data-title');
			var description = jQuery('#objective_'+id).attr('data-description');
			var lkey = jQuery.inArray(id,listed);
			if(lkey == -100 && !jQuery(this).is(':checked')){
				jQuery('#objective_remove_'+id).trigger('click');
			}
			if (jQuery(this).is(':checked')) {
				mapObjective(id,title,description,list,true);
			} else {
				var importance = 'checked';
				if(list == "flat"){
					importance = 'clinical';
				}							
				unmapObjective(id,list,importance);
			}
			
		});

		jQuery('.checked-mapped').live('change',function(){
			var id = jQuery(this).val();
			// parents will return all sets above that objective, which for anything other than curriculum objectives will be an array
			// this grabs all parents above the object and then fetches the list from the immediate (last) parent
			var sets_above = jQuery(this).parents('.mapped-list');
			var list = jQuery(sets_above[sets_above.length-1]).attr('data-importance');

			var title = jQuery('#mapped_objective_'+id).attr('data-title');
			var description = jQuery('#mapped_objective_'+id).attr('data-description');
			if (jQuery(this).is(':checked')) {
				mapObjective(id,title,description,list,false);
			} else {			
				console.log('No Remove Element');
				var importance = 'checked';
				if(list == "flat"){
					importance = 'clinical';
				}					
				console.log('id '+id+' list '+ list+' importance '+importance)
				if(jQuery('#mapped_objective_'+id).is(':checked')){
					mapObjective(id,title,description,list,false);
				}else{
					unmapObjective(id,list,importance);
				}				
			}
		});		

		jQuery('.mapping-toggle').click(function(){
			var state = $(this).attr('data-toggle');
			if(state == "show"){
				$(this).attr('data-toggle','hide');
				$(this).html('Hide Objective Sets');
				jQuery('.mapped_objectives').animate({width:'48%'},400,'swing',function(){
					//jQuery('.objectives').animate({display:'block'},400,'swing');											
					jQuery('.objectives').css({width:'0%'});
					jQuery('.objectives').show();
					jQuery('.objectives').animate({width:'48%'},400,'linear');
				});										
			}else{
				$(this).attr('data-toggle','show');
				$(this).html('Show Objective Sets');
				jQuery('.objectives').animate({width:'0%'},400,'linear',function(){
					jQuery('.objectives').hide();
					jQuery('.mapped_objectives').animate({width:'100%'},400,'swing');
				});																				
			}
		});

		/**
		* Init Code
		*/

		jQuery('#event-topics-toggle').trigger('click');

		if(jQuery('#mapped_hierarchical_objectives').children('li').length == 0 && jQuery('#mapped_flat_objectives').children('li').length == 0){
			jQuery('#toggle_sets').trigger('click');
		}			

		//load mapped array on page load
		jQuery('#checked_objectives_select').children('option').each(function(){
			mapped.push($(this).val());
		});
		jQuery('#clinical_objectives_select').children('option').each(function(){
			mapped.push($(this).val());
		});		

		jQuery('#mapped_primary_objectives').children('li').each(function(){
			if(jQuery(this).attr('data-id') !== undefined && jQuery(this).attr('data-id')){
				listed.push(jQuery(this).attr('data-id'));
			}
		});
		jQuery('#mapped_secondary_objectives').children('li').each(function(){
			if(jQuery(this).attr('data-id') !== undefined && jQuery(this).attr('data-id')){
				listed.push(jQuery(this).attr('data-id'));
			}
		});		
		jQuery('#mapped_tertiary_objectives').children('li').each(function(){
			if(jQuery(this).attr('data-id') !== undefined && jQuery(this).attr('data-id')){
				listed.push(jQuery(this).attr('data-id'));
			}
		});		

		jQuery('#mapped_flat_objectives').children('li').each(function(){			
			if(jQuery(this).attr('data-id') !== undefined && jQuery(this).attr('data-id')){
				listed.push(jQuery(this).attr('data-id'));
			}
		});		

	});

	function unmapObjective(id,list,importance){
		var key = jQuery.inArray(id,mapped);
		if(key != -1){
			mapped.splice(key,1);
		}
		var lkey = jQuery.inArray(id,listed);
		console.log('key '+key+' id '+id+' list '+list+' importance '+importance);
		if(lkey == -1){
			importance = 'checked';
		}

		jQuery("#"+importance+"_objectives_select option[value='"+id+"']").remove();				
		jQuery('#check_objective_'+id).attr('checked','');
		jQuery('#check_mapped_'+id).attr('checked','');
		jQuery('#text_container_'+id).remove();
		if(jQuery('#objective_remove_'+id).length > 0){
			jQuery('#mapped_objective_'+id).remove();
		}
		var children_exist = jQuery("#mapped_event_objectives li").length;
		if(lkey == -1 && !children_exist){
			if(jQuery('#'+list+'-toggle').hasClass('expanded')){
				jQuery('#'+list+'-toggle').removeClass('expanded');
				jQuery('#'+list+'-toggle').addClass('collapsed');
				var d = jQuery('#'+list+'-toggle').next();
				jQuery(d).slideUp();
			}				
		}
		var mapped_siblings = false;		
		jQuery('#objective_'+id).siblings('li.objective-container').each(function(){
			var oid = jQuery(this).attr('data-id');
			if(jQuery('#check_objective_'+oid).attr('checked')){
				mapped_siblings = true;
			}
		});
		jQuery('#objective_'+id).parents('.objective-list').each(function(){
			var mapped_cousins = false;
			var pid = jQuery(this).attr('data-id');
			if(mapped_siblings == false){
				jQuery('#objective_list_'+pid+' > li').each(function(){
					var cid = jQuery(this).attr('data-id');
					if(jQuery('#check_objective_'+cid).attr('checked')){
						mapped_cousins = true;
					}
				});
				if(mapped_cousins == false){
					jQuery('#check_objective_'+pid).attr('checked','');				
					jQuery('#check_objective_'+pid).attr('disabled',false);
				}
			}								
		});		

	}

	function mapObjective(id,title,description,list,create){
		var key = jQuery.inArray(id,mapped);	
		var lkey = jQuery.inArray(id,listed);		
		console.log(id);
		console.log(key);
		console.log(mapped);
		if(key != -1) return;	
		var importance = 'checked';	
		if(list === undefined || !list){			
			list = 'flat';
		}								
		if(list == 'flat'){
			importance = 'clinical';
		}

		if(description === undefined || !description || description == null || description == 'null'){
			description = '';
		}
		
		if(create && lkey == -1 && key == -1){
			var li = jQuery(document.createElement('li'))
							.attr('class','mapped-objective')
							.attr('id','mapped_objective_'+id)
							.attr('data-title',title)
							.attr('data-description',description)
							.html('<strong>'+title+'</strong>');
			var desc = jQuery(document.createElement('div'))
							.attr('class','objective-description')
							.attr('data-description',description);
			var sets_above = jQuery('#objective_'+id).parents('.objective-set');
			var set_id = jQuery(sets_above[sets_above.length-1]).attr('data-id');
			var set_name = jQuery('#objective_title_'+set_id).attr('data-title');
			if(set_name){
				jQuery(desc).html("From the Objective Set: <strong>"+set_name+"</strong><br/>");
			}
			jQuery(desc).append(description);
			
			jQuery(li).append(desc);
			var controls = 	jQuery(document.createElement('div'))
								.attr('class','event-objective-controls');			
			var check = jQuery(document.createElement('input'))
							.attr('type','checkbox')
							.attr('class','checked-mapped')
							.attr('id','check_mapped_'+id)
							.attr('checked','checked');	
			var rm = jQuery(document.createElement('img'))
							.attr('src',SITE_URL+'/images/action-delete.gif')
							.attr('data-id',id)
							.attr('class','objective-remove list-cancel-image')
							.attr('id','objective_remove_'+id);
			
			jQuery(controls).append(rm);
			jQuery(li).append(controls);
			//jQuery(li).append(rm);			
			jQuery('#mapped_event_objectives').append(li);
			jQuery('#mapped_event_objectives .display-notice').remove();
			jQuery('#objective_'+id).parents('.objective-list').each(function(){
				var id = jQuery(this).attr('data-id');
				jQuery('#check_objective_'+id).attr('checked','checked');
				jQuery('#check_objective_'+id).attr('disabled',true);
			});		
			if(jQuery('#event-toggle').hasClass('collapsed')){
				jQuery('#event-toggle').removeClass('collapsed');
				jQuery('#event-toggle').addClass('expanded');
				var d = jQuery('#event-toggle').next();
				console.log(d);
				jQuery(d).slideDown();
			}
			if(!jQuery('#event-list-wrapper').is(':visible')){
				jQuery('#event-list-wrapper').show();
			}
			importance = 'checked';			
			list = 'event';
		}

		var text_label = jQuery(document.createElement('label'))
							.attr('for','objective_text_'+id)
							.attr('class','content-small')
							.attr('id','objective_'+id+'_append')
							.attr('style','vertical-align:middle;')
							.html('Provide your sessional free-text objective below as it relates to this curricular objective.');

		if(importance == 'checked' && list != 'event'){
			var text_div = jQuery(document.createElement('div'))
							.attr('id','text_container_'+id)
							.attr('class','objective_text_container')
							.attr('data-id',id);
			var text = jQuery(document.createElement('textarea'))
							.attr('name','objective_text['+id+']')
							.attr('id',"objective_text_"+id)
							.attr('data-id',id) 
							.attr('class',"expandable")
							.attr('style',"height: 28px; overflow: hidden;");
							console.log(text);
			jQuery(text_div).append(text_label).append(text);
			jQuery('#mapped_objective_'+id).append(text_div);	
		}

		
		jQuery('#check_objective_'+id).attr('checked','checked');
		jQuery('#check_mapped_'+id).attr('checked','checked');
		if(jQuery("#"+importance+"_objectives_select option[value='"+id+"']").length == 0){
			var option = jQuery(document.createElement('option'))				
							.val(id)
							.attr('selected','selected')
							.html(title);														
			jQuery('#'+importance+'_objectives_select').append(option);
		}

		jQuery('#objective_'+id).parents('.objective-list').each(function(){
			var id = jQuery(this).attr('data-id');
			jQuery('#check_objective_'+id).attr('checked','checked');
			jQuery('#check_objective_'+id).attr('disabled',true);
		});			

		mapped.push(id);								
	}