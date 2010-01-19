<?php
	require('../inc/data/head.php');

  	$email = make_safe($_POST['email']);
	$from = $_POST['from_email'];
	$perms = $_POST['perms'];
	$message = $_POST['message'];
	
	$pwd =  substr(md5(uniqid(microtime())), 0, 6);
	
	$q = "INSERT INTO $utbl (id, usr, pwd, perms) VALUES(NULL, '$email', '$pwd', $perms)";
	
	$self = get_self();
	$self = str_replace('ajax/add_user.php', '', $self);
	
	$path = 'http://' . $self;
	
	$message .= "\n\n------------------------------\n\n";
	$message .= 'Login here: ' . $path . "\n";
	$message .= "Username: $email\nPassword: $pwd\n\n";
	$message .= 'Once you login you can change your password to something more familiar.';
	
	$headers = 'From: ' . $from;
	
	mail($email, 'SlideShowPro Director Login', $message, $headers);
		
	$db->query($q);
	
	session_start();
	
	_e(get_manage_users());
?>