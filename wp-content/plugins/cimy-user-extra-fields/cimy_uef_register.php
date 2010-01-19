<?php

function cimy_register_user_extra_hidden_fields_stage2() {
	global $start_cimy_uef_comment, $end_cimy_uef_comment;

	echo "\n".$start_cimy_uef_comment;

	foreach ($_POST as $name=>$value) {
		if (!(stristr($name, "cimy_uef_")) === FALSE) {
			echo "\t\t<input type=\"hidden\" name=\"".$name."\" value=\"".attribute_escape($value)."\" />\n";
		}
	}

	echo $end_cimy_uef_comment;
}

function cimy_register_user_extra_fields_signup_meta($meta) {
	foreach ($_POST as $name=>$value) {
		if (!(stristr($name, "cimy_uef_")) === FALSE) {
			$meta[$name] = $value;
		}
	}

	return $meta;
}

function cimy_register_user_extra_fields_mu_wrapper($blog_id, $user_id, $password, $signup, $meta) {
	cimy_register_user_extra_fields($user_id, $password, $meta);
}

function cimy_register_mu_overwrite_password($password) {
	global $wpdb;

	if (!empty($_GET['key']))
		$key = $_GET['key'];
	else
		$key = $_POST['key'];

	if (!empty($key)) {
		// seems useless since this code cannot be reached with a bad key anyway you never know
		$key = $wpdb->escape($key);

		$sql = "SELECT active, meta FROM ".$wpdb->signups." WHERE activation_key='".$key."'";
		$data = $wpdb->get_results($sql);

		// is there something?
		if (isset($data[0])) {
			// if not already active
			if (!$data[0]->active) {
				$meta = unserialize($data[0]->meta);

				if (!empty($meta["cimy_uef_wp_PASSWORD"])) {
					$password = $meta["cimy_uef_wp_PASSWORD"];
				}
			}
		}
	}

	return $password;
}

function cimy_register_user_extra_fields($user_id, $password="", $meta=array()) {
	global $wpdb_data_table, $wpdb, $max_length_value, $fields_name_prefix, $wp_fields_name_prefix, $wp_hidden_fields;
	
	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);
	
	$i = 1;
	
	// do first for the WP fields then for EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$are_wp_fields = true;
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$are_wp_fields = false;
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
		}
		
		$i++;

		foreach ($fields as $thisField) {

			$type = $thisField["TYPE"];
			$name = $thisField["NAME"];
			$field_id = $thisField["ID"];
			$label = $thisField["LABEL"];
			$rules = $thisField["RULES"];
			$input_name = $prefix.$wpdb->escape($name);
	
			// if flag to view also in the registration is activated
			if ($rules['show_in_reg']) {
				if (isset($meta[$input_name]))
					$data = stripslashes($meta[$input_name]);
				else if (isset($_POST[$input_name]))
					$data = stripslashes($_POST[$input_name]);
				else
					$data = "";
		
				if ($type == "picture") {
					$data = cimy_manage_upload($input_name, sanitize_user($_POST['user_login']), $rules, false, false, false);
				}
				else if ($type == "avatar") {
					// since avatars are drawn max to 512px then we can save bandwith resizing, do it!
					$rules['equal_to'] = 512;

					$data = cimy_manage_upload($input_name, sanitize_user($_POST['user_login']), $rules, false, false, true);
				}
				else {
					if ($type == "picture-url")
						$data = str_replace('../', '', $data);
						
					if (isset($rules['max_length']))
						$data = substr($data, 0, $rules['max_length']);
					else
						$data = substr($data, 0, $max_length_value);
				}
			
				$data = $wpdb->escape($data);
	
				if (!$are_wp_fields) {
					$sql = "INSERT INTO ".$wpdb_data_table." SET USER_ID = ".$user_id.", FIELD_ID=".$field_id.", ";
		
					switch ($type) {
						case 'avatar':
						case 'picture-url':
						case 'picture':
						case 'textarea':
						case 'textarea-rich':
						case 'dropdown':
						case 'password':
						case 'text':
							$field_value = $data;
							break;
			
						case 'checkbox':
							$field_value = $data == '1' ? "YES" : "NO";
							break;
			
						case 'radio':
							$field_value = $data == $field_id ? "selected" : "";
							break;
							
						case 'registration-date':
							$field_value = mktime();
							break;
					}
			
					$sql.= "VALUE='".$field_value."'";
					$wpdb->query($sql);
				}
				else {
					$f_name = strtolower($thisField['NAME']);
					
					$userdata = array();
					$userdata['ID'] = $user_id;
					$userdata[$wp_hidden_fields[$f_name]['post_name']] = $data;
					
					wp_update_user($userdata);
				}
			}
		}
	}
}

