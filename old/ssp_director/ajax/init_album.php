<?php
	require('../inc/data/head.php'); 
	require "../inc/data/upload_class.php";
	require "../inc/data/Zip.php";
     
	session_start();
    
	set_time_limit(0);
	
	$method = $_POST['images-format'];

	$album_name = make_safe($_POST['album_name']);
    	
	$target_dir = $base . 'albums/';

	@perms_process($target_dir);

	$q = "INSERT INTO $atbl (id, name) VALUES (NULL, '$album_name')";
	$new_id = $db->query($q);   
                                        
	if ($method == 2):
		if (!@mkdir($target_dir . 'album-' .$new_id, octdec($target_perms))):
			$q = "DELETE FROM $atbl WHERE id = $new_id";
			$db->query($q);
			$status = 2;
			$msg = 'There was an error when uploading your file(s). We tried to work in the albums directory, but did not have the necessary permissions. Please CHMOD the albums directory to 777 and try again.';
		else:
			@mkdir($target_dir . 'album-' .$new_id . '/lg', octdec($target_perms));
			$target_dir = $base . 'albums/album-' . $new_id . '/lg/';
		endif;
	elseif ($method == 3):
		$new_path = $_POST['scan-this'];
		$q = "UPDATE $atbl SET path = '$new_path' WHERE id = $new_id";
		$db->query($q);
		$path = $new_path;
		$status = 1;
	endif;

	if (empty($status)):	
		$my_upload = new file_upload;
		$my_upload->upload_dir = $target_dir; // "files" is the folder for the uploaded files (you have to create this folder)
		if ($method == 1):
			$my_upload->extensions = array(".zip", ".ZIP"); // specify the allowed extensions here
		else:
			$my_upload->extensions = array(".jpg", ".JPG", ".jpeg", ".JPEG", ".gif", ".GIF", ".PNG", ".png", ".FLV", ".flv", ".SWF", ".swf");
		endif;
		$my_upload->max_length_filename = 255; // change this value to fit your field length in your database (standard 100)	
		$my_upload->the_temp_file = $_FILES['upload']['tmp_name'];
	    $my_upload->the_file = $_FILES['upload']['name'];
		$my_upload->http_error = $_FILES['upload']['error'];
		$my_upload->replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n"; // because only a checked checkboxes is true
		$my_upload->do_filename_check = (isset($_POST['check'])) ? $_POST['check'] : "n"; // use this boolean to check for a valid filename
    
		if ($method == 1):
			if ($my_upload->upload()):
				if (!@mkdir($target_dir . 'album-' . $new_id, octdec($target_perms))):
					unlink($target_dir . $my_upload->the_file);
					$q = "DELETE FROM $atbl WHERE id = $new_id";
					$db->query($q);
					$msg = 'There was an error when uploading your file(s). We tried to create a folder for them, but our access was denied. You might try to set the permissions yourself to 777.';
					$status = 2;
				else:
					$my_zip = new Archive_Zip($target_dir . $my_upload->the_file);
					$my_zip->extract(Array('add_path'=>'../albums/album-' . $new_id));
					unlink($target_dir . $my_upload->the_file);
					$q = "UPDATE $atbl SET path = 'album-$new_id' WHERE id = $new_id";
					$lg = $target_dir . 'album-' . $new_id . '/lg/';
					if (!is_dir($lg)):
						mkdir($lg);
						$album_path = $target_dir . 'album-' . $new_id . '/';
						$dh = @opendir($album_path);
						while (false !== ($filename = readdir($dh))):
							if (eregi("gif",$filename) || eregi("jpg",$filename) || eregi("jpeg",$filename) || eregi("swf", $filename) || eregi("png", $filename) || eregi("flv", $filename)):
								rename("$album_path/$filename", "$lg/$filename");
							endif;
						endwhile;
					endif;
					$db->query($q);
					$status = 1;
				endif;
			else:
				$msg = $my_upload->show_error_string();
				if (empty($msg))
					$msg = 'Your ZIP file could not be uploaded because Director does not have the necessary permissions to upload to your server. Please change the permissions on the albums directory to 777.';
				$status = 2;
				$q = "DELETE FROM $atbl WHERE id = $new_id";
				$db->query($q);
			endif;
		else:
		     if ($my_upload->upload()):
			 	$q = "UPDATE $atbl SET path = 'album-$new_id' WHERE id = $new_id";
				$id = $db->query($q);
				$new_path = "album-$new_id";
				$status = 1;
			 else:
				$q = "DELETE FROM $atbl WHERE id = $new_id";
				$db->query($q);                                                                           
				$status = 2;
				$msg = $my_upload->show_error_string();
				@rmdirr($base . 'albums/album-' . $new_id . '/');
			 endif;
		endif;
	endif;

	if ($status == 1):
		if (empty($path))
			$path = "album-$new_id";
		$album_photos_dir = $base . 'albums/' . $path . '/lg';

		$dh = @opendir($album_photos_dir);
		if ($dh == false):
			$q = "DELETE FROM $atbl WHERE id = $new_id";
			$db->query($q);
			$status = 2;
			$msg = 'There was an error after uploading. ';
			if ($method == 1):
				$msg .= 'Please ensure that you uploaded the ZIP file in the correct structure as specified in the user guide.';
				@rmdirr($base . 'albums/' . $path . '/');
			else:
				$msg .= 'Did you upload the folder with the proper structure?' . $path;
			endif;                                       
		else:
			while (false !== ($filename = readdir($dh))):
				if (eregi("gif",$filename) || eregi("jpg",$filename) || eregi("jpeg",$filename) || eregi("swf", $filename) || eregi("png", $filename) || eregi("flv", $filename)):
					$album_photos[] = $filename;
				endif;
			endwhile;

			natsort($album_photos);  
			$album_photos = array_values($album_photos);
            
			if (!isset($make_internals) || $make_internals):
				$gdv = gd_version();
				if ($gdv != 0 && @mkdir($base . 'albums/' . $path . '/director')):
					$make_internals = true;
				else:
					$make_internals = false;
				endif;                       
			endif;
			
			for ($i = 0; $i < sizeof($album_photos); $i++):
				$src = $album_photos[$i];
				if ($make_internals && ((eregi("gif",$src) && (imagetypes() & IMG_GIF)) || eregi("jpg",$src) || eregi("jpeg",$src) || eregi("png", $src))):   
					createthumb($base . 'albums/' . $path . '/lg/' . $src, $base . 'albums/' . $path . '/director/' . $src, 200, 200, 75);
				endif;
				$q = "INSERT INTO $itbl (id, aid, src, seq) VALUES (NULL, $new_id, '$src', $i)";
				$db->query($q);
			endfor;
			
			if (is_dir($base . 'albums/' . $path . '/__MACOSX'))
				@rmdirr($base . 'albums/' . $path . '/__MACOSX');
		endif;
	endif;

	$self = get_self();
	$self = str_replace('ajax/init_album.php', '', $self);  

	$url = 'http://' . $self . '?p=edit-album&id=' . $new_id . '&new=1';
	$msg .= '<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" />';       
?>

<script type="text/javascript" language="javascript">
// <![CDATA[
	<?php if ($status == 2) { ?>
	window.parent.document.getElementById('messenger-p').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\"><?php _e($msg) ?></span>";
	<?php } else { ?>
	window.parent.location.href = "<?php _e($url) ?>";
	<?php } ?>
// ]]>
</script>