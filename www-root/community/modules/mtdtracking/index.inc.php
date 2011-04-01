<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Displayed the event calendar index, which will hopefully be a real calendar
 * sooner rather than later.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Don Zuiker <don.zuiker@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
 *
 */
if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	header("Location: " . COMMUNITY_URL);
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
} else {

ini_set('display_errors', 1);

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "../../../core/includes",
    get_include_path(),
)));

require_once("functions.inc.php");

if ((!defined("COMMUNITY_INCLUDED")) || (!defined("IN_MTDTRACKING"))) {
	exit;
} elseif (!$COMMUNITY_LOAD) {
	exit;
}
$community_title = $community_details["community_title"];

//Get the MOH Service Name for this Queen's Program
$query = "SELECT *
		  FROM  `pgme_moh_programs`
		  WHERE `pgme_program_name` like " . $db->qstr(trim($community_title) . "%");

$result = $db->GetRow($query);

// Get the service code
$query = "SELECT *
		  FROM  `mtd_moh_service_codes`
		  WHERE `service_description` = " . $db->qstr($result["moh_service_name"]);

$result = $db->GetRow($query);
$mtd_service_id = 0;
$mtd_service_code = "";
$mtd_service_description = "";

if ($result) {
	$mtd_service_id = $result["id"];
	$mtd_service_code = $result["service_code"];
	$mtd_service_description = $result["service_description"];
}

$query = "SELECT *
		  FROM  `mtd_facilities`
		  WHERE `kingston` = 1
		  ORDER BY `facility_name` ASC";

$mtd_locations_kingston = $db->GetAll($query);

$query = "SELECT *
		  FROM  `mtd_facilities`
		  WHERE `kingston` = 0
		  ORDER BY `facility_name` ASC";

$mtd_locations_other = $db->GetAll($query);

$query = "SELECT *
		  FROM  `mtd_moh_program_codes`
		  ORDER BY `program_description` ASC";

$programs = $db->GetAll($query);

$query = "SELECT *
		  FROM  `mtd_schools`";

$mtd_schools = $db->GetAll($query);

$query = "SELECT *
		  FROM  `mtd_categories`";

$mtd_categories = $db->GetAll($query);

$query = "SELECT `mtd_schedule`.`id`, `mtd_facilities`.`facility_name`, `mtd_residents`.`first_name`,
				 `mtd_residents`.`last_name`, `mtd_schedule`.`start_date`, `mtd_schedule`.`end_date`, `mtd_schedule`.`percent_time`
		  FROM  `mtd_schedule`, `mtd_facilities`, `mtd_moh_program_codes`,
				`mtd_schools`, `mtd_residents`
	      WHERE `mtd_schedule`.`location_id` = `mtd_facilities`.`id`
		  AND `mtd_schedule`.`program_id` = `mtd_moh_program_codes`.`id`
		  AND `mtd_schedule`.`service_id` = '" . $mtd_service_id . "'
		  AND `mtd_schedule`.`school_id` = `mtd_schools`.`id`
		  AND `mtd_schedule`.`resident_id` = `mtd_residents`.`id`
		  ORDER BY `mtd_schedule`.`id` DESC";

$mtd_schedule = $db->GetAll($query);

