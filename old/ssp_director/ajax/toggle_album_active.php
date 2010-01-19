<?php
	require('../inc/data/head.php');
	session_start();
	
	$aid = $_POST['aid'];
	$value = $_POST['value'];
	           
	$q = "UPDATE $atbl SET active = $value, startHere = 0 WHERE id = $aid";  
	
	$db->query($q);
	
	clear_album_cache($aid);
	
	if ($value == 0):
		$q = "DELETE FROM $dltbl WHERE aid = $aid";
		$db->query($q);
	endif;
	
	_e(get_dashboard());
?>