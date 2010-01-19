<?php
	require('../inc/data/head.php');
    
	$id = $_POST['id'];
	$did = $_POST['did'];
		
	$q = "DELETE FROM $dltbl WHERE id = $id";
	$db->query($q);  
	
	_e(get_dynamic_gallery($did));
	
	clear_dg_cache($did);
?>