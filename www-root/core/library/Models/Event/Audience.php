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
 * @author Organization: Queen's University
 * @author Unit: Health Sciences Education Technology Unit
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 *
 */

class Models_Event_Audience {

    private $eaudience_id,
            $event_id,
            $audience_type,
            $audience_value,
            $updated_date,
            $updated_by;

    /**
     * It's a constructor...
     * @param type $arr
     */
    public function __construct($arr = NULL) {
        if (is_array($arr)) {
            $this->fromArray($arr);
        }
    }

    /**
     * Returns objects values in an array.
     * @return Array
     */
    public function toArray() {
        $arr = false;
        $class_vars = get_class_vars(get_called_class());
        if (isset($class_vars)) {
            foreach ($class_vars as $class_var => $value) {
                $arr[$class_var] = $this->$class_var;
            }
        }
        return $arr;
    }

    /**
     * Uses key-value pair to set object values
     * @return Models_Form
     */
    public function fromArray($arr) {
        $class_vars = array_keys(get_class_vars(get_called_class()));
        foreach ($arr as $class_var_name => $value) {
            if (in_array($class_var_name, $class_vars)) {
                $this->$class_var_name = $value;
            }
        }
        return $this;
    }
    
    public static function fetchRow($eaudience_id) {
        global $db;
        
        $event_audience = false;
        
        $query = "SELECT * FROM `event_audience` WHERE `eaudience_id` = ?";
        $result = $db->GetRow($query, array($eaudience_id));
        if ($result) {
            $event_audience = new self($result);
        }
        
        return $event_audience;
    }
    
    public static function fetchAllByEventID($event_id) {
        global $db;
        
        $event_audience = false;
        
        $query = "SELECT * FROM `event_audience` WHERE `event_id` = ?";
        $results = $db->GetAll($query, array($event_id));
        if ($results) {
            $event_audience = array();
            foreach ($results as $result) {
                $event_audience[] = new self($result);
            }
        }
        
        return $event_audience;
    }

    /**
     * This method searches the audience of the specified event to see if the proxy_id
     * provided should be an active audience member.
     *
     * @param $proxy_id
     * @param $event_id
     * @param int $event_start
     * @return bool
     */
    public function isAudienceMember($proxy_id, $event_id, $event_start = 0) {
        $audience = array();

        $event_audience = $this->fetchAllByEventID($event_id);
        if ($event_audience) {
            foreach ($event_audience as $event) {
                $a = $event->getAudience($event_start);

                $members = $a->getAudienceMembers();
                if ($members) {
                    foreach ($members as $member) {
                        $audience[] = $member["id"];
                    }
                }
            }

            if ($audience && in_array($proxy_id, $audience)) {
                return true;
            }
        }

        return false;
    }
    
    public function getEventAudienceID() {
        return $this->eaudience_id;
    }
    
    public function getAudienceType() {
        return $this->audience_type;
    }

    public function getAudience($event_start = 0) {
        global $db;
        
        $audience = false;

        $event_start = (int) $event_start;

        switch ($this->audience_type) {
            case "proxy_id" :
                $query = "SELECT `id`, `firstname`, `lastname` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ?";
                $result = $db->GetRow($query, array($this->audience_value));
                if ($result) {
                    $audience_data["audience_name"] = $result["firstname"] . " " . $result["lastname"];
                    $audience_data["audience_type"] = $this->audience_type;
                    $audience = new Models_Audience($audience_data);
                }
            break;
            case "cohort" :
                $query = "SELECT `group_id`, `group_name` FROM `groups` WHERE `group_id` = ?";
                $result = $db->GetRow($query, array($this->audience_value));
                if ($result) {
                    $audience_data["audience_name"] = $result["group_name"];
                    $audience_data["audience_type"] = $this->audience_type;
                    $query = "SELECT b.`id`, b.`firstname`, b.`lastname` 
                                FROM `group_members` AS a
                                JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                ON a.`proxy_id` = b.`id`
                                WHERE a.`group_id` = ?
                                AND a.`member_active` = '1'";
                    $results = $db->GetAll($query, array($this->audience_value));
                    if ($results) {
                        $audience_data["audience_members"] = $results;
                    }
                    
                    if (!empty($audience_data)) {
                        $audience = new Models_Audience($audience_data);
                    }
                }
            break;
            case "group_id" :
                $query = "SELECT `cgroup_id`, `group_name` FROM `course_groups` WHERE `cgroup_id` = ?";
                $result = $db->GetRow($query, array($this->audience_value));
                if ($result) {
                    $audience_data["audience_name"] = $result["group_name"];
                    $audience_data["audience_type"] = $this->audience_type;
                    $query = "SELECT b.`id`, b.`firstname`, b.`lastname` 
                                FROM `course_group_audience` AS a
                                JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                ON a.`proxy_id` = b.`id`
                                WHERE a.`cgroup_id` = ?
                                AND a.`active` = '1'";
                    $results = $db->GetAll($query, array($this->audience_value));
                    if ($results) {
                        $audience_data["audience_members"] = $results;
                    }
                    
                    if (!empty($audience_data)) {
                        $audience = new Models_Audience($audience_data);
                    }
                }
            break;
            case "course_id" :
                $query = "SELECT *
                            FROM `course_audience` AS a
                            JOIN `curriculum_periods` AS b
                            ON a.`cperiod_id` = b.`cperiod_id`
                            WHERE a.`course_id` = ?
                            AND (? BETWEEN b.`start_date` AND b.`finish_date`)
                            AND b.`active` = '1'";
                $course_audiences = $db->GetAll($query, array($this->audience_value, $event_start));
                if ($course_audiences) {
                    $query = "SELECT `course_name` FROM `courses` WHERE `course_id` = ?";
                    $result = $db->GetRow($query, array($this->audience_value));
                    if ($result) {
                        $audience_data["audience_name"] = $result["course_name"];
                        $audience_data["audience_type"] = $this->audience_type;
                    }
                    
                    $members = array();
                    foreach ($course_audiences as $course_audience) {
                        if ($course_audience && $course_audience["audience_type"] == "group_id") {
                            
                            $query = "SELECT b.`id`, b.`firstname`, b.`lastname`
                                        FROM `group_members` AS a 
                                        JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                        ON a.`proxy_id` = b.`id`
                                        WHERE a.`group_id` = ?
                                        AND a.`member_active` = '1'";
                            $results = $db->GetAll($query, array($course_audience["audience_value"]));
                            if ($results) {
                                $members = array_merge($members, $results);
                            }
                        }
                    }
                    
                    $audience_data["audience_members"] = $members;

                    if (!empty($audience_data)) {
                        $audience = new Models_Audience($audience_data);
                    }
                }
            break;
            default:
                continue;
            break;
        }
        
        return $audience;
    }
}
