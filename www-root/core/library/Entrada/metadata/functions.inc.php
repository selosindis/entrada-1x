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
		<td class="control"><input type="hidden" name="meta_values[]" value="<?php echo $vid; ?>" /><input type="checkbox" class="delete_btn" id="delete_<?php echo $vid; ?>" name="delete_<?php echo $vid; ?>" value="0" /></td>
		<td><?php if ($descendant_type_sets) { ?>
			<select name="type_edit_<?php echo $vid; ?>">
				<?php 
				foreach ($descendant_type_sets as $type_set){
					$type = end($type_set);
					$selected = $type === $value->getType();
					echo build_option($type->getID(), html_encode(implode(" > ", $type_set)), $selected);
				} 
				?>
			</select>
			<?php } else { ?>
			<input type="hidden" name="type_edit_<?php echo $vid; ?>" value="<?php echo $category->getID(); ?>" />
			<?php } ?>
		</td>
		<td><input type="text" name="value_edit_<?php echo $vid; ?>" value="<?php echo html_encode($value->getValue()); ?>" /></td>
		<td><input type="text" name="notes_edit_<?php echo $vid; ?>" value="<?php echo nl2br(html_encode($value->getNotes())); ?>" /></td>
		<td><input type="text" name="eff_date_edit_<?php echo $vid; ?>" value="<?php echo ($eff_date = $value->getEffectiveDate()) ? date("Y-m-d", $eff_date) : "" ; ?>" /></td>
		<td><input type="text" name="exp_date_edit_<?php echo $vid; ?>" value="<?php echo ($exp_date = $value->getExpiryDate()) ? date("Y-m-d", $exp_date) : "" ; ?>" /></td>
	</tr>
	<?php
	return ob_get_clean();
}
