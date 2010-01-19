<?php
	require('../inc/data/head.php');
	$aid = $_POST['aid'];
	$action = $_POST['action'];
	
	$q = "SELECT path, active FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	
	if ($action == 'fill') {
		$q = "SELECT id, src FROM $itbl WHERE aid = $aid";
		$imgs = $db->get_results($q);
		
		$hr_dir = $base . 'albums/' . $a->path . '/hr/';
		$self = get_self();
		$self = str_replace('ajax/manage_links.php', '', $self);
		
		foreach ($imgs as $i):
			if (file_exists($hr_dir . $i->src)):
				$tgt = 'hr';
			else:
				$tgt = 'lg';
			endif;                              
			
			$path = 'http://' . $self . 'albums/' . $a->path . '/' . $tgt . '/';

			$q = "UPDATE $itbl SET link = CONCAT('$path', $itbl.src) WHERE id = {$i->id}";
			$db->query($q);
		endforeach;
	} elseif ($action == 'js') {
		$q = "SELECT id, src, title FROM $itbl WHERE aid = $aid";
		$imgs = $db->get_results($q);
		
		$hr_dir = $base . 'albums/' . $a->path . '/hr/';
		$self = get_self();
		$self = str_replace('ajax/manage_links.php', '', $self);
		
		foreach ($imgs as $i):
			if (file_exists($hr_dir . $i->src)):
				$tgt = 'hr';
			else:
				$tgt = 'lg';
			endif;                              
			
			$pop = 'http://' . $self . 'popup.php'; 
			$path = 'albums/' . $a->path . '/' . $tgt . '/' . $i->src;  
			$specs = getimagesize($base . $path); $w = $specs[0]; $h = $specs[1]; 
			$full = $pop . '?src=' . $path . '&w=' . $w . '&h=' . $h . '&title=' . $i->src;
			$link = "javascript:if (window.NewWindow) { NewWindow.close(); }; NewWindow=window.open('$full','myWindow','width=$w,height=$h,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,titlebar=no');NewWindow.focus(); void(0);";
			
			$link = make_safe($link);
			
			$q = "UPDATE $itbl SET link = \"$link\" WHERE id = {$i->id}";
			$db->query($q);
	   endforeach;
    } else {
	   	$q = "UPDATE $itbl SET link = '' WHERE aid = $aid";
		$db->query($q);
	}
	
	if ($a->active == 1):
		clear_album_cache($aid);
	endif;
?>