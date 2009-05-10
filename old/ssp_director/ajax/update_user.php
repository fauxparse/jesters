<?php
	require('../inc/data/head.php');
	$id = $_POST['id'];
	$val = $_POST['val'];
   
	$q = "UPDATE $utbl SET perms = $val WHERE id = $id";
	
	$db->query($q);

?>