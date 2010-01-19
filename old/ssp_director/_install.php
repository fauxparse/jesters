<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>SlideShowPro Director :: Install</title>
<link rel="stylesheet" href="css/master.css" /> 
</head>

<body>

<div id="header" style="margin-bottom:15px;"><img class="logo" src="images/director_logo.gif" width="192" height="48" alt="SlideShowPro Director" /></div>   

<div id="sub-header" class="clearfix">
	<div class="message"><div class="left">
		<p>Installing...</p>
	</div></div>
</div>    

<div id="container" class="clearfix">
	<?php 
		$test = version_compare(phpversion(), "4.3.0");
		if ($test >= 0):
			if (extension_loaded('mysql')):
	?>
	<div class="left">
<?php
	require_once('inc/data/head.php');
	
	$u = $_REQUEST['usr'];
	$p = $_REQUEST['pwd'];
	
	if (!$u){
		$u = 'admin';
		$p = substr(md5(uniqid(microtime())), 0, 6);
	}
	
	// Create Tables
	
	$db->query("CREATE TABLE $atbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(100), description BLOB, path VARCHAR(50), tn TINYINT(1) NOT NULL DEFAULT '0', aTn VARCHAR(150), active TINYINT(1) NOT NULL DEFAULT '0', startHere TINYINT(1) NOT NULL DEFAULT '0', audioFile VARCHAR(100) DEFAULT NULL, audioCap VARCHAR(200) DEFAULT NULL, displayOrder INT(4) DEFAULT '999', target TINYINT(1) NOT NULL DEFAULT '0', thumb_specs VARCHAR(255), process_specs VARCHAR(255), show_headers INT(1) NOT NULL DEFAULT '1')");
	
	echo "<p>Albums table written successfully...</p>";
	
	flush();
	
	$db->query("CREATE TABLE $itbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), aid INT, title VARCHAR(255), src VARCHAR(255), caption TEXT, link TEXT, active TINYINT(1) NOT NULL DEFAULT '1', seq INT(4) NOT NULL DEFAULT '999', pause INT(4) NOT NULL DEFAULT '0', target TINYINT(1) NOT NULL DEFAULT '0')");
	
	echo "<p>Images table written successfully...</p>";
	
	flush();
	
	$db->query("CREATE TABLE $utbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), usr VARCHAR(50), pwd VARCHAR(50), perms TINYINT(1) NOT NULL DEFAULT '1')");
	
	echo "<p>Users table written successfully...</p>";
	
	$db->query("CREATE TABLE $dtbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(100))");
	
	$db->query("CREATE TABLE $dltbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), did INT, aid INT, display INT DEFAULT '800')");
	
	echo "<p>Dynamic gallery tables written successfully...</p>";
	
	$db->query("CREATE TABLE $stbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(255), url VARCHAR(255))");
	
	echo "<p>Slide show tables written successfully...</p>";
	
	$db->query("INSERT INTO $utbl (id, usr, pwd, perms) VALUES (NULL, '$u', '$p', 4)");

?>
	
	<p>Success! <a href="index.php?p=login">Click here</a> to login for the first time with the username <strong><?php echo $u ?></strong> and the password <strong style="color:#292929;"><?php echo $p ?></strong>(Select the empty area to see your password). It is highly recommended that you now delete start.php, write.php and _install.php from your webserver.</p>
	
		<?php else: ?>
	    <h4>SlideShowPro Director requires PHP be built with the MySQL extension enabled. MySQL may very well be installed, but PHP has not been configured to work with MySQL.</h4>
	 	<?php endif; ?>
	<?php else: ?>
	
	<h4>SlideShowPro Director requires a PHP version of at least 4.3.0. We have determined your PHP version to be <?php echo(phpversion())?>. SlideShowPro Director is not compatible with this version of PHP. Contact your host to see if an upgrade to PHP is available.</h4> 
	
   <?php endif; ?>

</div></div>
</body>
</html>
