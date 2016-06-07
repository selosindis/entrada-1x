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
 * A model for handling Course Groups
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Group extends Models_Base  {
    protected   $group_id,
                $group_name,
                $group_type,
                $group_value,
                $start_date,
                $expire_date,
                $group_active,
                $updated_date,
                $updated_by;
    
    protected $table_name           = "groups";
    protected $primary_key          = "group_id";
    protected $default_sort_column  = "group_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID () {
        return $this->group_id;
    }
    
    public function getGroupName () {
        return $this->group_name;
    }
    
    public function getGroupType () {
        return $this->group_type;
    }
    
    public function getGroupValue () {
        return $this->group_value;
    }
    
    public function getStartDate () {
        return $this->start_date;
    }
    
    public function getExpireDate () {
        return $this->expire_date;
    }
    
    public function getGroupActive () {
        return $this->group_active;
    }
    
    public function getUpdatedDate () {
        return $this->updated_date;
    }
    
    public function getUpdatedBy () {
        return $this->updated_by;
    }

    /* @return bool|Models_Group */
    public static function fetchRowByID($group_id) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "group_id", "value" => $group_id, "method" => "=")
        ));
    }

    public static function getGroupMembers ($group_id = null, $active = 1) {
        global $db;
        $members = false;
        
        $query	= "	SELECT a.`id`, a.`number`, a.`firstname`, a.`lastname`, c.`gmember_id`, c.`member_active`,
                    a.`username`, a.`email`, a.`organisation_id`, a.`username`, b.`group`, b.`role`
                    FROM `".AUTH_DATABASE."`.`user_data` AS a
                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
                    ON a.`id` = b.`user_id`
                    INNER JOIN `group_members` c ON a.`id` = c.`proxy_id`
                    WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
                    AND b.`account_active` = 'true'
                    AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
                    AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
                    AND c.`group_id` = ?
                    AND c.`member_active` = ?
                    GROUP BY a.`id`
                    ORDER BY a.`lastname` ASC, a.`firstname` ASC";

        $results = $db->GetAll($query, array($group_id, $active));
        if ($results) {
            foreach ($results as $result) {
                $member = new User();
                $members[] = User::fromArray($result, $member);
            }
        }
        return $members;
    }
    
    public static function getIndividualMembers ($proxy_id = null) {
        global $db;
        $member = false;
        
        $query = "	SELECT a.`id` AS `user_id`, a.`number`, a.`firstname`, a.`lastname`,
                    a.`username`, a.`email`, a.`organisation_id`, b.`group`, b.`role`
                    FROM `".AUTH_DATABASE."`.`user_data` AS a
                    LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
                    ON a.`id` = b.`user_id`
                    WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
                    AND b.`account_active` = 'true'
                    AND (b.`access_starts` = '0' OR b.`access_starts` <= ?)
                    AND (b.`access_expires` = '0' OR b.`access_expires` > ?)
                    AND a.`id` = ?
                    GROUP BY a.`id`
                    ORDER BY a.`lastname` ASC, a.`firstname` ASC";
        
        $result = $db->GetRow($query, array(time(), time(), $proxy_id));
        if ($result) {
            $m = new User();
            $member = User::fromArray($result, $m);
        }
        return $member;
    }

    public static function fetchAllByGroupType($group_type, $organisation_id, $search_term) {
        global $db;

        $output = array();

        $query = "  SELECT a.*
                    FROM `groups` AS a
                    JOIN `group_organisations` AS b
                    ON a.`group_id` = b.`group_id`
                    WHERE a.`group_type` = " . $db->qstr($group_type) . "
                    AND b.`organisation_id` = " . $db->qstr($organisation_id) . "
                    AND a.`group_name` LIKE ".$db->qstr("%".$search_term."%");

        $results = $db->GetAll($query);
        if ($results) {
            foreach ($results as $result) {
                $output[] = new self($result);
            }
        }

        return $output;
    }
}
