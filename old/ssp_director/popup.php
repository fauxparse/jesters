<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo($_GET['title']); ?></title> 
		<style type="text/css" media="screen">
		/* <![CDATA[ */
			* { margin:0; padding:0; }
		/* ]]> */
		</style>
		
	</head>
	
	<body>
    <img src="<?php echo($_GET['src']); ?>" width="<?php echo($_GET['w']); ?>" height="<?php echo($_GET['h']); ?>" alt="<?php echo($_GET['title']); ?>">
	
	</body>
</html>

