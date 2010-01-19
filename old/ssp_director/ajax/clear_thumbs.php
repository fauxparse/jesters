<?php
	require('../inc/data/head.php');
	
	session_start();
	
	$aid = $_POST['aid'];

	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$path = $a->path;

	$album_dir = $base . 'albums/' . $path;
	$tn_path = $album_dir .  '/tn';
    $clear_prv = false;

	if (perms_process($tn_path)):                     
		rmdirr($tn_path);
		$pos = strpos($a->aTn, '/tn/'); 
		if ($pos):
			$set = ", aTn = ''";
			$clear_prv = true;
		else:
			$set = '';
		endif;
		$db->query("UPDATE $atbl SET tn = 0{$set}, thumb_specs = '' WHERE id = {$a->id}");
		if ($a->active == 1):
			clear_album_cache($aid); 
		endif;
	endif;
	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	$o = process_pane($a); 
	if ($clear_prv) { 
		$o .= "<img src=\"images/uggo.gif\" onload=\"update_preview_img(" .  $a->id . "); this.parentNode.removeChild(this);\" />";
	}               
	_e($o);      
?>