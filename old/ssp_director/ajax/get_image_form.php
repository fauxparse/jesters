<?php
	require('../inc/data/head.php');
	$id = $_POST['id'];
	$aid = $_POST['aid'];
	if (isset($_POST['rotate']))
	    $rotate = true;
	else
		$rotate = false;
	
	$q = "SELECT * FROM $itbl WHERE id = $id";
	$i = $db->get_row($q);
	
	$q = "SELECT * FROM $atbl WHERE id = $aid";
	$a = $db->get_row($q);
	
	$link_str = $a->path . '/lg/' . $i->src;
	
	$self = get_self();
	$self = str_replace('ajax/get_image_form.php', '', $self);
	
	$path = 'http://' . $self . 'albums/' . $a->path . '/tn/' . $i->src; 
	$path_lg = 'http://' . $self . 'albums/' . $a->path . '/lg/' . $i->src;
	$album_thumb = $a->aTn;
	
	if (eregi('.swf', $i->src)):
		$i_str = '<div style="width:100%; height:400px;"><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%" id="SlideShowPro" align="middle">
								<param name="allowScriptAccess" value="sameDomain" />
								<param name="movie" value="albums/' . $link_str . '" />
								<param name="quality" value="high" />
								<param name="bgcolor" value="#111111" />
								<embed src="albums/' . $link_str . '" quality="high" bgcolor="#111111" width="100%" height="100%" name="SlideShowPro" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
								</object></div>';
	elseif (eregi('.flv', $i->src)):
		$i_str = '<div style="width:100%; height:400px;"><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%" id="SlideShowPro" align="middle">
								<param name="allowScriptAccess" value="sameDomain" />
								<param name="movie" value="inc/html/viewer.swf?fn=' . $path_lg . '" />
								<param name="quality" value="high" />
								<param name="bgcolor" value="#111111" />
								<embed src="inc/html/viewer.swf?fn=' . $path_lg . '" quality="high" bgcolor="#111111" width="100%" height="100%" name="SlideShowPro" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
								</object></div>';
	else:
		$info = getimagesize($base . 'albums/' . $link_str);
		if ($info[0] >= $info[1])
			$attr = ' width="100%"';
		else
			$attr = ' height="400"';
		$i_str = '<div id="the_img"';
		if ($rotate)
			$i_str .= ' style="display:none;"';
		$i_str .= '><img src="albums/' . $link_str . '?' . substr(md5(uniqid(microtime())), 0, 6) . '"' . $attr . ' style="margin:0 auto;"';
		if ($rotate)
			$i_str .= ' onload="new Effect.BlindDown(this.parentNode);"';
		$i_str .= ' /></div>';
		
		$gdv = gd_version();
		if (eregi('jpg', $i->src) || eregi('jpeg', $i->src) || eregi('png', $i->src) || (eregi('gif', $i->src) && (imagetypes() & IMG_GIF))):
			$i_str .= '<div id="rotator"><a id="prev-image" href="#" onclick="prev_image(' . $a->id . '); return false;"><img src="images/icons/arrow_left.gif" height="16" width="16" alt="Previous image" title="Previous image" /></a>';
			
			if ($gdv != 0):
			$i_str .= '<a href="#" onclick="rotate_img(' . $i->id . ', ' . $aid . ', 90); return false"><img src="images/icons/arrow_rotate_countclockwise.gif" height="16" width="16" alt="Rotate left" title="Rotate left 90&deg;" /></a><a href="#" onclick="rotate_img(' . $i->id . ', ' . $aid . ', -90); return false"><img src="images/icons/arrow_rotate_clockwise.gif" height="16" width="16" alt="Rotate right" title="Rotate right 90&deg;" /></a>';
			endif;
			
			$i_str .= '<a id="next-image" href="#" onclick="next_image(' . $a->id . '); return false;"><img src="images/icons/arrow_right.gif" height="16" width="16" alt="Next image" title="Next image" /></a></div>';
		endif;
	endif;
	                                     
?>                                      

<div id="img-left" style="text-align:center;"><?php _e($i_str); ?><p><?php _e($i->src); ?></p></div>
<div id="img-right">
	<form id="theForm" name="theForm">
	<input type="hidden" name="id" value="<?php _e($id) ?>" />
	<input type="hidden" name="aid" value="<?php _e($aid) ?>" />
	<input type="hidden" name="filename" value="<?php _e($i->src) ?>" />
	<fieldset><label>Title</label><input type="text" name="title" value="<?php _e(htmlspecialchars($i->title)) ?>" size="50" /></fieldset>
	<fieldset><input type="checkbox" name="active"<?php if ($i->active == 1) _e(' checked="checked"'); ?> /> Include in slide show&nbsp;&nbsp;&nbsp;&nbsp;
			  <?php if (file_exists($base . '/albums/' . $a->path . '/tn/' . $i->src )): ?>
			  	<input id="album-thumb" type="checkbox" name="album-thumb"<?php if ($album_thumb == $path) _e(' checked="checked"'); ?> /> Use as album preview
			  <?php endif; ?>
	</fieldset>
	<fieldset><label>Link</label><input type="text" name="link" value="<?php _e(htmlspecialchars($i->link)); ?>" size="50" /></fieldset>
	<fieldset><input type="checkbox" name="tgt"<?php if ($i->target == 1) _e(' checked="checked"'); ?> /> Open link in same window</fieldset>
	<fieldset><label>Caption</label><textarea name="caption" cols="40" rows="5"><?php _e(htmlspecialchars($i->caption)) ?></textarea></fieldset>
	<fieldset><label>Pause (leave 0 for default)</label><input type="text" name="pause" value="<?php _e($i->pause) ?>" size="3" /></fieldset>
	<fieldset><input id="save-button" type="button" value="Save changes" onclick="save_image();" /> <span id="image-messenger" style="display:none;"></span></fieldset>
	</form>
</div>