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
 * General description of this file.
 * 
 * @author Organisation: UCLA
 * @author Unit: David Geffen School of Medicine
 * @author Developer: Zhen Gu <zgu@ucla.edu>
 * @copyright Copyright 2013 David Geffen School of Medicine at UCLA. All Rights Reserved.
 * 
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

if((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) { 

?>
<HTML>
    <HEAD>

    </HEAD>
    <body>
    <div id="inserted"></div>
    <div id="deleted"></div>

    <?php
    $keyword = preg_replace("/[^A-Za-z0-9]/", " ", $_POST['search_term']);
    $EVENT_ID = preg_replace("/[^A-Za-z0-9]/", " ", $_POST['event_id']);

    if (strlen($keyword) >= 2 && $keyword !== ' ') {
        $query = "SELECT DISTINCT d.`descriptor_ui`, d.`descriptor_name` 
            FROM `mesh_descriptors` AS d 
            JOIN `mesh_descriptor_concept` AS dc ON dc.`descriptor_ui` = d.`descriptor_ui`
            JOIN `mesh_concept_term` AS ct ON ct.`concept_ui` = dc.`concept_ui`
            JOIN `mesh_terms` AS t ON t.`term_ui` = ct.`term_ui`
            WHERE NOT EXISTS (
                SELECT ek.`keyword_id` 
                FROM `event_keywords` AS ek where d.`descriptor_ui` = ek.`keyword_id` and ek.`event_id` = ".$db->qstr($EVENT_ID).") 
            AND t.`term_name` LIKE ".$db->qstr("%".$keyword."%")."  
            ORDER BY `descriptor_name`";
        
        echo "<ul>";
        $results = $db->GetAll($query);
        if ($results) {
            foreach($results as $result) {
                echo "<li data-dui=\"".$result['descriptor_ui']."\" id=\"keyword\" data-dname=\"". $result['descriptor_name'] ."\" onclick=\"addval(this)\"><i class=\"icon-plus-sign \"></i> ". $result['descriptor_name'] . "</li>";
            }
        } 
        echo "</ul>";
    }
}
?>
    </body>
    
</HTML>
