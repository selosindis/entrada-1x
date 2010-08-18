<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_MANAGE_USER_STUDENTS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('mspr', 'create',true) || $user_record["group"] != "student") {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";
	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
}  else {
	
	require_once("Models/MSPRs.class.php");
	$PROXY_ID					= $user_record["id"];
	$user = User::get($user_record["id"]);
	
	process_mspr_admin($user);
	
	$PAGE_META["title"]			= "MSPR";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";


	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/admin/users/manage/students?section=mspr&id=".$PROXY_ID, "title" => "MSPR");

	$PROCESSED		= array();
	$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/ActiveDataEntryProcessor.js'></script>";
	$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/ActiveApprovalProcessor.js'></script>";
	
	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $proxy_id => $result) {
			if ($proxy_id != $_SESSION["details"]["id"]) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	
	$mspr = MSPR::get($user);
	
	if (isset($_GET['generate'])){
		header("Location: ".ENTRADA_URL."/admin/users/manage/students?section=mspr&id=".$PROXY_ID);
	}
	
	$clerkship_core_completed = $mspr["Clerkship Core Completed"];
	$clerkship_core_pending = $mspr["Clerkship Core Pending"];
	$clerkship_elective_completed = $mspr["Clerkship Electives Completed"];
	$clinical_evaluation_comments = $mspr["Clinical Performance Evaluation Comments"];
	$critical_enquiry = $mspr["Critical Enquiry"];
	$student_run_electives = $mspr["Student-Run Electives"];
	$observerships = $mspr["Observerships"];
	$international_activities = $mspr["International Activities"];
	$internal_awards = $mspr["Internal Awards"];
	$external_awards = $mspr["External Awards"];
	$studentships = $mspr["Studentships"];
	$contributions = $mspr["Contributions to Medical School"];
	$leaves_of_absence = $mspr["Leaves of Absence"];
	$formal_remediations = $mspr["Formal Remediation Received"];
	$disciplinary_actions = $mspr["Disciplinary Actions"];
	$community_health_and_epidemiology = $mspr["Community Health and Epidemiology"];
	$research_citations = $mspr["Research"];
				
	$year = $user->getGradYear();
	$class_data = MSPRClassData::get($year);
	
	$mspr_close = $mspr->getClosedTimestamp();
	
	if (!$mspr_close) { //no custom time.. use the class default
		$mspr_close = $class_data->getClosedTimestamp();	
	}

	$is_closed = $mspr->isClosed();
	
	display_status_messages();
	add_mspr_management_sidebar();
	
?>
 
<h1>Medical School Performance Report<?php echo ($mspr->isAttentionRequired()) ? ": Attention Required" : ""; ?></h1> 

<?php 
	if ($is_closed) {
		?>
<div class="display-notice"><strong>Note: </strong>This MSPR is now <strong>closed</strong> to student submissions. (Deadline was <?php echo date("F j, Y \a\\t g:i a",$mspr_close); ?>.) You may continue to approve, unapprove, or reject submissions, however students are unable to submit new data.</div>
		<?php
	} elseif ($mspr_close) {
		?>
<div class="display-notice"><strong>Note: </strong>The student submission deadline is <?php echo date("F j, Y \a\\t g:i a",$mspr_close); ?>. You may continue to approve, unapprove, or reject submissions after this date, however students will be unable to submit new data.</div>
		<?php
	}
?>

<div class="mspr-tree">

	<a href="#" onclick='document.fire("CollapseHeadings:expand-all");'>Expand All</a> / <a href="#" onclick='document.fire("CollapseHeadings:collapse-all");'>Collapse All</a>

	<h2 title="Information Requiring Approval">Information Requiring Approval</h2>
	<div id="information-requiring-approval">
	
		<div class="section">
			<h3 title="Contributions to Medical School" class="collapsable<?php echo ($contributions->isAttentionRequired()) ? "" : " collapsed"; ?>">Contributions to Medical School</h3>
			<div id="contributions-to-medical-school">
				<?php echo display_contributions_admin($contributions); ?>
			</div>
			<script language="javascript">
				var contributions = new ActiveApprovalProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=contributions',
					data_destination: $('contributions-to-medical-school'),
					action_form_selector: '#contributions-to-medical-school .entry form',
					section: "contributions"
				});
			</script>
		</div>
		
		<div class="section">
			<h3 title="Critical Enquiry" class="collapsable<?php echo ($critical_enquiry && $critical_enquiry->isAttentionRequired()) ? "" : " collapsed"; ?>">Critical Enquiry</h3>
			<div id="critical-enquiry">
				<div id="critical_enquiry"><?php echo display_critical_enquiry_admin($critical_enquiry); ?></div>
				<script language="javascript">
				var critical_enquiry = new ActiveApprovalProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=critical_enquiry',
					data_destination: $('critical_enquiry'),
					action_form_selector: '#critical_enquiry .entry form',
					section: "critical_enquiry"
				});
				
				</script>
			</div>
		</div>
		
		<div class="section">
			<h3 title="Community Health and Epidemiology" class="collapsable<?php echo ($community_health_and_epidemiology && $community_health_and_epidemiology->isAttentionRequired()) ? "" : " collapsed"; ?>">Community Health and Epidemiology</h3>
			<div id="community-health-and-epidemiology">
				<div id="community_health_and_epidemiology"><?php echo display_community_health_and_epidemiology_admin($community_health_and_epidemiology); ?></div>
				<script language="javascript">
				var community_health_and_epidemiology = new ActiveApprovalProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=community_health_and_epidemiology',
					data_destination: $('community_health_and_epidemiology'),
					action_form_selector: '#community_health_and_epidemiology .entry form',
					section: "community_health_and_epidemiology"
				});
				
				</script>
			</div>
		</div>
		
		<div class="section">
			<h3 title="Research" class="collapsable<?php echo ($research_citations->isAttentionRequired()) ? "" : " collapsed"; ?>">Research</h3>
			<div id="research">
				<?php echo display_research_citations_admin($research_citations); ?>
			</div>
			<script language="javascript">
				var research_citations = new ActiveApprovalProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=research_citations',
					data_destination: $('research'),
					action_form_selector: '#research .entry form',
					section: "research"
				});
			
			</script>
		</div>
		
		<div class="section">
			<h3 title="External Awards" class="collapsable<?php echo ($external_awards->isAttentionRequired()) ? "" : " collapsed"; ?>">External Awards</h3>
			<div id="external-awards">
				<div id="external_awards"><?php echo display_external_awards_admin($external_awards); ?></div>
				<script language="javascript">
					var external_awards = new ActiveApprovalProcessor({
						url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=external_awards',
						data_destination: $('external_awards'),
						action_form_selector: '#external_awards .entry form',
						section: "external_awards"
					});
				
				</script>
			</div>
		</div>
	</div>

	<h2 title="Required Information Section">Information Requiring Entry</h2>
	<div id="required-information-section">
	
		<div class="section">
			<h3 title="Clinical Performance Evaluation Comments Section" class="collapsable collapsed">Clinical Performance Evaluation Comments</h3>
			<div id="clinical-performance-evaluation-comments-section">
			
			<div id="add_clineval_link" style="float: right;">
				<ul class="page-action">
					<li><a id="add_clineval" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Clinical Performance Evaluation Comment</a></li>
				</ul>
			</div>
			<div class="clear">&nbsp;</div>
			<form id="add_clineval_form" name="add_clineval_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" style="display:none;" >
				<input type="hidden" name="user_id" value="<?php echo $PROXY_ID; ?>"></input>
				<table class="mspr_form">
					<colgroup>
						<col width="3%"></col>
						<col width="25%"></col>
						<col width="72%"></col>
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="submit" class="button" name="action" value="Add" />
								<div id="hide_clineval_link" style="display:inline-block;">
									<ul class="page-action-cancel">
										<li><a id="hide_clineval" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Comment ]</a></li>
									</ul>
								</div>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
						<td>&nbsp;</td>
						<td><label class="form-required" for="source">Source:</label></td>
						<td><input type="text" name="source"></input></td>
						</tr>	
						<tr>
						<td>&nbsp;</td>
						<td><label class="form-required" for="text">Comment:</label></td>
						<td><textarea name="text"></textarea></td>
						</tr>
					</tbody>
				
				</table>	
			
				<div class="clear">&nbsp;</div>
			</form>
		
		
			<div id="clinical_performance_eval_comments"><?php echo display_clineval_admin($clinical_evaluation_comments); ?></div>
		
			<script language="javascript">
		
			var clineval_comments = new ActiveDataEntryProcessor({
				url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=clineval',
				data_destination: $('clinical_performance_eval_comments'),
				new_form: $('add_clineval_form'),
				remove_forms_selector: '.remove_clineval_form',
				new_button: $('add_clineval_link'),
				hide_button: $('hide_clineval')
				
			});
		
			</script>
			</div>
		</div>
		
		<div class="section">
			<h3 title="Summer Studentships" class="collapsable collapsed">Summer Studentships</h3>
			<div id="summer-studentships">
			
			<div id="add_studentship_link" style="float: right;">
				<ul class="page-action">
					<li><a id="add_studentship" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&show=studentship_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Studentship</a></li>
				</ul>
			</div>
			<div class="clear">&nbsp;</div>
			
			
			<form id="add_studentship_form" name="add_studentship_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" style="display:none;" >
				<input type="hidden" name="user_id" value="<?php echo $PROXY_ID; ?>"></input>
		
				<table class="mspr_form">
					<colgroup>
						<col width="3%"></col>
						<col width="25%"></col>
						<col width="72%"></col>
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="submit" name="action" value="Add" />
								<div id="hide_studenstship_link" style="display:inline-block;">
									<ul class="page-action-cancel">
										<li><a id="hide_studentship" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Studentship ]</a></li>
									</ul>
								</div>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
						<td>&nbsp;</td>
						<td><label class="form-required" for="title">Title:</label></td>
						<td><input type="text" name="title"></input></td>
						</tr>	
						<tr>
						<td>&nbsp;</td>
						<td><label class="form-required" for="year">Year Awarded:</label></td>
						<td><select name="year">
							<?php 
							
							$cur_year = (int) date("Y");
							$start_year = $cur_year - 4;
							$end_year = $cur_year + 4;
							
							for ($opt_year = $start_year; $opt_year <= $end_year; ++$opt_year) {
									echo build_option($opt_year, $opt_year, $opt_year == $cur_year);
							}
							
							?>
							</select></td>
						</tr>
					</tbody>
				
				</table>	
			
				<div class="clear">&nbsp;</div>
			</form>
			
			<div id="studentships"><?php echo display_studentships_admin($studentships); ?></div>
			</div>
			<script language="javascript">
			var studentships = new ActiveDataEntryProcessor({
				url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=studentships',
				data_destination: $('studentships'),
				new_form: $('add_studentship_form'),
				remove_forms_selector: '.remove_studentship_form',
				new_button: $('add_studentship_link'),
				hide_button: $('hide_studentship')
		
			});
			
			</script>
		</div>

		<div class="section">

			<h3 title="International Activities" class="collapsable collapsed">International Activities</h3>
			<div id="international-activities">
				<script>
					document.observe("dom:loaded",function() {
						$('int_act_start').observe('focus',function(e) {
							showCalendar('',this,this,null,null,0,30,1);
						}.bind($('int_act_start')));
						$('int_act_end').observe('focus',function(e) {
							showCalendar('',this,this,null,null,0,30,1);
						}.bind($('int_act_end')));
					});
				</script>
				
					<div id="add_int_act_link" style="float: right;<?php if ($show_int_act_form) { echo "display:none;"; }   ?>">
					<ul class="page-action">
						<li><a id="add_int_act" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&show=int_act_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Activity</a></li>
					</ul>
				</div>
				
				<div class="clear">&nbsp;</div>
				<form id="add_int_act_form" name="add_int_act_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_int_act_form) { echo "style=\"display:none;\""; }   ?> >
					<input type="hidden" name="student_id" value="<?php echo $user->getID(); ?>"></input>
					<table class="mspr_form">
						<colgroup>
							<col width="3%"></col>
							<col width="25%"></col>
							<col width="25%"></col>
							<col width="47%"></col>
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="4">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" name="action" class="button" value="Add" />
									<div id="hide_int_act_link" style="display:inline-block;">
										<ul class="page-action-cancel">
											<li><a id="hide_int_act" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding International Activity ]</a></li>
										</ul>
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="title">Title:</label></td>
	 							<td><input name="title"></input></td><td><span class="content-small"><strong>Example:</strong> Geriatrics Observership</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="site">Site:</label></td>
								<td><input name="site"></input></td><td><span class="content-small"><strong>Example:</strong> Tokyo Metropolitan Hospital</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="location">Location:</label></td>
								<td><input name="location"></input></td><td><span class="content-small"><strong>Example:</strong> Tokyo, Japan</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="start">Start Date:</label></td>
								<td>
									<input type="text" name="start" id="int_act_start"></input></td><td><span class="content-small"><strong>Format:</strong> yyyy-mm-dd</span>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="end">End Date:</label></td>
								<td>
									<input type="text" name="end" id="int_act_end"></input></td><td>
								</td>
							</tr>
						</tbody>
					
					</table>	
				
					<div class="clear">&nbsp;</div>
				</form>
				<div id="int_acts"><?php echo display_international_activities_admin($international_activities); ?></div>
			
				<script language="javascript">
				var int_acts = new ActiveDataEntryProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=int_acts',
					data_destination: $('int_acts'),
					new_form: $('add_int_act_form'),
					remove_forms_selector: '.remove_international_activity_form',
					new_button: $('add_int_act_link'),
					hide_button: $('hide_int_act')
			
				});
				
				</script>
			</div>
		</div>
		
		<div class="section">
			<h3 title="Observerships" class="collapsable collapsed">Observerships</h3>
			<div id="observerships">
				<script>
					document.observe("dom:loaded",function() {
						$('observership_start').observe('focus',function(e) {
							showCalendar('',this,this,null,null,0,30,1);
						}.bind($('observership_start')));
						$('observership_end').observe('focus',function(e) {
							showCalendar('',this,this,null,null,0,30,1);
						}.bind($('observership_end')));
					});
				</script>
				
				<div id="add_observership_link" style="float: right;<?php if ($show_observership_form) { echo "display:none;"; }   ?>">
					<ul class="page-action">
						<li><a id="add_observership" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&show=observership_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Observership</a></li>
					</ul>
				</div>
				
				<div class="clear">&nbsp;</div>
				<form id="add_observership_form" name="add_observership_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_observership_form) { echo "style=\"display:none;\""; }   ?> >
					<input type="hidden" name="student_id" value="<?php echo $user->getID(); ?>"></input>
					<table class="mspr_form">
						<colgroup>
							<col width="3%"></col>
							<col width="25%"></col>
							<col width="72%"></col>
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" name="action" class="button" value="Add" />
									<div id="hide_observership_link" style="display:inline-block;">
										<ul class="page-action-cancel">
											<li><a id="hide_observership" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Observership ]</a></li>
										</ul>
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="title">Title/Discipline:</label></td>
	 							<td><input name="title"></input> <span class="content-small"><strong>Example:</strong> Family Medicine</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="site">Site:</label></td>
								<td><input name="site"></input> <span class="content-small"><strong>Example:</strong> Kingston General Hospital</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="location">Location:</label></td>
								<td><input name="location" value="Kingston, ON"></input> <span class="content-small"><strong>Example:</strong> Kingston, ON</span></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="start">Start Date:</label></td>
								<td>
									<input type="text" name="start" id="observership_start"></input> <span class="content-small"><strong>Format:</strong> yyyy-mm-dd</span>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="end">End Date:</label></td>
								<td>
									<input type="text" name="end" id="observership_end"></input>
								</td>
							</tr>
						</tbody>
					
					</table>	
				
					<div class="clear">&nbsp;</div>
				</form>
				<div id="observerships"><?php echo display_observerships_admin($observerships); ?></div>
			
				<script language="javascript">
				var observerships = new ActiveDataEntryProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=observerships',
					data_destination: $('observerships'),
					new_form: $('add_observership_form'),
					remove_forms_selector: '.remove_observership_form',
					new_button: $('add_observership_link'),
					hide_button: $('hide_observership')
			
				});
				
				</script>
			</div>
		</div>
		
		<div class="section">

			<h3 title="Student-Run Electives" class="collapsable collapsed">Student-Run Electives</h3>
			<div id="student-run-electives">
	
				<div id="add_student_run_elective_link" style="float: right;<?php if ($show_student_run_elective_form) { echo "display:none;"; }   ?>">
					<ul class="page-action">
						<li><a id="add_student_run_elective" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&show=student_run_elective_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Student Run Elective</a></li>
					</ul>
				</div>
				
				<div class="clear">&nbsp;</div>
				<form id="add_student_run_elective_form" name="add_student_run_elective_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_student_run_elective_form) { echo "style=\"display:none;\""; }   ?> >
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<table class="mspr_form">
						<colgroup>
							<col width="3%"></col>
							<col width="25%"></col>
							<col width="72%"></col>
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" name="action" class="button" value="Add" />
									<div id="hide_student_run_elective_link" style="display:inline-block;">
										<ul class="page-action-cancel">
											<li><a id="hide_student_run_elective" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Student-Run Elective ]</a></li>
										</ul>
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="group_name">Group Name:</label></td>
								<td><input name="group_name"></input></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="university">University:</label></td>
								<td><input name="university" value="Queen's University"></input></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="location">Location:</label></td>
								<td><input name="location" value="Kingston, ON"></input></td>
							</tr>	
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="start">Start:</label></td>
								<td>
									<select name="start_month">
									<?php
									echo build_option("","Month",true);
										
									for($month_num = 1; $month_num <= 12; $month_num++) {
										echo build_option($month_num, getMonthName($month_num));
									}
									?>
									</select>
									<select name="start_year">
									<?php 
									$cur_year = (int) date("Y");
									$start_year = $cur_year - 6;
									$end_year = $cur_year + 4;
									
									for ($opt_year = $start_year; $opt_year <= $end_year; ++$opt_year) {
											echo build_option($opt_year, $opt_year, $opt_year == $cur_year);
									}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><label class="form-required" for="end">End:</label></td>
								<td>
									<select name="end_month">
									<?php
									echo build_option("","Month",true);
										
									for($month_num = 1; $month_num <= 12; $month_num++) {
										echo build_option($month_num, getMonthName($month_num));
									}
									?>
									</select>
									<select name="end_year">
									<?php 
									echo build_option("","Year",true);
									$cur_year = (int) date("Y");
									$start_year = $cur_year - 6;
									$end_year = $cur_year + 4;
									
									for ($opt_year = $start_year; $opt_year <= $end_year; ++$opt_year) {
											echo build_option($opt_year, $opt_year, false);
									}
									?>
									</select>
								</td>
							</tr>
						</tbody>
					
					</table>	
				
					<div class="clear">&nbsp;</div>
				</form>
				<div id="student_run_electives"><?php echo display_student_run_electives_admin($student_run_electives); ?></div>
			
				<script language="javascript">
				var student_run_electives = new ActiveDataEntryProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=student_run_electives',
					data_destination: $('student_run_electives'),
					new_form: $('add_student_run_elective_form'),
					remove_forms_selector: '.remove_student_run_elective_form',
					new_button: $('add_student_run_elective_link'),
					hide_button: $('hide_student_run_elective')
			
				});
				
				</script>
			</div>
		</div>
		
		<div class="section">
			<h3 title="Internal Awards" class="collapsable collapsed">Internal Awards</h3>
			<div id="internal-awards">
		
				<div id="add_internal_award_link" style="float: right;">
					<ul class="page-action">
						<li><a id="add_internal_award" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Internal Award</a></li>
					</ul>
				</div>
			
				<div class="clear">&nbsp;</div>
				<form id="add_internal_award_form" name="add_internal_award_form" action="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" style="display:none;" >
					<input type="hidden" name="user_id" value="<?php echo $user->getID(); ?>"></input>
					<table class="mspr_form">
						<colgroup>
							<col width="3%"></col>
							<col width="25%"></col>
							<col width="72%"></col>
						</colgroup>
						<tfoot>
							<tr>
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
									<input type="submit" name="action" class="button" value="Add" />
									<div id="hide_internal_award_link" style="display:inline-block;">
										<ul class="page-action-cancel">
											<li><a id="hide_internal_award" href="<?php echo ENTRADA_URL; ?>/admin/users/manage/students?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Internal Award ]</a></li>
										</ul>
									</div>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
							<td>&nbsp;</td>
							<td><label class="form-required" for="title">Title:</label></td>
							<td><select name="title">
								<?php 
									$query		= "SELECT * FROM `student_awards_internal_types` where `disabled` = 0 order by `title` asc";
									$results	= $db->GetAll($query);
									if ($results) {
										foreach ($results as $result) {
											echo build_option($result['id'], clean_input($result["title"], array("notags", "specialchars")));
										}
									}
								?>
								</select></td>
							</tr>	
							<tr>
							<td>&nbsp;</td>
							<td><label class="form-required" for="year">Year Awarded:</label></td>
							<td><select name="year">
								<?php 
								
								$cur_year = (int) date("Y");
								$start_year = $cur_year - 4;
								$end_year = $cur_year + 4;
								
								for ($opt_year = $start_year; $opt_year <= $end_year; ++$opt_year) {
										echo build_option($opt_year, $opt_year, $opt_year == $cur_year);
								}
								
								?>
								</select></td>
							</tr>
						</tbody>
					
					</table>	
				
					<div class="clear">&nbsp;</div>
				</form>
				<div id="internal_awards"><?php echo display_internal_awards_admin($internal_awards); ?></div>
			
				<script language="javascript">
				var internal_awards = new ActiveDataEntryProcessor({
					url : '<?php echo webservice_url("mspr-admin"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=internal_awards',
					data_destination: $('internal_awards'),
					new_form: $('add_internal_award_form'),
					remove_forms_selector: '.remove_internal_award_form',
					new_button: $('add_internal_award_link'),
					hide_button: $('hide_internal_award')
			
				});
				
				</script>
			</div>
		</div>
	</div>
	
	<h2 title="Extracted Information Section" class="collapsed">Information Extracted from Other Sources</h2>
	<div id="extracted-information-section">
	
		<div class="section">
			<h3 title="Clerkship Core Rotations Completed Satisfactorily to Date Section"  class="collapsable collapsed">Clerkship Core Rotations Completed Satisfactorily to Date</h3>
			<div id="clerkship-core-rotations-completed-satisfactorily-to-date-section"><?php echo display_clerkship_core_completed($clerkship_core_completed); ?></div>
		</div>
		
		<div class="section">
			<h3 title="Clerkship Core Rotations Pending Section"  class="collapsable collapsed">Clerkship Core Rotations Pending</h3>
			<div id="clerkship-core-rotations-pending-section"><?php echo display_clerkship_core_pending($clerkship_core_pending); ?></div>
		</div>
		
		<div class="section">
			<h3 title="Clerkship Electives Completed Satisfactorily to Date Section"  class="collapsable collapsed">Clerkship Electives Completed Satisfactorily to Date</h3>
			<div id="clerkship-electives-completed-satisfactorily-to-date-section"><?php echo display_clerkship_elective_completed($clerkship_elective_completed); ?></div>
		</div>
		<div class="section">
			<h3 title="Leaves of Absence" class="collapsable collapsed">Leaves of Absence</h3>
			<div id="leaves-of-absence">
			<?php 
			echo display_mspr_details($leaves_of_absence);
			?>
			</div>
		</div>
		<div class="section">
			<h3 title="Formal Remediation Received" class="collapsable collapsed">Formal Remediation Received</h3>
			<div id="formal-remediation-received">
			<?php 
			echo display_mspr_details($formal_remediations);
			?>
			</div>
		</div>
		<div class="section">
			<h3 title="Disciplinary Actions" class="collapsable collapsed">Disciplinary Actions</h3>
			<div id="disciplinary-actions"> 
			<?php 
			echo display_mspr_details($disciplinary_actions);
			?>
			</div>
		</div>
	</div>
</div>
<?php 
}
