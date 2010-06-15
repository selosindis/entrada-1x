	function ActiveApprovalProcessor(options) {
		var url = options.url;
		var data_destination = options.data_destination;
		var approve_forms_selector = options.approve_forms_selector;
		var unapprove_forms_selector = options.unapprove_forms_selector;
		var section = options.section;
		var messages = options.messages;
		
		

		function process_entry(form) {
			document.fire(section + ':onBeforeUpdate');
			new Ajax.Updater(data_destination, url,
				{
					method:'post',
					parameters: form.serialize(),
					evalScripts:true,
					onComplete: function () {
						if (messages) {
							messages.update(data_destination.down('.status_messages'));
						}
						add_entry_listeners();
					}
				});
			
		}

		function entry_process_ajax(event) {
			event.stop();
			var form = Event.findElement(event, 'form');
			process_entry(form);
			
		}

		function addListener (element) { element.observe('submit',entry_process_ajax) }
		function removeListener (element) { element.stopObserving('submit',entry_process_ajax) }
		
		var add_entry_listeners, remove_entry_listeners;
					
		if (unapprove_forms_selector) {
			if (approve_forms_selector && (approve_forms_selector != unapprove_forms_selector)) {
				add_entry_listeners = function() { $$(unapprove_forms_selector).each(addListener); $$(approve_forms_selector).each(addListener); }
				remove_entry_listeners =  function() { $$(unapprove_forms_selector).each(removeListener); $$(approve_forms_selector).each(removeListener); }
			} else {
				add_entry_listeners =  function() { $$(unapprove_forms_selector).each(addListener); }
				remove_entry_listeners = function() { $$(unapprove_forms_selector).each(removeListener); }
			}
		} else {
			add_entry_listeners =  function() { $$(approve_forms_selector).each(addListener); }
			remove_entry_listeners = function() { $$(approve_forms_selector).each(removeListener); }
		}
		function init() {
			add_entry_listeners();
		}

		if (document.loaded) {
			init();
		} else {
			document.observe('dom:loaded', function () { 
				init();
			});
		}	
		
		function onBeforeUpdate() {
			if(options.onBeforeUpdate) {
				options.onBeforeUpdate();
			}
			document.stopObserving(section+':onBeforeUpdate', onBeforeUpdate);
			remove_entry_listeners();
		}

		document.observe(section+':onBeforeUpdate', onBeforeUpdate);
	}