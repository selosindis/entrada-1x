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

class MetaDataTypes extends Collection {

	//we want to only load these types once.
	private static $has_init = false;
	
	/**
	 * 
	 * @var MetaDataTypes
	 */
	private static $types; 
	
	/**
	 * @return MetaDataTypes
	 */
	public static function getAll() {
		if (self::$has_init) {
			return self::$types;
		}
		global $db;
		
		$query = "SELECT * from `meta_types`";
		
		$results = $db->getAll($query);
		$types = array();
		if ($results) {
			foreach ($results as $result) {
				$type =  MetaDataType::fromArray($result);
				$types[] = $type;
			}
		}
		self::$types = new self($types);
		self::$has_init = true;
	}
	
	/**
	 * @param $organisation
	 * @param $group
	 * @param $role
	 * @param $user
	 * @return MetaDataTypes
	 */
	public static function get($organisation=null, $group=null, $role=null, $user=null) {
		if (!self::$has_init) {
			self::getAll();
		}
		$relations = MetaDataRelations::getRelations($organisation, $group, $role, $user);
		$applicable_types = array();
		foreach ($relations as $relation) {
			$applicable_types[] = $relation->getMetaDataType(); 
		}
		return new self($applicable_types);
	}
}