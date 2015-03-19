<?php

//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN') || !current_user_can('activate_plugins')) exit();

global $wpdb;
require_once dirname(__FILE__).'/config.php';

function uninstallEnvialia($settings){
	global $wpdb;

	if(get_option('eliminar_datos', $defaults['eliminar_datos'])){
		$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
		$wpdb->query("DROP TABLE IF EXISTS $tabla");

		if (is_dir(ENVIALIA_UPLOADS)) rmdir(ENVIALIA_UPLOADS);

		foreach ($envialia_carrier_settings as $name) unregister_setting(ENVIALIA_PLUGIN_OPTIONS, $name);

		$term = get_term_by('name', 'sended', 'shop_order_status');
		if ($term) wp_delete_term($term->term_id, 'shop_order_status');
	}
}

if (is_multisite()){

    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    $original_blog_id = get_current_blog_id();

    foreach ($blog_ids as $blog_id){
        switch_to_blog($blog_id);
		uninstallEnvialia($settings);
    }

    switch_to_blog($original_blog_id);

}else uninstallEnvialia($settings);

/* FIN DE DESINSTALACIÓN */