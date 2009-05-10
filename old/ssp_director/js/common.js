// Holds the currently edited image's ID so we can do various checks
var current_image_edit = 0;
var cur_size = 175;
var cur_v = 1;

var detect = navigator.userAgent.toLowerCase();
var OS,browser,version,total,thestring;

if (checkIt('konqueror'))
{
	browser = "Konqueror";
	OS = "Linux";
}
else if (checkIt('safari')) browser = "Safari"
else if (checkIt('omniweb')) browser = "OmniWeb"
else if (checkIt('opera')) browser = "Opera"
else if (checkIt('webtv')) browser = "WebTV";
else if (checkIt('icab')) browser = "iCab"
else if (checkIt('msie')) browser = "Internet Explorer"
else if (!checkIt('compatible'))
{
	browser = "Netscape Navigator"
	version = detect.charAt(8);
}
else browser = "An unknown browser";

if (!version) version = detect.charAt(place + thestring.length);

if (!OS)
{
	if (checkIt('linux')) OS = "Linux";
	else if (checkIt('x11')) OS = "Unix";
	else if (checkIt('mac')) OS = "Mac"
	else if (checkIt('win')) OS = "Windows"
	else OS = "an unknown operating system";
}  

function checkIt(string)
{
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}

slider_init = true;

function toggle_album_options(id) {
	var elem = $(id);
	if (elem.style.display == 'none') {
		Effect.SlideDown(id);
	} else {
		Effect.SlideUp(id);
	}
}

function toggle_album_active(id, new_val) {
	if (new_val == 1)
		init_message('Activating album...', 1, false);
	else
		init_message('Inactivating album...', 1, false);
	var url = "ajax/toggle_album_active.php";
	var params = 'aid=' + id + '&value=' + new_val;
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, {method: 'post', parameters: params,
		onComplete: function() {
			if (new_val == 1)
				init_message('Activating album...done', 2, true);
			else
				init_message('Inactivating album...done', 2, true); 
			window.setTimeout("albums_sort_init()", 500);
		}});
}

function toggle_upload_format() {
	var elem = $('images-format');
	var val = elem.options[elem.selectedIndex].value;
	var file_elem = $('file-upload');
	var folder_scan = $('folder-scan');
	var file_lbl = $('file-label');
	switch(val) {
		case('0'):
			file_elem.style.display = 'none';    
			folder_scan.style.display = 'none';
			break;
		case('1'):
			file_lbl.innerHTML = 'ZIP file:';
			file_elem.style.display = 'block';    
			folder_scan.style.display = 'none';
			break;    
		case('2'):
			file_lbl.innerHTML = 'First file:';
			file_elem.style.display = 'block';    
			folder_scan.style.display = 'none';
			break; 
		case('3'):
			file_elem.style.display = 'none';    
			folder_scan.style.display = 'block';
			break;                                                  
			
	}
}   

function _confirm(msg, action, arg) {
	clear_messenger_classes();
	Element.addClassName($('messenger-span'), 'stop');          
	msg += '<br /><input type="button" value="Ok" onclick="' + action + '(' + arg + ');" /> <input type="button" value="Cancel" onclick="kill_messenger_quick(\'\'); return false;" />';
	$('messenger-span').innerHTML = msg;
	Effect.Appear('messenger-wrap', { duration: 0.1 });   
}

function delete_album(id) {
	_confirm("This will delete the album from the database and from your server. Are you sure you want to do this?", 'delete_album_exe', id);
}                                                                                                                                             

function delete_album_exe(id) {
	init_message('Deleting...', 1, false);
	var url = "ajax/delete_album.php";
	var params = 'aid=' + id;
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() { init_message('Album deleted...', 2, true) },
		onComplete: function() { albums_sort_init(); }
	});
}

function delete_slideshow(id) {
	_confirm("Are you sure you want to delete the link to this slide show?", 'delete_slideshow_exe', id);
}

function delete_slideshow_exe(id) {
	init_message('Deleting...', 1, false);
	var url = "ajax/delete_slideshow.php";
	var params = 'id=' + id;
	var tgt = 'slideshows';
	var myAjax = new Ajax.Updater(tgt, url, {method: 'post', parameters: params, onComplete: function() { init_message('Slide show deleted...', 2, true) } });
}  

