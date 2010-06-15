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
 * @version $Id: profile.inc.php 1114 2010-04-09 18:15:05Z finglanj $
 */
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('mspr', 'read',true) || $_SESSION["details"]["group"] != "student") {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["group"]."] and role [".$_SESSION["permissions"][$_SESSION[APPLICATION_IDENTIFIER]["tmp"]["proxy_id"]]["role"]."] do not have access to this module [".$MODULE."]");
}  else {
	require_once(dirname(__FILE__)."/includes/functions.inc.php");
	require_mspr_models();
	
	$PAGE_META["title"]			= "MSPR";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $_SESSION["details"]["id"];
	
	$user = User::get($PROXY_ID);
	process_external_awards_profile($user);
	
	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile?section=mspr", "title" => "MSPR");

	$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/ActiveDataEntryProcessor.js'></script>";
	$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/ActiveEditProcessor.js'></script>";
	$HEAD[] = "<script language='javascript' src='".ENTRADA_URL."/javascript/PriorityList.js'></script>";
	
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

	
echo display_status_messages();

$clerkship_core_completed = ClerkshipRotations::getCoreCompleted($user);
$clerkship_core_pending = ClerkshipRotations::getCorePending($user);
$clerkship_elective_completed = ClerkshipRotations::getElectiveCompleted($user);

$clinical_evaluation_comments = ClinicalPerformanceEvaluations::get($user);

$critical_enquiry = CriticalEnquiry::get($user);
$student_run_electives = StudentRunElectives::get($user);
$internal_awards = InternalAwardReceipts::get($user);
$external_awards = ExternalAwardReceipts::get($user);
$studentships = Studentships::get($user);

$contributions = Contributions::get($user);

$leaves_of_absence = LeavesOfAbsence::get($user);
$formal_remediations = FormalRemediations::get($user);
$disciplinary_actions = DisciplinaryActions::get($user);

$community_health_and_epidemiology = CommunityHealthAndEpidemiology::get($user);
$research_citations = ResearchCitations::get($user);

?>

<h1>Medical School Performance Report</h1> 


<div class="section">
<h2>Clerkship Core Rotations Completed Satisfactorily to Date</h2>
	<div id="clerkships_core_completed"><?php echo display_clerkship_details($clerkship_core_completed); ?></div>

</div>
<div class="section">
<h2>Clerkship Core Rotations Pending</h2>
	<div id="clerkships_core_pending"><?php echo display_clerkship_details($clerkship_core_pending); ?></div>
</div>
<div class="section">
<h2>Clerkship Electives Completed Satisfactorily to Date</h2>
	<div id="clerkships_electves_completed"><?php echo display_clerkship_details($clerkship_elective_completed); ?></div>

</div>
<div class="section" >
	<h2>Clinical Performance Evaluation Comments</h2>
	<div id="clinical_performance_eval_comments"><?php echo display_clineval_profile($clinical_evaluation_comments); ?></div>
	
</div>
<div class="section" >
	<h2>Extra-curricular Learning Activities</h2>
	
	<div class="subsection" >
	<h3>Observerships</h3>
	</div>
	<div class="subsection" >
	<h3>Student-Run Electives</h3>
	<div id="student_run_electives"><?php echo display_student_run_electives_public($student_run_electives); ?></div>
	</div>
</div>
<div class="section" >
	<h2>Critical Enquiry</h2>
	
		<?php 
	$show_critical_enquiry_form =  ($_GET['show'] == "critical_enquiry_form");

	//use intermediary variables to prevent trying to reference methods on a non-existent object. This results in one condition test rather than testing on every output
	if ($critical_enquiry) {
		$ce_title = $critical_enquiry->getTitle();
		$ce_location = $critical_enquiry->getLocation();
		$ce_supervisor = $critical_enquiry->getSupervisor();
		$ce_organization = $critical_enquiry->getOrganization();
	} else {
		$ce_title = "";
		$ce_location = "";
		$ce_supervisor = "";
		$ce_organization = "";
	}
	
	?>	
	<div id="edit_critical_enquiry_link" style="float: right;<?php if ($show_critical_enquiry_form) { echo "display:none;"; }   ?>">
		<ul class="page-action-edit">
			<li><a id="edit_critical_enquiry" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&show=critical_enquiry_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Edit Critical Enquiry</a></li>
		</ul>
	</div>
	<div class="clear">&nbsp;</div>
	<form id="edit_critical_enquiry_form" name="edit_critical_enquiry_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_critical_enquiry_form) { echo "style=\"display:none;\""; }   ?> >
		<input type="hidden" name="action" value="edit_critical_enquiry"></input>
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
						<input type="submit" class="button" value="Update Project" />
						<div id="hide_critical_enquiry_link" style="display:inline-block;">
							<ul class="page-action-cancel">
								<li><a id="hide_critical_enquiry" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Editing Project ]</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="critical_enquiry_title">Title:</label></td>
					<td><input name="critical_enquiry_title" type="text" style="width:40%;" value="<?php echo $ce_title; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="critical_enquiry_organization">Organization:</label></td>
					<td><input name="critical_enquiry_organization" type="text" style="width:40%;" value="<?php echo $ce_organization; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="critical_enquiry_location">Location:</label></td>
					<td><input name="critical_enquiry_location" type="text" style="width:40%;" value="<?php echo $ce_location; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="critical_enquiry_supervisor">Supervisor:</label></td>
					<td><input name="critical_enquiry_supervisor" type="text" style="width:40%;" value="<?php echo $ce_supervisor; ?>"></input></td>
				</tr>	
			</tbody>
		
		</table>	
	
		<div class="clear">&nbsp;</div>
	</form>
	<div id="critical_enquiry"><?php echo display_supervised_project_profile($critical_enquiry); ?></div>
	<div class="clear">&nbsp;</div>
	<script language="javascript">
	var critical_enquiry = new ActiveEditProcessor({
		url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=critical_enquiry',
		data_destination: $('critical_enquiry'),
		edit_form: $('edit_critical_enquiry_form'),
		edit_button: $('edit_critical_enquiry_link'),
		hide_button: $('hide_critical_enquiry'),
		section: 'critical-enquiry'

	});
	
	</script>
