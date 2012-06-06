/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var username = "";
		var password = "";
		var previous_data = 0;
		var url = "";
	
		function onBodyLoad(){
			document.addEventListener("deviceready", onDeviceReady, false);
		}

		function onDeviceReady () {
			//window.localStorage.clear();
			
			$("#require-password").bind ("change", function () {
				$("#password").show();
				$("#password-label").show();
				$("#username").show();
				$("#username-label").show();
				console.log("require change");
				
			});
			
			$("#save-password").bind ("change", function () {
				console.log("save change");
				if ($("#password").val() == "" || $("#username").val() == "") {
					$("#require-password").attr("checked","checked");
					navigator.notification.alert("You must supply your login credentials in order to remain logged in.");
				} else{
					$("#password").hide();
					$("#password-label").hide();
					$("#username").hide();
					$("#username-label").hide();
					
				}
			});
			
			window.localStorage.setItem("url", "192.168.2.4/entrada/www-root");
			/**
			 * check to see if there are settings in local storage, if there is then set each value of the settings form
			 */
			if (window.localStorage.getItem("url")) {
				$("#url").val(window.localStorage.getItem("url"));
			}
			
			if (window.localStorage.getItem("username")) {
				$("#username").val(window.localStorage.getItem("username"));
			}
			
			if (window.localStorage.getItem("password-settings")) {
				var settings = window.localStorage.getItem("password-settings");
				if (settings == 1) {
					$("#save-password").prop("checked", true);
					$("#password").hide();
					$("#password-label").hide();
					$("#username").hide();
					$("#username-label").hide();
					set_methods();
				} else {
					$("#require-password").prop("checked", true);
					$(".close").hide();
					$("#password").show();
					$("#password-label").show();
					$("#username").show();
					$("#username-label").show();
					dhx.Touch.disable();
					$.mobile.changePage("#settings", { 
						transition: "flip"
					});
				}
			}
			
			/**
			 * if any settings are not in local storage then send the user to the settings page where they must supply missing information
			 */
			if (!window.localStorage.getItem("url") || !window.localStorage.getItem("username") || !window.localStorage.getItem("password-settings")) {
				$(".close").hide();
				dhx.Touch.disable();
				$.mobile.changePage("#settings", { 
					transition: "flip"
				});
			}

			$("#save-settings").bind("tap", function () {
				
				/**
				 * store url, username and password to be used in later api calls
				 */
				window.url = $("#url").val();
				window.username = $("#username").val();
				window.password = $("#password").val();
				
				
				/**
				 * save the values of the settings form into internal storage
				 */
				window.localStorage.setItem("password-settings", $("input[name=login-option]:radio:checked").val());
				window.localStorage.setItem("url", window.url);
				window.localStorage.setItem("username", window.username);
				
				var settings = window.localStorage.getItem("password-settings");
				if (settings == 1) 
				{
					if (window.localStorage.getItem("private_hash") == "") { 
						var method = "hash";
						$.ajax({
							url:'http://' + window.localStorage.getItem("url") + '/api/mobile.api.php',
							type: 'post',
							data: ({method:method, username:window.username, password:window.password}),
							dataType: 'text',
							success:function (data) {
								window.localStorage.setItem("private_hash", data);
								//navigator.notification.alert(window.localStorage.getItem("private_hash"));
							}, error: function (event, request, settings, exception) {
								//navigator.notification.alert("Error Calling: " + settings.url + "<br />HTTP Code: " + request.status + exception + event.Behavior);
							} 

						});
					}
				} else if (settings == 0) {
					window.localStorage.setItem("private_hash", "");
				}
				set_methods();
			});

			function set_methods() {
				$("#agenda").live("swipeleft", function () {
					$(".notices").addClass("ui-btn-active");
					$(".agenda").removeClass("ui-btn-active");
					$.mobile.changePage("#events", { transition: "slide" });
				});

				$("#events").live("swiperight", function () {
					$(".agenda").addClass("ui-btn-active");
					$(".notices").removeClass("ui-btn-active");
					$.mobile.changePage("#agenda", { transition: "slide", reverse: true });
				});

				$("#old_notice_page_content").live("swiperight", function () {
					$(".notices").addClass("ui-btn-active");
					$.mobile.changePage("#events", { transition: "slide", reverse: true });
				});

				$("#notice_page_content").live("swiperight", function () {
					$(".notices").addClass("ui-btn-active");
					$.mobile.changePage("#events", { transition: "slide", reverse: true });
				});

				load_scheduler();
				fetch_notices();
				count_notices(true);

				$("#logout").bind("tap", function () {
					$$("scheduler").clearAll();
				});

				$(".agenda").bind("tap", function () {
					$(".agenda").addClass("ui-btn-active");
					$(".notices").removeClass("ui-btn-active");
				});

				$(".notices").bind("tap", function () {
					$(".notices").addClass("ui-btn-active");
					$(".agenda").removeClass("ui-btn-active");
					$(".agenda").attr("data-direction", "reverse");
					$(".notices").addClass("ui-btn-active");
					dhx.Touch.disable();
					count_notices(false);
					fetch_notices();
				});

				$(".close").bind("tap", function() {
					//dhx.Touch.enable();
					if ($(".agenda").hasClass("ui-btn-active")) {
						//window.location.href = "#agenda";
					} else if ($(".notices").hasClass("ui-btn-active") || $(".notices").hasClass("ui-li-has-count")) {
						//window.location.href = "#events";
					}
				});

				$(".back").bind("tap", function () {
					$(".notices").addClass("ui-btn-active");
				});
			}

			function load_scheduler() {
				var method = "agenda";
					$.ajax({
					url:'http://' + window.localStorage.getItem("url") + '/api/mobile.api.php',
					type: 'post',
					dataType: 'text',
					data: ({method:method, username:window.username, password:window.password, hash:window.localStorage.getItem("private_hash")}),
					success:function(data){
						if ($.isEmptyObject(data)) {
							navigator.notification.alert("Failed to authenticate");
						} else {
							$.mobile.changePage("#agenda", { 
								transition: "flip"
							});
							scheduler.config.readonly = true;
							scheduler.config.header_date = "%M %j, %Y";
							scheduler.config.item_date = "%l %F %j, %Y";
							scheduler.config.hour_date = "%h:%i%a";
							scheduler.config.scale_hour = "%h";
							dhx.ready(function(){
								dhx.Touch.disable();
								dhx.ui({
									view: "scheduler",
									container: "content",
									id: "scheduler"
								});

								$$("scheduler").parse(data);
								$$("scheduler").$$("dayList").scrollTo(0,31*8);
								$$("scheduler").$$("buttons").setValue("day");
								$$("scheduler").$$("day").show();
								scheduler_size();
							});

							$(window).bind("resize", function () {
								scheduler_size();
							});
							function scheduler_size() {
								var width = $("html").width();
								var height = $("html").height() - 95;
								$("#content").css("height", height);
								$$("scheduler").define("width", width);
								$$("scheduler").define("height", height);
								$$("scheduler").resize();
								$(".dhx_dayevents_scale_event").css("width", width - 45);
								$(".dhx_dayevents_scale_event").children("div").each(function () {
									$(this).css("width", width - 44);
								});

								if ($$("scheduler").$$("buttons").getValue() =="day") {
									$$("scheduler").$$("dayList").render();
								}
							}
						}
					}, error: function (event, request, settings, exception) {
						navigator.notification.alert("Error Calling: " + settings.url + "<br />HTTP Code: " + request.status + exception + event.Behavior);
					} 
				});
			}

			function fetch_notices () {
				var method = "notices";
				$.ajax({
					url:'http://' + window.localStorage.getItem("url") + '/api/mobile.api.php',
					type: 'post',
					data: ({method:method, sub_method:"fetchnotices", username:window.username, password:window.password, hash:window.localStorage.getItem("private_hash")}),
					dataType: 'json',
					success:function (data) {
						list_notices (data);
					}, error: function (event, request, settings, exception) {
						navigator.notification.alert("Error Calling: " + settings.url + "<br />HTTP Code: " + request.status + exception + event.Behavior);
					} 

				});
			}

			function list_notices (data) {
				$("#old_notice_list").hide();
				var list = $("#notice_list");
				var old_list = $("#old_notice_list");
				list.html("");
				list.append("<li data-role='list-divider' role='heading' class='ui-li ui-li-divider ui-btn ui-bar-b ui-corner-top ui-btn-up-undefined'>Newest Notices</li>");
				old_list.html("");
				old_list.append("<li data-role='list-divider' role='heading' class='ui-li ui-li-divider ui-btn ui-bar-c ui-corner-top ui-btn-up-undefined'>Previously Read Notices</li>");
				$.each(data, function (key, val) {
					if (val.last_read <= val.updated_date) {
						list.append("<li data-theme='d' data-role='button' id='"+ val.notice_id +"' class='ui-btn notice_button'><a href='#notice_page' data-transition='slide'>"+ val.default_date +"</a></li>");
						$("#"+ val.notice_id +"").bind("tap", function () {
							var notice_details_list = $("#notice_details_list");
							notice_details_list.html("");
							notice_details_list.append("<li data-role='list-divider' role='heading' class='ui-li ui-li-divider ui-btn ui-bar-b ui-corner-top ui-btn-up-undefined'>" + val.default_date + "</li>");
							notice_details_list.append("<li data-theme='d' class='notice_summary'>" + val.notice_summary + "</li>");
							$(".mark_read").bind("tap", function() {
								var method = "notices";
								var sub_method = "mark_read";
								var id = val.notice_id;
								$.ajax({
									url:'http://' + window.localStorage.getItem("url") + '/api/mobile.api.php',
									type: 'post',
									data: ({method : method, sub_method: sub_method, username: window.username, password:window.password, notice_id: id, hash:window.localStorage.getItem("private_hash")}),
									dataType: 'text',
									success: function(data) {
										count_notices(false);
									}	
								});
								count_notices(false);
								$(".mark_read").unbind("tap");
							});
							notice_details_list.listview("destroy").listview();
						});
					} else {
						$("#old_notice_list").show();
						old_list.append("<li data-theme='d' data-role='button' id='"+ val.notice_id +"' class='ui-btn notice_button'><a href='#old_notice_page' data-transition='slide'>"+ val.default_date +"</a></li>");
						$("#"+ val.notice_id +"").bind("tap", function () {
							var old_notice_details_list = $("#old_notice_details_list");
							old_notice_details_list.html("");
							old_notice_details_list.append("<li data-role='list-divider' role='heading' class='ui-li ui-li-divider ui-btn ui-bar-b ui-corner-top ui-btn-up-undefined'>" + val.default_date + "</li>");
							old_notice_details_list.append("<li data-theme='d' class='notice_summary'>" + val.notice_summary + "</li>");
							old_notice_details_list.listview("destroy").listview();
						});
					}
				});
				
					$("#old_notice_list").listview();
					list.listview();
					//$("#events").page();
				
				
			}

			function scheduler_update () {
				/*var method = "agenda";
				$.ajax({
					url:'http://' + window.url + '/api/mobile.api.php',
					type: 'post',
					data: ({method : method}),
					success:function(data){
						$$("scheduler").clearAll()
						$$("scheduler").parse(data);

						if($$("scheduler").$$("buttons").getValue()=="day"){ 
							$$("scheduler").$$("dayList").render();
						}

						if($$("scheduler").$$("buttons").getValue() == "month") {
							$$("scheduler").$$("calendarView").render();
						}
					}
				});
				$("#result").ajaxError(function(event, request, settings, exception) {
					$("#result").html("Error Calling: " + settings.url + "<br />HTTP Code: " + request.status + exception + event.Behavior);
				});*/
			}

			function count_notices (timeout) {
				var method = "notices";
				var sub_method = "count_notices";
				$.ajax({
					url:'http://' + window.localStorage.getItem("url") + '/api/mobile.api.php',
					type: 'post',
					data: ({method : method, sub_method: sub_method, username:window.username, password:window.password, hash:window.localStorage.getItem("private_hash")}),
					dataType: 'text',
					complete: function() {
						if (timeout) {
							setTimeout(function () {
								count_notices(true);
							}, 30000);
						} else {
						}
					},
					success: function(data) {
						if (data > 0) {
							$("#notice_list").show();
							if (data != window.previous_data) {
								$(".notice").addClass("ui-li-has-count");
								$(".notice a").append("<span class='ui-li-count ui-btn-down-e ui-btn-corner-all count' style='right: 18px;'>" + data + "</span>");
								fetch_notices();
							} else {
							}
						} else if (data == 0) {
							$("#notice_list").hide();
							//$(".count").remove();
							$(".notices").removeClass("ui-btn-up-e");
							fetch_notices();
						}
						window.previous_data = data;
					}
				});
				$("#result").ajaxError(function(event, request, settings, exception) {
					$("#result").html("Error Calling: " + settings.url + "<br />HTTP Code: " + request.status + exception + event.Behavior);
				});
			}
		}