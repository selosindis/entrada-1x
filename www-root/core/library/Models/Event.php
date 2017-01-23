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
 * A model for handling events
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
class Models_Event extends Models_Base {
    protected $event_id,
              $parent_id,
              $event_children,
              $recurring_id,
              $eventtype_id,
              $region_id,
              $course_id,
              $course_num,
              $event_phase,
              $event_title,
              $event_description,
              $include_parent_description,
              $event_goals,
              $event_objectives,
              $objectives_release_date,
              $event_message,
              $include_parent_message,
              $event_location,
              $event_start,
              $event_finish,
              $event_duration,
              $audience_visible,
              $attendance_required,
              $release_date,
              $release_until,
              $event_color,
              $draft_id,
              $updated_date,
              $updated_by;

    protected static $table_name = "events";
    protected static $primary_key = "event_id";
    protected static $default_sort_column = "event_id";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getID() {
        return $this->event_id;
    }
    
    public function getParentID () {
        return $this->parent_id;
    }
    
    public function getEventChildren () {
        return $this->event_children;
    }
    
    public function getRecurringID () {
        return $this->recurring_id;
    }
    
    public function getEventTypeID () {
        return $this->eventtype_id;
    }
    
    public function getRegionID () {
        return $this->region_id;
    }
    
    public function getCourseID () {
        return $this->course_id;
    }
    
    public function getCourseNum () {
        return $this->course_num;
    }
    
    public function getEventPhase () {
        return $this->event_phase;
    }
    
    public function getEventTitle () {
        return $this->event_title;
    }
    
    public function getEventDescription () {
        return $this->event_description;
    }
    
    public function getIncludeParentDescription () {
        return $this->include_parent_description;
    }
    
    public function getEventGoals () {
        return $this->event_goals;
    }
    
    public function getEventObjectives () {
        return $this->event_objectives;
    }
    
    public function getObjectivesReleaseDate () {
        return $this->objectives_release_date;
    }
    
    public function getEventMessage () {
        return $this->event_message;
    }
    
    public function getIncludeParentMessage () {
        return $this->include_parent_message;
    }
    
    public function getEventLocation () {
        return $this->event_location;
    }
    
    public function getEventStart () {
        return $this->event_start;
    }
    
    public function getEventFinish () {
        return $this->event_finish;
    }
    
    public function getEventDuration () {
        return $this->event_duration;
    }
    
    public function getAudienceVisible() {
        return $this->audience_visible;
    }

    public function getAttendanceRequired() {
        return $this->attendance_required;
    }
    
    public function getReleaseDate () {
        return $this->release_date;
    }
    
    public function getReleaseUntil () {
        return $this->release_until;
    }

    public function getColor() {
        return $this->event_color;
    }
    
    public function getDraftID () {
        return $this->draft_id;
    }
    
    public function getUpdatedDate () {
        return $this->updated_date;
    }
    
    public function getUpdatedBy () {
        return $this->updated_by;
    }
    
    public function getOrganisationID() {
        $course = Models_Course::get($this->course_id);
        if ($course) {
            return $course->getOrganisationID();
        } else {
            return false;
        }
    }

    public function setEventId($event_id) {
        $this->event_id = $event_id;
    }

    /* @return bool|Models_Event */
    public static function get($event_id = null) {
        $self = new self();
        return $self->fetchRow(array("event_id" => $event_id));
    }

    /* @return ArrayObject|Models_Event[] */
    public function fetchAllByCourseID($course_id = null) {
        return $this->fetchAll(array("course_id" => $course_id));
    }

    public static function fetchAllByCourseIDEventtypeID($course_id, $eventtype_id) {
        $self = new self();
        return $self->fetchAll(array(
                array("key" => "course_id", "value" => $course_id, "method" => "="),
                array("key" => "eventtype_id", "value" => $eventtype_id, "method" => "=")
            )
        );
    }

