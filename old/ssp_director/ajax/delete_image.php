<?php
	require('../inc/data/head.php');
	$id = $_POST['id'];
	
	$q = "SELECT src, aid FROM $itbl WHERE id = $id";
	$i = $db->get_row($q);
	$src = $i->src;
	
	$q = "SELECT id, path, aTn, active FROM $atbl WHERE id = {$i->aid}";
	$a = $db->get_row($q);
	$path = $a->path;
	
	$q = "DELETE FROM $itbl WHERE id = $id";
	$db->query($q);
	
	clear_album_cache($i->aid);                                             
	
	$clear_thumb = false;

	if (!empty($a->aTn)):
		$atn_arr = explode('albums/', $a->aTn);
		if (count($atn_arr) > 0):
			$str = $atn_arr[1];
			if ($str == $path . '/tn/' . $src):
				$db->query("UPDATE $atbl SET aTn = '' WHERE id = {$a->id}"); 
				$clear_thumb = true;
			endif;
		endif;    
	endif;
	
	$album_dir = $base . 'albums/' . $path;
	
	@unlink($album_dir . '/lg/' . $src) or die('<span id="messenger-span" class="exclamation">Image deleted from the database. An error occurred when deleting it from the server. The program did not have sufficient permissions to delete it.</span>');
	
	// If the above was successfull, these should be as well
	
	// In case they have a high rez folder
	@unlink($album_dir . '/hr/' . $src);
	// Take care of the thumb if it exists
   	@unlink($album_dir . '/tn/' . $src);
	// Director internals
	@unlink($album_dir . '/director/' . $src);
	
	$o .= '<span id="messenger-span" class="accept">Image deleted from the database and server successfully.';
	if ($clear_thumb):
		$o .= '<img src="images/uggo.gif" onload="update_preview(\'\'); this.parentNode.removeChild(this);" />';
	endif;                                                                                                      
	$o .= '</span>';
	_e($o);
?>                                      