<?php

function cimy_plugin_install () {
	// for WP >= 2.5 when adding a global here need to be added also to main global
	global $wpdb, $old_wpdb_data_table, $wpdb_data_table, $old_wpdb_fields_table, $wpdb_fields_table, $wpdb_wp_fields_table, $cimy_uef_options, $cimy_uef_version, $cuef_upload_path, $cimy_uef_domain;

	if (!cimy_check_admin('activate_plugins'))
		return;
	
	$force_update = false;
	
	if (!($options = cimy_get_options()))
		cimy_manage_db('new_options');
	else
		$force_update = true;

	$charset_collate = "";
	
	// try to get proper charset and collate
	if ( $wpdb->supports_collation() ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	if ($force_update) {
		if (version_compare($options['version'], "0.9.1", "<=") === true) {
			unset($options['show_buggy_ie_warning']);
		}

		if (version_compare($options['version'], "1.0.0-beta1", "<=") === true) {
			$sql = "RENAME TABLE ".$old_wpdb_fields_table." TO ".$wpdb_fields_table;
			$wpdb->query($sql);
			
			$sql = "RENAME TABLE ".$old_wpdb_data_table." TO ".$wpdb_data_table;
			$wpdb->query($sql);

			$options['wp_hidden_fields'] = array();
			
			// convert all html entity to normal chars
			$sql = "SELECT * FROM ".$wpdb_fields_table;
			$fields = $wpdb->get_results($sql, ARRAY_A);
			
			foreach ($fields as $field) {
				$id = $field['ID'];
				$name = $wpdb->escape(html_entity_decode($field['NAME'], ENT_QUOTES, "UTF-8"));
				$label = $wpdb->escape(html_entity_decode($field['LABEL'], ENT_QUOTES, "UTF-8"));
				$desc = $wpdb->escape(html_entity_decode($field['DESCRIPTION'], ENT_QUOTES, "UTF-8"));
				$value = $wpdb->escape(html_entity_decode($field['VALUE'], ENT_QUOTES, "UTF-8"));
				
				$rules = unserialize($field['RULES']);
				$rules['equal_to'] = html_entity_decode($rules['equal_to'], ENT_QUOTES, "UTF-8");
				$rules = $wpdb->escape(serialize($rules));
				
				$sql = "UPDATE ".$wpdb_fields_table." SET name='".$name."', value='".$value."', description='".$desc."', label='".$label."', rules='".$rules."' WHERE ID=".$id;
				
				$wpdb->query($sql);
			}
		}

		if (version_compare($options['version'], "1.1.0-rc1", "<=") === true) {
			$sql = "SELECT ID FROM ".$wpdb_fields_table." WHERE TYPE='picture'";
			$f_pictures = $wpdb->get_results($sql, ARRAY_A);
			
			if (isset($f_pictures)) {
				if ($f_pictures != NULL) {
					foreach ($f_pictures as $f_picture) {
						$sql = "SELECT VALUE FROM ".$wpdb_data_table." WHERE FIELD_ID=".$f_picture['ID'];
						$p_filenames = $wpdb->get_results($sql, ARRAY_A);

						if (isset($p_filenames)) {
							if ($p_filenames != NULL) {
								foreach ($p_filenames as $p_filename) {
									$path_pieces = explode("/", $p_filename['VALUE']);
									$p_filename = basename($p_filename['VALUE']);
									$user_login = array_slice($path_pieces, -2, 1);
									
									$p_oldfilename_t = $cuef_upload_path.$user_login[0]."/".cimy_get_thumb_path($p_filename, true);
									$p_newfilename_t = $cuef_upload_path.$user_login[0]."/".cimy_get_thumb_path($p_filename, false);
									
									if (is_file($p_oldfilename_t))
										rename($p_oldfilename_t, $p_newfilename_t);
								}
							}
						}
					}
				}
			}
		}

		if (version_compare($options['version'], "1.1.0", "<=") === true) {
			if ($charset_collate != "") {
				$sql = "ALTER TABLE ".$wpdb_fields_table.$charset_collate;
				$wpdb->query($sql);
				
				$sql = "ALTER TABLE ".$wpdb_wp_fields_table.$charset_collate;
				$wpdb->query($sql);
				
				$sql = "ALTER TABLE ".$wpdb_data_table.$charset_collate;
				$wpdb->query($sql);
			}
		}

		if (version_compare($options['version'], "1.3.0-beta1", "<=") === true) {
			$options["users_per_page"] = 50;
		}

		if (version_compare($options['version'], "1.3.0-beta2", "<=") === true) {
			unset($options["disable_cimy_fieldvalue"]);
		}

		if (version_compare($options['version'], "1.3.1", "<=") === true) {
			$options["extra_fields_title"] = __("Extra Fields", $cimy_uef_domain);

			// Added again since after cleanup DB migration code in v1.3.0-beta2 was buggy!
			if (isset($options["disable_cimy_fieldvalue"]))
				unset($options["disable_cimy_fieldvalue"]);

			if (!isset($options["users_per_page"]))
				$options["users_per_page"] = 50;
		}

		if (version_compare($options['version'], "1.4.0-beta2", "<=") === true) {
			unset($options['items_per_fieldset']);

			$sql = "ALTER TABLE ".$wpdb_fields_table." ADD COLUMN FIELDSET bigint(20) NOT NULL DEFAULT 0 AFTER F_ORDER";
			$wpdb->query($sql);
		}

		$options['version'] = $cimy_uef_version;

		cimy_set_options($options);
	}
	
	if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_wp_fields_table'") != $wpdb_wp_fields_table) {

		$sql = "CREATE TABLE ".$wpdb_wp_fields_table." (ID bigint(20) NOT NULL AUTO_INCREMENT, F_ORDER bigint(20) NOT NULL, NAME varchar(20), LABEL TEXT, DESCRIPTION TEXT, TYPE varchar(20), RULES TEXT, VALUE TEXT, PRIMARY KEY (ID), INDEX F_ORDER (F_ORDER), INDEX NAME (NAME))".$charset_collate.";";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}

	if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_data_table'") != $wpdb_data_table) {

		$sql = "CREATE TABLE ".$wpdb_data_table." (ID bigint(20) NOT NULL AUTO_INCREMENT, USER_ID bigint(20) NOT NULL, FIELD_ID bigint(20) NOT NULL, VALUE TEXT NOT NULL, PRIMARY KEY (ID), INDEX USER_ID (USER_ID), INDEX FIELD_ID (FIELD_ID))".$charset_collate.";";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}

	if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_fields_table'") != $wpdb_fields_table) {

		$sql = "CREATE TABLE ".$wpdb_fields_table." (ID bigint(20) NOT NULL AUTO_INCREMENT, F_ORDER bigint(20) NOT NULL, FIELDSET bigint(20) NOT NULL DEFAULT 0, NAME varchar(20), LABEL TEXT, DESCRIPTION TEXT, TYPE varchar(20), RULES TEXT, VALUE TEXT, PRIMARY KEY (ID), INDEX F_ORDER (F_ORDER), INDEX NAME (NAME))".$charset_collate.";";

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
}

