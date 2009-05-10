<?php
	require('../inc/data/head.php');
	
	session_start();
  	
	$name = make_safe($_POST['new_name']);
	$q = "INSERT INTO $dtbl (id, name) VALUES(NULL, '$name')";
	$new_id = $db->query($q);  
	
	_e(write_dynamic_list(true, $new_id));
?>