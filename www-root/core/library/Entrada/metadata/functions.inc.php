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

require_once("Models/utility/Collection.class.php"); 
require_once("Models/utility/SimpleCache.class.php"); 

require_once("Models/users/User.class.php"); 
require_once("Models/users/Users.class.php"); 

require_once("Models/users/metadata/MetaDataRelation.class.php");
require_once("Models/users/metadata/MetaDataRelations.class.php");
require_once("Models/users/metadata/MetaDataType.class.php");
require_once("Models/users/metadata/MetaDataTypes.class.php");
require_once("Models/users/metadata/MetaDataValue.class.php");
require_once("Models/users/metadata/MetaDataValues.class.php");

/**
 * @param MetaDataType $type
 * @return array
 */
function getParentArray(MetaDataType $type) {
	$parent = $type->getParent();
	if (is_null($parent)) {
		return array($type);	
	}
	else {
		$arr = getParentArray($parent);
		array_push($arr, $type);
		return $arr;
	} 
}

/**
 * @param MetaDataType $type
 * @return array
 */
function getTopParentType(MetaDataType $type) {
	$chain = getParentArray($type);
	return array_shift($chain);
}

/**
 * returns an array of arrays. each entry in the outer array is an array of steps *down* in the hierarchy from the provided MetaDataType to the relevant types in MetaDataTypes. $types in the hierarchy may include types which are not directly accessible for this user/group/etc 
 * @param MetaDataTypes $source_types
 * @param MetaDataType $type
 * @return array
 */
function getDescendentTypesArray(MetaDataTypes $source_types, MetaDataType $type) {
	//the easiest way to do this is to get the parent arrays, 
	//if the provided type is in the arrays, splice that and anything before out
	$child_types = array();
	foreach ($source_types as $source_type) {
		$parent_array = getParentArray($source_type);
		$pos = array_search($type, $parent_array);
		if ($pos !== false) {
			array_splice($parent_array, 0, $pos+1);
			if ($parent_array) { //the parent might have been the only element and we just removed it.
				$child_types[] = $parent_array;
			}
		}
	}
	return $child_types;
}

/**
 * returns an array of the types in the provided MetaDataTypes collection which refer to the provided MetaDataType as a parent. This is fundamentally different from the descendent types method.
 * @param MetaDataTypes $source_types
 * @param MetaDataType $type
 * @return array
 */
function getChildTypes(MetaDataTypes $source_types, MetaDataType $type) {
	$children = array();
	foreach ($source_types as $source_type) {
		if ($source_type->getParent() === $type) {
			$children[] = $source_type;
		}
	}	
	return $children;
}

function getUniqueDescendantTypeIDs(MetaDataTypes $source_types, MetaDataType $type) {
	$desc_type_sets = getDescendentTypesArray($source_types, $type);
	//var_dump($desc_type_sets); 
	$type_ids = array();
	foreach($desc_type_sets as $desc_type_set) {
		foreach ($desc_type_set as $desc_type) {
			$id = $desc_type->getID();
			if (!in_array($id, $type_ids)) {
				$type_ids[] = $id;
			}
		}
	}
	return $type_ids;
}

function displayMetaDataRow_User(MetaDataValue $value, MetaDataType $category) {
	$chain = getParentArray($value->getType());
	array_shift($chain);//toss the top
	if ($chain) {
		$sub_type = implode(" > ", $chain); 
	} else {
		$sub_type = "N/A";	
	}
	ob_start();
	?>
	<tr class="value_display" id="value_display_<?php echo $value->getID(); ?>">
		<td><?php echo html_encode($sub_type); ?></td>
		<td><?php echo html_encode($value->getValue()); ?></td>
		<td class="flex"><?php echo nl2br(html_encode($value->getNotes)); ?></td>
		<td><?php echo ($eff_date = $value->getEffectiveDate()) ? date("Y-m-d", $eff_date) : "" ; ?></td>
		<td><?php echo ($exp_date = $value->getExpiryDate()) ? date("Y-m-d", $exp_date) : "" ; ?></td>
	</tr>
	<?php
	return ob_get_clean();
}

