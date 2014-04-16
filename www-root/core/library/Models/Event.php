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
 * A class to handle events.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <ryan.warner@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */
class Models_Event {
    private $event_id,
            $parent_id,
            $event_children,
            $recurring_id,
            $region_id,
            $course_id,
            $event_phase,
            $event_title,
            $event_description,
            $include_parent_description,
            $event_goals,
            $objectives_release_date,
            $event_message,
            $include_parent_message,
            $event_location,
            $event_start,
            $event_finish,
            $event_duration,
            $release_date,
            $release_until,
            $updated_date,
            $updated_by,
            $draft_id,
            $audience_visible = 1,
            $eventtype_id,
            $course_num;
    
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
    
    public static function fetchRow($event_id) {
        global $db;
        
        $event = false;
        
        $query = "SELECT * FROM `events` WHERE `event_id` = ?";
        $result = $db->GetRow($query, array($event_id));
        if ($result) {
            $event = new self($result);
        }
        
        return $event;
    }
    
    public function getEventID() {
        return $this->event_id;
    }
    
    public function getParentID() {
        return $this->parent_id;
    }
    
    public function getParentEvent() {
        return self::fetchRow($this->parent_id);
    }
    
    public function getEventChildren() {
        return $this->event_children;
    }
    
    public function getRecurringID() {
        return $this->recurring_id;
    }
    
    public function getRegionID() {
        return $this->region_id;
    }
    
    public function getCourseID() {
        return $this->course_id;
    }
    
    public function getCourse() {
        
    }
    
    public function getEventPhase() {
        return $this->event_phase;
    }
    
    public function getEventTitle() {
        return $this->event_title;
    }
    
    public function getEventDescription() {
        return $this->event_description;
    }
    
    public function getIncludeParentDescription() {
        return $this->include_parent_description;
    }

    public function getEventGoals() {
        return $this->event_goals;
    }
    
    public function getObjectivesReleaseDate() {
        return $this->objectives_release_date;
    }
    
    public function getEventMessage() {
        return $this->event_message;
    }
    
    public function getIncludeParentMessage() {
        return $this->include_parent_message;
    }
    
    public function getEventLocation() {
        return $this->event_location;
    }
    
    public function getEventStart() {
        return $this->event_start;
    }
    
    public function getEventFinish() {
        return $this->event_start;
    }
    
    public function getEventDuration() {
        return $this->event_duration;
    }
    
    public function getReleaseDate() {
        return $this->release_date;
    }
    
    public function getReleaseUntil() {
        return $this->release_until;
    }
    
    public function getUpdatedDate() {
        return $this->updated_date;
    }
    
    public function getUpdatedBy() {
        return $this->draft_id;
    }
    
    public function getEventTypeID() {
        return $this->event_type_id;
    }
    
    public function getCourseNum() {
        return $this->course_num;
    }
    
    public function getEventAudience() {
        return Models_Event_Audience::fetchAllByEventID($this->event_id);
    }
    
    public function getAudienceVisible() {
        return $this->audience_visible;
    }
}

?>
