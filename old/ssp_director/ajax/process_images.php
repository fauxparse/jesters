<?php
	require('../inc/data/head.php');
	
	session_start();
	ob_start();     
	set_time_limit(0);
	
	$aid = $_POST['aid'];
	$w = $_POST['pvalw'];
	$h = $_POST['pvalh'];
	$quality = $_POST['pquality'];
	
	if (!is_numeric($quality) || $quality < 1):
		$quality = 75;
	else:
		if ($quality > 100):
			$quality = 100;
		endif;
	endif;

	$q = "SELECT path, active FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$path = $a->path;

	$album_dir = $base . 'albums/' . $path;
	$lg_path = $album_dir . '/lg';
	$hr_path = $album_dir . '/hr';
    
	$accepted_types = "jpg,JPG,JPEG,jpeg,png,PNG";
	if(imagetypes() & IMG_GIF) { $accepted_types .= "GIF,gif"; };
	
	$link = '?p=edit-album&id=' . $aid . '&tab=generate';

    $error = false; 
	$copy_hr = false;

	if (perms_process($album_dir)):
	    
		if (!is_dir($hr_path)):
			@mkdir($hr_path);
			$pics = directory($lg_path, $accepted_types);
			$copy_hr = true;
		else:
			$pics = directory($hr_path, $accepted_types);
		endif;
	
		if (count($pics) == 0):
			die('<script type="text/javascript" language="javascript">
			// <![CDATA[
			window.parent.document.getElementById(\'messenger-p\').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\">No images found in this album\'s directory. Please add images first!.<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" /></span>";	
			// ]]>
			</script>');
		else:
		    $i = 0;
		    $total = count($pics);
			foreach ($pics as $pi):
				$i++;
				if ($copy_hr):
					rename($lg_path . '/' .$pi, $hr_path . '/' . $pi);
				endif;
				createthumb($hr_path . '/' .$pi, $lg_path . '/' .$pi, $w, $h, $quality);
				echo "<script type=\"text/javascript\" language=\"javascript\">
				// <![CDATA[
				window.parent.document.getElementById('messenger-span').innerHTML = 'Processing images ($i/$total)';	
				// ]]>
				</script>";
				echo str_pad('',4096)."\n";
				ob_flush();  
				flush();
			endforeach; 
			
			$process_specs = "{$w}x{$h}x{$quality}";
			$db->query("UPDATE $atbl SET process_specs = '$process_specs' WHERE id = $aid");
		    $msg .= 'Images processed!<img src=\"images/uggo.gif\" onload=\"init_message(\'Images processed!\', 2, true); this.parentNode.removeChild(this);\" />';
		
			$q = "SELECT * FROM $atbl WHERE id = $aid";
			$a = $db->get_row($q);
			
		  	$refresh = process_pane($a);
			$refresh = str_replace('"', '\"', $refresh);
			$refresh = str_replace("\n", '', $refresh);
			$refresh = str_replace("\r", '', $refresh);
	    endif;
	else:
		die('<script type="text/javascript" language="javascript">
		// <![CDATA[
		window.parent.document.getElementById(\'messenger-p\').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\">The permissions on your albums directory are not sufficient to process your images. We tried to set the permissions, but were denied. Try to CHMOD this album\'s directory to 777 via FTP and try again.<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" /></span>";	
		// ]]>
		</script>');                        
	endif;
?>       
 
<script type="text/javascript" language="javascript">
// <![CDATA[ 
	window.parent.document.getElementById('messenger-span').innerHTML = "<?php _e($msg); ?>";
	window.parent.document.getElementById('process-pane').innerHTML = "<?php _e($refresh); ?>";  
// ]]>
</script>            
