var events_sortable;
var initial_total_duration;
function cleanupList() {
	ol = $('duration_container');
	if(ol.immediateDescendants().length > 0) {
		ol.show();
		$('duration_notice').hide();
	} else {
		ol.hide();
		$('duration_notice').show();
	}
	var some_too_low = false;
	total = $$('input.duration_segment').inject(0, function(acc, e) {
		seg = parseInt($F(e), 10);
		if(seg < 60) {
			some_too_low = true;
		}
		if (Object.isNumber(seg)) {
			acc += seg;
		}
		return acc;
	});
	// if(some_too_low) {
	// 	alert("Error. No event types can have durations of less than 60 minutes.");
	// }
	if(typeof initial_total_duration == "undefined") {
		initial_total_duration = total;
	}
	str = 'Total time: '+total+' minutes';
	if(EVENT_LIST_STATIC_TOTAL_DURATION && total != initial_total_duration) {
		str += ', original total time: '+initial_total_duration+" minutes";
	}
	str += '.';
	$('total_duration').update(str);
	events_sortable = Sortable.create('duration_container', {
		onUpdate: writeOrder
	});
	writeOrder(null);
}

function writeOrder(container) {
	$('eventtype_duration_order').value = Sortable.sequence('duration_container').join(',');
}

document.observe('click', function(e, el) {
  if (el = e.findElement('.remove')) {
    $(el).up().remove();
    cleanupList();
  }
});


document.observe("dom:loaded", function() {        
	if(typeof EVENT_LIST_STATIC_TOTAL_DURATION == "undefined") {
		EVENT_LIST_STATIC_TOTAL_DURATION = false;
	}
	if(typeof INITIAL_EVENT_DURATION != "undefined") {
		initial_total_duration = INITIAL_EVENT_DURATION;
	}
	
	$('eventtype_ids').observe('change', function(event){
		select = $('eventtype_ids');
		option = select.options[select.selectedIndex];
		li = new Element('li', {id: 'type_'+option.value, 'class': ''});
		li.insert(option.text+"  ");
		li.insert(new Element('a', {href: '#', 'class': 'remove'}).insert(new Element('img', {src: DELETE_IMAGE_URL})));
		span = new Element('span', {'class': 'duration_segment_container'});
		span.insert('Duration: ');
		name = 'duration_segment[]';
		span.insert(new Element('input', {'class': 'duration_segment', name: 'duration_segment[]', onchange: 'cleanupList();', 'value': 60}));
		span.insert(' minutes');
		li.insert(span);
		$('duration_container').insert(li);
		cleanupList();
		select.selectedIndex = 0;

	});
	cleanupList();
});