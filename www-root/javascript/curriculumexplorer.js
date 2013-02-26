jQuery(function(){
	jQuery(".objective-link").live("click", function(){
		jQuery("#objective-list .objective-link.active").removeClass("active");
		jQuery(this).addClass("active");
		var link = jQuery(this).clone();
		link.children("span").remove();
		jQuery.ajax({
			url: SITE_URL + "/curriculumexplorer?mode=ajax&objective_parent=" + jQuery(this).attr("data-id") + "&year=" + YEAR + "&course_id=" + COURSE,
			success: function(data) {
				var jsonResponse = JSON.parse(data);
				if (jsonResponse.child_objectives.length > 0) {
					var new_list = jQuery(document.createElement("ul"));
					for (var i=0; i < jsonResponse.child_objectives.length; i++) {
						var new_list_item = jQuery(document.createElement("li"));
						new_list_item.append(
							jQuery(document.createElement("a"))
								.addClass("objective-link")
								.attr("href", "#")
								.attr("data-id", jsonResponse.child_objectives[i].objective_id)
								.html("<span>" + ((COURSE != "" ? 0 : parseInt(jsonResponse.child_objectives[i].course_count)) + parseInt(jsonResponse.child_objectives[i].event_count)) + "</span>" + jsonResponse.child_objectives[i].objective_name));
						new_list.append(new_list_item);
					}
					jQuery("#objective-list").html(new_list);
				}

				jQuery("#objective-details").html("");
				jQuery("#objective-details").append("<h1>"+link.html()+"</h1>");
				if (jsonResponse.courses.length > 0 && COURSE == "") {
					jQuery("#objective-details").append(jQuery(document.createElement("h2")).html("Mapped Courses"));
					for (var i=0; i < jsonResponse.courses.length; i++) {
						var new_course = jQuery(document.createElement("div"));
						new_course.addClass("course-container").attr("data-id", jsonResponse.courses[i].course_id);
						new_course.append(
							jQuery(document.createElement("p")).append(
								jQuery(document.createElement("a"))
										.attr("href", SITE_URL+"/admin/events?section=edit&id="+jsonResponse.courses[i].course_id)
										.html("<strong>"+jsonResponse.courses[i].course_code+":</strong> " + jsonResponse.courses[i].course_name)
							)
						);
						jQuery("#objective-details").append(new_course);
					}
				}

				if (jsonResponse.events != null) {
					jQuery("#objective-details").append(jQuery(document.createElement("h2")).html("Events"));
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
											.attr("href", SITE_URL+"/admin/events?section=edit&id="+jsonResponse.events[v][i].event_id)
											.html(jsonResponse.events[v][i].event_title)
								).append(" <span class=\"content-small\">Event on " + event_date.toDateString() + "</span>")
							);
							course_container.append(new_event);
							delete(event_date);
						}
						jQuery("#objective-details").append(course_container);
					}
				}

				if (jsonResponse.events == null && jsonResponse.courses.length <= 0) {

					if (jsonResponse.objective_parent != OBJECTIVE_PARENT) {
						jQuery("#objective-details").append("<div class=\"display-generic\">We were unable to find any mapped courses or any learning events tied to this objective.</div>");
					} else {
						jQuery("#objective-details").append("<div class=\"display-generic\">Please select a objective from the list on the left.</div>");
					}
				}

				if (typeof jsonResponse.child_objectives[0] != "undefined") {
					var breadcrumb = "";
					for (var i = 0; i < jQuery("#objective-breadcrumb a").length; i++) {
						if (jQuery("#objective-breadcrumb a").eq(i).attr("data-id") == jsonResponse.child_objectives[0].objective_parent) {
							break;
						}
						breadcrumb += "<a href=\"#\" class=\"objective-link\" data-id=\""+jQuery("#objective-breadcrumb a").eq(i).attr("data-id")+"\">"+jQuery("#objective-breadcrumb a").eq(i).html()+"</a> &gt; ";
					}
					jQuery("#objective-breadcrumb").html("");
					jQuery("#objective-breadcrumb").append(breadcrumb).append(link);
				}

			}
		})
		return false;
	});
});