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
 * Entrada upgrade helper.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Brandon Thorn <bt37@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 * 
*/

@set_time_limit(0);
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../../../core",
    dirname(__FILE__) . "/../../../core/includes",
    dirname(__FILE__) . "/../../../core/library",
    get_include_path(),
)));

require_once("config/config.inc.php");
require_once "Zend/Loader/Autoloader.php";
$loader = Zend_Loader_Autoloader::getInstance();
require_once("config/settings.inc.php");
require_once("Entrada/adodb/adodb.inc.php");
require_once("functions.inc.php");
require_once("dbconnection.inc.php");

if((!isset($_SERVER["argv"])) || (@count($_SERVER["argv"]) < 1)) {
	echo "<html>\n";
	echo "<head>\n";
	echo "	<title>Processing Error</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "This file should be run by command line only:";
	echo "<div style=\"font-family: monospace\">/usr/bin/php -f ".__FILE__."</div>";
	echo "</body>\n";
	echo "</html>\n";
	exit;
}

echo "\n\n";

$query = "INSERT INTO `org_community_types` (`organisation_id`, `community_type_name`, `default_community_template`, `default_community_theme`, `default_community_keywords`, `default_community_protected`, `default_community_registration`, `default_community_members`, `default_mail_list_type`, `community_type_options`, `community_type_active`)
(
    SELECT a.`organisation_id`, b.`community_type_name`, b.`default_community_template`, b.`default_community_theme`, b.`default_community_keywords`, b.`default_community_protected`, b.`default_community_registration`, b.`default_community_members`, b.`default_mail_list_type`, b.`default_community_type_options`, b.`community_type_active` 
    FROM `".AUTH_DATABASE."`.`organisations` AS a
    JOIN `global_lu_community_types` AS b
    ON b.`ctype_id` = 1
    WHERE a.`organisation_active` = 1
)";

if ($db->Execute($query)) {
	echo "Successfully inserted Organisation specific Community Types.\n";
} else {
	echo "Error while inserting Organisation specific Community Types.\n";
}

$query = "INSERT INTO `community_type_pages` (`type_id`, `type_scope`, `parent_id`, `page_order`, `page_type`, `menu_title`, `page_title`, `page_url`, `page_content`, `page_active`, `page_visible`, `allow_member_view`, `allow_troll_view`, `allow_public_view`, `lock_page`, `updated_date`, `updated_by`) 
(
    SELECT a.`octype_id`, 'organisation', b.`parent_id`, b.`page_order`, b.`page_type`, b.`menu_title`, b.`page_title`, b.`page_url`, b.`page_content`, b.`page_active`, b.`page_visible`, b.`allow_member_view`, b.`allow_troll_view`, b.`allow_public_view`, b.`lock_page`, b.`updated_date`, b.`updated_by`
    FROM `org_community_types` AS a
    JOIN `community_type_pages` AS b
    ON b.`type_id` = 1
    AND b.`type_scope` = 'global'
)";

if ($db->Execute($query)) {
	echo "Successfully inserted Organisation specific Community Type Pages.\n";
} else {
	echo "Error while inserting Organisation specific Community Type Pages.\n";
}

$query = "INSERT INTO `community_type_templates` (`template_id`, `type_id`, `type_scope`) 
(
    SELECT b.`template_id`, a.`octype_id`, 'organisation' FROM `org_community_types` AS a
    JOIN `community_type_templates` AS b
    ON b.`type_id` = 1
    AND b.`type_scope` = 'global'
);";

if ($db->Execute($query)) {
	echo "Successfully inserted Organisation specific Community Type Templates.\n";
} else {
	echo "Error while inserting Organisation specific Community Type Templates.\n";
}

echo "\n\n";