<?php
	require('../inc/data/head.php');
	
	session_start();
	
	$id = $_POST['id'];
   
	$q = "DELETE FROM $utbl WHERE id = $id";
	
	$db->query($q);
	
	_e(get_manage_users());

?>