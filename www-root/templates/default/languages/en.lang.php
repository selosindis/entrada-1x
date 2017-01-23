<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This is the template for the default English language file for Entrada.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Faculty of Veterinary Medicine
 * @author Developer: Szemir Khangyi <skhangyi@ucalgary.ca>
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2008 University of Calgary. All Rights Reserved.
 *
*/

global $AGENT_CONTACTS;

return array (
	/*
	 * Navigation
	 */
	"navigation_tabs" => array (
		"public" => array (
			"dashboard" => array ("title" => "Dashboard"),
			"communities" => array ("title" => "Communities"),
			"curriculum/search" => array (
				"title" => "Curriculum",
				"children" => array (
                    "curriculum/search" => array (
                        "title" => "Curriculum Search"
                    ),
					"curriculum/explorer" => array (
						"title" => "Curriculum Explorer"
					),
                    "curriculum/matrix" => array (
						"title" => "Curriculum Matrix"
					),
				)
			),
			"courses" => array ("title" => "Courses"),
			"events" => array ("title" => "Learning Events"),
			"clerkship" => array (
			    "title" => "Clerkship",
			    "resource" => "clerkship",
			    "permission" => "read",
			    "children" => array (
			        "clerkship" => array (
                        "title" => "Schedules",
                    ),
                    "clerkship/logbook" => array (
                        "resource" => "clerkship",
                        "permission" => "read",
                        "limit-to-groups" => "student",
                        "title" => "Logbook",
                    ),
                    "evaluations" => array (
                        "title" => "Evaluations",
                    )
                )
			),
			"people" => array ("title" => "People Search"),
			"annualreport" => array ("title" => "My Annual Report", "resource" => "annualreport", "permission" => "read", "limit-to-groups" => "faculty")
		),
		"admin" => array (
			"observerships" => array ("title" => "Manage Observerships")
		)
	),

	/*
	 * Global terminology used across different Entrada modules.
	 */
    "Organisation" => "Organisation",
    "Organisations" => "Organisations",
    "My Organisations" => "My Organisations",
    "Give Feedback!" => "Give Feedback!",
    "Quick Polls" => "Quick Polls",
	"Message Center" => "Message Center",
    "global_button_save" => "Save",
    "global_button_cancel" => "Cancel",
    "global_button_proceed" => "Proceed",
    "global_button_post" => "Post",
    "global_button_update" => "Update",
    "global_button_reply" => "Reply",    
    "login" => "Login",
    "logout" => "Logout",
    "selected_courses" => "Selected Courses",
	"available_courses" => "Available Courses",
	"all_courses" => "All Courses",
	"no_courses" => "No Courses",
    "Course" => "Program",
	"SSO Login" => "SSO Login",

	/*
	 * Feedback
	 */
	"global_feedback_widget" => array(
		"global" => array(
			"system"		=> array(
				"link-text" => APPLICATION_NAME." Feedback",
				"link-desc" => "Please share any feedback you may have about this page.",
				"form"		=> array(
					"title" => "Feedback about ".APPLICATION_NAME,
					"description" => "This form is provided so you can efficiently provide our developers with important feedback regarding this application. Whether you are reporting a bug, feature request or just general feedback, all messages are important to us and appreciated.<br /><br />
									<span class=\"content-small\">Please note: If you are submitting a bug or problem, please try to be specific as to the issue. If possible also let us know how to recreate the problem.</span>",
					"anon"	=> false,
					"recipients" => array(
						$AGENT_CONTACTS["administrator"]["email"] => $AGENT_CONTACTS["administrator"]["name"]
					)
				)
			)
		)
	),

    /*
     * Events Module
     */
	"events_filter_controls" => array (
		"teacher" => array (
			"label" => "Teacher Filters"
		),
		"student" => array (
			"label" => "Student Filters"
		),
		"group" => array (
			"label" => "Cohort Filters"
		),
		"course" => array (
			"label" => "Course Filters"
		),
		"term" => array (
			"label" => "Term Filters"
		),
		"eventtype" => array (
			"label" => "Learning Event Type Filters"
		),
		"cp" => array (
			"label" => "Clinical Presentation Filters",
			"global_lu_objectives_name" => "MCC Presentations"
		),
		"co" => array (
			"label" => "Curriculum Objective Filters",
			"global_lu_objectives_name" => "Curriculum Objectives"
		),
		"topic" => array (
			"label" => "Hot Topic Filters"
		),
		"department" => array (
			"label" => "Department Filters"
		),
	),

    /*
     * Course and event colours
     */
    "event_color_palette" => array("#0055B7", "#00A7E1", "#40B4E5", "#6EC4E8", "#97D4E9"),
    "course_color_palette" => array("#0055B7", "#00A7E1", "#40B4E5", "#6EC4E8", "#97D4E9"),

	/*
	 * Dashboard Module
	 */
    "public_dashboard_feeds" => array (
		"global" => array (
			array ("title" => "Entrada Project", "url" => "http://www.entrada-project.org/feed/", "removable" => false),
			array ("title" => "Zend Developer Zone", "url" => "http://feeds.feedburner.com/PHPDevZone", "removable" => true),
			array ("title" => "Insider Medicine", "url" => "http://insidermedicine.ca/xml/Patient/insidermedicine_English.xml", "removable" => true),
			array ("title" => "Google News Top Stories", "url" => "https://news.google.com/news?cf=all&hl=en&pz=1&topic=tc&output=rss", "removable" => true)
		),
		"medtech" => array (
			// array ("title" => "Admin Feed Example", "url" => "http://www.yourschool.ca/admin.rss", "removable" => false)
		),
		"student" => array (
			// array ("title" => "Student Feed Example", "url" => "http://www.yourschool.ca/student.rss", "removable" => false)
		),
		"alumni" => array (
			// array ("title" => "Student Feed Example", "url" => "http://www.yourschool.ca/student.rss", "removable" => false)
		),
		"faculty" => array (
			// array ("title" => "Faculty Feed Example", "url" => "http://www.yourschool.ca/faculty.rss", "removable" => false)
		),
		"resident" => array (
			// array ("title" => "Resident Feed Example", "url" => "http://www.yourschool.ca/resident.rss", "removable" => false)
		),
		"staff" => array (
			// array ("title" => "Staff Feed Example", "url" => "http://www.yourschool.ca/staff.rss", "removable" => false)
		)
	),
    "public_dashboard_links" => array (
		"global" => array (
			array ("title" => "Entrada Project", "url" => "http://www.entrada-project.org", "target" => "_blank"),
			array ("title" => "School Library", "url" => ENTRADA_URL."/library", "target" => "_blank"),
			array ("title" => "Insider Medicine", "url" => "http://insidermedicine.ca", "target" => "_blank"),
			array ("title" => "Zend Developer Zone", "url" => "http://devzone.zend.com", "target" => "_blank"),
		),
		"medtech" => array (
			// array ("title" => "Additional Admin Link", "url" => "http://admin.yourschool.ca")
		),
		"student" => array (
			// array ("title" => "Additional Student Link", "url" => "http://student.yourschool.ca")
		),
		"alumni" => array (
			// array ("title" => "Additional Alumni Link", "url" => "http://alumni.yourschool.ca")
		),
		"faculty" => array (
			// array ("title" => "Additional Faculty Link", "url" => "http://faculty.yourschool.ca")
		),
		"resident" => array (
			// array ("title" => "Additional Resident Link", "url" => "http://resident.yourschool.ca")
		),
		"staff" => array (
			// array ("title" => "Additional Staff Link", "url" => "http://staff.yourschool.ca")
		)
	),
    "public_dashboard_title_medtech" => "MEdTech Dashboard",
    "public_dashboard_title_student" => "Student Dashboard",
    "public_dashboard_title_alumni" => "Alumni Dashboard",
    "public_dashboard_title_faculty" => "Faculty Dashboard",
    "public_dashboard_title_resident" => "Resident Dashboard",
    "public_dashboard_title_staff" => "Staff Dashboard",
    "public_dashboard_block_weather" => "Weather Forecast",
    "public_dashboard_block_community" => "My Communities",

	/*
	 * Communities Module
	 */
    "breadcrumb_communities_title"=> "Entrada Communities",
    "public_communities_heading_line" => "Need a <strong>collaborative space</strong> for your <strong>group</strong> to online?",
    "public_communities_tag_line" => "The <strong>Entrada Community Platform</strong> gives your group a <strong>space to connect</strong> online. You can create websites, study groups, share documents, upload photos, maintain mailing lists, announcements, and more!",
    "public_communities_title" => "Entrada Communities",
    "public_communities_create" => "Create a Community",
    "public_communities_count" => "<strong>Powering</strong> %s communities",
    "Community Permissions" => "Community Permissions",
    "community_history_add_announcement" => "A new announcement (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_announcement" => "Announcement (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_forum" => "A new discussion forum (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_post" => "A new discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_forum" => "Discussion forum (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_post" => "Discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_reply" => "Discussion post #%RECORD_ID% of (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%PARENT_ID%#post-%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_reply" => "Discussion post (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-post&id=%PARENT_ID%#post-%RECORD_ID%\">%RECORD_TITLE%</a>) was replied to.",
    "community_history_add_event" => "A new event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_event" => "Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
	"community_history_add_event" => "A new event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_event" => "Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
	"community_history_add_learning_event" => "A new learning event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_learning_event" => "Learning Event (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_photo_comment" => "New comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%PARENT_ID%\">%RECORD_TITLE%</a>) photo.",
    "community_history_add_gallery" => "A new photo gallery (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_photo" => "A new photo (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_photo_comment" => "Comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%PARENT_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_gallery" => "Photo gallery (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_photo" => "Photo (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_page" => "A new page (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a>) has been created.",
    "community_history_edit_page" => "Page (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_edit_home_page" => "<a href=\"%SITE_COMMUNITY_URL%\">Home page</a> has been updated.",
    "community_history_add_poll" => "A new poll (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_edit_poll" => "Poll (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_add_file_comment" => "New comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%PARENT_ID%\">%RECORD_TITLE%</a>) file.",
    "community_history_add_file" => "A new file (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been uploaded.",
    "community_history_add_share" => "A new shared folder (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been added.",
    "community_history_add_file_revision" => "A new revision of (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been uploaded.",
    "community_history_edit_file_comment" => "Comment on (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%PARENT_ID%\">%RECORD_TITLE%</a>) updated.",
    "community_history_edit_file" => "File (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a>) had been updated.",
    "community_history_edit_share" => "Shared folder (<a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>) has been updated.",
    "community_history_create_moderated_community" => "Community (<a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>) has been created, but is waiting for administrator approval.",
    "community_history_create_active_community" => "Community (<a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>) has been created, and is now active.",
    "community_history_add_member" => "A new member (<a href=\"%SYS_PROFILE_URL%?id=%PROXY_ID%\">%RECORD_TITLE%</a>) has joined this community.",
    "community_history_add_members" => "%RECORD_ID% new member(s) added to the community.",
    "community_history_edit_community" => "The community profile was updated by <a href=\"%SYS_PROFILE_URL%?id=%RECORD_ID%\">%RECORD_TITLE%</a>.",
    "community_history_rename_community" => "Community is now known as <a href=\"%SITE_COMMUNITY_URL%\">%RECORD_TITLE%</a>",
    "community_history_activate_module" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%\">%RECORD_TITLE%</a> module was activated for this community.",
    "community_history_move_file" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-file&id=%RECORD_ID%\">%RECORD_TITLE%</a> file was moved to a different <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-folder&id=%PARENT_ID%\">folder</a>.",
	"community_history_move_photo" => "The <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-photo&id=%RECORD_ID%\">%RECORD_TITLE%</a> photo was moved to a different <a href=\"%SITE_COMMUNITY_URL%:%PAGE_URL%?action=view-gallery&id=%PARENT_ID%\">gallery</a>.",

    "Join Community" => "Join Community",
    "Join this community to access more features." => "Join this community to access more features.",
    "Admin Center" => "Admin Center",
    "Manage Community" => "Manage Community",
    "Manage Members" => "Manage Members",
    "Manage Pages" => "Manage Pages",
    "This Community" => "This Community",
    "My membership" => "My membership",
    "View all members" => "View all members",
    "Quit this community" => "Quit this community",
    "Log In" => "Log In",
    "Additional Pages" => "Additional Pages",
    "Permission Masks" => "Permission Masks",
    "Community Login" => "Community Login",
    "Course Navigation" => "Course Navigation",

	/*
	 * MSPR Module
	 */
	"mspr_no_entity" => "No Entity ID provided.",
	"mspr_invalid_entity" => "Item not found or invalid identifier provided",
	"mspr_no_action" => "No action requested.",
	"mspr_invalid_action" => "Invalid action requested for this item",
	"mspr_no_section" => "No MSPR section specified",
	"mspr_invalid_section" => "Invalid MSPR section specified",
	"mspr_no_comment" => "A comment is required and none was provided",
	"mspr_no_reject_reason" => "A reason for the rejection is required and none was provided",
	"mspr_invalid_user_info" => "Invalid user information provided",
	"mspr_no_details" => "Details are required and none were provided",
	"mspr_insufficient_info" => "Insufficient information provided.",
	"mspr_email_failed" => "Failed to send rejection email.",
	"mspr_observership_preceptor_required" => "A faculty preceptor must be selected or a non-faculty preceptor name entered.",
	"mspr_observership_invalid_dates" => "A valid start date is required.",
	"mspr_too_many_critical_enquiry" => "Cannot have more than one Critical Enquiry on MSPR. Please edit the existing project or remove it before adding a new one.",
	"mspr_too_many_community_based_project" => "Cannot have more than one Community-Based Project on MSPR. Please edit the existing project or remove it before adding a new one.",

	/*
     * Courses Module
     */
	"course" => "Course",
	"courses" => "Courses",
    "Course Director" => "Course Director",
    "Course Directors" => "Course Directors",
    "Curriculum Coordinator" => "Curriculum Coordinator",
    "Curriculum Coordinators" => "Curriculum Coordinators",
	"Faculty" => "Faculty",
    "Program Coordinator" => "Program Coordinator",
    "Program Coordinators" => "Program Coordinators",
    "Evaluation Rep" => "Evaluation Rep",
    "Student Rep" => "Student Rep",
    "Add A New Course" => "Add A New Course",
    "Delete Courses" => "Delete Courses",
    "Search Courses..." => "Search Courses...",
    "Loading Courses..." => "Loading Courses...",
    "No Courses Found" => "No Courses Found",
    "No Courses Selected to delete" => "No Courses Selected to delete",
    "Please confirm you would like to delete the selected Courses(s)?" => "Please confirm you would like to delete the selected Courses(s)?",
    "Load More Courses" => "Load More Courses",

	"evaluation_filtered_words" => "Dr. Doctor; Firstname Lastname",

	/*
	 * Course Group Module
	 *
	 */
	"Course Groups" => "Course Groups",
	"Add Group" => "Add Group",
	"Group Details" => "Group Details",
	"Group Name Prefix" => "Group Name Prefix",
	"Group Type" => "Group Type",
	"Create" => "Create",
	"empty groups" => "empty groups",
	"Automatically populate groups" => "Automatically populate groups",
	"Selected learners within" => "Selected learners within",
	"Populate based on" => "Populate based on",
	"Number of Groups" => "Number of Groups",
	"Group Size" => "Group Size",
	"No method supplied" => "No method supplied",
	"No Groups Found." => "No Groups Found.",
	"Invalid GET method." => "Invalid GET method.",
	"Assign Period" => "Assign Period",
	"Delete Groups" => "Delete Groups",
	"Add New Groups" => "Add New Groups",
	"Download as CSV" => "Download as CSV",
	"Search the Course Groups" => "Search the Course Groups",
	"No groups to display" => "No groups to display",
	"No Group Selected to delete." => "No Group Selected to delete.",
	"Please confirm that you would like to proceed with the selected Group(s)?" => "Please confirm that you would like to proceed with the selected Group(s)?",
	"Assign Curriculum Period" => "Assign Curriculum Period",
	"Please select only Group(s) without curriculum period assigned." => "Please select only Group(s) without curriculum period assigned.",
	"Please confirm that you would like to assign the curriculum period to the selected Group(s)?" => "Please confirm that you would like to assign the curriculum period to the selected Group(s)?",
	"Print" => "Print",
	"Male" => "Male",
	"Female" => "Female",
	"View Members" => "View Members",
	"Delete Members" => "Delete Members",
	"Name" => "Name",
	"Group & Role" => "Group & Role",
	"No members to display" => "No members to display",
	"Add Members" => "Add Members",

	/*
	 * Curriculum Explorer
	 */
	"curriculum_explorer" => array(
		"badge-success" => "0.3",
		"badge-warning" => "0.1",
		"badge-important" => "0.05"
	),

	/*
	 * Copyright Notice
	 */
    "copyright_title" => "Acceptable Use Agreement",
    "copyright_accept_label" => "I will comply with this copyright policy.",
	"copyright" => array(
		"copyright-version" => "", // Latest copyright version date time stamp (YYYY-MM-DD HH:MM:SS). You can also leave this empty to disable the acceptable use feature.
		"copyright-firstlogin" => "<strong>Use of Copyright Materials In ".APPLICATION_NAME."</strong>
			<p>Copyright protects the form in which literary, artistic, musical and dramatic works are expressed. In COUNTRY, copyright exists once a work is expressed in fixed form; no special registration needs to take place. Copyright usually resides with the creator of the work. Copyright exists in most work for 50 years after the death of the creator.</p>
			<p>The University of UNIVERSITY encourages access to works while ensuring that the rights of creators are respected in accordance with the Copyright Act, (see...)</p>
			<p>It is the responsibility of each individual to ensure compliance with copyright regulations.</p>
			<p>To proceed, you accept to comply with the copyright policy.</p>",
		"copyright-uploads" => "<strong>Use of Copyright Materials In ".APPLICATION_NAME."</strong>
			<p>Copyright protects the form in which literary, artistic, musical and dramatic works are expressed. In COUNTRY, copyright exists once a work is expressed in fixed form; no special registration needs to take place. Copyright usually resides with the creator of the work. Copyright exists in most work for 50 years after the death of the creator.</p>
			<p>The University of UNIVERSITY encourages access to works while ensuring that the rights of creators are respected in accordance with the Copyright Act, (see...)</p>
			<p>It is the responsibility of each individual to ensure compliance with copyright regulations.</p>
			<p>To proceed, you accept to comply with the copyright policy.</p>",
	),

    /*
     * Gradebook Module
     */
    "assignment_notice" => "<p>A new assignment [<a href=\"%assignment_submission_url%\">%assignment_title%</a>] has been released in %course_code%: %course_name%.</p>
        <p>The details provided for this assignment are as follows:</p>
        <p>Due Date: %due_date%</p>
        <p>Title: %assignment_title%</p>
        <p>Description:<br />%assignment_description%</p>",

	"Cancel" => "Cancel",
	"Add" => "Add",
	"Assign" => "Assign",
	"Submit" => "Submit",
	"Save" => "Save",
	"Back" => "Back",
	"Delete" => "Delete",
	"Done" => "Done",
	"Close" => "Close",

    /**
     * Assessments Module
     */
    "assessments" => array(
        "title" => "Assessment & Evaluation",
        "breadcrumb" => array(
            "title" => "Assessment & Evaluation"
        ),
        "forms" => array(
            "title" => "Forms",
            "breadcrumb" => array(
                "title" => "Forms"
            ),
            "buttons" => array(
                "add_form" => "Add Form",
                "delete_form" => "Delete Form"
            ),
            "placeholders" => array(
                "form_bank_search" => "Begin Typing to Search the Forms..."
            ),
            "add-form" => array(
                "title" => "Create New Form",
                "breadcrumb" => array(
                    "title" => "Add Form"
                ),
            ),
            "edit-form" => array(
                "title" => "Editing Form:",
                "breadcrumb" => array(
                    "title" => "Edit Form"
                ),
                "form_not_found" => "Sorry, there was a problem loading the form using that ID.",
                "no_form_elements" => "There are currently no items attached to this form."
            ),
            "add-permission" => array(
                "title" => "Add Permission",
                "breadcrumb" => array(
                    "title" => "Add Permission"
                ),
                "labels" => array(
                    "label_contact_type"    => "Contact Type",
                    "label_contact_name"    => "Contact Name"
                ),
                "contact_types" => array(
                    "proxy_id"          => "Individual",
                    "organisation_id"   => "Organisation",
                    "course_id"         => "Course"
                )
            ),
            "form" => array(
                "label_form_type"           => "Form Type",
                "label_form_title"          => "Form Title",
                "label_form_description"    => "Form Description",
                "label_form_permissions"    => "Form Permissions",
                "title_form_items"          => "Form Items",
                "title_form_info"           => "Form Information",
                "title_modal_delete_element" => "Delete Element",
                "btn_add_single_item"       => "Add Individual Item(s)",
                "btn_add_free_text"         => "Add Free Text",
                "btn_add_item"              => "Add Item",
                "btn_add_rubric"            => "Add Grouped Item",
                "btn_add_data_src"          => "Add Data Source",
                "btn_add_form_el"           => "Add Text",
                "text_no_attached_items"    => "There are currently no items attached to this form.",
                "text_modal_delete_element" => "Would you like to delete this form element?",
                "text_modal_no_form_items_selected" => "No Forms Items Selected to delete.",
                "text_modal_delete_form_items" => "Please confirm you would like to delete the selected <span></span> Form Item(s).",
                "text_modal_delete_form_items_success" => "You have successfully deleted the selected <span></span> Form Item(s).",
                "text_modal_delete_form_items_error" => "Unfortunately, an error was encountered while attempting to remove the selected <span></span> Form Item(s).",
            ),
            "add-element" => array(
                "title" => "Add Form Element",
                "breadcrumb" => array(
                    "title" => "Add Element"
                ),
                "failed_to_create" => "Sorry, we were unable to add this element to the form.",
                "already_attached" => "Sorry, the element you are attempting to add is already attached to the form.",
                "no_available_items" => "There are no items available to attach to this form.",
                "add_element_notice" => "Please check off the items you wish to add to your form and click the Add Elements button below."
            ),
            "index" => array(
                "delete_success" => "Forms have been successfully deleted. You will now be taken back to the Forms index.",
                "delete_error" => "There was a problem deleting the forms you selected. An administrator has been informed, please try again later.",
                "title_heading" => "Form Title",
                "created_heading" => "Date Created",
                "items_heading" => "Items",
                "no_forms_found" => "You currently have no forms to display. To Add a new form click the Add Form button above.",
                "text_modal_no_forms_selected" => "No Forms Selected to delete.",
                "text_modal_delete_forms" => "Please confirm you would like to delete the selected Form(s).",
                "no_forms_found" => "You currently have no forms to display. To Add a new form click the Add Form button above.",
                "title_modal_delete_forms" => "Delete Forms"
            )
        ),
        "items" => array (
            "title" => "Items",
            "breadcrumb" => array(
                "title" => "Items"
            ),
            "buttons" => array(
                "add_item" => "Add A New Item",
                "item_list_view_toggle_title" => "Toggle Item List View",
                "item_detail_view_toggle_title" => "Toggle Item Detail View",
                "delete_items" => "Delete Items"
            ),
            "placeholders" => array(
                "item_bank_search" => "Begin Typing to Search the Items..."
            ),
            "add-item" => array(
                "title" => "Create New Item",
                "breadcrumb" => array(
                    "title" => "Add Item"
                ),
            ),
            "edit-item" => array(
                "title" => "Editing Item:",
                "item_not_found" => "Unfortunately, there was a problem loading the item using that ID.",
                "breadcrumb" => array(
                    "title" => "Edit Item"
                ),
            ),
            "add-permission" => array(
                "title" => "Add Item Permission",
                "breadcrumb" => array(
                    "title" => "Add Item Permission"
                ),
                "labels" => array(
                    "label_contact_type"    => "Contact Type",
                    "label_contact_name"    => "Contact Name"
                ),
                "contact_types" => array(
                    "proxy_id"          => "Individual",
                    "organisation_id"   => "Organisation",
                    "course_id"         => "Course"
                )
            ),
            "form" => array(
                "label_item_type"           => "Item Type",
                "label_item_text"           => "Item Text",
                "label_item_code"           => "Item Code",
                "label_allow_comments"      => "Allow comments for this Item",
                "label_optional_comments"	=> "Comments are optional",
                "label_mandatory_comments"	=> "Require comments for any response",
                "label_flagged_comments"	=> "Require comments for flagged responses",
                "label_responses"           => "Number of Responses",
                "label_item_permissions"    => "Item Permissions",
                "label_item_objectives"     => "Curriculum Tags",
                "btn_add_item"              => "Add New Item",
                "btn_attach_item"           => "Attach Selected",
                "btn_add_attach_item"       => "Add &amp; Attach New Item"
            ),
            "index" => array(
                "title" => "Items",
                "breadcrumb" => array(
                    "title" => "Items"
                ),
                "failed_to_create" => "Sorry, we were unable to add this item to the form.",
                "already_attached" => "Sorry, the item you are attempting to add is already attached to the form.",
                "cannot_attach_rubric_item_to_form" => "You cannot attach Grouped Item Items directly to Forms.",
                "no_available_items" => "There are no items available to attach to this form.",
                "add_item_notice" => "Please check off the items you wish to add to your form and click the Add Elements button below.",
                "no_items_found" => "You currently have no items to display. To Add a new Item select Add and Attach New Item from the dropdown button above.",
                "no_items_selected" => "No Items selected to attach",
                "title_modal_delete_items" => "Delete Items",
                "text_modal_no_items_selected" => "No Items Selected to delete.",
                "text_modal_delete_items" => "Please confirm you would like to delete the selected Items(s)?",
            ),
            "add-items" => array(
                "title" => "Add Item",
                "breadcrumb" => array(
                    "title" => "Add Item"
                ),
                "failed_to_create" => "Sorry, we were unable to add this item to the form.",
                "already_attached" => "Sorry, the item you are attempting to add is already attached to the form."
            ),
            "responses" => array(
                "label_response_text" => "Response Text",
                "label_response_category" => "Response Category",
                "label_response_performance_flag" => "Flag",
                "label_row" => "Response"
            )
        ),
        "rubrics" => array(
            "title" => "Grouped Items",
            "breadcrumb" => array(
                "title" => "Grouped Items"
            ),
            "buttons" => array(
                "add_rubric" => "Add Grouped Item",
                "rubric_list_view_toggle_title" => "Toggle Grouped Item List View",
                "rubric_detail_view_toggle_title" => "Toggle Grouped Item Detail View"
            ),
            "placeholders" => array (
                "rubric_bank_search" => "Begin Typing to Search the Grouped Items...",
            ),
            "add-rubric" => array(
                "title" => "Create New Grouped Item",
                "breadcrumb" => array(
                    "title" => "Add Grouped Item"
                ),
            ),
            "edit-rubric" => array(
                "title" => "Editing Grouped Item:",
                "breadcrumb" => array(
                    "title" => "Edit Grouped Item"
                ),
            ),
            "add-permission" => array(
                "title" => "Add Permission",
                "breadcrumb" => array(
                    "title" => "Add Permission"
                ),
                "labels" => array(
                    "label_contact_type"    => "Contact Type",
                    "label_contact_name"    => "Contact Name"
                )
            ),
            "add-element" => array(
                "title" => "Add Elements to Grouped Item",
                "breadcrumb" => array(
                    "title" => "Add Elements to Grouped Item"
                )
            ),
            "rubric" => array(
                "label_rubric_type"           => "Grouped Item Type",
                "label_rubric_title"          => "Grouped Item Title",
                "label_scale_title"         => "Title",
                "label_rubric_description"    => "Grouped Item Description",
                "label_rubric_permissions"    => "Grouped Item Permissions",
                "title_rubric_items"          => "Grouped Item Items",
                "title_modal_delete_item" => "Delete Element",
                "btn_add_item"              => "Add Item",
                "btn_add_rubric"            => "Add Grouped Item",
                "btn_add_scale"             => "Add Grouped Item",
                "btn_delete_scale"          => "Delete Grouped Item",
                "btn_add_data_src"          => "Add Data Source",
                "btn_add_rubric_el"           => "Add Grouped Item Item",
                "text_no_attached_items"    => "There are currently no items attached to this rubric.",
                "text_modal_delete_item" => "Would you like to delete this Grouped Item element?",
                "no_available_items" => "There are no items to display.",
                "delete_rubric_item_modal"  => "Are you sure you want to delete this Grouped Item Item?"
            ),
            "index" => array(
                "text_modal_delete_rubrics" => "Please confirm you would like to delete the selected Grouped Item(s)?",
                "title_modal_delete_rubrics" => "Delete Grouped Items",
                "text_modal_no_rubrics_selected" => "No Grouped Items Selected to delete.",
                "no_rubrics_found" => "You currently have no Grouped Items to display. To Add a new Grouped Item click the Add Grouped Item button above.",
                "btn_attach_rubric" => "Attach Grouped Item",
                "btn_create_and_attach_item" => "Create and Attach"
            )
        ),
        "distributions" => array(
            "title" => "Distributions",
            "breadcrumb" => array(
                "title" => "Distributions"
            ),
        ),
        "schedule" => array(
            "title" => "Schedules",
            "breadcrumb" => array(
                "title" => "Schedules"
            ),
            "edit-schedule" => array(
                "title" => "Edit Schedule",
                "breadcrumb" => array(
                    "title" => "Edit"
                ),
                "schedule_information" => "Schedule Information",
                "children_organisation_title" => "Academic Years",
                "children_academic_year_title" => "Streams",
                "children_stream_title" => "Blocks",
                "no_children" => "There are currently no child schedules.",
                "errors" => array(
                    "title" => "The title can not be empty.",
                    "start_date" => "Start date must come before end date.",
                    "schedule_type" => "Invalid schedule type.",
                    "organisation_id" => "An organisation ID is required."
                )
            ),
            "add-schedule" => array(
                "title" => "Add Schedule",
                "breadcrumb" => array(
                    "title" => "Add"
                )
            )
        )
    ),

	/**
	 *  profile Module
	 */
	"profile" => array(
		"title" => "My Profile",
		"breadcrumb" => array(
			"title" => "My Profile"
		),
		"buttons" => array(
			"delete_anotification" => "Delete Active Notifications"
		),
		"placeholders" => array(
			"anotification_bank_search" => "Search the Active Notifications..."
		),
		"index" => array(
			"title_modal_delete_anotification" => "Delete Active Notifications",
			"text_modal_no_anotifications_selected" => "No Active Notifications Selected to delete.",
			"text_modal_delete_anotifications" => "Please confirm you would like to delete the selected Active Notification(s)?",
			"no_anotifications_found" => "You currently have no active notifications to display.",
		)
	),


	"rotationschedule" => array(
        "title" => "Rotation Schedule",
        "breadcrumb" => array(
            "title" => "Rotation Schedule"
        ),
        "import" => array(
            "title" => "Rotation Schedule",
            "breadcrumb" => array(
                "title" => "Rotation Schedule"
            )
        ),
        "edit-draft" => array(
            "title" => "Edit Draft",
            "add-slot" => "Add Slot"
        ),
        "edit" => array(
            "title" => "Edit Schedule",
            "add-slot" => "Add Slot"
        ),
        "drafts" => array(
            "title" => "My Drafts"
        )
    ),
	"default" => array(
		"btn_submit"    => "Submit",
		"btn_save"      => "Save",
		"btn_add"       => "Add",
		"btn_back"      => "Back",
		"btn_cancel"    => "Cancel",
		"btn_delete"    => "Delete",
		"btn_done"      => "Done",
		"btn_close"     => "Close",
		"btn_add_elements" => "Add Elements",
		"invalid_req_method" => "Invalid request method.",
		"invalid_get_method" => "Invalid GET method.",
		"invalid_post_method" => "Invalid POST method.",
		"contact_types" => array(
			"proxy_id"          => "Individual",
			"organisation_id"   => "Organisation",
			"course_id"         => "Course"
		),
		"date_created" => "Date Created",
		"btn_my_drafts" => "My Drafts",
		"btn_publish"   => "Publish Draft",
		"btn_unpublish"   => "Withdraw Rotation Schedule",
		"btn_import"    => "Import",
		"btn_new"       => "New",
		"deactivate"    => "Deactivate",
		"activate"    => "Activate"
	),

    /**
     * Community Text
     *
     */
    "community" => array(
        "discussion" => array(
            "error_open"	=> "Error updating Discussion Boards Open.",
            "error_request" => "Invalid request method."
        ),
    ),

    /**
     *  Admin - Settings - Grading Scale
     */
    "Grading Scale" => "Grading Scale",
    "Add New Grading Scale" => "Add New Grading Scale",
    "Edit Grading Scale" => "Edit Grading Scale",
    "Delete Grading Scale" => "Delete Grading Scale",
    "Add Range" => "Add Range",

    "Twitter" => "Twitter",
    "Twitter Handle" => "Twitter Handle",
    "Twitter Hastags" => "Twitter Hastags",

    "Manage Organisations" => "Manage Organisations",
    "Add New Organisation" => "Add New Organisation",
    "Delete Selected" => "Delete Selected",

    "Add Organisation" => "Add Organisation",
    "Organisation Name" => "Organisation Name",
    "Description" => "Description",
    "Country" => "Country",
    "Province / State" => "Province / State",
    "Please select a <b>Country</b> from above first." => "Please select a <b>Country</b> from above first.",
    "City" => "City",
    "Postal Code" => "Postal Code",
    "Address 1" => "Address 1",
    "Address 2" => "Address 2",
    "Telephone" => "Telephone",
    "Fax" => "Fax",
    "E-Mail Address" => "E-Mail Address",
    "Website" => "Website",
    "Interface Template" => "Interface Template",
    "AAMC Institution ID" => "AAMC Institution ID",
    "AAMC Institution Name" => "AAMC Institution Name",
    "AAMC Program ID" => "AAMC Program ID",
    "AAMC Program Name" => "AAMC Program Name",

    "Organisation Details" => "Organisation Details",
    "Delete Organisations" => "Delete Organisations"

);
