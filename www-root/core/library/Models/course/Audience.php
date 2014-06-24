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
 * A model for handeling a course audience
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Course_Audience extends Models_Base {
    protected $caudience_id,
              $course_id,
              $cperiod_id,
              $audience_type,
              $audience_value,
              $enroll_start,
              $enroll_finish,
              $ldap_sync_date,
              $audience_active;
    
    protected $table_name = "course_audience";
    protected $default_sort_column = "caudience_id";
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }

    public function getID () {
      return $this->caudience_id;
    }
    
    public function getCourseID () {
        return $this->course_id;
    } 
    
    public function getCperiodID () {
        return $this->cperiod_id;
    }
    
    public function getAudienceType () {
        return$this->audience_type;
    }
    
    public function getAudienceValue () {
        return $this->audience_value;
    }
    
    public function getEnrollStart () {
        return $this->enroll_start;
    }
    
    public function getEnrollFinish () {
        return $this->enroll_finish;
    }
    
    public function getAudienceActive () {
        return $this->audience_active;
    }
    
    public function getLdapSyncDate () {
        return $this->ldap_sync_date;
    }
    
    public function setLdapSyncDate ($ldap_sync_date) {
        $this->ldap_sync_date = $ldap_sync_date;
    }
    
    public function fetchAllByCourseIDCperiodID ($course_id = null, $cperiod_id = null, $active = 1) {
        return $this->fetchAll(array("course_id" => $course_id, "cperiod_id" => $cperiod_id, "audience_active" => $active));
    }
    
    public function fetchRowByCourseIDCperiodID ($course_id = null, $cperiod_id = null, $active = 1) {
        return $this->fetchRow(array("course_id" => $course_id, "cperiod_id" => $cperiod_id, "audience_active" => $active));
    }
    
    public function getMember ($search_term = false) {
        return Models_Group_Member::getUser($this->audience_value, $search_term);
    }
    
    public function getMembers ($search_term = false) {
        return Models_Group_Member::getUsersByGroupID($this->audience_value, $search_term);
    }
    
    public function getGroupName() {
        $group = Models_Group::fetchRowByID($this->audience_value);
        if ($group) {
            return $group->getGroupName();
        } else {
            return false;
        }
    }
    
    public function fetchRowByCourseIDAudienceTypeAudienceValue ($course_id = null, $audience_type = null, $audience_value = null, $active = 1) {
        return $this->fetchRow(array("course_id" => $course_id, "audience_type" => $audience_type, "audience_value" => $audience_value, "audience_active" => $active));
    }
    
    public function update() {
		global $db;
		if ($db->AutoExecute("`".$this->table_name."`", $this->toArray(), "UPDATE", "`caudience_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
    
    public function getCurriculumPeriod($cperiod_id = null) {
        return Models_CurriculumPeriod::fetchRowByID($cperiod_id);
    }
}

?>