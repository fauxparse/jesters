<?php
	function get_self($script = '') {
		$self = $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != 80):
			$self .= ':' . $_SERVER['SERVER_PORT'];
		endif;
		if (isset($_SERVER['SCRIPT_URL'])):
			$self .= $_SERVER['SCRIPT_URL'];
		else:
			$self .= $_SERVER['PHP_SELF'];
		endif;                    
		if (empty($script)):        
			return $self; 
	    else:
	   		$self = str_replace($script, '', $self);
	 		return $self;
	    endif;
	}        
?>