echo "<script language=\"text/javascript\">var DELETE_IMAGE_URL = '".ENTRADA_URL."/images/action-delete.gif';</script>";

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuerydialog = jQuery('<div></div>')
			.html('<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>You must select at least one record in order to delete.')
			.dialog({
				autoOpen: false,
				title: 'Please Select a Record',
				buttons: {
					Ok: function() {
						jQuery(this).dialog('close');
					}
				}
			});

		jQueryError = jQuery('<div></div>')
		.html('<span class="ui-icon ui-icon-locked" style="float:left; margin:0 7px 50px 0;"></span>Error: You cannot delete records from previous years. Contact support if you need one deleted.')
		.dialog({
			autoOpen: false,
			title: 'Error',
			buttons: {
				Cancel: function() {
					jQuery(this).dialog('close');
				},
				'Contact Support': function() {
					sendFeedback('<?php echo ENTRADA_URL;?>/agent-feedback.php?enc=<?php echo feedback_enc()?>');
					jQuery(this).dialog('close');
				}
			}
		});

		jQuery("#add_MTD_form").submit(function(e) {
			//Cancel the default submit behaviour
			e.preventDefault();
			var form = jQuery(this);
			resident_name = form.find( 'input[name="resident_name"]' ).val();
			start_date = form.find( 'input[name="start_date"]' ).val();
			end_date = form.find( 'input[name="end_date"]' ).val();
			mtdlocation = form.find( 'select[name="mtdlocation"]' ).val();
			service_id =  form.find( 'input[name="service_id"]' ).val();
			program_id =  form.find( 'select[name="program_id"]' ).val();
			school_id =  form.find( 'select[name="school_id"]' ).val();
			mtdlocation_duration_order =  form.find( 'input[name="mtdlocation_duration_order"]' ).val();
			duration_segment =  form.find( 'input[name="duration_segment[]"]' ).val();
			
			url = form.attr( 'action' );
			// Send the data using post and put the results in a div
			jQuery.post( url, { resident_name: resident_name, start_date: start_date, end_date: end_date, mtdlocation: mtdlocation,
								service_id: service_id, program_id: program_id, school_id: school_id, mtdlocation_duration_order: mtdlocation_duration_order,
								duration_segment: duration_segment} ,
				function( data ) {
				var content = jQuery( data ).find( '#responseMsg' );
				jQuery( "#submitResponse" ).html( content );
				var options = {};
				jQuery("#submitResponse").delay(5000).hide("blind", options, 1000);
			});
			window.setTimeout('mtd_schedule.flexReload()', 1000);
			});

		function validateRequired(formArray)
		{
		for (field in formArray) {
		if (field == null || field == "")
		{
		  alert("First name must be filled out");
		  return false;
		  }
		}
		}

		jQuery.datepicker.setDefaults({
			showOn: 'focus',
			buttonImageOnly: false,
			buttonImage: 'calendar.gif',
			buttonText: 'Calendar',
			dateFormat: 'yy-mm-dd',
			numberOfMonths: 2,
			showButtonPanel: true,
			closeOnSelect:false
		});

		//Add datepickers
		jQuery( "#start_date" ).datepicker({
			onSelect: function( selectedDate ) {
				instance = jQuery( this ).data( "datepicker" ),
				date = jQuery.datepicker.parseDate(
					   instance.settings.dateFormat ||
					   jQuery.datepicker._defaults.dateFormat,
					   selectedDate, instance.settings );
			    var start_date = new Date(date);
				var default_end_date = start_date.addWeeks(4);
				jQuery("#end_date").datepicker( 'setDate', default_end_date )
				jQuery("#end_date").datepicker( "option", "minDate", date );
			}
		}).datepicker('setDate', Date.today());

        jQuery( "#end_date" ).datepicker().datepicker('setDate', Date.today().addWeeks(4));

		mtd_schedule = jQuery("#mtd_schedule").flexigrid
		(
			{
			url: '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=api-mtd-load-schedule&service_id=" . $mtd_service_id; ?>',
			dataType: 'json',
			method: 'POST',
			colModel : [
				{display: 'Location', name : 'facility_name', width : 250, sortable : true, align: 'left'},
				{display: 'Last', name : 'last_name', width : 100, sortable : true, align: 'left'},
				{display: 'First', name : 'first_name', width : 100, sortable : true, align: 'left'},
				{display: 'Start', name : 'start_date', width : 100, sortable : true, align: 'left'},
				{display: 'End', name : 'end_date', width : 100,  sortable : true, align: 'left'},
				{display: '%', name : 'percent_time', width : 50,  sortable : true, align: 'left'}
				],
			searchitems : [
				{display: 'Location', name : 'facility_name'},
				{display: 'Last', name : 'last_name', isdefault: true},
				{display: 'First', name : 'first_name'}
				],
			sortname: "start_date",
			sortorder: "desc",
			resizable: false,
			usepager: true,
			collapseTable: false,
			showToggleBtn: false,
			title: 'Medical Training Days',
			useRp: true,
			rp: 20,
			showTableToggleBtn: true,
			width: 750,
			height: 400,
			nomsg: 'No Results',
			buttons : [
	            {name: 'Delete Selected', bclass: 'delete', onpress : deleteRecord}
	            ]
			}
		);

	});


		function deleteRecord(com,grid) {
		    if (com=='Delete Selected') {
		    	jQuery(function() {
		    		var error = "false";
					if(jQuery('.trSelected',grid).length>0) {
						console.log("Selected row found.")
			    		// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
						jQuery("#delete-confirm").dialog("destroy");
						jQuery('.trSelected', grid).each(function() {
	               			
						});

						if(error == "false") {
							// allow deletion
							jQuery("#delete-confirm").dialog({
							resizable: false,
							height:180,
							modal: true,
							buttons: {
								'Delete': function() {
									var ids = "";
				               		jQuery('.trSelected', grid).each(function() {
				               			var id = jQuery(this).attr('id');
										id = id.substring(id.lastIndexOf('row')+3);
										if(ids == "") {
											ids = id;
										} else {
											ids = id+"|"+ids;
										}
									});
									jQuery.ajax
						            ({
						               type: "POST",
						               dataType: "json",
						               url: '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=api-mtd-delete&rid='+ids
						             });

							       	window.setTimeout('mtd_schedule.flexReload()', 1000);
									jQuery(this).dialog('close');
								},
								Cancel: function() {
									jQuery(this).dialog('close');
								}
							}
							});
						} else {
							jQueryError.dialog('open');
						}
			    	} else {
				    	jQuerydialog.dialog('open');
			    	}
				});
		    }
		}


