<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 Queen's University. All Rights Reserved.
 *
*/
@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if ((isset($_SESSION["isAuthorized"])) && ((bool) $_SESSION["isAuthorized"])) {
	if (isset($_REQUEST["id"]) && ((int)$_REQUEST["id"])) {
		$PROXY_ID = clean_input($_REQUEST["id"], array("int"));
	} else {
		$PROXY_ID = 0;
	}
	$query = "	SELECT DISTINCT(b.`rotation_id`), c.`rotation_title` FROM
				`".CLERKSHIP_DATABASE."`.`event_contacts` AS a
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`events` AS b
				ON a.`event_id` = b.`event_id`
				LEFT JOIN `".CLERKSHIP_DATABASE."`.`global_lu_rotations` AS c
				ON b.`rotation_id` = c.`rotation_id`
				WHERE a.`etype_id` = ".$db->qstr($PROXY_ID)."
				AND a.`econtact_type` = 'student'
				AND b.`event_start` < ".$db->qstr(time());
	$rotations = $db->GetAll($query);
	?>
	<div style="clear: both"></div>
	<?php 
	$summary_shown = false;
	if ($rotations) {
		?>
		<form action="<?php echo WEBSITE_URL ?>/admin/clerkship/flag" method="post">
			<table class="tableList" cellspacing="0" summary="Clerkship Progress Summary">
				<colgroup>
					<col class="modified" />
					<col class="region" />
					<col class="date" />
					<col class="date" />
					<col class="date" />
					<col class="date" />
				</colgroup>
				<thead>
					<tr>
						<td class="modified">&nbsp;</td>
						<td class="region-large">Rotation</td>
						<td class="date-smallest">Objectives Logged</td>
						<td class="date-smallest">Objectives Required</td>
						<td class="date-smallest">Procedures Logged</td>
						<td class="date-smallest">Procedures Required</td>
					</tr>
				</thead>
				<tbody>									
				<?php
				foreach ($rotations as $rotation) {
					if ($rotation["rotation_id"]) {
						$procedures_required = 0;
					    $objectives_required = 0;
					    $objectives_recorded = 0;
					    $procedures_recorded = 0;
					    
						$query = "	SELECT `objective_id`, MAX(`number_required`) AS `required`
									FROM `".CLERKSHIP_DATABASE."`.`logbook_mandatory_objectives`
									WHERE `rotation_id` = ".$db->qstr($rotation["rotation_id"])."
									GROUP BY `objective_id`";
						$required_objectives = $db->GetAll($query);
						if ($required_objectives) {
							foreach ($required_objectives as $required_objective) {
								$objectives_required += $required_objective["required"];
								$query = "	SELECT COUNT(`objective_id`) AS `recorded`
											FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_objectives`
											WHERE `lentry_id` IN
											(
												SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
												WHERE `entry_active` = '1' 
												AND `proxy_id` = ".$db->qstr($PROXY_ID)."
											)
											AND `objective_id` = ".$db->qstr($required_objective["objective_id"])."
											GROUP BY `objective_id`";
								$recorded = $db->GetOne($query);
								
								if ($recorded) {
									$objectives_recorded += ($recorded <= $required_objective["required"] ? $recorded : $required_objective["required"]);
								}
							}
						}
						$query = "	SELECT `lprocedure_id`, MAX(`number_required`) AS `required`
									FROM `".CLERKSHIP_DATABASE."`.`logbook_preferred_procedures`
									WHERE `rotation_id` = ".$db->qstr($rotation["rotation_id"])."
									GROUP BY `lprocedure_id`";
						$required_procedures = $db->GetAll($query);
						if ($required_procedures) {
							foreach ($required_procedures as $required_procedure) {
								$procedures_required += $required_procedure["required"];
								$query = "SELECT COUNT(`lprocedure_id`) AS `recorded`
										FROM `".CLERKSHIP_DATABASE."`.`logbook_entry_procedures`
										WHERE `lentry_id` IN
										(
											SELECT `lentry_id` FROM `".CLERKSHIP_DATABASE."`.`logbook_entries`
											WHERE `entry_active` = '1' 
											AND `proxy_id` = ".$db->qstr($PROXY_ID)."
										)
										AND `lprocedure_id` = ".$db->qstr($required_procedure["lprocedure_id"])."
										GROUP BY `lprocedure_id`";
								$recorded = $db->GetOne($query);
								
								if ($recorded) {
									$procedures_recorded += ($recorded <= $required_procedure["required"] ? $recorded : $required_procedure["required"]);
								}
							}
						}
						$url = WEBSITE_URL."/clerkship/logbook?section=view&type=missing&core=".$rotation["rotation_id"]."&id=".$PROXY_ID;
						$summary_shown = true;
						?>
						<tr class="entry-log">
							<td class="modified">&nbsp;</td>
							<td class="region-large"><a href="<?php echo $url."\">".$rotation["rotation_title"]; ?></a></td>
							<td class="date-smallest"><a href="<?php echo $url."\">".$objectives_recorded; ?></a></td>
							<td class="date-smallest"><a href="<?php echo $url."\">".$objectives_required; ?></a></td>
							<td class="date-smallest"><a href="<?php echo $url."\">".$procedures_recorded; ?></a></td>
							<td class="date-smallest"><a href="<?php echo $url."\">".$procedures_required; ?></a></td>
						</tr>
						<?php
					}
				}
				?>		
				</tbody>
			</table>
		</form>
		<?php
	}
	if (!$summary_shown) {
		$NOTICE++;
		$NOTICESTR[] = $student_name . " has not begun any core rotations in the system at this time.";
		echo display_notice();
	}
}