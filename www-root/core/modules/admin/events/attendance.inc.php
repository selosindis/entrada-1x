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
 * This file is used to modify content (i.e. goals, objectives, file resources
 * etc.) within a learning event from the entrada.events table.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_EVENTS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed('eventcontent', 'update', false)) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/admin/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] does not have access to this module [".$MODULE."]");
} else {

	?>
	<script type="text/javascript">
		var EVENT_LIST_STATIC_TOTAL_DURATION = true;
	</script>
	<?php
	if ($EVENT_ID) {
		$query		= "	SELECT a.*, b.`organisation_id`
						FROM `events` AS a
						LEFT JOIN `courses` AS b
						ON b.`course_id` = a.`course_id`
						WHERE a.`event_id` = ".$db->qstr($EVENT_ID);
		$event_info	= $db->GetRow($query);
		if ($event_info) {
			if (!$ENTRADA_ACL->amIAllowed(new EventContentResource($event_info["event_id"], $event_info["course_id"], $event_info["organisation_id"]), "update")) {
				application_log("error", "Someone attempted to modify content for an event [".$EVENT_ID."] that they were not the coordinator for.");
				echo 'here';
				header("Location: ".ENTRADA_URL."/admin/".$MODULE);
				exit;
			} else {
				$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/events?".replace_query(array("section" => "content", "id" => $EVENT_ID)), "title" => "Event Content");
				switch ($STEP) {
					case 2:			
						ob_clean();						
						
						if (!isset($_POST["attending"])) {
							echo htmlspecialchars(json_encode(array('error'=>'No value supplied for attending.')), ENT_NOQUOTES);
							exit;
						}
						
						if (isset($_POST["proxy_id"]) && $tmp_input = (int)$_POST["proxy_id"]) {
							$query = "	SELECT `id` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($tmp_input);
							$proxy_id = (int)$db->GetOne($query);
						} elseif(isset($_POST["number"]) && $tmp_input = (int)$_POST["number"]) {
							$query = "	SELECT `id` FROM `".AUTH_DATABASE."`.`user_data` WHERE `number` = ".$db->qstr($tmp_input);
							$proxy_id = (int)$db->GetOne($query);
						}
												
						$attending = (int)$_POST["attending"];						
						
						if ($proxy_id) {
							
							if (!events_fetch_event_audience_for_user($EVENT_ID,$proxy_id)) {
								echo htmlspecialchars(json_encode(array('error'=>'This user is not an audience member for this event.')), ENT_NOQUOTES);
								exit;									
							}
							
							/**
							 * When using the student number, there's no reference to a checkbox to get the attending value. 
							 * If 2 is passed it will just set the value of attending to the opposite of what it currentlty is. 
							 */
							if ($attending == 2) {
								$query = "SELECT * FROM `event_attendance` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `proxy_id` = ".$db->qstr($proxy_id);
								$attending = !(int)$db->GetRow($query);
							}				
							
							if ($attending) {
								$attendance_record = array(
															"event_id"=>$EVENT_ID,
															"proxy_id"=>$proxy_id,
															"updated_date"=>time(),
															"updated_by"=>$ENTRADA_USER->getId()
															);
								if ($db->AutoExecute("event_attendance",$attendance_record,"INSERT")) {
									echo htmlspecialchars(json_encode(array('success'=>'Successfully added attendance.','proxy_id'=>$proxy_id)), ENT_NOQUOTES);
									exit;												
								} else {
									echo htmlspecialchars(json_encode(array('error'=>'Error occurred updating record.')), ENT_NOQUOTES);
									exit;												
								}
							} else {
								$query = "DELETE FROM `event_attendance` WHERE `event_id` = ".$db->qstr($EVENT_ID)." AND `proxy_id` = ".$db->qstr($proxy_id);
								if ($db->Execute($query)) {
									echo htmlspecialchars(json_encode(array('success'=>'Successfully removed attendance.','proxy_id'=>$proxy_id)), ENT_NOQUOTES);
									exit;												
								} else {
									echo htmlspecialchars(json_encode(array('error'=>'Error occurred updating record.')), ENT_NOQUOTES);
									exit;												
								}								
							}
						} else {
							echo htmlspecialchars(json_encode(array('error'=>'Unable to locate user.')), ENT_NOQUOTES);
							exit;														
						}
						exit;
						break;
					default:
						break;
				}
				
				$audience = events_fetch_event_audience_attendance($EVENT_ID);
				
				if (isset($_GET["download"]) && trim($_GET["download"]) == "csv") {
					if ($audience) {
						ob_clean();
						$output = "";
						foreach($audience as $learner) {
							$output .= $learner["number"].','.$learner["lastname"].','.$learner["firstname"].','.($learner["has_attendance"]?'Present':'Absent')."\n";
						}
						$file_title = "attendance-for-event-".$event_info["event_id"]."-".time().".csv";
						header("Pragma: public");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Content-Type: text/csv");
						header("Content-Disposition: inline; filename=\"".$file_title."\"");
						header("Content-Length: ".@strlen($output));
						header("Content-Transfer-Encoding: binary\n");

						echo $output;
						exit;						
						
					}
				}
				events_subnavigation($event_info,'attendance');
				echo "<div class=\"content-small\">".fetch_course_path($event_info["course_id"])."</div>\n";
				echo "<h1 class=\"event-title\">".html_encode($event_info["event_title"])."</h1>\n";

				if ($SUCCESS) {
					fade_element("out", "display-success-box");
					echo display_success();
				}

				if ($NOTICE) {
					echo display_notice();
				}

				if ($ERROR) {
					echo display_error();
				}
				?>
				<a name="event-attendance-section"></a>
				<h2 title="Event Resources Section">Event Attendance</h2>
				<div id="event-attendance-section">					
						<div style="float:right;margin-bottom:5px;">
							<label for="number">Student Number:</label> <input type="text" name="number" id="number"/>
						</div>
						<table class="tableList" cellspacing="0" summary="List of Attached Files">
							<colgroup>
								<col class="modified"/>
								<col class="title"/>
								<col class="title"/>
							</colgroup>
							<thead>
								<tr>
									<td class="modified">&nbsp;</td>
									<td class="title">Last Name</td>
									<td class="title">First Name</td>
								</tr>
							</thead>
							<tbody>
								<?php	if ($audience) {
											foreach ($audience as $learner) {?>
								<tr>
									<td><input type="checkbox" class="attendance-check" value="<?php echo $learner["id"];?>" id="learner-<?php echo $learner["id"];?>"<?php echo $learner["has_attendance"]?' checked="checked"':'';?>/></td>
									<td><?php echo $learner["lastname"];?></td>
									<td><?php echo $learner["firstname"];?></td>
								</tr>
									<?php	} 										
									}else { ?>
								<tr>
									<td colspan="3"><?php echo display_notice(array("There is no audience associated with this event."));?></td>
								</tr>										
							<?php	}
										?>
							</tbody>
						</table>
					
						<input type="button" class="button" value="Download CSV" onclick="window.location = '<?php echo ENTRADA_URL."/admin/events?".replace_query(array("section" => "attendance", "id" => $EVENT_ID,"download"=>"csv"));?>'"/>
				</div>
				
				<script type="text/javascript">
				$$('select.ed_select_off').each(function(el) {
					$(el).disabled = true;
					$(el).fade({ duration: 0.3, to: 0.25 });
				});
				
				jQuery('.attendance-check').click(function(){
					var proxy_id = jQuery(this).val();
					var attending;
					if(jQuery(this).is(':checked')){
						attending = 1;
					}else{
						attending = 0;
					}
					jQuery.ajax({
						type: "POST",
						url: "<?php echo ENTRADA_URL;?>/admin/events?section=attendance&id=<?php echo $EVENT_ID;?>&step=2",
						data: "proxy_id="+proxy_id+"&attending="+attending,
						success: function(data){
							try{
								var result = jQuery.parseJSON(data);
								if(result.error){
									alert(result.error);
								}
							}catch(e){
								
							}
						}
					});
				});
				jQuery('#number').keydown(function(e){
						if(e.keyCode == 13){
							var number = jQuery(this).val();
							var attending;
							jQuery.ajax({
								type: "POST",
								url: "<?php echo ENTRADA_URL;?>/admin/events?section=attendance&id=<?php echo $EVENT_ID;?>&step=2",
								data: "number="+number+"&attending=2",
								success: function(data){
									try{
										var result = jQuery.parseJSON(data);
										if(result.error){
											alert(result.error);
										}else{
											jQuery('#number').val('');
											jQuery('#learner-'+result.proxy_id).toggleCheck();
										}
										jQuery('#number').focus();
									}catch(e){

									}
								}
							});
							return false;
						}
					});		
					
					  jQuery.fn.toggleCheck = function() {
							return this.each(function() {
								this.checked = !this.checked;
							});
						};
				</script>
				<?php			
			}
		} else {
			$ERROR++;
			$ERRORSTR[] = "In order to edit a event you must provide a valid event identifier. The provided ID does not exist in this system.";

			echo display_error();

			application_log("notice", "Failed to provide a valid event identifer when attempting to edit a event.");
		}
	} else {
		$ERROR++;
		$ERRORSTR[] = "In order to edit a event you must provide the events identifier.";

		echo display_error();

		application_log("notice", "Failed to provide event identifer when attempting to edit a event.");
	}
}