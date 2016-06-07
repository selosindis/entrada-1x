<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves profile information for users or a specific user
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <brandon.thorn@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../core",
    dirname(__FILE__) . "/../../core/includes",
    dirname(__FILE__) . "/../../core/library",
    dirname(__FILE__) . "/../../core/library/vendor",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

$valid_app = false;

if (isset($_GET["app"]) && $tmp_input = clean_input($_GET["app"],array("trim","notags"))) {
	$APP_ID = $tmp_input;
	$query = "	SELECT * FROM `".AUTH_DATABASE."`.`registered_apps` WHERE `script_id` = ".$db->qstr($APP_ID);
	$valid_app = $db->GetRow($query);
}

if (!$valid_app) {
	application_log("error","API accessed with invalid App ID");
	exit;		
}


$PROXY_ID = (int)isset($_GET["uid"])?$_GET["uid"]:0;

$PROFILE = (int)isset($_GET["dept"])?$_GET["dept"]:0;

$APP = (int)isset($_GET["app"])?$_GET["app"]:0;

if (!$PROXY_ID && !$PROFILE) {
	application_log("error","API accessed with no proxy ID and no profile ID provided");
	exit;
}



if (!$PROXY_ID)  exit;

	$query = "	SELECT p.`id` AS `dep_pub_id`, p.`pub_id`, p.`pub_type`,
					   COALESCE(a.`title`,b.`title`,c.`title`,d.`title`,e.`lectures_papers_list`) AS `title`,
					   COALESCE(a.`year_reported`,b.`year_reported`,c.`year_reported`,d.`year_reported`,e.`year_reported`) AS `year`,
					   COALESCE(a.`pubmed_id`,b.`pubmed_id`,c.`pubmed_id`,d.`pubmed_id`,0) AS `pubmed_id`
				FROM `entrada_auth`.`user_data` u 
				JOIN `profile_publications` p
				ON u.`id` = p.`proxy_id`
				LEFT JOIN`ar_peer_reviewed_papers` a
				ON u.`id` = a.`proxy_id`
				AND p.`pub_type` = 'peer_reviewed'
				AND p.`pub_id` = a.`peer_reviewed_papers_id`
				LEFT JOIN `ar_non_peer_reviewed_papers` b
				ON u.`id` = b.`proxy_id`
				AND p.`pub_type` = 'non_peer_reviewed'
				AND p.`pub_id` = b.`non_peer_reviewed_papers_id`				
				LEFT JOIN `ar_book_chapter_mono` c
				ON u.`id` = c.`proxy_id`
				AND p.`pub_type` = 'book_chapters_mono'
				AND p.`pub_id` = c.`book_chapter_mono_id`								
				LEFT JOIN `ar_poster_reports` d 
				ON u.`id` = d.`proxy_id`
				AND p.`pub_type` = 'poster_reports'
				AND p.`pub_id` = d.`poster_reports_id`												
				LEFT JOIN `ar_conference_papers` e
				ON u.`id` = e.`proxy_id`
				AND p.`pub_type` = 'conference_papers'
				AND p.`pub_id` = e.`conference_papers_id`																
				WHERE u.`id` = ".$db->qstr($PROXY_ID).($PROFILE?" AND p.`dep_id` = ".$db->qstr($PROFILE):"")."
				ORDER BY `year` DESC";
	$publications = $db->GetAll($query);
	
	if ($publications) {
		echo json_encode($publications);
	}else{
		echo json_encode(array('error'=>'No publications found for user.'));
	}
exit;
