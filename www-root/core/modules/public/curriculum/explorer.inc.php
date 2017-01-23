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
 * This file displays the list of objectives pulled
 * from the entrada.global_lu_objectives table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

if((!defined("PARENT_INCLUDED")) || (!defined("IN_CURRICULUM"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("search", "read")) {
	add_error("Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.");

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
} else {

	$PAGE_META["title"]			= "Curriculum Explorer";
	$PAGE_META["description"]	= "Allowing you to browse the curriculum by objective set, course, and date.";
	$PAGE_META["keywords"]		= "";

	$BREADCRUMB[] = array("url" => ENTRADA_RELATIVE."/curriculum/explorer", "title" => "Explorer");

    search_subnavigation("explorer");
	?>
	<h1>Curriculum Explorer</h1>

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