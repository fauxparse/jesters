<?php
require('../inc/data/head.php'); 

if ($_POST['type'] == 'single'):
	_e(get_single_image($_POST['img'], $_POST['size']));
else:
	$output = '';
	$ids = $_POST['img_ids'];
	$id_arr = explode(',', $ids);
	foreach($id_arr as $i):
		$output .= _e(get_single_image($i, $_POST['size'])); 
	endforeach;
	_e($output);
endif;
     
?>