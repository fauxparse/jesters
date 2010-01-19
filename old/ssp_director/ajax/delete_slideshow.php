<?php
	require('../inc/data/head.php');
	$id = $_POST['id'];
	
	session_start();
   
	$q = "DELETE FROM $stbl WHERE id = $id";
	$db->query($q);
	
	_e(get_slideshow_list());
?>