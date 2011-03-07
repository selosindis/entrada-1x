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

class MetaDataRelations extends Collection {
	
	
	public static function getRelations($organisation=null, $group=null, $role=null, $user=null) {
		$cache = SimpleCache::getCache();
		$relation_set = $cache->get("MetaTypeRelation", "$organization-$group-$role-$user");
		if ($relation_set) {
			return $relation_set;
		}
		global $db;
		$conditions = generateMaskConditions($organisation, $group, $role, $user);
		$query = "SELECT * from `meta_type_relations`";
		if ($conditions) {
			$query .= "\n WHERE ".$conditions;
			
		}
		$results = $db->getAll($query);
		$relations = array();
		if ($results) {
			foreach ($results as $result) {
				$relation =  MetaDataRelation::fromArray($result);
				$relations[] = $relation;
			}
		}
		$relation_set = new self($relations);
		$cache->set($relation_set,"MetaTypeRelation", "$organization-$group-$role-$user" );
		return $relation_set;
	}
	
}