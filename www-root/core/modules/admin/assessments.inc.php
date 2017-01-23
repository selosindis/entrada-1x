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
 * Primary controller file for the Assessments module.
 * /admin/assessments
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
*/

if(!defined("PARENT_INCLUDED")) {
	exit;
} elseif((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed($MODULES["events"]["resource"], $MODULES["events"]["permission"], false)) {
	$ERROR++;
	$ERRORSTR[]	= sprintf($translate->_("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:%1\$s\">%2\$s</a> for assistance."), html_encode($AGENT_CONTACTS["administrator"]["email"]), html_encode($AGENT_CONTACTS["administrator"]["name"]));

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
    $MODULE_TEXT = $translate->_($MODULE);

    $sidebar_html  = "<ul class=\"menu\">\n";
    $sidebar_html .= "	<li class=\"".(!$SUBMODULE ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."\">".$translate->_("Dashboard")."</a></li>";
    $sidebar_html .= "	<li class=\"".($SUBMODULE == "distributions" ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."/distributions\">".$translate->_("Distributions")."</a></li>";
    $sidebar_html .= "	<li class=\"".($SUBMODULE == "forms" ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."/forms\">".$translate->_("Forms")."</a></li>";
    $sidebar_html .= "	<li class=\"".($SUBMODULE == "items" || $SUBMODULE == "rubrics" ? "on" : "off")."\"><a href=\"".ENTRADA_URL."/admin/".$MODULE."/items\">".$translate->_("Items")."</a></li>";
    $sidebar_html .= "</ul>\n";

    new_sidebar_item($translate->_("Assessment & Evaluation"), $sidebar_html, "page-assessments-eval", "open");
    
    define("IN_ASSESSMENTS", true);

    define("RUBRIC_ITEMTYPE_ID", 11);

    $BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/" . $MODULE, "title" => $translate->_("Assessment & Evaluation"));

    ?>
    <div class="alert alert-info">
        <p class="lead">This feature is available in Entrada ME Consortium Edition only at this time. Please feel free to <a href="http://www.entrada.org/contact" target="_blank"><strong>contact us</strong></a> to arrange a demo.</p>
        <p class="pull-right">
            <a class="btn btn-primary btn-large" href="http://www.entrada.org" target="_blank">
                <i class="fa fa-info-circle"></i> Learn More
            </a>
        </p>
        <div class="clearfix"></div>
    </div>
    <?php
}