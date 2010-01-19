<?php
	// Login Processing
	include("inc/data/head.php");
	session_start();
	
	if (authenticate_user($_POST['user'], $_POST['pass'])):
		$q = "SELECT * FROM $utbl WHERE usr = '{$_POST['user']}' AND pwd = '{$_POST['pass']}'";
		$u = $db->get_row($q);
		$_SESSION['login'] = $u->usr;
		$_SESSION['loginID'] = $u->id;
		$_SESSION['perms'] = $u->perms;
		$_SESSION["loginIP"] = $_SERVER["REMOTE_ADDR"];
		if (!empty($_SESSION['redirect']))
			header("Location: index.php?{$_SESSION['redirect']}");
		else
			header('Location: index.php?p=dash');
	else:
		$_SESSION['status'] = 'Incorrect Username and Password. Please try again...';
		header('Location: index.php?p=login');
	endif;
?>