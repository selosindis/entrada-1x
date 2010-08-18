<?php
require_once("Models/MSPRs.class.php");

function generateMSPRHTML(MSPR $mspr,$timestamp = null) {
	if (!$timestamp) {
		$timestamp = time();
	}
	$user = $mspr->getUser();
	$name = $user->getFirstname() . " " . $user->getLastname();
	$grad_year = $user->getGradYear();
	$entry_year = $user->getEntryYear();
	$doc_date = date("F j, Y",$timestamp);
	ob_start();
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
	<html>
	
		<head>
			<title>Medical School Performance Report of <?php echo $name; ?></title>
		
			<meta name="author" content="Associate Dean, Undergraduate Medical Education, Queen's University">
			<meta name="copyright" content="<?php echo COPYRIGHT_STRING; ?>">
			<meta name="docnumber" content="Generated: <?php echo date(DEFAULT_DATE_FORMAT, $timestamp) ?>">
			<meta name="generator" content="Entrada MSPR Generator">
			<meta name="keywords" content="Class of <?php echo $year; ?>, Undergraduate, Education, Dean's Letter, MSPR, Medical School Performance Report">
			<meta name="subject" content="Medical School Performance Report">
		</head>
		
		<body>
			<h1></h1>
			<div align="right"><img src="<?php echo ENTRADA_URL;?>/images/Letterhead.png" height=300 width=402></div>
			<div align="right"><b><u><?php echo $doc_date; ?></u></b></div>
			<center><h2><u><?php echo $name; ?></u></h2></center>
			<div><?php echo $name;?> entered the first year at Queen's University, School of Medicine in <?php echo $entry_year; ?> and is expected to graduate with the degree of Doctor of Medicine from Queen's in May of <?php echo $grad_year; ?>. The following is intended to supplement the official Queen's University Transcript.</div>
			<br><br>
			<?php 
				$component = $mspr["Clerkship Core Completed"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Clerkship Rotations Completed Satisfactorily to Date</u></h3>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo nl2br($entity->getDetails()); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$component = $mspr["Clerkship Core Pending"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Clerkship Rotations Pending</u></h3>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo nl2br($entity->getDetails()); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$component = $mspr["Clerkship Electives Completed"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Clerkship Electives Completed Satisfactorily to Date</u></h3>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td>
						<table width="100%" border=0 cellpadding=0 cellspacing=0>
							<tr>
								<td valign="top" width="50%"><?php echo $entity->getTitle(); ?></td>
								<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
							</tr>
							<tr>
								<td valign="top" colspan=2><?php echo $entity->getLocation()."<br>Supervisor: ". $entity->getSupervisor(); ?></td>
							</tr>
						</table>
					</td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$component = $mspr["Clinical Performance Evaluation Comments"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Clinical Performance Evaluation Comments</u></h3>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					 <td valign="top">"<?php echo nl2br($entity->getComment()); ?>" <?php echo $entity->getSource(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$observerships = $mspr["Observerships"];
				$student_run_electives = $mspr["Student-Run Electives"];
				$international_activities = $mspr["International Activities"];
				if (($observerships && $observerships->count() > 0) || ($student_run_electives && $student_run_electives->count() > 0 ) || ($international_activities && $international_activities->count() >0)) { 
			?>
			<h3><u>Extra-Curricular Learning Activities</u></h3>
			<i>Activities appear below only when a proof of attendance has been received. This category includes: Observerships, University-approved International Activities,(unless attributable to the Critical Enquiry Project) and extra-curricular learning activites.</i>
			<?php 
				}

				$component = $observerships;
				if ($component && $component->count() > 0) { 
			?>
			<h4>Observerships</h4>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo nl2br($entity->getDetails()); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
		
			<?php 
				}

				$component = $student_run_electives;
				if ($component && $component->count() > 0) { 
			?>
			<h4>Student-Run Electives</h4>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo nl2br($entity->getDetails()); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$component = $international_activities;
				if ($component && $component->count() > 0) { 
			?>
			<h4>International Activities</h4>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo nl2br($entity->getDetails()); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$entity = $mspr["Critical Enquiry"];
				if ($entity) { 
			?>
			<h3><u>Critical Enquiry</u></h3>
			<i>All students are required to complete a Critical Enquiry project.<br>Critical Enquiry appears on the Official University Transcript under the course code MEDS 428.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
			</table><br>
			
			<?php 
				}

				$entity = $mspr["Community Health and Epidemiology"];
				if ($entity) { 
			?>
			<h3><u>Community Health and Epidemiology Project</u></h3>
			<i>Students are required to complete a project in either Community Health <u>or</u> History of Medicine. The title of the project appears below.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
			</table><br>
			<?php 
				}

				$component = $mspr["Research"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Research</u></h3>
			<i>Students are encouraged to pursue extracurricular research endeavours to enrich their academic experience. Research undertaken during the medical program appears below.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getText()); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$internal_awards = $mspr["Internal Awards"];
				$external_awards = $mspr["External Awards"];
				if (($internal_awards && $internal_awards->count() > 0) || ($external_awards && $external_awards->count() > 0)) { 
			?>
			<h3><u>Academic Awards</u></h3>
			<i>A brief summary of the terms of reference accompanies each award. Only items of academic significance and either acknowledged or awarded by Queen's University are presented.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					$internal_awards->rewind();
					$external_awards->rewind();
					while(true) {
						if (!$internal_awards->valid() && !$external_awards->valid())  {
							 break;
						} elseif($internal_awards->valid() && $external_awards->valid()) {
							$in = $internal_awards->current();
							$ex = $external_awards->current();
							if (($in->getAwardYear() < $ex->getAwardYear())) {
								$entity = $ex;
								$external_awards->next();
							} elseif ($in->getAwardYear() > $ex->getAwardYear()) {
								$entity = $in;
								$internal_awards->next();
							} else {
								if (strcasecmp($in->getAward()->getTitle(),$ex->getAward()->getTitle())) {
									$entity = $in;
									$internal_awards->next();
								} elseif ($in->getAwardYear() > $ex->getAwardYear()) {
									$entity = $ex;
									$external_awards->next();
								}	
							}
						} elseif($internal_awards->valid()) {
							$entity = $internal_awards->current();
							$internal_awards->next();
						} elseif($external_awards->valid()) {
							$entity = $external_awards->current();
							$external_awards->next();
						}
						$award = $entity->getAward(); 
						?>
				<tr>
					<td valign="top" width="50%"><?php echo $award->getTitle(); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getAwardYear(); ?></td>
				</tr>
				<tr>
					<td valign="top" colspan=2><blockquote><?php echo nl2br($award->getTerms()); ?></blockquote></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			
			<?php 
				}

				$component = $mspr["Studentships"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Studentships</u></h3>
			<i>A limited number of summer scholarships may be available to students in the first and second medcal years through the office of the Associate Dean, Undergraduate Medical Education. Awards are adjudicated by the Awards Committee (Medicine) on the basis of academic achievement and preferred area of interest. Successfulstudents are required to arrange a research project with a faculty member and submit a proposal of the work to be undertaken for approval by the awards committee.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top" width="50%"><?php echo $entity->getTitle(); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getYear(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				}

				$component = $mspr["Contributions to Medical School"];
				if ($component && $component->count() > 0) { 
			?>
			<h3><u>Contributions to Medical School/Student Life</u></h3>
			<i>Participation in the School of Medicine student government, committees (such as admissions), and organization of extra-curricular learning activities and Seminars is listed below.</i><br><br>
			<?php 
				}
			?>
			<!--  PAGE BREAK -->
			<h3><u>Leaves of Absence</u></h3>
			<i>This section is intended for an explanation of special circumstances such as illness or concurrent degrees which may have extended the duration of the program</i><br><br>
			<?php 
				$component = $mspr["Leaves of Absence"];
				if ($component && $component->count() > 0) { 
			?>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				} else {
			?>
				None on Record.
			<?php 
				}
			?>
			<h3><u>Formal Remediation Received</u></h3>
			<i>This section notes instances of Formal Remediation.</i><br><br>
			<?php 
				$component = $mspr["Formal Remediation Received"];
				if ($component && $component->count() > 0) { 
			?>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
			<?php 
				} else {
			?>
				None on Record.
			<?php 
				}
			?>
			<h3><u>Disciplinary Actions</u></h3>
			<i>This section is intended to catalogue items noted by the Student Progress and Promotion Committee of an exceptional nature such as breaches of professionalism, failure of a course/block, etc.</i><br><br>
			<?php 
				$component = $mspr["Disciplinary Actions"];
				if ($component && $component->count() > 0) { 
			?>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						?>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
						<?php
					}
			?>
			</table>
			<?php 
				} else {
			?>
				None on Record.
			<?php 
				}
			?>
		</body>
	</html>
	<?php
	return ob_get_clean();
}