function rotate_img(id, aid, r) {         
	Effect.BlindUp('the_img');
	init_message('Rotating...', 1, false);
	var url = "ajax/rotate_image.php";
	var params = 'id=' + id + '&aid=' + aid + '&deg=' + r;
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() {
			var url = "ajax/get_image_form.php"; 
			var params = 'id=' + id + '&aid=' + aid + '&rotate=1';
			var tgt = 'target';
			var myAjax = new Ajax.Updater(tgt, url, {
				method: 'post',
				parameters: params,
				onComplete: function(){ 
					init_message('Rotating...done', 1, true);
					var the_img = $('image_' + id).getElementsByTagName('IMG')[0];
					var img_src = the_img.src;
					var rndNum = Math.random() 
					rndNum = parseInt(rndNum * 1000);
					the_img.src = img_src + '?' + rndNum;
					if (Element.hasClassName(the_img, 'tall')) {
						Element.removeClassName(the_img, 'tall');
						Element.addClassName(the_img, 'wide');
					} else {
						Element.removeClassName(the_img, 'wide');
						Element.addClassName(the_img, 'tall');	
					}
				} });
	   }
   });
} 

function prefill_links(id, type) {
	$('links-button').disabled = true;   
	$('links-js-button').disabled = true;   
	$('links-clear-button').disabled = true;
	$('links-messenger').innerHTML = '<small>Filling links...</small>';
	$('links-messenger').style.display = 'inline';
	var url = "ajax/manage_links.php";
	var params = 'aid=' + id + '&action=' + type;
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('links-messenger').innerHTML = '<small>Links filled!</small>';
			window.setTimeout("kill_messenger('links-messenger')", 2000);
			$('links-button').disabled = false;
			$('links-js-button').disabled = false;
			$('links-clear-button').disabled = false;
	    } });
}

function clear_links(id) {
	$('links-button').disabled = true; 
	$('links-js-button').disabled = true;
	$('links-clear-button').disabled = true;
	$('links-messenger').innerHTML = '<small>Clearing links...</small>';
	$('links-messenger').style.display = 'inline';
	var url = "ajax/manage_links.php";
	var params = 'aid=' + id + '&action=clear';
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('links-messenger').innerHTML = '<small>Links cleared!</small>';
			window.setTimeout("kill_messenger('links-messenger')", 2000);
			$('links-button').disabled = false;
			$('links-clear-button').disabled = false;
			$('links-js-button').disabled = false; 
		} });
}
 
function prefill_targets(id) {
	$('target-button').disabled = true;
	$('target-clear-button').disabled = true;
	$('target-messenger').innerHTML = '<small>Setting targets...</small>';
	$('target-messenger').style.display = 'inline';
	var url = "ajax/manage_targets.php";
	var params = 'aid=' + id + '&action=fill';
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('target-messenger').innerHTML = '<small>Targets set!</small>';
			window.setTimeout("kill_messenger('target-messenger')", 2000);
			$('target-button').disabled = false;
			$('target-clear-button').disabled = false;
	    } });
}

function clear_targets(id) {
	$('target-button').disabled = true;
	$('target-clear-button').disabled = true;
	$('target-messenger').innerHTML = '<small>Setting targets...</small>';
	$('target-messenger').style.display = 'inline';
	var url = "ajax/manage_targets.php";
	var params = 'aid=' + id + '&action=clear';
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('target-messenger').innerHTML = '<small>Targets set!</small>';
			window.setTimeout("kill_messenger('target-messenger')", 2000);
			$('target-button').disabled = false;
			$('target-clear-button').disabled = false;
		} });
}

function prefill_captions(id) {
	$('captions-button').disabled = true;
	$('captions-clear-button').disabled = true;
	$('captions-messenger').innerHTML = '<small>Filling captions...</small>';
	$('captions-messenger').style.display = 'inline';
	var url = "ajax/manage_captions.php";
	var params = 'aid=' + id + '&action=fill' + '&str=' + $F('caption');
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('captions-messenger').innerHTML = '<small>Captions filled!</small>';
			window.setTimeout("kill_messenger('captions-messenger')", 2000);
			$('captions-button').disabled = false;
			$('captions-clear-button').disabled = false;
			if ($('caption-clear').style.display == 'none') 
				Effect.BlindDown('caption-clear');
	    } });
}

