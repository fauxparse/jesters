<?php
	require('../inc/data/head.php');
	$order = $_POST['active-albums'];
	$aid = 0;
	
	while (list($key, $val) = each($order)) {
		$key++;
		$q = "UPDATE $atbl SET displayOrder = $key WHERE id = $val";
		$db->query($q);
		if ($aid == 0) { $aid = $val; };
	}
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_main_cache(); 
	endif;
?>