<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/configuration", "title" => "Manage Configuration");

	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);

		/**
		 * Add the Regional Education module secondary navigation.
		 */
		$sidebar_html  = "<ul class=\"menu\">";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/configuration/settings/\">Manage Entrada Settings</a></li>\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/configuration/organisations/\">Manage Organisations</a></li>\n";
		$sidebar_html .= "</ul>";
		new_sidebar_item("Configuration", $sidebar_html, "config-nav", "open");

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