function clear_captions(id) {
	$('captions-button').disabled = true;
	$('captions-clear-button').disabled = true;
	$('captions-clear-messenger').innerHTML = '<small>Clearing captions...</small>';
	$('captions-clear-messenger').style.display = 'inline';
	var url = "ajax/manage_captions.php";
	var params = 'aid=' + id + '&action=clear';
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('captions-clear-messenger').innerHTML = '<small>Captions cleared!</small>';
			window.setTimeout("kill_messenger('captions-clear-messenger')", 2000);
			$('captions-button').disabled = false;
			$('captions-clear-button').disabled = false;
			window.setTimeout("new Effect.BlindUp('caption-clear')", 1000);
		} });
} 

function prefill_titles(id) {
	$('title-button').disabled = true;
	$('title-clear-button').disabled = true;
	$('title-messenger').innerHTML = '<small>Filling titles...</small>';
	$('title-messenger').style.display = 'inline';
	var url = "ajax/manage_titles.php";
	var params = 'aid=' + id + '&action=fill' + '&str=' + $F('title');
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('title-messenger').innerHTML = '<small>Titles filled!</small>';
			window.setTimeout("kill_messenger('title-messenger')", 2000);
			$('title-button').disabled = false;
			$('title-clear-button').disabled = false;
			if ($('title-clear').style.display == 'none') 
				Effect.BlindDown('title-clear');
	    } });
}

function clear_titles(id) {
	$('title-button').disabled = true;
	$('title-clear-button').disabled = true;
	$('title-clear-messenger').innerHTML = '<small>Clearing titles...</small>';
	$('title-clear-messenger').style.display = 'inline';
	var url = "ajax/manage_titles.php";
	var params = 'aid=' + id + '&action=clear';
	var myAjax = new Ajax.Request(url, { 
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			$('title-clear-messenger').innerHTML = '<small>Titles cleared!</small>';
			window.setTimeout("kill_messenger('title-clear-messenger')", 2000);
			$('title-button').disabled = false;
			$('title-clear-button').disabled = false;
			window.setTimeout("new Effect.BlindUp('title-clear')", 1000);
		} });
}

function fill_cap_tag(val) {
	var previous_val = $F('caption');
	if (previous_val != '')
		previous_val += ' ';
	$('caption').innerHTML = previous_val + val;
} 

function fill_title_tag(val) {
	var previous_val = $F('title');
	if (previous_val != '')
		previous_val += ' ';
	$('title').value = previous_val + val;
}

function add_slideshow() {
	if ($('theForm').name.value == '') {
		init_message('Please give the slide show a name.', 3, false);
		return false;
	}
	
	if ($('theForm').url.value == '') {
		init_message('Please give the URL to the slide show.', 3, false);
		return false;
	}
	
	var allNodes = Form.serialize("theForm");
	var url = "ajax/add_slideshow.php";
	var tgt = 'slideshows';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		parameters: allNodes, 
		onSuccess: function() {
			Form.reset('theForm');
			}                    
		});
}   

function update_album() {
	$('save-button').disabled = true;
	$('album-messenger').innerHTML = '<small>Saving...</small>';
	$('album-messenger').style.display = 'inline';
	var allNodes = Form.serialize("theForm");
	var url = "ajax/update_album.php";
	var tgt = 'album-name';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		parameters: allNodes, 
		onSuccess: function() {
			$('album-messenger').innerHTML = '<small>Album updated!</small>';
			window.setTimeout("kill_messenger('album-messenger')", 2000);
			$('save-button').disabled = false;
			} 
		});
}

function update_dg_name(id) {
	$('dg-name-btn').disabled = true;
	$('dg-messenger').innerHTML = '<small>Saving...</small>';
	$('dg-messenger').style.display = 'inline';
	var allNodes = "name=" + $F('name') + '&did=' + id;
	var url = "ajax/update_dg_name.php";
	var tgt = 'album-name';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		parameters: allNodes, 
		onSuccess: function() {
			$('dg-messenger').innerHTML = '<small>Name updated!</small>';
			window.setTimeout("kill_messenger('dg-messenger')", 2000);
			$('dg-name-btn').disabled = false;
			} 
		});
}
 
