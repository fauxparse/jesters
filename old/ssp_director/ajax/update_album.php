<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	$name = make_safe($_POST['name']);
	$description = make_safe($_POST['description']);
	
	$q = "UPDATE $atbl SET name = '$name', description = '$description' WHERE id = $aid";
	
	$db->query($q);
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;
	_e(stripslashes($name));
?>