<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Andrew Dos-Santos <andrew.dos-santos@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 * @version $Id: ar_delete.api.php 600 2009-08-12 15:19:17Z ad29 $
*/

/**
 * Delete the record - used by the annualreport module.
 */

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

date_default_timezone_set(DEFAULT_TIMEZONE);

session_start();

$proxy_id 			= $_GET['id'];
$args				= $_GET['t'];
$rid				= $_GET["rid"];

$args 	= explode(",", $args);
$table	= $args[0];

if(strpos($rid, "|") !== false) {
	$ids 	= explode("|", $rid);
	
	for($i=0; $i<count($ids); $i++) {
		$query = "SELECT *
		FROM `".DATABASE_NAME."`.`".$table."` 
		WHERE `proxy_id` = ".$db->qstr($proxy_id);
		
		if($results = $db->GetAll($query)) {	
			$query = "DELETE FROM `".DATABASE_NAME."`.`".$table."` 
			WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `".$args[1]."` = ".$db->qstr($ids[$i]);
			
			if(!$db->Execute($query)) {
				echo $db->ErrorMsg();
				exit;
			}
		} else {
			echo '({"total":"0", "results":[]})';
		}
	}
} else {
	$id 	= $rid;
	
	$query = "SELECT *
	FROM `".DATABASE_NAME."`.`".$table."` 
	WHERE `proxy_id` = ".$db->qstr($proxy_id);
	
	if($results = $db->GetAll($query)) {	
		$query = "DELETE FROM `".DATABASE_NAME."`.`".$table."` 
		WHERE `proxy_id` = ".$db->qstr($proxy_id)." AND `".$args[1]."` = ".$db->qstr($rid);
		
		if(!$db->Execute($query)) {
			echo $db->ErrorMsg();
			exit;
		}
	} else {
		echo '({"total":"0", "results":[]})';
	}
}
?>