function cimy_registration_check_mu_wrapper($data) {
	$user_login = $data['user_name'];
	$user_email = $data['user_email'];
	$errors = $data['errors'];

	$data['errors'] = cimy_registration_check($user_login, $user_email, $errors);

	return $data;
}

function cimy_registration_check($user_login, $user_email, $errors) {
	global $wpdb, $rule_canbeempty, $rule_email, $rule_maxlen, $fields_name_prefix, $wp_fields_name_prefix, $rule_equalto_case_sensitive, $apply_equalto_rule, $cimy_uef_domain, $cimy_uef_file_types, $rule_equalto_regex;

	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);
	
	$i = 1;

	// do first for the WP fields then for EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
		}
		
		$i++;

		foreach ($fields as $thisField) {
	
			$field_id = $thisField['ID'];
			$name = $thisField['NAME'];
			$rules = $thisField['RULES'];
			$type = $thisField['TYPE'];
			$label = $thisField['LABEL'];
			$description = $thisField['DESCRIPTION'];
			$input_name = $prefix.$wpdb->escape($name);
			$unique_id = $prefix.$field_id;
			
			if (isset($_POST[$input_name]))
				$value = stripslashes($_POST[$input_name]);
			else
				$value = "";
	
			if ($type == "dropdown") {
				$ret = cimy_dropDownOptions($label, $value);
				$label = $ret['label'];
				$html = $ret['html'];
			}
			
			if (in_array($type, $cimy_uef_file_types)) {
				// filesize in Byte transformed in KiloByte
				$file_size = $_FILES[$input_name]['size'] / 1024;
				$file_type = $_FILES[$input_name]['type'];
				$value = $_FILES[$input_name]['name'];
			}
	
			// if flag to view also in the registration is activated
			if ($rules['show_in_reg']) {
	
				switch ($type) {
					case 'checkbox':
						$value == 1 ? $value = "YES" : $value = "NO";
						break;
					case 'radio':
						intval($value) == intval($field_id) ? $value = "YES" : $value = "NO";
						break;
				}
	
				// if the flag can be empty is NOT set OR the field is not empty then other check can be useful, otherwise skip all
				if ((!$rules['can_be_empty']) || ($value != "")) {
					if (($rules['email']) && (in_array($type, $rule_email))) {
						if (!is_email($value))
							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('hasn&#8217;t a correct email syntax.', $cimy_uef_domain));
					}
			
					if ((!$rules['can_be_empty']) && (in_array($type, $rule_canbeempty))) {
						if ($value == '')
							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t be empty.', $cimy_uef_domain));
					}
			
					if ((isset($rules['equal_to'])) && (in_array($type, $apply_equalto_rule))) {
						
						$equalTo = $rules['equal_to'];
						// 	if the type is not allowed to be case sensitive
						// 	OR if case sensitive is not checked
						// AND
						// 	if the type is not allowed to be a regex
						// 	OR if regex rule is not set
						// THEN switch to uppercase
						if (((!in_array($type, $rule_equalto_case_sensitive)) || (!$rules['equal_to_case_sensitive'])) && ((!in_array($type, $rule_equalto_regex)) || (!$rules['equal_to_regex']))) {
							
							$value = strtoupper($value);
							$equalTo = strtoupper($equalTo);
						}

						if ($rules['equal_to_regex']) {
							if (!preg_match($equalTo, $value)) {
								$equalmsg = " ".__("isn&#8217;t correct", $cimy_uef_domain);
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.$equalmsg.'.');
							}
						}
						else if ($value != $equalTo) {
							if (($type == "radio") || ($type == "checkbox"))
								$equalTo == "YES" ? $equalTo = __("YES", $cimy_uef_domain) : __("NO", $cimy_uef_domain);
							
							if ($type == "password")
								$equalmsg = " ".__("isn&#8217;t correct", $cimy_uef_domain);
							else
								$equalmsg = ' '.__("should be", $cimy_uef_domain).' '.$equalTo;
							
							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.$equalmsg.'.');
						}
					}
					
					// CHECK IF IT IS A REAL PICTURE
					if (($type == "picture") || ($type == "avatar")) {
						if (stristr($file_type, "image/") === false) {
							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('should be an image.', $cimy_uef_domain));
						}
					}
					
					// MIN LEN
					if (isset($rules['min_length'])) {
						$minlen = intval($rules['min_length']);
	
						if (in_array($type, $cimy_uef_file_types)) {
							if ($file_size < $minlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size less than', $cimy_uef_domain).' '.$minlen.' KB.');
							}
						}
						else {
							if (strlen($value) < $minlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length less than', $cimy_uef_domain).' '.$minlen.'.');
							}
						}
					}
					
					// EXACT LEN
					if (isset($rules['exact_length'])) {
						$exactlen = intval($rules['exact_length']);
						
						if (in_array($type, $cimy_uef_file_types)) {
							if ($file_size != $exactlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size different than', $cimy_uef_domain).' '.$exactlen.' KB.');
							}
						}
						else {
							if (strlen($value) != $exactlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length different than', $cimy_uef_domain).' '.$exactlen.'.');
							}
						}
					}
					
					// MAX LEN
					if (isset($rules['max_length'])) {
						$maxlen = intval($rules['max_length']);
						
						if (in_array($type, $cimy_uef_file_types)) {
							if ($file_size > $maxlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size more than', $cimy_uef_domain).' '.$maxlen.' KB.');
							}
						}
						else {
							if (strlen($value) > $maxlen) {
								
								$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length more than', $cimy_uef_domain).' '.$maxlen.'.');
							}
						}
					}
				}
			}
		}
	}
	
	return $errors;
}

