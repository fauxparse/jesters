<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>SlideShowPro Director :: Install</title>
<link rel="stylesheet" href="css/master.css" />
<script type="text/javascript" language="javascript">
	function validate_this() {
		theForm = document.forms[0];
		if (theForm.svr.value == '') {
			alert("Please give the address to your MySQL host.");
			return false;
		}
		if (theForm.db.value == '') {
			alert("Please give the database that you want Director to be installed in.");
			return false;
		}
		if (theForm.usr.value == '') {
			alert("Please give the user that Director should use to login to your MySQL host.");
			return false;
		}
		if (theForm.pwd.value == '') {
			if (confirm("You have not given a password so Director will try to login with no password. Is this correct?"))
				return true;
			else
				return false;
		}                    
		return true;
	}
</script> 
</head>

<body>

<div id="header" style="margin-bottom:15px;"><img class="logo" src="images/director_logo.gif" width="192" height="48" alt="SlideShowPro Director" /></div>   

<div id="sub-header" class="clearfix">
	<div class="message">
		<div class="left">
			<p>Welcome to SlideShowPro Director!</p>
		</div>
	</div>
</div>
<div id="container">
	<?php 
		$test = version_compare(phpversion(), "4.3.0");
		if ($test >= 0):
			if (extension_loaded('mysql')): 
	?>
<form action="write.php" method="post" style="margin:0;padding:0;" onsubmit="return validate_this();">
	<h3>Server Details :: Be sure you have created the database first!</h3>
	<fieldset style="margin-top:.8em;"><label>MySQL Server:</label><input type="text" name="svr" /> <small>ex. mysql.server.com</small></fieldset>
	<fieldset><label>Database Name:</label><input type="text" name="db" /></fieldset>
	<fieldset><label>Username:</label><input type="text" name="usr" /></fieldset>
	<fieldset><label>Password:</label><input type="password" name="pwd" /></fieldset>
	
	<h3>Advanced Settings</h3>
	<fieldset style="margin-top:.8em;"><label>MySQL Table Prefix:</label><input type="text" name="pr" value="ssp_" /> <small>Change only if this causes a conflict with existing tables in your database.</small></fieldset>
	<fieldset><input type="submit" value="Start Install" /></fieldset>
</form>
	<?php else: ?>
    <h4>SlideShowPro Director requires PHP be built with the MySQL extension enabled. MySQL may very well be installed, but PHP has not been configured to work with MySQL.</h4>
 	<?php endif; ?>
   <?php
		else:
   ?>        
	<h4>SlideShowPro Director requires a PHP version of at least 4.3.0. We have determined your PHP version to be <?php echo(phpversion())?>. SlideShowPro Director is not compatible with this version of PHP. Contact your host to see if an upgrade to PHP is available.</h4> 
   <?php endif; ?>
</div>
</body>
</html>
