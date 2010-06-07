<script type="text/javascript">
function addObjective(element, primary) {
	if (primary == null) {
		primary = true;
	}
	var importance_string = (primary ? 'primary' : 'secondary');
	var ids = new Array();
	ids["primary"] = "";
	ids["secondary"] = "";
	ids[importance_string] = element.value;

	var alreadyAdded = false;
	$$('input.primary_objectives').each(
		function (e) {
			if (!ids["primary"]) {
				ids["primary"] = e.value;
			} else {
				ids["primary"] += ','+e.value;
			}
			if (e.value == element.value) {
				alreadyAdded = true;
				primary = true;
				importance_string = 'primary';
			}
		}
	);
	$$('input.secondary_objectives').each(
		function (e) {
			if (!ids["secondary"]) {
				ids["secondary"] = e.value;
			} else {
				ids["secondary"] += ','+e.value;
			}
			if (e.value == element.value) {
				alreadyAdded = true;
				primary = false;
				importance_string = 'secondary';
			}
		}
	);
	if (!alreadyAdded) {
		var attrs = {
	        type		: 'hidden',
	        className	: importance_string+'_objectives',
	        id			: importance_string+'_objective_'+element.value,
	        value		: element.value,
	        name		: importance_string+'_objectives[]'
		};
	
	    var newInput = new Element('input', attrs);
		$('objectives_head').insert({after: newInput});
	}
	if (!alreadyAdded) {
		new Ajax.Updater('objectives_list', '<?php echo ENTRADA_URL; ?>/api/objectives.api.php', 
			{
				method:	'post',
				parameters: 'course_ids=<?php echo $course_ids_string ?>&primary_ids='+ids["primary"]+"&secondary_ids="+ids["secondary"]
	    	}
	    );
	}
	var tr = $(element.parentNode.parentNode);
	tr.addClassName(importance_string);
	if (tr.hasClassName('category')) {
		tr.addClassName(importance_string);
		$$('tr.parent'+element.value).each( 
			function (e) {
				e.addClassName('disabled');
			}
		);
		$$('tr.parent'+element.value+" input").each( 
			function (e) {
				e.disable();
			}
		);
	} else {
		tr.previousSiblings().each( 
			function (e) {
				if (e.hasClassName('category')) {
					e.addClassName('disabled');
					$$('#'+e.id+' input').each( function (e) { e.disable(); } );
					throw $break;
				}
			}
		);
	}
}

function removeObjective(element, primary) {
	if (primary == null) {
		primary = true;
	}
	var tr = $(element.parentNode.parentNode);
	var importance_string = (primary ? 'primary' : 'secondary');
	if (tr.hasClassName('primary')) {
		importance_string = 'primary';
		primary = true;
	} else if (tr.hasClassName('secondary')) {
		importance_string = 'secondary';
		primary = false;
	}
	if ($(importance_string + '_objective_'+element.value)) {
		$(importance_string + '_objective_'+element.value).remove();
	} else if ($((importance_string == "primary" ? "secondary" : "primary") + '_objective_'+element.value)) {
		$((importance_string == "primary" ? "secondary" : "primary") + '_objective_'+element.value).remove();
	}
	var ids = new Array();
	ids["primary"] = "";
	ids["secondary"] = "";
	$$('input.primary_objectives').each(
		function (e) {
			if (ids["primary"] == null) {
				ids["primary"] = e.value;
			} else {
				ids["primary"] += ','+e.value;
			}
		}
	);
	$$('input.secondary_objectives').each(
		function (e) {
			if (ids["secondary"] == null) {
				ids["secondary"] = e.value;
			} else {
				ids["secondary"] += ','+e.value;
			}
		}
	);
	
	new Ajax.Updater('objectives_list', '<?php echo ENTRADA_URL; ?>/api/objectives.api.php', 
		{
			method:	'post',
			parameters: 'course_ids=<?php echo $course_ids_string ?>&primary_ids='+ids["primary"]+"&secondary_ids="+ids["secondary"]
    	}
    );
	tr.removeClassName(importance_string);
	if (tr.hasClassName('category')) {
		$$('tr.parent'+element.value).each( 
			function (e) {
				e.removeClassName('disabled');
			}
		);
		$$('tr.parent'+element.value+" input").each( 
			function (e) {
				e.enable();
			}
		)
	} else {
		tr.previousSiblings().each( 
			function (e) {
				if (e.hasClassName('category')) {
					e.removeClassName('disabled');
					$$('#'+e.id+' input').each( function (el) { 
						var still_checked = false;
						$$('tr.parent'+el.value).each( 
							function (ele) {
								if (ele.hasClassName(importance_string)) {
									still_checked = true;
								}
							}
						);
						if (!still_checked) {
							el.enable(); 
						}
					} );
					throw $break;
				}
			}
		);
	}
}

function moveObjective(objective_id, move_location) {
	$((move_location == 'primary' ? 'secondary' : 'primary')+'_objective_'+objective_id).className 	= move_location+'_objectives'; 
	$((move_location == 'primary' ? 'secondary' : 'primary')+'_objective_'+objective_id).name		= move_location+'_objectives[]';
	$((move_location == 'primary' ? 'secondary' : 'primary')+'_objective_'+objective_id).id			= move_location+'_objective_'+objective_id;
	$('row_'+objective_id).removeClassName((move_location == 'primary' ? 'secondary' : 'primary'));
	$('row_'+objective_id).addClassName(move_location);
	if ($('objective_'+objective_id+'_list')) {
		$$('#objective_'+objective_id+'_list li').each(
			function (e) {
				e.addClassName(move_location);
				e.removeClassName((move_location == 'primary' ? 'secondary' : 'primary'));
			}
		);
	} else {
		$('objective_'+objective_id+'_row').addClassName(move_location);
		$('objective_'+objective_id+'_row').removeClassName((move_location == 'primary' ? 'secondary' : 'primary'));
	}
}
</script>