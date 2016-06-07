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

class Models_Event_Audience extends Models_Base {
    protected   $eaudience_id,
                $event_id,
                $audience_type,
                $audience_value,
                $custom_time,
                $custom_time_start,
                $custom_time_end,
                $updated_date,
                $updated_by;

    protected $audience_name;

    protected $table_name           = "event_audience";
    protected $primary_key          = "eaudience_id";
    protected $default_sort_column  = "eaudience_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID() {
        return $this->eaudience_id;
    }

    public function getEventAudienceID() {
        return $this->eaudience_id;
    }

    public function getEventID() {
        return $this->event_id;
    }

    public function getAudienceType() {
        return $this->audience_type;
    }

    public function getAudienceValue() {
        return $this->audience_value;
    }

    public function getCustomTime() {
        return $this->custom_time;
    }

    public function getCustomTimeStart() {
        return $this->custom_time_start;
    }

    public function getCustomTimeEnd() {
        return $this->custom_time_end;
    }

    public function setEventId($event_id) {
        $this->event_id = $event_id;
    }

    public function setUpdatedBy($id) {
        $this->updated_by = $id;
    }

    public function setUpdatedDate($time) {
        $this->updated_date = $time;
    }

    public function setCustomTime($custom_time) {
        $this->custom_time = $custom_time;
    }

    public function setCustomTimeStart($custom_time_start) {
        $this->custom_time_start = $custom_time_start;
    }

    public function setCustomTimeEnd($custom_time_end) {
        $this->custom_time_end = $custom_time_end;
    }

    public function getAudienceName() {
        if (NULL === $this->audience_name) {
            $audience_value = $this->audience_value;
            $audience_type  = $this->audience_type;
            switch ($audience_type) {
                case "cohort" :
                    $cohort = Models_Group::fetchRowByID($audience_value);
                    if ($cohort) {
                        $this->audience_name = $cohort->getGroupName();
                    }
                break;
                case "group_id" :
                    $cgroup = Models_Course_Group::fetchRowByID($audience_value);
                    if ($cgroup) {
                        $this->audience_name = $cgroup->getGroupName();
                    }
                    break;
                case "proxy_id" :
                    $student = User::fetchRowByID($audience_value);
                    if ($student) {
                        $this->audience_name = $student->getFullname();
                    }
                    break;
            }
        }

        return $this->audience_name;
    }

    /* @return bool|Models_Event_Audience */
    public static function fetchRowByID($eaudience_id = NULL) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "eaudience_id", "value" => $eaudience_id, "method" => "=")
        ));
    }

    /* @return bool|Models_Event_Audience */
    public static function fetchRowByEventIdTypeValue($event_id, $event_type, $event_value) {
        $self = new self();
        return $self->fetchRow(array(
            array("key" => "event_id", "value" => $event_id, "method" => "="),
            array("key" => "audience_type", "value" => $event_type, "method" => "="),
            array("key" => "audience_value", "value" => $event_value, "method" => "=")
        ));
    }

    /* @return ArrayObject|Models_Event_Audience[] */
    public static function fetchAllByEventIdType($event_id, $audience_type) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "event_id", "value" => $event_id, "method" => "="),
            array("key" => "audience_type", "value" => $audience_type, "method" => "=")
        ));
    }

    /* @return ArrayObject|Models_Event_Audience[] */
    public static function fetchAllByEventID($event_id = NULL) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "event_id", "value" => $event_id, "method" => "=")
        ));
    }

    public function delete() {
        global $db;
        $sql = "DELETE FROM `" . $this->database_name . "`.`" . $this->table_name . "`
                WHERE `" . $this->primary_key . "` = " . $db->qstr($this->getID());

        if ($db->Execute($sql)) {
            return true;
        } else {
            application_log("error", "Error deleting  ".get_called_class()." id[" . $this->getID() . "]. DB Said: " . $db->ErrorMsg());
            return false;
        }
    }

    public static function buildSimpleArray($audience_array) {
        if (isset($audience_array) && is_array($audience_array)) {
            $new_audience = array(
                "audience_type"     => $audience_array["audience_type"],
                "audience_value"    => (int)$audience_array["audience_value"],
                "custom_time"       => (int)$audience_array["custom_time"],
                "custom_time_start" => (int)$audience_array["custom_time_start"],
                "custom_time_end"   => (int)$audience_array["custom_time_end"]
            );
        }
        return $new_audience;
    }

    public static function buildInsertUpdateDelete($array) {
        $return = array(
            "insert" => "",
            "update" => "",
            "delete" => ""
        );

        if (isset($array) && is_array($array)) {
            $insert = array();
            $update = array();
            $delete = array();
            $add    = $array["add"];
            $remove = $array["remove"];
            if (isset($add) && is_array($add) && !empty($add)) {
                foreach($add as $key => $item) {
                    // If the key is not set in the remove
                    if (is_array($remove)) {
                        if (!array_key_exists($key, $remove)) {
                            $insert[$key] = unserialize($item);
                        } else {
                            $update[$key] = unserialize($item);
                            unset($remove[$key]);
                        }
                    }
                }
                if (is_array($remove) && !empty($remove)) {
                    foreach ($remove as $item) {
                        $delete[$key] = unserialize($item);
                    }
                }
            } else if (isset($remove) && is_array($remove) && !empty($remove)) {
                // There is no add and there are some to remove, so they're delete not update
                foreach($remove as $key => $item) {
                    $delete[$key] = unserialize($item);
                }
            }
            $return = array(
                "insert" => $insert,
                "update" => $update,
                "delete" => $delete
            );
        }
        return $return;
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