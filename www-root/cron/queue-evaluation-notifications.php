<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Cron job responsible for sending pending notifications.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/
@set_time_limit(0);
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
require_once("Models/evaluation/Evaluation.class.php");

//queue notifications for each user with the evaluations which have opened for them in the last 24 hours.
$query = "SELECT * FROM `evaluations` AS a
			JOIN `evaluation_forms` AS b
			ON a.`eform_id` = b.`eform_id`
			JOIN `evaluations_lu_targets` AS c
			ON b.`target_id` = c.`target_id`
			WHERE a.`evaluation_start` <= ".$db->qstr(time() - 86400)."
			AND a.`evaluation_finish` >= ".$db->qstr((time() - (ONE_WEEK * 10)))."
			AND a.`evaluation_finish` <= ".$db->qstr(time());
$ended_evaluations = $db->GetAll($query);

if ($ended_evaluations) {
	foreach ($ended_evaluations as $evaluation) {
		$overdue_evaluations[$evaluation["evaluation_id"]] = Evaluation::getOverdueEvaluations($evaluation);
	}
}
foreach ($overdue_evaluations as $evaluation_id => $overdue_evaluation_users) {
	foreach ($overdue_evaluation_users as $overdue_evaluation) {
		require_once("Models/notifications/NotificationUser.class.php");
		require_once("Models/notifications/Notification.class.php");
		$proxy_id = $overdue_evaluation["user"]["id"];
		$notification_user = NotificationUser::get($proxy_id, "evaluation", $evaluation_id, $proxy_id);
		if (!$notification_user) {
			$notification_user = NotificationUser::add($proxy_id, "evaluation", $evaluation_id, $proxy_id);
		}
		Notification::add($notification_user->getID(), $proxy_id, $evaluation_id);
	}
}

?>