function getResident(id) {
url = jQuery("#find_resident_url").val();
console.log(url)
// Send the data using post and put the results in a div
jQuery.get( url, {resident_id: id},
function( data ) {
	if (jQuery( data ).find( '#resident_not_found' ).html().toString() == "") {
		jQuery("#resident_not_found").html("");
		jQuery("#school_id").val(jQuery( data ).find( '#school_id' ).html().toString());
		jQuery("#school_description").html(jQuery( data ).find( '#school_description' ).html().toString());
		jQuery("#program_id").val(jQuery( data ).find( '#program_id' ).html().toString());
		jQuery("#category_id").val(jQuery( data ).find( '#category_id' ).html().toString());
		jQuery("#full_name").html(jQuery( data ).find( '#full_name' ).html().toString());
		jQuery("#student_no").val(jQuery( data ).find( '#student_no' ).html().toString());
		jQuery("#program_description").html(jQuery( data ).find( '#program_description' ).html());
		jQuery("#category_description").html(jQuery( data ).find( '#category_description' ).html());
		//Remove program name from resident name
		var resident_name = jQuery("#resident_name").val().toString();
		var resident_name_arr = resident_name.split("-");
		//console.log(resident_name)
		console.log(resident_name_arr[0].toString())
		jQuery("#resident_name").val(resident_name_arr[0].toString().strip());
	}
});
}

function clearForm() {
	jQuery("#school_id").val(null);
	jQuery("#school_description").html("");
	jQuery("#program_id").val(null);
	jQuery("#category_id").val(null);
	jQuery("#full_name").html("");
	jQuery("#resident_name").val(null);
	jQuery("#program_description").html("");
	jQuery("#category_description").html("");
	jQuery("#start_date").val(null);
	jQuery("#end_date").val(null);
	jQuery("#student_no").val(null);
	//remove the locations
	jQuery('.location_duration').remove();
}

</script>

<style type="text/css">
	.mtd-form{
		background-color: #FFFFF2;
		border:1px black solid;
		margin:15px 0;
		padding:5px;
		width: 70%;
		float: left;
	}
	.mtd-resident-profile{
		background-color: #FFFFF2;
		border:1px black solid;
		margin:15px 0;
		padding:5px;
		width: 25%;
		float: right;
	}
	#resident_not_found {
		color: red;
	}
</style>

<!-- will be replaced with result of ajax post -->
<div id="submitResponse"></div>
<div id="delete-confirm" style="display:none"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>All selected rows will be deleted. Are you sure?</p></div>

