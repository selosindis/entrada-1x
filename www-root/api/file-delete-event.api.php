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
 * Returns a list of checkboxes for recurring events that have at least one
 * file synced with a file in the $_GET['efile_ids'] array.
 * 
 * @author Organisation: David Geffen School of Medicine at UCLA
 * @author Unit: Instructional Design and Technology Unit
 * @author Developer: Robert Fotino <robert.fotino@gmail.com>
 * @copyright Copyright 2014 Regents of the University of California. All Rights Reserved.
 * 
*/

set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

if (!isset($_SESSION['isAuthorized']) || !$_SESSION['isAuthorized']) {
    exit;
}

ob_start("on_checkout");

if (isset($_GET['efile_ids'])) {
    $EFILE_IDS = array_map(intval, explode(',', $_GET['efile_ids']));
} else {
    exit;
}

$event_query = "
    SELECT a.* FROM `events` AS a
    JOIN `event_files` AS b
    ON b.`event_id`=a.`event_id`
    AND b.`efile_id` IN (".implode(',', array_map($db->qstr, $EFILE_IDS)).")
    GROUP BY a.`event_id`
    LIMIT 1";
$event_info = $db->GetRow($event_query);
if ($event_info) {
    $EVENT_ID = (int)$event_info['event_id'];
}

$recurring_events_query = "
    SELECT * FROM `events`
    WHERE `recurring_id` = (
        SELECT `recurring_id`
        FROM `events`
        WHERE `event_id` = ".$db->qstr($EVENT_ID)."
    )
    AND `event_id` != ".$db->qstr($EVENT_ID);
$all_recurring_events = $db->GetAll($recurring_events_query);
if ($all_recurring_events) {
    $recurring_events = array();
    foreach ($all_recurring_events as $recurring_event) {
        foreach ($EFILE_IDS as $efile_id) {
            $file_info_query = "SELECT * FROM `event_files` WHERE `event_id`=".$db->qstr($efile_id);
            $file_info = $db->GetRow($file_info_query);
            if (!$file_info) {
                continue;
            }
            $synced_file_query = "
                SELECT `efile_id` FROM `event_files`
                WHERE `event_id` = ".$db->qstr($recurring_event['event_id'])."
                AND `file_name` = ".$db->qstr($file_info['file_name'])."
                AND `updated_date` = ".$db->qstr($file_info['updated_date']);
            if ($db->GetOne($synced_file_query)) {
                $recurring_events[] = $recurring_event;
                break;
            }
        }
    }
}

if (!$recurring_events) {
    exit;
}
?>

<div class="alert alert-block alert-info">
    Please select the following related recurring events from which you would like to remove the selected files:
</div>
<?php foreach ($recurring_events as $recurring_event) { ?>
<div class="row-fluid">
    <span class="span1">
        &nbsp;
    </span>
    <span class="span1">
        <input type="checkbox" id="recurring_event_<?php echo $recurring_event["event_id"] ?>" name="recurring_event_ids[]" value="<?php echo $recurring_event["event_id"]; ?>" checked="checked" />
    </span>
    <label class="span10" for="recurring_event_<?php echo $recurring_event["event_id"] ?>">
        <strong class="space-right">
            <?php echo html_encode($recurring_event["event_title"]); ?>
        </strong>
        [<span class="content-small"><?php echo html_encode(date(DEFAULT_DATE_FORMAT, $recurring_event["event_start"])); ?></span>]
    </label>
</div>
<?php }
