<?php
/*
Plugin Name: Envialia Carrier for Woocommerce
Version: 2.2
Plugin URI: http://wordpress.org/plugins/envialia-carrier-for-woocomerce/
Description: Calcula automáticamente el importe del envío por peso o valor de la compra mediante los ficheros csv de Envialia, permitiendo elegir el servicio más conveniente (24h, 72h, internacional...) y también permite tramitar la recogida de los paquetes por Envialia con solo un click, generando las etiquetas del paquete, el número de traking, etc.
Author URI: http://www.netsis.es/
Author: Netsis Estudio
License: GPL2
*/

/////////////// Definimos las variables globales ///////////////////////

$e_comm = null;
$uploads = wp_upload_dir();

define (ENVIALIA_PLUGIN_ID, 'envialia-carrier-for-woocomerce');
define (ENVIALIA_PLUGIN_TABLE, 'envialia_carrier');
define (ENVIALIA_PLUGIN_OPTIONS, 'envialia_carrier_settings');
define (ENVIALIA_UPLOADS, $uploads['basedir'].'/envialia-carrier-for-woocomerce/');
define (ENVIALIA_UPLOADS_URL, $uploads['baseurl'].'/envialia-carrier-for-woocomerce/');

$settings = array(
				'url',
				'codigo_agencia',
				'codigo_cliente',
				'password_cliente',
				'cli_nombre',
				'cli_direccion',
				'cli_poblacion',
				'cli_codpostal',
				'cli_provincia',

				'servicio_E24',
				'servicio_E72',
				'servicio_EEU',
				'servicio_EWW',
				'mostrar_economico',

				'precio_fijo',
				'calcular_precio',
				'impuesto',
				'coste_fijo',

				'paises_iva',
				'ruta_tarifas'
			);

$defaults = array(
				'servicio_E24' => 1,
				'servicio_E72' => 1,
				'servicio_EEU' => 1,
				'servicio_EWW' => 1,
				'mostrar_economico' => 0,

				'calcular_precio' => 0, // 0 peso | 1 importe
				'impuesto' => 21,
				'coste_fijo' => 0,

				'paises_iva' => 'AT, BE, BG, CC, CY, CZ, DK, EE, FI, FR, DE, GR, HU, IE, IT, LV, LT, LU, MT, NL, PL, PT, RO, SK, SI, ES, SE, GB',
				'ruta_tarifas' => dirname(__FILE__).'/tarifas'
			);

/////////////// Instalar el Plugin ///////////////////////

function installEnvialiaCarrier(){
	global $wpdb;

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;

	if ($wpdb->get_var("SHOW TABLES LIKE $tabla") === $tabla){
		//Actualización tabla de envíos
	}else{
		// Crear tabla de envíos
		$wpdb->query("CREATE TABLE $tabla (
				        id_envio int(11) NOT NULL AUTO_INCREMENT,
				        id_envio_order int(11) NOT NULL,
				        codigo_envio varchar(50) NOT NULL,
				        url_track varchar(255) NOT NULL,
				        num_albaran varchar(100) NOT NULL,
				        codigo_barras varchar(255) NULL,
				        fecha datetime NOT NULL,
				        PRIMARY KEY (`id_envio`)
					)");

		// Crear directorio de uploads
		if (!is_dir(ENVIALIA_UPLOADS)) mkdir(ENVIALIA_UPLOADS);

		// Crear estado de enviado
		if (!term_exists('sended', 'shop_order_status')) wp_insert_term('sended', 'shop_order_status');
	}
}

/////////////// Desintalar el Plugin ///////////////////////

function uninstallEnvialiaCarrier(){
	global $wpdb, $settings;

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
	$wpdb->query("DROP TABLE $tabla");

	if (is_dir(ENVIALIA_UPLOADS)) rmdir(ENVIALIA_UPLOADS);

	foreach ($settings as $name) unregister_setting(ENVIALIA_PLUGIN_OPTIONS, $name);

    $term = get_term_by('name', 'sended', 'shop_order_status' );
    if ($term) wp_delete_term($term->term_id, 'shop_order_status');
}

/////////////// Creando el menú ///////////////////////

function envialiaCarrierMenu(){
	add_menu_page('Servicio de paquetería Envialia', 'Envialia', 'publish_pages', 'envialia-carrier-panel', 'envialiaCarrierPanel', plugins_url('envialia-carrier-for-woocomerce/img/envialia-icon.png'), 66);
	add_submenu_page('envialia-carrier-panel', 'Configuración de envios', 'Configuración', 'manage_options', 'envialia-carrier-options', 'envialiaCarrierSettingsPage');
	add_submenu_page('envialia-carrier-panel', 'Estado del plugin', 'Estado', 'manage_options', 'envialia-carrier-status', 'envialiaCarrierStatusPage');
}