function cimy_manage_db($command) {
	global $wpdb, $wpdb_data_table, $wpdb_wp_fields_table, $wpdb_fields_table, $cimy_uef_options, $cimy_uef_version, $is_mu, $cimy_uef_domain;
	
	if (!cimy_check_admin('activate_plugins'))
		return;

	$options = array(
		'extra_fields_title' => __("Extra Fields", $cimy_uef_domain),
		'users_per_page' => 50,
		'aue_hidden_fields' => array('website', 'posts', 'email'),
		'wp_hidden_fields' => array(),
		'fieldset_title' => '',
	);

	switch ($command) {
		case 'new_options':
			$options['version'] = $cimy_uef_version;
			
			cimy_set_options($options);
			break;

		case 'default_options':
			$old_options = cimy_get_options();
			
			if (isset($old_options['version']))
				$options['version'] = $old_options['version'];
			else
				$options['version'] = $cimy_uef_version;
			
			cimy_set_options($options);
			
			break;
			
		case 'drop_options':
			if ($is_mu)
				delete_site_option($cimy_uef_options);
			else
				delete_option($cimy_uef_options);
			
			break;
			
		case 'empty_wp_fields':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_wp_fields_table'") == $wpdb_wp_fields_table) {
				$sql = "TRUNCATE TABLE ".$wpdb_wp_fields_table;
				$wpdb->query($sql);
			}
			break;

		case 'empty_extra_fields':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_fields_table'") == $wpdb_fields_table) {
				$sql = "TRUNCATE TABLE ".$wpdb_fields_table;
				$wpdb->query($sql);
			}
			break;

		case 'empty_data':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_data_table'") == $wpdb_data_table) {
				$sql = "TRUNCATE TABLE ".$wpdb_data_table;
				$wpdb->query($sql);
			}
			break;
			
		case 'drop_wp_fields':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_wp_fields_table'") == $wpdb_wp_fields_table) {
				$sql = "DROP TABLE ".$wpdb_wp_fields_table;
				$wpdb->query($sql);
			}
			break;
			
		case 'drop_extra_fields':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_fields_table'") == $wpdb_fields_table) {
				$sql = "DROP TABLE ".$wpdb_fields_table;
				$wpdb->query($sql);
			}
			break;

		case 'drop_data':
			if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb_data_table'") == $wpdb_data_table) {
				$sql = "DROP TABLE ".$wpdb_data_table;
				$wpdb->query($sql);
			}
			break;
			
	}
}

