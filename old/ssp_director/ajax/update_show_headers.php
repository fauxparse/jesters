<?php
	require('../inc/data/head.php');
	$val = $_POST['val'];
	$aid = $_POST['aid'];
	                                
   	$q = "UPDATE $atbl SET show_headers = $val WHERE id = $aid";
                                    
	$db->query($q);          
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;
?>