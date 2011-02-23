function addRow(user_id, event) {
	Event.stop(event);
	var category_id = $('cat_id').getAttribute("id");
	new Ajax.Request(api_url,
		{
			method:'post',
			parameters: { proxy_id: user_id, type: category_id, request: 'new_value' },
			evalScripts:true,
			onSuccess: function (response) {
				var head = $('user_head_' + user_id);
				var xml = response.responseXML;
				var value_id = xml.firstChild.getAttribute("id");
				if (value_id) {
					var value_parts = /value_edit_(\d+)/.exec(value_id);
					if (value_parts && value_parts[1]) {
						head.insert({after: response.responseText});
						document.fire('MetaData:onAfterRowInsert', value_parts[1]);
					}
				}
			},
			onComplete: function(response) {
				if (response.status != 200) {
					display_error(response.responseText);
				}
			}
		});
	document.fire('MetaData:onBeforeRowInsert', category_id);
}

function deleteRow(value_id) {
	var tr = $('value_edit_'+value_id);
	tr.setAttribute("class", "value_delete");
	var checkbox = $('delete_'+value_id);
	var opts = [ "enable", "disable" ];
	tr.select('input:not([type=checkbox]), select').invoke(opts[Number(checkbox.checked)]);
}

function mkEvtReq(regex, func) {
	return function(event) {
		var element = Event.findElement(event);
		var tr = element.up('tr');
		var id = tr.getAttribute('id');
		var res = regex.exec(id);
		if (res && res[1]) {
			var target_id = res[1];
			func(target_id, event);
		}
		return false;
	};
}

var addRowReq = mkEvtReq(/^user_head_(\d+)$/,addRow);
var deleteRowReq = mkEvtReq(/^value_edit_(\d+)$/, deleteRow);

function addDeleteListener(value_id) {
	var btn = $('delete_'+value_id);
	btn.observe('click', deleteRowReq);
}

function hideCals() {
	if (hide_cals) {
		hideCalendars();
	}
}

function addCalendarListener(value_id) {
	$$("#value_edit_"+value_id+ " input.date").each(function(e){
		e.observe('focus',function(e) {
			hide_cals = false;
			showCalendar('',this,this,null,this.getAttribute("id"),0,$(this).getHeight()+1,1);
		}.bind(e));
		e.observe('blur',function(e) {
			hide_cals = true;
			hideCals.delay(0.2);
		}.bind(e));
	});
}

function addCalendarListeners() {
	$$(".DataTable input.date").each(function(e){
		e.observe('focus',function(e) {
			hide_cals = false;
			showCalendar('',this,this,null,this.getAttribute("id"),0,$(this).getHeight()+1,1);
		}.bind(e));
		e.observe('blur',function(e) {
			hide_cals = true;
			hideCals.delay(0.2);
		}.bind(e));
	});
}

function addUserListeners() {
	$$('.DataTable .add_btn').invoke("observe", "click", addRowReq);
}

function addDeleteListeners() {
	$$('.DataTable .delete_btn').invoke("observe", "click", deleteRowReq);
}

function removeListeners() {
	$$('.DataTable .add_btn, .DataTable .delete_btn, #save_btn, .DataTable input.date').invoke("stopObserving");
}

function addSaveListener() {
	$('save_btn').observe("click", updateValues);
}

function updateValues(event) {
	Event.stop(event);
	new Ajax.Request(api_url,
			{
				method:'post',
				parameters: $('meta_data_form').serialize(true),
				evalScripts:true,
				onSuccess: function (response) {
					removeListeners();
					$('meta_data_form').update(response.responseText);
					table_init();
					
				},
				onComplete: function(response) {
					document.fire('MetaData:onAfterUpdate');
					if (response.status != 200) {
						display_error(response.responseText);
					}
				}
			});
	document.fire('MetaData:onBeforeUpdate');
}

function getTable(event) {
	Event.stop(event);
	params = $('table_selector').serialize(true);
	if (params.associated_cat_id) {
		params.request = 'get_table';
		new Ajax.Request(api_url,
				{
					method:'post',
					parameters: params,
					evalScripts:true,
					onSuccess: function (response) {
						removeListeners();
						$('meta_data_form').update(response.responseText);
						table_init();
					},
					onComplete: function(response) {
						document.fire('MetaData:onAfterUpdate');
						if (response.status != 200) {
							display_error(response.responseText);
						}
					}
				});
		document.fire('MetaData:onBeforeUpdate');
	}
}

function getCategories() {
	new Ajax.Request(api_url,
			{
				method:'post',
				parameters: $('table_selector').serialize(true),
				evalScripts:true,
				onSuccess: function (response) {
					$('assoc_cat_holder').update(response.responseText);
				},
				onComplete: function(response) {
					if (response.status != 200) {
						display_error(response.responseText);
					}
				}
			});
	document.fire('MetaData:onBeforeCatUpdate');
}

function page_init() {
	var errModal = new Control.Modal('errModal', {
		overlayOpacity:	0.75,
		closeOnClick:	'overlay',
		className:		'modal-description',
		fade:			true,
		fadeDuration:	0.30
	});
	display_error = ErrorHandler(errModal);
	
	var loadingModal = new Control.Modal('loadingModal', {
		overlayOpacity:	0.75,
		closeOnClick:	false,
		className:		'modal-description',
		fade:			true,
		fadeDuration:	0.30
	});
	
	document.observe('MetaData:onBeforeUpdate', function () {loadingModal.open();});
	document.observe('MetaData:onAfterUpdate', function () {loadingModal.close();});
	
	$('associated_group').observe('change', setRoleList);
	$('associated_group').observe('change', getCategories);
	$('associated_organisation_id').observe('change', getCategories);
	$('associated_role').observe('change', getCategories);
	setRoleList();
	getCategories();
	
	$('table_selector').observe('submit', getTable);
}

function table_init() {
	addUserListeners();
	addDeleteListeners();
	addCalendarListeners();
	document.observe('MetaData:onAfterRowInsert', function(event) {
		addDeleteListener(event.memo);
		addCalendarListener(event.memo);
	});
	addSaveListener();
}

function ErrorHandler(modal) {
	if (modal && modal.container) {
		var modal_close = modal.container.down(".modal-close");
		function close_modal(event) {
			modal.close();
		} 
		modal_close.observe("click", close_modal);

		return function (text) { 
			modal.container.down(".status").update(text);
			modal.open();
		};
	}
}

function setRoleList() {
	var group = $('associated_group').getValue();
	var roles = user_groups[group];
	
	if (roles) {
		var role_opts = "";
		roles.each(function (role) {
			role_opts += "<option value=\""+role.toLowerCase()+"\">"+role+"</option>";
		}); 
		
		$('associated_role').update(role_opts);
		
	} //else? broken? all groups have at least one role
}