function update_audio() {
	$('save-button').disabled = true;
	$('audio-messenger').innerHTML = '<small>Saving...</small>';
	$('audio-messenger').style.display = 'inline';
	var allNodes = Form.serialize("theForm");
	var url = "ajax/update_audio.php";
	var myAjax = new Ajax.Request(url, {
		method: 'post',
		parameters: allNodes, 
		onSuccess: function() {
			$('audio-messenger').innerHTML = '<small>Album audio updated!</small>';
			window.setTimeout("kill_messenger('audio-messenger')", 2000);
			$('save-button').disabled = false;
			} 
		});
}

function update_profile() {
	if ($('theForm').pass.value != $('theForm').pass_confirm.value) {
		init_message("Passwords don't match!", 3, false);
		return false;
	} else {
		$('user-messenger').innerHTML = '<small>Saving...</small>';
		$('user-messenger').style.display = 'inline';
		$('save-button').disabled = true;
		var allNodes = Form.serialize("theForm");
		var url = "ajax/update_profile.php";
		var myAjax = new Ajax.Request(url, {
			method: 'post',
			parameters: allNodes, 
			onSuccess: function() {
				$('user-messenger').innerHTML = '<small>Profile updated!</small>';
				window.setTimeout("kill_messenger('user-messenger')", 2000);
				$('save-button').disabled = false;
				$('theForm').pass_confirm.value = '';
				$('theForm').pass.value = '';
				} 
			});  
	}
}

function toggle_preview(tgt) { 
	if (tgt != '') {
		var elem = $(tgt);
	}
	var elems = ['preview-select', 'preview-edit', 'preview-upload']; 
	for (i=0; i< 3; i++) {
		if (Element.visible(elems[i])) {
			new Effect.BlindUp(elems[i]);
			if (tgt != '' && tgt != elems[i]) { 
				new Effect.BlindDown(elem, { queue: 'end' }); 
			}
			return;
		}
	} 
	if (tgt != '') {
	new Effect.BlindDown(elem, { queue: 'end' }); 
	}
}    

function update_preview(url) {
	var bg = '#202020';
	if (url != '') {
		bgImg = 'url(' + url + ')';
		$('album-preview').style.backgroundImage = bgImg;
		$('album-preview').style.backgroundPosition = 'center';
		$('album-preview').style.backgroundRepeat = 'no-repeat';
	} else {
		$('album-preview').style.background = bg;
	}
/*	var elem = $('preview-img').parentNode.getElementsByTagName('SPAN')[0];*/
/*	elem.style.bottom = '5px'; */
}

function update_preview_img(aid) {
	var url = "ajax/update_preview_img.php"; 
	var tgt = 'preview-img';
	var params = 'aid=' + aid;
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		parameters: params
		});
}

function update_show_tn(val, aid) {
	if (val)
		val = 1;
	else
		val = 0;
	var params = 'val=' + val + '&aid=' + aid;
	var url = "ajax/update_show_tn.php";
	var myAjax = new Ajax.Request(url, {
		method: 'post',
		parameters: params 
	});
}

function update_show_headers(val, aid) {
	if (val)
		val = 1;
	else
		val = 0;
	var params = 'val=' + val + '&aid=' + aid;
	var url = "ajax/update_show_headers.php";
	var myAjax = new Ajax.Request(url, {
		method: 'post',
		parameters: params 
	});
}   

function img_sort_init() {
	if (slider_init) {
		var demoSlider = new Control.Slider('handle1','track1', {
			axis:'horizontal',
			minimum: 0,
			maximum:200,
		    alignX: -8,
			increment: 2,
			sliderValue: 200 }
			);               
			
		demoSlider.options.onSlide = function(value){
		  scaleIt(value);
		}

		demoSlider.options.onChange = function(value){
		  scaleIt(value);
		}
		v_cooked = getCookie('v');
		if (v_cooked == null) {
			v_cooked = 1;
	    }      
		demoSlider.setValue(v_cooked);
		slider_init = false;
		Effect.BlindDown($('image-view'));
		kill_messenger(''); 
   	}   
		Position.includeScrollOffsets = true;
    	Sortable.create('image-view', {
		overlap:'horizontal',
		constraint: false, 
		handle: 'scale-image',
		scroll: window,
		 onUpdate:function() {
			update_img_order();
		 }     
	});
                            
}

