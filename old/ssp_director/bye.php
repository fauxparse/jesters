<?php
	// Logs the User Out
	session_start();
	session_destroy();
	session_start();
	$_SESSION['status'] = 'Logged out successfully...';
	header('Location: index.php?p=login');
?>