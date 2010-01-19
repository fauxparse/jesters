<?php
	require('../inc/data/head.php');
    
	session_start();
	
  	$id = $_POST['id'];
	$refer = $_POST['refer'];
		
	$q = "DELETE FROM $dtbl WHERE id = $id";
	$db->query($q);                    
	
	$q = "DELETE FROM $dltbl WHERE did = $id";
	$db->query($q);
	
	if ($refer == 'dashboard'):
		_e(get_dashboard());
	else:
		_e(write_dynamic_list());
    endif;

	clear_dg_cache($id);
?>