function update_img_order() {
	var keys = Sortable.serialize('image-view');
	var url = "ajax/update_image_order.php";
	var myAjax = new Ajax.Request(url, { 
		method: 'post',
		parameters: keys,
		onComplete: function () {
			renum_images();
		}
	});
}      

function renum_images() {
	var elem = document.getElementsByClassName('counter');
	var total = elem.length;
	$('img-count').innerHTML = total;
	for (i=0; i < elem.length; i++) {
			elem[i].innerHTML = (i+1) + '/' + total;
	}
}

function dynamic_sort_init() {
	Sortable.create('sort',
		{overlap:'vertical', constraint: 'vertical', tag: 'li',
		 onUpdate:function(){
			var keys = Sortable.serialize('sort', { tag: 'li' });
			var url = "ajax/update_dynamic_order.php";
			var myAjax = new Ajax.Request(url, {method: 'post', parameters: keys});
		 }
	});
}

function albums_sort_init() {
	var check = $('active-albums').getElementsByTagName('LI').length;
	if (check > 1) {
		Sortable.create('active-albums',
			{overlap:'vertical', constraint: 'vertical',
			 onUpdate:function(){
				var keys = Sortable.serialize('active-albums');
				var url = "ajax/update_album_order.php";
				var myAjax = new Ajax.Request(url, {method: 'post', parameters: keys});
			 }
		});             
	}
	if (check == 1) {
		Element.removeClassName($('active-albums').getElementsByTagName('LI')[0], 'sort');
	}
}

function edit_image(id, aid) {
	init_message("Loading image for edit...", 1, true);
  	clear_classes('current');
	var elem = $('image_' + id);
	elem.className = 'current';
	var url = "ajax/get_image_form.php";
	var params = 'id=' + id + '&aid=' + aid;
	var tgt = 'target';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		evalScripts:true,
		parameters: params,
		onComplete: function() {
			init_message("Loading image for edit...", 2, true);
			var elem = $('edit-box');
			if (elem.style.display == 'none') {
				Effect.BlindDown(elem, { queue: 'end' });        
			}
			new Effect.ScrollTo('container', { offset: -10 });
			current_image_edit = id;
		} }); 
}  

function prev_image(aid) {
	init_message("Loading image for edit...", 1, true);
	lis = $('image-view').getElementsByTagName('LI');
	for (i=0; i < lis.length; i++) {
		if (Element.hasClassName(lis[i], 'current')) {
			if (lis[i-1] == undefined) {
				id = lis[lis.length-1].id.split('image_')[1];
			} else {
				id = lis[i-1].id.split('image_')[1];
			}   
			edit_image(id, aid);
			break;
		}
	}
} 

function next_image(aid) { 
	init_message("Loading image for edit...", 1, true);
	lis = $('image-view').getElementsByTagName('LI');
	for (i=0; i < lis.length; i++) {
		if (Element.hasClassName(lis[i], 'current')) {
			if (lis[i+1] == undefined) {
				id = lis[0].id.split('image_')[1];
			} else {
				id = lis[i+1].id.split('image_')[1];
			}   
			edit_image(id, aid);
			break;
		}
	}
}

function hide_image_edit() {
	clear_classes('current');
	Effect.BlindUp('edit-box');
	current_image_edit = 0;
}

function clear_classes(class_name) {
	var clear_these = document.getElementsByClassName(class_name);
	for (i=0; i < clear_these.length; i++) {
		clear_these[i].className = '';
	}
}  

function save_image() {
	$('save-button').disabled = true;
	$('image-messenger').innerHTML = '<small>Saving image...</small>';
	$('image-messenger').style.display = 'inline';
	var allNodes = Form.serialize("theForm");
	var url = "ajax/update_image.php";
	var tgt = 'dummy-tgt';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: allNodes, 
		onSuccess: function() { 
			$('save-button').disabled = false;
			$('image-messenger').innerHTML = '<small>Image saved!</small>';
			window.setTimeout("kill_messenger('image-messenger')", 2000);
		} 
	});
}

