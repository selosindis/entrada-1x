<?php
require_once("Models/mspr/MSPRs.class.php");
require_once("Entrada/mspr/functions.inc.php");
define("MAX_CONTRIBUTIONS", 6);
define("MAX_OBSERVERSHIPS", 8);


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
			<table width="100%" border=0 cellpadding=0 cellspacing=0>
			<tr>
			<td align="left"><h1>Medical Student Performance Record</h1></td>
			<td align="right" width=400><img src="<?php echo str_replace("https://", "http://",ENTRADA_URL); ?>/images/Letterhead.png" height=300 width=400></td>
			</tr>
			</table>
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
					 <td valign="top"><?php echo trim(nl2br($entity->getComment())); ?> <i><?php echo $entity->getSource(); ?></i></td>
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
			<i>Activities appear below only when a proof of attendance has been received. This category includes: Observerships, University-approved International Activities,(unless attributable to the Critical Enquiry Project) and extra-curricular learning activities.</i>
			<?php 
				}

				$component = $observerships;
				if ($component && $component->count() > 0) { 
					$observership_no = 0;
			?>
			<h4>Observerships</h4>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						if (++$observership_no > MAX_OBSERVERSHIPS) break;
							$preceptor = trim($entity->getPreceptorFirstname() . " " . $entity->getPreceptorLastname());
							if ((preg_match("/\b[Dd][Rr]\./", $preceptor) == 0) && ($entity->getPreceptorFirstname() != "Various")) {
								$preceptor = "Dr. ".$preceptor;
							}
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
				if ($entity && $entity->isApproved()) { 
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
				if ($entity && $entity->isApproved()) { 
			?>
			<h3><u>Community-Based Project</u></h3>
			<i>Students are required to complete a project in either Community Health <u>or</u> History of Medicine. The title of the project appears below.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
				<tr>
					<td valign="top"><?php echo nl2br($entity->getDetails()); ?></td>
				</tr>
			</table><br>
			<?php 
				}

				$component = $mspr["Research"];
				if ($component) {
					$component->filter('is_approved');
				}
				
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
				if ($external_awards) {
					$external_awards->filter('is_approved');
				}
				
				$component = new Collection();
				if ($internal_awards) {
					foreach ($internal_awards as $award) {
						$component->push($award);
					}
				}
				if ($external_awards) {
					foreach ($external_awards as $award) {
						$component->push($award);
					}
				}
				$component->sort('year','asc');

				if ($component->count() > 0) { 
			?>
			<h3><u>Academic Awards</u></h3>
			<i>A brief summary of the terms of reference accompanies each award. Only items of academic significance and either acknowledged or awarded by Queen's University are presented.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
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
			<i>A limited number of summer scholarships may be available to students in the first and second medical years through the office of the Associate Dean, Undergraduate Medical Education. Awards are adjudicated by the Awards Committee (Medicine) on the basis of academic achievement and preferred area of interest. Successful students are required to arrange a research project with a faculty member and submit a proposal of the work to be undertaken for approval by the awards committee.</i><br><br>
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
				if ($component) {
					$component->filter('is_approved');
				}
				if ($component && $component->count() > 0) { 
					$contribution_no = 0;
			?>
			<h3><u>Contributions to Medical School/Student Life</u></h3>
			<i>Participation in the School of Medicine student government, committees (such as admissions), and organization of extra-curricular learning activities and Seminars is listed below.</i><br><br>
			<table width="100%" border=0 cellpadding=5 cellspacing=0>
			<?php
					foreach($component as $entity) {
						if (++$contribution_no > MAX_CONTRIBUTIONS) break;
						?>
				<tr>
					<td valign="top" width="50%"><?php echo $entity->getOrgEvent()."<br>".$entity->getRole(); ?></td>
					<td valign="top" width="50%" align="right"><?php echo $entity->getPeriod(); ?></td>
				</tr>
						<?php
					}
			?>
			</table><br>
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
			<!--  PAGE BREAK -->
			<h3>Queen's School of Medicine Program of Study Specifics</h3>
<p>Our mission is to educate health professionals and students in the biomedical sciences by conducting research, by generating a spirit of enquiry, and by serving the health needs of the people of southeastern Ontario, drawing on Queen's learning environment to enable our graduates to become the leading health professionals for Canada's rural, northern, and urban communities and to provide researchers and educators for the nation's future.</p>

<p>The curriculum is divided into three sequential phases:</p>
<ul>
<li>Phase I is a 15 week introduction to the integrated biomedical sciences;</li>
<li>Phase II consists of system-oriented, clinically-based learning spanning 65 weeks, including an 8-week critical enquiry research project; and </li>
<li>Phase III is the clinical clerkship which includes:
<ul><li>6 weeks of Medicine-General</li>
<li>6 weeks of Medicine-Specialty</li>
<li>6 weeks of Surgery</li>
<li>6 weeks of Perioperative/Acute Care</li>
<li>6 weeks of Obstetrics and Gynecology</li>
<li>6 weeks of Pediatrics</li>
<li>6 weeks of Family Medicine</li>
<li>14 weeks of student-chosen Electives</li></ul></li>
</ul>

<p>The curriculum also includes integrated horizontal learning:</p> 
<ul>
	<li>Medicine in Society
		<ul>
			<li>Information Literacy</li>
			<li>Medical Ethics</li>
			<li>Law</li>
			<li>History of Medicine</li>
			<li>Family Medicine
				<ul>
					<li>this includes participation at the end of first year in the "Community Week" Rural Medicine program, which is designed to increase exposure to Family Medicine early in the undergraduate training.</li>
				</ul>
			</li>
			<li>Growth and Development</li>
			<li>Geriatrics</li>
			<li>Psychosocial Aspects of Medicine</li>
			<li>Community Health and Epidemiology</li>
		</ul>
	</li>
	<li>Communication skills</li>
	<li>Clinical skills</li>
	<li>Self-directed learning
		<ul>
			<li>didactic lectures</li>
			<li>tutorials</li>
			<li>symposia</li>
			<li>Problem Based Learning (PBL)</li>
			<li>team assignments</li>
		</ul>
	</li>
</ul>
<p>Examinations are criterion-based and are graded as honours/pass/fail.</p> 

<p>The student is provided with a set of educational objectives at the beginning of the period of instruction together with a description of the evaluation techniques to be used and a criteria-related statement of honours/pass/fail levels. At the end of the period of instruction, reports on the student's standing in each course is made to the Medical School Office in the form of honours / pass / fail designation together with a narrative description of his or her performance where appropriate. In the clinical programs, such narratives encompass both cognitive and affective criteria and should be reviewed with the student. Students are invited to sign these reports and record differences in opinion.
The requirements for standing in clinical programs embrace behavioural standards no less than cognitive skills. Thus, personal and professional attributes which are generally recognized as fundamental to the work of a thoughtful, sensitive, and competent physician are evaluated. By the same token, any conduct of a student engaged in a clinical program that could reasonably be regarded by his/her peers as disgraceful, dishonourable, unbecoming or unprofessional must be considered as a major component in the academic decision regarding standing.</p>

<p>The honours / pass / fail grade is based on aggregate marks from different components of the course, as described in the course syllabus.</p>
<ul>
<li>Honours: &gt;=79.1%</li>
<li>Pass: &gt;=60%</li>
<li>Fail: &lt;60%</li>
</ul>
<p>An honours grade is available in the Pediatrics clerkship course. The following rotations only evaluate on a pass/fail basis: Family Medicine, Medicine-General, Medicine-Specialty, Perioperative/Acute Care, Psychiatry, Surgery and Obstetrics and Gynecology.</p>

<p>The actual marks are used by the Undergraduate Office to identify areas of student performance requiring remediation and the allocation of prizes, scholarships and summer studentships. The marks do not appear on the University transcript and are not disclosed to agencies outside the University.</p>

<p>The Student Progress and Promotion Committee will receive reports on student standing in each course or designated portion of the MD program, together with a narrative description, where appropriate, of the student's performance; review the progress of each student registered in the MD program of the Faculty of Health Sciences with respect to cognitive, affective and skill components; consider the academic performance of any medical student whose name has been referred to it; make decisions with respect to standing, promotion, supplemental privileges, the repeating of a portion of the MD program, and the requirement to withdraw from the further study of medicine. Such decisions will constitute the official statement of standing.</p>

<p>The purpose of a remedial program is to assist the student in overcoming the deficiencies. The remedial program shall comprise one or more of the following:</p>
<ol type="a">
<li>repetition of a phase, block, course or rotation;</li>
<li>remedial work to be done during a period of time in which the student is not participating in the activities of another scheduled block, unit or rotation (with the exception of electives);</li>
<li>remedial work to be done during a scheduled phase, block, course or rotation. This option is reserved for a remedial program that is designed to correct limited, circumscribed deficits.</li>
</ol>

<p>In the case of (a) the student must meet the objectives of the specific phase, block, course or rotation and be evaluated by the same methods as other students. If the student achieves a passing grade in the remedial program he/she will be deemed to have satisfactorily completed the phase, block, course or rotation for which the remedial program was a part or whole thereof. If the student does not achieve a passing grade in the remedial program, he/she will be deemed to have received two failing grades and the student's status will be considered by the Student Progress and Promotion Committee. In reaching its decision, the Committee will review the student's performance throughout the MD Program. The student will not be permitted to proceed in the program until a decision is made by the Committee.</p>
<br>
<h3>Clerkship Performance Evaluation</h3>
<p>Students receive an evaluation at the end of each clinical rotation. Successful completion of each core clerkship rotation is necessary to be granted a medical degree. If time permits, a failed core rotation will be repeated during the fourth year elective period. Alternatively, the student may need to complete a remedial rotation at the end of the clerkship (the 4-week period before convocation). The relevant departmental clerkship coordinator will oversee the remedial rotation, providing student feedback and ongoing educational support as necessary. If the remedial rotation is unsuccessful, the student will be required to repeat all or part of the clerkship as decided by the Student Progress and Promotion Committee.</p>

<br><br>
Sincerely,
<br><br>
<br><br>
<br><br>


Anthony J. Sanfilippo, MD, FRCP(C)<br>
Associate Dean<br>
Undergraduate Medical Education<br>
		</body>
	</html>
	<?php
	return ob_get_clean();
}