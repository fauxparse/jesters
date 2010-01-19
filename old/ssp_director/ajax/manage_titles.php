<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	$action = $_POST['action'];   
	
	if ($action == 'fill'):
		$q = "SELECT id, src FROM $itbl WHERE aid = $aid";
		$imgs = $db->get_results($q);
		if (count($imgs) > 0):
			$template = $_POST['str'];
			foreach($imgs as $i):
				$copy = $template;
				$copy = str_replace('[img_name]', $i->src, $copy);
				$q = "UPDATE $itbl SET title = '$copy' WHERE id = {$i->id}";
				$db->query($q);
				$copy = null;
			endforeach;
		endif;
	else:
	   $q = "UPDATE $itbl SET title = '' WHERE aid = $aid";
		$db->query($q);
	endif;
	
	$a = $db->get_row("SELECT active FROM $atbl WHERE id = $aid"); 
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;
?>