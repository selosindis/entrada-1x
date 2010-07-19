var ENTRADA_URL;

jQuery(document).ready(function($) {
	var flexiopts = {
		minwidth: 50,
		height: 'auto',
		disableSelect: true
	};
	$('table.gradebook.single').flexigrid($.extend(flexiopts, {width: 450}));
	$('table.gradebook').flexigrid(flexiopts);

	$('table.gradebook .grade').editable(ENTRADA_URL+'/api/gradebook.api.php', {
		placeholder: '-',
		indicator: '<img width="16" height="16" src="'+ENTRADA_URL+'/images/loading.gif">',
		onblur: 'submit',
		width: 40,
		cssclass: 'editing',
		onsubmit: function(settings, original) {
		},
		submitdata: function(value, settings) {
			return {
				grade_id: $(this).attr('data-grade-id'),
				assessment_id: $(this).attr('data-assessment-id'),
				proxy_id: $(this).attr('data-proxy-id')
			};
		},
		callback: function(value, settings) {
			// If grade came back deleted remove the grade ID data
			if(value != "-") {
				var values = value.split("|");
				var grade_id = values[0];
				value = values[1];
				$(this).html(value);				
			}
			
			if(value == "-") {
				$(this).attr('data-grade-id', '');
				$(this).next('.gradesuffix').hide();
			} else {
				$(this).attr('data-grade-id', grade_id);
				$(this).next('.gradesuffix').show();
			}
			
		}
	}).keyup(function(e){
		switch(e.which) {
			case 38:
			case 40: 
			// Go up or down a line
			$('input', this).trigger('blur');
			var pos = $(this).parent().parent().prevAll().length;
			var row = $(this).parent().parent().parent();
			if(e.which == 38) { //going up!
				var dest = row.prev();
			} else {
				var dest = row.next();
			}

			if(dest) {
				var next = dest.children()[pos];
				if(next) {
					next = $(next).find('.grade');
				}
			}

			$(next).trigger('click');
			break;
			default:
		}
	});
});