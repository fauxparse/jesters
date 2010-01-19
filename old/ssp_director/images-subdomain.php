<?php
	// THE USE OF THIS FILE IS DEPRECATED
	// You should now use images.php?subdomain=1 instead.
	// The file will redirect old links appropriately, including any query strings                      
	
	$query = $_SERVER['QUERY_STRING'];      
	
	if (!empty($query)):
		$query = '&' . $query;                 
	endif;
	
	header("Location: images.php?subdomain=1{$query}");
?>