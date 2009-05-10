<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	$track = $_POST['track'];
	$description = make_safe($_POST['description']);
    	
	if ($track == 'None')
		$track = NULL;
		
	$q = "UPDATE $atbl SET audioFile = '$track', audioCap = '$description' WHERE id = $aid";
	
	$db->query($q);
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;

?>