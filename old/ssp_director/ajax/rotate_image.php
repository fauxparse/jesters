<?php
	require('../inc/data/head.php');
	session_start();
	
	$id = $_POST['id'];                       
	$aid = $_POST['aid']; 
	$r = $_POST['deg'];
	
	$q = "SELECT aid, src FROM $itbl WHERE id = $id";
	$img = $db->get_row($q); 
	
	$q = "SELECT path, thumb_specs, process_specs FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	
	$path = $base . 'albums/' . $a->path . '/lg/' . $img->src;
	$path_tn = $base . 'albums/' . $a->path . '/tn/' . $img->src;
	$path_hr = $base . 'albums/' . $a->path . '/hr/' . $img->src; 
	$path_dir = $base . 'albums/' . $a->path . '/director/' . $img->src; 
		
	if (file_exists($path_hr)):
		rotate_img($path_hr, $r);
		
		if (!empty($a->process_specs)):  
			$specs = explode('x', $a->process_specs);
			$w = $specs[0]; $h = $specs[1]; $q = $specs[2];
			settype($w, 'integer'); settype($h, 'integer'); settype($q, 'integer');
			createthumb($path_hr, $path, $w, $h, $q);
		else:
			rotate_img($path, $r);
		endif;
		
		if (!empty($a->thumb_specs)):  
			$specs = explode('x', $a->thumb_specs);
			$w = $specs[0]; $h = $specs[1]; $q = $specs[2];
			settype($w, 'integer'); settype($h, 'integer'); settype($q, 'integer');
			createthumb($path_hr, $path_tn, $w, $h, $q);
		else:
			rotate_img($path_tn, $r);
		endif;
		
		if (file_exists($path_dir)):
			createthumb($path_hr, $path_dir, 200, 200, 75);
		endif;
	else:
		rotate_img($path, $r);
	
		if (file_exists($path_tn)):
			rotate_img($path_tn, $r);
		endif; 
	
		if (file_exists($path_hr)):
			rotate_img($path_hr, $r);
		endif;
		
		if (file_exists($path_dir)):
			rotate_img($path_dir, $r);
		endif;
   endif;
?>