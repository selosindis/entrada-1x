var events_sortable;
var initial_total_duration;

function cleanupList() {
	ol = $('duration_container');
	if(ol.immediateDescendants().length > 0) {
		ol.show();
		if ($('duration_notice')) {
			$('duration_notice').hide();
		}
	} else {
		ol.hide();
		if ($('duration_notice')) {
			$('duration_notice').show();
		}
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
	str = 'Total time: '+total+' % (should not exceed 100%)';
	if(EVENT_LIST_STATIC_TOTAL_DURATION && total != initial_total_duration) {
		str += ', original total time: '+initial_total_duration+" %";
	}
	str += '.';
	$('total_duration').update(str);
	events_sortable = Sortable.create('duration_container', {
		onUpdate: writeOrder
	});
	writeOrder(null);
}

function writeOrder(container) {
	$('mtdlocation_duration_order').value = Sortable.sequence('duration_container').join(',');
}

Event.observe(window, 'load', function() {
	if(typeof EVENT_LIST_STATIC_TOTAL_DURATION == "undefined") {
		EVENT_LIST_STATIC_TOTAL_DURATION = false;
	}
	if(typeof INITIAL_EVENT_DURATION != "undefined") {
		initial_total_duration = INITIAL_EVENT_DURATION;
	}

	$('mtdlocation').observe('change', function(event){
                var percent_val = 0;
                if ($$("li[id^='mtdlocation_']").length == 0) {
                    percent_val = 100;
                }
                else {
                    percent_val = 0;
                }
		select = $('mtdlocation');
		option = select.options[select.selectedIndex];
		li = new Element('li', {id: 'mtdlocation_'+option.value, 'class': 'location_duration'});
		li.insert(option.text+"");
                Prototype.Browser.IE7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
                Prototype.Browser.IE8 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 8;
                if (!Prototype.Browser.IE7  && !Prototype.Browser.IE8) {
                    li.insert(new Element('a', {href: '#', onclick: '$(this).up().remove(); cleanupList(); return false;', 'class': 'remove'}).insert(new Element('img', {src: DELETE_IMAGE_URL})));
                }
		span = new Element('span', {'class': 'duration_segment_container', 'style': 'margin-left:5px;'});
		span.insert('Time:');
		name = 'duration_segment[]';
		span.insert(new Element('input', {'style': 'width:25px;', 'class': 'duration_segment', name: 'duration_segment[]', onchange: 'cleanupList();', 'value': percent_val}));
		span.insert(' %&nbsp&nbsp');
                
		li.insert(span);

                if (Prototype.Browser.IE7  || Prototype.Browser.IE8) {
                    li.insert(new Element('a', {href: '#', onclick: '$(this).up().remove(); cleanupList(); return false;', 'class': 'remove'}).insert(new Element('img', {src: DELETE_IMAGE_URL})));
                }
                $('duration_container').insert(li);
		cleanupList();
		select.selectedIndex = 0;

	});
	cleanupList();
});