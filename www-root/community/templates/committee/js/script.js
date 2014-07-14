/* Drop down menu */
jQuery(document).ready(function () {
    
	if (jQuery(".left-nav").length == 0) {
        jQuery(".content").removeClass("span-18").addClass("span-23");
	}
    
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
	
    getPreference ();
    
	/* Collapse-Expand panels */
	jQuery(".toggle-panel").click(function(e) {
        e.preventDefault();
        setPreference();
		if (jQuery(".right-nav").hasClass("collapsed")) {
			expandMenu ()
		} else {
			collapseMenu ()
		}
	});
});

function getPreference () {
    var preference = readCookie("community_"+ COMMUNITY_ID +"_nav_preference");
    
    if (preference == "collapsed") {
        collapseMenu ();
    } else {
        expandMenu();
    }
}

function setPreference () {
    var preference = (jQuery(".right-nav").hasClass("collapsed") ? "expanded" : "collapsed");
    createCookie("community_"+ COMMUNITY_ID +"_nav_preference", preference, 365);
}

function collapseMenu () {
    jQuery(".toggle a").removeClass("off");
    jQuery(".right-nav").addClass("collapsed").css("display", "none");
    
    if (jQuery(".content").hasClass("span-18")) {
        if (jQuery(".left-nav").length == 0) {
            jQuery(".content").addClass("span-23");
        } else {
             jQuery(".content").removeClass("span-13").addClass("span-18");
        }
    } else {
        jQuery(".content").removeClass("span-13").addClass("span-18");
    }
}

function expandMenu () {
    jQuery(".toggle a").addClass("off");
    jQuery(".right-nav").removeClass("collapsed").css("display", "block");
        
    if (jQuery(".content").hasClass("span-23")) {
        jQuery(".content").removeClass("span-23").addClass("span-18");
    } else {
        if (jQuery(".left-nav").length == 0) {
            jQuery(".content").removeClass("span-23").addClass("span-18");
        } else {
            jQuery(".content").removeClass("span-18").addClass("span-13");
        }
    }
}