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
 * This file is used to author and share quizzes with other folks who have
 * administrative permissions in the system.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
 * @version $Id: add.inc.php 317 2009-01-19 19:26:35Z simpson $
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_QUIZZES"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('quiz', 'create', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/".$MODULE."?".replace_query(array("section" => "add")), "title" => "Adding Quiz");
	
	$PROCESSED["associated_proxy_ids"]	= array();
	
	echo "<h1>Adding Quiz</h1>\n";

	// Error Checking
	switch ($STEP) {
		case 2 :
			/**
			 * Required field "quiz_title" / Quiz Title.
			 */
			if ((isset($_POST["quiz_title"])) && ($tmp_input = clean_input($_POST["quiz_title"], array("notags", "trim")))) {
				$PROCESSED["quiz_title"] = $tmp_input;
			} else {
				$ERROR++;
				$ERRORSTR[] = "The <strong>Quiz Title</strong> field is required.";
			}

			/**
			 * Non-Required field "quiz_description" / Quiz Description.
			 */
			if ((isset($_POST["quiz_description"])) && ($tmp_input = clean_input($_POST["quiz_description"], array("trim", "allowedtags")))) {
				$PROCESSED["quiz_description"] = $tmp_input;
			} else {
				$PROCESSED["quiz_description"] = "";
			}

			/**
			 * Required field "associated_proxy_ids" / Quiz Authors (array of proxy ids).
			 * This is actually accomplished after the quiz is inserted below.
			 */
			if((isset($_POST["associated_proxy_ids"]))) {
				$associated_proxy_ids = explode(',',$_POST["associated_proxy_ids"]);
				foreach($associated_proxy_ids as $contact_order => $proxy_id) {
					if($proxy_id = clean_input($proxy_id, array("trim", "int"))) {
						$PROCESSED["associated_proxy_ids"][(int) $contact_order] = $proxy_id;
					}
				}
			}
			
			/**
			 * The current quiz author must be in the quiz author list.
			 */
			if (!in_array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
				array_unshift($PROCESSED["associated_proxy_ids"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
			}

			if (!$ERROR) {
				$PROCESSED["updated_date"]	= time();
				$PROCESSED["updated_by"]	= $_SESSION["details"]["id"];

				if ($db->AutoExecute("quizzes", $PROCESSED, "INSERT")) {
					if ($quiz_id = $db->Insert_Id()) {
						
						/**
						 * Add the quiz authors to the quiz_contacts table.
						 */
						if ((is_array($PROCESSED["associated_proxy_ids"])) && (count($PROCESSED["associated_proxy_ids"]))) {						
							foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
								if (!$db->AutoExecute("quiz_contacts", array("quiz_id" => $quiz_id, "proxy_id" => $proxy_id, "updated_date" => time(), "updated_by" => $_SESSION["details"]["id"]), "INSERT")) {
									$ERROR++;
									$ERRORSTR[] = "There was an error while trying to attach a <strong>Quiz Author</strong> to this quiz.<br /><br />The system administrator was informed of this error; please try again later.";

									application_log("error", "Unable to insert a new quiz_contact record while adding a new quiz. Database said: ".$db->ErrorMsg());
								}
							}
						}

						application_log("success", "New quiz [".$quiz_id."] added to the system.");

						header("Location: ".ENTRADA_URL."/admin/".$MODULE."?section=add-question&id=".$quiz_id);
						exit;
					} else {
						$ERROR++;
						$ERRORSTR[] = "There was a problem inserting this quiz into the system. The system administrator was informed of this error; please try again later.";

						application_log("error", "There was an error inserting a quiz, as there was no insert ID. Database said: ".$db->ErrorMsg());
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "There was a problem inserting this quiz into the system. The system administrator was informed of this error; please try again later.";

					application_log("error", "There was an error inserting a quiz. Database said: ".$db->ErrorMsg());
				}
			}
			
			if ($ERROR) {
				$STEP = 1;
			}
		break;
		case 1 :
		default :
			continue;
		break;
	}

	// Display Content
	switch ($STEP) {
		case 2 :
			if ($SUCCESS) {
				echo display_success();
			}
			if ($NOTICE) {
				echo display_notice();
			}
			if ($ERROR) {
				echo display_error();
			}
		break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";
			
			/**
			 * Load the rich text editor.
			 */
			load_rte();

			/**
			 * Compiles the full list of people who are able to access this
			 * module based on the $ADMINISTRATION array in settings.inc.php.
			 */
			$author_list_where	= array();
			$groups_roles		= permissions_by_module($MODULE);
			foreach ($groups_roles as $group => $roles) {
				foreach ($roles as $role) {
					$author_list_where[] = "(b.`group` = ".$db->qstr($group)." AND b.`role` = ".$db->qstr($role).")";
				}
			}
			
			$author_list	= array();
			$query			= "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`, a.`organisation_id`, b.`group`, b.`role`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON b.`user_id` = a.`id`
								WHERE b.`app_id` = '".AUTH_APP_ID."'
								AND (".implode(" OR ", $author_list_where).")
								ORDER BY a.`lastname` ASC, a.`firstname` ASC";
			$results		= $db->GetAll($query);
			if ($results) {
				foreach ($results as $result) {
					$author_list[] = array('proxy_id'=>$result["proxy_id"], 'fullname'=>$result["fullname"]." (".ucwords($result["group"])." > ".ucwords($result["role"]).")", 'organisation_id'=>$result['organisation_id']);
				}
			}

			if ($ERROR) {
				echo display_error();
			}
			?>
			<form action="<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>?section=add&amp;step=2" method="post" id="addQuizForm">
			<table style="width: 100%" cellspacing="0" cellpadding="2" border="0" summary="Adding Quiz">
			<colgroup>
				<col style="width: 3%" />
				<col style="width: 20%" />
				<col style="width: 77%" />
			</colgroup>
			<tfoot>
				<tr>
					<td colspan="3" style="padding-top: 50px">
						<table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td style="width: 25%; text-align: left">
								<input type="button" class="button" value="Cancel" onclick="window.location='<?php echo ENTRADA_URL; ?>/admin/<?php echo $MODULE; ?>'" />
							</td>
							<td style="width: 75%; text-align: right; vertical-align: middle">
								<input type="submit" class="button" value="Proceed" />
							</td>
						</tr>
						</table>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td colspan="3"><h2>Quiz Information</h2></td>
				</tr>
				<tr>
					<td></td>
					<td><label for="quiz_title" class="form-required">Quiz Title</label></td>
					<td><input type="text" id="quiz_title" name="quiz_title" value="<?php echo html_encode($PROCESSED["quiz_title"]); ?>" maxlength="64" style="width: 96%" /></td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top">
						<label for="quiz_description" class="form-nrequired">Quiz Description</label>
					</td>
					<td>
						<textarea id="quiz_description" name="quiz_description" style="width: 550px; height: 125px" cols="70" rows="10"><?php echo clean_input($PROCESSED["quiz_description"], array("trim", "allowedtags", "encode")); ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td style="vertical-align: top">
						<label for="associated_proxy_ids" class="form-required">Quiz Authors</label>
						<div class="content-small" style="margin-top: 15px">
							<strong>Tip:</strong> Select any other individuals you would like to give access to assigning or modifying this quiz.
						</div>
					</td>
					<td>
				<div style="position: relative;">
								<?php
								if ((isset($PROCESSED["associated_proxy_ids"])) && (is_array($PROCESSED["associated_proxy_ids"]))) {
									if (!in_array($_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
										array_unshift($PROCESSED["associated_proxy_ids"], $_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]);
									}
								}
								//Fetch list of categories
								$query	= "SELECT `organisation_id`,`organisation_title` FROM `".AUTH_DATABASE."`.`organisations` ORDER BY `organisation_title` ASC";
								$organisation_results	= $db->GetAll($query);
								if($organisation_results) {
									$organisations = array();
									foreach($organisation_results as $result) {
										if($ENTRADA_ACL->amIAllowed('resourceorganisation'.$result["organisation_id"], 'read')) {
											$organisation_categories[$result["organisation_id"]] = array('text' => $result["organisation_title"], 'value' => 'organisation_'.$result["organisation_id"], 'category'=>true);
										}
									}
								}

								//Get the possible teacher filters
								if(isset($author_list) && is_array($author_list) && !empty($author_list)) {
									$authors = $organisation_categories;
									foreach($author_list as $r) {
										if(in_array($r['proxy_id'], $PROCESSED["associated_proxy_ids"])) {
											$checked = 'checked="checked"';
										} else {
											$checked = '';
										}
										if(isset($authors[$r["organisation_id"]])) {
											$authors[$r["organisation_id"]]['options'][] = array('text' => $r['fullname'], 'value' => $r['proxy_id'], 'checked' => $checked);
										}
									}

									echo lp_multiple_select_popup('associated_proxy_ids', $authors, array('title'=>'Select Multiple Authors:', 'width'=> '500px', 'submit_text'=>'Done', 'cancel'=>false, 'submit'=>true));
								}

								?>
				</div>
				<input class="multi-picklist" id="associated_proxy_ids" name="associated_proxy_ids" style="display: none;">
				<div id="associated_proxy_ids_list"></div>
				<input type="button" onclick="$('associated_proxy_ids_options').show();" value="Select Multiple">
				<script type="text/javascript">
					if($('associated_proxy_ids_options')) {
						$('associated_proxy_ids_options').addClassName('multiselect-processed');
						multiselect = new Control.SelectMultiple('associated_proxy_ids','associated_proxy_ids_options',{
							labelSeparator: '; ',
							checkboxSelector: 'table.select_multiple_table tr td input[type=checkbox]',
							nameSelector: 'table.select_multiple_table tr td.select_multiple_name label',
							overflowLength: 70,
							filter: 'associated_proxy_ids_select_filter',
							resize: 'associated_proxy_ids_scroll',
							afterCheck: function(element) {
								var tr = $(element.parentNode.parentNode);
								tr.removeClassName('selected');
								if(element.checked) {
									tr.addClassName('selected');
								}
							},
							updateDiv: function(options, isnew) {
								ul = options.inject(new Element('ul', {'class':'menu'}), function(list, option) {
									list.appendChild(new Element('li', {'class':'community'}).update(option));
									return list;
								});
								$('associated_proxy_ids_list').update(ul);
							}
						});

						$('associated_proxy_ids_close').observe('click',function(event){
							this.container.hide();
							return false;
						}.bindAsEventListener(multiselect));

					}
				</script>
			</td>
				</tr>
			</tbody>
			</table>
			</form>
			<?php
		break;
	}
}