function editMetaDataRow_User(MetaDataValue $value, MetaDataType $category, array $descendant_type_sets = array()) {
	$vid = $value->getID();
	ob_start();	
	?>
	<tr class="value_edit" id="value_edit_<?php echo $vid; ?>">
		<td class="control"><input type="checkbox" class="delete_btn" id="delete_<?php echo $vid; ?>" name="value[<?php echo $vid; ?>][delete]" value="1" /></td>
		<td><?php if ($descendant_type_sets) { ?>
			<select name="value[<?php echo $vid; ?>][type]">
				<?php 
				foreach ($descendant_type_sets as $type_set){
					$type = end($type_set);
					$selected = $type === $value->getType();
					echo build_option($type->getID(), html_encode(implode(" > ", $type_set)), $selected);
				} 
				?>
			</select>
			<?php } else { ?>
			<input type="hidden" name="value[<?php echo $vid; ?>][type]" value="<?php echo $category->getID(); ?>" />
			<?php } ?>
		</td>
		<td><input type="text" name="value[<?php echo $vid; ?>][value]" value="<?php echo html_encode($value->getValue()); ?>" /></td>
		<td><input type="text" name="value[<?php echo $vid; ?>][notes]" value="<?php echo nl2br(html_encode($value->getNotes())); ?>" /></td>
		<td><input type="text" name="value[<?php echo $vid; ?>][effective_date]" value="<?php echo ($eff_date = $value->getEffectiveDate()) ? date("Y-m-d", $eff_date) : "" ; ?>" /></td>
		<td><input type="text" name="value[<?php echo $vid; ?>][expiry_date]" value="<?php echo ($exp_date = $value->getExpiryDate()) ? date("Y-m-d", $exp_date) : "" ; ?>" /></td>
	</tr>
	<?php
	return ob_get_clean();
}

function getCategories(MetaDataTypes $available_types) {
	$categories = array();
	//For each of the applicable types without a parent (top-level types), create a section to help organize    
	foreach ($available_types as $type) {
		$top_p = getTopParentType($type);
		if (!in_array($top_p, $categories, true)) {
			$categories[] = $top_p;
		}
	}
	return $categories;
}

function getTypes_User(User $user) {
	$org_id = $user->getOrganisationID();
	$group = $user->getGroup();
	$role = $user->getRole();
	$proxy_id = $user->getID();
	
	return MetaDataTypes::get($org_id, $group, $role, $proxy_id);
}

function getUserCategoryValues(User $user, MetaDataType $category) {
	$org_id = $user->getOrganisationID();
	$group = $user->getGroup();
	$role = $user->getRole();
	$proxy_id = $user->getID();
	
	return MetaDataValues::get($org_id, $group, $role,$proxy_id, $category);
}

function editMetaDataTable_User(User $user) {
	$types = getTypes_User($user);
	$categories = getCategories($types);
		
	ob_start();
	?>
	<input type="hidden" name="request" value="update" />
	<table class="DataTable" callpadding="0" cellspacing="0">
	<colgroup>
	<col width="4%" />
	<col width="18%" />
	<col width="15%" />
	<col width="33%" />
	<col width="15%" />
	<col width="15%" />
	</colgroup>
	<thead>
		<tr>
			<th></th>
			<th>Sub-type</th>
			<th>Value</th>
			<th>Notes</th>
			<th>Effective Date</th>
			<th>Expiry Date</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
	<td></td>
	<td colspan="5" class="control">
	<input type="submit" value="Save" id="save_btn" />
	</td>
	</tr>
	</tfoot>
	<?php foreach ($categories as $category) { 
		$values = getUserCategoryValues($user, $category);
		//var_dump($values);
		$descendant_type_sets = getDescendentTypesArray($types, $category); 
		$label = html_encode($category->getLabel());
	?>
	<tbody id="cat_<?php echo $category->getID(); ?>">
		<tr class="cat_head" id="cat_head_<?php echo $category->getID(); ?>">
			<td></td>
			<th colspan="2"><?php echo $label; ?></th>
			<td class="control" colspan="3"><ul class="page-action"><li><a href="#" class="add_btn" id="add_btn_<?php echo $category->getID(); ?>">Add <?php echo $label; ?></a></li></ul></td>
		</tr>
		<?php
			foreach ($values as $value) {
				echo editMetaDataRow_User($value, $category, $descendant_type_sets);
			} ?>
	</tbody>
	<?php } ?>
	</table>	
	<?php
	return ob_get_clean();
}