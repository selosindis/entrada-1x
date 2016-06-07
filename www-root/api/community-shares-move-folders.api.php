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
 * this file updates the parent folder fields to move folders
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
$record = array();
if ($_POST['user_access'] == '3') {
    $admin = true;
} else {
    $admin = false;
}
    
if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"]) && ((bool) $admin)) {
    
    //checks to make sure that the variables we need are set
    if(isset($_POST['community_id']) && (isset($_POST['movedFolders'])) || isset($_POST['movedFiles'])) {
        
        if (isset($_POST['movedFolders'])) {
            foreach($_POST['movedFolders'] as $movedFolder) {
                $SQL = "UPDATE
                `community_shares`
                SET `parent_folder_id` = '" . $movedFolder['destinationFolder'] . "'
                WHERE  `community_id` = " . $_POST['community_id'] . "
                AND `cshare_id` = '" . $movedFolder['folderMoved'] . "'";
                
                if (!$db->Execute($SQL)) {
                    $errors[] = array(
                        "type" => "folder",
                        "community_id" => $_POST['community_id'],
                        "id" => $movedFolder['folderMoved']
                    );
                
                }
            }
        }   

        if (isset($_POST['movedFiles'])) {
            foreach($_POST['movedFiles'] as $movedFile) {
                if ($movedFile['type'] == 'file') {
                    $SQL = "UPDATE
                    `community_share_files`
                    SET `cshare_id` = '" . $movedFile['destinationFolder'] . "'
                    WHERE  `community_id` = " . $_POST['community_id'] . "
                    AND `csfile_id` = '" . $movedFile['id_moved'] . "'";
                    
                    $SQL_file_versions = "UPDATE
                    `community_share_file_versions`
                    SET `cshare_id` = '" . $movedFile['destinationFolder'] . "'
                    WHERE  `community_id` = " . $_POST['community_id'] . "
                    AND `csfile_id` = '" . $movedFile['id_moved'] . "'";
                            //
                } else if ($movedFile['type'] == 'link') { //link
                    $SQL = "UPDATE
                    `community_share_links`
                    SET `cshare_id` = '" . $movedFile['destinationFolder'] . "'
                    WHERE  `community_id` = " . $_POST['community_id'] . "
                    AND `cslink_id` = '" . $movedFile['id_moved'] . "'"; 
                }
                
                if (!$db->Execute($SQL)) {
                    $errors[] = array(
                        "type" => $movedFile['type'],
                        "community_id" => $_POST['community_id'],
                        "id" => $movedFile['id_moved']
                    );
                }

                if ($SQL_file_versions) {
                    if (!$db->Execute($SQL_file_versions)) {
                        $errors[] = array(
                            "type" => $movedFile['type'],
                            "community_id" => $_POST['community_id'],
                            "id" => $movedFile['id_moved']
                        );
                    }
                }

            }
        }
        //adds the errors to the log files and the array for the page feedback
        if ($errors) {
            foreach ($errors as $error) {
                switch ($error['type']) {
                    case 'folder':
                        application_log("error", "Error moving folder id: " . $movedFolder['folderMoved'] . " to folder: " . $movedFolder['destinationFolder']);
                        break;
                    case 'file':
                        application_log("error", "Error moving file id: " . $movedFile['id_moved'] . " to folder: " . $movedFile['destinationFolder']);
                        break;
                    case 'link':
                        application_log("error", "Error moving link id: " . $movedFile['id_moved'] . " to folder: " . $movedFile['destinationFolder']);
                        break;                        
                }
            }
            $record['errors'] = $errors;
        }
    }
} else {
    $record['errors'] = "Not Authorized";
    application_log("error", "Error moving folders - Account not authroized");
}
    header("Content-type: application/json");
    echo json_encode($record);
?>