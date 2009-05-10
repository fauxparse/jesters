<?php
	require('../inc/data/head.php');
	
	session_start();
	
	$aid = $_POST['aid'];
	$dim = $_POST['dim'];
	$quality = $_POST['quality']; 

	$q = "SELECT aTn, path, active FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$tn = $a->aTn;

	$where = explode($a->path . '/tn/', $tn);

	$album_thumb_dir = $base . 'album-thumbs/';

	$source = $base . 'albums/' . $a->path . '/lg/' . $where[1]; 
	$tgt = $album_thumb_dir . 'album-' . $aid . '.jpg';

	if (@perms_process($album_thumb_dir)):                     
 	
		if (file_exists($tgt))
			unlink($tgt);
		createthumb($source, $tgt, $dim, $dim, $quality);   
	
		$self = get_self();
		$self = str_replace('ajax/generate_preview.php', '', $self);
		$tn_path = 'http://' . $self . 'album-thumbs/album-' . $aid . '.jpg';
		$db->query("UPDATE $atbl SET aTn = '$tn_path' WHERE id = $aid");
		
		if ($a->active == 1):
			clear_album_cache($aid); 
		endif;
		
		$q = "SELECT * FROM $atbl WHERE id = $aid";
		$a = $db->get_row($q);
		
		$self = get_self('/ajax/generate_preview.php');
		$img_file = str_replace('http://' . $self, '', $a->aTn);
		$size = getimagesize($base . $img_file);
		
		$o = preview_options($a);
		$o .= '<img src="images/uggo.gif" onload="init_message(\'Preview generated!\', 2, true); update_preview(\'' . $tn_path . '?dummy=' . random_str() . '\'); this.parentNode.removeChild(this);" />';  
	else: 
		$q = "SELECT * FROM $atbl WHERE id = $aid";
		$a = $db->get_row($q);
		
		$self = get_self('/ajax/generate_preview.php');
		$img_file = str_replace('http://' . $self, '', $a->aTn);
		$size = getimagesize($base . $img_file);
		
		$o = preview_options($a);    
		$o .= '<img src="images/uggo.gif" onload="init_message(\'The permissions on the album-thumbs folder are not sufficient to create a custom preview. We tried to set the permissions for you, but were denied. Please CHMOD this folder to 777 and try again.\', 3, false); this.parentNode.removeChild(this);" />';                        
	endif;   
	
	_e($o);   
?>