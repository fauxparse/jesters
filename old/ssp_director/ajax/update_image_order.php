<?php
	require('../inc/data/head.php');
	$order = $_POST['image-view'];
	
	while (list($key, $val) = each($order)) {
		$key++;
		$q = "UPDATE $itbl SET seq = $key WHERE id = $val";
		$db->query($q);
		if ($aid == 0):
			$i = $db->get_row("SELECT aid FROM $itbl WHERE id = $val");
			$aid = $i->aid;
		endif;
	}
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;
?>