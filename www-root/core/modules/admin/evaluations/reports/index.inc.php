<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * The default file that is loaded when /admin/evaluations is accessed.
 *
 * @author Organisation: Univeristy of Calgary
 * @author Unit: Faculty of Medicine
 * @author Developer: Ilya Sorokin <isorokin@ucalgary.ca>
 * @copyright Copyright 2010 University of Calgary. All Rights Reserved.
 *
*/

if (!defined("IN_EVALUATIONS")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("evaluations", "update", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$GROUP."] and role [".$ROLE."] does not have access to this module [".$MODULE."]");
} else {
	?>
	<style type="text/css">
		ol.system-reports li {
			width:			70%;
			color:			#666666;
			font-size:		12px;
			padding:		0px 15px 15px 0px;
			margin-left:	5px;
		}
		
		ol.system-reports li a {
			font-size:		13px;
			font-weight:	bold;
		}
	</style>
	<h1>Evaluation Reports</h1>
	
	<h2 style="color: #669900">Student Evaluations</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=student-course-evaluations">Course Evaluations</a><br />
			Reports showing the students' evaluation of their pre-clerkship courses.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=student-teacher-evaluations">Teacher Evaluations</a><br />
			Reports showing the students' evaluation of their pre-clerkship teachers.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=student-peer-evaluations">Peer Evaluations</a><br />
			Reports showing the students' evaluation of their peers.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=student-group-evaluations">Group Evaluations</a><br />
			Reports showing the students' evaluation of their small and clinical groups.
		</li>
	</ol>
	
	<h2 style="color: #669900">Faculty Evaluations</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=faculty-student-group-evaluations">Faculty's Evaluations of Students in Student Groups</a><br />
			Reports that show evaluations by faculty of students in teaching groups.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=faculty-student-clinical-group-evaluations">Faculty's Evaluations of Students performance in Clinical groups.</a><br />
			Reports that show evaluation by faculty of students in clinical groups.
		</li>
		
	</ol>
	
	<h2 style="color: #669900">Administrative Reports</h2>
	<ol class="system-reports">
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=active-evaluations">Active Evaluations Overview</a><br />
			A report showing all open evaluations during the given time period.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=flagged-evaluations">Flagged Evaluations</a><br />
			A report showing all evaluations that contain active flags during the given time period.
		</li>
		<li>
			<a href="<?php echo ENTRADA_URL; ?>/admin/evaluations/reports?section=archive-evaluations">Archive</a><br />
			A report showing all closed and expired evaluations during the given time period.
		</li>
	</ol>
	<?php
}