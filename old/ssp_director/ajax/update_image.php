<?php
	require('../inc/data/head.php');
	$id = $_POST['id'];
	$aid = $_POST['aid'];
	$title = make_safe($_POST['title']);
	
	if (!empty($_POST['active']))
		$active = 1;
	else
		$active = 0;
	                          
	$q = "SELECT path, aTn FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);   
	
	$self = get_self();
	$self = str_replace('ajax/update_image.php', '', $self);
	
	$path = 'http://' . $self . 'albums/' . $a->path . '/tn/' . $_POST['filename'];
	$path_alt = 'http://www.' . $self . 'albums/' . $a->path . '/tn/' . $_POST['filename'];
	
	if (!isset($_POST['album-thumb'])):  
		if ($a->aTn == $path || $a->aTn == $path_alt):
			$set_to = '';
		endif;
	else:
    	$set_to = $path;
	endif;
	
	$q = "UPDATE $atbl SET aTn = '$set_to' WHERE id = $aid";
	$db->query($q);
	
	$link = make_safe($_POST['link']);

	if (!empty($_POST['tgt']))
		$tgt = 1;
	else
		$tgt = 0;
	$caption = make_safe($_POST['caption']);
	$pause = $_POST['pause']; 
	
	$q = "UPDATE $itbl SET title = '$title', active = $active, link = '$link', target = $tgt, caption = '$caption', pause = $pause WHERE id = $id";
	$db->query($q);
	
	if ($a->active == 1):
		clear_album_cache($aid); 
	endif;
	
	$o .= '<img src="images/uggo.gif" onload="update_preview(\'' . $set_to . '\'); this.parentNode.removeChild(this);" />';
	_e($o); 
?>