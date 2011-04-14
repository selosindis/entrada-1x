<?php

/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves the categories list up in a select box.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if (!defined("IN_MTDTRACKING")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: " . ENTRADA_URL);
	exit;
} else {
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
	require_once("functions.inc.php");

	/**
	 * Clears all open buffers so we can return a plain response for the Javascript.
	 */
	ob_clear_open_buffers();
	
	date_default_timezone_set(DEFAULT_TIMEZONE);

	session_start();

	$proxy_id = $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"];
	$PROCESSED["service_id"] = validate_integer_field($_GET["service_id"]);

	if (isset($_POST["sortname"]) && $_POST["sortname"] != '') {
		$sort = $_POST["sortname"];
	} else {
		$sort = 'start_date';
	}

	if (isset($_POST["sortorder"]) && $_POST["sortorder"] != '') {
		$dir = $_POST["sortorder"];
	} else {
		$dir = 'DESC';
	}

	if (isset($_POST["rp"]) && $_POST["rp"] != '') {
		$limit = $_POST["rp"];
	} else {
		$limit = '10';
	}

	if (isset($_POST["page"]) && $_POST["page"] != '') {
		$page = $_POST['page'];
		if ($page == 1) {
			$start = '0';
		} else {
			$start = ((int) $page * (int) $limit) - (int) $limit;
		}
	} else {
		$page = '1';
		$start = '0';
	}

	if (isset($_POST["query"]) && $_POST["query"] != '') {
		$where = " AND " . $_POST["qtype"] . " LIKE '%" . $_POST["query"] . "%'";
	} else {
		$where = "";
	}

	$query = "SELECT `mtd_schedule`.`id`, `mtd_facilities`.`facility_name`, `user_data_resident`.`first_name`,
				 `user_data_resident`.`last_name`, `mtd_schedule`.`start_date`, `mtd_schedule`.`end_date`,
				 `mtd_locale_duration`.`percent_time`
		  FROM  `" . DATABASE_NAME . "`.`mtd_schedule`,
				`" . DATABASE_NAME . "`.`mtd_facilities`,
				`" . AUTH_DATABASE . "`.`user_data_resident`,
				`" . DATABASE_NAME . "`.`mtd_locale_duration`
	      WHERE `mtd_schedule`.`id` = `mtd_locale_duration`.`schedule_id`
		  AND `mtd_facilities`.`id` = `mtd_locale_duration`.`location_id`
		  AND `mtd_schedule`.`service_id` = '" . $PROCESSED["service_id"] . "'
		  AND `mtd_schedule`.`resident_id` = `user_data_resident`.`proxy_id`" . $where . "
		  ORDER BY " . $sort . " " . $dir . "
		  LIMIT " . $start . " , " . $limit;

	$results = $db->GetAll($query);

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header("Content-type: text/json");

	if ($results) {
		$total = count($results);
		$data['page'] = $page;
		$data['total'] = $total;
		foreach ($results as $row) {

			$rows[] = array(
				"id" => $row["id"],
				"cell" => array($row["facility_name"]
					, $row["last_name"]
					, $row["first_name"]
					, $row["start_date"]
					, $row["end_date"]
					, $row["percent_time"]
				)
			);
		}
		$data['rows'] = $rows;
		$data['params'] = $_POST;

		echo json_encode($data);
	} else {
		$data['page'] = 1;
		$data['total'] = 0;
		$rows[] = array();
		$data['rows'] = $rows;
		echo json_encode($data);
	}
}
exit();
?>