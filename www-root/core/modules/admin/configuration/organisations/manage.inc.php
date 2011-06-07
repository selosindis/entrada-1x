<?php


	if (($router) && ($router->initRoute())) {
		$PREFERENCES = preferences_load($MODULE);
		
		
		if(isset($_GET["org_id"])){
			$ORGANISATION_ID = $_GET["org_id"];
		}else if(isset($_GET["id"])){
			$ORGANISATION_ID = $_GET["id"];
		}
		
		
		if($ORGANISATION_ID){

		$query = "SELECT * FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$db->qstr($ORGANISATION_ID);

		$ORGANISATION = $db->GetRow($query);
		$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/configuration/organisations/manage?id=".$ORGANISATION['organisation_id'], "title" => $ORGANISATION["organisation_title"]);
		
		$sidebar_html  = "<ul class=\"menu\">";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/objectives?id=".$ORGANISATION_ID."\">Manage Objectives</a></li>\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/eventtypes?id=".$ORGANISATION_ID."\">Manage Eventtypes</a></li>\n";
		$sidebar_html .= "	<li class=\"link\"><a href=\"".ENTRADA_URL."/admin/configuration/organisations/manage/hottopics?id=".$ORGANISATION_ID."\">Manage Hot Topics</a></li>\n";
		$sidebar_html .= "</ul>";
		new_sidebar_item("Organisation Management", $sidebar_html, "config-org-nav", "open");
		
		
		$module_file = $router->getRoute();
		if ($module_file) {
			require_once($module_file);
		}

		}
		else{
			$url = ENTRADA_URL."/admin/configuration/organisations/";
			$ERROR++;
			$ERRORSTR[] = "No organisation was selected. Please select an organisation and try again. In five seconds you will now be returned to the organisation screen, or, click <a href = \"".$url."\">here</a> to continue.";
			echo display_error();
			$ONLOAD[]	= "setTimeout('window.location=\\'".$url."\\'', 5000)";
			
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