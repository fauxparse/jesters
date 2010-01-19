<?php
	require('../inc/data/head.php');
    
	set_time_limit(0);
	
	$aid = $_POST['id'];

	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);

	$path = dirname(__FILE__);
	$path = str_replace("ajax", "", $path);

	$album_path = $path . 'albums/' . $a->path . '/lg';
	$dh  = @opendir($album_path);
	while (false !== ($filename = readdir($dh))) {
		if (eregi("gif",$filename) || eregi("jpg",$filename) || eregi("jpeg",$filename) || eregi("swf", $filename) || eregi("png", $filename) || eregi("flv", $filename)) {
			$album_photos[] = $filename;
		}
	}           
    
	$target_dir = $base . 'albums/' . $a->path . '/lg/';
	$hr_dir = $base . 'albums/' . $a->path . '/hr/';
	$tn_dir = $base . 'albums/' . $a->path . '/tn/';
	$int_dir = $base . 'albums/' . $a->path . '/director/';
	
	$i = 0;
	$ids = '';
	for($j = 0; $j < sizeof($album_photos); $j++) {
		$n = $album_photos[$j];
		$q = "SELECT id FROM $itbl WHERE aid = $aid AND src = '$n'";
		$r = mysql_query($q);
	
		if (mysql_num_rows($r) == 0)
		{
			mysql_query("INSERT INTO $itbl (id, aid, src) VALUES (NULL, $aid, '$n')"); 
			$ids .= mysql_insert_id() . ',';
			$pi = $n;
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
			
			$i++;
		}
	}   

	if ($i > 0):
		$ids = rtrim($ids, ',');
		$msg = "<span id=\"messenger-span\" class=\"hourglass\">New images found, adding them now...<img src=\"images/uggo.gif\" onload=\"insert_batch_images('$ids'); this.parentNode.removeChild(this);\" /></span>";
		if ($a->active == 1):
			clear_album_cache($aid); 
		endif;
	else:
		$msg = "<span id=\"messenger-span\" class=\"exclamation\">No new images found.<br /><input type=\"button\" value=\"Ok\" onclick=\"kill_messenger_quick('');\" /></span>";
	endif;

	_e($msg);  
?>