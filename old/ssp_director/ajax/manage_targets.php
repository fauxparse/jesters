<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	$action = $_POST['action'];
	
	if ($action == 'fill'):
		$q = "UPDATE $itbl SET target = 1 WHERE aid = $aid";
	else:
	   $q = "UPDATE $itbl SET target = 0 WHERE aid = $aid";
	endif;
	
	$db->query($q);
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;          
?>