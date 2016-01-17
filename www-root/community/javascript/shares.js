function noPublic(obj) {
	obj.checked = false;
	alert('Non-Authenticated / Public Users cannot access this function at this time.');

	return;
}



function addFile() {
	if (addFileHTML) {
		var file_id	= $$('#file_list div.file-upload').length;
		var newItem		= new Template(addFileHTML);

		$('file_list').insert(newItem.evaluate({'file_id' : file_id, 'file_number' : (file_id + 1)}));
	}

	return;
}


function uploadFile() {
	/*if($('display-upload-button')) {
		if($('display-upload-status')) {
			if(($('uploaded_file')) && ($('uploaded_file').value != '')) {
				$('display-upload-button').innerHTML = $('display-upload-status').innerHTML;
			}
		}
	}

	if($('upload-file-form')) {
		$('upload-file-form').submit();
	}*/

	$('upload-file-form').submit();
	return;
}

function fetchFilename(file_id) {
	var fn = $('uploaded_file_'+file_id).value;
	if (fn == ''){
		$('uploaded_file_'+file_id).value = '';
	} else {
			var filename = fn.match(/[\/|\\]([^\\\/]+)$/);

			if (filename == null) {
				filename = fn; // Opera
			} else {
				filename = filename[1];
			}

			$('file_'+file_id+'_title').value = filename;
		$('file_'+file_id+'_title').focus();
	}
}

function updateFolderIcon(folder_number) {
	if ($('folder_icon')) {
		if((!folder_number) || (folder_number < 1)  || (folder_number > 6) || (folder_number == '')) {
			folder_number = 1;
		}

		var folder_icon_number = folder_number;

		if($('folder-icon-' + folder_number)) {
			$('folder-icon-1').style.border = '1px solid #FFF';
			$('folder-icon-2').style.border = '1px solid #FFF';
			$('folder-icon-3').style.border = '1px solid #FFF';
			$('folder-icon-4').style.border = '1px solid #FFF';
			$('folder-icon-5').style.border = '1px solid #FFF';
			$('folder-icon-6').style.border = '1px solid #FFF';
			$('folder-icon-' + folder_number).style.border = '1px solid #999999';
		}

		$('folder_icon').value = folder_number;
	}

	return;
}

function acceptButton(cb) {
    if(cb.checked)
    {
        $('upload-button').disabled = false;
    }
    else
    {
        $('upload-button').disabled = true;
    }
    return;
}