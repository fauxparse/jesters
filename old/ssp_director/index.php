<?php
// Get common server-side stuff and hookup to the DB
include("inc/data/head.php");                 

session_start();

// What page are we on?
if (empty($_GET['p']))
	// No page parameter, must be at the Dashboard
	$p = 'dash';
else
	$p = $_GET['p'];
	
// Is tab set
if (empty($_GET['tab']))
	$tab = '';
else
	$tab = $_GET['tab'];

if ($p != 'login'):
	session_authenticate();
endif;

if (!empty($_GET['new']))
	$new = 1;
else
	$new = 0;
	
// Set page title before we include the HTML Head Section
switch($p):
	case('edit-album'):
		$page_title = "Edit album";
		break;   
	case('add-album'):
		$page_title = "Add an album";
		break;     
	case('manage-users'):
		$page_title = "Manage users";
		break;    
	case('user-profile'):
		$page_title = "User profile";
		break;    
	case('dynamic-galleries'):
		$page_title = "Dynamic galleries";
		break;    
	case('dynamic-gallery'):
		$page_title = "Dynamic galleries";
		break;    
	case('login'):
		$page_title = "Login";
		break;    
	case('edit-slideshows'):
		$page_title = "Edit slide shows";
		break;    
   	case('upgrade'):
		$page_title = "Upgrade";
		break;
	default:
		$page_title = "Dashboard";
		break;
endswitch;

// HTML DOCTYPE and Header
include("inc/html/head.php");

?>
	
	<?php
		switch($p):
			case('edit-album'):
				if (isset($_GET['tab']) && $_GET['tab'] == 'images'):
	?>
	
	<body onload="img_sort_init();">
		
	<?php   
				endif;
			break;
			
			case('dynamic-gallery'):
	?>
	
	<body onload="dynamic_sort_init();">
	
	<?php
			break;
			
			case('dash'):
	?>
	
    <body onload="albums_sort_init();">
	
	<?php
			break;
			
			case('login'):
			
	?>
	
	<body onload="$('user').focus();">
		
	<?php
			break;
			
		  	default:
	?>
	
	<body>
	
	<?php
		endswitch;
    ?>
			
   	<div id="messenger-wrap" style="display:none;">
		<div id="messenger">
			<p id="messenger-p" class="clearfix"><span id="messenger-span"></span></p>            
		</div>
	</div> 
  	<?php
    	if (isset($_GET['tab']) && $_GET['tab'] == 'images'):
	?>
	<script type="text/javascript" language="javascript">init_message('Loading Images', 1, false);</script>
	<?php
		endif;
	?>	
	<?php _e(get_header($page_title)); ?>
				    
			   	<div id="fill">	
	      		<?php
	 					// Get content based on page parameter
						switch($p):
							case('edit-images'):
								_e(get_images_edit($_GET['id']));
								break;
								
							case('edit-album'):
								_e(get_album_edit($_GET['id'], $tab, $new));
								break;
						   	
							case('generate-thumbs'):
								_e(get_generate_thumbs($_GET['id']));
								break;    
						
							case('add-album'):
								_e(get_add_album());
								break;    
							
					   		case('album-process'):
								_e(get_album_process($_GET['id'], $_SESSION['status']));
								break;
							
							case('login'):
								_e(get_login($_SESSION['status']));
								break;    
					   		
						    case('manage-users'):
								_e(get_manage_users());
								break;    
							
							case('dynamic-galleries'):
								_e(get_dynamic_galleries());
								break;
							        
							case('dynamic-gallery'):
								_e(get_dynamic_gallery($_GET['id']));
								break;    
							
							case('user-profile'):
								_e(get_user_profile());
								break;
								          
							case('edit-slideshows'):
								_e(get_edit_slideshows());
								break;        
								
							case('upgrade'):
								_e(get_upgrade());
								break;	
							
							default:
								_e(get_dashboard());
								break;
						endswitch;                                                      
						
						$_SESSION['status'] = null;
					?>
					</div>
					 		
			<?php _e(get_footer()); ?>

	</body>
	
</html>

