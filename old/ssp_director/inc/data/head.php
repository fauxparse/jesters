<?php
	if (!defined('PATH_SEPARATOR') ) {
	    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
	}
	$sep = PATH_SEPARATOR;
	
	if ($sep == ';'):
		$slash = '\\';    
	else:
		$slash = '/';    
	endif;
	
	$base = str_replace("inc{$slash}data", '', dirname(__FILE__));
	
	// Because Windows systems just like to throw session data all over the place
	if (session_save_path() == '.\\'):
		session_save_path($base);
	endif;
	
	error_reporting(0);
	
	ini_set('include_path', ini_get('include_path') . $sep . dirname(__FILE__) . $sep . dirname(__FILE__) . '/magpie' . $sep . dirname(__FILE__) . '/../user' . $sep . dirname(__FILE__) . '/../../config');
	
	if (!@include_once('conf.php')):
		die("No configuration file found. Please begin installation from <a href=\"start.php\">start.php</a>.");
	endif;
	
	@include('user_setup.php');
	require_once('functions.php');
	require_once('connect.php');
	require_once('authentication.php');
	require_once('rss_fetch.inc');
	
	header('Content-type: text/html; charset=utf-8');  
?>