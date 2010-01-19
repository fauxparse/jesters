<?php
	require('../inc/data/head.php'); 
	require "../inc/data/upload_class.php";

	session_start();

	$aid = $_POST['aid'];

	$a = $db->get_row("SELECT * FROM $atbl WHERE id = $aid") ;

	$max_size = 1024*100; // the max. size for uploading
  
	$target_dir = $base . 'album-thumbs/';
	if (perms_process($target_dir)):
		$my_upload = new file_upload;
		$my_upload->upload_dir = $target_dir; // "files" is the folder for the uploaded files (you have to create this folder)
		$my_upload->extensions = array(".jpg", ".JPG", ".jpeg", ".JPEG", ".gif", ".GIF", ".PNG", ".png", ".SWF", ".swf"); // specify the allowed extensions here
		$ext_arr = explode('.', $_FILES['upload']['name']);
		$ext = $ext_arr[count($ext_arr)-1];
		$my_upload->max_length_filename = 100; // change this value to fit your field length in your database (standard 100)	
		$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
		$my_upload->the_file = 'album-' . $aid . '.' . $ext;
		$my_upload->http_error = $_FILES['upload']['error'];
		$my_upload->replace = "y"; // because only a checked checkboxes is true
		$my_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
   

		if ($my_upload->upload()):
			$self = get_self();
			$self = str_replace('ajax/upload_thumb.php', '', $self);
			$tn_path = 'http://' . $self . 'album-thumbs/album-' . $aid . '.' . $ext;
			$db->query("UPDATE $atbl SET aTn = '$tn_path' WHERE id = $aid");
		
			if ($a->active == 1):
				clear_album_cache($aid);
			endif; 
			
            $specs = getimagesize($base . 'album-thumbs/album-' . $aid . '.' . $ext); 
            
			$a = $db->get_row("SELECT * FROM $atbl WHERE id = $aid") ;
			$preview .= preview_options($a);
			$preview = str_replace('"', '\"', $preview); 
			
			$msg = '<img src=\"images/uggo.gif\" onload=\"init_message(\'Preview uploaded!\', 2, true); refill_preview(' . $aid . '); update_preview(\'' . $tn_path . '?dummy=' . random_str() . '\'); toggle_preview(\'\'); this.parentNode.removeChild(this);\" />'; 
		
		else:
			$msg = $my_upload->show_error_string();
		endif; 
	else:
		$msg = '<img src=\"images/uggo.gif\" onload=\"init_message(\'Permission denied for the album-thumbs folder. We tried to set it for you, but were denied. Please CHMOD this folder to 777.\', 3, false); this.parentNode.removeChild(this);\" />'; 
	endif;

?>

<script type="text/javascript" language="javascript">
// <![CDATA[
	window.parent.document.getElementById('upload-prv-msg').innerHTML = "<p><?php _e($msg) ?></p>";
	<?php if (!empty($preview)) { ?>
	window.parent.document.getElementById('preview-img').innerHTML = "<?php _e($preview) ?>";
	<?php } ?>
	window.parent.document.getElementById('thumb-form').smt.value = 'Upload';
	window.parent.document.getElementById('thumb-form').upload.value = '';
// ]]>
</script>