function delete_image(id) {
	_confirm('This will delete the image from this album and from the server. Are you sure you want to continue?', 'delete_image_exe', id);
}

function delete_image_exe(id) {
	init_message('Deleting image...', 1, false)
	var url = "ajax/delete_image.php";
	var params = 'id=' + id;
	var tgt = 'messenger-p';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post',
		parameters: params,
		onComplete: function() {
			Effect.Fade('image_' + id);
			Element.removeClassName($('counter_' + id), 'counter');
			Element.addClassName($('counter_' + id), 'counter-off');
			update_img_order();
			if (current_image_edit == id)
				hide_image_edit();
			init_message('', 1, false);
			window.setTimeout("kill_messenger('')", 3000);
		} 
		});
} 

function generate_thumbs() {
	var w = $('valw').value;
	var h = $('valh').value;

	if (w == '') {
		init_message('Please enter the maximum width for your thumbnails!', 3, false);
		return false;
	}
	
	if (h == '') {
		init_message('Please enter the maximum height for your thumbnails!', 3, false);
		return false;
	} 
	init_message('Generating thumbs', 1, false);
	return true;
}

function process_images() {
	var dimw = $('pvalw').value;
	var dimh = $('pvalh').value;
	var quality = $('pquality').value;
	                    
	if (dimw == '') {
		init_message('Please enter the maximum width for your images!', 3, false);
		return false;
	}
	
	if (dimh == '') {
		init_message('Please enter the maximum height for your images!', 3, false);
		return false;
	}  
	
	init_message('Processing images', 1, false);
	return true;
}

function clear_thumbs(id) {
	_confirm("Are you sure you want to clear your thumbnails for this album?", 'clear_thumbs_exe', id);
}

function clear_thumbs_exe(id) {
	init_message('Clearing thumbs...', 1, false)
	
	var url = "ajax/clear_thumbs.php";
	var params = 'aid=' + id;
	var tgt = 'process-pane';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params,
		onComplete: function () {
			init_message('Clearing thumbs...done', 2, true)
			var url = "ajax/fill_preview.php";
			var params = 'aid=' + id;
		    var tgt = 'select-thumb';
			var myAjax = new Ajax.Updater(tgt, url, { 
				method: 'post', 
				parameters: params
			});
		} });    
}   

function update_user_perms(elem, id) {
	var new_val = elem.options[elem.selectedIndex].value;
	var url = "ajax/update_user.php";
	var params =  'val=' + new_val + '&id=' + id;
	var myAjax = new Ajax.Request(url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() { 
			init_message('User updated successfully...', 2, true);
		} 
	});
}

function generate_preview(id) {
	var dim = $('preview-val').value;
	var quality = $('preview-quality').value;
	
	if (quality == '' || quality > 100 || quality < 0)
		quality = 75;
		
	if (dim == '') {
		init_message('Please enter the maximum width/height for your preview!', 3, false);
		return false;
	} else {
		$('preview-val').disabled = true;
		$('preview-button').disabled = true;
		init_message('Generating preview...', 1, false);

		var url = "ajax/generate_preview.php";
		var params = 'aid=' + id + '&dim=' + $('preview-val').value + '&quality=' + quality;
        var tgt = 'preview-img';
		var myAjax = new Ajax.Updater(tgt, url, { 
			method: 'post', 
			parameters: params,
			onSuccess: function () {
				toggle_preview('');
				$('preview-val').disabled = false;
				$('preview-button').disabled = false;
				$('preview-messenger').style.display = 'none';
				refill_preview(id);
			} }); 
	}
}

function refill_preview(id) {
	var url = "ajax/fill_preview.php";
	var params = 'aid=' + id;
    var tgt = 'select-thumb';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params
	});
}   

function designate_preview(img, aid) {
	init_message('Setting album preview...', 1, false);
	if (img == '') {
		toggle_preview('');
	}
	var url = "ajax/designate_preview.php";
	var params = 'aid=' + aid + '&img=' + img;
    var tgt = 'preview-img';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params,
		onSuccess: function () {
			if (Element.visible('preview-select'))
				Effect.BlindUp('preview-select');
			var url = "ajax/check_preview.php";
			var params = 'aid=' + aid;
		    var tgt = 'preview-edit';
			var myAjax = new Ajax.Updater(tgt, url, { 
				method: 'post', 
				parameters: params,
				onComplete: function () {
					init_message('Setting album preview...done', 2, true);
				} 
				
			});
		}
	    });
}   

