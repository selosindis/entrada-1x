
var AutoCompleteList = function() {
	var sortables = new Array();
	
	return function (options) {
		var type = options.type;
		var url = options.url;
		var remove_image = options.remove_image;
		
		var self = this;
		
		
		function updateOrder() {
			$('associated_'+type).value = Sortable.sequence(type+'_list');
		}
		
		this.addItem = function () {
			if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
				var li = new Element('li', {'class':'community', 'id':type+'_'+$(type+'_id').value, 'style':'cursor: move;'}).update($(type+'_name').value);
				var img = new Element('img', {'class' : 'list-cancel-image', 'src':remove_image} );
				var id = $(type+'_id').value;
				$(type+'_name').value = '';
				li.insert({bottom:img});
				$(type+'_id').value	= '';
				$(type+'_list').appendChild(li);
				img.observe('click', self.removeItem(id));
				sortables[type] = Sortable.destroy($(type+'_list'));
				Sortable.create(type+'_list', {onUpdate : updateOrder});
				updateOrder(type);
			} else if ($(type+'_'+$(type+'_id').value) != null) {
				alert('Important: Each user may only be added once.');
				$(type+'_id').value = '';
				$(type+'_name').value = '';
				return false;
			} else if ($(type+'_name').value != '' && $(type+'_name').value != null) {
				alert('Important: When you see the correct name pop-up in the list as you type, make sure you select the name with your mouse, do not press the Enter button.');
				return false;
			} else {
				return false;
			}
		}
		
		this.addItemNoError = function () {
			if (($(type+'_id') != null) && ($(type+'_id').value != '') && ($(type+'_'+$(type+'_id').value) == null)) {
				self.addItem();
			}
		}
		
		this.removeItem = function(id) {
			return function() {
				if ($(type+'_'+id)) {
					$(type+'_'+id).remove();
					Sortable.destroy($(type+'_list'));
					Sortable.create(type+'_list', {onUpdate : updateOrder});
					updateOrder();
				}
			}
		}

		//-----------------//

		function checkInput() {
			if (($(type+'_name') != null) && ($(type+'_ref') != null) && ($(type+'_id') != null)) {
				if ($(type+'_name').value != $(type+'_ref').value) {
					$(type+'_id').value = '';
				}
			}

			return true;
		}

		function selectInput(id) {
			if ((id != null) && ($(type+'_id') != null)) {
				$(type+'_id').value = id;
			}
		}

		function copyInput() {
			if (($(type+'_name') != null) && ($(type+'_ref') != null)) {
				$(type+'_ref').value = $(type+'_name').value;
			}

			return true;
		}
		
		new Ajax.Autocompleter(	type + '_name', 
				type + '_name_auto_complete', 
				url, 
				{	frequency: 0.2, 
					minChars: 2, 
					afterUpdateElement: function (text, li) {
						selectInput(li.id); 
						copyInput();
					}
				});

		$(type + '_name').observe('keyup', checkInput);
		$(type + '_name').observe('blur', self.addItemNoError);
		$('add_associated_' + type).observe('click', self.addItem);
		$(type + '_name').observe('keypress', function(event){
			if(event.keyCode == Event.KEY_RETURN) {
				self.addItem();
				Event.stop(event);
			}
		});
		
		Sortable.create(type + '_list', {onUpdate : updateOrder});
		$('associated_'+type).value = Sortable.sequence(type+'_list');
		
	};
}();