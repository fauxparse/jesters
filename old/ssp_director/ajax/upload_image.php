<?php
	require('../inc/data/head.php'); 
	require "../inc/data/upload_class.php";

	session_start();

	$aid = $_POST['aid'];

	$a = $db->get_row("SELECT active, path, thumb_specs, process_specs FROM $atbl WHERE id = $aid") ;
  
	$target_dir = $base . 'albums/' . $a->path . '/lg/';
	$hr_dir = $base . 'albums/' . $a->path . '/hr/';
	$tn_dir = $base . 'albums/' . $a->path . '/tn/';
	$int_dir = $base . 'albums/' . $a->path . '/director/';
	
	if (perms_process($target_dir)):
		$my_upload = new file_upload;
		$my_upload->upload_dir = $target_dir; // "files" is the folder for the uploaded files (you have to create this folder)
		$my_upload->extensions = array(".jpg", ".JPG", ".jpeg", ".JPEG", ".gif", ".GIF", ".PNG", ".png", ".FLV", ".flv", ".SWF", ".swf"); // specify the allowed extensions here
		$my_upload->max_length_filename = 100; // change this value to fit your field length in your database (standard 100)	
		$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
		$my_upload->the_file = $_FILES['upload']['name'];
		$my_upload->http_error = $_FILES['upload']['error'];
		$my_upload->replace = "y"; // because only a checked checkboxes is true
		$my_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
   

		if ($my_upload->upload()):
			$check = $db->get_results("SELECT id FROM $itbl WHERE aid = $aid AND src = '{$my_upload->the_file}'");
			if (count($check) == 0):
				$i = $db->query("INSERT INTO $itbl (id, src, aid) VALUES(NULL, '{$my_upload->the_file}', $aid)");
				$pi = $my_upload->the_file;  
				if (!empty($a->process_specs) && is_dir($hr_dir)):
					rename($target_dir . $pi, $hr_dir . $pi); 
					$specs = explode('x', $a->process_specs);
					$w = $specs[0]; $h = $specs[1]; $q = $specs[2];
					settype($w, 'integer'); settype($h, 'integer'); settype($q, 'integer'); 
					createthumb($hr_dir . $pi, $target_dir . $pi, $w, $h, $q);
				endif;
				
				if (!empty($a->thumb_specs) && is_dir($tn_dir)):
					$specs = explode('x', $a->thumb_specs);
					$w = $specs[0]; $h = $specs[1]; $q = $specs[2];
					settype($w, 'integer'); settype($h, 'integer'); settype($q, 'integer');
					createthumb($target_dir . $pi, $tn_dir . $pi, $w, $h, $q);
				endif;
				
				if (is_dir($int_dir)):
					createthumb($target_dir . $pi, $int_dir . $pi, 200, 200, 75);
				endif;
				
				$msg = '<span id=\"messenger-span\" class=\"accept\">Image Added! <img src=\"images/uggo.gif\" onload=\"insert_image(' . $i . '); this.parentNode.removeChild(this);\" /></span>'; 
				if ($a->active == 1):
					clear_album_cache($aid);
				endif;
			else:
				$msg = '<span id=\"messenger-span\" class=\"accept\">Image Added! (Existing Image Replaced)<img src=\"images/uggo.gif\" onload=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" /></span>';
			endif; 
		
		else:
			$msg = '<span id=\"messenger-span\" class=\"exclamation\">' . $my_upload->show_error_string();
			$msg .= '<input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\');\" /></span>';
		endif; 
	else:
		$msg = '<span id=\"messenger-span\" class=\"exclamation\">Permission denied for the albums folder. We tried to set it for you, but were denied. Please CHMOD this folder to 777.';
		$msg .= '<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\');\" /></span>';
	endif;     
?>

<script type="text/javascript" language="javascript">
// <![CDATA[
	window.parent.document.getElementById('messenger-p').innerHTML = "<?php _e($msg) ?>";
	window.parent.document.getElementById('image-form').smt.value = 'Upload';
	window.parent.document.getElementById('image-form').upload.value = '';
// ]]>
</script>