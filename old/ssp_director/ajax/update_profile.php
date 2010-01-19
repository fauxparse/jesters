<?php
	require('../inc/data/head.php');
	
	session_start();
	
	$user = make_safe($_POST['username']);
	$pass = make_safe($_POST['pass']);           
	
	$q = "UPDATE $utbl SET usr = '$user'";
	
	if ($pass != ''):
		$q .= ", pwd = '$pass'";
	endif;
	
	$q .= " WHERE id = {$_SESSION['loginID']}";
	
	$_SESSION['login'] = $user;
 
	$db->query($q);

?>