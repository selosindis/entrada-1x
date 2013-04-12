<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * This file gives Entrada users the ability to update their user profile.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
if (!defined("IN_PROFILE")) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif(!$ENTRADA_ACL->isLoggedInAllowed('profile', 'read')) {
	$ONLOAD[]	= "setTimeout('window.location=\\'".ENTRADA_URL."/".$MODULE."\\'', 15000)";

	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] do not have access to this module [".$MODULE."]");
} else {
	
	$action = clean_input($_POST["action"], "alpha");
	
	if ($action) {
		ob_clear_open_buffers();

		switch ($action) {
			case "uploadimage" :
				$filesize = moveImage($_FILES["image"]["tmp_name"], $ENTRADA_USER->getID(), $_POST["coordinates"], $_POST["dimensions"]);

				if ($filesize) {
					$PROCESSED_PHOTO["proxy_id"]			= $ENTRADA_USER->getID();
					$PROCESSED_PHOTO["photo_active"]		= 1;
					$PROCESSED_PHOTO["photo_type"]			= 1;
					$PROCESSED_PHOTO["updated_date"]		= time();
					$PROCESSED_PHOTO["photo_filesize"]		= $filesize;

					$query = "SELECT `photo_id` FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
					$photo_id = $db->GetOne($query);

					if ($photo_id) {
						if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "UPDATE", "`photo_id` = ".$db->qstr($photo_id))) {
							echo json_encode(array("status" => "success", "data" => webservice_url("photo", array($ENTRADA_USER->getID(), "upload"))."/".time()));
						}
					} else {
						if ($db->AutoExecute(AUTH_DATABASE.".user_photos", $PROCESSED_PHOTO, "INSERT")) {
							echo json_encode(array("status" => "success", "data" => webservice_url("photo", array($ENTRADA_USER->getID(), "upload"))."/".time()));
						} else {
							echo json_encode(array("status" => "error"));
						}
					}
				}
			break;
			case "togglephoto" :
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
				$photo_record = $db->GetRow($query);
				if ($photo_record) {
					$photo_active = ($photo_record["photo_active"] == "1" ? "0" : "1");
					$query = "UPDATE `".AUTH_DATABASE."`.`user_photos` SET `photo_active` = ".$db->qstr($photo_active)." WHERE `proxy_id` = ".$db->qstr($ENTRADA_USER->getID());
					if ($db->Execute($query)) {
						echo json_encode(array("status" => "success", "data" => array("imgurl" => webservice_url("photo", array($ENTRADA_USER->getID(), $photo_active == "1" ? "upload" : "official" ))."/".time(), "imgtype" => $photo_active == "1" ? "uploaded" : "official")));
					} else {
						application_log("error", "An error occurred while attempting to update user photo active flag for user [".$ENTRADA_USER->getID()."], DB said: ".$db->ErrorMsg());
						echo json_encode(array("status" => "error"));
					}
				} else {
					echo json_encode(array("status" => "error", "data" => "No uploaded photo record on file. You must upload a photo before you can toggle photos."));
				}
			break;
			default:
			break;
		}
		
		exit;
		
	}
	$PAGE_META["title"]			= "My Profile";
	$PAGE_META["description"]	= "";
	$PAGE_META["keywords"]		= "";

	$PROXY_ID					= $ENTRADA_USER->getID();
	$VALID_MIME_TYPES			= array("image/pjpeg" => "jpg", "image/jpeg" => "jpg", "image/jpg" => "jpg", "image/gif" => "gif", "image/png" => "png");
	$VALID_MAX_FILESIZE			= 2097512; // 2MB
	$VALID_MAX_DIMENSIONS		= array("photo-width" => 216, "photo-height" => 300, "thumb-width" => 75, "thumb-height" => 104);
	$RENDER						= false;

	$BREADCRUMB[]	= array("url" => ENTRADA_URL."/profile", "title" => "Personal Information");

	$PROCESSED		= array();

	if ((is_array($_SESSION["permissions"])) && ($total_permissions = count($_SESSION["permissions"]) > 1)) {
		$sidebar_html  = "The following individual".((($total_permissions - 1) != 1) ? "s have" : " has")." given you access to their ".APPLICATION_NAME." permission levels:";
		$sidebar_html .= "<ul class=\"menu\">\n";
		foreach ($_SESSION["permissions"] as $access_id => $result) {
			if ($access_id != $ENTRADA_USER->getDefaultAccessId()) {
				$sidebar_html .= "<li class=\"checkmark\"><strong>".html_encode($result["fullname"])."</strong><br /><span class=\"content-small\">Exp: ".(($result["expires"]) ? date("D M d/y", $result["expires"]) : "Unknown")."</span></li>\n";
			}
		}
		$sidebar_html .= "</ul>\n";

		new_sidebar_item("Delegated Permissions", $sidebar_html, "delegated-permissions", "open");
	}

	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/tabpane/tabpane.js?release=".html_encode(APPLICATION_VERSION)."\"></script>\n";
	$HEAD[] = "<link href=\"".ENTRADA_URL."/css/tabpane.css?release=".html_encode(APPLICATION_VERSION)."\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
	$HEAD[] = "<style type=\"text/css\"> .dynamic-tab-pane-control .tab-page {height:auto;}</style>\n";
	$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/profile.js\"></script>";

	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload")) {
		$size_upload = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-upload");
	}
	if (file_exists(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official")) {
		$size_official = getimagesize(STORAGE_USER_PHOTOS."/".$PROXY_ID."-official");
	}
	
	if ($ERROR) {
		fade_element("out", "display-error-box");
		echo display_error();
	}

	if ($SUCCESS) {
		fade_element("out", "display-success-box");
		echo display_success();
	}

	if ($NOTICE) {
		fade_element("out", "display-notice-box");
		echo display_notice();
	}
	
	$ONLOAD[] = "provStateFunction(\$F($('profile-update')['country_id']))";
	
	$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `".AUTH_DATABASE."`.`user_data`.`id`=".$db->qstr($ENTRADA_USER->getID());
	$result	= $db->GetRow($query);
	if ($result) {
		?>
		<script type="text/javascript">
		function provStateFunction(country_id) {
			var url='<?php echo webservice_url("province"); ?>';
			<?php
				if ($PROCESSED["province"] || $PROCESSED["province_id"]) {
					$source_arr = $PROCESSED;
				} else {
					$source_arr = $result;
				}
				$province = $source_arr["province"];
				$province_id = $source_arr["province_id"];
				$prov_state = ($province) ? $province : $province_id;
			?>

			url = url + '?countries_id=' + country_id + '&prov_state=<?php echo $prov_state; ?>';
			new Ajax.Updater($('prov_state_div'), url,
				{
					method:'get',
					onComplete: function (init_run) {

						if ($('prov_state').type == 'select-one') {
							$('prov_state_label').removeClassName('form-nrequired');
							$('prov_state_label').addClassName('form-required');
							if (!init_run)
								$("prov_state").selectedIndex = 0;

						} else {

							$('prov_state_label').removeClassName('form-required');
							$('prov_state_label').addClassName('form-nrequired');
							if (!init_run)
								$("prov_state").clear();

						}
					}.curry(!provStateFunction.initialzed)
				});
			provStateFunction.initialzed = true;

		}
		provStateFunction.initialzed = false;

		</script>

		<h1 style="margin-top: 0px">Personal Information</h1>
		This section allows you to update your <?php echo APPLICATION_NAME; ?> user profile information. Please note that this information does not necessarily reflect any information stored at the main University. <span style="background-color: #FFFFCC; padding-left: 5px; padding-right: 5px">This is not your official university contact information.</span>
		<br /><br />
		<div id="profile-wrapper">
		<script src="<?php echo ENTRADA_URL; ?>/javascript/jquery/jquery.imgareaselect.min.js" type="text/javascript"></script>
		<link href='<?php echo ENTRADA_URL; ?>/css/imgareaselect-default.css' rel='stylesheet' type='text/css' />
		<style type="text/css">
			#profile-wrapper {position:relative;}
			#upload_profile_image_form {margin:0px;float:left;}
			#profile-image-container {position:absolute;right:0px;}
			#upload-image-modal-btn {position:absolute;right:7px;top:7px;display:none;outline:none;}
			#btn-toggle {position:absolute;right:7px;bottom:7px;display:none;outline:none;}
			.profile-image-preview {text-align:center;max-width:250px;margin:auto;}
			.modal-body{max-height:none;}
		</style>
		<?php $profile_image = ENTRADA_ABSOLUTE . '/../public/images/' . $ENTRADA_USER->getID() . '/' . $ENTRADA_USER->getID() . '-large.png'; ?>
		<script type="text/javascript">
		function dataURItoBlob(dataURI, type) {
			type = typeof a !== 'undefined' ? type : 'image/jpeg';
			var binary = atob(dataURI.split(',')[1]);
			var array = [];
			for (var i = 0; i < binary.length; i++) {
				array.push(binary.charCodeAt(i));
			}
			return new Blob([new Uint8Array(array)], {type: type});
		}

		jQuery(function(){

			jQuery("#btn-toggle .btn").live("click", function() {
				var clicked = jQuery(this);
				if (clicked.parent().hasClass(clicked.html().toLowerCase())) { 
					
				} else {
					jQuery.ajax({
						url : "<?php echo ENTRADA_URL; ?>/profile",
						data : "action=togglephoto",
						type : "post",
						async : true,
						success : function(data) {
							var jsonResponse = JSON.parse(data);
							jQuery("#profile-image-container .thumbnail img").attr("src", jsonResponse.data.imgurl);
							jQuery("#btn-toggle .btn.active").removeClass("active");
							clicked.addClass("active");
							clicked.parent().removeClass((jsonResponse.data.imgtype == "uploaded" ? "official" : "uploaded")).addClass(jsonResponse.data.imgtype);
						}
					});
				}
				return false;
			});

			function selectImage(image){
				jQuery(".description").hide();
				var image_width;
				var image_height;
				var w_offset;
				var h_offset

				image_width = image.width();
				image_height = image.height();
				w_offset = parseInt((image_width - 153) / 2);
				h_offset = parseInt((image_height - 200) / 2);

				jQuery("#coordinates").attr("value", w_offset + "," + h_offset + "," + (w_offset + 153) + "," + (h_offset + 200));
				jQuery("#dimensions").attr("value", image_width + "," + image_height)

				image.imgAreaSelect({ 
					aspectRatio: '75:98', 
					handles: true, 
					x1: w_offset, y1: h_offset, x2: w_offset + 153, y2: h_offset + 200,
					instance: true,
					persistent: true,
					onSelectEnd: function (img, selection) {
						jQuery("#coordinates").attr("value", selection.x1 + "," + selection.y1 + "," + selection.x2 + "," + selection.y2);
					}
				});
			};

			jQuery(".org-profile-image").hover(function(){
				jQuery(this).find("#edit-button").animate({"opacity" : 100}, {queue: false}, 150).css("display", "block");
			}, function() {
				jQuery(this).find("#edit-button").animate({"opacity" : 0}, {queue: false}, 150);
			});

			/* file upload stuff starts here */

			var reader = new FileReader();

			reader.onload = function (e) {
				jQuery(".preview-image").attr('src', e.target.result)
				jQuery(".preview-image").load(function(){
					selectImage(jQuery(".preview-image"));
				});
			};

			// Required for drag and drop file access
			jQuery.event.props.push('dataTransfer');

			jQuery("#upload-image").on('drop', function(event) {

				jQuery(".modal-body").css("background-color", "#FFF");

				event.preventDefault();

				var file = event.dataTransfer.files[0];

				if (file.type.match('image.*')) {
					jQuery("#image").html(file);
					reader.readAsDataURL(file);
				} else {
					// However you want to handle error that dropped file wasn't an image
				}
			});

			jQuery("#upload-image").on("dragover", function(event) {
				jQuery(".modal-body").css("background-color", "#f3f3f3");
				return false;
			});

			jQuery("#upload-image").on("dragleave", function(event) {
				jQuery(".modal-body").css("background-color", "#FFF");
			});

			jQuery('#upload-image').on('hidden', function () {
				if (jQuery(".profile-image-preview").length > 0) {
					jQuery(".profile-image-preview").remove();
					jQuery(".imgareaselect-selection").parent().remove();
					jQuery(".imgareaselect-outer").remove();
					jQuery("#image").val("");
					jQuery(".description").show();
				}
			});

			jQuery('#upload-image').on('shown', function() {
				if (jQuery(".profile-image-preview").length <= 0) {
					var preview = jQuery("<div />").addClass("profile-image-preview");
					preview.append("<img />");
					preview.children("img").addClass("preview-image");
					jQuery(".preview-img").append(preview);
				}
			});

			jQuery("#upload-image-button").live("click", function(){
				if (typeof jQuery(".preview-image").attr("src") != "undefined") {
					jQuery("#upload_profile_image_form").submit();
					jQuery('#upload-image').modal("hide");
				} else {
					jQuery('#upload-image').modal("hide");
				}
			});

			jQuery("#upload_profile_image_form").submit(function(){
				var imageFile = dataURItoBlob(jQuery(".preview-image").attr("src"));
				console.log(imageFile);

				var xhr = new XMLHttpRequest();
				var fd = new FormData();
				fd.append('action', 'uploadimage');
				fd.append('image', imageFile);
				fd.append('coordinates', jQuery("#coordinates").val());
				fd.append('dimensions', jQuery("#dimensions").val());

				xhr.open('POST', "<?php echo ENTRADA_URL; ?>/profile", true);
				xhr.send(fd);

				xhr.onreadystatechange = function() {
					if (xhr.readyState == 4 && xhr.status == 200) {                
						var jsonResponse = JSON.parse(xhr.responseText);
						if (jsonResponse.status == "success") {
							jQuery("#profile-image-container .thumbnail img.img-polaroid").attr("src", jsonResponse.data);
						} else {
							// Some kind of failure notification.
						};
					} else {
						// another failure notification.
					}
				}

				if (jQuery(".profile-image-preview").length > 0) {
					jQuery(".profile-image-preview").remove();
					jQuery(".imgareaselect-selection").parent().remove();
					jQuery(".imgareaselect-outer").remove();
					jQuery("#image").val("");
					jQuery(".description").show();
				}

				return false;
			});

			jQuery("#image").live("change", function(){
				var files = jQuery(this).prop("files");

				if (files && files[0]) {
					reader.readAsDataURL(files[0]);
				}
			});

			jQuery("#profile-image-container").hover(function(){
				jQuery("#profile-image-container .btn, #btn-toggle").fadeIn("fast");
			}, 
			function() {
				jQuery("#profile-image-container .btn").fadeOut("fast");
			});
		});
		</script>
		<div id="upload-image" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="label" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="label">Upload Photo</h3>
			</div>
			<div class="modal-body">
				<div class="preview-img"></div>
				<div class="description alert" style="height:264px;width:483px;padding:20px;">
					To upload a new profile image you can drag and drop it on this area, or use the Browse button to select an image from your computer.
				</div>
			</div>
			<div class="modal-footer">
				<form name="upload_profile_image_form" id="upload_profile_image_form" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data">
					<input type="hidden" name="coordinates" id="coordinates" value="" />
					<input type="hidden" name="dimensions" id="dimensions" value="" />
					<input type="file" name="image" id="image" />
				</form>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
				<button id="upload-image-button" class="btn btn-primary">Upload</button>
			</div>
		</div>
		

		<div id="profile-image-container">
			<a href="#upload-image" id="upload-image-modal-btn" data-toggle="modal" class="btn btn-primary" id="upload-profile-image">Upload Photo</a>
			<?php
			$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_photos` WHERE `proxy_id` = ".$db->qstr($result["id"])." AND `photo_active` = '1'";
			$uploaded_photo = $db->GetRow($query);
			?>
			<span class="thumbnail"><img src="<?php echo webservice_url("photo", array($ENTRADA_USER->getID(), $uploaded_photo ? "upload" : "official"))."/".time(); ?>" width="192" height="250" class="img-polaroid" /></span>
			<div class="btn-group" id="btn-toggle" class=" <?php echo $uploaded_photo ? "uploaded" : "official"; ?>">
				<a href="#" class="btn <?php echo $uploaded_photo ? "" : "active"; ?>" id="image-nav-left">Official</a>
				<a href="#" class="btn <?php echo $uploaded_photo ? "active" : ""; ?>" id="image-nav-right">Uploaded</a>
			</div>
		</div>

		<form class="form-horizontal" name="profile-update" id="profile-update" action="<?php echo ENTRADA_URL; ?>/profile" method="post" enctype="multipart/form-data" accept="<?php echo ((@is_array($VALID_MIME_TYPES)) ? implode(",", array_keys($VALID_MIME_TYPES)) : ""); ?>">
			<input type="hidden" name="action" value="profile-update" />
			<div class="control-group">
				<label class="control-label">Last Login:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo ((!$_SESSION["details"]["lastlogin"]) ? "Your first login" : date(DEFAULT_DATE_FORMAT, $_SESSION["details"]["lastlogin"])); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label">Username:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo html_encode($_SESSION["details"]["username"]); ?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Password:</label>
				<div class="controls">
					<a class="btn btn-link" href="<?php echo PASSWORD_CHANGE_URL; ?>">Click here to change password</a>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Account Type:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input"><?php echo ucwords($_SESSION["details"]["group"])." <i class=\"icon-arrow-right\"></i> ".ucwords($_SESSION["details"]["role"]); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label">Organisation:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input">
						<?php
							$query		= "SELECT `organisation_title` FROM `".AUTH_DATABASE."`.`organisations` WHERE `organisation_id` = ".$_SESSION['details']['organisation_id'];
							$oresult	= $db->GetRow($query);
							if($oresult) {
								echo $oresult['organisation_title'];
							}
						?>
					</span>
				</div>
			</div>
			<?php if (isset($_SESSION["details"]["grad_year"])) { ?>
			<div class="control-group">
				<label class="control-label">Graduating Year:</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input">Class of <?php echo html_encode($_SESSION["details"]["grad_year"]); ?></span>
				</div>
			</div>
			<?php } ?>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="prefix">Full Name:</label>
				<div class="controls">
					<select class="inline" id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
						<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
						<?php
						if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
							foreach ($PROFILE_NAME_PREFIX as $key => $prefix) {
								echo "<option value=\"".html_encode($prefix)."\"".(($result["prefix"] == $prefix) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
							}
						}
						?>
					</select>
					<span class="help-inline"><?php echo html_encode($result["firstname"]." ".$result["lastname"]); ?></span>
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="email">Primary E-mail:</label>
				<div class="controls">
					<?php if($_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"] == "faculty") { ?>
					<input type="text" class="input-xlarge" id="email" name="email" value="<?php echo html_encode($result["email"]); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
					<?php } else { ?>
					<span class="input-xlarge uneditable-input"><?php echo html_encode($result["email"]); ?></span>
					<?php } ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_alt">Secondary E-mail:</label>
				<div class="controls">
					<input class="input-xlarge" name="email_alt" id="email_alt" type="text" placeholder="example@email.com" value="<?php echo html_encode($result["email_alt"]); ?>" />
				</div>
			</div>
			<div class="control-group"></div>
			<?php if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"]) { ?>
			<div class="control-group">
				<label class="control-label">Google Account:</label>
				<div class="controls">
					
						<?php
						if (($result["google_id"] == "") || ($result["google_id"] == "opt-out") || ($result["google_id"] == "opt-in") || ($_SESSION["details"]["google_id"] == "opt-in")) {
							?>
							Your <?php echo $GOOGLE_APPS["domain"]; ?> account is <strong>not active</strong>. ( <a href="javascript: create_google_account()" class="action">create my account</a> )
							<script type="text/javascript">
							function create_google_account() {
								$('google-account-details').update('<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif\" width=\"16\" height=\"16\" alt=\"Please wait\" border=\"0\" style=\"margin-right: 2px; vertical-align: middle\" /> <span class=\"content-small\">Please wait while your account is created ...</span>');
								new Ajax.Updater('google-account-details', '<?php echo ENTRADA_URL; ?>/profile', { method: 'post', parameters: { 'action' : 'google-update', 'google_account' : 1, 'ajax' : 1 }});
							}
							</script>
							<?php
						} else {
							$google_address = html_encode($result["google_id"]."@".$GOOGLE_APPS["domain"]);
							?>
							<span class="input-xlarge uneditable-input"><?php echo $google_address; ?></span>
							<?php
						}
						?>
					
				</div>
			</div>
			<?php if ($google_address) { ?>
			<div class="control-group">
				<label class="control-label"></label>
				<div class="controls">
					<a href="#reset-google-password-box" id="reset-google-password" class="btn" data-toggle="modal">Reset my <strong><?php echo $GOOGLE_APPS["domain"]; ?></strong> password</a> <a href="http://webmail.<?php echo $GOOGLE_APPS["domain"]; ?>" class="btn" target="_blank">visit <?php echo html_encode($GOOGLE_APPS["domain"]); ?> webmail</a>
				</div>
			</div>
			<?php } ?>
			<div class="control-group"></div>
			<?php } ?>
			<div class="control-group">
				<label class="control-label" for="telephone">Telephone Number:</label>
				<div class="controls">
					<input class="input-xlarge" name="telephone" id="telephone" type="text" placeholder="Example: 613-533-6000 x74918" value="<?php echo html_encode($result["telephone"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="fax">Fax Number:</label>
				<div class="controls">
					<input class="input-xlarge" name="fax" id="fax" type="text" placeholder="Example: 613-533-3204" value="<?php echo html_encode($result["fax"]); ?>" />
				</div>
			</div>
			<div class="control-group"></div>
			<div class="control-group">
				<label class="control-label" for="country_id">Country:</label>
				<div class="controls">
					<?php
						$countries = fetch_countries();
						if ((is_array($countries)) && (count($countries))) {

							$country_id = ($PROCESSED["country_id"])?$PROCESSED["country_id"]:$result["country_id"];

							echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction(this.value);\">\n";
							echo "<option value=\"0\"".((!country_id) ? " selected=\"selected\"" : "").">-- Select Country --</option>\n";
							foreach ($countries as $country) {
								echo "<option value=\"".(int) $country["countries_id"]."\"".(($country_id == $country["countries_id"]) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
							}
							echo "</select>\n";
						} else {
							echo "<input type=\"hidden\" id=\"country_id\" name=\"country_id\" value=\"0\" />\n";
							echo "Country information not currently available.\n";
						}
					?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="prov_state">Province:</label>
				<div class="controls">
					<div id="prov_state_div" class="padding5v">Please select a <strong>Country</strong> from above first.</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="city">City:</label>
				<div class="controls">
					<input class="input-xlarge" name="city" id="city" type="text" value="<?php echo html_encode($result["city"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="address">Address:</label>
				<div class="controls">
					<input class="input-xlarge" name="address" id="address" type="text" value="<?php echo html_encode($result["address"]); ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="postcode">Postal Code:</label>
				<div class="controls">
					<input class="input-xlarge" name="postcode" id="postcode" type="text" placeholder="Example: K7L 3N6" value="<?php echo html_encode($result["postcode"]); ?>" />
				</div>
			</div>
			<?php if ($_SESSION["details"]["group"] != "student") { ?>
			<div class="control-group">
				<label class="control-label" for="hours">Office Hours:</label>
				<div class="controls">
					<textarea id="office_hours" name="office_hours" maxlength="100"><?php echo html_encode($result["office_hours"]); ?></textarea>
				</div>
			</div>
			<?php } ?>
			<div class="control-group">
				
				<span class="controls">
					<input type="submit" class="btn btn-primary right" value="Save" />
				</div>
			</div>
			
		</form>
		</div>
		<?php
		if (((bool) $GOOGLE_APPS["active"]) && $result["google_id"] && !in_array($result["google_id"], array("opt-out", "opt-in"))) {
			?>
			<div id="reset-google-password-box" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					<h1>Reset <strong><?php echo ucwords($GOOGLE_APPS["domain"]); ?></strong> Password</h1>
				</div>
				<div class="modal-body">
					<div id="reset-google-password-form">
						<div id="reset-google-password-form-status">To reset your <?php echo ucwords($GOOGLE_APPS["domain"]); ?> account password at Google, please enter your new password below and click the <strong>Submit</strong> button.</div>
						<form action="#" method="post">
							<table style="width: 100%; margin-top: 15px" cellspacing="2" cellpadding="0">
								<colgroup>
									<col style="width: 35%" />
									<col style="width: 65%" />
								</colgroup>
								<tbody>
									<tr>
										<td><label for="google_password_1" class="form-required">New Password</label></td>
										<td><input type="password" id="google_password_1" name="password1" value="" style="width: 175px" maxlength="24" /></td>
									</tr>
									<tr>
										<td><label for="google_password_2" class="form-required">Re-Enter Password</label></td>
										<td><input type="password" id="google_password_2" name="password2" value="" style="width: 175px" maxlength="24" /></td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
					<div id="reset-google-password-waiting" class="display-generic" style="display: none">
						<img src="<?php echo ENTRADA_RELATIVE; ?>/images/indicator.gif" width="16" height="16" alt="Please wait" border="0" style="margin-right: 2px; vertical-align: middle" /> <span class="content-small">Please wait while your password is being changed ...</span>
					</div>
					<div id="reset-google-password-success" class="display-success" style="display: none">
						We have successfully reset your <?php echo $GOOGLE_APPS["domain"]; ?> account password at Google.<br /><br />If you would like to log into your webmail account, please do so via <a href="http://webmail.qmed.ca" target="_blank">http://webmail.qmed.ca</a>.
					</div>
				</div>
				<div class="modal-footer">
					<button id="reset-google-password-close" class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<button id="reset-google-password-submit" class="btn btn-primary">Submit</button>
				</div>
			</div>
			<script type="text/javascript" defer="defer">
			$('reset-google-password-close').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				$('reset-google-password-success', 'reset-google-password-waiting').invoke('hide');
	
				$('google_password_1').setValue('');
				$('google_password_2').setValue('');
				Control.Modal.close();
			});
	
			$('reset-google-password-submit').observe('click', function() {
				$('reset-google-password-submit', 'reset-google-password-form').invoke('hide');
				$('reset-google-password-waiting').show();
	
				if ($('google_password_1') && $('google_password_2')) {
					var new_password = $F('google_password_1');
					var test_password = $F('google_password_2');
	
					if (new_password && test_password) {
						if (new_password == test_password) {
							new Ajax.Request('<?php echo ENTRADA_URL; ?>/profile', {
								method: 'post',
								parameters: {
									'action' : 'google-password-reset',
									'password' : new_password,
									'ajax' : 1
								},
								onSuccess: function(response) {
									$('reset-google-password-form-status').update('');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-success').show();
								},
								onFailure: function(response) {
									$('reset-google-password-form-status').update('<div class="display-error">We were unable to reset your password at this time, please try again later. If this error persists please contact the system administrator and inform them of the error.</div>');
									$('reset-google-password-waiting').hide();
									$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
								}
							});
						} else {
							$('reset-google-password-form-status').update('<div class="display-error">Your passwords did not match, please try again.</div>');
							$('reset-google-password-waiting').hide();
							$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
						}
					} else {
						$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
						$('reset-google-password-waiting').hide();
						$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
					}
				} else {
					$('reset-google-password-form-status').update('<div class="display-error" style="margin: 0">Please make sure you enter your new password, then re-enter it again in the space provided.</div>');
					$('reset-google-password-waiting').hide();
					$('reset-google-password-submit', 'reset-google-password-form').invoke('show');
				}
			});

			</script>
			<?php }
	} else {
		$NOTICE++;
		$NOTICESTR[]	= "Unfortunately your ".APPLICATION_NAME." profile is not accessible at this time, please try again later.";

		echo display_notice();

		application_log("error", "A user profile was not available in the database? Database said: ".$db->ErrorMsg());
	}
}