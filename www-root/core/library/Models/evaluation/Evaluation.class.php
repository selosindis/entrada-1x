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
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/

/**
 * Evaluation class with basic information and access to evaluation related info
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@quensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class Evaluation {
	private $id;
	
	/**
	 * Returns an Evaluation object created using the array inputs supplied
	 * @param array $arr
	 * @return Evaluation
	 */
	public static function fromArray(array $arr, Evaluation $evaluation) {
		$evaluation->id = $arr['id'];
		return $evaluation;
	}

	public static function getEditQuestionControls($question_data) {
		global $db, $PROCESSED;
		if (isset($question_data["questiontype_id"]) && $question_data["questiontype_id"]) {
			$query = "SELECT * FROM `evaluations_lu_questiontypes`
						WHERE `questiontype_id` = ".$db->qstr($question_data["questiontype_id"]);
			$questiontype = $db->GetRow($query);
		} else {
			$questiontype = array("questiontype_shortname" => "matrix_single");
		}
		switch ($questiontype["questiontype_shortname"]) {
			case "rubric" :
				?>
					<tr>
						<td style="vertical-align: top">
							<label for="rubric_title" class="form-required">Rubric Title</label>
						</td>
						<td>
							<input type="text" id="rubric_title" name="rubric_title" style="width: 330px;" value="<?php echo ((isset($question_data["rubric_title"])) ? clean_input($question_data["rubric_title"], "encode") : ""); ?>">
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top">
							<label for="rubric_description" class="form-required">Rubric Description</label>
						</td>
						<td>
							<textarea id="rubric_description" class="expandable" name="rubric_description" style="width: 98%; height:0"><?php echo ((isset($question_data["rubric_description"])) ? clean_input($question_data["rubric_description"], "encode") : ""); ?></textarea>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top">
							<label for="columns_count" class="form-required">Number of Columns</label>
						</td>
						<td>
							<select name="columns_count" id="columns_count" onchange="updateColumns(this.options[this.selectedIndex].value, $('categories_count').value)">
								<option value="2"<?php echo (isset($question_data["columns_count"]) && $question_data["columns_count"] == 2 ? " selected=\"selected\"" : ""); ?>>2</option>
								<option value="3"<?php echo ((isset($question_data["columns_count"]) && $question_data["columns_count"] == 3) || !isset($question_data["columns_count"]) || !$question_data["columns_count"] ? " selected=\"selected\"" : ""); ?>>3</option>
								<option value="4"<?php echo (isset($question_data["columns_count"]) && $question_data["columns_count"] == 4 ? " selected=\"selected\"" : ""); ?>>4</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td style="padding-top: 5px; vertical-align: top">
							<label for="response_text_0" class="form-required">Column Labels</label>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td style="padding-top: 5px; vertical-align: top">
							<input type="hidden" value="<?php echo (isset($question_data["categories_count"]) && (int) $question_data["categories_count"] ? $question_data["categories_count"] : 1); ?>" name="categories_count" id="categories_count" />
						</td>
						<td style="padding-top: 5px">
							<table class="form-question" cellspacing="0" cellpadding="2" border="0" summary="Form Question Responses">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 77%" />
								<col style="width: 20%" />
							</colgroup>
							<thead>
								<tr>
									<td colspan="2">&nbsp;</td>
									<td class="center" style="font-weight: bold; font-size: 11px">Minimum Pass</td>
								</tr>
							</thead>
							<tbody id="columns_list">
								<?php
									echo Evaluation::getRubricColumnList($question_data);
								?>
							</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<h2>Categories</h2>
							<div style="float: right; margin-top: -40px;">
								<ul class="page-action">
									<li><a style="cursor: pointer;" onclick="loadCategories($('columns_count').options[$('columns_count').selectedIndex].value, (parseInt($('categories_count').value) + 1), 0)">Add Another Category</a></li>
								</ul>
							</div>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 5px" colspan="2">
							<table class="form-question" id="category_list" cellspacing="0" cellpadding="2" border="0" summary="Form Question Responses">
							<?php
								echo Evaluation::getRubricCategoryList($question_data);
							?>
							</table>
						</td>
					</tr>
				<?php
			break;
			case "free_text" :
				?>
				<tr>
					<td style="vertical-align: top">
						<label for="question_text" class="form-required">Question Text</label>
					</td>
					<td>
						<textarea id="question_text" class="expandable" name="question_text" style="width: 100%; height:0"><?php echo ((isset($question_data["question_text"])) ? clean_input($question_data["question_text"], "encode") : ""); ?></textarea>
					</td>
				</tr>
				<tr>
					<td style="padding-top: 5px; vertical-align: top">
						<label class="form-required">Available Responses</label>
					</td>
					<td>
						<?php
							add_notice("The evaluators will be asked to enter a free text comment as a response to this question.");
							echo display_notice();
						?>
					</td>
				</tr>
				<?php
			break;
			case "descriptive_text" :
				?>
					<tr>
						<td style="vertical-align: top">
							<label for="question_text" class="form-nrequired">Descriptive Text</label>
						</td>
						<td>
							<textarea id="question_text" class="expandable" name="question_text" style="width: 98%; height:0"><?php echo ((isset($question_data["question_text"])) ? clean_input($question_data["question_text"], "encode") : ""); ?></textarea>
						</td>
					</tr>
				<?php
			break;
			case "matrix_single" :
			default :
				?>
					<tr>
						<td style="vertical-align: top">
							<label for="question_text" class="form-required">Question Text</label>
						</td>
						<td>
							<textarea id="question_text" class="expandable" name="question_text" style="width: 98%; height:0"><?php echo ((isset($question_data["question_text"])) ? clean_input($question_data["question_text"], "encode") : ""); ?></textarea>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 5px; vertical-align: top">
							<label for="response_text_0" class="form-required">Available Responses</label>
						</td>
						<td style="padding-top: 5px">
							<table class="form-question" cellspacing="0" cellpadding="2" border="0" summary="Form Question Responses">
							<colgroup>
								<col style="width: 3%" />
								<col style="width: 77%" />
								<col style="width: 20%" />
							</colgroup>
							<thead>
								<tr>
									<td colspan="2">&nbsp;</td>
									<td class="center" style="font-weight: bold; font-size: 11px">Minimum Pass</td>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach (range(1, 4) as $number) {
									$minimum_passing_level = (((!isset($question_data["evaluation_form_responses"][$number]["minimum_passing_level"]) && ($number == 1)) || (isset($question_data["evaluation_form_responses"][$number]["minimum_passing_level"]) && (int) $question_data["evaluation_form_responses"][$number]["minimum_passing_level"])) ? true : false);
									?>
									<tr>
										<td style="padding-top: 13px">
											<label for="response_text_<?php echo $number; ?>" class="form-required"><?php echo $number; ?></label>
										</td>
										<td style="padding-top: 10px">
											<input type="text" id="response_text_<?php echo $number; ?>" name="response_text[<?php echo $number; ?>]" style="width: 99%" value="<?php echo ((isset($question_data["evaluation_form_responses"][$number]["response_text"])) ? clean_input($question_data["evaluation_form_responses"][$number]["response_text"], "encode") : ""); ?>" />
										</td>
										<td class="minimumPass center" style="padding-top: 10px">
											<input type="radio" name="minimum_passing_level" id="fail_indicator_<?php echo $number; ?>" value="<?php echo $number; ?>"<?php echo (($minimum_passing_level) ? " checked=\"true\"" : ""); ?> />
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
							</table>
						</td>
					</tr>
				<?php
			break;
		}
	}
	public static function getRubricCategoryList($question_data) {
		if ($question_data) {
			?>
			<colgroup>
				<col style="width: 20%" />
				<col style="width: 57%" />
				<col style="width: 20%" />
			</colgroup>
			<?php
			foreach (range(1, (isset($question_data["categories_count"]) && (int) $question_data["categories_count"] ? (int) $question_data["categories_count"] : 1)) as $rownum) {
				?>
				<tbody style="<?php echo ($rownum % 2 == 1 ? "background-color: #EEE;" : "background-color: #FFF;"); ?>">
					<tr>									
						<td style="padding-top: 10px;">
							<label for="category_<?php echo $rownum; ?>" class="form-required">Category Title</label>
						</td>
						<td colspan="2" style="padding: 10px 4px 0px 4px;">
							<input class="category" type="text" id="category_<?php echo $rownum; ?>" name="category[<?php echo $rownum; ?>]" style="width: 79%" value="<?php echo ((isset($question_data["evaluation_form_categories"][$rownum]["category"])) ? clean_input($question_data["evaluation_form_categories"][$rownum]["category"], "encode") : ""); ?>" />
							<?php
							echo "<div class=\"controls\" style=\"float: right;\">\n";
							echo "	<a id=\"question_delete_".$rownum."\" class=\"question-controls-delete\" onclick=\"loadCategories($('columns_count').options[$('columns_count').selectedIndex].value, (parseInt($('categories_count').value) - 1), ".$rownum.")\" title=\"".$rownum."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
							echo "</div>\n";
							?>
						</td>
					</tr>
					<tr>
						<td>
							&nbsp;
						</td>
						<td colspan="2">
							<div class="rubric_criteria_list" style="padding-right: 20px;">
								<?php
							foreach (range(1, (isset($question_data["columns_count"]) && (int) $question_data["columns_count"] ? (int) $question_data["columns_count"] : 3)) as $colnum) {
								?>
								<div style="width: 100%; text-align: right; margin: 10px 0px; float: right;">
									<div style="position: absolute;">
										<label style="text-align: left; vertical-align: top;" class="form-required" for="category_<?php echo $rownum."_criteria_".$colnum; ?>">Column <?php echo $colnum; ?> Criteria</label>
									</div>
									<textarea class="criteria_<?php echo $rownum; ?>" style="width: 65%;" id="criteria_<?php echo $colnum; ?>" name="criteria[<?php echo $rownum."][".$colnum; ?>]"><?php echo (isset($question_data["evaluation_form_category_criteria"][$rownum][$colnum]["criteria"]) && $question_data["evaluation_form_category_criteria"][$rownum][$colnum]["criteria"] ? html_encode($question_data["evaluation_form_category_criteria"][$rownum][$colnum]["criteria"]) : ""); ?></textarea>
								</div>
								<?php
							}
							?>
							</div>
						</td>
					</tr>
				</tbody>
				<?php
			}
		}
	}
	public static function getRubricColumnList($question_data) {
		if ($question_data) {
			foreach (range(1, (isset($question_data["columns_count"]) && (int) $question_data["columns_count"] ? (int) $question_data["columns_count"] : 3)) as $number) {
				$minimum_passing_level = (((!isset($question_data["evaluation_form_responses"][$number]["minimum_passing_level"]) && ($number == 1)) || (isset($question_data["evaluation_form_responses"][$number]["minimum_passing_level"]) && (int) $question_data["evaluation_form_responses"][$number]["minimum_passing_level"])) ? true : false);
				?>
				<tr>
					<td style="padding-top: 13px">
						<label for="response_text_<?php echo $number; ?>" class="form-required"><?php echo $number; ?></label>
					</td>
					<td style="padding-top: 10px">
						<input type="text" class="response_text" id="response_text_<?php echo $number; ?>" name="response_text[<?php echo $number; ?>]" style="width: 99%" value="<?php echo ((isset($question_data["evaluation_form_responses"][$number]["response_text"])) ? clean_input($question_data["evaluation_form_responses"][$number]["response_text"], "encode") : ""); ?>" />
					</td>
					<td class="minimumPass center" style="padding-top: 10px">
						<input type="radio" name="minimum_passing_level" id="fail_indicator_<?php echo $number; ?>" value="<?php echo $number; ?>"<?php echo (($minimum_passing_level) ? " checked=\"true\"" : ""); ?> />
					</td>
				</tr>
				<?php
			}
		}
	}
	public static function getQuestionAnswerControls($questions) {
		global $db, $ALLOW_QUESTION_MODIFICATIONS, $FORM_ID;
		
		?>
		<div id="form-content-questions-holder">
			<ol id="form-questions-list">
			<?php
			$rubric_id = 0;
			$show_rubric_headers = false;
			$show_rubric_footers = false;
			$rubric_table_open = false;
			foreach ($questions as $key => $question) {
				if (isset($question["questiontype_id"]) && $question["questiontype_id"]) {
					$query = "SELECT * FROM `evaluations_lu_questiontypes`
								WHERE `questiontype_id` = ".$db->qstr($question["questiontype_id"]);
					$questiontype = $db->GetRow($query);
				} else {
					$questiontype = array("questiontype_shortname" => "matrix_single");
				}
				switch ($questiontype["questiontype_shortname"]) {
					case "rubric" :
						$query = "SELECT * FROM `evaluation_form_rubric_questions` AS a 
									JOIN `evaluation_form_rubrics` AS b
									ON a.`efrubric_id` = b.`efrubric_id`
									WHERE a.`efquestion_id` = ".$db->qstr($question["efquestion_id"]);
						$rubric = $db->GetRow($query);
						if ($rubric) {
							if ($rubric["efrubric_id"] != $rubric_id) {
								if ($rubric_id) {
									$show_rubric_footers = true;
								}
								$rubric_id = $rubric["efrubric_id"];
								$show_rubric_headers = true;
							}
							if ($show_rubric_footers) {
								$show_rubric_footers = false;
								$rubric_table_open = false;
								echo "</table></div></li>";
							}
							if ($show_rubric_headers) {
								$rubric_table_open = true;
								echo "<li id=\"question_".$question["efquestion_id"]."\">\n";
								echo "<h2>".$rubric["rubric_title"]."<span style=\"font-weight: normal; margin-left: 10px;\" class=\"content-small\">".$rubric["rubric_description"]."</span></h2>\n";
								if ($ALLOW_QUESTION_MODIFICATIONS) {
									echo "<div class=\"rubric-controls\">\n";
									echo "	<a href=\"".ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&amp;section=edit&amp;record=".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-edit.gif\" alt=\"Edit Question\" title=\"Edit Question\" /></a>";
									echo "	<a id=\"question_delete_".$question["efquestion_id"]."\" class=\"question-controls-delete\" href=\"#delete-question-confirmation-box\" title=\"".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
									echo "</div>\n";
								}
								echo "<br /><div class=\"question\"><table class=\"rubric\">\n";
								echo "	<tr>\n";
								$columns = 0;
								$query = "	SELECT a.*
											FROM `evaluation_form_responses` AS a
											WHERE a.`efquestion_id` = ".$db->qstr($question["efquestion_id"])."
											ORDER BY a.`response_order` ASC";
								$responses = $db->GetAll($query);
								if ($responses) {
									$response_width = floor(100 / (count($responses) + 1));
									echo "		<th style=\"width: ".$response_width."%; text-align: left; border-bottom: \">\n";
									echo "			Categories";
									echo "		</th>\n";
									foreach ($responses as $response) {
										$columns++;
										echo "<th style=\"width: ".$response_width."%; text-align: left;\">\n";
										echo clean_input($response["response_text"], "specialchars");
										echo "</th>\n";
									}
								}
								echo "	</tr>\n";
								$show_rubric_headers = false;
							}
							
							$question_number = ($key + 1);

							echo "<tr id=\"question_".$question["efquestion_id"]."\"".(($key % 2) ? " class=\"odd\"" : "").">";
							
							$query = "	SELECT b.*, a.`efquestion_id`, a.`minimum_passing_level`
										FROM `evaluation_form_responses` AS a
										JOIN `evaluation_form_response_criteria` AS b
										ON a.`efresponse_id` = b.`efresponse_id`
										WHERE a.`efquestion_id` = ".$db->qstr($question["efquestion_id"])."
										ORDER BY a.`response_order` ASC";
							$criteriae = $db->GetAll($query);
							if ($criteriae) {
								$criteria_width = floor(100 / (count($criteriae) + 1));
								echo "		<td style=\"width: ".$criteria_width."%\">\n";
								echo "			".$question["question_text"];
								echo "		</td>\n";
								foreach ($criteriae as $criteria) {
									echo "<td style=\"width: ".$criteria_width."%; vertical-align: top;".($criteria["minimum_passing_level"] ? " background-color: #F0F0F0;" : "")."\" >\n";
									echo "	<div style=\"width: 100%; text-align: center; padding-bottom: 10px;\">";
									echo "		<input type=\"radio\" id=\"".$form_record["eform_id"]."_".$criteria["efquestion_id"]."_".$criteria["efresponse_id"]."\" name=\"form[".$form_record["eform_id"]."][".$criteria["efquestion_id"]."]\" />";
									echo "	</div>\n";
									echo clean_input($criteria["criteria_text"], "specialchars");
									echo "</td>\n";
								}
							}
							echo "</tr>";
						}
					break;
					case "free_text" :
					case "descriptive_text" :
						if ($rubric_table_open) {
							echo "</table></li>";
							$rubric_table_open = false;
							$rubric_id = 0;
						} 
						$question_number = ($key + 1);

						echo "<li id=\"question_".$question["efquestion_id"]."\"".(($key % 2) ? " class=\"odd\"" : "").">";
						if ($ALLOW_QUESTION_MODIFICATIONS) {
							echo "<div class=\"controls\">\n";
							echo "	<a href=\"".ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&amp;section=edit&amp;record=".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-edit.gif\" alt=\"Edit Question\" title=\"Edit Question\" /></a>";
							echo "	<a id=\"question_delete_".$question["efquestion_id"]."\" class=\"question-controls-delete\" href=\"#delete-question-confirmation-box\" title=\"".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
							echo "</div>\n";
						}
						echo "	<div id=\"question_text_".$question["efquestion_id"]."\" class=\"question\">\n";
						echo "		".clean_input($question["question_text"], "specialchars");
						echo "	</div>\n";
						echo "	<div class=\"clear\"></div>";
						if ($questiontype["questiontype_shortname"] == "free_text") {
							echo "	<div class=\"comments\">";
							echo "	<label for=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"form-nrequired\">Comments:</label>";
							echo "	<textarea id=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"expandable\" style=\"width:95%; height:40px;\"></textarea>";
							echo "	</div>";
						}
						echo "</li>\n";
					break;
					case "matrix_single" :
					default :
						if ($rubric_table_open) {
							echo "</table></li>";
							$rubric_table_open = false;
							$rubric_id = 0;
						} 
						$question_number = ($key + 1);

						echo "<li id=\"question_".$question["efquestion_id"]."\"".(($key % 2) ? " class=\"odd\"" : "").">";
						if ($ALLOW_QUESTION_MODIFICATIONS) {
							echo "<div class=\"controls\">\n";
							echo "	<a href=\"".ENTRADA_URL."/admin/evaluations/forms/questions?id=".$FORM_ID."&amp;section=edit&amp;record=".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-edit.gif\" alt=\"Edit Question\" title=\"Edit Question\" /></a>";
							echo "	<a id=\"question_delete_".$question["efquestion_id"]."\" class=\"question-controls-delete\" href=\"#delete-question-confirmation-box\" title=\"".$question["efquestion_id"]."\"><img class=\"question-controls\" src=\"".ENTRADA_URL."/images/action-delete.gif\" alt=\"Delete Question\" title=\"Delete Question\" /></a>";
							echo "</div>\n";
						}
						echo "	<div id=\"question_text_".$question["efquestion_id"]."\" class=\"question\">\n";
						echo "		".clean_input($question["question_text"], "specialchars");
						echo "	</div>\n";
						echo "	<div class=\"responses\">\n";
						$query = "	SELECT a.*
									FROM `evaluation_form_responses` AS a
									WHERE a.`efquestion_id` = ".$db->qstr($question["efquestion_id"])."
									ORDER BY a.`response_order` ASC";
						$responses = $db->GetAll($query);
						if ($responses) {
							$response_width = floor(100 / count($responses));

							foreach ($responses as $response) {
								echo "<div style=\"width: ".$response_width."%\">\n";
								echo "	<label for=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."\">".clean_input($response["response_text"], "specialchars")."</label><br />";
								echo "	<input type=\"radio\" style=\"margin-top: 5px\" id=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."\" name=\"form[".$form_record["eform_id"]."][".$response["efquestion_id"]."]\" />";
								echo "</div>\n";
							}
						}
						echo "	</div>\n";
						echo "	<div class=\"clear\"></div>";
						echo "	<div class=\"comments\">";
						echo "	<label for=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"form-nrequired\">Comments:</label>";
						echo "	<textarea id=\"".$form_record["eform_id"]."_".$response["efquestion_id"]."_".$response["efresponse_id"]."_comment\" class=\"expandable\" style=\"width:95%; height:40px;\"></textarea>";
						echo "	</div>";
						echo "</li>\n";
					break;
				}
			}
			if ($rubric_table_open) {
				echo "</table></li>";
			} 
				
			?>
			</ol>
		</div>
		<?php
		if ($ALLOW_QUESTION_MODIFICATIONS) {
			?>
			<div id="delete-question-confirmation-box" class="modal-confirmation">
				<h1>Delete Form <strong>Question</strong> Confirmation</h1>
				Do you really wish to remove this question from your evaluation form?
				<div class="body">
					<div id="delete-question-confirmation-content" class="content"></div>
				</div>
				If you confirm this action, the question will be permanently removed.
				<div class="footer">
					<input type="button" class="button" value="Close" onclick="Control.Modal.close()" style="float: left; margin: 8px 0px 4px 10px" />
					<input type="button" class="button" value="Confirm" onclick="deleteFormQuestion(deleteQuestion_id)" style="float: right; margin: 8px 10px 4px 0px" />
				</div>
			</div>
			<script type="text/javascript" defer="defer">
				var deleteQuestion_id = 0;

				Sortable.create('form-questions-list', { handles : $$('#form-questions-list div.question'), onUpdate : updateFormQuestionOrder });

				$$('a.question-controls-delete').each(function(obj) {
					new Control.Modal(obj.id, {
						overlayOpacity:	0.75,
						closeOnClick:	'overlay',
						className:		'modal-confirmation',
						fade:			true,
						fadeDuration:	0.30,
						beforeOpen: function() {
							deleteQuestion_id = obj.readAttribute('title');
							$('delete-question-confirmation-content').innerHTML = $('question_text_' + obj.readAttribute('title')).innerHTML;
						},
						afterClose: function() {
							deleteQuestion_id = 0;
							$('delete-question-confirmation-content').innerHTML = '';
						}
					});
				});

				function updateFormQuestionOrder() {
					new Ajax.Request('<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions', {
						method: 'post',
						parameters: { id : <?php echo $FORM_ID; ?>, section : 'api-order', result : Sortable.serialize('form-questions-list', { name : 'order' }) },
						onSuccess: function(transport) {
							var count = 0;
							$$('#form-questions-list li').each(function(obj) {
								if (obj.hasClassName('odd')) {
									obj.removeClassName('odd');
								}

								if (!(count % 2)) {
									obj.addClassName('odd');
								}
								count++;
							});
							if (!transport.responseText.match(200)) {
								new Effect.Highlight('form-content-questions-holder', { startcolor : '#FFD9D0' });
							}
						},
						onError: function() {
							new Effect.Highlight('form-content-questions-holder', { startcolor : '#FFD9D0' });
						}
					});
				}

				function deleteFormQuestion(efquestion_id) {
					Control.Modal.close();
					$('question_' + efquestion_id).fade({ duration: 0.3 });

					new Ajax.Request('<?php echo ENTRADA_URL; ?>/admin/evaluations/forms/questions', {
						method: 'post',
						parameters: { id: '<?php echo $FORM_ID; ?>', section: 'api-delete', record: efquestion_id },
						onSuccess: function(transport) {
							if (transport.responseText.match(200)) {
								$('question_' + efquestion_id).remove();

								if ($$('#form-questions-list li').length == 0) {
									$('form-content-questions-holder').hide();
									$('display-no-question-message').show();
								}
							} else {
								if ($$('#question_' + efquestion_id + ' .display-error').length == 0) {
									var errorString = 'Unable to delete this question at this time.<br /><br />The system administrator has been notified of this error, please try again later.';
									var errorMsg = new Element('div', { 'class': 'display-error' }).update(errorString);

									$('question_' + efquestion_id).insert(errorMsg);
								}

								$('question_' + efquestion_id).appear({ duration: 0.3 });

								new Effect.Highlight('question_' + efquestion_id, { startcolor : '#FFD9D0' });
							}
						},
						onError: function() {
							$('question_' + efquestion_id).appear({ duration: 0.3 });

							new Effect.Highlight('question_' + efquestion_id, { startcolor : '#FFD9D0' });
						}
					});
				}
			</script>
			<?php
		}
	}
	
	public static function getTargetControls ($target_data, $options_for = "", $form_id = 0) {
		global $ENTRADA_USER, $ENTRADA_ACL, $db, $use_ajax;
		if ($form_id) {
			$query = "	SELECT b.*
						FROM `evaluation_forms` AS a
						LEFT JOIN `evaluations_lu_targets` AS b
						ON b.`target_id` = a.`target_id`
						WHERE a.`form_active` = '1'
						AND b.`target_active` = '1'
						AND a.`eform_id` = ".$db->qstr($form_id);
			$target_details = $db->GetRow($query);
			if ($target_details) {
				switch ($target_details["target_shortname"]) {
					case "course" :
						$courses_list = array();

						$query = "	SELECT `course_id`, `organisation_id`, `course_code`, `course_name`
									FROM `courses`
									WHERE `organisation_id`=".$ENTRADA_USER->getActiveOrganisation()."
									AND `course_active` = '1'
									ORDER BY `course_code` ASC, `course_name` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								if ($ENTRADA_ACL->amIAllowed(new CourseResource($result["course_id"], $result["organisation_id"]), "read")) {
									$courses_list[$result["course_id"]] = ($result["course_code"]." - ".$result["course_name"]);
								}
							}
						}
						?>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<label for="PickList" class="form-required">Select Courses</label>
								<div class="content-small"><strong>Hint:</strong> Select the course or courses you would like to have evaluated.</div>
							</td>
							<td style="vertical-align: top">
								<select class="multi-picklist" id="PickList" name="course_ids[]" multiple="multiple" size="4" style="width: 100%; margin-bottom: 5px">
								<?php
								if ((is_array($PROCESSED["evaluation_targets"])) && (!empty($PROCESSED["evaluation_targets"]))) {
									foreach ($PROCESSED["evaluation_targets"] as $course_id) {
										echo "<option value=\"".(int) $course_id."\">".html_encode($courses_list[$course_id])."</option>\n";
									}
								}
								?>
								</select>
								<div style="float: left; display: inline">
									<input type="button" id="courses_list_state_btn" class="button" value="Show List" onclick="toggle_list('courses_list')" />
								</div>
								<div style="float: right; display: inline">
									<input type="button" id="courses_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
									<input type="button" id="courses_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
								</div>
								<div id="courses_list" style="clear: both; padding-top: 3px; display: none">
									<h2>Course List</h2>
									<select class="multi-picklist" id="SelectList" name="other_courses_list" multiple="multiple" size="15" style="width: 100%">
									<?php
									foreach ($courses_list as $course_id => $course_name) {
										if (!in_array($course_id, $PROCESSED["evaluation_targets"])) {
											echo "<option value=\"".(int) $course_id."\">".html_encode($course_name)."</option>\n";
										}
									}
									?>
									</select>
								</div>
							</td>
						</tr>
						<?php
					break;
					case "teacher" :
						$teachers_list = array();

						$query = "	SELECT a.`id` AS `proxy_id`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
									FROM `".AUTH_DATABASE."`.`user_data` AS a
									LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
									ON b.`user_id` = a.`id`
									LEFT JOIN `event_contacts` AS c
									ON c.`proxy_id` = a.`id`
									LEFT JOIN `events` AS d
									ON d.`event_id` = c.`event_id`
									WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
									AND (b.`group` = 'faculty' OR 
										(b.`group` = 'resident' AND b.`role` = 'lecturer')
									)
									AND d.`event_finish` >= ".$db->qstr(strtotime("-12 months"))."
									GROUP BY a.`id`
									ORDER BY a.`lastname` ASC, a.`firstname` ASC";
						$results = $db->GetAll($query);
						if ($results) {
							foreach ($results as $result) {
								$teachers_list[$result["proxy_id"]] = $result["fullname"];
							}
						}
						?>
						<tr>
							<td></td>
							<td style="vertical-align: top">
								<label for="PickList" class="form-required">Select Teachers</label>
								<div class="content-small"><strong>Hint:</strong> Select the teacher or teachers you would like to have evaluated.</div>
							</td>
							<td style="vertical-align: top">
								<select class="multi-picklist" id="PickList" name="teacher_ids[]" multiple="multiple" size="4" style="width: 100%; margin-bottom: 5px">
								<?php
								if ((is_array($PROCESSED["evaluation_targets"])) && (!empty($PROCESSED["evaluation_targets"]))) {
									foreach ($PROCESSED["evaluation_targets"] as $proxy_id) {
										echo "<option value=\"".(int) $proxy_id."\">".html_encode($teachers_list[$proxy_id])."</option>\n";
									}
								}
								?>
								</select>
								<div style="float: left; display: inline">
									<input type="button" id="teachers_list_state_btn" class="button" value="Show List" onclick="toggle_list('teachers_list')" />
								</div>
								<div style="float: right; display: inline">
									<input type="button" id="teachers_list_remove_btn" class="button-remove" onclick="delIt()" value="Remove" />
									<input type="button" id="teachers_list_add_btn" class="button-add" onclick="addIt()" style="display: none" value="Add" />
								</div>
								<div id="teachers_list" style="clear: both; padding-top: 3px; display: none">
									<h2>Course List</h2>
									<select class="multi-picklist" id="SelectList" name="other_teachers_list" multiple="multiple" size="15" style="width: 100%">
									<?php
									foreach ($teachers_list as $proxy_id => $teacher_name) {
										if (!in_array($proxy_id, $PROCESSED["evaluation_targets"])) {
											echo "<option value=\"".(int) $proxy_id."\">".html_encode($teacher_name)."</option>\n";
										}
									}
									?>
									</select>
								</div>
							</td>
						</tr>
						<?php
					break;
					case "student" :
					case "peer" :
						$query = "SELECT * FROM `course_groups` AS a 
									JOIN `courses` AS b 
									ON a.`course_id` = b.`course_id` 
									ORDER BY b.`course_name`, 
										LENGTH(a.`group_name`), 
										a.`group_name` ASC";
						$temp_course_groups = $db->GetAll($query);
						$course_groups = array();
						if ($temp_course_groups) {
							foreach ($temp_course_groups as $temp_course_group) {
								$course_groups[$temp_course_group["cgroup_id"]] = $temp_course_group;
							}
						}
						unset($temp_course_groups);
						?>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Evaluation Targets</label></td>
							<td>
								<table>
									<tbody>
										<tr>
											<td style="vertical-align: top"><input type="radio" name="target_type" id="target_type_custom" value="custom" onclick="selectEvaluationTargetOption('custom')" style="vertical-align: middle" checked="checked" /></td>
											<td colspan="2" style="padding-bottom: 15px">
												<label for="target_type_custom" class="radio-group-title">Custom Evaluation Targets</label>
												<div class="content-small">This evaluation is intended for a custom selection of evaluation targets.</div>

												<div id="evaluation_target_type_custom_options" style="position: relative; margin-top: 10px;">
													<select id="target_type" onchange="showMultiSelect();" style="width: 275px;">
														<option value="">-- Select an target type --</option>
														<option value="cohorts">Cohorts of learners</option>
															<?php

														if ($course_groups) {
															?>
															<option value="course_groups">Course specific small groups</option>
															<?php
														}
															?>
														<option value="students">Individual learners</option>
													</select>

													<span id="options_loading" style="display:none; vertical-align: middle"><img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please Wait" title="" style="vertical-align: middle" /> Loading ... </span>
													<span id="options_container"></span>
													<?php
													/**
													 * Compiles the list of groups from groups table (known as Cohorts).
													 */
													$COHORT_LIST = array();
													$query = "	SELECT a.*
																FROM `groups` AS a
																JOIN `group_organisations` AS b
																ON a.`group_id` = b.`group_id`
																WHERE a.`group_active` = '1'
																AND a.`group_type` = 'cohort'
																AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
																ORDER BY LENGTH(a.`group_name`), a.`group_name` ASC";
													$results = $db->GetAll($query);
													if ($results) {
														foreach($results as $result) {
															$COHORT_LIST[$result["group_id"]] = $result;
														}
													}

													$GROUP_LIST = $course_groups;

													/**
													 * Compiles the list of students.
													 */
													$STUDENT_LIST = array();
													$query = "	SELECT a.`id` AS `proxy_id`, b.`role`, CONCAT_WS(' ', a.`firstname`, a.`lastname`) AS `fullname`, a.`organisation_id`
																FROM `".AUTH_DATABASE."`.`user_data` AS a
																LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
																ON a.`id` = b.`user_id`
																WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
																AND b.`account_active` = 'true'
																AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
																AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
																AND b.`group` = 'student'
																AND a.`grad_year` >= '".(date("Y") - ((date("m") < 7) ?  2 : 1))."'
																ORDER BY a.`grad_year` ASC, a.`lastname` ASC, a.`firstname` ASC";
													$results = $db->GetAll($query);
													if ($results) {
														foreach($results as $result) {
															$STUDENT_LIST[$result["proxy_id"]] = array("proxy_id" => $result["proxy_id"], "fullname" => $result["fullname"], "organisation_id" => $result["organisation_id"]);
														}
													}
													$target_data["form_id"] = $form_id;
													$PROCESSED = Evaluation::processTargets($target_data, $PROCESSED);

													if (!isset($PROCESSED["associated_cohort_ids"]) && !isset($PROCESSED["associated_cgroup_ids"]) && !isset($PROCESSED["associated_proxy_ids"]) && !isset($target_data["evaluation_target_cohorts"]) && !isset($target_data["evaluation_target_course_groups"]) && !isset($target_data["evaluation_target_students"]) && isset($EVALUATION_ID)) {
														$query = "SELECT * FROM `evaluation_targets` WHERE `evaluation_id` = ".$db->qstr($EVALUATION_ID);
														$results = $db->GetAll($query);
														if ($results) {
															$PROCESSED["target_type"] = "custom";

															foreach($results as $result) {
																switch($result["target_type"]) {
																	case "course_id" :
																		$PROCESSED["target_type"] = "course";

																		$PROCESSED["associated_course_ids"] = (int) $result["target_value"];
																	break;
																	case "cohort" :
																		$PROCESSED["associated_cohort_ids"][] = (int) $result["target_value"];
																	break;
																	case "group_id" :
																		$PROCESSED["associated_cgroup_ids"][] = (int) $result["target_value"];
																	break;
																	case "proxy_id" :
																		$PROCESSED["associated_proxy_ids"][] = (int) $result["target_value"];
																	break;
																}
															}
														}
													}

													$cohort_ids_string = "";
													$cgroup_ids_string = "";
													$student_ids_string = "";

													if (isset($PROCESSED["associated_course_ids"]) && $PROCESSED["associated_course_ids"]) {
														$course_target_included = true;
													} else {
														$course_target_included = false;
													}

													if (isset($PROCESSED["associated_cohort_ids"]) && is_array($PROCESSED["associated_cohort_ids"])) {
														foreach ($PROCESSED["associated_cohort_ids"] as $group_id) {
															if ($cohort_ids_string) {
																$cohort_ids_string .= ",group_".$group_id;
															} else {
																$cohort_ids_string = "group_".$group_id;
															}
														}
													}

													if (isset($PROCESSED["associated_cgroup_ids"]) && is_array($PROCESSED["associated_cgroup_ids"])) {
														foreach ($PROCESSED["associated_cgroup_ids"] as $group_id) {
															if ($cgroup_ids_string) {
																$cgroup_ids_string .= ",cgroup_".$group_id;
															} else {
																$cgroup_ids_string = "cgroup_".$group_id;
															}
														}
													}

													if (isset($PROCESSED["associated_proxy_ids"]) && is_array($PROCESSED["associated_proxy_ids"])) {
														foreach ($PROCESSED["associated_proxy_ids"] as $proxy_id) {
															if ($student_ids_string) {
																$student_ids_string .= ",student_".$proxy_id;
															} else {
																$student_ids_string = "student_".$proxy_id;
															}
														}
													}
													?>
													<input type="hidden" id="evaluation_target_cohorts" name="evaluation_target_cohorts" value="<?php echo $cohort_ids_string; ?>" />
													<input type="hidden" id="evaluation_target_course_groups" name="evaluation_target_course_groups" value="<?php echo $cgroup_ids_string; ?>" />
													<input type="hidden" id="evaluation_target_students" name="evaluation_target_students" value="<?php echo $student_ids_string; ?>" />
													<input type="hidden" id="evaluation_target_course" name="evaluation_target_course" value="<?php echo $course_target_included ? "1" : "0"; ?>" />

													<ul class="menu multiselect" id="target_list" style="margin-top: 5px">
													<?php
													if (isset($PROCESSED["associated_cohort_ids"]) && count($PROCESSED["associated_cohort_ids"])) {
														foreach ($PROCESSED["associated_cohort_ids"] as $group) {
															if ((array_key_exists($group, $COHORT_LIST)) && is_array($COHORT_LIST[$group])) {
																?>
																<li class="group" id="target_group_<?php echo $COHORT_LIST[$group]["group_id"]; ?>" style="cursor: move;"><?php echo $COHORT_LIST[$group]["group_name"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeTarget('group_<?php echo $COHORT_LIST[$group]["group_id"]; ?>', 'cohorts');" class="list-cancel-image" /></li>
																<?php
															}
														}
													}
													if (isset($PROCESSED["associated_cgroup_ids"]) && count($PROCESSED["associated_cgroup_ids"])) {
														foreach ($PROCESSED["associated_cgroup_ids"] as $group) {
															if ((array_key_exists($group, $GROUP_LIST)) && is_array($GROUP_LIST[$group])) {
																?>
																<li class="group" id="target_cgroup_<?php echo $GROUP_LIST[$group]["cgroup_id"]; ?>" style="cursor: move;"><?php echo $GROUP_LIST[$group]["group_name"]." - ".$GROUP_LIST[$group]["course_code"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeTarget('cgroup_<?php echo $GROUP_LIST[$group]["cgroup_id"]; ?>', 'course_groups');" class="list-cancel-image" /></li>
																<?php
															}
														}
													}

													if (isset($PROCESSED["associated_proxy_ids"]) && count($PROCESSED["associated_proxy_ids"])) {
														foreach ($PROCESSED["associated_proxy_ids"] as $student) {
															if ((array_key_exists($student, $STUDENT_LIST)) && is_array($STUDENT_LIST[$student])) {
																?>
																<li class="user" id="target_student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>" style="cursor: move;"><?php echo $STUDENT_LIST[$student]["fullname"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeTarget('student_<?php echo $STUDENT_LIST[$student]["proxy_id"]; ?>', 'students');" class="list-cancel-image" /></li>
																<?php
															}
														}
													}
													?>
													</ul>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<?php
						if ($target_details["target_shortname"] == "peer") {
							?>
							<tr>
								<td colspan="2">&nbsp;</td>
								<td>
									<?php echo display_notice("When creating peer assessments, learners will be able to assess any others within the same cohort, course group, or custom list of students, depending on which evaluation targets you include. <br /><br />Additionally, they will not be able to view results of evaluations done on themselves until they have filled out all of the required evaluations available to them, or the evaluation period ends, whichever comes first."); ?>
								</td>
							</tr>
							<?php
						}
					break;
					case "rotation_core" :
						$target_data["form_id"] = $form_id;
						$PROCESSED = Evaluation::processTargets($target_data, $PROCESSED);

						?>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Evaluation Targets</label></td>
							<td>
								<table>
									<tbody>
										<tr>
											<td style="vertical-align: top"><input type="radio" name="target_type" id="target_type_rotations" value="rotations" onclick="selectEvaluationTargetOption('rotations')" style="vertical-align: middle"  checked="checked" /></td>
											<td colspan="2" style="padding-bottom: 15px">
												<label for="target_type_rotations" class="radio-group-title">Each Service in the selected Core Rotation</label>
												<div class="content-small">This evaluation is intended for all events associated with a custom selection of Core Rotations.</div>
												<?php

												$ROTATION_LIST = array();
												$rotations[0] = array("text" => "All Core Rotations", "value" => "all", "category" => true);

												$query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`
															WHERE `rotation_id` != ".$db->qstr(MAX_ROTATION);
												$rotation_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
												if ($rotation_results) {
													foreach ($rotation_results as $rotation) {
														$ROTATION_LIST[$rotation["rotation_id"]] = $rotation;
														if (isset($PROCESSED["associated_rotation_ids"]) && is_array($PROCESSED["associated_rotation_ids"]) && in_array($rotation["rotation_id"], $PROCESSED["associated_rotation_ids"])) {
															$checked = "checked=\"checked\"";
														} else {
															$checked = "";
														}

														$rotations[0]["options"][] = array("text" => $rotation["rotation_title"], "class" => "cat_enabled", "value" => "rotation_".$rotation["rotation_id"], "checked" => $checked);
													}

													echo lp_multiple_select_inline("rotations", $rotations, array("title" => "Select Core Rotations:", "hidden" => false, "class" => "select_multiple_area_container", "category_check_all" => true, "submit" => false));
												} else {
													echo display_notice("There are no core rotations available.");
												}
												if (isset($PROCESSED["associated_rotation_ids"]) && is_array($PROCESSED["associated_rotation_ids"])) {
													foreach ($PROCESSED["associated_rotation_ids"] as $rotation_id) {
														if ($rotation_ids_string) {
															$rotation_ids_string .= ",rotation_".$rotation_id;
														} else {
															$rotation_ids_string = "rotation_".$rotation_id;
														}
													}
												}
												?>
												<input type="hidden" id="evaluation_target_rotations" name="evaluation_target_rotations" value="<?php echo $rotation_ids_string; ?>" />
												<ul class="menu multiselect" id="target_list" style="margin-top: 5px;">
													<?php
													if (is_array($PROCESSED["associated_rotation_ids"]) && count($PROCESSED["associated_rotation_ids"])) {
														foreach ($PROCESSED["associated_rotation_ids"] as $rotation) {
															if ((array_key_exists($rotation, $ROTATION_LIST)) && is_array($ROTATION_LIST[$rotation])) {
																?>
																<li class="group" id="target_rotation_<?php echo $ROTATION_LIST[$rotation]["rotation_id"]; ?>" style="cursor: move;"><?php echo $ROTATION_LIST[$rotation]["rotation_title"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeTarget('rotation_<?php echo $COHORT_LIST[$group]["group_id"]; ?>', 'rotations');" class="list-cancel-image" /></li>
																<?php
															}
														}
													}
													?>
												</ul>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<?php echo display_notice("When creating core rotation evaluations, the list of <strong>Core Rotations</strong>, <strong>Evaluators</strong>, the <strong>Evaluation Start</strong>, and the <strong>Evaluation Finish</strong> determine which electives will be targeted for evaluation. <br /><br />Each of the services in one of selected <strong>Core Rotations</strong> which ends between the <strong>Evaluation Start</strong> and the <strong>Evaluation Finish</strong> for learners in the <strong>Evaluators</strong> list will require/allow an evaluation to be completed on it."); ?>
							</td>
						</tr>
						<?php
					break;
					case "rotation_elective" :
						?>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<?php echo display_notice("When creating clerkship elective evaluations, the list of <strong>Evaluators</strong>, the <strong>Evaluation Start</strong>, and the <strong>Evaluation Finish</strong> determine which electives will be targeted for evaluation. <br /><br />Each elective which ends between the <strong>Evaluation Start</strong> and the <strong>Evaluation Finish</strong> for learners in the <strong>Evaluators</strong> list will require/allow an evaluation to be completed on it."); ?>
							</td>
						</tr>
						<?php
					break;
					case "preceptor" :
						$target_data["form_id"] = $form_id;
						$PROCESSED = Evaluation::processTargets($target_data, $PROCESSED);
						?>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="faculty_name" class="form-nrequired">Evaluation Targets</label></td>
							<td>
								<table>
									<tbody>
										<tr>
											<td style="vertical-align: top"><input type="hidden" name="target_subtype" value="preceptor" /><input type="radio" name="target_type" id="target_type_rotations" value="rotations" onclick="selectEvaluationTargetOption('rotations')" style="vertical-align: middle"  checked="checked" /></td>
											<td colspan="2" style="padding-bottom: 15px">
												<label for="target_type_rotations" class="radio-group-title">Each Service in the selected Clerkship Rotation</label>
												<div class="content-small">This evaluation is intended for all events associated with a custom selection of Clerkship Rotations.</div>
												<?php

												$ROTATION_LIST = array();
												$rotations[0] = array("text" => "All Clerkship Rotations", "value" => "all", "category" => true);

												$query = "	SELECT *
															FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`";
												$rotation_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
												if ($rotation_results) {
													foreach ($rotation_results as $rotation) {
														$ROTATION_LIST[$rotation["rotation_id"]] = $rotation;
														if (isset($PROCESSED["associated_rotation_ids"]) && is_array($PROCESSED["associated_rotation_ids"]) && in_array($rotation["rotation_id"], $PROCESSED["associated_rotation_ids"])) {
															$checked = "checked=\"checked\"";
														} else {
															$checked = "";
														}

														$rotations[0]["options"][] = array("text" => $rotation["rotation_title"], "value" => "rotation_".$rotation["rotation_id"], "class" => "cat_enabled", "checked" => $checked);
													}

													echo lp_multiple_select_inline("rotations", $rotations, array("title" => "Select Clerkship Rotations:", "hidden" => false, "class" => "select_multiple_area_container", "category_check_all" => true, "submit" => false));
												} else {
													echo display_notice("There are no clerkship rotations available.");
												}
												if (isset($PROCESSED["associated_rotation_ids"]) && is_array($PROCESSED["associated_rotation_ids"])) {
													foreach ($PROCESSED["associated_rotation_ids"] as $rotation_id) {
														if ($rotation_ids_string) {
															$rotation_ids_string .= ",rotation_".$rotation_id;
														} else {
															$rotation_ids_string = "rotation_".$rotation_id;
														}
													}
												}
												?>
												<input type="hidden" id="evaluation_target_rotations" name="evaluation_target_rotations" value="<?php echo $rotation_ids_string; ?>" />
												<ul class="menu multiselect" id="target_list" style="margin-top: 5px;">
													<?php
													if (is_array($PROCESSED["associated_rotation_ids"]) && count($PROCESSED["associated_rotation_ids"])) {
														foreach ($PROCESSED["associated_rotation_ids"] as $rotation) {
															if ((array_key_exists($rotation, $ROTATION_LIST)) && is_array($ROTATION_LIST[$rotation])) {
																?>
																<li class="group" id="target_rotation_<?php echo $ROTATION_LIST[$rotation]["rotation_id"]; ?>" style="cursor: move;"><?php echo $ROTATION_LIST[$rotation]["rotation_title"]; ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="removeTarget('rotation_<?php echo $COHORT_LIST[$group]["group_id"]; ?>', 'rotations');" class="list-cancel-image" /></li>
																<?php
															}
														}
													}
													?>
												</ul>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<?php echo display_notice("When creating clerkship preceptor evaluations, the list of <strong>Rotations</strong>, <strong>Evaluators</strong>, the <strong>Evaluation Start</strong>, and the <strong>Evaluation Finish</strong> determine which electives will be targeted for evaluation. <br /><br />Each preceptor for services in one of the selected <strong>Rotations</strong> which ends between the <strong>Evaluation Start</strong> and the <strong>Evaluation Finish</strong> for learners in the <strong>Evaluators</strong> list will require/allow an evaluation to be completed on it."); ?>
							</td>
						</tr>
						<?php
					break;
					case "self" :
						?>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<?php echo display_notice("When creating self evaluations, the list of evaluators also acts as the target, as learners can only evaluate themselves."); ?>
							</td>
						</tr>
						<?php
					break;
					default :
						?>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td>
								<?php echo display_notice("The target that you have selected is not currently available."); ?>
							</td>
						</tr>
						<?php
						application_log("error", "Unaccounted for target_shortname [".$target_details["target_shortname"]."] encountered. An update to api-targets.inc.php is required.");
					break;
				}

				/**
				 * This will eventually need to be moved up into the above switch, or brought into a class
				 * that should have been written for this.
				 */
				if ($target_details["target_shortname"] != "peer" && $target_details["target_shortname"] != "student") {
					?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td style="vertical-align: top">
							<label for="PickList" class="form-required">Select Students</label>
							<div class="content-small"><strong>Hint:</strong> Select the student or students you would like to evaluate the teachers above.</div>
						</td>
						<td style="vertical-align: top">
							<table style="width: 100%" cellspacing="0" cellpadding="0">
								<colgroup>
									<col style="width: 4%" />
									<col style="width: 96%" />
								</colgroup>
								<tbody>
									<tr>
										<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_cohort" value="cohort" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
										<td style="padding-bottom: 15px">
											<label for="target_group_type_cohort" class="radio-group-title">Entire class must complete this evaluation</label>
											<div class="content-small">This evaluation must be completed by everyone in the selected class.</div>
										</td>
									</tr>
									<tr class="target_group cohort_target">
										<td></td>
										<td style="vertical-align: middle" class="content-small">
											<label for="cohort" class="form-required">All students in</label>
											<select id="cohort" name="cohort" style="width: 203px; vertical-align: middle">
												<?php
												$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
												if (isset($active_cohorts) && !empty($active_cohorts)) {
													foreach ($active_cohorts as $cohort) {
														echo "<option value=\"".$cohort["group_id"]."\"".((($PROCESSED["evaluation_targets"][0]["target_type"] == "cohort") && ($PROCESSED["evaluation_targets"][0]["target_value"] == $cohort["group_id"])) ? " selected=\"selected\"" : "").">".html_encode($cohort["group_name"])."</option>\n";
													}
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr>
										<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_percentage" value="percentage" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
										<td style="padding-bottom: 15px">
											<label for="target_group_type_percentage" class="radio-group-title">Percentage of class must complete this evaluation</label>
											<div class="content-small">This evaluation must be completed by certain percentage of students in the selected class.</div>
										</td>
									</tr>
									<tr class="target_group percentage_target">
										<td>&nbsp;</td>
										<td style="vertical-align: middle" class="content-small">
											<input type="text" class="percentage" id="percentage_percent" name="percentage_percent" style="width: 30px; vertical-align: middle" maxlength="3" value="100" /> <label for="percentage_cohort" class="form-required">of the</label>
											<select id="percentage_cohort" name="percentage_cohort" style="width: 203px; vertical-align: middle">
											<?php
											$active_cohorts = groups_get_active_cohorts($ENTRADA_USER->getActiveOrganisation());
											if (isset($active_cohorts) && !empty($active_cohorts)) {
												foreach ($active_cohorts as $cohort) {
													echo "<option value=\"".$cohort["group_id"]."\">".html_encode($cohort["group_name"])."</option>\n";
												}
											}
											?>
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr >
										<td style="vertical-align: top"><input type="radio" name="target_group_type" id="target_group_type_proxy_id" value="proxy_id" onclick="selectTargetGroupOption(this.value)" style="vertical-align: middle" /></td>
										<td style="padding-bottom: 15px">
											<label for="target_group_type_proxy_id" class="radio-group-title">Selected students must complete this evaluation</label>
											<div class="content-small">This evaluation must be completed only by the selected individuals.</div>
										</td>
									</tr>
									<tr class="target_group proxy_id_target">
										<td>&nbsp;</td>
										<td style="vertical-align: middle" class="content-small">
											<label for="student_name" class="form-required">Student Name</label>

											<input type="text" id="student_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
											<div class="autocomplete" id="student_name_auto_complete"></div>

											<input type="hidden" id="associated_student" name="associated_student" />
											<input type="button" class="button-sm" id="add_associated_student" value="Add" style="vertical-align: middle" />
											<span class="content-small" style="margin-left: 3px; padding-top: 5px"><strong>e.g.</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?></span>
											<ul id="student_list" class="menu" style="margin-top: 15px">
												<?php
												if (($PROCESSED["evaluation_evaluators"][0]["evaluator_type"] == "proxy_id") && is_array($PROCESSED["evaluation_evaluators"]) && !empty($PROCESSED["evaluation_evaluators"])) {
													foreach ($PROCESSED["evaluation_evaluators"] as $evaluator) {
														$proxy_id = (int) $evaluator["evaluator_value"];
														?>
														<li class="community" id="student_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo get_account_data("fullname", $proxy_id); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="student_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
														<?php
													}
												}
												?>
											</ul>
											<input type="hidden" id="student_ref" name="student_ref" value="" />
											<input type="hidden" id="student_id" name="student_id" value="" />
										</td>
									</tr>
								</tbody>
							</table>
							<div id="scripts-on-open" style="display: none;">
							selectTargetGroupOption('<?php echo (isset($PROCESSED["evaluation_targets"][0]["target_type"]) ? $PROCESSED["evaluation_targets"][0]["target_type"] : 'cohort'); ?>');
							student_list = new AutoCompleteList({ type: 'student', url: '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=student', remove_image: '<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif' });
							</div>
						</td>
					</tr>
					<?php
				} elseif ($target_details["target_shortname"] == "peer") {
					?>
					<div id="scripts-on-open" style="display: none;">
						$('submittable_notice').update('<div class="display-notice"><ul><li>If you set the Min or Max Submittable for a Peer Evaluation to 0, the value will default to the number of targets available to evaluate.</li></ul></div>');
					</div>
					<?php
				} elseif ($target_details["target_shortname"] == "student") {
					?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr
					<tr>
						<td></td>
						<td style="vertical-align: top"><label for="evalfaculty_name" class="form-required">Faculty Evaluators</label></td>
						<td>
							<input type="hidden" name="target_group_type" id="target_group_type_faculty" value="faculty" style="vertical-align: middle" />
							<div id="scripts-on-open" style="display: none;">
								faculty_list = new AutoCompleteList(
									{ 
										type: 'evalfaculty', 
										url: '<?php echo ENTRADA_RELATIVE; ?>/api/personnel.api.php?type=evalfaculty', 
										remove_image: '<?php echo ENTRADA_RELATIVE; ?>/images/action-delete.gif'
									});
							</div>
							<input type="text" id="evalfaculty_name" name="fullname" size="30" autocomplete="off" style="width: 203px; vertical-align: middle" />
							<div class="autocomplete" id="evalfaculty_name_auto_complete"></div>
							<input type="hidden" id="associated_evalfaculty" name="associated_evalfaculty" />
							<input type="button" class="button-sm" id="add_associated_evalfaculty" value="Add" style="vertical-align: middle" />
							<span class="content-small">(<strong>Example:</strong> <?php echo html_encode($_SESSION["details"]["lastname"].", ".$_SESSION["details"]["firstname"]); ?>)</span>
							<ul id="evalfaculty_list" class="menu" style="margin-top: 15px">
								<?php
								if (($PROCESSED["evaluation_evaluators"][0]["evaluator_type"] == "faculty") && is_array($PROCESSED["evaluation_evaluators"]) && !empty($PROCESSED["evaluation_evaluators"])) {
									foreach ($PROCESSED["evaluation_evaluators"] as $evaluator) {
										$proxy_id = (int) $evaluator["evaluator_value"];
										?>
										<li class="community" id="evalfaculty_<?php echo $proxy_id; ?>" style="cursor: move;"><?php echo get_account_data("fullname", $proxy_id); ?><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif" onclick="faculty_list.removeItem('<?php echo $proxy_id; ?>');" class="list-cancel-image" /></li>
										<?php
									}
								}
								?>
							</ul>
							<input type="hidden" id="evalfaculty_ref" name="evalfaculty_ref" value="" />
							<input type="hidden" id="evalfaculty_id" name="evalfaculty_id" value="" />
						</td>
					</tr>
					<?php
				}
			}
		} else {
			$organisation[$ENTRADA_USER->getActiveOrganisation()] = array("text" => fetch_organisation_title($ENTRADA_USER->getActiveOrganisation()), "value" => "organisation_" . $ENTRADA_USER->getActiveOrganisation(), "category" => true);

			switch ($options_for) {
				case "cohorts" : // Classes
					/**
					 * Cohorts.
					 */
					if ((isset($target_data["evaluation_target_cohorts"]))) {
						$associated_targets = explode(',', $target_data["evaluation_target_cohorts"]);
						if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
							foreach($associated_targets as $target_id) {
								if (strpos($target_id, "group") !== false) {
									if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
										$query = "	SELECT * FROM `groups`
													WHERE `group_id` = ".$db->qstr($group_id)."
													AND `group_type` = 'cohort'
													AND `group_active` = 1";
										$result	= $db->GetRow($query);
										if ($result) {
											$PROCESSED["associated_cohort_ids"][] = $group_id;
										}
									}
								}
							}
						}
					}

					$groups = $organisation;

					$query = "	SELECT a.*
								FROM `groups` AS a
								JOIN `group_organisations` AS b
								ON b.`group_id` = a.`group_id`
								WHERE a.`group_active` = '1'
								AND b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								AND a.`group_type` = 'cohort'
								ORDER BY a.`group_name` DESC";
					$groups_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
					if ($groups_results) {
						foreach ($groups_results as $group) {
							if (isset($PROCESSED["associated_cohort_ids"]) && is_array($PROCESSED["associated_cohort_ids"]) && in_array($group["group_id"], $PROCESSED["associated_cohort_ids"])) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}

							$groups[$ENTRADA_USER->getActiveOrganisation()]["options"][] = array("text" => $group["group_name"], "value" => "group_" . $group["group_id"], "checked" => $checked);
						}

						echo lp_multiple_select_popup("cohorts", $groups, array("title" => "Select Cohorts of Learners:", "submit_text" => "Close", "submit" => true));
					} else {
						echo display_notice("There are no cohorts of learners available.");
					}
				break;
				case "course_groups" :
					/**
					 * Course Groups
					 */
					if (isset($target_data["evaluation_target_course_groups"])) {
						$associated_targets = explode(',', $target_data["evaluation_target_course_groups"]);
						if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
							foreach($associated_targets as $target_id) {
								if (strpos($target_id, "cgroup") !== false) {
									if ($cgroup_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
										$query	= "	SELECT *
													FROM `course_groups`
													WHERE `cgroup_id` = ".$db->qstr($cgroup_id)."
													AND (`active` = '1')";
										$result	= $db->GetRow($query);
										if ($result) {
											$PROCESSED["associated_cgroup_ids"][] = $cgroup_id;
										}
									}
								}
							}
						}
					}

					$groups = $organisation;

					$query = "SELECT a.*, b.`course_name`, b.`course_code` FROM `course_groups` AS a 
								JOIN `courses` AS b 
								ON a.`course_id` = b.`course_id` 
								WHERE b.`organisation_id` = ".$db->qstr($ENTRADA_USER->getActiveOrganisation())."
								ORDER BY b.`course_name`, 
									LENGTH(a.`group_name`), 
									a.`group_name` ASC";
					$groups_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
					if ($groups_results) {
						$last_course_category = "";
						foreach ($groups_results as $group) {
							if ($last_course_category != $group["course_name"]) {
								$last_course_category = $group["course_name"];
								$groups[$group["course_id"]] = array("text" => $group["course_name"], "value" => "course_" . $group["course_id"], "category" => true);
							}
							if (isset($PROCESSED["associated_cgroup_ids"]) && is_array($PROCESSED["associated_cgroup_ids"]) && in_array($group["cgroup_id"], $PROCESSED["associated_cgroup_ids"])) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}

							$groups[$group["course_id"]]["options"][] = array("text" => $group["group_name"].($group["course_code"] ? " - ".$group["course_code"] : ""), "value" => "cgroup_" . $group["cgroup_id"], "checked" => $checked);
						}

						echo lp_multiple_select_popup("course_groups", $groups, array("title" => "Select Course Specific Small Groups:", "submit_text" => "Close", "submit" => true));
					} else {
						//echo display_notice("There are no small groups in the course you have selected.");
					}
				break;
				case "students" : // Students
					/**
					 * Learners
					 */
					if ((isset($target_data["evaluation_target_students"]))) {
						$associated_targets = explode(',', $target_data["evaluation_target_students"]);
						if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
							foreach($associated_targets as $target_id) {
								if (strpos($target_id, "student") !== false) {
									if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
										$query = "	SELECT a.*
													FROM `".AUTH_DATABASE."`.`user_data` AS a
													LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
													ON a.`id` = b.`user_id`
													WHERE a.`id` = ".$db->qstr($proxy_id)."
													AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
													AND b.`account_active` = 'true'
													AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
													AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
										$result	= $db->GetRow($query);
										if ($result) {
											$PROCESSED["associated_proxy_ids"][] = $proxy_id;
										}
									}
								}
							}
						}
					}

					$students = $organisation;

					$query = "	SELECT a.`id` AS `proxy_id`, a.`organisation_id`, b.`role`, CONCAT_WS(', ', a.`lastname`, a.`firstname`) AS `fullname`
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								WHERE b.`app_id` IN (".AUTH_APP_IDS_STRING.")
								AND a.`organisation_id` = " . $db->qstr($ENTRADA_USER->getActiveOrganisation()) . "
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")
								AND b.`group` = 'student'
								AND a.`grad_year` >= ".$db->qstr((fetch_first_year() - 4)).
								(($ENTRADA_USER->getGroup() == "student") ? " AND a.`id` = ".$db->qstr($ENTRADA_USER->getID()) : "")."
								GROUP BY a.`id`
								ORDER BY a.`grad_year` DESC, a.`lastname` ASC, a.`firstname` ASC";
					$student_results = $db->CacheGetAll(LONG_CACHE_TIMEOUT, $query);
					if ($student_results) {
						foreach ($student_results as $student) {
							if (isset($PROCESSED["associated_proxy_ids"]) && is_array($PROCESSED["associated_proxy_ids"]) && in_array($student["proxy_id"], $PROCESSED["associated_proxy_ids"])) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}

							$students[$ENTRADA_USER->getActiveOrganisation()]["options"][] = array("text" => $student["fullname"], "value" => "student_".$student["proxy_id"], "checked" => $checked);
						}

						echo lp_multiple_select_popup("students", $students, array("title" => "Select Individual Learners:", "submit_text" => "Close", "submit" => true));
					} else {
						echo display_notice("There are no students available.");
					}
				break;
				default :
					application_log("notice", "Unknown evaluation target filter type [" . $options_for . "] provided to evaluation targets API.");
				break;
			}
		}
	}
	
	public static function processTargets ($target_data, $PROCESSED = array()) {
		global $db;
		if (!isset($PROCESSED["eform_id"]) || !$PROCESSED["eform_id"]) {
			$PROCESSED["eform_id"] = 0;
		}

		if ((isset($target_data["form_id"]) && ($eform_id = clean_input($target_data["form_id"], "int"))) || (isset($target_data["eform_id"]) && ($eform_id = clean_input($target_data["eform_id"], "int"))) || (isset($PROCESSED["eform_id"]) && ($eform_id = clean_input($PROCESSED["eform_id"], "int")))) {
			$PROCESSED["eform_id"] = $eform_id;
			$query = "	SELECT a.*, b.`target_id`, b.`target_shortname`
						FROM `evaluation_forms` AS a
						LEFT JOIN `evaluations_lu_targets` AS b
						ON b.`target_id` = a.`target_id`
						WHERE a.`eform_id` = ".$db->qstr($eform_id)."
						AND a.`form_active` = '1'";
			$result = $db->GetRow($query);
			if ($result) {
				$evaluation_target_id = $result["target_id"];
				$evaluation_target_type = $result["target_shortname"];
			} else {
				add_error("The <strong>Evaluation Form</strong> that you selected is not currently available for use.");
			}
		}
		/**
		 * Processing for evaluation_targets table.
		 */
		switch ($evaluation_target_type) {
			case "course" :
				if (isset($target_data["course_ids"]) && is_array($target_data["course_ids"]) && !empty($target_data["course_ids"])) {
					foreach ($target_data["course_ids"] as $course_id) {
						$course_id = clean_input($course_id, "int");
						if ($course_id) {
							$query = "SELECT `course_id` FROM `courses` WHERE `course_id` = ".$db->qstr($course_id);
							$result = $db->GetRow($query);
							if ($result) {
									$PROCESSED["evaluation_targets"][] = array("target_value" => $result["course_id"], "target_type" => "course_id");
							}
						}
					}

					if (empty($PROCESSED["evaluation_targets"])) {
						add_error("You must select at least one <strong>course</strong> that you would like to have evaluated.");
					}
				} else {
					add_error("You must select <strong>which courses</strong> you would like to have evaluated.");
				}
			break;
			case "teacher" :
				if (isset($target_data["teacher_ids"]) && is_array($target_data["teacher_ids"]) && !empty($target_data["teacher_ids"])) {
					foreach ($target_data["teacher_ids"] as $proxy_id) {
						$proxy_id = clean_input($proxy_id, "int");
						if ($proxy_id) {
							$query = "	SELECT a.`id` AS `proxy_id`
										FROM `".AUTH_DATABASE."`.`user_data` AS a
										LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
										ON b.`user_id` = a.`id`
										WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
										AND (b.`group` = 'faculty' OR 
											(b.`group` = 'resident' AND b.`role` = 'lecturer')
										)
										AND a.`id` = ".$db->qstr($proxy_id);
							$result = $db->GetRow($query);
							if ($result) {
									$PROCESSED["evaluation_targets"][] = array("target_value" => $result[$proxy_id], "target_type" => "proxy_id");
							}
						}
					}

					if (empty($PROCESSED["evaluation_targets"])) {
						add_error("You must select at least one <strong>teacher</strong> that you would like to have evaluated.");
					}
				} else {
					add_error("You must select <strong>which teachers</strong> you would like to have evaluated.");
				}
			break;
			case "peer" :
			case "student" :
				/**
				 * Cohorts.
				 */
				if ((isset($target_data["evaluation_target_cohorts"]))) {
					$associated_targets = explode(',', $target_data["evaluation_target_cohorts"]);
					if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
						foreach($associated_targets as $target_id) {
							if (strpos($target_id, "group") !== false) {
								if ($group_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
									$query = "	SELECT *
												FROM `groups`
												WHERE `group_id` = ".$db->qstr($group_id)."
												AND `group_type` = 'cohort'
												AND `group_active` = 1";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_cohort_ids"][] = $group_id;
										$PROCESSED["evaluation_targets"][] = array("target_value" => $group_id, "target_type" => "group_id");
									}
								}
							}
						}
					}
				} else {
					$query = "	SELECT * FROM `groups` AS a
								JOIN `evaluation_targets` AS b
								ON b.`target_value` = a.`group_id`
								AND b.`target_type` = 'group_id'
								WHERE `group_type` = 'cohort'
								AND `group_active` = 1";
					$results	= $db->GetAll($query);
					if ($results) { 
						foreach ($results as $result) {
							$PROCESSED["associated_cohort_ids"][] = $result["group_id"];
							$PROCESSED["evaluation_targets"][] = array("target_value" => $result["group_id"], "target_type" => "group_id");
						}
					}
				}
				/**
				 * Course Groups
				 */
				if (isset($target_data["evaluation_target_course_groups"])) {
					$associated_targets = explode(',', $target_data["evaluation_target_course_groups"]);
					if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
						foreach($associated_targets as $target_id) {
							if (strpos($target_id, "cgroup") !== false) {
								if ($cgroup_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
									$query = "	SELECT *
												FROM `course_groups`
												WHERE `cgroup_id` = ".$db->qstr($cgroup_id)."
												AND (`active` = '1')";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_cgroup_ids"][] = $cgroup_id;
										$PROCESSED["evaluation_targets"][] = array("target_value" => $cgroup_id, "target_type" => "cgroup_id");
									}
								}
							}
						}
					}
				} else {
					$query = "	SELECT * FROM `course_groups` AS a
								JOIN `evaluation_targets` AS b
								ON b.`target_value` = a.`cgroup_id`
								AND b.`target_type` = 'cgroup_id'
								WHERE `active` = 1";
					$results	= $db->GetAll($query);
					if ($results) {
						foreach ($results as $result) {
							$PROCESSED["associated_cgroup_ids"][] = $result["cgroup_id"];
							$PROCESSED["evaluation_targets"][] = array("target_value" => $result["cgroup_id"], "target_type" => "cgroup_id");
						}
					}
				}
				/**
				 * Learners
				 */
				if ((isset($target_data["evaluation_target_students"]))) {
					$associated_targets = explode(',', $target_data["evaluation_target_students"]);
					if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
						foreach($associated_targets as $target_id) {
							if (strpos($target_id, "student") !== false) {
								if ($proxy_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
									$query = "	SELECT a.*
												FROM `".AUTH_DATABASE."`.`user_data` AS a
												LEFT JOIN `".AUTH_DATABASE."`.`user_access` AS b
												ON a.`id` = b.`user_id`
												WHERE a.`id` = ".$db->qstr($proxy_id)."
												AND b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
												AND b.`account_active` = 'true'
												AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
												AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_proxy_ids"][] = $proxy_id;
										$PROCESSED["evaluation_targets"][] = array("target_value" => $proxy_id, "target_type" => "proxy_id");
									}
								}
							}
						}
					}
				} elseif ($target_data["evaluation_id"]) {
					$query = "	SELECT a.*
								FROM `".AUTH_DATABASE."`.`user_data` AS a
								JOIN `".AUTH_DATABASE."`.`user_access` AS b
								ON a.`id` = b.`user_id`
								JOIN `evaluation_targets` AS c
								ON a.`id` = c.`target_value`
								AND c.`target_type` = 'proxy_id'
								WHERE b.`app_id` = ".$db->qstr(AUTH_APP_ID)."
								AND c.`evaluation_id` = ".$target_data["evaluation_id"]."
								AND b.`account_active` = 'true'
								AND (b.`access_starts` = '0' OR b.`access_starts` <= ".$db->qstr(time()).")
								AND (b.`access_expires` = '0' OR b.`access_expires` > ".$db->qstr(time()).")";
					$results	= $db->GetAll($query);
					if ($results) { 
						foreach ($results as $result) {
							$PROCESSED["associated_proxy_ids"][] = $result["proxy_id"];
							$PROCESSED["evaluation_targets"][] = array("target_value" => $result["proxy_id"], "target_type" => "proxy_id");
						}
					}
				}
			break;
			case "rotation_core" :
				/**
				 * Core Rotations
				 */
				if ((isset($target_data["evaluation_target_rotations"]))) {
					$associated_targets = explode(',', $target_data["evaluation_target_rotations"]);
					if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
						foreach($associated_targets as $target_id) {
							if (strpos($target_id, "rotation") !== false) {
								if ($rotation_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
									$query = "	SELECT * FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`
												WHERE `rotation_id` = ".$db->qstr($rotation_id)."
												AND `rotation_id` != ".$db->qstr(MAX_ROTATION);
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_rotation_ids"][] = $rotation_id;
									}
								}
							}
						}
					}
				}
			break;
			case "preceptor" :
				/**
				 * Rotations
				 */
				if ((isset($target_data["evaluation_target_rotations"]))) {
					$associated_targets = explode(',', $target_data["evaluation_target_rotations"]);
					if ((isset($associated_targets)) && (is_array($associated_targets)) && (count($associated_targets))) {
						foreach($associated_targets as $target_id) {
							if (strpos($target_id, "rotation") !== false) {
								if ($rotation_id = clean_input(preg_replace("/[a-z_]/", "", $target_id), array("trim", "int"))) {
									$query = "	SELECT *
												FROM `".CLERKSHIP_DATABASE."`.`global_lu_rotations`
												WHERE `rotation_id` = ".$db->qstr($rotation_id);
									$result	= $db->GetRow($query);
									if ($result) {
										$PROCESSED["associated_rotation_ids"][] = $rotation_id;
									}
								}
							}
						}
					}
				}

			break;
			default :
				add_error("The form type you have selected is currently unavailable. The system administrator has been notified of this issue, please try again later.");

				application_log("error", "Unaccounted for target_shortname [".$evaluation_target_type."] encountered. An update to add.inc.php is required.");
			break;
		}
		return $PROCESSED;
	}
}

?>