<?php
	require('../inc/data/head.php'); 
	require "../inc/data/upload_class.php";

	session_start();

	$result = false;
  
	$target_dir = $base . 'album-audio/';

	if (perms_process($target_dir)):
		$my_upload = new file_upload;
		$my_upload->upload_dir = $target_dir; // "files" is the folder for the uploaded files (you have to create this folder)
		$my_upload->extensions = array(".mp3"); // specify the allowed extensions here
		$my_upload->max_length_filename = 100; // change this value to fit your field length in your database (standard 100)	
		$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
		$my_upload->the_file = $_FILES['upload']['name'];
		$my_upload->http_error = $_FILES['upload']['error'];
		$my_upload->replace = "y"; // because only a checked checkboxes is true
		$my_upload->do_filename_check = "y"; // use this boolean to check for a valid filename
   

		if ($my_upload->upload()):
			$result = true;
			$aid = $_POST['aid'];
			$db->query("UPDATE $atbl SET audioFile = '{$my_upload->the_file}' WHERE id = $aid");
		
			$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
			if ($a->active == 1):
				clear_album_cache($aid); 
			endif;
			
			$mp3s = directory($target_dir, 'mp3');
			if (count($mp3s) == 0):
				$inner = 'No mp3s in the album-audio folder!';
			else:
				$inner = '<select name=\"track\"><option value=\"None\">No Audio for this Album</option>';
				foreach($mp3s as $m):
					if ($my_upload->the_file == $m):
						$checked = ' selected=\"selected\"';
					else:
						$checked = '';
					endif;
					$inner .= '<option' . $checked . '>' . $m . '</option>';
				endforeach;                                 
                                                                                            
				$inner .= '</select>';                                                          
				$inner .= '<img src=\"images/uggo.gif\" onload=\"init_message(\'Audio uploaded successfully!\', 2, true); this.parentNode.removeChild(this);\" />';
			  endif;
		else:
			$msg = $my_upload->show_error_string();                                                                                       
			$msg .= '<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" />';
		endif; 
	else:
		$msg = 'Permission denied for the album-audio folder. We tried to set it for you, but were denied. Please CHMOD this folder to 777.';
		$msg .= '<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" />';
	endif;   
?>

<script type="text/javascript" language="javascript">
// <![CDATA[ 
	<?php if (!$result) { ?>
	window.parent.document.getElementById('messenger-p').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\"><?php _e($msg) ?></span>";
	<?php } ?>
	<?php if (!empty($inner)) { ?>
	window.parent.document.getElementById('audio-select').innerHTML = "<?php _e($inner) ?>";
	<?php } ?>
	window.parent.document.getElementById('audio-form').smt.value = 'Upload';
	window.parent.document.getElementById('audio-form').upload.value = '';
// ]]>
</script>
