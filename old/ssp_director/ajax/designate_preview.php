<?php
	require('../inc/data/head.php'); 
	
	session_start();
	
	$aid = $_POST['aid'];
	$img = $_POST['img'];
                               
	$db->query("UPDATE $atbl SET aTn = '$img' WHERE id = $aid");
	
	$a = $db->get_row("SELECT id, active, path, aTn FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;

	$o = preview_options($a);
	$o .= '<img src="images/uggo.gif" onload="update_preview(\'' . $img . '\'); this.parentNode.removeChild(this);" />';
	
	_e($o);
?>