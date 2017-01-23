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
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

if (!defined("PARENT_INCLUDED")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("report", "read", false)) {
	$ONLOAD[] = "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	add_error("You do not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	require_once("Classes/utility/Collection.class.php");
	require_once("Classes/utility/SimpleCache.class.php");

	require_once("Classes/organisations/Organisation.class.php");
	require_once("Classes/organisations/Organisations.class.php");

	define("IN_AAMC_CI", true);

	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/reports/aamc", "title" => "AAMC Curriculum Inventory");

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