function cimy_delete_users_info($fields_id) {
	global $wpdb, $wpdb_data_table;
	
	if (!cimy_check_admin('edit_users'))
		return;
	
	$sql = "DELETE FROM ".$wpdb_data_table." WHERE ".$fields_id;
	$wpdb->query($sql);
}

function cimy_delete_user_info($user_id) {
	global $wpdb, $wpdb_data_table, $cuef_upload_path;
	
	if (!current_user_can('edit_user', $user_id))
		return;
	
	// function to delete all files/subdirs in a path
	// taken from PHP unlink's documentation comment by torch - torchsdomain dot com @ 22-Nov-2006 09:27
	// modified by Marco Cimmino to delete correctly call recursion before so can also delete subdirs when empty
	if (!function_exists(cimy_rfr)) {
		function cimy_rfr($path, $match) {
			static $deld = 0, $dsize = 0;

			// remember that glob returns FALSE in case of error
			$dirs = glob($path."*");
			$files = glob($path.$match);

			// call recursion before so we delete files in subdirs first!
			if (is_array($dirs)) {
				foreach ($dirs as $dir) {
					if (is_dir($dir)) {
						$dir = basename($dir) . "/";
						cimy_rfr($path.$dir, $match);
					}
				}
			}

			if (is_array($files)) {
				foreach ($files as $file) {
					if (is_file($file)) {
						$dsize += filesize($file);
						unlink($file);
						$deld++;
					}
					else if (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			return "$deld files deleted with a total size of $dsize bytes";
		}
	}
	
	$sql = "DELETE FROM ".$wpdb_data_table." WHERE USER_ID=".$user_id;
	$wpdb->query($sql);
	
	$profileuser = get_user_to_edit($user_id);
	$user_login = $profileuser->user_login;
	
	$file_path = $cuef_upload_path.$user_login."/";
	
	// delete all uploaded files for that users
	cimy_rfr($file_path, "*");
	
	// delete also the subdir
	if (is_dir($file_path))
		rmdir($file_path);
}

function cimy_insert_ExtraFields_if_not_exist($user_id, $field_id) {
	global $wpdb, $wpdb_data_table;

	$sql = "SELECT ID FROM ".$wpdb_data_table." WHERE FIELD_ID=".$field_id." AND USER_ID=".$user_id;
	$exist = $wpdb->get_var($sql);

	if ($exist == NULL) {
		$sql = "INSERT INTO ".$wpdb_data_table." SET FIELD_ID=".$field_id.", USER_ID=".$user_id.", VALUE=''";
		$wpdb->query($sql);
	}
}

function cimy_get_options() {
	global $is_mu, $cimy_uef_options;

	if ($is_mu)
		$options = get_site_option($cimy_uef_options);
	else
		$options = get_option($cimy_uef_options);

	return $options;
}

function cimy_set_options($options) {
	global $is_mu, $cimy_uef_options, $cimy_uef_options_descr;

	if ($is_mu)
		update_site_option($cimy_uef_options, $options);
	else
		update_option($cimy_uef_options, $options, $cimy_uef_options_descr, "no");
}

?>