</div>
<div class="section" >
	<h2>Community Health and Epidemiology</h2>
	
	
		<?php 
	$show_community_health_and_epidemiology_form =  ($_GET['show'] == "community_health_and_epidemiology_form");

	//use intermediary variables to prevent trying to reference methods on a non-existent object. This results in one condition test rather than testing on every output
	if ($community_health_and_epidemiology) {
		$chae_title = $community_health_and_epidemiology->getTitle();
		$chae_location = $community_health_and_epidemiology->getLocation();
		$chae_supervisor = $community_health_and_epidemiology->getSupervisor();
		$chae_organization = $community_health_and_epidemiology->getOrganization();
	} else {
		$chae_title = "";
		$chae_location = "";
		$chae_supervisor = "";
		$chae_organization = "";
	}
	
	?>	
	<div id="edit_community_health_and_epidemiology_link" style="float: right;<?php if ($show_community_health_and_epidemiology_form) { echo "display:none;"; }   ?>">
		<ul class="page-action-edit">
			<li><a id="edit_community_health_and_epidemiology" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&show=community_health_and_epidemiology_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Edit Community Health and Epidemiology Project</a></li>
		</ul>
	</div>
	<div class="clear">&nbsp;</div>
	<form id="edit_community_health_and_epidemiology_form" name="edit_community_health_and_epidemiology_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_community_health_and_epidemiology_form) { echo "style=\"display:none;\""; }   ?> >
		<input type="hidden" name="action" value="edit_community_health_and_epidemiology"></input>
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
						<input type="submit" class="button" value="Update Project" />
						<div id="hide_community_health_and_epidemiology_link" style="display:inline-block;">
							<ul class="page-action-cancel">
								<li><a id="hide_community_health_and_epidemiology" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Editing Project ]</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="community_health_and_epidemiology_title">Title:</label></td>
					<td><input name="community_health_and_epidemiology_title" type="text" style="width:40%;" value="<?php echo $chae_title; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="community_health_and_epidemiology_organization">Organization:</label></td>
					<td><input name="community_health_and_epidemiology_organization" type="text" style="width:40%;" value="<?php echo $chae_organization; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="community_health_and_epidemiology_location">Location:</label></td>
					<td><input name="community_health_and_epidemiology_location" type="text" style="width:40%;" value="<?php echo $chae_location; ?>"></input></td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="community_health_and_epidemiology_supervisor">Supervisor:</label></td>
					<td><input name="community_health_and_epidemiology_supervisor" type="text" style="width:40%;" value="<?php echo $chae_supervisor; ?>"></input></td>
				</tr>	
			</tbody>
		
		</table>	
	
		<div class="clear">&nbsp;</div>
	</form>
	<div id="community_health_and_epidemiology"><?php echo display_supervised_project_profile($community_health_and_epidemiology); ?></div>
	<div class="clear">&nbsp;</div>
	<script language="javascript">
	var community_health_and_epidemiology = new ActiveEditProcessor({
		url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=community_health_and_epidemiology',
		data_destination: $('community_health_and_epidemiology'),
		edit_form: $('edit_community_health_and_epidemiology_form'),
		edit_button: $('edit_community_health_and_epidemiology_link'),
		hide_button: $('hide_community_health_and_epidemiology'),
		section:'community_health_and_epidemiology'

	});
	
	</script>
