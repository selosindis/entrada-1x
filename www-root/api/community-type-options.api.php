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
    if (isset($_POST["community_type_id"]) && ((int) $_POST["community_type_id"])) {
        $community_type_id = ((int) $_POST["community_type_id"]);
    }
	if (isset($_POST["category_id"]) && ((int) $_POST["category_id"])) {
        $CATEGORY_ID = ((int) $_POST["category_id"]);
    }
    if (isset($_POST["group"]) && (clean_input($_POST["group"], "module"))) {
        $GROUP = clean_input($_POST["group"], "module");
    }
    if (isset($_POST["page_ids"]) && is_array($_POST["page_ids"])) {
        $page_ids = $_POST["page_ids"];
    } else {
		$page_ids = array();
	}
	?>
	<tr>
		<td style="padding-top:6px;"><?php echo help_create_button("Community Template", "community_template"); ?></td>
		<td style="padding-top:6px;"><label for="community_template" class="form-nrequired">Community Template</label></td>
		<td>
			<div>
				<?php
					$query = "SELECT a.* FROM `community_templates` AS a
								JOIN `community_type_templates` AS b
								ON a.`template_id` = b.`template_id`
								WHERE b.`type_id` = ".$db->qstr($community_type_id)."
								AND b.`type_scope` = 'organisation'"; 
					$results = $db->GetAll($query);
					if ($results) {
					?>
						<ul class="community-themes">
						<?php
						$default_templates = array();
						$groups = array();
						$category = array();
						$default_categories = array();
						$default_groups = array();
						foreach($results as $community_template) {
							$permissions_query = "SELECT * FROM `communities_template_permissions` WHERE `template`=". $db->qstr($community_template["template_name"]);
							$template_permissions = $db->GetAll($permissions_query);
							if ($template_permissions) {
								foreach ($template_permissions as $template_permission) {
									if ($template_permission["permission_type"] == "group") {
										$groups = explode(",",$template_permission["permission_value"]);
									}
									if (($template_permission["permission_type"] == null && $template_permission["permission_value"] == null)) {
										$default_templates[] = $template_permission["template"];
									}
									if (($template_permission["permission_type"] == "category_id" && $template_permission["permission_value"] != null)) {
										$category = explode(",",$template_permission["permission_value"]);
									}
									if (($template_permission["permission_type"] == "category_id" && $template_permission["permission_value"] == null)) {
										$category_permissions_query = " SELECT * FROM `communities_template_permissions` 
																		WHERE `permission_type`= 'group' 
																		AND `template`=". $db->qstr($template_permission["template"]);
										$category_permissions = $db->GetAll($category_permissions_query);
										if($category_permissions) {
											foreach ($category_permissions as $category_permission) {
												$default_categories = explode(",", $category_permission["permission_value"]);
												if (in_array($GROUP, $default_categories)) {
													$default_categories[] = $category_permission["template"];
												}

											}
										}
									}
									?>
									<?php
								}
								if ((in_array($GROUP, $groups) && in_array($CATEGORY_ID, $category)) || (in_array($community_template["template_name"], $default_templates)) || (in_array($community_template["template_name"], $default_categories))) {
								?>
								<li id="<?php echo $community_template["template_name"]."-template"; ?>">
									<div class="template-rdo">
										<input type="radio" id="<?php echo "template_option_".$community_template["template_id"] ?>" name="template_selection" value="<?php echo $community_template["template_id"]; ?>"<?php echo ((($template_selection == 0) && ($community_template["template_id"] == 1) || ($template_selection == $community_template["template_id"])) ? " checked=\"checked\"" : ""); ?> />
									</div>
									<div class="large-view">
										<a href="#" class="<?php echo "large-view-".$community_template["template_id"]; ?>"><img src="<?php echo ENTRADA_URL. "/images/icon-magnify.gif"  ?>" /></a>
									</div>
									<label for="<?php echo "template_option_".$community_template["template_id"]; ?>"><?php echo ucfirst($community_template["template_name"]. " Template"); ?></label>
								</li>
								<?php
								}
							} 
						}
						?>
						</ul>
						<div class="default-large" style="display:none;">
							<img src="<?php echo ENTRADA_URL."/images/template-default-large.gif" ?>" alt="Default Template Screen shot" />
						</div>
						<div class="committee-large" style="display:none;">
							<img src="<?php echo ENTRADA_URL."/images/template-meeting-large.gif" ?>" alt="Committee Template Screen shot" />
						</div> 
						<div class="vp-large" style="display:none;">
							<img src="<?php echo ENTRADA_URL."/images/template-vp-large.gif" ?>" alt="Virtual Patient Template Screen shot" />
						</div> 
						<div class="learningModule-large" style="display:none;">
							<img src="<?php echo ENTRADA_URL."/images/template-education-large.gif" ?>" alt="Learning Template Screen shot" />
						</div>
						<div class="course-large" style="display:none;">
							<img src="<?php echo ENTRADA_URL."/images/template-course-large.gif" ?>" alt="Course Template Screen shot" />
						</div> 
						<?php
						}
						?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" style="padding-top: 20px">
			<h2>Community Pages</h2>
		</td>
	</tr>
	<tr>
		<td style="vertical-align: top"><?php echo help_create_button("Pages to be generated in the community upon creation.", ""); ?></td>
		<td style="vertical-align: top"><span class="form-required">Available Default Pages</span></td>
		<td id="community_type_pages">
			<?php
				echo community_type_pages_inlists($community_type_id, 0, 0, array(), $page_ids);
			?>
		</td>
	</tr>
	<?php
}
