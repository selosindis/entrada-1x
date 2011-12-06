/* Drop down menu */
	jQuery(document).ready(function () {	
	
	jQuery('nav li').hover(
		function () {
			//show its submenu
			jQuery('ul',this).slideDown(100);
		}, 
		function () {
			//hide its submenu
			jQuery('ul',this).slideUp(100);			
		}
	);
	
	/* Collapse-Expand panels */
	jQuery(".toggle-panel").click(function() {
		if (jQuery(".right-nav").hasClass("collapsed")){
			jQuery(".toggle a").addClass("off");
			jQuery(".content").removeClass("span-18").addClass("span-13");
			jQuery(".right-nav").removeClass("collapsed").show("slide", {direction: "right"}, 200);
		}
		else{
			jQuery(".toggle a").removeClass("off");
			jQuery(".right-nav").addClass("collapsed").hide("slide", {direction: "right"}, 200,function(){
			    jQuery(".content").addClass("span-18").removeClass("span-13");
			});
		}
		return false;
	});
});






















