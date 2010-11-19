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
 * The default file that is loaded when /admin/users is accessed.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

require_once("Models/utility/SimpleCache.class.php");
require_once("InternalAwardReceipts.class.php");
require_once("Award.class.php");
	
class InternalAward extends Award{
	private $id;
	private $disabled;

	function __construct($id, $title, $terms, $disabled) {
		$awarding_body = INTERNAL_AWARD_AWARDING_BODY;
		parent::__construct($title,$terms, $awarding_body);
		$this->id = $id;
		$this->disabled = $disabled;
	}
	
	static function create($title,$terms) {
		global $db;
		
		$query = "insert into `student_awards_internal_types` (`title`,`award_terms`) value (".$db->qstr($title).", ".$db->qstr($terms).")";
		if(!$db->Execute($query)) {
			add_error("Failed to create new award.");
			application_log("error", "Unable to update a student_awards_internal_types record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully added new award.");
			$insert_id = $db->Insert_ID();
			return self::get($insert_id); 
		}
	}
	
	function getRecipients() {
		return InternalAwardReceipts::get($this);
	}
	
	static function get($award_id) {
		global $db;
		$query		= "SELECT *, `id` as award_id FROM `student_awards_internal_types` where `id`=".$db->qstr($award_id);
		$result	= $db->GetRow($query);
		if ($result) {
			$award = self::fromArray($result);
			return $award;
		} else {
			add_error("Error Retrieving Internal Award");
			application_log("error", "Unable to retrieve a student_awards_internal_types record. Database said: ".$db->ErrorMsg());
		}
	}
	
	public static function fromArray(array $arr) {
		return new self($arr['award_id'], $arr['title'], $arr['award_terms'], $arr['disabled']);
	}
	
	public function isDisabled() {
		return (bool)($this->disabled);	
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function update($title,$terms) {
		global $db;
		$query = "update `student_awards_internal_types` set
				 `title`=".$db->qstr($title).", 
				 `award_terms`=".$db->qstr($terms)." 
				 where `id`=".$db->qstr($this->id);
		
		if(!$db->Execute($query)) {
			add_error("Failed to update award.");
			application_log("error", "Unable to update a student_awards_internal_types record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully updated award.");
			$this->title = $title;
			$this->terms = $terms;
		}
	}
	
	public function disable() {
		global $db;
		$query = "Update `student_awards_internal_types` set `disabled`=1 where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to disable award.");
			application_log("error", "Unable to update a student_awards_internal_types record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully disabled award.");
			$this->disabled = 1;
		}
		
	}
	
	public function enable() {
		global $db;
		$query = "Update `student_awards_internal_types` set `disabled`=0 where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to enable award.");
			application_log("error", "Unable to update a student_awards_internal_types record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully enabled award.");
			$this->disabled = 0;
		}
	}
	
	public function delete() {
		global $db;
	
		//first get the list of recipients and make sure we're not going to break things deleting this.
		
		$recipients = $this->getRecipients();
		
		if (is_array($recipients) && count($recipients) > 0) {
			add_error("Failed to remove award from database. Please unassign all recipients of this award prior to deleting it.");
			return;
		}
		
		$query = "DELETE FROM `student_awards_internal_types` where `id`=".$db->qstr($this->id);
		if(!$db->Execute($query)) {
			add_error("Failed to remove award from database.");
			application_log("error", "Unable to delete a student_awards_internal_type record. Database said: ".$db->ErrorMsg());
		} else {
			add_success("Successfully removed award.");
		}		
	}
}