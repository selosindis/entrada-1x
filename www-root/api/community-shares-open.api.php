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
 * this file inserts the open folders into the community_shares_open table
 *
 * @author Organization: David Geffen School of Medicine at UCLA
 * @author Unit: Instructional Design and Technology Unit
 * @author Developer: Sam Payne <spayne@mednet.ucla.edu>
 * @copyright Copyright 2013 Regents of The University of California. All Rights Reserved.
 *
*/

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    dirname(__FILE__) . "/../core/library/vendor",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
    //checks to make sure that the variables we need are set
    if(isset($_POST['community_id']) && isset($_POST['foldersOpen'])) {
        $PROXY_ID = $ENTRADA_USER->getActiveId();
        
        $query        = "SELECT *
                        FROM `community_shares_open`
                        WHERE `community_id` = ".$_POST['community_id']."
                        AND `page_id` = ".$_POST['page_id']."
                        AND `proxy_id` = ".$PROXY_ID;
        
        $results    = $db->GetAll($query);
        if ($results) {
            //update sql
            $SQL = "UPDATE
                    `community_shares_open`
                    SET `shares_open` = '".$_POST['foldersOpen']."'
                    WHERE  `community_id` = ".$_POST['community_id']."
                    AND `page_id` = ".$_POST['page_id']."
                    AND `proxy_id` = ".$PROXY_ID;
        } else {
            //insert sql
            $SQL = "INSERT INTO
                    `community_shares_open` (community_id, page_id, proxy_id, shares_open)
                    VALUES (".$_POST['community_id'].",".$_POST['page_id'].",".$PROXY_ID.",'".$_POST['foldersOpen']."')";
        }
        $db->Execute($SQL);
    }
       
header("Content-type: application/json");
}
?>