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

 
class MetaDataType {
	
	private	$meta_type_id,
			$label,
			$description,
			$parent_type_id;
	
	/**
	 * Returns the parent type, if any. Returns null if none is found.
	 * @return MetaDataType
	 */
	public function getParent() {
		if ($this->parent_type_id) {
			return MetaDataType::get($this->parent_type_id);
		}
	}
	
	public function getID() {
		return $this->meta_type_id;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getDescription() {
		return $this->description;
	}

	function __construct() {
	}
	
	/**
	 * 
	 * @param $arr 
	 * @param $type If using a pre-existing object, this parameter will be the mutated object. If null, or not supplied, a new MetaDataType will be returned
	 * @return MetaDataType
	 */
	public static function fromArray(array $arr, MetaDataType $type = null) {
		$cache = SimpleCache::getCache();
		if (is_null($type)) {
			$user = $cache->get("MetaDataType",$arr['meta_type_id']); //re-use a cached copy if we can. helps prevent inconsistent objects 
			if (!$type) {
				$type = new self();
			}
		}
		
		$type->meta_type_id = $arr['meta_type_id'];
		$type->label = $arr['label'];
		$type->description = $arr['description'];
		$type->parent_type_id = $arr['parent_type_id'];
		
		$cache->set($type, "MetaDataType",$arr['meta_type_id']);
		
		return $type;
	}
	
	public static function get($meta_type_id) {
		$cache = SimpleCache::getCache();
		$type = $cache->get("MetaDataType",$meta_type_id);
		if (!$type) {
			global $db;
			$query = "SELECT * FROM `meta_types` WHERE `meta_type_id` = ?";
			$result = $db->getRow($query, array($meta_type_id));
			if ($result) {
				$type = self::fromArray($result);
			}		
		}
		return $type;
	}

	function __toString() {
		return $this->getLabel();
	}
	
}