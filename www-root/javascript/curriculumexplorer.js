function renderDOM(jsonResponse, link) {
	if (jsonResponse.child_objectives.length > 0) {
		var new_list = jQuery(document.createElement("ul"));
		for (var i=0; i < jsonResponse.child_objectives.length; i++) {
			var new_list_item = jQuery(document.createElement("li"));
			var count;
			
			if (jsonResponse.child_objectives[i].course_count || jsonResponse.child_objectives[i].event_count) {
				count = ((COURSE != "" ? 0 : parseInt(jsonResponse.child_objectives[i].course_count)) + parseInt(jsonResponse.child_objectives[i].event_count));
			} else {
				count = parseInt((jsonResponse.courses != null ? jsonResponse.courses.length : 0)) + parseInt((jsonResponse.events != null ? jsonResponse.events.length : 0));
			}
			 
			var color = "green";
			if (count < 5) {
				color = "red";
			} else if (count >= 5 && count < 10) {
				color = "yellow";
			}
			new_list_item.append(
				jQuery(document.createElement("a"))
					.addClass("objective-link")
					.attr("href", SITE_URL + "/curriculumexplorer?objective_parent="+jsonResponse.child_objectives[i].objective_parent + "&id=" + jsonResponse.child_objectives[i].objective_id + "&step=2")
					.attr("data-id", jsonResponse.child_objectives[i].objective_id)
					.html(jsonResponse.child_objectives[i].objective_name))
					.prepend("<span class=\"" + color + "\">" + ((COURSE != "" ? 0 : parseInt(jsonResponse.child_objectives[i].course_count)) + parseInt(jsonResponse.child_objectives[i].event_count)) + "</span>");
			new_list.append(new_list_item);
		}
		jQuery("#objective-list").html(new_list);
	}

	var courses = false;
	var events = false;
	jQuery("#objective-details").html("");
	jQuery("#objective-details").append("<h1>"+link.html()+"</h1>");
	if (jsonResponse.courses != null) {
	if (jsonResponse.courses.length > 0 && COURSE == "") {
		jQuery("#objective-details").append(jQuery(document.createElement("h2")).html("Mapped Courses"));
		for (var i=0; i < jsonResponse.courses.length; i++) {
			var new_course = jQuery(document.createElement("div"));
			new_course.addClass("course-container").attr("data-id", jsonResponse.courses[i].course_id);
			new_course.append(
				jQuery(document.createElement("p")).append(
					jQuery(document.createElement("a"))
							.attr("href", SITE_URL+"/courses?id="+jsonResponse.courses[i].course_id)
							.html("<strong>"+jsonResponse.courses[i].course_code+":</strong> " + jsonResponse.courses[i].course_name)
				)
			);
			jQuery("#objective-details").append(new_course);
		}
		courses = true;
	}
	}

	if (jsonResponse.events != null) {
		jQuery("#objective-details").append(jQuery(document.createElement("h2")).html("Mapped Events"));
		for (var v in jsonResponse.events) {
			var course_container = jQuery(document.createElement("div")).addClass("course-container");
			var new_course = jQuery(document.createElement("h3"));
			new_course.html(v);
			course_container.append(new_course);
			for (var i=0; i < jsonResponse.events[v].length; i++) {
				var event_date = new Date(jsonResponse.events[v][i].event_start * 1000);
				var new_event = jQuery(document.createElement("div"));
				new_event.addClass("event-container").attr("data-id", jsonResponse.events[v][i].event_id);
				new_event.append(
					jQuery(document.createElement("p")).append(
						jQuery(document.createElement("a"))
								.attr("href", SITE_URL+"/events?rid="+jsonResponse.events[v][i].event_id)
								.html(jsonResponse.events[v][i].event_title)
					).append("<br /><span class=\"content-small\">Event on " + event_date.toDateString() + "</span>")
				);
				course_container.append(new_event);
				delete(event_date);
			}
			jQuery("#objective-details").append(course_container);
		}
		events = true;
	}

	if (courses == false && events == false) {
		jQuery("#objective-details").append("<div class=\"display-generic\">Please select an objective from the menu on the left.</div>");
	}

	if (typeof jsonResponse.breadcrumb != "undefined") {
		jQuery("#objective-breadcrumb").html(jsonResponse.breadcrumb);	
	}
}

jQuery(function(){
	jQuery(".objective-link").live("click", function(){
		jQuery("#objective-list .objective-link.active").removeClass("active");
		jQuery(this).addClass("active");
		var link = jQuery(this).clone();
		link.children("span").remove();
		jQuery("#objective-details").html("<h1>"+link.html()+"</h1>" + "<div class=\"loading display-generic\">Loading...<br /><img src=\""+SITE_URL+"/images/loading.gif\" /></div>");
		jQuery.ajax({
			url: SITE_URL + "/curriculumexplorer?mode=ajax&objective_parent=" + jQuery(this).attr("data-id") + "&year=" + YEAR + "&course_id=" + COURSE + "&count=" + COUNT,
			success: function(data) {
				var jsonResponse = JSON.parse(data);
				renderDOM(jsonResponse, link);
			}
		})
		location.hash = "id-" + jQuery(this).attr("data-id");
		return false;
	});
});