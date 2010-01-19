<?php
	require('../inc/data/head.php');
	$order = $_POST['sort'];
	$id = 0;
	
	while (list($key, $val) = each($order)) {
		$key++;
		$q = "UPDATE $dltbl SET display = $key WHERE id = $val";
		$db->query($q);    
		if ($id == 0):
			$a = $db->get_row("SELECT did FROM $dltbl WHERE id = $val");
			$id = $a->did;
		endif;
	}
	
	clear_dg_cache($id);        
?>