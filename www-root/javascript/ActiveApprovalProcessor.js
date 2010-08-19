	function ActiveApprovalProcessor(options) {
		try {
		var url = options.url;
		var data_destination = options.data_destination;
		var action_form_selector = options.action_form_selector;
		var section = options.section;
		var messages = options.messages;

		function process_entry(form, action) {
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
						document.fire(section+':onAfterUpdate');
					}
				});
			document.fire(section+':onBeforeUpdate');
		}

		function entry_process_ajax(event) {
			Event.stop(event);
			var form = Event.findElement(event, 'form');
			process_entry(form);
		}

		function addListener (element) { element.observe('submit',entry_process_ajax) }
		function removeListener (element) { element.stopObserving('submit',entry_process_ajax) }
		
		var add_entry_listeners, remove_entry_listeners;
					
		
		add_entry_listeners =  function() { $$(action_form_selector).each(addListener); }
		remove_entry_listeners = function() { $$(action_form_selector).each(removeListener); }
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
		catch (e) {console.log(e);}
	}