function delete_user(id) {
	_confirm("Are you sure you want to delete this user?", 'delete_user_exe', id);
}  

function delete_user_exe(id) {
	init_message('Deleting User...', 1, false)
	var url = "ajax/delete_user.php";
	var params = 'id=' + id;
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, { method: 'post', parameters: params,
	 	onComplete: function () {
			init_message('Deleting User...Done', 2, true)
	}});                
}

function add_user() {
	if (validate_user_invite()) {
	var allNodes = Form.serialize("theForm");
	var url = "ajax/add_user.php";
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: allNodes, 
		onSuccess: function() {
			init_message('User added (an email with instructions was sent as well)...', 2, true);
		} 
	});
	}
}

function add_dynamic_gallery() {
	if ($F('new_name') == '') {
		init_message("Please give the new dynamic gallery a name.", 3, false);
		return;
	} else {
	var params = 'new_name=' + $F('new_name');
	var url = "ajax/add_dynamic_gallery.php";
	var tgt = 'dynamic-fill';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() {
			$('new_name').value = '';  
		} 
	});
	}
}	

function delete_dynamic_gallery(id, refer) {                                                                                               
	args = id + ', \'' + refer + '\'';
	_confirm("This will delete this dynamic gallery and all of the links created within it. Are you sure?", 'delete_dynamic_gallery_exe', args);
}

function delete_dynamic_gallery_exe(id, refer) {
	init_message('Deleting dynamic gallery...', 1, false)
	var params = 'id=' + id + '&refer=' + refer;
	var url = "ajax/delete_dynamic_gallery.php";
	if (refer == 'dashboard')
		var tgt = 'fill';
	else
		var tgt = 'dynamic-fill';
		
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() {     
			init_message('Deleting dynamic gallery...done', 2, true);
		},
		onComplete: function() { albums_sort_init(); } 
	});
}

function add_dynamic_link(did, aid) {
	var params = 'did=' + did + '&aid=' + aid;
	var url = "ajax/add_dynamic_link.php";
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() {    
			window.setTimeout("dynamic_sort_init()", 500);
		} 
	});
}

function delete_dynamic_link(id, did) {
	var params = 'id=' + id + '&did=' + did;
	var url = "ajax/delete_dynamic_link.php";
	var tgt = 'fill';
	var myAjax = new Ajax.Updater(tgt, url, {
		method: 'post', 
		parameters: params, 
		onSuccess: function() {      
			window.setTimeout("dynamic_sort_init()", 500);
		} 
	});
}  

function clear_messenger_classes() {
	var elem_p = $('messenger-span');
	Element.removeClassName(elem_p, 'hourglass');
	Element.removeClassName(elem_p, 'accept');
	Element.removeClassName(elem_p, 'exclamation');
	Element.removeClassName(elem_p, 'stop');
}                                                  

function init_message(msg, status, autokill) {
	var elem_p = $('messenger-span');
	var elem = $('messenger-wrap');
	if (msg != '') {    
		clear_messenger_classes();
		switch(status) {
			case(1):
				Element.addClassName(elem_p, 'hourglass');
				break;    
			case(2):
				Element.addClassName(elem_p, 'accept');
				break;
			case(3):
				Element.addClassName(elem_p, 'exclamation');
				msg += '<br /><input type="button" value="Ok" onclick="kill_messenger_quick(\'\'); this.parentNode.removeChild(this);" />'
				break;
		}          
		elem_p.innerHTML = msg;
	}
	Effect.Appear(elem, { duration: 0.1 });
	if (autokill)
		window.setTimeout("kill_messenger('')", 1000);
}

function kill_messenger(name) {
	if (name == '')
		name = 'messenger-wrap';
	Effect.Fade(name, { duration: 0.1, queue: 'end' });
}

function kill_messenger_quick(name) {
	if (name == '')
		name = 'messenger-wrap';
	Element.hide(name);
}

