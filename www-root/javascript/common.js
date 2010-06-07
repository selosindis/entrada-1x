function toggle_list(element_id) {
	if($(element_id).style.display == 'none') {
		new Effect.BlindDown($(element_id), { duration: 0.3 });

		$(element_id+'_state_btn').addClassName('button-red');
		$(element_id+'_state_btn').value = 'Hide List';
		
		$(element_id+'_add_btn').appear({ duration: 0.3 });
	} else {
		new Effect.BlindUp($(element_id), { duration: 0.3 });
		
		$(element_id+'_state_btn').removeClassName('button-red');
		$(element_id+'_state_btn').value = ('Show List');

		$(element_id+'_add_btn').fade({ duration: 0.3 });
	}
}

function toggle_visibility_checkbox(obj, element_id, effect) {
	if((!effect) || (effect != 'blind')) {
		effect = 'fade';
	}
	
	if($(element_id) != null) {
		if(obj.checked == true) {
			switch(effect) {
				case 'fade' :
					Effect.Appear(element_id);
				break;
				case 'blind' :
					Effect.BlindDown(element_id);
				break;
				default :
					$(element_id).style.display	= '';
				break;
			}
		} else {
			switch(effect) {
				case 'fade' :
					Effect.Fade(element_id);
				break;
				case 'blind' :
					Effect.BlindUp(element_id);
				break;
				default :
					$(element_id).style.display	= 'none';
				break;
			}
		}
	}
	return;
}

function toggle_visibility(element_id, effect) {
	if($(element_id) != null) {
		if($(element_id).style.display == 'none') {
			switch(effect) {
				case 'fade' :
					Effect.Appear(element_id);
				break;
				case 'blind' :
					Effect.BlindDown(element_id);
				break;
				default :
					$(element_id).style.display	= '';
				break;
			}
		} else {
			switch(effect) {
				case 'fade' :
					Effect.Fade(element_id);
				break;
				case 'blind' :
					Effect.BlindUp(element_id);
				break;
				default :
					$(element_id).style.display	= 'none';
				break;
			}
		}
	}
	return;
}

function updateTime(type) {
	var hour	= $F(type+'_hour');
	var minute	= $F(type+'_min');
	var suffix	= '';

	// If it's not past 12 don't bother.
	if(hour >= 12) {
		hour		= hour % 12;
		suffix	= 'PM';
	} else {
		suffix	= 'AM';
	}

	// Crude adjustments for silly 12 hour format.
	if(hour == '0') {
		hour = '12';
	}
	// Crude adjustments for the zeros.
	if(minute == '0') {
		minute = '00';
	}
	
	$(type+'_display').innerHTML = hour+':'+minute+' '+suffix;

	return;
}

function dateLock(field) {
	if($(field) && $(field).checked == true) {
		$(field+'_text').className	= 'form-required';
		$(field+'_date').disabled	= false;
		if($(field+'_hour') != null) {
			$(field+'_hour').disabled = false;
		}
		if($(field+'_min') != null) {
			$(field+'_min').disabled = false;
		}
	} else {
		$(field+'_text').className	= 'form-nrequired';
		$(field+'_date').disabled	= true;
		if($(field+'_hour') != null) {
			$(field+'_hour').disabled = true;
		}
		if($(field+'_min') != null) {
			$(field+'_min').disabled = true;
		}
	}
	return;
}

function upload() {
	$('addbutton').disabled		= true;
	$('addbutton').style.color	= '#666666';
	$('status').innerHTML		= 'Please wait. Uploading data to server ...';
	
	document.forms[0].submit();
}

function customConfig(config) {
	config.toolbar = [
		[ "bold", "italic", "underline", "separator",
		  "orderedlist", "unorderedlist", "outdent", "indent", "separator",
		  "htmlmode", "popupeditor"
		]
	];
	config.pageStyle	= 'body { font-family: Verdana, Arial, sans-serif; font-size: 12px; margin: 5px }';
	config.statusBar	= false;
}

function getSelectedButton(buttonGroup) {
	for (var i = 0; i < buttonGroup.length; i++) {
		if (buttonGroup[i].checked) {
			return i;
		}
	}
	return -1; //no button selected
}

