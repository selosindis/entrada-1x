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
					var url = modal_container.children("form").attr("action");
					jQuery.ajax({
						url: url,
						type: "POST",
						async: false,
						data: modal_container.children("form").serialize(),
						success: function(data) {
							var jsonData = JSON.parse(data);
							
							if (jsonData.status == "success") {
								var list_item = jQuery("#objective_"+objective_id);
								var order = jsonData.updates.objective_order;
								var objective_parent = jsonData.updates.objective_parent;
								
								jQuery("#objective_"+objective_id).remove();
								
								if (jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent).children().length > 0) {
									if (jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent).children().length != order) {
										jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent + " li").eq(order).before(list_item)
									} else {
										jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent).append(list_item);
									}
								}
							}
						}
					});
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
				Add : function() {
					var url = modal_container.children("form").attr("action");
					jQuery.ajax({
						url: url,
						type: "POST",
						async: false,
						data: modal_container.children("form").serialize(),
						success: function(data) {

							var jsonData = JSON.parse(data);
							
							var order = jsonData.updates.objective_order;
							
							if (jsonData.status == "success") {
								
								var objective_parent = jsonData.updates.objective_parent;
								var list_item = jQuery(document.createElement("li"));

								list_item.addClass("objective-container")
										 .attr("id", "objective_"+jsonData.updates.objective_id)
										 .attr("data-id", jsonData.updates.objective_id)
										 .attr("data-code", jsonData.updates.objective_code)
										 .attr("data-name", jsonData.updates.objective_name)
										 .attr("data-desc", jsonData.updates.objective_description)
										 .append(jQuery(document.createElement("div")).attr("id", "objective_title_"+jsonData.updates.objective_id).attr("data-title", jsonData.updates.objective_name).addClass("objective-title").html(jsonData.updates.objective_name))
										 .append(jQuery(document.createElement("div")).addClass("objective-controls"))
										 .append(jQuery(document.createElement("div")).attr("id", "description_"+jsonData.updates.objective_id).addClass("objective-description").html(jsonData.updates.objective_description))
										 .append(jQuery(document.createElement("div")).attr("id", "children_"+jsonData.updates.objective_id).addClass("objective-children"));
								list_item.children(".objective-controls").append(jQuery(document.createElement("i")).addClass("objective-edit-control").attr("data-id", jsonData.updates.objective_id))
										 .append(jQuery(document.createElement("i")).addClass("objective-add-control").attr("data-id", jsonData.updates.objective_id))
										 .append(jQuery(document.createElement("i")).addClass("objective-delete-control").attr("data-id", jsonData.updates.objective_id));
													
								if (jQuery("#children_" + parent_id).children().length > 0) {
									/*
									 * add it to the dom
									 */
									if (jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent).children().length != order) {
										jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent + " li").eq(order).before(list_item)
									} else {
										jQuery("#children_" + objective_parent + " #objective_list_" + objective_parent).append(list_item);
									}
								}
							}

						}
					});
					jQuery(this).dialog( "close" );
				}
			},
			close: function(event, ui){
				modal_container.dialog("destroy");
			}
		});
		return false;
	});
	
	jQuery(".objective-delete-control").live("click", function(){
		var objective_id = jQuery(this).attr("data-id");
		var modal_container = jQuery(document.createElement("div"));
		var url = SITE_URL + "/admin/settings/manage/objectives?org=1&section=delete&mode=ajax&objective_id="+objective_id;
		modal_container.load(url);
		
		modal_container.dialog({
			title: "Delete Objective",
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
				Delete : function() {
					console.log(modal_container.children("form").serialize());
					jQuery.ajax({
						url: modal_container.children("form").attr("action"),
						type: "POST",
						async: false,
						data: modal_container.children("form").serialize(),
						success: function(data) {
							var jsonData = JSON.parse(data);
							if (jsonData.status != "error") {
								jQuery("#objective_"+objective_id).remove();
								modal_container.dialog( "close" );
							} else {
								if (jQuery(".ui-dialog .display-generic .check-err").length <= 0) {
									jQuery(".ui-dialog .display-generic").append("<p class=\"check-err\"><strong>Please note:</strong> The checkbox below must be checked off to delete this objective and its children.</p>");
								}
							}
						}
					});
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
			d_control = jQuery(document.createElement('i'))
						.attr('class','objective-delete-control')
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
						.append(a_control)
						.append(d_control);
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
