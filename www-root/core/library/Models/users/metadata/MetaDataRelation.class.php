<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

 
class MetaDataRelation {
	
	const SEPARATOR = ":";
	public static $TYPES = array("organisation","group","role","user");
	
	private $meta_data_relation_id,
			$meta_type_id,
			$entity_type,
			$entity_value;
			
	private $organisation,
			$group,
			$role,
			$user;
			
	public function getOrganisationRestriction() {
		return $this->organisation;
	}
	
	public function getGroupRestriction() {
		return $this->group;
	}
	
	public function getRoleRestriction() {
		return $this->role;
	}
	
	public function getUserRestriction() {
		return $this->user;
	}
	
	public function isRelated(User $user) {
		if ((($this->organisation) && ($user->getOrganisationID() != $this->organisation))
		|| (($this->group) && ($user->getGroup() != $this->group))
		|| (($this->role) && ($user->getRole() != $this->role))
		|| (($this->user) && ($user->getID() != $this->user))) {
			return false;
		}
		return true;
	}
	
	private function parseParts() {
		$type_parts = explode(self::SEPARATOR, $this->entity_type);
		$value_parts = explode(self::SEPARATOR, $this->entity_value);
		if (count($type_parts) !== count($value_parts)) {
			throw new Exception("Invalid meta data relation");
		}
		$parts = array_combine($type_parts, $value_parts);
		foreach (self::$TYPES as $part) {
			$this->{$part} = $parts[$part];
		}
	}
	
	public function get($meta_data_relation_id) {
		$cache = SimpleCache::getCache();
		$relation = $cache->get("MetaDataRelation",$meta_data_relation_id);
		if (!$relation) {
			global $db;
			$query = "SELECT * FROM `meta_data_relations` WHERE `meta_data_relation_id` = ?";
			$result = $db->getRow($query, array($meta_data_relation_id));
			if ($result) {
				$relation = self::fromArray($result);  			
			}		
		} 
		return $relation;
	}
	
	public static function fromArray(array $arr, self $MDR = null) {
		if (is_null($MDR)) {
			$MDR = new self();
		}
		$MDR->meta_data_relation_id = $arr['meta_data_relation_id'];
		$MDR->meta_type_id = $arr['meta_type_id'];
		$MDR->entity_type = $arr['entity_type'];
		$MDR->entity_value = $arr['entity_value'];
		$MDR->parseParts();
		return $MDR;
	}
	
	public function getMetaDataType() {
		return MetaDataType::get($this->meta_type_id);
	}
}