function sendFeedback(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;

		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);

		feedbackWindow = window.open(url, 'feedbackWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		feedbackWindow.blur();
		window.focus();

		feedbackWindow.resizeTo(windowW, windowH);
		feedbackWindow.moveTo(windowX, windowY);

		feedbackWindow.focus();
	}
	return;
}

function sendClerkship(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;
	
		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);
	
		clerkshipWindow = window.open(url, 'clerkshipWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		clerkshipWindow.blur();
		window.focus();
	
		clerkshipWindow.resizeTo(windowW, windowH);
		clerkshipWindow.moveTo(windowX, windowY);
	
		clerkshipWindow.focus();
	}
	return;
}

function sendAccommodation(url) {
	if(url) {
		var windowW = 485;
		var windowH = 585;
	
		var windowX = (screen.width / 2) - (windowW / 2);
		var windowY = (screen.height / 2) - (windowH / 2);
	
		accommodationWindow = window.open(url, 'accommodationWindow', 'width='+windowW+', height='+windowH+', scrollbars=yes');
		accommodationWindow.blur();
		window.focus();
	
		accommodationWindow.resizeTo(windowW, windowH);
		accommodationWindow.moveTo(windowX, windowY);
	
		accommodationWindow.focus();
	}
	return;
}

function closeWindow() {
	window.close();

	if (window.opener && !window.opener.closed) {
		window.opener.focus();
	}
}

function fieldCopy(copy_from, copy_to, copy_only_empty) {
	if((!copy_only_empty) || (copy_only_empty == null)) {
		copy_only_empty = 0;
	} else {
		copy_only_empty = 1;
	}

	if(((copy_only_empty) && (document.getElementById(copy_from) != null)) || (!copy_only_empty)) {
		if((!copy_only_empty) || ((copy_only_empty) && (document.getElementById(copy_to).value != ""))) {
		} else {
			document.getElementById(copy_to).value = document.getElementById(copy_from).value;
		}
	}

	return true;
}

function noPublic(obj) {
	obj.checked = false;
	alert('Non-Authenticated / Public Users cannot access this function at this time.');

	return;
}

function uploadPhoto() {
	if($('display-upload-button')) {
		if($('display-upload-status')) {
			if(($('photo_file')) && ($('photo_file').value != '')) {
				$('display-upload-button').innerHTML = $('display-upload-status').innerHTML;
			}
		}
	}

	if($('upload-photo-form')) {
		$('upload-photo-form').submit();
	}

	return;
}

function photoShow(url, width, height) {
	img = new Image(width, height);
	img.src = url;
	var win = new UI.Window(
	{
		shadow:	true,
		shadowTheme: "drop_shadow",
		theme: "alphacube",
		title: "User Photo",
		width: img.width + 4,
		height: img.height + 38,
		resizable: false
	}).center().setContent("<img src=\'"+url+"\' />").show();
}
function setMaxLength() {
	var x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'content-small';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = 'Character Count: <span>0</span>/'+x[i].getAttribute('maxlength');
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];

			x[i].onkeyup = x[i].onchange = checkMaxLength;
			x[i].onkeyup();
		}
	}
}

function checkMaxLength() {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	if (currentLength > maxLength)
		this.relatedElement.className = 'content-red';
	else
		this.relatedElement.className = 'content-small';
	this.relatedElement.firstChild.nodeValue = currentLength;
	// not innerHTML
}

var checkflag = 'false';
function selection(field) {
	if(checkflag == 'false') {
		if(!field.length) {
			field.checked = true;
		} else {
			for (i = 0; i < field.length; i++) {
				field[i].checked = true;
			}
		}
		checkflag = 'true';
		return;
	} else {
		if(!field.length) {
			field.checked = false;
		} else {
			for (i = 0; i < field.length; i++) {
				field[i].checked = false;
			}
		}
		checkflag = 'false';
		return;
	}
}

