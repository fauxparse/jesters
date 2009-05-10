<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	
	session_start();
	
	clear_album_cache($aid);        
	
	$q = "SELECT id, path FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$path = $a->path;
	
	$q = "DELETE FROM $atbl WHERE id = $aid";
	$db->query($q);
	
	$q = "DELETE FROM $itbl WHERE aid = $aid";
	$db->query($q);
	
	$q = "DELETE FROM $dltbl WHERE aid = $aid";
	$db->query($q);
	
	$album_dir = $base . 'albums/' . $path; 
	$album_thumb_dir = $base . 'album-thumbs/'; 
	
	if (!empty($path)):
		$q = "SELECT id FROM $atbl WHERE path = '$path'";
		$check = $db->get_results($q);
		if (count($check) == 0):
			if (perms_process($album_dir)):
				rmdirr($album_dir);
			endif;
		endif;    
		if (perms_process($album_thumb_dir)):
			$str = 'album-' . $a->id; 
			$pics = directory($album_thumb_dir, "jpg,JPG,JPEG,jpeg,png,PNG");
			foreach ($pics as $p):
				if (eregi($str, $p)):
					@unlink($album_thumb_dir . $p);
				endif;
			endforeach;
		endif;
	endif;
	
	_e(get_dashboard());
?>