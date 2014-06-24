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
 * A model to handle interaction with the draft events.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Ryan Warner <rw65@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */
class Models_Event_Draft_Event extends Models_Base {
    
    protected $devent_id, $event_id, $draft_id, $parent_id, $event_children, 
              $recurring_id, $region_id, $course_id, $event_phase, $event_title,
              $event_description, $include_parent_description, $event_goals,
              $event_objectives, $objectives_release_date, $event_message, 
              $include_parent_message, $event_location, $event_start, $event_finish,
              $event_duration, $release_date, $release_until, $updated_date, $updated_by;
    
    protected $table_name = "draft_events";
    protected $default_sort_column = "event_start";

    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getDeventID() {
        return $this->devent_id;
    }

    public function getEventID() {
        return $this->event_id;
    }

    public function getDraftID() {
        return $this->draft_id;
    }

    public function getParentID() {
        return $this->parent_id;
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

    public function getEventObjectives() {
        return $this->event_objectives;
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
        return $this->event_finish;
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
        return $this->updated_by;
    }
    
    public static function fetchAllByDraftID($draft_id) {
        $self = new self();

        $constraints = array(
            array(
                "mode"      => "AND",
                "key"       => "draft_id",
                "value"     => $draft_id,
                "method"    => "="
            )
        );
        
        $objs = $self->fetchAll($constraints, "=", "AND", $sort_col, $sort_order);
        $output = array();

        if (!empty($objs)) {
            foreach ($objs as $o) {
                $output[] = $o;
            }
        }

        return $output;
    }
    
    public static function fetchAllByDraftIDByDate($draft_id, $start, $finish = NULL) {
        global $db;
        
        $output = false;
        
        $query = "SELECT * FROM `draft_events` WHERE `draft_id` = ? AND `event_start` >= ? " . (!is_null($finish) ? " AND `event_finish` <= ?" : "") . " ORDER BY `event_start`";
        $results = $db->GetAll($query, array($draft_id, $start, $finish));
        if ($results) {
            $output = array();
            foreach ($results as $result) {
                $output[] = new self($result);
            }
        }
        
        return $output;
    }
    
    public static function fetchRowByID($devent_id = 0) {
        $self = new self();
        return $self->fetchRow(array(
                array("key" => "devent_id", "value" => $devent_id, "method" => "=", "mode" => "AND")
            )
        );
    }


}

?>