var ExpandableTextarea = Class.create({
	initialize: function(el) {
		this.textbox = { element: el, defaultheight: el.getHeight() }
		this.textbox.element.update = this.setTextboxHeight;
		this.createHiddenElement();
		this.setTextboxHeight(false);
		this.animate = (typeof Scriptaculous == 'undefined') ? false : true;
		this.textbox.element.setStyle({'overflow': 'hidden'});
		
		Event.observe(this.textbox.element, 'keyup', this.handleKeyUp.bind(this));
		Event.observe(this.textbox.element, 'focus', this.setTextboxHeight.bind(this));
	},

	createHiddenElement: function() {
		this.hiddenelement = new Element('div').show();

		// How do I get rid of this mess?
		this.hiddenelement.setStyle({
			'paddingTop': this.textbox.element.getStyle('paddingTop'),
			'paddingRight': this.textbox.element.getStyle('paddingRight'),
			'paddingBottom': this.textbox.element.getStyle('paddingBottom'),
			'paddingLeft': this.textbox.element.getStyle('paddingLeft'),
			'fontSize': this.textbox.element.getStyle('font-size'),
			'fontFamily': this.textbox.element.getStyle('font-family'),
			'width': this.textbox.element.getStyle('width'),
			'display': 'block',
			'visibility': 'hidden',
			'position': 'absolute',
			'top': '0',
			'left': '0'
		});

		this.textbox.element.parentNode.appendChild(this.hiddenelement);
	},

	handleKeyUp: function() {
		this.setTextboxHeight(this.animate);
	},

	setTextboxHeight: function(animate) {
		currenttextheight = this.hiddenelement.update(this.textbox.element.value.replace(/\n/g, '\n').replace(/<|>/g, ' ').replace(/\n/g, '<br />').replace(/&/g,"&amp;").replace(/  /g,' &nbsp;')).getHeight();
		goalheight = ((currenttextheight>this.textbox.defaultheight)?currenttextheight+20:this.textbox.defaultheight);

		if(animate)
			this.textbox.element.morph({ height: goalheight + 'px'}, { duration: 0.2 });
		else
			this.textbox.element.setStyle({ height: goalheight + 'px' });

	}
});

var CollapseHeadings = Class.create({
	initialize: function(el) {
		this.el = $(el);
		this.child = this.el.title.split(' ').join('-').toLowerCase();
		if (($(this.child)) && (this.el.hasClassName('nocollapse') == false)) {
			this.el.addClassName('collapsable');

			if (this.el.hasClassName('collapsed')) {
				$(this.child).hide();
			} else {
				this.el.addClassName('expanded');
			}

			Event.observe(this.el, 'click', this.toggler.bind(this));
		}
	},
	
	toggler: function() {
		if ($(this.child).visible()) {
			this.el.removeClassName('expanded');
			this.el.addClassName('collapsed');

			Effect.BlindUp(this.child, { duration: 0.3 })
		} else {
			this.el.removeClassName('collapsed');
			this.el.addClassName('expanded');

			Effect.BlindDown(this.child, { duration: 0.3 })
		}
	}
});

Event.observe(window, 'load', function() {
	$$('textarea.expandable').each(function(el) {
		new ExpandableTextarea(el);
	});
	
	$$('h2').each(function (el) {
		new CollapseHeadings(el);
	});
});

// Used on the Adding / Editing Calendar Events page.
function checkForNewRegion() {
	if(document.getElementById('region_id').options[document.getElementById('region_id').selectedIndex].value == 'new') {
		document.getElementById('new_region_layer').style.display = '';
		document.getElementById('new_region').focus();
	} else {
		document.getElementById('new_region_layer').style.display = 'none';
		document.getElementById('region_id').focus();
	}
}

function setMaxLength() {
	var x = document.getElementsByTagName('textarea');
	var counter = document.createElement('div');
	counter.className = 'content-small';
	for (var i=0;i<x.length;i++) {
		if (x[i].getAttribute('maxlength')) {
			var counterClone = counter.cloneNode(true);
			counterClone.relatedElement = x[i];
			counterClone.innerHTML = 'Character Count: <span>0</span>/'+x[i].getAttribute('maxlength');
			x[i].parentNode.insertBefore(counterClone,x[i].nextSibling);
			x[i].relatedElement = counterClone.getElementsByTagName('span')[0];

			x[i].onkeyup = x[i].onchange = checkMaxLength;
			x[i].onkeyup();
		}
	}
}

function checkMaxLength() {
	var maxLength = this.getAttribute('maxlength');
	var currentLength = this.value.length;
	if (currentLength > maxLength)
		this.relatedElement.className = 'content-red';
	else
		this.relatedElement.className = 'content-small';
	this.relatedElement.firstChild.nodeValue = currentLength;
	// not innerHTML
}