</div>
<div class="section" >
	<h2>Research</h2>
	
		<?php 
	$show_research_citations_form =  ($_GET['show'] == "research_citations_form");
	?>	
	<div id="add_research_citation_link" style="float: right;<?php if ($show_research_citations_form) { echo "display:none;"; }   ?>">
		<ul class="page-action">
			<li><a id="add_research_citation" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&show=research_citations_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Research Citation</a></li>
		</ul>
	</div>
	<div class="clear">&nbsp;</div>
	<form id="add_research_citation_form" name="add_research_citation_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_research_citations_form) { echo "style=\"display:none;\""; }   ?> >
		<input type="hidden" name="action" value="add_research_citation"></input>
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
						<input type="submit" class="button" value="Add Research" />
						<div id="hide_research_citation_link" style="display:inline-block;">
							<ul class="page-action-cancel">
								<li><a id="hide_research_citation" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Contribution ]</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
				<td>&nbsp;</td>
				<td><label class="form-required" for="research_citation_details">Citation:</label></td>
				<td><input name="research_citation_details" type="text" style="width:40%;"></input> <span class="content-small">Note: Shoul adhere to MLA guidelines.</span>
				</td>
				</tr>
			</tbody>
		
		</table>	
	
		<div class="clear">&nbsp;</div>
	</form>

	<div id="research_citations"><?php echo display_research_citations_profile($research_citations); ?></div>
	<div class="clear">&nbsp;</div>
	<em>Note: Grayed rows indicate the research details are subject to, and pending, dean approval.</em>
	<script language="javascript">
	var research_citations = new ActiveDataEntryProcessor({
		url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=research_citations',
		data_destination: $('research_citations'),
		new_form: $('add_research_citation_form'),
		remove_forms_selector: '.remove_research_citation_form',
		new_button: $('add_research_citation_link'),
		hide_button: $('hide_research_citation'),
		section:'research_citations'

	});

	var research_citation_priority_list = new PriorityList({
		url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=research_citations',
		data_destination: $('research_citations'),
		format: /research_citation_([0-9]*)$/,
		tag: "tr",
		handle:'.handle',
		section:'research_citations',
		element: 'research_citations_body'
	});
	</script>
</div>
<div class="section" >
	<h2>Academic Awards</h2>
	<div class="subsection">
		<h3>Internal Awards</h3>
		<div id="internal_awards"><?php echo display_internal_awards($internal_awards); ?></div>
		
	</div>
	<div class="subsection">
		 
		<?php 
		$show_external_awards_form =  ($_GET['show'] == "external_awards_form");
		?>	
		<h3>External Awards</h3>
		<div id="add_external_award_link" style="float: right;<?php if ($show_external_awards_form) { echo "display:none;"; }   ?>">
			<ul class="page-action">
				<li><a id="add_external_award" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&show=external_awards_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add External Award</a></li>
			</ul>
		</div>
		<div class="clear">&nbsp;</div>
		<form id="add_external_award_form" name="add_external_award_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_external_awards_form) { echo "style=\"display:none;\""; }   ?> >
			<input type="hidden" name="action" value="add_external_award"></input>
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
							<input type="submit" class="button" value="Add Award" />
							<div id="hide_external_award_link" style="display:inline-block;">
								<ul class="page-action-cancel">
									<li><a id="hide_external_award" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding External Award ]</a></li>
								</ul>
							</div>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="external_award_title">Title:</label></td>
					<td><input name="external_award_title" type="text" style="width:60%;"></input></td>
					</tr>	
					<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="external_award_body">Awarding Body:</label></td>
					<td><input name="external_award_body" type="text" style="width:60%;"></input></td>
					</tr>	
					<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="external_award_terms">Award Terms:</label></td>
					<td><textarea name="external_award_terms" style="width: 100%; height: 100px;" cols="65" rows="20"></textarea></td>
					</tr>	
					<tr>
					<td>&nbsp;</td>
					<td><label class="form-required" for="external_award_year">Year Awarded:</label></td>
					<td><select name="external_award_year">
						<?php 
						
						$cur_year = (int) date("Y");
						$start_year = $cur_year - 10;
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
		<div id="external_awards"><?php echo display_external_awards_profile($external_awards); ?></div>
		<div class="clear">&nbsp;</div>
		<em>Note: Grayed rows indicate the award is subject to, and pending, dean approval.</em>
		<script language="javascript">
		var external_awards = new ActiveDataEntryProcessor({
			url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=external_awards',
			data_destination: $('external_awards'),
			new_form: $('add_external_award_form'),
			remove_forms_selector: '.remove_external_award_form',
			new_button: $('add_external_award_link'),
			hide_button: $('hide_external_award'),
		section:'external_awards'
	
		});
		
		</script>
	</div>
	
	
