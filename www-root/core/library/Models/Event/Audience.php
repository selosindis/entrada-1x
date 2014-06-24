<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Audience
 *
 * @author rw65
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
    
    public function getEventAudienceID() {
        return $this->eaudience_id;
    }
    
    public function getAudienceType() {
        return $this->audience_type;
    }
    
    public function getAudience() {
        global $db;
        
        $audience = false;

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
                    $results = $db->GetAssoc($query, array($this->audience_value));
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
                    $results = $db->GetAssoc($query, array($this->audience_value));
                    if ($results) {
                        $audience_data["audience_members"] = $results;
                    }
                    
                    if (!empty($audience_data)) {
                        $audience = new Models_Audience($audience_data);
                    }
                }
            break;
            case "course_id" :
                $query = "SELECT * FROM `course_audience` WHERE `course_id` = ?";
                $course_audiences = $db->GetAll($query, array($this->audience_value));
                
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
                            $query = "SELECT `cgroup_id`, `group_name` FROM `course_groups` WHERE `cgroup_id` = ?";
                            $result = $db->GetRow($query, array($course_audience["audience_value"]));
                            if ($result) {
                                $query = "SELECT b.`id`, b.`firstname`, b.`lastname` 
                                            FROM `course_group_audience` AS a
                                            JOIN `".AUTH_DATABASE."`.`user_data` AS b
                                            ON a.`proxy_id` = b.`id`
                                            WHERE a.`cgroup_id` = ?
                                            AND a.`active` = '1'";
                                $results = $db->GetAssoc($query, array($course_audience["audience_value"]));
                                if ($results) {
                                    $members = array_merge($members, $results);
                                }
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
            break;
        }
        
        return $audience;
    }

}

?>
