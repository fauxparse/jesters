<?php
	require('../inc/data/head.php');

  	$name = make_safe($_POST['name']);
	$url = make_safe($_POST['url']);
		
	$q = "INSERT INTO $stbl (id, name, url) VALUES(NULL, '$name', '$url')";
		
	$new_id = $db->query($q);
	
	session_start();
	
	_e(get_slideshow_list(true, $new_id));

?>