function scaleIt(v) {
	setCookie('v', v);    
	var scalePhotos = document.getElementsByClassName('scale-image');
	floorSize = .5;
	ceilingSize = 1.0;
	v = floorSize + (v * (ceilingSize - floorSize)); 
	size = v*175;
	cur_size = size;
  	cur_v = v;
  	var len = scalePhotos.length;
    var elem;          
	size = size + 'px';
  	for (i=0; i < len; i++) {
		elem = scalePhotos[i];
		elem.style.width = size;
		if (browser != 'Safari')
			elem.childNodes[0].style.height = size;
		elem.style.height = size;
		elem.parentNode.style.width = size;
	} 
}

function scaleItSingle(elem_id) {
	top_elem = $(elem_id);
	elem = top_elem.childNodes[0];
	elem.style.width = cur_size+'px';
	elem.childNodes[0].style.height = cur_size+'px';
	elem.style.height = cur_size+'px';
	top_elem.style.width = cur_size+'px';   
}

function insert_image(id) {
	var url = "ajax/show_new_image.php";                         
	var params = 'img=' + id + '&size=' + cur_size + '&type=single';
    var tgt = 'image-view';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params,
		insertion: Insertion.Bottom,
		onComplete: function() {
			scaleItSingle('image_' + id);
			update_img_order();
			Effect.Appear('image_' + id);
			img_sort_init();
			new Effect.ScrollTo('image_' + id);
			kill_messenger('');
			new Effect.Highlight('image_' + id, { startcolor: '#76B41C', endcolor: '#303030', restorecolor: '#303030', queue: 'end' });
		}                                                              
	});
}

function insert_batch_images(ids) {
	var url = "ajax/show_new_image.php";                         
	var params = 'img_ids=' + ids + '&size=' + cur_size + '&type=batch';
    var tgt = 'image-view';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params,
		insertion: Insertion.Bottom,
		onComplete: function() {
			var id_arr = new Array();
			id_arr = ids.split(',');
			new Effect.ScrollTo('image_' + id_arr[0]); 
			for (i=0; i<id_arr.length; i++) {
				scaleItSingle('image_' + id_arr[i]);
				Effect.Appear('image_' + id_arr[i], { queue: 'end' });
				new Effect.Highlight('image_' + id_arr[i], { startcolor: '#76B41C', endcolor: '#303030', restorecolor: '#303030', queue: 'end' });
			}
			update_img_order();
			img_sort_init();
			kill_messenger('');
		}                                                              
    });
}

function validate_add_album() {
	if ($F('album_name') == '') {
		init_message("Please give the album a name before continuing. You can always change this later.", 3, false);
		return false;
	}
	if ($F('images-format') == 0) {
		init_message("Select how you will be adding your images.", 3, false);
		return false;
	}
	if ($F('images-format') == 3 && $F('scan-this') == 0) {
		init_message('No folders are available to scan. Upload file to the albums folder for them to show up here.', 3, false);
		return false;
   	}          
	if ($F('images-format') != 3 && $('upload').value == '') {
		init_message('Select a file to upload.', 3, false);
		return false;
	}
	init_message('Adding album...please wait', 1, false)        
	return true;
}

function init_audio_upload() {
	init_message('Uploading audio...please wait...', 1, false);
}   


function validate_user_invite() {
	
	if ($F('email') == '') {
		init_message("Please enter the recipient's email", 3, false);
		return false;
	}
	if ($F('from_email') == '') {
		init_message("Please enter your email for the 'from' address", 3, false);
		return false;
	}
	   
	return true;
}

function setCookie(name, value, expires, path, domain, secure) {
    document.cookie= name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires.toGMTString() : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}

function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    } else {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1) {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
} 

function fetch_slideshow() {
	window.open($F('ss_select'), 'Slideshow');
}

function toggle_view_btn(val) {
	if (val == 0)
		$('view_ss_btn').disabled = true;
	else
		$('view_ss_btn').disabled = false;
}

function rescan_album(id) {
	init_message('Scanning for new images...', 1, false);
	var url = "ajax/rescan_album.php";                         
	var params = 'id=' + id;
    var tgt = 'messenger-p';
	var myAjax = new Ajax.Updater(tgt, url, { 
		method: 'post', 
		parameters: params                                                           
	});
}                                