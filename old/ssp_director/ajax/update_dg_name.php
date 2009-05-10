<?php
	require('../inc/data/head.php');
	$did = $_POST['did'];
	$name = make_safe($_POST['name']);
	
	$q = "UPDATE $dtbl SET name = '$name' WHERE id = $did";
	
	$db->query($q);
	
	_e($name);
?>