/////////////// Acciones de Envialia en pedidos ///////////////////////

function envialiaOrderActions($actions, $order){
	global $e_comm;

	$is_sended = get_post_meta($order->id, '_sended');

	if ($e_comm->login() && $order->status == 'completed' && !$is_sended){

		$actions['send'] = array(
			'url' 		=> admin_url('admin.php?page=envialia-carrier-panel&order=' . $order->id . '&action=send'),
			'name' 		=> 'Enviar',
			'action' 	=> 'send'
		);
	}

	return $actions;
}

/////////////// Acciones de Envialia en mi cuenta ///////////////////////

function envialiaMyAccountActions($actions, $order){
	global $wpdb;

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
	$traking_url = $wpdb->get_var("SELECT url_track FROM $tabla WHERE id_envio_order = '{$order->id}'");
	$is_sended = get_post_meta($order->id, '_sended');

	if ($is_sended && $order->status == 'sended' && strlen($traking_url)>0){
		$actions[] = array(
			'url' 		=> $traking_url,
			'name' 		=> 'Traking'
		);
	}

	return $actions;
}

/////////////// Registrar los estilos CSS ///////////////////////

function registrarEstilosEnvialiaCarrier() {
	wp_register_style('envialia-carrier', plugins_url('envialia-carrier-for-woocomerce/estilos.css'));
	wp_enqueue_style('envialia-carrier');
}

/////////////// Páginas del plugin ///////////////////////

function envialiaCarrierSettingsPage(){
	cargarPagina('configuracion');
}

function envialiaCarrierPanel(){
	cargarPagina('envios');
}

function envialiaCarrierStatusPage(){
	cargarPagina('status');
}

/////////////// Acciones extra del plugin ///////////////////////

function envialiaPluginExtraActionLinks($links) {
   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=envialia-carrier-options') .'">Settings</a>';
   $links[] = '<a href="http://netsis.es/downloads/envialia-woocommerce-plugin" target="_blank">Premium</a>';
   return $links;
}

/////////////// Registrando las funciones ///////////////////////

register_activation_hook(__FILE__, 'installEnvialiaCarrier');
register_uninstall_hook(__FILE__, 'uninstallEnvialiaCarrier');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'envialiaPluginExtraActionLinks');
add_action('admin_enqueue_scripts', 'registrarEstilosEnvialiaCarrier');
add_action('woocommerce_admin_order_actions', 'envialiaOrderActions', 20, 2);
add_action('woocommerce_my_account_my_orders_actions', 'envialiaMyAccountActions', 20, 2);
add_filter('woocommerce_shipping_methods', 'addEnvialiaShippingMethods');
add_action('woocommerce_shipping_init', 'envialiaShippingMethod');
add_action('admin_menu', 'envialiaCarrierMenu');
add_action('admin_init', 'envialiaCarrierSettings');
add_action('init', 'envialiaCarrierInit');

/////////////// Configuracion del plugin ///////////////////////

function envialiaCarrierSettings(){
	global $settings;
	foreach ($settings as $name) register_setting(ENVIALIA_PLUGIN_OPTIONS, $name);
}

/////////////// Lanzar el plugin ///////////////////////

function envialiaCarrierInit(){
	global $e_comm;

	if(!session_id()) session_start();
	require dirname(__FILE__).'/funciones.php';
	require dirname(__FILE__).'/paginator.class.php';
	$e_comm = new envialia();
}

/////////////// Obtiene la información del plugin ///////////////////////

function obtenerInformacionPlugin($id, $nombre) {
	$argumentos = (object) array('slug' => $id);
	$consulta = array('action' => 'plugin_information', 'timeout' => 15, 'request' => serialize($argumentos));
	$respuesta = get_transient($id);

	if (!$respuesta){
		$respuesta = wp_remote_post('http://api.wordpress.org/plugins/info/1.0/', array('body' => $consulta));
		set_transient($nombre, $respuesta, 24 * HOUR_IN_SECONDS);
	}

	$vars = unserialize($respuesta['body']);
	if (is_object($vars)) $plugin = get_object_vars(unserialize($respuesta['body']));
	if (!isset($plugin['rating'])) $plugin['rating'] = 100;

	return $plugin;
}

?>