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
 * @version $Id: ar_loadgrid.api.php 600 2009-08-12 15:19:17Z ad29 $
*/

/**
 * Load the grid - used by the annualreport module.
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

$proxy_id 	= $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
$args		= html_decode($_GET["t"]);

if(isset($_POST["sortname"]) && $_POST["sortname"] != '') {
	$sort 	= $_POST["sortname"];
} else {
	$sort 	= 'year_reported';
}

if(isset($_POST["sortorder"]) && $_POST["sortorder"] != '') {
	$dir 	= $_POST["sortorder"];
} else {
	$dir 	= 'DESC';
}

if(isset($_POST["rp"]) && $_POST["rp"] != '') {
	$limit 	= $_POST["rp"];
} else {
	$limit 	= '10';
}

if(isset($_POST["page"]) && $_POST["page"] != '') {
	$page 		= $_POST['page'];
	if($page == 1) {
		$start 	= '0';
	} else {
		$start 	= ((int)$page * (int)$limit) - (int)$limit;
	}
} else {
	$page		= '1';
	$start 		= '0';
}

if(isset($_POST["query"]) && $_POST["query"] != '') {
	$where 		= " AND " . $_POST["qtype"] . " LIKE '%" . $_POST["query"] . "%'";
} else {
	$where 		= "";
}

$args 	= explode(",", $args);
$table	= $args[0];

$query = "SELECT COUNT(proxy_id) AS total
FROM `".$table."` 
WHERE `proxy_id` = ".$db->qstr($proxy_id).$where;

$result = $db->GetRow($query);
$total = $result["total"];

$query = "SELECT *
FROM `".$table."` 
WHERE `proxy_id` = ".$db->qstr($proxy_id).$where."
ORDER BY ".$sort." ".$dir."
LIMIT ".$start." , ".$limit;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/json");

if($results = $db->GetAll($query)) {
	$json = "";
	$json .= "{\n";
	$json .= "page: $page,\n";
	$json .= "total: $total,\n";
	$json .= "rows: [";
	$rc = false;
	foreach($results as $row) {
		if ($rc) $json .= ",";
		$json .= "\n{";
		$json .= "id:'".$row[$args[1]]."',";
		$json .= "cell:[";
		for($i=2;$i<count($args);$i++) {
			// Replace all line returns as to not break JSON output (grid will not load otherwise)
			$row[$args[$i]] = str_replace("\r\n", " ", $row[$args[$i]]);
			$row[$args[$i]] = str_replace("\n", " ", $row[$args[$i]]);
			$row[$args[$i]] = str_replace("\r", " ", $row[$args[$i]]);
			if($i > 2) {
				if($row[$args[$i]] != "") {
					$json .= ",'".addslashes($row[$args[$i]])."'";
				} else {
					$json .= ",'".addslashes("N/A")."'";
				}
			} else {
				if($row[$args[$i]] != "") {
					$json .= "'".addslashes($row[$args[$i]])."'";
				} else {
					$json .= "'".addslashes("N/A")."'";
				}
			}
		}
		
		$json .= ",'edit'";
		$json .= "]}";
		$rc = true;
	}
	$json .= "]\n";
	$json .= "}";
	echo $json;
} else {
	echo '({"total":"0", "rows":[]})';
}
?>