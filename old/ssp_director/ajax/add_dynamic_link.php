<?php
	require('../inc/data/head.php');
    
	$did = $_POST['did']; 
  	$aid = $_POST['aid'];
		
	$q = "INSERT INTO $dltbl (id, did, aid) VALUES(NULL, $did, $aid)";
	$db->query($q);  
	
	_e(get_dynamic_gallery($did));
	
	clear_dg_cache($did);
?>