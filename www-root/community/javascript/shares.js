function noPublic(obj) {
	obj.checked = false;
	alert('Non-Authenticated / Public Users cannot access this function at this time.');

	return;
}

function uploadFile() {
	if($('display-upload-button')) {
		if($('display-upload-status')) {
			if(($('uploaded_file')) && ($('uploaded_file').value != '')) {
				$('display-upload-button').innerHTML = $('display-upload-status').innerHTML;
			}
		}
	}

	if($('upload-file-form')) {
		$('upload-file-form').submit();
	}

	return;
}

function fetchFilename() {
	var fn = $('uploaded_file').value;
	if (fn == ''){
		$('uploaded_file').value = '';
	} else {
		if($('file_title').value == '') {
			var filename = fn.match(/[\/|\\]([^\\\/]+)$/);

			if (filename == null) {
				filename = fn; // Opera
			} else {
				filename = filename[1];
			}

			$('file_title').value = filename;
		}

		$('file_title').focus();
	}
}

function updateFolderIcon(folder_number) {
	if($('folder_icon')) {
		if((!folder_number) || (folder_number < 1)  || (folder_number > 6) || (folder_number == '')) {
			folder_number = 1;
		}

		var folder_icon_number = folder_number;

		if($('folder-icon-' + folder_number)) {
			$('folder-icon-1').style.borderColor = '#FFFFFF';
			$('folder-icon-2').style.borderColor = '#FFFFFF';
			$('folder-icon-3').style.borderColor = '#FFFFFF';
			$('folder-icon-4').style.borderColor = '#FFFFFF';
			$('folder-icon-5').style.borderColor = '#FFFFFF';
			$('folder-icon-6').style.borderColor = '#FFFFFF';
			$('folder-icon-' + folder_number).style.borderColor = '#999999';
		}

		$('folder_icon').value = folder_number;
	}

	return;
}