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

 
class MetaDataValue {
	private $meta_value_id,
			$meta_type_id,
			$proxy_id,
			$data_value,
			$notes,
			$effective_date,
			$expiry_date;
	
	function __construct() {
	}
	
	public static function fromArray(array $arr) {
		$value=new self();
		$value->meta_value_id = $arr['meta_value_id'];
		$value->meta_type_id = $arr['meta_type_id'];
		$value->proxy_id = $arr['proxy_id'];
		$value->data_value = $arr['data_value'];
		$value->notes = $arr['value_notes'];
		$value->effective_date = $arr['effective_date'];
		$value->expiry_date = $arr['expiry_date'];
		return $value;
	}
	
	public function getType() {
		return MetaDataType::get($this->meta_type_id);
	}
	
	public function getUser() {
		return User::get($this->proxy_id);
	}
	
	public function getValue() {
		return $this->data_value;
	}
	
	public function getNotes() {
		return $this->notes;
	}
	
	public function getEffectiveDate() {
		return $this->effective_date;
	}
	
	public function getExpiryDate() {
		return $this->expiry_date;
	}
	
	public function getID() {
		return $this->meta_value_id;
	}
	
	public static function get($meta_value_id) {
		$cache = SimpleCache::getCache();
		$value = $cache->get("MetaValue",$meta_value_id);
		if (!$value) {
			global $db;
			$query = "SELECT * FROM `meta_values` WHERE `meta_value_id` = ?";
			$result = $db->getRow($query, array($meta_value_id));
			if ($result) {
				$value = self::fromArray($result);  			
			}		
		} 
		return $value;
	}
	
	public static function create($type_id, $proxy_id) {
		global $db;
		$query = "INSERT INTO `meta_values` (`meta_type_id`, `proxy_id`) value (?,?)";
		$result = $db->Execute($query, array($type_id, $proxy_id));
		if ($result !== false) {
			return $db->Insert_ID('meta_values', 'meta_value_id');
		}
	}
	
	public static function update() {
		
	} 
}