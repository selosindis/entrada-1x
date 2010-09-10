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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
require_once("Models/courses/SimpleCache.class.php");

/**
 * Course class with all information. Methods referring to other classes are not all complete.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@quensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 */
class Course {
	private $course_id,
			$curriculum_type_id,
			$director_id,
			$pcoord_id,
			$evalrep_id,
			$studrep_id,
			$course_name,
			$course_code,
			$course_description,
			$unit_collaborator,
			$unit_communicator,
			$unit_health_advocate,
			$unit_manager,
			$unit_professional,
			$unit_scholar,
			$unit_medical_expert,
			$unit_summative_assessment,
			$unit_formative_assessment,
			$unit_grading,
			$resources_required,
			$resources_optional,
			$course_url,
			$course_message,
			$notifications,
			$organization,
			$active;

	function __construct(	$course_id,
							$curriculum_type_id,
							$director_id,
							$pcoord_id,
							$evalrep_id,
							$studrep_id,
							$course_name,
							$course_code,
							$course_description,
							$unit_collaborator,
							$unit_communicator,
							$unit_health_advocate,
							$unit_manager,
							$unit_professional,
							$unit_scholar,
							$unit_medical_expert,
							$unit_summative_assessment,
							$unit_formative_assessment,
							$unit_grading,
							$resources_required,
							$resources_optional,
							$course_url,
							$course_message,
							$notifications,
							$organization,
							$active
							) {

		$this->course_id = $course_id;
		$this->curriculum_type_id = $curriculum_type_id;
		$this->director_id = $director_id;
		$this->pcoord_id = $pcoord_id;
		$this->evalrep_id = $evalrep_id;
		$this->studrep_id = $studrep_id;
		$this->course_name = $course_name;
		$this->course_code = $course_code;
		$this->course_description = $course_description;
		$this->unit_collaborator = $unit_collaborator;
		$this->unit_communicator = $unit_communicator;
		$this->unit_health_advocate = $unit_health_advocate;
		$this->unit_manager = $unit_manager;
		$this->unit_professional = $unit_professional;
		$this->unit_scholar = $unit_scholar;
		$this->unit_medical_expert = $unit_medical_expert;
		$this->unit_summative_assessment = $unit_summative_assessment;
		$this->unit_formative_assessment = $unit_formative_assessment;
		$this->unit_grading = $unit_grading;
		$this->resources_required = $resources_required;
		$this->resources_optional = $resources_optional;
		$this->course_url = $course_url;
		$this->course_message = $course_message;
		$this->notifications = $notifications;
		$this->organization = $organization;
		$this->active = $active;
		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"Course",$this->$id);
	}
	
	/**
	 * Returns the id of the user
	 * @return int
	 */
	public function getID() {
		return $this->course_id;
	}
	
	public function getDirector(){
		return User::get($this->director_id);
	}
	
	public function getPCoordinator() {
		return User::get($this->pcoord_id);
	}
	
	public function getEvalRep() {
		return User::get($this->evalrep_id);
	}
	
	public function getStudentRep() {
		return User::get($this->studrep_id);
	}
	
	public function getCourseName() {
		return $this->course_name;
	}
	
	public function getCourseCode() {
		return $this->course_code;
	}
	
	public function getDescription() {
		return $this->course_description;
	}
	
	public function getCurriculumType() {
		//TODO add curriculum type	
	}
	
	public function getObjectives() {
		//TODO add objective request after Objectives class
	}
	
	public function getUnitCollaborator() {
		return $this->unit_collaborator;
	}
	
	public function getUnitCommunicator() {
		return $this->unit_communicator;
	}
	
	public function getUnitHealthAdvocate() {
		return $this->unit_health_advocate;
	}
	
	public function getUnitManager() {
		return $this->unit_manager;
	}
	
	public function getUnitProfessional() {
		return $this->unit_professional;
	}
	
	public function getUnitScholar() {
		return $this->unit_scholar;
	}
	
	public function getUnitMedicalExpert() {
		return $this->unit_medical_expert;
	}

	public function getUnitSummativeAssessment() {
		return $this->unit_summative_assessment;
	}
	
	public function getUnitFormativeAsessment() {
		return $this->unit_formative_assessment;
	}
	
	public function getUnitGrading() {
		return $this->unit_grading;
	}
	
	public function getResourcesRequired() {
		return $this->resources_required;
	}
	
	public function getResourcesOptional() {
		return $this->resources_optional;
	}
	
	public function getURL() {
		return $this->courrse_url;
	}
	
	public function getCourseMessage() {
		return $this->course_message;
	}
	
	public function isActive() {
		return $this->active === 1;
	}
	
	public function hasNotifications() {
		return $this->notifications === 1;
	}
	
	public function getOrganization() {
		//TODO return the organization object once the class is created
	}
	
	public static function get($course_id) {
		$cache = SimpleCache::getCache();
		$course = $cache->get("Course",$course_id);
		if (!$user) {
			global $db;
			$query = "SELECT * FROM `courses` WHERE `id` = ".$db->qstr($course_id);
			$result = $db->getRow($query);
			if ($result) {
				$course =  new Course($result['course_id'],$result['curriculum_type_id'],$result['director_id'],$result['pcoord_id'],$result['evalrep_id'],$result['studrep_id'],$result['course_name'],$result['course_code'],$result['course_description'],$result['unit_collaborator'],$result['unit_communicator'],$result['unit_health_advocate'],$result['unit_manager'],$result['unit_professional'],$result['unit_scholar'],$result['unit_medical_expert'],$result['unit_summative_assessment'],$result['unit_formative_assessment'],$result['unit_grading'],$result['resources_required'],$result['resources_optional'],$result['course_url'],$result['course_message'],$result['notifications'],$result['organization'],$result['active']);			
			}		
		} 
		return $course;
	}
}