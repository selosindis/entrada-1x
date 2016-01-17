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
 * A model for handling communities.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Community extends Models_Base {

    protected $community_id, $community_parent, $category_id, $community_url, $octype_id, $community_template,
        $community_theme, $community_shortname, $community_title, $community_description, $community_keywords,
        $community_email, $community_website, $community_protected, $community_registration, $community_members,
        $community_active, $community_opened, $community_notifications, $sub_communities, $storage_usage, $storage_max,
        $updated_date, $updated_by;

    protected $table_name = "communities";
    protected $default_sort_column = "community_title";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public static function search($search_query, $format = "array", $start_results = 0, $max_results = 100) {
        global $db;

        $output = array();

        if (!in_array($format, array("json", "array")) ) {
            $format = "array";
        }

        $query = "SELECT `community_id`, `category_id`, `community_url`, `community_shortname`, `community_title`, `community_description`, `community_keywords`,
                                    MATCH (`community_title`, `community_description`, `community_keywords`) AGAINST (? IN BOOLEAN MODE) AS `rank`
                                    FROM `communities`
                                    WHERE `community_active`='1'
                                    AND MATCH (`community_title`, `community_description`, `community_keywords`) AGAINST (? IN BOOLEAN MODE)
                                    ORDER BY `rank` DESC, `community_title` ASC
                                    LIMIT ?, ?";

        $results = $db->GetAll($query, array($search_query, $search_query, $start_results, $max_results));
        if ($results) {
            $output = $results;
        }

        switch ($format) {
            case "json" :
                return json_encode($output);
            break;
            case "array" :
            default :
                return $output;
            break;
        }
    }
}
