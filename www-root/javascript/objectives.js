var EDITABLE = true;
var loaded = [];
jQuery(document).ready(function(){
	jQuery('.objective-desc-control').live('click',function(){
		var id = jQuery(this).attr('data-id');
		if(jQuery('#description_'+id).is(':visible')){
			jQuery('#description_'+id).slideUp();
		}else{
			jQuery('#description_'+id).slideDown();
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
		var id = jQuery(this).parent().attr('data-id');
		//GET from api
		var children = [];
		if (loaded[id]) {
			children = loaded[id]
		} else {
			children = jQuery.get('path/to/api',{'objective_id':id});
			loaded[id] = children;
		}
		
		
		var container,title,title_text,controls,d_control,e_control,a_control,description,child_container;

		for(i = 0;i<children.length;i++){
			//Javascript to create DOM elements from JSON response
			container = jQuery(document.createElement('li'))
						.attr('class','objective-container')
						.attr('id','objective_'+children[i].objective_id)
						.attr('data-id',children[i].objective_id)
						.attr('data-code',children[i].objective_code)
						.attr('data-name',children[i].objective_name)
						.attr('data-description',children[i].objective_description);
			if(children[i].objective_code){
				title_text = children[i].objective_code+': '+children[i].objective_name
			}else{
				title_text = children[i].objective_name;
			}
			title = 	jQuery(document.createElement('div'))
						.attr('class','objective-title')
						.attr('data-id',children[i].objective_id)
						.html(title_text);
			if(EDITABLE){
				controls = 	jQuery(document.createElement('div'))
							.attr('class','objective-controls');
				//this will need to change at some point
				d_control = jQuery(document.createElement('i'))
							.attr('class','objective-desc-control')
							.attr('data-id',children[i].objective_id);
				e_control = jQuery(document.createElement('i'))
							.attr('class','objective-edit-control')
							.attr('data-id',children[i].objective_id);
				a_control = jQuery(document.createElement('i'))
							.attr('class','objective-add-control')
							.attr('data-id',children[i].objective_id);		
			}	
			description = 	jQuery(document.createElement('div'))
							.attr('class','objective-description')
							.attr('id','description_'+children[i].objective_id);
							.html(children[i].objective_description)
			child_container = 	jQuery(document.createElement('div'))
								.attr('class','objective-children')
								.attr('id','children_'+children[i].objective_id);
			child_list = 	jQuery(document.createElement('ul'))
								.attr('class','objective-list')
								.attr('id','objective_list_'+children[i].objective_id);												jQuery(child_container).append(child_list);			
			if(EDITABLE){
			jQuery(controls).append(d_control)
								.append(e_control)
								.append(a_control);
			}
			jQuery(container).append(title);
			if(EDITABLE){
				jQuery(container).append(controls);
			}
			jQuery(container).append(description)
								.append(child_container);
			jQuery('#objective_list_'+id).append(container);
		}
	});
	
	jQuery('#expand-all').click(function(){
		jQuery('.objective_title').each(function(){
			jQuery(this).trigger('click');
		});
	});
});