</div>
<div class="section" >
	<h2>Summer Studentships</h2>
	<div id="studentships"><?php echo display_studentships($studentships); ?></div>
</div>
<div class="section" >
	<h2>Contributions to Medical School</h2>
	
	<?php 
	$show_contributions_form =  ($_GET['show'] == "contributions_form");
	?>	
	<div id="add_contribution_link" style="float: right;<?php if ($show_contributions_form) { echo "display:none;"; }   ?>">
		<ul class="page-action">
			<li><a id="add_contribution" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&show=contributions_form&id=<?php echo $PROXY_ID; ?>" class="strong-green">Add Contribution</a></li>
		</ul>
	</div>
	<div class="clear">&nbsp;</div>
	<form id="add_contribution_form" name="add_contribution_form" action="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" method="post" <?php if (!$show_contributions_form) { echo "style=\"display:none;\""; }   ?> >
		<input type="hidden" name="action" value="add_contribution"></input>
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
						<input type="submit" class="button" value="Add Contribution" />
						<div id="hide_contribution_link" style="display:inline-block;">
							<ul class="page-action-cancel">
								<li><a id="hide_contribution" href="<?php echo ENTRADA_URL; ?>/profile?section=mspr&id=<?php echo $PROXY_ID; ?>" class="strong-green">[ Cancel Adding Contribution ]</a></li>
							</ul>
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
				<td>&nbsp;</td>
				<td><label class="form-required" for="contribution_role">Role:</label></td>
				<td><input name="contribution_role" type="text" style="width:40%;"></input></td>
				</tr>	
				<tr>
				<td>&nbsp;</td>
				<td><label class="form-required" for="contribution_org_event">Organization/Event:</label></td>
				<td><input name="contribution_org_event" type="text" style="width:40%;"></input></td>
				</tr>	
										<tr>
							<td>&nbsp;</td>
							<td><label class="form-required" for="contribution_start">Start:</label></td>
							<td>
								<select name="contribution_start_month">
								<?php
								echo build_option("","Month",true);
									
								for($month_num = 1; $month_num <= 12; $month_num++) {
									echo build_option($month_num, getMonthName($month_num));
								}
								?>
								</select>
								<select name="contribution_start_year">
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
							<td><label class="form-required" for="contribution_end">End:</label></td>
							<td>
								<select tabindex="1" name="contribution_end_month">
								<?php
								echo build_option("","Month",true);
									
								for($month_num = 1; $month_num <= 12; $month_num++) {
									echo build_option($month_num, getMonthName($month_num));
								}
								?>
								</select>
								<select name="contribution_end_year">
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

	<div id="contributions"><?php echo display_contributions_profile($contributions); ?></div>
	<div class="clear">&nbsp;</div>
	<em>Note: Grayed rows indicate the award is subject to, and pending, dean approval.</em>
	<script language="javascript">
	var contributions = new ActiveDataEntryProcessor({
		url : '<?php echo webservice_url("mspr-profile"); ?>&id=<?php echo $PROXY_ID; ?>&mspr-section=contributions',
		data_destination: $('contributions'),
		new_form: $('add_contribution_form'),
		remove_forms_selector: '.remove_contribution_form',
		new_button: $('add_contribution_link'),
		hide_button: $('hide_contribution'),
		section:'contributions'

	});
	
	</script>

</div>
<div class="section">
	<h2>Leaves of Absence</h2>
	<?php 
	echo display_mspr_details_table($leaves_of_absence);
	?>
</div>
<div class="section">
	<h2>Formal Remediation Received</h2>
	<?php 
	echo display_mspr_details_table($formal_remediations);
	?>
</div>
<div class="section">
	<h2>Disciplinary Actions</h2> 
	<?php 
	echo display_mspr_details_table($disciplinary_actions);
	?>
</div>

<?php 
}
?>