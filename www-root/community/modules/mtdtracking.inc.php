<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Controller file for the events / calendar module.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if (!defined("COMMUNITY_INCLUDED")) {
	header("Location: " . ENTRADA_URL);
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}

define("IN_MTDTRACKING", true);

communities_build_parent_breadcrumbs();

$BREADCRUMB[] = array("url" => COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL, "title" => $MENU_TITLE);
$ALLOWED_HTML_TAGS = "<span><a><ol><ul><li><strike><br><p><div><strong><em><h1><h2><h3><small>";

if (communities_module_access($COMMUNITY_ID, $MODULE_ID, $SECTION)) {
	if ((@file_exists($section_to_load = COMMUNITY_ABSOLUTE . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $COMMUNITY_MODULE . DIRECTORY_SEPARATOR . $SECTION . ".inc.php")) && (@is_readable($section_to_load))) {
		/**
		 * Add the RSS feed version of the page to the <head></head> tags.
		 */
		$HEAD[] = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"%TITLE% " . $MENU_TITLE . " RSS 2.0\" href=\"" . COMMUNITY_URL . "/feeds" . $COMMUNITY_URL . ":" . $PAGE_URL . "/rss20\" />";
		$HEAD[] = "<link rel=\"alternate\" type=\"text/xml\" title=\"%TITLE% " . $MENU_TITLE . " RSS 0.91\" href=\"" . COMMUNITY_URL . "/feeds" . $COMMUNITY_URL . ":" . $PAGE_URL . "/rss\" />";

		require_once($section_to_load);
	} else {
		$ONLOAD[] = "setTimeout('window.location=\\'" . COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "\\'', 5000)";

		$ERROR++;
		$ERRORSTR[] = "The action you are looking for does not exist for this module.";

		echo display_error();

		application_log("error", "Communities system tried to load " . $section_to_load . " which does not exist or is not readable by PHP.");
	}
} else {
	$ONLOAD[] = "setTimeout('window.location=\\'" . COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "\\'', 5000)";

	$ERROR++;
	$ERRORSTR[] = "You do not have access to this section of this module. Please contact a community administrator for assistance.";

	echo display_error();
}
?>