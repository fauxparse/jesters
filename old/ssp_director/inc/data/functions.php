<?php
	require("find_self.php");

	$max_size = return_bytes(ini_get('upload_max_filesize')); // the max. size for uploading
	define("MAX_SIZE", $max_size);

	if (!isset($target_perms))
		$target_perms = '0777';
	
	if (!isset($externals))
		$externals = true;

	//// UTILITIES

	// Shorthand for echo
	function _e($txt) {
		echo $txt;
	}

	function return_bytes($val) {
	   $val = trim($val);
	   $last = strtolower($val{strlen($val)-1});
	   switch($last) {
	       case 'g':
	           $val *= 1024;
	       case 'm':
	           $val *= 1024;
	       case 'k':
	           $val *= 1024;
	   }
	   return $val;
	}

	function clear_dg_cache($did) {
		global $base;
	   	$cache_dir = "$base/xml_cache";
		@perms_process($cache_dir);
		if (file_exists("$cache_dir/images_gid_$did.xml")) { @unlink("$cache_dir/images_gid_$did.xml"); };
		if (file_exists("$cache_dir/images_gid_{$did}_no_www.xml")) { @unlink("$cache_dir/images_gid_{$did}_no_www.xml"); };
	}

	function clear_main_cache() {
		global $base;
		$cache_dir = "$base/xml_cache";
		@perms_process($cache_dir);
	   	if (file_exists("$cache_dir/images.xml")) { @unlink("$cache_dir/images.xml"); };
		if (file_exists("$cache_dir/images_no_www.xml")) { @unlink("$cache_dir/images_no_www.xml"); };
	}  

	function clear_album_cache($id) {
		global $db, $dltbl, $base;
		$cache_dir = "$base/xml_cache";                              
		@perms_process($cache_dir);
		if (file_exists("$cache_dir/images.xml")) { @unlink("$cache_dir/images.xml"); };
		if (file_exists("$cache_dir/images_no_www.xml")) { @unlink("$cache_dir/images_no_www.xml"); };
		$links = $db->get_results("SELECT did FROM $dltbl WHERE aid = $id");
		if (count($links) > 0):
			foreach($links as $l):
				clear_dg_cache($l->did);
			endforeach;                      
		endif;
	}

	// Check GD
	function gd_version($user_ver = 0) {
	   if (! extension_loaded('gd')) { return; }
	   static $gd_ver = 0;
	   if ($user_ver == 1) { $gd_ver = 1; return 1; }
	   if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
	   if (function_exists('gd_info')) {
	       $ver_info = gd_info();
	       preg_match('/\d/', $ver_info['GD Version'], $match);
	       $gd_ver = $match[0];
	       return $match[0];
	   }
	   if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
	       if ($user_ver == 2) {
	           $gd_ver = 2;
	           return 2;
	       } else {
	           $gd_ver = 1;
	           return 1;
	       }
	   }
	   ob_start();
	   phpinfo(8);
	   $info = ob_get_contents();
	   ob_end_clean();
	   $info = stristr($info, 'gd version');
	   preg_match('/\d/', $info, $match);
	   $gd_ver = $match[0];
	   return $match[0];
	}

	// Clean up stuff going into the DB
	function make_safe($val) {
		if (!get_magic_quotes_gpc()):
			$val = mysql_real_escape_string($val);
		endif;
 
		return $val;
	}                                     
	// Permissions
	function perms_process($path) {
		global $target_perms;
		$perms = substr(sprintf('%o', fileperms($path)), -4);
		settype($perms, "string"); 
		if ($perms === $target_perms):
			return true;
		else:                 
			if (@chmod($path, octdec($target_perms)))
				return true;
			else
				return false;
	    endif;
	}   

	// Get all files in a folder by filter, returns them in an array
	function directory($dir,$filters) {
		$handle=opendir($dir);
		$files=array();
		if ($filters == "all"){while(($file = readdir($handle))!==false){$files[] = $file;}}
		if ($filters != "all"){
			$filters=explode(",",$filters);
			while (($file = readdir($handle))!==false) {
				for ($f=0;$f<sizeof($filters);$f++):
					$system=explode(".",$file);
					if ($system[1] == $filters[$f]){$files[] = $file;}
				endfor;
			}
		}
		closedir($handle);
		return $files;
	}

	// Make the thumbs
	function createthumb($name, $filename, $new_w, $new_h, $quality) {
		$system = explode(".", $name);
		$count = count($system);
		if (preg_match("/jpg|jpeg|JPG|JPEG/",$system[$count-1])) { $src_img=imagecreatefromjpeg($name); }
		if (preg_match("/png/",$system[$count-1])) { $src_img=imagecreatefrompng($name); }
		if (preg_match("/gif|GIF/",$system[$count-1])) {
			$src_img=imagecreatefromgif($name);
			if (imagetypes() & IMG_GIF):      
				$src_img=imagecreatefromgif($name);
		   	else:
				return;
			endif;
		}
		$old_x = imagesx($src_img);
		$old_y = imagesy($src_img);

		$original_aspect = $old_x/$old_y;
		$new_aspect = $new_w/$new_h;
		
		if ($original_aspect >= $new_aspect):
			$thumb_w=$new_w;
			$thumb_h=($new_w*$old_y)/$old_x;
		else:
			$thumb_w=($new_h*$old_x)/$old_y;
			$thumb_h=$new_h;
		endif;
		
		if (gd_version() != 2):
			$dst_img=imagecreate($thumb_w, $thumb_h);
			imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);    
		else:
			$dst_img=imagecreatetruecolor($thumb_w,$thumb_h);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y); 
		endif;
		if (preg_match("/png/",$system[1])):
			imagepng($dst_img, $filename); 
	    elseif (preg_match("/gif|GIF/", $system[1])):
			imagegif($dst_img, $filename);
		else:
			imagejpeg($dst_img, $filename, $quality); 
		endif;
		imagedestroy($dst_img); 
		imagedestroy($src_img); 
	}
	
	function rotate_img($name, $r){ 
		$system=explode(".",$name);
		$count = count($system);
		if (preg_match("/jpg|jpeg|JPG|JPEG/",$system[$count-1])){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/png/",$system[$count-1])){$src_img=imagecreatefrompng($name);}
		if (preg_match("/gif|GIF/",$system[$count-1])) {
			if (imagetypes() & IMG_GIF):      
				$src_img=imagecreatefromgif($name);
			else:
				return;
			endif;
		}   
		
		$new = imagerotate($src_img, $r, 0);
		
		if (preg_match("/png/",$system[1])):
			imagepng($new, $name); 
	    elseif (preg_match("/gif|GIF/", $system[1])):
			imagegif($new, $name);
		else:
			imagejpeg($new, $name, 95); 
		endif;
	   
		imagedestroy($src_img); 
	}

	// Recursive Directory Removal
	function rmdirr($dir) {
	   	if (!$dh = @opendir($dir)) return;
	   	while (($obj = readdir($dh))):
	       	if ($obj=='.' || $obj=='..') continue;
	       	$path = $dir.'/'.$obj;
			if (is_dir($path)):
				@rmdirr($path);
			else:
				@unlink($path);
			endif;
	   	endwhile;
	 	closedir($dh);
	   	rmdir($dir);
	}

	function dynamic_count($id) {
		global $db, $dltbl;
		$q = "SELECT id FROM $dltbl WHERE did = $id";
		$count = $db->get_results($q);
		return count($count);
	}

	function images_count($id) {
		global $db, $itbl;
		$q = "SELECT id FROM $itbl WHERE aid = $id";
		$count = $db->get_results($q);
		return count($count);
	}

	function autop($pee, $br=1) {
		$pee = preg_replace("/(\r\n|\n|\r)/", "\n", $pee); // cross-platform newlines
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		$pee = preg_replace('/\n?(.+?)(\n\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
		if ($br) $pee = preg_replace('|(?<!</p>)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		return $pee;
	}

	function random_str() {
		return substr(md5(uniqid(microtime())), 0, 6);
	}
	//// GETTERS
	// These functions write the content of the various pages

	function get_footer() {
		global $version, $externals;
	
		if ($externals):
			$url = 'http://www.slideshowpro.net/director_version/director_version.xml';
			$rss = fetch_rss($url);
			$cur_version = $rss->items[0]['title'];
		    $i = '';
			if ($cur_version != $version && strpos($version, 'b') === false):
				$i = ' <a href="' . trim($rss->items[0]['guid']) . '" title="New Version Available">New Version Available</a>';
			endif;
		else:
			$i = '';
		endif;       
	    ////////////////////////////////////////////////////////////////////////////////////
	    // WARNING: Changing the copyright information in the following code is a violation
		//			of the license you agreed to when purchasing SlideShowPro Director.      
		////////////////////////////////////////////////////////////////////////////////////                                                                 
	   	$o = '<div id="footer">
			   		<strong>SlideShowPro Director v' . $version . '</strong>' . $i . ' &copy; 2005-' . date('Y') . ' Bradleyboy Productions and Dominey Design. All Rights Reserved. <a href="http://www.slideshowpro.net/downloads/userguide/ssp_dir/Dir_UserGuide.pdf">User Guide</a> | <a href="http://www.slideshowpro.net/forum/" title="Forum">Forum</a> | <a href="http://www.slideshowpro.net" title="SlideShowPro">SlideShowPro</a> 
				</div>';  
		
		return $o;
	}   

	// Write Head and Navigation
	function get_header($location) {
		global $db, $utbl, $version;
		if (isset($_SESSION['login']))
			$user = $db->get_row("SELECT * FROM $utbl WHERE usr = '{$_SESSION['login']}'");
	
		$o = '<div id="header"><a href="?p=dash" title="Home"><img class="logo" src="images/director_logo.gif" width="192" height="48" alt="SlideShowPro Director" /></a><ul id="main-nav">';
		if ($location == 'Login'):
			$o .= '<li class="selected first"><a href="?p=login" title="Login">Login</a></li>';
		elseif ($location == 'Dashboard'):
			$o .= '<li class="selected first"><a href="?p=dash" title="Dashboard">Dashboard</a></li>';
			$hello = 'Hello <strong>' . $user->usr . '</strong>';
		else:
			$o .= '<li class="first"><a href="?p=dash" title="Dashboard">Dashboard</a></li><li class="selected"><a href="#" title="' . $location . '">' . $location . '</a></li>';
		endif;
		$o .='</ul>';
	
		if ($location != 'Login'):
			$o .= '<div id="user-profile"><a href="?p=user-profile" title="Edit profile">Profile</a> | <a href="bye.php" title="Sign out">Sign out</a>';
			if ($_SESSION['perms'] > 2):
				$o .= ' | <a href="?p=manage-users" title="Manage users">Manage users</a>';
			endif;  
			$o .= '</div>';
			if ($location != 'Dashboard' && $location != 'Edit slide shows' && $location != 'Upgrade'):
				$o .= get_slideshow_select();
			endif;
			
		endif;
		$o .= '</div>';
	
	 	return $o;
 	
	}

	// Dashboard
	function get_dashboard() {
		global $db, $atbl, $dtbl, $stbl, $show_news, $base, $externals;
		$q = "SELECT * FROM $dtbl";
		$dynamics = $db->get_results($q);

		$o = '<div id="sub-header" class="clearfix">
				<h3 id="hello">Hello <strong>' . $_SESSION['login'] . '</strong></h3>
			  </div>';
		$o .= '<div id="container">';
		
		$q = "SELECT * from $atbl ORDER BY displayOrder";
		$albums = $db->get_results($q);
	
		$ao = '';
		$io = '';
	
		$active_albums = 0;
		$inactive_albums = 0;
	
		if (count($albums) != 0):
		foreach($albums as $a):
			$images = images_count($a->id);
			$temp = '<li'; 
			$class = '';
			if ($a->active == 1):
				$class = ' sort';
			else:
				$class = ' inactive';
			endif;
			$temp .=' id="album_' . $a->id . '" class="clearfix' . $class . '"><div class="thumb" style="background:';
		
			if (!empty($a->aTn)):
				$temp .= 'url(' . $a->aTn . ') no-repeat center;';
			else:
				$temp .= '#242424;';
			endif;
			$temp .= '"></div><div><h5>'. $a->name . '</h5>
						<small>' . $images . ' images</small>
						<span><small><a href="#" onclick="toggle_album_active(\'' . $a->id . '\', ';
			if ($a->active == 1):
		    	$temp .= '\'0\'); return false;">Make inactive';
			else:
				$temp .= '\'1\'); return false;">Make active'; 
			endif;  
		    $temp .= '</a>';
	            
			$temp .= ' | <a href="?p=edit-album&amp;id=' . $a->id . '">Edit</a>';
		 	if ($_SESSION['perms'] > 1):
				$temp .= ' | <a href="#" onclick="delete_album(\'' . $a->id . '\'); return false;">Delete</a>';
			endif;
					
			$temp .= '</small></span></div></li>';
		    if ($a->active == 1):
		    	$ao .= $temp;
				$active_albums++;
			else:
				$io .= $temp;
				$inactive_albums++;
			endif;
		endforeach;
		endif;
	
	
		$o .= '<div class="clearfix grid"><div class="left"><h3>Your albums';
		                                                                 
		if ($_SESSION['perms'] > 1):
		$o .= '<span><small><a href="?p=add-album" title="Add an album">Add an album</a></small></span>';
		endif;
		$o .= '</h3>';
		if ($active_albums == 0 && $inactive_albums == 0):
			$o .= '<p>You have no albums.</p>';
		else:
			$o .= '<h4>';
			$o .= $active_albums . ' active and ';
			$o .= $inactive_albums . ' inactive';
			if ($active_albums > 1)
				$o .= ' <small>(Drag active albums to reorder)</small>';
			$o .= '</h4>';   
			$o .= '<ul id="active-albums" class="dash-strip">';
			$o .= $ao;          
	        $o .= '</ul>';
			$o .= '<ul id="inactive-albums" class="dash-strip">';
			$o .= $io;          
	        $o .= '</ul>';
		endif;
			$o .= '</div>';
		
			$o .= '<div class="right"><div class="grid">';
		
			$o .= '<h3>Your dynamic galleries<span><small><a href="?p=dynamic-galleries">Manage galleries</a></small></span></h3>';
		
			if (count($dynamics) > 0):
				$ol = '';
				foreach($dynamics as $d):
					$album_count = dynamic_count($d->id);
					$temp = '<li class="clearfix">
							<h5>'. $d->name . '</h5>
							<small>' . $album_count . ' album';
					if ($album_count != 1)
						$temp .= 's';
				    $temp .= '</small>
							<span><small><a href="?p=dynamic-gallery&amp;id=' . $d->id . '">Edit</a>';
					if ($_SESSION['perms'] > 1):
						$temp .= ' | <a href="#" onclick="delete_dynamic_gallery(' . $d->id . ', \'dashboard\'); return false;">Delete</a>';
					endif;
					$temp .= '</small></span></li>';
					$ol .= $temp;
				
				endforeach;
				$o .= '<ul class="dash-strip tight">';
				$o .= $ol;
				$o .= '</ul>';
			else:
				$o .= '<p>You have no dynamic galleries.</p>';
			endif;
			$o .= '</div><div class="grid">';
			$o .= '<h3>Your gallery data <small>(Copy and paste as SlideShowPro\'s XML File Path)</small></h3>';
	$self = get_self();
	$self = str_replace('index.php', '', $self);
	$self = str_replace('ajax/toggle_album_active.php', '', $self);
	$self = str_replace('ajax/delete_album.php', '', $self);
	$self = str_replace('ajax/delete_dynamic_gallery.php', '', $self);
	$self = str_replace('ajax/delete_slideshow.php', '', $self);
	$o .= '<ul class="dash-strip tight">
			<li class="clearfix"><h5>All active albums</h5>http://' . $self . 'images.php</li>';
	if (count($dynamics) != 0):
		foreach($dynamics as $d):
			$o .= '<li class="clearfix"><h5>Dynamic gallery: ' . $d->name . '</h5>http://' . $self . 'images.php?gid=' . $d->id . '</li>';
		endforeach;
	endif;
	$o .= '</ul></div>';
	
			$o .= '<h3>Your slide shows<span><small><a href="?p=edit-slideshows">Edit</a></small></span></h3>
			<div id="slideshows">
';    
$o .= get_slideshow_list();
			$o .= '</div></div></div>';
	
		if ($externals):
		// Fetch News 
	  		$url = 'http://www.slideshowpro.net/news/index.xml';
			$rss = fetch_rss($url);

			$o .= '<div id="news">
					<h3>SlideShowPro news</h3><div class="clearfix grid"><div class="left">';

			$i = 0;
			foreach ($rss->items as $item):
				$href = $item['guid'];
				$title = $item['title'];
				$content = $item['description'];
				if (!empty($item['category']))
					$cat = $item['category'];
				else
					$cat = 'General';
		
				if ($i == 0):                   
					$o .= '<h4>Latest news</h4><div class="indent"><h5 class="news"><a href="' . $href . '" target="_blank">' . $title . '</a></h5>';
					$o .= '<p><small>' . $cat . ' | ' . date('F j, Y', strtotime($item['pubdate'])) . '</small></p>';
					$o .= autop($content);
					$o .= '<p><a href="' . $href . '" target="_blank">Permalink</a></p></div></div>';
					$o .= '<div class="right"><h4>Older news</h4><div class="indent">';
				elseif ($i < 6):
				   	$o .= '<h5 class="news"><a href="' . $href . '" target="_blank">' . $title . '</a></h5><p><small>' . date('F j, Y', strtotime($item['pubdate'])) . '</small></p>';
				endif;         
				$i++;          
			endforeach;
                                
			$o .= '</div></div></div>';    
		endif;    
		$o .= '</div></div>';

		return $o;
	}

	// Edit Album
	function get_album_edit($id, $tab, $new=0) {
		global $db, $atbl, $itbl, $base;
		$q = "SELECT * from $atbl WHERE id = $id";
		$a = $db->get_row($q);
	
		$self = get_self();
		$self = str_replace('index.php', '', $self);
	
		$o = '<div id="sub-header" class="clearfix">
				<div class="message">
				 <div id="album-preview" class="thumb" style="background:';

				if (!empty($a->aTn)):
					$o .= '#202020 url(' . $a->aTn . ') no-repeat center;';
				else:
					$o .= '#202020;';
				endif;
			
				$link = '?p=edit-album&amp;id=' . $a->id;
				$o .= '"></div>
			
				<small>You are editing this album:</small>
			
				<h3 id="album-name">' . $a->name . '</h3>
				<small>No. of images: <span id="img-count">' . images_count($a->id) . '</span>';
				if ($tab == 'images')
					$o .= ' (<a href="#" onclick="rescan_album(' . $a->id . '); return false;">Rescan for new images</a>)';
				$o .= '</small></div>
				<ul id="album-nav"><li' . ($tab == '' ? ' class="selected first"' : ' class="first"') . '><a href="' . $link . '" title="Edit info">Edit info</a></li><li' . ($tab == 'generate' ? ' class="selected"' : '') . '><a href="' . $link . '&amp;tab=generate" title="Generate content">Generate content</a></li><li' . ($tab == 'images' ? ' class="selected"' : '') . '><a href="' . $link . '&amp;tab=images" title="Edit Images">Edit images</a></li><li' . ($tab == 'audio' ? ' class="selected"' : '') . '><a href="' . $link . '&amp;tab=audio" title="Audio">Audio</a></li></ul>
			  </div><div id="container" class="clearfix">';
	
		// Make sure we have an album
		if (empty($a)):
			$o .= '<p class="alert">No Album Found with this ID (' . $id . ')</p>';
		else:
			switch($tab):
				case('generate'):
					$o .= '<div class="left">
					 <div id="process-pane">';
					$o .= process_pane($a);    
					$o .= '</div>';
				
					$o .= '<div class="grid">
							<h3>Album preview</h3><ul class="dash-strip tight"><li class="clearfix" id="preview-img">';
					
					$o .= preview_options($a);
				
					$o .= '
					<div id="preview-select" class="embed" style="display:none;"><div id="select-thumb">';
					$o .= fill_preview($a->id);
					$o .= '</div></div>
					<div id="preview-edit" class="embed" style="display:none;">';
				
					$o .= sub_preview($a->id);
				
					$o .= '</div>';
				
					if ($_SESSION['perms'] > 1):
					$o .= '<div id="preview-upload" class="embed" style="display:none;"><h4>Upload a custom preview</h4>
					<form method="post" action="ajax/upload_thumb.php" id="thumb-form" class="indent" enctype="multipart/form-data" target="hidden" onsubmit="init_message(\'Uploading preview...\', 1, false); return true;">
					<input type="hidden" name="aid" value="' . $a->id . '" /> 
					<div id="upload-prv-msg"></div>
						<fieldset>
							<label style="display:inline;">Select File: </label><input type="file" name="upload" />
						</fieldset>
					
						<fieldset>
							<input name="smt" type="submit" value="Upload" onclick="this.value=\'Uploading...\';" />
						</fieldset>
					</form></div>';
					endif;
					$o .= '</div></div><div class="right">
							<div class="grid">
							<h3>Image attributes</h3>
							<ul class="dash-strip tight"><li class="clearfix">Titles<span><small><a href="#" onclick="Effect.toggle(\'populate-titles\', \'blind\'); return false;">Edit</a></small></span></li></ul>
							<div id="populate-titles" class="embed" style="display:none">
							<p>To set all of your titles, enter the text you would like to use below.</p>
							<fieldset class="solo">
							<p class="tag_list">
								You may use the following tags:<br />
								<a href="#" onclick="fill_title_tag(\'[img_name]\'); return false;">[img_name]</a> &mdash; Image Name
							</p>
								<input type="text" id="title" size="40" /><br />
								<input id="title-button" type="button" value="Populate" onclick="prefill_titles(\'' . $a->id . '\')" style="margin-top:5px;" /> <span id="title-messenger" style="display:none;"></span></fieldset>';
							$q = "SELECT id FROM $itbl WHERE title <> '' AND aid = $id LIMIT 1";
							$check = $db->get_results($q);
							
							$o .= '<div id="title-clear"';
							if (count($check) == 0): 
								$o .= ' style="display:none;"';
							endif;
							$o .= '><fieldset class="solo"><input id="title-clear-button" type="button" value="Clear titles" onclick="clear_titles(\'' . $a->id . '\')" /> <span id="title-clear-messenger" style="display:none;"></span></fieldset><p><small><strong class="warn">Warning</strong>: This will overwrite any titles you have already set.</small></p>';
						   $o .= '</div></div>
							<ul class="dash-strip tight"><li class="clearfix">Links<span><small><a href="#" onclick="Effect.toggle(\'populate-links\', \'blind\'); return false;">Edit</a></small></span></li></ul>  
								
							<div id="populate-links" class="embed" style="display:none">
							<fieldset class="solo"><input id="links-button" type="button" value="Populate" onclick="prefill_links(\'' . $a->id . '\', \'fill\')" /> <input id="links-js-button" type="button" value="Populate (js)" onclick="prefill_links(\'' . $a->id . '\', \'js\')" /> <input id="links-clear-button" type="button" value="Clear" onclick="clear_links(\'' . $a->id . '\')" /> <span id="links-messenger" style="display:none;"></span></fieldset>
							<p>This will populate all of your links with a link to the lg version of the image. If an hr folder is present (high resolution), the links will be directed to that version of the image. By using the "Populate (js)" button, the links will be filled with code to open a chromeless browser window, exactly the size of the given image.</p>';
							$q = "SELECT id FROM $itbl WHERE link <> '' AND aid = $id LIMIT 1";
							$check = $db->get_results($q);
							if (count($check) == 1):
								$o .= '<p><small><strong class="warn">Warning</strong>: This will overwrite any links you have already set.</small></p>';
							endif;

							$o .= '<fieldset class="solo">Links Open in: <input id="target-button" type="button" value="Same window" onclick="prefill_targets(\'' . $a->id . '\')" /> <input id="target-clear-button" type="button" value="New window" onclick="clear_targets(\'' . $a->id . '\')" /> <span id="target-messenger" style="display:none;"></span></fieldset>
							<p>Here you can set your links to open in a new window (the default) or in the same window as the slide show. You can override this on a per image basis in the <a href="' . $link . '&amp;tab=images">Edit Images</a> tab.</p>';
							$o .= '</div>
						   <ul class="dash-strip tight"><li class="clearfix">Captions<span><small><a href="#" onclick="Effect.toggle(\'populate-captions\', \'blind\'); return false;">Edit</a></small></span></li></ul>  
							<div id="populate-captions" class="embed" style="display:none">
							<p>To set all of your captions, enter the text you would like to use below.</p>
							<fieldset class="solo">
							<p class="tag_list">
								You may use the following tags:<br />
								<a href="#" onclick="fill_cap_tag(\'[img_name]\'); return false;">[img_name]</a> &mdash; Image Name
							</p>
								<textarea id="caption" rows="5" cols="40"></textarea id="caption"> <br />
								<input id="captions-button" type="button" value="Populate" onclick="prefill_captions(\'' . $a->id . '\')" style="margin-top:5px;" /> <span id="captions-messenger" style="display:none;"></span></fieldset>';
							$q = "SELECT id FROM $itbl WHERE caption <> '' AND aid = $id LIMIT 1";
							$check = $db->get_results($q);
							
							$o .= '<div id="caption-clear"';
							if (count($check) == 0): 
								$o .= ' style="display:none;"';
							endif;
							$o .= '><fieldset class="solo"><input id="captions-clear-button" type="button" value="Clear captions" onclick="clear_captions(\'' . $a->id . '\')" /> <span id="captions-clear-messenger" style="display:none;"></span></fieldset><p><small><strong class="warn">Warning</strong>: This will overwrite any captions you have already set.</small></p>';
						   $o .= '</div></div></div></div>';
				
					break;    
				case('images'):
					$o .= get_images_edit($a->id);
					$o .= '<div id="dummy-tgt"></div>';
					break;      
				case('audio'):
					$mp3s = directory('album-audio', 'mp3');
					if (count($mp3s) == 0):
						$inner = '<div id="audio-select">No mp3s in the album-audio folder!</div>';
					else:
						$inner = '<div id="audio-select"><select name="track"><option value="None">No Audio for this Album</option>
									';
						foreach($mp3s as $m):
							if ($a->audioFile == $m):
								$checked = ' selected="selected"';
							else:
								$checked = '';
							endif;
							$inner .= '<option' . $checked . '>' . $m . '</option>';
						endforeach;                                 
					
						$inner .= '</select></div>';
					endif;
					$o .= '  
							<h3>Album audio</h3>
							<div class="left">
						   <form action="#" id="theForm" name="theForm" onsubmit="update_audio(); return false;">
							<input type="hidden" name="aid" value="' . $a->id . '" />
						   <fieldset>
						   		<label>Select a song:</label>
								' . $inner . '
						   </fieldset>
						   <fieldset>
								<label>Audio caption</label> <textarea cols="40" rows="5" name="description">' . htmlspecialchars($a->audioCap) . '</textarea>
						   </fieldset>
						   <fieldset><input id="save-button" type="submit" value="Save changes" /> <span id="audio-messenger" style="display:none;"></span>
						   </fieldset>
						</form>
						<p><small><strong>Note</strong>: For songs to show up in the dropdown above, they must be uploaded to the album-audio folder.</small></p>
						</div>';
					
						if ($_SESSION['perms'] > 1):
						$o .= '<div class="right">
							<div class="embed">
							<h4>Upload an audio file</h4>
							<form method="post" action="ajax/upload_audio.php" id="audio-form" enctype="multipart/form-data" target="hidden" onsubmit="init_audio_upload();">
							<input type="hidden" name="aid" value="' . $a->id . '" /> 
							<div id="audio-msg"></div>
								<fieldset>
									<label style="display:inline;">Select File:</label> <input type="file" name="upload" />
								</fieldset>
							
								<fieldset>
									<input name="smt" type="submit" value="Upload" />
								</fieldset>
							</form>
							</div>
						</div>';
						endif;
					break;
				default:
					$o .= '<div class="left"><h3>Album name &amp; description</h3>
						   <form id="theForm" name="theForm" action="#" onsubmit="update_album(); return false">
						   <input type="hidden" name="aid" value="' . $a->id . '" />
					  
						   <fieldset>
						   		<label>Album name</label> <input type="text" name="name" value="' . htmlspecialchars($a->name) . '" size="40" />
						   </fieldset>
						   <fieldset>
								<label>Album description</label> <textarea cols="45" rows="5" name="description">' . htmlspecialchars($a->description) . '</textarea>
						   </fieldset>
						   <fieldset><input id="save-button" type="submit" value="Save changes" /> <span id="album-messenger" style="display:none;"></span>
						   </fieldset>
						</form></div>
					
						<div class="right">
						<h3>Album options</h3>
					   	<ul class="dash-strip">
						   
							<li><input class="radio" type="checkbox" id="show-headers"';
						if ($a->show_headers == 1)
							$o .= ' checked="checked"';
						$o .= ' onchange="update_show_headers(this.checked, ' . $a->id . ')" /> Show album title &amp; description in slide show</li>                
						 <li><input class="radio" type="checkbox" id="show-thumbs"';
						if ($a->tn == 1)
							$o .= ' checked="checked"';
						$o .= ' onchange="update_show_tn(this.checked, ' . $a->id . ')" /> Show thumbnails in slide show</li>
						</ul>';
						if ($new==1):
							$o .= "<img src=\"images/uggo.gif\" onload=\"init_message('Album added!', 2, true);\" this.parentNode.removeChild(this);\" />";
						endif;
						$o .= '</div>';
					break;
		
			endswitch;
		endif;
		$o .= '</div><iframe id="hidden" name="hidden" style="height:0; width:0" src="inc/html/blank.html"></iframe>';

		return $o;
	}
	
	function preview_options($a) {
		global $base;
		$where = explode($a->path . '/tn/', $a->aTn);
	    $count = count($where);

        $gdv = gd_version();
        
		$o = '';
		if ($a->aTn != ''):
			$self = get_self('/index.php');
			$self = str_replace('/ajax/designate_preview.php', '', $self); 
			$self = str_replace('/ajax/update_preview_img.php', '', $self);
			$self = str_replace('/ajax/generate_preview.php', '', $self); 
			
			$img_file = str_replace('http://' . $self, '', $a->aTn);
			$size = getimagesize($base . $img_file);
			$o .= '<img src="' . $a->aTn . '?' . random_str() . '" width="' . $size[0] . '" height="' . $size[1] . '" />';
		else:
			$o .= 'No preview selected for this album.';
		endif;
		
		$o .= '<span><small><a href="#" onclick="toggle_preview(\'preview-select\'); return false;">Select</a>';
        
		if ($count > 1 && (file_exists($base . 'albums/' . $a->path . '/lg/' . $where[1]) || file_exists($base . 'albums/' . $a->path . '/hr/' . $where[1])) && $gdv != 0):   
		$o .= ' | <a href="#" onclick="toggle_preview(\'preview-edit\'); return false;">Resize</a>';
		endif;
		if ($_SESSION['perms'] > 1):
			$o .= ' | <a href="#" onclick="toggle_preview(\'preview-upload\'); return false;">Upload</a>';
		endif; 
		if (!empty($a->aTn)):
			$o .= ' | <a href="#" onclick="designate_preview(\'\', ' . $a->id . '); return false;">Clear</a>'; 
		endif; 
	    $o .= '</small></span></li></ul>';      
		return $o;
	}

	function process_pane($a) {
		global $base;
		$gdv = gd_version();
  
		$o = '<div class="grid">
					<h3>Image processing</h3>       
					';

			if (empty($a->process_specs)):
				$in = 'No large images have been generated for this album.';
			else:
				$specs = explode('x', $a->process_specs);
				$in = "Large images are processed at {$specs[0]}x{$specs[1]} ({$specs[2]} quality).";
			endif;
			$o .= '<ul class="dash-strip tight">
							<li class="clearfix">' . $in . '<span><small><a href="#" onclick="Effect.toggle(\'process-edit\', \'blind\'); return false;">Edit</a></small></span></li>
						</ul>
						<div id="process-edit" class="embed" style="display:none;">';
			if ($gdv == 0):
		   		$o .= '<p>This functionality requires the GD library to be installed. Check with your host or system administrator for more information</p>';
			else:								
			$o .= '
						<form method="post" action="ajax/process_images.php" target="hidden" onsubmit="return process_images();">
						<fieldset class="solo">
						<input name="aid" type="hidden" value="' . $a->id . '" />
						<input type="text" id="pvalw" name="pvalw" size="4" /> x <input type="text" id="pvalh" name="pvalh" size="4" /> pixels at <input type="text" id="pquality" name="pquality" size="3" value="75" /> quality <input id="process-button" type="submit" value="Generate" /> <span id="process-messenger" style="display:none;"></span>
				   </fieldset>
					</form>
				   <p>This publishes slide show images from your uploaded photos. Enter a maximum value for their width and height, plus a quality setting between 0 and 100, and an optimized batch of images will be published and scaled proportionally. New images added to this album will be automatically processed using these values.</p>'; 
		endif; 
		$o .= '</div>';
		
		if (empty($a->thumb_specs)):
			$in = 'No thumbnails have been generated for this album.';
		else:
			$specs = explode('x', $a->thumb_specs);
			$in = "Thumbnails are processed at {$specs[0]}x{$specs[1]} ({$specs[2]} quality).";
		endif;
		$o .= '<ul class="dash-strip tight">
						<li class="clearfix">' . $in . '<span><small><a href="#" onclick="Effect.toggle(\'thumbs-edit\', \'blind\'); return false;">Edit</a></small></span></li>
					</ul>
					<div id="thumbs-edit" class="embed" style="display:none;">';
					 if ($gdv == 0):
					   		$o .= '<p>This functionality requires the GD library to be installed. Check with your host or system administrator for more information</p>'; 
					else:  
				    $o .= '<form method="post" action="ajax/generate_thumbs.php" target="hidden" onsubmit="return generate_thumbs();"> 
					<fieldset class="solo">
					<input name="aid" type="hidden" value="' . $a->id . '" />
					<input type="text" id="valw" name="valw" size="4" /> x <input type="text" id="valh" name="valh" size="4" /> pixels at <input type="text" id="quality" name="quality" size="3" value="75" /> quality <input id="thumbs-button" type="submit" value="Generate" /> <span id="thumbs-messenger" style="display:none;"></span>
			   </fieldset>
				</form>
			   <p>This generates thumbnail images from your uploaded photos. They appear in SlideShowPro when a user rolls over image numbers in the navigation. Enter a maximum value for their width and height, plus a quality setting between 0 and 100, and an optimized batch of images will be published and scaled proportionally. When an image is added to this album, a new thumbnail will automatically be processed using these values.</p>';
		if (is_dir($base . 'albums/' . $a->path . '/tn')):
			   $o .= '<p><small><strong class="warn">Warning</strong>: You have already generated thumbnails for this album. This will overwrite your existing files.</small></p>
 
   
		<fieldset class="solo">
				   <input id="clear-thumbs-button" type="button" value="Clear thumbnails" onclick="clear_thumbs(' . $a->id . ');" /> <span id="clear-thumbs-messenger"><small><strong class="warn">Warning:</strong> This will delete your existing thumbnails!</small></span>
			   </fieldset>'; 
			   endif; 
			endif;
		
		$o .= '</div></div>';
		return $o;
	} 

	function sub_preview($aid) {
		global $db, $atbl, $base;
		$a = $db->get_row("SELECT * FROM $atbl WHERE id = $aid");
		$gdv = gd_version(); 
	
		$where = explode($a->path . '/tn/', $a->aTn);
	    $count = count($where);
	
		$o = ' ';
	
		$o = '
		<form action="#" id="theForm" name="theForm" onsubmit="generate_preview(' . $a->id . '); return false;"><fieldset class="solo">
					<input type="text" id="preview-val" size="4" /> pixels at <input type="text" id="preview-quality" size="3" value="75" /> quality <input id="preview-button" type="submit" value="Resize" /> <span id="preview-messenger" style="display:none;"></span>
			  </fieldset></form><p>If you would like to create a custom sized preview from this image, enter the maximum width or height in the form above. The value is applied to the largest dimension, with the other scaled proportionally. You may also set the quality of the generated thumb from 1 to 100 (default: 75)</p>';
		if (file_exists($base . 'album-thumbs/album-' . $a->id . '.jpg')):
			$o .= '<p><small><strong class="warn">Warning</strong>: You have already generated a custom thumbnail for this album. This will overwrite your existing file.</small></p>';
		endif;         
	
		return $o;
	}

	function fill_preview($aid) {
		global $db, $atbl, $base;
		$a = $db->get_row("SELECT * FROM $atbl WHERE id = $aid");
	     
		if (!empty($a->thumb_specs)):
		    $specs = explode('x', $a->thumb_specs);
		    $w = $specs[0] + 4; $h = $specs[1] + 4;
			$style = "style=\"width:{$w}px; height:{$h}px;\"";  
	    endif;
	
		$uri_self = get_self();
		$uri_self = str_replace('index.php', '', $uri_self);
		$uri_self = str_replace('ajax/fill_preview.php', '', $uri_self);
		$uri_self = str_replace('ajax/generate_thumbs.php', '', $uri_self);
	
		$o = '<div>';
		$tn_dir =  $base . 'albums/' . $a->path . '/tn';
		$i = 0;
		if (is_dir($tn_dir)):
			$pics = directory($tn_dir, "jpg,JPG,JPEG,jpeg,png,PNG,GIF,gif");
			$o .= '<ul id="preview-list">'; 
			foreach($pics as $pic):
				$full_path = 'http://' . $uri_self . 'albums/' . $a->path . '/tn/' . $pic; 
				$o .= '<li ' . $style . '><a href="#" onclick="designate_preview(\'' . $full_path . '\', ' . $a->id . '); return false;" ' . $style . '><img src="' . $full_path . '?dummy=' . random_str() . '" /></a></li>';  
				$i++;
			endforeach;  
			$o .= '</ul>';
		endif;         
		$custom_path = $base . 'album-thumbs/album-' . $a->id . '.jpg';
		if (file_exists($custom_path)):
			$full_path = 'http://' . $uri_self . 'album-thumbs/album-' . $a->id . '.jpg';
			$o .= '<a href="#" onclick="designate_preview(\'' . $full_path . '\', ' . $a->id . '); return false;"><img src="' . $full_path . '?dummy=' . random_str() . '" /></a>'; 
			$i++;
		endif;         
		if ($i == 0)
			$o .= '<p>No thumbnails for this album. Use the form above to generate some now.</p>';
		$o .= '</div>';
		return $o;
	}

	// Edit Album Images
	function get_images_edit($id) {
		global $db, $itbl, $atbl;
	
		$q = "SELECT id, path FROM $atbl WHERE id = $id";
		$a = $db->get_row($q);                            
	
		$q = "SELECT * FROM $itbl WHERE aid=$id ORDER BY seq";
		$images = $db->get_results($q);
	
		$o = '';
	
			$o .= '<div id="edit-box" class="clearfix" style="display:none"><p><a href="#" onclick="hide_image_edit(); return false;">&uarr; Hide</a></p><div id="target"></div></div>';
			$o .= '<div id="thumb-resize" class="clearfix">';
			if ($_SESSION['perms'] > 1):
			$o .= '<div id="add-image">Add an image: 
				<form method="post" action="ajax/upload_image.php" id="image-form" enctype="multipart/form-data" target="hidden" onsubmit="init_message(\'Uploading new image...please wait...\', 1, false)">
				<input type="hidden" name="aid" value="' . $a->id . '" />
				<input type="file" name="upload" />
				<input name="smt" type="submit" value="Upload" onclick="this.value=\'Uploading...\';" />
				</form><span id="upload-img-msg"></span>
				<div id="resize-label">Thumbnail size:&nbsp;&nbsp;&nbsp;&nbsp;</div></div>';
			endif;
		
			$o .= '<div id="slider"><div id="track1">
				<div id="handle1"><img src="images/scaler_slider.gif" /></div></div></div></div>';
	  
		        $o .= '<ul id="image-view" style="display:none;" class="clearfix">';
			$n = 1;
			$t = count($images);
			if ($t != 0):
			foreach($images as $i):
				$path_to_i = 'albums/' . $a->path . '/director/' . $i->src;
			    if (!file_exists($path_to_i)):
			    	$path_to_i = 'albums/' . $a->path . '/lg/' . $i->src;
				endif;
				if (eregi('.swf|.flv', $i->src)):
					$inner = "<p><small>{$i->src}</small></p>";
				else: 
					$info = getimagesize($path_to_i);
					$h = $info[1]; $w = $info[0];
					if ($w >= $h):                
						$new_h = ($h * 200) / $w;
						$attr = ' class="wide"';
					else:;
						$attr = ' class="tall"';
					endif;
					$inner = '<img src="' . $path_to_i .'"' . $attr . ' />';
				endif;
			
				$o .= '<li id="image_' . $i->id . '">
				<div class="scale-image"><div>' . $inner . '</div></div>
				<div class="info">
				<div class="counter" id="counter_' . $i->id . '">' . $n . '/' . $t . '</div>
				<a title="Edit image" class="edit-image-btn" href="#" onclick="edit_image(\'' . $i->id . '\', \'' . $a->id . '\'); return false;"><img src="images/icons/transparent.gif" width="16" height="16" alt="Edit image" /></a>';
				if ($_SESSION['perms'] > 1):
					$o .= ' <a title="Delete image" class="delete-image-btn" href="#" onclick="delete_image(\'' . $i->id . '\'); return false;"><img src="images/icons/transparent.gif" width="16" height="16" alt="Delete image" /></a>';
				endif;
				$o .= '</div>
				</li>';
				$n++;
			endforeach;
			endif;                                                        
			$o .= '</ul>';      
	
		return $o;
	}

	function get_single_image($id, $size = 150) {
		global $itbl, $atbl, $db, $base;
		$q = "SELECT id, aid, src FROM $itbl WHERE id = $id";
		$i = $db->get_row($q); 
		$q = "SELECT id, path FROM $atbl WHERE id = {$i->aid}";
		$a = $db->get_row($q);
	    
		$path_to_i = 'albums/' . $a->path . '/director/' . $i->src;
		if (!file_exists($path_to_i)):
	    	$path_to_i = 'albums/' . $a->path . '/lg/' . $i->src;
		endif;                                                         
		if (eregi('.swf|.flv', $i->src)):
			$inner = "<p><small>{$i->src}</small></p>";
		else: 
			$path_to_i = 'albums/' . $a->path . '/lg/' . $i->src;
			$info = getimagesize($base . $path_to_i);
			$h = $info[1]; $w = $info[0];
			if ($w >= $h):                
				$new_h = ($h * 200) / $w;
				$attr = ' class="wide"';
			else:
				$attr = ' class="tall"';
			endif;
			$inner = '<img id="drag" src="' . $path_to_i .'"' . $attr . '" />';
		endif;
		$o = '<li id="image_' . $i->id . '"><div class="scale-image"><div>' . $inner . '</div></div><div class="info"><div class="counter" id="counter_' . $i->id . '"></div><a href="#" onclick="edit_image(\'' . $i->id . '\', \'' . $a->id . '\'); return false;"><img src="images/icons/image_edit.png" width="16" height="16" alt="Edit Image" /></a> <a href="#" onclick="delete_image(\'' . $i->id . '\'); return false;"><img src="images/icons/image_delete.png" width="16" height="16" alt="Delete image" /></a></div></li>';
 
		return $o;
	}

	// Add an Album
	function get_add_album() {
		$o = '<div id="sub-header" class="clearfix"><div id="left"><div class="message"><p>Use the form below to add an album.</p></div></div></div>
			<div id="container" class="clearfix">
				<div class="left clearfix">
					<form method="post" action="ajax/init_album.php" id="zip-form" enctype="multipart/form-data" target="hidden" onsubmit="return validate_add_album();">
		<div class="grid">
			<h3>Step 1: Album name</h3>
				<p>Give your album a name. You can always change this later.</p>
				 <fieldset class="solo"><input id="album_name" type="text" name="album_name" size="40" /></fieldset>
				';
		$o .= '</div><div class="grid"><h3>Step 2: Add images</h3>';
		$o .= '<p>Next, select how you will be adding your images. NOTE: Your server limits the size of uploaded files to ' . ini_get('upload_max_filesize') . 'B (' . return_bytes(ini_get('upload_max_filesize')) . ' bytes).</p>
				<fieldset class="solo"><select id="images-format" name="images-format" onchange="toggle_upload_format()">
					<option value="0">Select Method...</option>';
					$o .= '<option value="1">Upload images as a Zip archive</option>';
					$o .= '<option value="2">Upload images one at a time</option>    
					<option value="3">Scan a folder on the server for images</option>
				</select></fieldset>
			   
					<fieldset id="file-upload" style="display:none;" class="solo"><label id="file-label">ZIP File:</label><input type="file" id="upload" name="upload" class="file" /></fieldset>
				
					<fieldset id="folder-scan" style="display:none;" class="solo">';
				
					$album_dir = @opendir('albums/');
					$i = 0;
					$output = '<label>Folder to Scan:</label><select id="scan-this" name="scan-this">';
				    while (false !== ($file = readdir($album_dir))) {

				    	if ( $file != '.' && $file != '..' )
				    	{
							if ( is_dir ( './albums/'.$file ) && substr($file, 0, 1) != '.' )
							{
								$output .= "<option value=\"$file\">$file</option>";
								$i += 1;
							}
				    	}
				    }

				    closedir($album_dir);

				    if ( $i != 0 )
				    {
				    	$output .= '</select>';
				    }
					else
				    {
				    	$output = '<input id="scan-this" type="hidden" name="scan-this" value="0" />No folders found in your <strong>albums</strong> directory, you need to upload your photos first!';
				    }
				
					$o .= $output;
					$o .= '</fieldset></div>
					<div class="grid"><h3>Step 3: Finish</h3>
					<p>Press the button below to add the album!</p>
					<fieldset class="solo"><input type="submit" value="Create Album" /></fieldset></div> 
				</form></div></div>
				<iframe name="hidden" id="hidden" style="width:0; height:0;" src="inc/html/blank.html"></iframe> 
	  			';
		return $o;
	}   

	// Generate Thumbs
	function get_album_process($id, $status) {
		global $db, $atbl, $itbl;
		if ($status == 'Good'):
			$q = "SELECT path FROM $atbl WHERE id = $id";
			$a = $db->get_row($q);
			$path = $a->path;
		
			$album_photos_dir = 'albums/'.$path.'/lg';
				
			$dh  = @opendir($album_photos_dir) or die('<h2>Error</h2><p class="alert">Folder <strong>albums/'.$path.'/lg</strong> does not exist. A common cause of this problem is an uploaded ZIP file that did not have the proper folder structure.</p>');
		
			while (false !== ($filename = readdir($dh))):
				if ( eregi("jpg",$filename) || eregi("jpeg",$filename) || eregi("swf", $filename) || eregi("png", $filename) || eregi("flv", $filename)):
					$album_photos[] = $filename;
				endif;
			endwhile;
		
			natsort($album_photos);
		
			for ($i = 0; $i < sizeof($album_photos); $i++):
				$src = $album_photos[$i];
				$q = "INSERT INTO $itbl (id, aid, src, seq) VALUES (NULL, $id, '$src', $i)";
				$db->query($q);
			endfor;
		
			$o = '<h2>Album Added Successfully</h2>';
			$o .= '<p class="alert-success">Your album was added successfully.</p>';
			$o .= '<p>You are now ready to edit your album\'s <a href="?p=edit-album&amp;id=' . $id . '">data</a> and <a href="?p=edit-images&amp;id=' . $id . '">images</a>, or <a href="?p=generate-thumbs&amp;id=' . $id . '">generate thumbnails</a>.</p>'; 
		else:
			$o = '<h2>Error</h2>';
			$o .= '<p class="alert">' . $status . '</p>';
		endif;
	
		return $o;
	}

	// Login
	function get_login($status) {
		$o = '<div id="sub-header" class="clearfix" style="padding-bottom:10px;"><div class="message"><div class="left">';
		if ($status != null):
			$o .= '<h4>' . $status . '</h4>';
		else:
			$o .= '<h4>Please login</h4>';
		endif;
	
		$o .= '<form method="post" action="login.php" class="indent">
	
				<fieldset><label>Username:</label><input id="user" type="text" name="user" /></fieldset>
				<fieldset><label>Password:</label><input type="password" name="pass" /></fieldset>
				<fieldset><input type="submit" value="Login" /></fieldset>
			  </form>
				</div></div></div>';
		return $o;
	}   

	// Manage Users
	function get_manage_users() {
		global $utbl, $db;
		$usr_level = $_SESSION['perms'];
		if ($usr_level < 3):
			$o = '<h3>Permission denied</h3>
						<p class="alert">You do not have the necessary priveleges to manage users. Only administrators can access this page.</p>';
		else:
			$o = '<div id="sub-header" class="clearfix">      
					<div class="left">
					<div class="message">
					<p>Here you can give others access to edit your galleries. You can also give them a variety of permissions, detailed to the right.</p>
					<p>Once you submit this form, the user will be emailed with a username, login and instructions on how to use them, including a link to the login page.
					</div>
					</div>
					<div class="right">
				   <p><strong>Editors</strong> - Can edit any data (albums, images, dynamic galleries) but cannot add or delete anything.</p>
						<p><strong>Contributors</strong> - Same permissions as editors, along with the ability to add or delete albums, images or dynamic galleries.</p>
						<p><strong>Administrators</strong> - The same permissions you have. All of the above plus the ability to manage users.</p></div></div>';
		
			$o .= '<div id="container" class="clearfix"><div class="left">
						<h3>Add a user</h3>
						<p>Once you add the user, they will be emailed with login instructions and the message that you write below.</p>
						<form action="#" name="theForm" id="theForm" onsubmit="add_user(); return false;">
							<fieldset>
								<label>Users email:</label>
								<input type="text" name="email" id="email" />
							</fieldset>
						
							<fieldset>
								<label>Your email (will be shown as the From address):</label>
								<input type="text" name="from_email" id="from_email" />
							</fieldset>
						
							<fieldset>
								<label>User level:</label>
								<select name="perms">
									<option value="1">Editor</option>
									<option value="2">Contributor</option>
									<option value="3">Administrator</option>
								</select>
							</fieldset>
						
							<fieldset>
								<label>Message:</label>
								<textarea name="message" rows="5" cols="40"></textarea>
							</fieldset>
						
							<fieldset>
								<input type="submit" value="Add User &raquo;" />
							</fieldset>
						</form>
				   </div>';
			$q = "SELECT * FROM $utbl WHERE id <> {$_SESSION['loginID']} AND perms <> 4";
			$users = $db->get_results($q);   
		
			$o .= '<div class="right"><h3>Your users</h3>';
			if (count($users) == 0):
				$o .= '<p>You have not added any other users yet. Use the form to the left to add one.</p>';
			else:
				$o.= '<table id="users" cellpadding="0" cellspacing="0">
							<tr>
								<th>Username</th><th>Permissions</th><th>Delete</th>
							</tr>';
				$i = 0;
				foreach($users as $u):
					$user_perms = $u->perms;
					$select = '<select onchange="update_user_perms(this, ' . $u->id . ')">';
					$select .= '<option value="1"';
				    if ($user_perms == 1)
						$select .= ' selected="selected"';
					$select .= '>Editor</option>';
					$select .= '<option value="2"';
				    if ($user_perms == 2)
						$select .= ' selected="selected"';
					$select .= '>Contributor</option>';
					$select .= '<option value="3"';
				    if ($user_perms == 3)
						$select .= ' selected="selected"';
					$select .= '>Administrator</option></select>';
					$o .= '<tr';
					if ($i == 1):
						$o .= ' class="alt"';
						$i = 0;
					else:
						$i = 1;
					endif;
					$o .= '><td>' . $u->usr . '</td><td>' . $select . '</td><td><a href="#" onclick="delete_user(' . $u->id . '); return false;">Delete this User</a></td></tr>';
				endforeach;
			
				$o .= '</table>';
			endif;
			$o .= '</div></div>'; 
		
		endif;
		return $o;
	}   

	// User Profile
	function get_user_profile() {
		global $db, $utbl;
		$o = '<div id="sub-header" class="clearfix"><div class="left"><div class="message"><p>Note: Leave password fields blank to keep your existing password.</p></div></div></div>
				<div id="container" class="clearfix"><form action="#" id="theForm" name="theForm" onsubmit="update_profile(); return false;">
					<fieldset>
						<label>Username:</label>
						<input type="text" name="username" value="' . $_SESSION['login'] . '" />
					</fieldset>
				
					<fieldset>
						<label>Password:</label>
						<input type="password" name="pass" />
					</fieldset>
				
					<fieldset>
						<label>Confirm Password:</label>
						<input type="password" name="pass_confirm" />
					</fieldset>
				
			   
						<input id="save-button" type="submit" value="Save Profile" /> <span id="user-messenger" style="display:none;"></span>
			  
				</form></div>';
	
		return $o;
	}   

	//Dynamic Galleries
	function get_dynamic_galleries() {
		$o = '<div id="sub-header" class="clearfix">
		<div class="left">
			<div class="message">
				<p>Dynamic Galleries allow you to manage an unlimited number of slide shows from the same SlideShowPro Director install. You can create a dynamic gallery, add whichever albums you wish to it, and even alter the order in which they are displayed.</p>
			</div>
		</div>
		<ul id="album-nav"><li class="selected first"><a href="index.php?p=dynamic-galleries">Manage</a></li></ul>

		</div>
		<div id="container" class="clearfix"><div class="left"><h3>Edit dynamic galleries</h3>';

		$o .= '<div id="dynamic-fill">';
		$o .= write_dynamic_list();
		$o .= '</div></div>';
		if ($_SESSION['perms'] > 1):       
			  $o .= '
			  <div class="right"><h3>Add a dynamic gallery</h3>
					<p>Add a new dynamic gallery by giving it a name below. Once added, you can specify what albums to include in the dynamic gallery and what order they should appear in.</p>
					<form action="#" onsubmit="add_dynamic_gallery(); return false;">
					<fieldset class="solo">
						<input type="text" name="new_name" id="new_name" /> <input type="submit" value="Add New Dynamic Gallery" />
					</fieldset>
					</form>         
			  </div>';  
		endif;   	
		$o .= '</div>';
		return $o;
	}

	function get_edit_slideshows() {
		$o = '<div id="sub-header" class="clearfix"><div class="left"><div class="message"><p>Keep a list of all your slideshows for quick links to check any edits you make with SlideShowPro Director.</p></div></div></div>
		<div id="container"><div class="clearfix"><div class="left"><h3>Your slide shows</h3><div id="slideshows">';
		$o .= get_slideshow_list();
		$o .= '</div></div>';
			
		$o .= '<div class="right"><h3>Add a new link</h3>
				<form id="theForm" name="theForm" action="#" onsubmit="add_slideshow(); return false;">
				<fieldset>
				<label>Name: </label>
				<input type="text" name="name" />
				</fieldset>
				<fieldset>
				<label>URL: </label>
				<input type="text" name="url" size="35" />
				</fieldset>
				<fieldset>
				<input type="submit" value="Add Link" />
				</fieldset>
			   	</form>';
	       
		$o .= '</div></div></div>';		
		return $o;
	}

	function get_slideshow_list($ajax = false, $new_id = 0) {
		global $db, $stbl;
		$q = "SELECT * FROM $stbl ORDER BY name";
		$slideshows = $db->get_results($q);
		$o = '';
		if (count($slideshows) > 0):
			$o .= '<ul class="dash-strip tight">';
			foreach ($slideshows as $s):
				$o .= '<li class="clearfix" id="s' . $s->id . '"';
				if ($ajax && $new_id == $s->id)
					$o .= ' style="display:none;"';
				$o .= '>' . $s->name . '<span><small><a href="' . $s->url . '" target="_blank">View</a> | <a href="#" onclick="delete_slideshow(\'' . $s->id . '\');  return false;">Delete</a></small></span></li>';
			endforeach;
			$o .= '</ul>';
		else:
			$o .= '<p>You have not added any links to your slideshows yet.</p>';
		endif;
		if ($ajax):
			$o .= "<img src=\"images/uggo.gif\" onload=\"new Effect.Appear('s$new_id', { duration: 0.5 }); this.parentNode.removeChild(this);\" />";
		endif;
		return $o; 
	} 

	function get_slideshow_select() {
		global $db, $stbl;
		$q = "SELECT * FROM $stbl";
		$slideshows = $db->get_results($q);
		$o = '';
		if (count($slideshows) > 0):
			$o .= '<div id="slideshow-select"><select id="ss_select" onchange="toggle_view_btn(this.value)">';
			$o .= '<option value="0">Your slide shows</option>';
			foreach ($slideshows as $s):
				$o .= '<option value="' . $s->url . '">' . $s->name . '</option>';
			endforeach;
			$o .= '</select> <input id="view_ss_btn" type="button" value="View" onclick="fetch_slideshow()" disabled="disabled" /></div>';
		endif;
		return $o; 
	}

	// Individual Dynamic Gallery
	function get_dynamic_gallery($id) {
		global $db, $dltbl, $atbl, $dtbl;
		$dg = $db->get_row("SELECT * FROM $dtbl WHERE id = $id");
	
		$self = get_self();
		$self = str_replace('index.php', '', $self);
		$self = str_replace('ajax/delete_dynamic_link.php', '', $self);
		$self = str_replace('ajax/add_dynamic_link.php', '', $self); 
	
		$q = "SELECT a.id, a.name, d.id as did FROM $dltbl AS d, $atbl AS a WHERE d.did = $id AND d.aid = a.id ORDER BY d.display";
		$results = $db->get_results($q);
	
		$xml = 'http://' . $self . 'images.php?gid=' . $id;
	
		$o = '<div id="sub-header" class="clearfix">
				<div class="message">
					<small>You are editing this dynamic gallery:</small>
			
					<h3 id="album-name">' . $dg->name . '</h3>
					<small id="albums-count">No. of albums: ' . count($results) . '</small>
				</div>
			
				<ul id="album-nav"><li class="first"><a href="index.php?p=dynamic-galleries">Manage</a></li><li class="selected"><a href="index.php?p=dynamic-galleries">Edit gallery</a></li></ul>
				</div>
			
				<div id="container" class="clearfix">
				<div class="clearfix grid">
					<div class="left"> 
					<h3>Edit dynamic gallery name</h3>
					<form action="#" onsubmit="update_dg_name(\'' . $dg->id . '\'); return false;">
						<fieldset>
					   		<input type="text" id="name" name="name" value="' . htmlspecialchars($dg->name) . '" /> <input id="dg-name-btn" type="button" value="Save changes" onclick="update_dg_name(\'' . $dg->id . '\')" /> <span id="dg-messenger" style="display:none;"></span>
					   </fieldset>
					</form>
					</div>
					<div class="right">
					<h3>XML link for this dynamic gallery</h3>
					<ul class="dash-strip"><li class="clearfix">' . $xml . '</li></ul>
					</div>
				</div>
				<div class="left">
				 <h3>Albums in this dynamic gallery</h3>';
	
	
	
		if (count($results) == 0):
			$o .= '<p class="alert">No albums are a part of this dynamic gallery. You may add one from the list to the right.</p>';
		else:
			$o .= '<ul id="sort" class="dash-strip">';
			foreach($results as $r):
				$o .= '<li class="sort clearfix" id="dynamic_' . $r->did . '">' . $r->name . '<span><small><a href="#" onclick="this.parentNode.innerHTML = \'Removing...\'; delete_dynamic_link(' . $r->did . ', ' . $id . '); return false;">Remove</a></small></span></li>';
			endforeach;
			$o  .= '</ul>';     
		endif;
		$o .= '</div>
			
				<div class="right">
					<h3>Albums not in this dynamic gallery</h3>';
	
	
	
		$q = "SELECT id, name FROM $atbl WHERE active = 1";
		$albums = $db->get_results($q);
	
		$i = 0;
		$io = '';
	
		if (count($albums) > 0):    
			foreach($albums as $a):
				$q = "SELECT id FROM $dltbl WHERE did = $id AND aid = {$a->id}";
				$check = $db->get_results($q);
				if (count($check) == 0):
					$io .= '<li class="clearfix">' . $a->name . '<span><small><a href="#" onclick="this.parentNode.innerHTML = \'Adding...\'; add_dynamic_link(' . $id . ', ' . $a->id . '); return false;">Add</a></small></span></li>';
					$i++;
				endif;
			endforeach; 
		endif;
	
		if ($i > 0):
			$o .= '<ul class="dash-strip">';
			$o .= $io;                      
			$o .= '</ul>';
		else:
			$o .= '<p class="alert">No more albums available to add.</p>';
		endif;
	
		$o .= '</div></div>';
		    
		return $o;
	}

	// DRY
	function write_dynamic_list($ajax = false, $new_id = 0) {
		global $dtbl, $db;
		$q = "SELECT * FROM $dtbl ORDER BY name";
		$galleries = $db->get_results($q);
		$o = '';
		if (count($galleries) == 0):
			$o .= '<p class="alert">You have not added any dynamic galleries yet.</p>';
		else:
			$o .= '<ul class="dash-strip">';
			foreach($galleries as $g):
				$o .= '<li class="clearfix" id="g' . $g->id . '"';
				if ($ajax && $new_id == $g->id)
					$o .= ' style="display:none;"';
				$o .= '>' . $g->name . '<span><small><a href="?p=dynamic-gallery&amp;id=' . $g->id . '">Edit</a>';
				if ($_SESSION['perms'] > 1):
					$o .= ' | <a href="#" onclick="delete_dynamic_gallery(' . $g->id . '); return false;">Delete</a>';
				endif;
				$o .= '</small></span></li>';
			endforeach;
			$o .= '</ul>';      
		endif;                  
		if ($ajax):
			$o .= "<img src=\"images/uggo.gif\" onload=\"new Effect.Appear('g$new_id', { duration: 0.5 } ); this.parentNode.removeChild(this);\" />";
		endif;
		return $o;              
	}   

	function get_upgrade() {
		global $db, $atbl, $itbl, $utbl, $stbl, $base, $version;
	
		$db->hide_errors();
	    
		// 1.0.0
		$db->query("ALTER TABLE $atbl CHANGE displayOrder displayOrder INT(4) NOT NULL DEFAULT '999'");
		$db->query("ALTER TABLE $itbl CHANGE seq seq INT(4) NOT NULL DEFAULT '999'");
		$db->query("ALTER TABLE $itbl ADD pause INT(4) NOT NULL DEFAULT '0'");
		$db->query("ALTER TABLE $itbl ADD title VARCHAR(255)");
		$db->query("ALTER TABLE $itbl ADD target TINYINT(1) NOT NULL DEFAULT '0'");
		$db->query("ALTER TABLE $utbl ADD perms TINYINT(1) NOT NULL DEFAULT '1'");
		$db->query("CREATE TABLE $stbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(255), url VARCHAR(255))");
		$db->query("UPDATE $utbl SET perms = 4 WHERE id = {$_SESSION['loginID']}");
		
		// 1.0.3
		$db->query("ALTER TABLE $atbl ADD show_headers INT(1) NOT NULL DEFAULT '1'");
		$db->query("ALTER TABLE $atbl ADD process_specs VARCHAR(255)");
		$db->query("ALTER TABLE $atbl ADD thumb_specs VARCHAR(255)");
		$db->query("UPDATE $atbl SET show_headers = 1");
		$db->query("ALTER TABLE $itbl CHANGE src src VARCHAR(255)");
	
		$db->show_errors();

		$dir = "$base/xml_cache";
		$set = @perms_process($dir);
	
		if ($dh = @opendir($dir)):
			while (($obj = readdir($dh))):
		       if ($obj=='.' || $obj=='..' || $obj =='.svn') continue;
		       if (!@unlink($dir.'/'.$obj)) rmdirr($dir.'/'.$obj);
		   	endwhile;    
		endif; 
	
		$o = '<div id="sub-header" class="clearfix" style="padding-bottom:10px;"><div class="message"><div class="left"><h3>Upgrade Complete!</h3>
					<p>SlideShowPro Director ' . $version . ' is now ready for you to use.';
		if (!$set):
			$o .= ' For the caching system to work, you should take a moment to set the permissions on the xml_cache folder to 777. Also, make sure any cache files in the xml_cache folder have been deleted.';
	   	endif; 
		$o .= ' <a href="index.php">Return to dashboard</a></p>';
	    if (!file_exists('config/conf.php')):
		$o .= '<p>NOTE: We now recommend moving your inc/data/conf.php file to the config folder to protect it from future upgrading. If the config folder does not exist, create it in the root of the ssp_director folder.</div>';
		endif;
	
		return $o;
	}      
?>