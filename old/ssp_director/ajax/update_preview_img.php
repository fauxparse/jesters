<?php                    
	require('../inc/data/head.php');
	
	session_start();
	
	$aid = $_POST['aid'];
	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	
	_e(preview_options($a))
?>