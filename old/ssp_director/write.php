<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>SlideShowPro Director :: Install</title>
<link rel="stylesheet" href="css/master.css" />
<script type="text/javascript">
	function checkPwd(){
		elem = document.getElementById('theForm');
		var pwd = elem.pwd.value;
		var pwd2 = elem.pwd2.value;
		
		if (pwd == pwd2)
		{
			return true;
		}
		else
		{
			alert('Passwords Do Not Match!');
			return false;
		}
	}
		
</script>
</head>

<body>

<div id="header" style="margin-bottom:15px;"><img class="logo" src="images/director_logo.gif" width="192" height="48" alt="SlideShowPro Director" /></div>

<div id="sub-header" class="clearfix">
	<div class="message"><div class="left">
		<p>Writing configuration file....</p>
	</div></div>
</div>

<div id="container" class="clearfix">
	<div class="left">
<?php
if ( !$_POST )
{
	die('<h3>Error</h3><p>Please start the installation from <a href="start.php">start.php</a>.</p>');
} else
{
$server = $_REQUEST['svr'];
$db = $_REQUEST['db'];
$usr = $_REQUEST['usr'];
$pwd = $_REQUEST['pwd'];
$pr = $_REQUEST['pr'];

$filename = 'config/';

$perms = substr(sprintf('%o', fileperms($filename)), -4);

if ($perms != '0777')
	@chmod($filename, 0777) or die("<h3>Error</h3><p>SlideShowPro Director does not have the proper permissions to write the <strong>config/conf.php</strong> file. Director tried to set the permissions for you, but was rejected. Please change permissions on this directory to 777 and restart setup.</p><p>If you cannot set the permissions to this directory, open up the example configuration file (conf.php.example) in a text editor, fill in the values manually and save it as conf.php. Then simply run _install.php.</p><p><a href=\"start.php\">Go back to setup</a></p>");
				
$fill = "<?php\n\n";
$fill .= '$host = \''.$server."';\n";
$fill .= '$db = \''.$db."';\n";
$fill .= '$user = \''.$usr."';\n";
$fill .= '$pass = \''.$pwd."';\n\n";
$fill .= '$pre = \''.$pr."';\n\n";
$fill .= '?>';

$filename = $filename . 'conf.php';

$handle = fopen($filename, 'w+');

if (fwrite($handle, $fill) == false)
{
	die('<h3>Error</h3><p>An error occured. Your server may not allow writing files via PHP. If not, follow the instructions for creating the configuration file in the help documents.</p>');
}

fclose($handle);

echo '<h3>Success</h3><p>Your configuration file was successfully created. You are now ready to install SlideShowPro Director. All that is needed is for you to pick a username and password below. This is the username and password you will use to login to SlideShowPro Director.</p>';
?> 

<form id="theForm" action="_install.php" method="post" onsubmit="return checkPwd()">
	<fieldset><label>Username:</label><input type="text" name="usr" /></fieldset>
	<fieldset><label>Password:</label><input type="password" name="pwd" /></fieldset>
	<fieldset><label>Password Again:</label><input type="password" name="pwd2" /></fieldset>
	<fieldset><input type="submit" value="Install Director" /></fieldset>
</form>
<?php } ?>
</div>
</div>
</body>
</html>
