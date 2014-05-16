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
 * A model for handeling courses
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2013 Queen's University. All Rights Reserved.
 */

class Models_Course extends Models_Base {
    protected $course_id,
            $organisation_id,
            $curriculum_type_id,
            $parent_id,
            $pcoord_id,
            $evalrep_id,
            $studrep_id,
            $course_name,
            $course_code,
            $course_description,
            $course_objectives,
            $unit_collaborator,
            $unit_communicator,
            $unit_health_advocate,
            $unit_manager,
            $unit_scholar,
            $unit_professional,
            $unit_medical_expert,
            $unit_summative_assessment,
            $unit_formative_assessment,
            $unit_grading,
            $resources_required,
            $resources_optional,
            $course_url,
            $course_message,
            $permission,
            $sync_ldap,
            $sync_ldap_courses,
            $notifications,
            $course_active;
    
    protected $table_name = "courses";
    protected $default_sort_column = "course_id";
    
    public function getID () {
        return $this->course_id;
    }
    
    public function getOrganisationID () {
        return $this->organisation_id;
    }
    
    public function getCurriculumTypeID () {
        return $this->curriculum_type_id;
    }
    
    public function getParentID () {
        return $this->parent_id;
    }
    
    public function getPcoordID () {
        return $this->pcoord_id;
    }
    
    public function getEvalrepID () {
        return $this->evalrep_id;
    }
    
    public function getStudrepID () {
        return $this->studrep_id;
    }
    
    public function getCourseName () {
        return $this->course_name;
    }
    
    public function getCourseCode () {
        return $this->course_code;
    }
    
    public function getCourseDescription () {
        return $this->course_description;
    }
    
    public function getCourseObjectives () {
        return $this->course_objectives;
    }
    
    public function getUnitCollaborator () {
        return $this->unit_collaborator;
    }
    
    public function getUnitCommunicator () {
        return $this->unit_communicator;
    }
    
    public function getUnitHealthAdvocate () {
        return $this->unit_health_advocate;
    }
    
    public function getUnitManager () {
        return $this->unit_manager;
    }
    
    public function getUnitScholar () {
        return $this->unit_scholar;
    }
    
    public function getUnitProfessional () {
        return $this->unit_professional;
    }
    
    public function getUnitMedicalExpert () {
        return $this->unit_medical_expert;
    }
    
    public function getUnitSummativeAssessment () {
        return $this->unit_summative_assessment;
    }
    
    public function getUnitFormativeAssessment () {
        return $this->unit_formative_assessment;
    }
    
    public function getUnitGrading () {
        return $this->unit_grading;
    }
    
    public function getResourcesRequired () {
        return $this->resources_required;
    }
    
    public function getResourcesOptional () {
        return $this->resources_optional;
    }
    
    public function getCourseUrl () {
        return $this->course_url;
    }
    
    public function getCourseMessage () {
        return $this->course_message;
    }
    
    public function getPermission () {
        return $this->permission;
    }
    
    public function getSyncLdap () {
        return $this->sync_ldap;
    }
    
    public function getSyncLdapCourses () {
        return $this->sync_ldap_courses;
    }
    
    public function getNotifications () {
        return $this->notifications;
    }
   
    public function getActive () {
        return $this->course_active;
    }
    
    public function __construct($arr = NULL) {
        parent::__construct($arr);
    }
    
    public static function get($course_id) {
        $self = new self();
        return $self->fetchRow(array("course_id" => $course_id, "course_active" => 1));
    }
    
    public function getAudience ($cperiod_id = null) {
        $audience = new Models_Course_Audience();
        return $audience->fetchAllByCourseIDCperiodID($this->getID(), $cperiod_id);
    }
    
    public function getMembers ($cperiod_id = null, $search_term = false) {
        $course_audience = $this->getAudience($cperiod_id);
        $a = false;
        if ($course_audience) {
            foreach ($course_audience as $audience) {
                if ($audience->getAudienceType() == "group_id") {
                    $audience_members = $audience->getMembers($search_term);
                    if ($audience_members) {
                        $a["groups"][$audience->getGroupName()] = $audience_members;
                    } 
                } else if ($audience->getAudienceType() == "proxy_id") {
                    $audience_member = $audience->getMember($search_term);
                    if ($audience_member) {
                        $a["individuals"][] = $audience_member;
                    }
                }
            }
        }
        ksort($a);
        return $a;
    }
    
    public function update() {
		global $db;
		if ($db->AutoExecute("`courses`", $this->toArray(), "UPDATE", "`course_id` = ".$db->qstr($this->getID()))) {
			return true;
		} else {
			return false;
		}
	}
}

?>