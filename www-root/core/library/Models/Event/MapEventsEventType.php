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
 * A model for handling mapped event types
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Event_MapEventsEventType extends Models_Base {
    protected $map_events_eventtypes_id,
              $fk_instructional_method_id,
              $fk_eventtype_id,
              $updated_date,
              $updated_by;
    
    protected $table_name = "map_events_eventtypes";
    protected $default_sort_column = "map_events_eventtypes_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getID () {
        return $this->map_events_eventtypes_id;
    }
    
    public function getInstructionalMethodID () {
        return $this->fk_instructional_method_id;
    }
    
    public function getEventTypeID () {
        return $this->fk_eventtype_id;
    }
    
    public function getUpdatedDate () {
        return $this->updated_date;
    }
    
    public function getUpdatedBy () {
        return $this->updated_by;
    }
    
    public static function fetchAllByInstructionalMethodID ($instructional_method_id = null) {
        $self = new self();
        return $self->fetchAll(array(
            array("key" => "fk_instructional_method_id", "value" => $instructional_method_id, "method" => "=")
        ));
    }
    
    public function update () {
        return false;
    }
    
    public function insert () {
        return false;
    }
    
    public function delete () {
        return false;
    }
}
