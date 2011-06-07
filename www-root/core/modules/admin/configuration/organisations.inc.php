<?php

$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/configuration/organisations", "title" => "Manage Organisations");

	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);
		
		if(isset($_GET["step"])){
			$STEP = $_GET["step"];
		}
		else{
			$STEP = "";
		}
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}

		/**
		 * Check if preferences need to be updated on the server at this point.
		 */
		preferences_update($MODULE, $PREFERENCES);
	} else {
		$url = ENTRADA_URL."/admin/".$MODULE;
		application_log("error", "The Entrada_Router failed to load a request. The user was redirected to [".$url."].");

		header("Location: ".$url);
		exit;
	}