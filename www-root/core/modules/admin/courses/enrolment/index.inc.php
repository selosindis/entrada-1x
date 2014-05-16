<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_COURSE_ENROLMENT"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('course', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    
    $course = Models_Course::get($COURSE_ID);
    
    if ($course) {
        courses_subnavigation($course->toArray(), "enrolment");
        $curriculum_periods = Models_CurriculumPeriod::fetchRowByCurriculumTypeIDCourseID($course->getCurriculumTypeID(), $course->getID());
        if ($curriculum_periods) {
            if (isset($_GET["cperiod_id"]) && $temp = clean_input($_GET["cperiod_id"], array("trim", "int"))) {
                $cperiod_id = $temp;				
            }

            $course_audience = $course->getMembers($cperiod_id);

            if (isset($_GET["download"]) && $type = clean_input($_GET["download"], array("trim", "striptags"))) {
                switch($type){
                    case "csv":
                        ob_clean();
                        if ($course_audience) {
                            $output = "";
                            $num_members = 0;
                            foreach ($course_audience as $audience_type => $audience_type_members) {
                                if ($audience_type == "groups") {
                                    foreach ($audience_type_members as $group_name => $audience) {
                                        foreach ($audience as $audience_member) {
                                            $num_members++;
                                            $output .= $group_name.",".$audience_member->getFullname(false).",".$audience_member->getNumber().",". $audience_member->getEmail() . "\n";
                                        }
                                    }
                                } else if ($audience_type == "individuals") {
                                    foreach ($audience_type_members as $audience_member) {
                                        $num_members++;
                                        $output .= "Individual Enrolment,".$audience_member->getFullname(false).",".$audience_member->getNumber().",". $audience_member->getEmail() . "\n";
                                    }
                                }
                            }
                        }

                        $output .= "\n\n";
                        $output .= "Total Number of Users,,".$num_members."\n";
                        header("Pragma: public");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Content-Type: text/csv");
                        header("Content-Disposition: inline; filename=\"ClassList.csv\"");
                        header("Content-Length: ".@strlen($output));
                        header("Content-Transfer-Encoding: binary\n");

                        echo $output;
                        exit;
                        break;
                    default:
                        break;
                }
            } ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var timer;  
                    var done_interval = 600;
                    var course_id = "<?php echo $course->getID(); ?>";
                    var enrolment_view = "<?php echo (isset($PREFERENCES["enrolment_view"]) ? $PREFERENCES["enrolment_view"] : "grid"); ?>";

                    getSyncDate(course_id);

                    $(".view-toggle[data-view='"+ enrolment_view +"']").addClass("active");
                    $(".enrolment-loading").removeClass("hide");

                    getEnrolments(course_id, enrolment_view);

                    $("#enrolment-search").keyup(function () {
                        $("#enrolment-container").empty();
                        $(".enrolment-loading").removeClass("hide");
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            getEnrolments(course_id, enrolment_view);
                        }, done_interval);
                    });

                    $("#cperiod_select").on("change", function () {
                        $("#enrolment-container").empty();
                        $(".enrolment-loading").removeClass("hide");
                        $("#sync-icon").addClass("icon-refresh");
                        $("#sync-enrolment").removeClass("disabled synched").html("<i id=\"sync-icon\" class=\"icon-refresh\"></i> Synchronize Enrolment");
                        getSyncDate(course_id);
                        getEnrolments(course_id, enrolment_view);
                        
                        $("#sync-enrolment").off("click");
                        $("#sync-enrolment").on("click", function (e) {
                            e.preventDefault();
                            $(this).off("click");
                            var cperiod_id = $("#cperiod_select").val();

                            $("#sync-icon").removeClass("icon-refresh").addClass("icon-loading");
                            $(this).addClass("disabled");
                            $("#enrolment-container").empty();
                            $(".enrolment-loading").removeClass("hide");
                            jQuery.ajax ({
                                url : "<?php echo ENTRADA_URL ?>/api/course-enrolment.api.php",
                                type : "GET",
                                data : "method=sync&course_id=" + course_id + "&cperiod_id=" + cperiod_id,
                                success: function(data) {
                                    var jsonResponse = JSON.parse(data);
                                    if (jsonResponse.status == "success") {
                                        getEnrolments(course_id, enrolment_view);
                                        $("#sync-date").html(jsonResponse.data.sync_date);
                                    }

                                }
                            });
                        });
                    });

                    $("#sync-enrolment").on("click", function (e) {
                        e.preventDefault();
                        $(this).off("click");
                        var cperiod_id = $("#cperiod_select").val();

                        $("#sync-icon").removeClass("icon-refresh").addClass("icon-loading");
                        $(this).addClass("disabled");
                        $("#enrolment-container").empty();
                        $(".enrolment-loading").removeClass("hide");
                        jQuery.ajax ({
                            url : "<?php echo ENTRADA_URL ?>/api/course-enrolment.api.php",
                            type : "GET",
                            data : "method=sync&course_id=" + course_id + "&cperiod_id=" + cperiod_id,
                            success: function(data) {
                                console.log(data);
                                var jsonResponse = JSON.parse(data);
                                if (jsonResponse.status == "success") {
                                    getEnrolments(course_id, enrolment_view);
                                    $("#sync-date").html(jsonResponse.data.sync_date);
                                }

                            }
                        });    
                    });

                    $(".view-toggle").on("click", function (e) {
                        e.preventDefault();
                        $("#enrolment-container").empty();
                        $(".enrolment-loading").removeClass("hide");
                        enrolment_view = $(this).attr("data-view");
                        getEnrolments(course_id, enrolment_view);
                    });

                    $("#print").on("click", function (e) {
                        e.preventDefault();
                        window.print();
                    });
                });

                function getEnrolments (course_id, enrolment_view) {
                    var search_term = jQuery("#enrolment-search").val();
                    var cperiod_id = jQuery("#cperiod_select").val();

                    jQuery.ajax ({
                        url : "<?php echo ENTRADA_URL ?>/api/course-enrolment.api.php",
                        type : "GET",
                        data : "method=list&course_id=" + course_id + "&cperiod_id=" + cperiod_id + "&enrolment_view=" + enrolment_view + "&search_term=" + search_term,
                        success: function(data) {
                            jQuery(".enrolment-loading").addClass("hide");
                            if (jQuery("#sync-icon").hasClass("icon-loading")) {
                                jQuery("#sync-enrolment").html("Enrolment Synchronized");
                            }
                            jQuery("#sync-icon").removeClass("icon-loading").addClass("icon-refresh");
                            var jsonResponse = JSON.parse(data);
                            if (jsonResponse.status == "success") {
                                switch (enrolment_view) {
                                    case "grid" :
                                        listEnrolmentGrid(jsonResponse.data);
                                    break;
                                    case "table" :
                                        listEnrolmentTable(jsonResponse.data);
                                    break;
                                }   
                            } else {
                                display_notice(jsonResponse.data, "#enrolment-container", "append");
                            }
                        }
                    });
                }

                function listEnrolmentGrid (enrolment) {
                    jQuery.each(enrolment, function (audience_type, audience_type_members) {
                        if (audience_type == "groups") {
                            jQuery.each(audience_type_members, function (group_name, audience) {
                                var group_heading = document.createElement("h2");
                                var group_div = document.createElement("div");

                                jQuery(group_div).addClass("row space-below medium").attr({id: group_name.split(' ').join('-').toLowerCase() + "-section"});
                                jQuery(group_heading).text(group_name).attr({title: group_name + " Section"}).addClass("collapsable");
                                jQuery("#enrolment-container").append(group_heading);

                                jQuery.each(audience, function (audience, audience_member) {
                                    var media_div = buildGrid(audience_member);
                                    jQuery(group_div).append(media_div);
                                    jQuery("#enrolment-container").append(group_div);
                                });
                            });
                        } else if (audience_type == "individuals") {
                            var group_heading = document.createElement("h2");
                            var group_div = document.createElement("div");

                            jQuery(group_div).addClass("row space-below medium");
                            jQuery(group_heading).text("Individual Enrolments");
                            jQuery("#enrolment-container").append(group_heading);

                            jQuery.each(audience_type_members, function (audience_type_members, audience_member) {
                                var media_div = buildGrid(audience_member);
                                jQuery(group_div).append(media_div);
                                jQuery("#enrolment-container").append(group_div);
                            });
                        }
                    });
                }

                function listEnrolmentTable(enrolment) {
                    jQuery.each(enrolment, function (audience_type, audience_type_members) {
                        if (audience_type == "groups") {
                            jQuery.each(audience_type_members, function (group_name, audience) {
                                var group_heading = document.createElement("h2");
                                var group_div = document.createElement("div");

                                jQuery(group_div).addClass("row space-below medium").attr({id: group_name});
                                jQuery(group_heading).text(group_name);
                                jQuery("#enrolment-container").append(group_heading);

                                var table = document.createElement("table");
                                var table_head = document.createElement("thead");
                                var name_heading = document.createElement("th");
                                var email_heading = document.createElement("th");
                                var number_heading = document.createElement("th");

                                jQuery(name_heading).text("Student Name");
                                jQuery(email_heading).text("Student Email");
                                jQuery(number_heading).text("Student Number");

                                jQuery(table_head).append(name_heading).append(email_heading).append(number_heading);
                                jQuery(table).addClass("table table-striped table-bordered").append(table_head);


                                jQuery.each(audience, function (audience, audience_member) {
                                    var row = buildTable(audience_member);
                                    jQuery(table).append(row);
                                    jQuery("#enrolment-container").append(table);
                                });
                            });
                        } else if (audience_type == "individuals") {
                            var group_heading = document.createElement("h2");
                            var group_div = document.createElement("div");

                            jQuery(group_div).addClass("row space-below medium");
                            jQuery(group_heading).text("Individual Enrolments");
                            jQuery("#enrolment-container").append(group_heading);

                            var table = document.createElement("table");
                            var table_head = document.createElement("thead");
                            var name_heading = document.createElement("th");
                            var email_heading = document.createElement("th");
                            var number_heading = document.createElement("th");

                            jQuery(name_heading).text("Student Name");
                            jQuery(email_heading).text("Student Email");
                            jQuery(number_heading).text("Student Number");

                            jQuery(table_head).append(name_heading).append(email_heading).append(number_heading);
                            jQuery(table).addClass("table table-striped table-bordered").append(table_head);

                            jQuery.each(audience_type_members, function (audience_type_members, audience_member) {
                                var row = buildTable(audience_member);
                                jQuery(table).append(row);
                                jQuery("#enrolment-container").append(table);

                            });
                        }
                    });
                }

                function buildGrid (audience_member) {
                    var media_div = document.createElement("div");
                    var media_body = document.createElement("div");
                    var media_heading = document.createElement("h4");
                    var media_heading_a = document.createElement("a");
                    var media_heading_small = document.createElement("small");
                    var media_p = document.createElement("p");
                    var media_p_email_a = document.createElement("a");

                    jQuery(media_div).addClass("media course-members-media-list");
                    jQuery(media_body).addClass("media-body");
                    jQuery(media_heading).addClass("media-heading");
                    jQuery(media_heading_a).addClass("print-black").text(audience_member.firstname + " " + audience_member.lastname).attr({href: "<?php echo ENTRADA_URL . "/people?profile=" ?>" + audience_member.username});
                    jQuery(media_heading_small).addClass("pull-right print-black").text("#"+ audience_member.number);
                    jQuery(media_p_email_a).addClass("print-black").text(audience_member.email).attr({href: "mailto:" + audience_member.email});

                    jQuery(media_heading).append(media_heading_a).append(media_heading_small);
                    jQuery(media_p).append(media_p_email_a);
                    jQuery(media_body).append(media_heading).append(media_p);
                    jQuery(media_div).append(media_body);

                    return media_div;
                }

                function buildTable (audience_member) {
                    var row = document.createElement("tr");
                    var name_cell = document.createElement("td");
                    var email_cell = document.createElement("td");
                    var number_cell = document.createElement("td");
                    var name_a = document.createElement("a");
                    var email_a = document.createElement("a");
                    var number_a = document.createElement("a");

                    jQuery(name_a).text(audience_member.firstname + " " + audience_member.lastname).attr({href: "<?php echo ENTRADA_URL . "/people?profile=" ?>" + audience_member.username});
                    jQuery(email_a).text(audience_member.email).attr({href: "mailto:" + audience_member.email});
                    jQuery(number_a).text("#"+ audience_member.number).attr({href: "<?php echo ENTRADA_URL . "/people?profile=" ?>" + audience_member.username});

                    jQuery(name_cell).append(name_a);
                    jQuery(email_cell).append(email_a);
                    jQuery(number_cell).append(number_a);
                    jQuery(row).append(name_cell).append(email_cell).append(number_cell);

                    return row;
                }

                function getSyncDate(course_id) {
                    var cperiod_id = jQuery("#cperiod_select").val();

                    jQuery.ajax ({
                        url : "<?php echo ENTRADA_URL ?>/api/course-enrolment.api.php",
                        type : "GET",
                        data : "method=sync_date&course_id=" + course_id + "&cperiod_id=" + cperiod_id,
                        success: function(data) {
                            console.log(data);
                            var jsonResponse = JSON.parse(data);
                            if (jsonResponse.status == "success") {
                                jQuery("#sync-date").html(jsonResponse.data.ldap_sync_date);
                            }
                        }
                    });
                }
            </script>
            <div class="row-fluid">
                <div class="span12">
                    <div class="span3">
                        <h1>Enrolment</h1>
                    </div>
                    <div class="span9 no-printing">
                        <?php 
                        if ($curriculum_periods) { ?>
                        <form class="pull-right form-horizontal no-printing" style="margin-bottom:0; margin-top:18px">
                            <div class="control-group">
                                <label for="cperiod_select" class="control-label muted">Period:</label>
                                <div class="controls">
                                    <select style="width:100%" id="cperiod_select" name="cperiod_select">
                                        <?php														
                                        foreach ($curriculum_periods as $period) { ?>
                                            <option value="<?php echo html_encode($period->getID());?>" <?php echo (($period->getID() == $cperiod_id) ? "selected=\"selected\"" : "");?>>
                                                <?php echo (($period->getCurriculumPeriodTitle()) ? html_encode($period->getCurriculumPeriodTitle()) . " - " : "") . date("F jS, Y", html_encode($period->getStartDate()))." to ".date("F jS, Y", html_encode($period->getFinishDate())); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row-fluid no-printing">
                <div class="span12">
                    <div class="span5">
                        <div class="row-fluid no-printing">
                            <form>
                                <div class="control-group">
                                    <div class="controls">
                                        <input style="margin-bottom:0px" type="text" id="enrolment-search" placeholder="Search Enrolment" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="span7">
                        <div class="pull-right">
                            <div class="btn-group">
                                <a class="btn course-enrolment-button" id="sync-enrolment" href="#"><i class="icon-refresh" id="sync-icon"></i> Synchronize Enrolment</a>
                                <button class="btn dropdown-toggle course-enrolment-dropdown" data-toggle="dropdown">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo ENTRADA_URL;?>/admin/courses/enrolment?id=<?php echo html_encode($course->getID());?>&amp;cperiod_id=<?php echo $cperiod_id ?>&amp;download=csv"><i class="icon-file"></i> Download Enrolment as CSV</a></li>
                                    <li><a href="#" id="print"><i class="icon-print"></i> Print Enrolment</a></li>
                                </ul>
                            </div>
                            <div class="btn-group" data-toggle="buttons-radio">
                                <a href="#" title="Toggle grid view" data-view="grid" class="btn course-enrolment-button view-toggle"><i class="icon-th-large"></i></a>
                                <a href="#" title="Toggle table view" data-view="table" class="btn course-enrolment-button view-toggle"><i class=" icon-align-justify"></i></a>
                            </div>
                        </div>
                        <p class="text-success pull-right" id="sync-date"></p>
                    </div>                
                </div>
            </div>
            <div class="enrolment-loading hide"><img src="<?php echo ENTRADA_URL ."/images/loading.gif" ?>" /></div>
            <div id="enrolment-container"></div>
        <?php
        } else { ?>
            <div class="alert alert-warning">
                This course currently has no curriculum periods associated with it.
            </div>
        <?php    
        }
    } else { ?>
        <div class="alert alert-warning">
            In order to edit a course enrolment you must provide a course identifier.
        </div>
    <?php    
    }
}