<div id ="MTD_form_container" class="mtd-form">
	<form id="add_MTD_form" action="<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=add" method="post">
				<label for="resident_name">Resident Name:</label><br />
				<input id="resident_name" name="resident_name" type="text" class="required" size="50"/><em>*</em>
				<div class="autocomplete" id="resident_name_auto_complete"></div>
				<script type="text/javascript">
					new Ajax.Autocompleter('resident_name', 'resident_name_auto_complete', '<?php echo ENTRADA_RELATIVE; ?>/api/resident_search.api.php', {frequency: 0.2, minChars: 2, afterUpdateElement: function (text, li) {getResident(li.id);}});</script>
		
			<input id="find_resident_url" type="hidden" value="<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL; ?>?section=find_resident" />
			<p><label for="start_date">Start Date:</label><br />
				<input id="start_date" name="start_date" type="text" /></p>
			<p><label for="end_date">End Date:</label><br />
				<input id="end_date" name="end_date" type="text" /></p>
			<p><label for="location">Location:</label><br />
				<select id="mtdlocation" name="mtdlocation">
					<option value="">Choose a Location</option>
					<?php
					echo "<optgroup label=\"Kingston\">";
					foreach ($mtd_locations_kingston as $mtd_location){
						echo "<option value=\"" . $mtd_location["id"] . "\">" . $mtd_location["facility_name"] . "</option>";
					}
					echo "</optgroup>";
					echo "<optgroup label=\"Other\">";
					foreach ($mtd_locations_other as $mtd_location){
						echo "<option value=\"" . $mtd_location["id"] . "\">" . $mtd_location["facility_name"] . "</option>";
					}
					echo "</optgroup>";
					?>
				</select>
			</p>
			<div id="duration_container" class="sortableList" style="display: none"></div>
			<div id="total_duration" class="content-small">Total percent time: 0 %.</div>
			<input id="mtdlocation_duration_order" name="mtdlocation_duration_order" style="display: none;">
			<div id="duration_notice" class="content-small">Use the list above to select the different components of this event. When you select one, it will appear here and you can change the order and duration.</div>

			<input type="hidden" id="program_id" name="program_id" />
			<input type="hidden" id="school_id" name="school_id" />
			<input type="hidden" id="student_no" type="text" />
			<input type="hidden" id="category_id" name="category_id" />

<?php echo "<label>Service Code: </label>" . $mtd_service_code . " (" . $mtd_service_description . ")"; ?>
			<input type="hidden" id ="service_id" name="service_id" value="<?php echo $mtd_service_id ?>" />
			<p><input id="add_submit" type="submit" value="Add" style="margin-right:20px"/><a href="#" id="clearForm" onclick="clearForm();return false;">Clear Form</a></p>
	</form>
	</div>
	<div id ="mtd_resident_container" class="mtd-resident-profile">
		<div id="resident_not_found"></div>
		<table id="resident_data">
			<colgroup>
				<col style="font-weight: bold;" />
				<col class="resident_data" />
			</colgroup>
			<thead><tr><td colspan="2" style="font-weight:bold; font-size: 14px; border-bottom: 2px solid #999999;">Resident Profile</td><td></td></tr></thead>
			<tbody>
				<tr><td style="font-weight:bold;">Name: </td></tr>
				<tr><td><span id="full_name" class="resident_data" /></td></tr>
				<tr><td style="font-weight:bold;">School: </td></tr>
				<tr><td><span id="school_description"/></td></tr>
				<tr><td style="font-weight:bold;">Program: </td></tr>
				<tr><td><span id="program_description"/></td></tr>
				<tr><td style="font-weight:bold;">Category: </td></tr>
				<tr><td><span id="category_description"/></td></tr>
			</tbody>
		</table>
	</div>

<br class="clearboth">

<div id="button_container" style="float: right;">
	<input type="button" onclick="window.location = '<?php echo COMMUNITY_URL . $COMMUNITY_URL . ":" . $PAGE_URL . "?section=export_schedule&service_id=" . $mtd_service_id; ?>'" value="Download All Data" />
</div>
<br />
<br class="clearboth">

<div id="schedule_container" style="float: left;">
	<table id="mtd_schedule" class="" style="display:none"></table>
</div>

<?php } ?>