function cimy_registration_form($errors=null) {
	global $wpdb, $start_cimy_uef_comment, $end_cimy_uef_comment, $rule_maxlen_needed, $fields_name_prefix, $wp_fields_name_prefix, $is_mu, $cuef_plugin_dir, $cimy_uef_file_types, $cimy_uef_textarea_types, $wp_27;

	// needed by cimy_uef_init_mce.php
	$cimy_uef_register_page = true;
	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);

	if ($wp_27) {
		if ($is_mu)
			$input_class = "cimy_uef_input_mu";
		else
			$input_class = "cimy_uef_input_27";
	}
	else {
		if ($is_mu)
			$input_class = "cimy_uef_input_mu";
		else
			$input_class = "cimy_uef_input";
	}

	$options = cimy_get_options();

	$tabindex = 21;
	
	echo $start_cimy_uef_comment;
	echo "\t";
	// needed to apply default values only first time and not in case of errors
	echo '<input type="hidden" name="cimy_post" value="1" />';
	echo "\n";
	$radio_checked = array();

	$i = 1;
	$upload_image_function = false;

	// do first for the WP fields then for EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
			$current_fieldset = 0;

			if ($options['fieldset_title'] != "")
				$fieldset_titles = explode(',', $options['fieldset_title']);
			else
				$fieldset_titles = array();
			
			if (isset($fieldset_titles[$current_fieldset]))
				echo "\n\t<h2>".$fieldset_titles[$current_fieldset]."</h2>\n";
		}
		
		$i++;
		
		$tiny_mce_objects = "";
	
		foreach ($fields as $thisField) {
	
			$field_id = $thisField['ID'];
			$name = $thisField['NAME'];
			$rules = $thisField['RULES'];
			$type = $thisField['TYPE'];
			$label = $thisField['LABEL'];
			$description = $thisField['DESCRIPTION'];
			$fieldset = $thisField['FIELDSET'];
			$input_name = $prefix.attribute_escape($name);
			$post_input_name = $prefix.$wpdb->escape($name);
			$maxlen = 0;
			$unique_id = $prefix.$field_id;

			if (isset($_POST[$post_input_name]))
				$value = stripslashes($_POST[$post_input_name]);
			// if there is no value and not $_POST means is first visiting then put all default values
			else if (!isset($_POST["cimy_post"])) {
				$value = $thisField['VALUE'];
				
				switch($type) {
	
					case "radio":
						if ($value == "YES")
							$value = $field_id;
						else
							$value = "";
						
						break;
		
					case "checkbox":
						if ($value == "YES")
							$value = "1";
						else
							$value = "";
						
						break;
				}
			}
			else
				$value = "";
			
			$value = attribute_escape($value);
			
			// if flag to view also in the registration is activated
			if ($rules['show_in_reg']) {
				if (($fieldset > $current_fieldset) && (isset($fieldset_titles[$fieldset])) && ($i != 1)) {
					$current_fieldset = $fieldset;

					if (isset($fieldset_titles[$current_fieldset]))
						echo "\n\t<h2>".$fieldset_titles[$current_fieldset]."</h2>\n";
				}

				if (($description != "") && ($type != "registration-date")) {
					echo "\t";
					echo '<p id="'.$prefix.'p_desc_'.$field_id.'" class="desc"><br />'.$description.'</p>';
					echo "\n";
				}

				echo "\t";
				echo '<p id="'.$prefix.'p_field_'.$field_id.'">';
				echo "\n\t";
	
				switch($type) {
					case "picture-url":
					case "password":
					case "text":
						$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
						$obj_class = ' class="'.$input_class.'"';
						$obj_name = ' name="'.$input_name.'"';
						
						if ($type == "picture-url")
							$obj_type = ' type="text"';
						else
							$obj_type = ' type="'.$type.'"';
						
						$obj_value = ' value="'.$value.'"';
						$obj_value2 = "";
						$obj_checked = "";
						$obj_tag = "input";
						$obj_closing_tag = false;
						break;
						
					case "dropdown":
						$ret = cimy_dropDownOptions($label, $value);
						$label = $ret['label'];
						$html = $ret['html'];
						
						$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
						$obj_class = ' class="'.$input_class.'"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = '';
						$obj_value = '';
						$obj_value2 = $html;
						$obj_checked = "";
						$obj_tag = "select";
						$obj_closing_tag = true;
						break;
						
					case "textarea":
						$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
						$obj_class = ' class="'.$input_class.'"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = "";
						$obj_value = "";
						$obj_value2 = $value;
						$obj_checked = "";
						$obj_tag = "textarea";
						$obj_closing_tag = true;
						break;
						
					case "textarea-rich":
						if ($tiny_mce_objects == "")
							$tiny_mce_objects = $fields_name_prefix.$field_id;
						else
							$tiny_mce_objects .= ",".$fields_name_prefix.$field_id;

						$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
						$obj_class = ' class="'.$input_class.'"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = "";
						$obj_value = "";
						$obj_value2 = $value;
						$obj_checked = "";
						$obj_tag = "textarea";
						$obj_closing_tag = true;
						break;

					case "checkbox":
						$obj_label = '<label class="cimy_uef_label_checkbox" for="'.$unique_id.'"> '.$label.'</label><br />';
						$obj_class = ' class="cimy_uef_checkbox"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = ' type="'.$type.'"';
						$obj_value = ' value="1"';
						$obj_value2 = "";
						$value == "1" ? $obj_checked = ' checked="checked"' : $obj_checked = '';
						$obj_tag = "input";
						$obj_closing_tag = false;
						break;
		
					case "radio":
						$obj_label = '<label class="cimy_uef_label_radio" for="'.$unique_id.'"> '.$label.'</label>';
						$obj_class = ' class="cimy_uef_radio"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = ' type="'.$type.'"';
						$obj_value = ' value="'.$field_id.'"';
						$obj_value2 = "";
						$obj_tag = "input";
						$obj_closing_tag = false;
	
						// do not check if another check was done
						if ((intval($value) == intval($field_id)) && (!in_array($name, $radio_checked))) {
							$obj_checked = ' checked="checked"';
							$radio_checked += array($name => true);
						}
						else {
							$obj_checked = '';
						}
						
						break;

					case "avatar":
					case "picture":
						// javascript will be added later
						$upload_image_function = true;
						$obj_label = '<label for="'.$unique_id.'">'.$label.' </label>';
						$obj_class = ' class="cimy_uef_picture"';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = ' type="file"';
						$obj_value = ' value="'.$value.'"';
						$obj_value2 = "";
						$obj_checked = ' onchange="uploadPic(\'registerform\', \''.$unique_id.'\', \''.__("Please upload an image with one of the following extensions", $cimy_uef_domain).'\');"';
						$obj_tag = "input";
						$obj_closing_tag = false;
						break;
						
					case "registration-date":
						$obj_label = '';
						$obj_class = '';
						$obj_name = ' name="'.$input_name.'"';
						$obj_type = ' type="hidden"';
						$obj_value = ' value="'.$value.'"';
						$obj_value2 = "";
						$obj_checked = "";
						$obj_tag = "input";
						$obj_closing_tag = false;
						break;
				}
	
				$obj_id = ' id="'.$unique_id.'"';

				// tabindex not used in MU, dropping...
				if ($is_mu)
					$obj_tabindex = "";
				else {
					$obj_tabindex = ' tabindex="'.strval($tabindex).'"';
					$tabindex++;
				}

				$obj_maxlen = "";
	
				if ((in_array($type, $rule_maxlen_needed)) && (!in_array($type, $cimy_uef_file_types))) {
					if (isset($rules['max_length'])) {
						$obj_maxlen = ' maxlength="'.$rules['max_length'].'"';
					} else if (isset($rules['exact_length'])) {
						$obj_maxlen = ' maxlength="'.$rules['exact_length'].'"';
					}
				}
				
				if (in_array($type, $cimy_uef_textarea_types))
					$obj_rowscols = ' rows="3" cols="25"';
				else
					$obj_rowscols = '';
		
				echo "\t";
				$form_object = '<'.$obj_tag.$obj_type.$obj_name.$obj_id.$obj_class.$obj_value.$obj_checked.$obj_maxlen.$obj_rowscols.$obj_tabindex;
				
				if ($obj_closing_tag)
					$form_object.= ">".$obj_value2."</".$obj_tag.">";
				else
					$form_object.= " />";
	
				if (($type != "radio") && ($type != "checkbox"))
					echo $obj_label;

				if ($is_mu) {
					if ( $errmsg = $errors->get_error_message($unique_id) ) {
						echo '<p class="error">'.$errmsg.'</p>';
					}
				}

				// write to the html the form object built
				echo $form_object;
	
				if (!(($type != "radio") && ($type != "checkbox")))
					echo $obj_label;

				echo "\n\t</p>\n";

				if ((($type == "textarea-rich") || (in_array($type, $cimy_uef_file_types))) && ($wp_27))
					echo "\t<br />\n";
			}
		}
	}
	
	if ($tiny_mce_objects != "") {
		$mce_skin = "";
		
		require_once($cuef_plugin_dir.'/cimy_uef_init_mce.php');
	}

	if ($upload_image_function)
		wp_print_scripts("cimy_uef_upload_pic");

	echo $end_cimy_uef_comment;
}

?>