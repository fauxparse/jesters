<?php
	require('../inc/data/head.php');
	
	if (!isset($_POST['aid'])) { exit; }
	
	ob_start();
		
	set_time_limit(0);
	
	$aid = $_POST['aid'];
	$w = $_POST['valw'];
	$h = $_POST['valh'];
	$quality = $_POST['quality'];
	
	if (!is_numeric($quality) || $quality < 1):
		$quality = 75;
	else:
		if ($quality > 100):
			$quality = 100;
		endif;
	endif;

	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$path = $a->path;

	$album_dir = $base . 'albums/' . $path;
	$lg_path = $album_dir . '/lg';
	$tn_path = $album_dir .  '/tn';
	$hr_path = $album_dir .  '/hr';
    $use_hr = false;

	$link = '?p=edit-album&id=' . $aid . '&tab=generate';
    
	$success = true;
	
	if (perms_process($album_dir)):                     
		if (!is_dir($tn_path)):
			@mkdir($tn_path, octdec($target_perms)) or die('<script type="text/javascript" language="javascript">
			// <![CDATA[
			window.parent.document.getElementById(\'messenger-p\').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\">Unable to create a thumbnail folder for this album. Please CHMOD this album\'s folder to 777 and try again.<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" /></span>";	
			// ]]>
			</script>');
		endif;
	    
		if (is_dir($hr_path)):
			$pics = directory($hr_path, "jpg,JPG,JPEG,jpeg,png,PNG,gif,GIF");
			$use_hr = true;
		else:
			$pics = directory($lg_path, "jpg,JPG,JPEG,jpeg,png,PNG,gif,GIF");
		endif;        
	
		if (count($pics) == 0):
			die('<script type="text/javascript" language="javascript">
			// <![CDATA[
			window.parent.document.getElementById(\'messenger-p\').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\">No images found in this album\'s directory. Please add images before you try to create thumbnails.<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" /></span>";	
			// ]]>
			</script>');
		else:
		    $i = 0;                
		    $total = count($pics);
			foreach ($pics as $pi):
			 	$i++;
				if (file_exists($tn_path . '/' . $pi))
					unlink($tn_path . '/' . $pi);
				if ($use_hr):
					createthumb($hr_path . '/' .$pi, $tn_path . '/'.$pi, $w, $h, $quality);
				else:
				    createthumb($lg_path . '/' .$pi, $tn_path . '/'.$pi, $w, $h, $quality);
				endif;
				echo "<script type=\"text/javascript\" language=\"javascript\">
				// <![CDATA[
				window.parent.document.getElementById('messenger-span').innerHTML = 'Generating thumbs ($i/$total)';	
				// ]]>
				</script>";
				echo str_pad('',4096)."\n";
				ob_flush();  
				flush();
			endforeach;    
			
			ob_end_flush();           			
			
			$thumb_specs = "{$w}x{$h}x{$quality}";
		
			$db->query("UPDATE $atbl SET tn = 1, thumb_specs = '$thumb_specs' WHERE id = $aid");
		    
			if ($a->active == 1):
				clear_album_cache($aid);
			endif;
		    
			$q = "SELECT * FROM $atbl WHERE id = $aid";
			$a = $db->get_row($q);
			
			$msg = 'Thumbnails generated!<img src=\"images/uggo.gif\" onload=\"init_message(\'Thumbnails generated!\', 2, true); update_preview_img(\'' .  $a->id .'\')\" />';
			$refresh = process_pane($a);
			$refresh = str_replace('"', '\"', $refresh);
			$refresh = str_replace("\n", '', $refresh);
			$refresh = str_replace("\r", '', $refresh);
			$inner = fill_preview($aid);
	    endif;
	else:
		$msg = 'The permissions on your albums directory are not sufficient to create thumbnails. We tried to set the permissions, but were denied. Try to CHMOD this album\'s directory to 777 via FTP and try again.';
		$msg .= '<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick(\'\'); this.parentNode.removeChild(this);\" />';
		$success = false;                        
	endif;      
?>   

<script type="text/javascript" language="javascript">
// <![CDATA[ 
	<?php if ($success) { ?>
	window.parent.document.getElementById('process-pane').innerHTML = "<?php _e($refresh); ?>";  
	window.parent.document.getElementById('messenger-p').innerHTML = "<span id=\"messenger-span\" class=\"accept\"><?php _e($msg); ?></span>";
	window.parent.document.getElementById('select-thumb').innerHTML = "<?php _e(str_replace('"', '\"', $inner)); ?>"; 
 	
	<?php } else { ?>
	window.parent.document.getElementById('messenger-p').innerHTML = "<span id=\"messenger-span\" class=\"exclamation\"><?php _e($msg); ?></span>";	
	<?php } ?>
// ]]>
</script>