    public function fetchAllByCourseIdStartDateFinishDate($course_id = 0, $start_date = 0, $finish_date = 0, $released_only = true) {
        global $db;

        $course_id = (int) $course_id;
        $start_date = (int) $start_date;
        $finish_date = (int) $finish_date;
        $released_only = (bool) $released_only;

        $events = false;

        $query = "SELECT *
                    FROM `events`
                    WHERE `course_id` = ?
                    AND `event_start` >= ?
                    AND `event_finish` <= ?";
        if ($released_only) {
            $query .= " AND (`release_date` = '0' OR `release_date` <= UNIX_TIMESTAMP())
                        AND (`release_until` = '0' OR `release_until` >= UNIX_TIMESTAMP())";
        }
        $query .= " ORDER BY `event_start` ASC";

        $results = $db->GetAll($query, array($course_id, $start_date, $finish_date));
        if ($results) {
            foreach ($results as $result) {
                $event = new self($result);
                $events[] = $event;
            }
        }

        return $events;
    }

    /* @return ArrayObject|Models_Event[] */
    public function fetchAllByCourseIdTitle($course_id = null, $title = null) {
        global $db;
        $events = false;
        
        $query = "  SELECT * FROM `events` 
                    WHERE  `course_id` = ?
                    AND `event_title` LIKE ?
                    AND (`parent_id` = ? OR `parent_id` IS NULL)
                    ORDER BY `event_start` ASC";
        
        $results = $db->GetAll($query, array($course_id, "%".$title."%", "0"));
        
        if ($results) {
            foreach ($results as $result) {
                $event = new self($result);
                $events[] = $event;
            }
        }
        
        return $events;
    }

    /* @return ArrayObject|Models_Event[] */
    public function fetchAllByCourseIdTitleDates($course_id = null, $title = null, $cperiod_start_date = null, $cperiod_finish_date = null) {
        global $db;
        $events = false;
        
        $query = "  SELECT * FROM `events` 
                    WHERE  `course_id` = ?
                    AND `event_title` LIKE ?
                    AND `event_start` >= ?
                    AND `event_finish` <= ?
                    AND (`parent_id` = ? OR `parent_id` IS NULL)
                    ORDER BY `event_start` ASC";
        
        $results = $db->GetAll($query, array($course_id, "%".$title."%", $cperiod_start_date, $cperiod_finish_date, "0"));
        
        if ($results) {
            foreach ($results as $result) {
                $event = new self($result);
                $events[] = $event;
            }
        }
        
        return $events;
    }

    /* @return ArrayObject|Models_Event_Audience[] */
    public function getEventAudience() {
        return Models_Event_Audience::fetchAllByEventID($this->event_id);
    }

    public function getEventType () {
        return Models_EventType::get($this->eventtype_id);
    }
    
    /* @return ArrayObject|Models_Event[] */
    public static function fetchAllRecurringByEventID($event_id = null) {
        global $db;
        $recurring_events_query = "
                    SELECT * FROM `events`
                    WHERE `recurring_id` = (
                        SELECT `recurring_id`
                        FROM `events`
                        WHERE `event_id` = " . $db->qstr($event_id)."
                    )
                    AND `event_id` != " . $db->qstr($event_id);
        $all_recurring_events = $db->GetAll($recurring_events_query);
        if ($all_recurring_events) {
            foreach ($all_recurring_events as $result) {
                $event = new self($result);
                $events[] = $event;
            }
        }
        return $events;
    }

    public static function getRecurringEventIds($event_id = null) {
        $data = array("recurring_events" => false);

        if ($event_id != 0) {
            //get any recurring events as well
            $recurring_events = Models_Event::fetchAllRecurringByEventID($event_id);
            if (isset($recurring_events) && is_array($recurring_events) && !empty($recurring_events)) {
                $PROCESSED["recurring_events"] = $recurring_events;
            }

            if (isset($PROCESSED["recurring_events"])) {
                $R_Events = array();
                foreach ($PROCESSED["recurring_events"] as $events) {
                    if (isset($events) && is_object($events)) {
                        $R_Events[] = $events->getID();
                    }
                }
                $data["recurring_events"] = $PROCESSED["recurring_events"];
                $data["recurring_event_ids"] = $R_Events;
            }
            return $data;
        }
    }
}
