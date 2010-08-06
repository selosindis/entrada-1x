	function ActiveDataEntryProcessor(options) {
		var url = options.url;
		var data_destination = options.data_destination;
		var new_form = options.new_form;
		var remove_forms_selector = options.remove_forms_selector;
		var new_button = options.new_button;
		var hide_button = options.hide_button;
		var messages = options.messages;
		var section = options.section;
		
		function new_entry() {
			new Ajax.Updater(data_destination, url,
				{
					method:'post',
					parameters: new_form.serialize(),
					evalScripts:true,
					onComplete: function () {
						if (messages) {
							messages.update(data_destination.down('.status_messages'));
						}
						new_form.reset()
						add_entry_remove_listeners();
						document.fire(section+':onAfterUpdate');
					}
				});
			document.fire(section + ':onBeforeUpdate');
			hide_new_entry_handle();
		}

		function remove_entry(form) {
			if (confirm("Are you sure you want to delete this item?")) {
				remove_entry_remove_listeners();
				new Ajax.Updater(data_destination, url,
				{
					method:'post',
					parameters: form.serialize(),
					evalScripts:true,
					onComplete: function () {
						if (messages) {
							messages.update(data_destination.down('.status_messages'));
						}
						add_entry_remove_listeners();
						document.fire(section+':onAfterUpdate');
					}
				});
			}
			document.fire(section + ':onBeforeUpdate');
		}

		function submit_entry_ajax(event) {
			new_entry();
			event.stop();
		}
		
		function show_new_entry_handle(event) {
			new_form.show();
			new_button.hide();
			if (event) event.stop();
			return false;
		}
		
		function hide_new_entry_handle(event) {
			new_form.hide();
			new_button.show();
			if (event) event.stop();
			return false;
		}

		function entry_remove_ajax(event) {
			event.stop();
			var form = Event.findElement(event, 'form');
			remove_entry(form);
			
		}

		function add_entry_remove_listeners() {
			$$(remove_forms_selector).each(function (element) { element.observe('submit',entry_remove_ajax) });
		}
		
		function remove_entry_remove_listeners() {
			$$(remove_forms_selector).each(function (element) { element.stopObserving('submit',entry_remove_ajax) });
		}
		
		new_button.observe('click', show_new_entry_handle);
		new_button.observe('keydown', show_new_entry_handle);
		hide_button.observe('click', hide_new_entry_handle);
		hide_button.observe('keydown', hide_new_entry_handle);
		new_form.observe('submit',submit_entry_ajax);
		document.observe(section+':onAfterUpdate', onAfterUpdate);
		document.observe(section+':onBeforeUpdate', onBeforeUpdate);

		function onAfterUpdate() {
			if(options.onAfterUpdate) {
				options.onAfterUpdate();
			}
			init();
		}
		
		function onBeforeUpdate() {
			if(options.onBeforeUpdate) {
				options.onBeforeUpdate();
			}
			remove_entry_remove_listeners();
		}
		
		function init() {
			add_entry_remove_listeners();
		}
		
		if (document.loaded) {
			init();
		} else {
			document.observe('dom:loaded', function () { 
				init();
			});
		}
	}