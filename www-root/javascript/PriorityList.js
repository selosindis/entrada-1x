	function PriorityList(options) {
		var section = options.section;
		var url = options.url;
		var data_destination = options.data_destination;
		var messages = options.messages;
		var handle = options.handle;
		var element = options.element;
		
		
		function resequence() {
			var params = Sortable.serialize(element,{name:section});
			params += ((params)?'&':'') + "action=resequence";
			new Ajax.Updater(data_destination, url,
				{
					method:'post',
					parameters: params, 
					evalScripts:true,
					onComplete: function () {
						if (messages) {
							messages.update(data_destination.down('.status_messages'));
						}
						document.fire(section+':onAfterUpdate');
					}
				});
			document.fire(section+':onBeforeUpdate');
		}
		
		
		function onUpdate(_onUpdate, element){
			if (_onUpdate) {
				_onUpdate();
			}
			resequence();
		}
		

		options.onUpdate = onUpdate.curry(options.onUpdate); 
		document.observe(section+':onAfterUpdate', onAfterUpdate);
		document.observe(section+':onBeforeUpdate', onBeforeUpdate);
		
		
		function onBeforeUpdate() {
			if(options.onBeforeUpdate) {
				options.onBeforeUpdate();
			}
			Sortable.destroy(element);
		}
		
		function onAfterUpdate() {
			if(options.onAfterUpdate) {
				options.onAfterUpdate();
			}
			init();
		}
		
		function init() {
			try {
				Sortable.create(element, options);
			} catch (e) {clog(e);}
		}
		
		if (document.loaded) {
			init();
		} else {
			document.observe('dom:loaded', function () { 
				init();
			});
		}
	}