var EDITABLE = false;
var loaded = [];
jQuery(document).ready(function(){
	jQuery('.objective-collapse-control').live('click',function(){
		var id = jQuery(this).attr('data-id');
		if(jQuery('#children_'+id).is(':visible')){
			jQuery('#children_'+id).slideUp();
		}else if(loaded[id] === undefined || !loaded[id]){
			jQuery('#objective_title_'+id).trigger('click');
		}else{
			jQuery('#children_'+id).slideDown();
		}
	});
	jQuery('.objective-edit-control').live('click',function(){
		var id = jQuery(this).attr('data-id');
		var code = jQuery('#objective_'+id).attr('data-code');
		var description = jQuery('#objective_'+id).attr('data-description');
		var name = jQuery('#objective_'+id).attr('data-name');
		//popup form modal for editing
		jQuery('#form_code').val(code);
		jQuery('#form_description').val(description);
		jQuery('#form_name').val(name);
	});		
	jQuery('.objective-add-control').live('click',function(){
		var id = jQuery(this).attr('data-id');
		//popup empty form modal
	});		
	jQuery('.objective-title').live('click',function(){
		var id = jQuery(this).attr('data-id');		
		var children = [];
		if (loaded[id] === undefined || !loaded[id]) {
			jQuery.ajax({
							url:SITE_URL+'/api/fetchobjectives.api.php',
							data:{'objective_id':id},
							success:function(data,status,xhr){								
								loaded[id] = jQuery.parseJSON(data);
								children = loaded[id];
								buildDOM(children,id);	
							}
					});

		} else if (jQuery('#children_'+id).is(':visible')) {
			jQuery('#children_'+id).slideUp();	
		} else {
			// children = loaded[id];	
			// buildDOM(children,id);
			jQuery('#children_'+id).slideDown();	
		}
		
		
		
	});

	jQuery('#expand-all').click(function(){
		jQuery('.objective_title').each(function(){
			jQuery(this).trigger('click');
		});
	});
	
	
	jQuery(".objective-edit-control").live("click", function(){
		var objective_id = jQuery(this).attr("data-id");
		var modal_container = jQuery(document.createElement("div"));
		
		modal_container.load(SITE_URL + "/admin/settings/manage/objectives?org=1&section=edit&id=" + objective_id + "&mode=ajax");
		
		modal_container.dialog({
			title: "Edit Objective",
			modal: true,
			draggable: false,
			resizable: false,
			width: 700,
			minHeight: 550,
			maxHeight: 700,
			buttons: {
				Cancel : function() {
					jQuery(this).dialog( "close" );
				},
				Save : function() {
					console.log(modal_container.children("form").serialize());
					jQuery(this).dialog( "close" );
				}
			},
			close: function(event, ui){
				modal_container.dialog("destroy");
			}
		});
		return false;
	});
	
	jQuery(".objective-add-control").live("click", function(){
		var parent_id = jQuery(this).attr("data-id");
		var modal_container = jQuery(document.createElement("div"));
		var url = SITE_URL + "/admin/settings/manage/objectives?org=1&section=add&mode=ajax&parent_id="+parent_id;
		modal_container.load(url);
		
		modal_container.dialog({
			title: "Add New Objective",
			modal: true,
			draggable: false,
			resizable: false,
			width: 700,
			minHeight: 550,
			maxHeight: 700,
			buttons: {
				Cancel : function() {
					jQuery(this).dialog( "close" );
				},
				Save : function() {
					console.log(modal_container.children("form").serialize());
//					jQuery.ajax(function(){
//						url: url
//					});
					jQuery(this).dialog( "close" );
				}
			},
			close: function(event, ui){
				modal_container.dialog("destroy");
			}
		});
		return false;
	});
});

function buildDOM(children,id){
	var container,title,title_text,controls,check,d_control,e_control,a_control,description,child_container;
	jQuery('#children_'+id).hide();
	for(i = 0;i<children.length;i++){
		//Javascript to create DOM elements from JSON response
		container = jQuery(document.createElement('li'))
					.attr('class','objective-container draggable')
					.attr('data-id',children[i].objective_id)
					.attr('data-code',children[i].objective_code)
					.attr('data-name',children[i].objective_name)
					.attr('data-description',children[i].objective_description)					
					.attr('id','objective_'+children[i].objective_id);
		if(children[i].objective_code){
			title_text = children[i].objective_code+': '+children[i].objective_name
		}else{
			title_text = children[i].objective_name;
		}
		title = 	jQuery(document.createElement('div'))
					.attr('class','objective-title')
					.attr('id','objective_title_'+children[i].objective_id)
					.attr('data-id',children[i].objective_id)
					.attr('data-title',title_text)
					.html(title_text);

		controls = 	jQuery(document.createElement('div'))
					.attr('class','objective-controls');
						
		//this will need to change at some point
		// c_control = jQuery(document.createElement('i'))
		// 			.attr('class','objective-collapse-control')
		// 			.attr('data-id',children[i].objective_id)
		// 			.html('Collapse');
		if(EDITABLE == true){						
			e_control = jQuery(document.createElement('i'))
						.attr('class','objective-edit-control')
						.attr('data-id',children[i].objective_id);
			a_control = jQuery(document.createElement('i'))
						.attr('class','objective-add-control')
						.attr('data-id',children[i].objective_id);		
		} else {
			check = 	jQuery(document.createElement('input'))
						.attr('type','checkbox')
						.attr('class','checked-objective')
						.attr('id','check_objective_'+children[i].objective_id)
						.val(children[i].objective_id);	
		}
		description = 	jQuery(document.createElement('div'))
						.attr('class','objective-description')
						.attr('id','description_'+children[i].objective_id)
						.html(children[i].objective_description);
		child_container = 	jQuery(document.createElement('div'))
							.attr('class','objective-children')
							.attr('id','children_'+children[i].objective_id);
		child_list = 	jQuery(document.createElement('ul'))
							.attr('class','objective-list')
							.attr('id','objective_list_'+children[i].objective_id);													
		jQuery(child_container).append(child_list);			
		jQuery(controls).append(check);
		if(EDITABLE == true){
		jQuery(controls).append(e_control)
						.append(a_control);
		}
		jQuery(container).append(title)
							.append(controls)
							.append(description)
							.append(child_container);
		jQuery(container).draggable({
								revert:true
							});
		jQuery('#objective_list_'+id).append(container);
	}	

	if(children.error !== undefined){
		var warning = jQuery(document.createElement('li'))
						.attr('class','display-notice')
						.html(children.error);
		jQuery('#objective_list_'+id).append(warning);
	}

	jQuery('#children_'+id).slideDown();
}
