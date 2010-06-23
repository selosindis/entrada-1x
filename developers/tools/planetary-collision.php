#!/usr/bin/php
<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
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
 * Tools: Planetary Collision. Used for merging two previously independent 
 * Entrada installations into one big clusterfuck of a database. 
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: Harry Brundage <hbrundage@qmed.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__)."/includes");

set_time_limit(0);

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only.";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

require_once("classes/adodb/adodb.inc.php");

function values_sql($fields, $id_increment = 0) {
	global $ddb;
	$r = "(";
	$i = 0;
	foreach($fields as $name => $field) {
		if($i == 0 && $id_increment > 0) {
			$r .= $ddb->qstr($fields[$name]+$id_increment).",";
		} else {
			$r .= $ddb->qstr($fields[$name]).",";
		}
		$i++;
	}
	$r = substr($r, 0, -1).")";
	return $r;
}

$DBUSER = "hbrundage";
$PASSWORD = "balls";
$PRIMARY_DB 		= array("host" => "developer.qmed.ca", "database_prefix" => "shared_test_"	, "user" => $DBUSER, "pass"=>$PASSWORD);
$SECONDARY_DB 		= array("host" => "developer.qmed.ca", "database_prefix" => "rehab_test_"	, "user" => $DBUSER, "pass"=>$PASSWORD);
$DESTINATION_DB 	= array("host" => "developer.qmed.ca", "database_prefix" => "shared_"		, "user" => $DBUSER, "pass"=>$PASSWORD);
$DATABASE_TYPE 		= "mysql";

// Set up reference vars for later use
foreach(array("PRIMARY", "SECONDARY", "DESTINATION") as $which) {
	$ref = $which."_DB";
	$info = $$ref;
	foreach(array("ENTRADA", "AUTH", "CLERKSHIP") as $where) {
		$name = $which."_".$where."_DB";
		$$name = $info["database_prefix"].strtolower($where);
	}
}

$pdb = NewADOConnection($DATABASE_TYPE);
$pdb->Connect($PRIMARY_DB["host"], $PRIMARY_DB["user"], $PRIMARY_DB["pass"], $PRIMARY_DB["database_prefix"]."entrada");
$pdb->SetFetchMode(ADODB_FETCH_ASSOC);

$sdb = NewADOConnection($DATABASE_TYPE);
$sdb->Connect($SECONDARY_DB["host"], $SECONDARY_DB["user"], $SECONDARY_DB["pass"], $SECONDARY_DB["database_prefix"]."entrada");
$sdb->SetFetchMode(ADODB_FETCH_ASSOC);

$ddb = NewADOConnection($DATABASE_TYPE);
$ddb->Connect($DESTINATION_DB["host"], $DESTINATION_DB["user"], $DESTINATION_DB["pass"], $DESTINATION_DB["database_prefix"]."entrada");
$ddb->SetFetchMode(ADODB_FETCH_ASSOC);

// General Structure:
// 1. Find out user id offset by getting destination db auto_increment
// 2. Merge user data tables by pulling all users out of secondary and into primary. Update user ID by adding $X amount.
// 3. Merge courses, events and other stuff, ensuring all proxy ids have $X added to them.

// 1. Proxy ID offset
$data = $pdb->GetRow("SELECT MAX(id) FROM ".$PRIMARY_AUTH_DB.".user_data");
$X = $data["MAX(id)"];

// 2. Merge user data tables
$rs = $sdb->Execute("SELECT * FROM ".$SECONDARY_AUTH_DB.".user_data");

while (!$rs->EOF) {
	$sql = "INSERT INTO ".$DESTINATION_AUTH_DB.".user_data VALUES ".values_sql($rs->fields, $X);
	var_dump($ddb->Execute($sql));
	$rs->MoveNext();
}

// 2. Merge user access tables

$rs->Close();

?>