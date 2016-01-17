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
 * A model for handling External Link Event Resources
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 */

class Models_Event_Resource_Link extends Models_Base {
    protected $elink_id,
            $event_id,
            $required,
            $timeframe,
            $proxify,
            $link,
            $link_title,
            $link_notes,
            $accesses,
            $release_date,
            $release_until,
            $updated_date,
            $updated_by;
    
    protected $table_name = "event_links";
    protected $default_sort_column = "elink_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public function getID() {
        return $this->elink_id;
    }
    
    public function getEventID() {
        return $this->event_id;
    }
    
    public function getRequired() {
        return $this->required;
    }
    
    public function getTimeframe() {
        return $this->timeframe;
    }
    
    public function getProxify() {
        return $this->proxify;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function getLinkTitle() {
        return $this->link_title;
    }
    
    public function getLinkNotes() {
        return $this->link_notes;
    }
    
    public function getAccesses() {
        return $this->accesses;
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
    
    public function getActive() {
        return $this->active;
    }
    
    public static function fetchRowByID ($id = null) {
        $self = new self();
        
        $contraints = array (
            array(
                "key" => "elink_id",
                "value" => $id,
                "method" => "="
            ),
        );
        
        return $self->fetchRow($contraints);
    }
    
    public function getViewed() {
        global $db;
        global $ENTRADA_USER;
        
        $query	= "	SELECT `statistic_id`, `proxy_id`, `module`, `action`, `action_field`, `action_value`, `prune_after`, MAX(`timestamp`) AS `timestamp`
                    FROM `statistics`
                    WHERE `proxy_id` = ?
                    AND `action` = 'link_access'
                    AND `action_field` = 'link_id'
                    AND `action_value` = ?";
        
        $result = $db->GetRow($query, array($ENTRADA_USER->getActiveId(), $this->elink_id));
        if ($result) {
            return new Models_Statistic($result);
        } else {
            return false;
        }
    }
    
    public function insert() {
        global $db;
        
        if ($db->AutoExecute($this->table_name, $this->toArray(), "INSERT")) {
            $this->elink_id = $db->Insert_ID();
            return $this;
        } else {
            return false;
        }
    }
    
    public function update() {
        global $db;
        
        if ($db->AutoExecute($this->table_name, $this->toArray(), "UPDATE", "`elink_id` = ".$db->qstr($this->elink_id))) {
            return $this;
        } else {
            return false;
        }
    }
    
    public function delete() {
        global $db;
        
        $query = "DELETE FROM `".$this->table_name."` WHERE `elink_id` = ?";
        if ($db->Execute($query, $this->elink_id)) {
            return true;
        } else {
            return false;
        }
    }
}