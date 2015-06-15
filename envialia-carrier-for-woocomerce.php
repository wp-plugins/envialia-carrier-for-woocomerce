<?php
/*
Plugin Name: Envialia Carrier for Woocommerce
Version: 2.8
Plugin URI: http://wordpress.org/plugins/envialia-carrier-for-woocomerce/
Description: Calcula automáticamente el importe del envío por peso o valor de la compra mediante los ficheros csv de Envialia, permitiendo elegir el servicio más conveniente (24h, 72h, internacional...) y también permite tramitar la recogida de los paquetes por Envialia con solo un click, generando las etiquetas del paquete, el número de traking, etc.
Author URI: https://www.netsis.es/
Author: Netsis Estudio
License: GPL2
*/

/////////////// Cargamos la configuración ///////////////////////

$e_comm = null;
require_once dirname(__FILE__).'/config.php';

/////////////// Instalar el Plugin ///////////////////////

function installEnvialiaCarrier(){
	global $wpdb;

	if (!current_user_can('activate_plugins')) return;

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;

	if ($wpdb->get_var("SHOW TABLES LIKE `$tabla`") === $tabla){
		//Actualización tabla de envíos
	}else{
		// Crear tabla de envíos
		$wpdb->query("CREATE TABLE `$tabla` (
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

		// Crear estado de enviado (obsoleto desde WC 2.1)
		if (!term_exists('sended', 'shop_order_status')) wp_insert_term('sended', 'shop_order_status');
	}
}

/////////////// Creando el menú ///////////////////////

function envialiaCarrierMenu(){
	add_menu_page('Servicio de paquetería Envialia', 'Envialia', 'publish_pages', 'envialia-carrier-panel', 'envialiaCarrierPanel', ENVIALIA_PLUGIN_URI.'/img/envialia-icon.png', 66);
	add_submenu_page('envialia-carrier-panel', 'Configuración de envios', 'Configuración', 'manage_options', 'envialia-carrier-options', 'envialiaCarrierSettingsPage');
	add_submenu_page('envialia-carrier-panel', 'Simulador de checkout', 'Simulador', 'manage_options', 'envialia-carrier-simulator', 'envialiaCarrierSimulatorPage');
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

/////////////// Estados de la orden de Envialia ///////////////////////

function registerSendedShipmentOrderStatus(){
    register_post_status('wc-sended', array(
        'label'                     => 'Enviado',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Enviados <span class="count">(%s)</span>', 'Enviados <span class="count">(%s)</span>')
    ));
}

function addSendedShipmentToOrderStatuses($order_statuses) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ($order_statuses as $key => $status) {

        $new_order_statuses[ $key ] = $status;

        if ('wc-processing' === $key){
            $new_order_statuses['wc-sended'] = 'Enviado';
        }
    }

    return $new_order_statuses;
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
	wp_register_style('envialia-carrier', ENVIALIA_PLUGIN_URI.'/estilos.css');
	wp_enqueue_style('envialia-carrier');
}

/////////////// Páginas del plugin ///////////////////////

function envialiaCarrierPanel(){
	global $wpdb, $e_comm, $envialia_carrier_servicios;

	if (isset($_GET['action'])){
		switch($_GET['action']){
			case 'send':
				$shipment_number = $wpdb->get_var("SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'shipping' AND order_id = '$idOrden'");
				$servicio_activo = (isset($_POST['servicio_elegido']))? $_POST['servicio_elegido']:$wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = 'method_id' AND order_item_id = '$shipment_number'");

				if (!empty($servicio_activo) && array_key_exists($servicio_activo, $envialia_carrier_servicios)){
					$e_comm->realizaEnvio($_GET['order'], $servicio_activo);
					break;
				}
			case 'setService':
				$pais = implode(get_post_meta(102, '_shipping_first_name'));
				cargarPagina('servicioEnvio');
				return;
			case 'label':
				$e_comm->descargarEtiqueta($_GET['albaran']);
				break;
			case 'delete':
				$e_comm->cancelaEnvio($_GET['order'], $_GET['albaran']);
				break;
			default:
		}
	}

	cargarPagina('envios');
}

function envialiaCarrierSettingsPage(){
	cargarPagina('configuracion');
}

function envialiaCarrierSimulatorPage(){
	cargarPagina('simulador');
}

function envialiaCarrierStatusPage(){
	cargarPagina('estadoPlugin');
}

/////////////// Acciones extra del plugin ///////////////////////

function envialiaPluginExtraActionLinks($links) {
   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=envialia-carrier-options') .'">Settings</a>';
   ### $links[] = '<a href="https://netsis.es/downloads/envialia-woocommerce-plugin/" target="_blank">Premium</a>';
   return $links;
}

/////////////// Registrando las funciones ///////////////////////

require dirname(__FILE__).'/funciones.php';
require dirname(__FILE__).'/paginator.class.php';

register_activation_hook(__FILE__, 'installEnvialiaCarrier');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'envialiaPluginExtraActionLinks');
add_action('admin_enqueue_scripts', 'registrarEstilosEnvialiaCarrier');
add_action('woocommerce_admin_order_actions', 'envialiaOrderActions', 20, 2);
add_action('woocommerce_my_account_my_orders_actions', 'envialiaMyAccountActions', 20, 2);
add_filter('woocommerce_shipping_methods', 'addEnvialiaShippingMethods');
add_action('woocommerce_shipping_init', 'envialiaShippingMethod');
add_action('admin_menu', 'envialiaCarrierMenu');
add_action('admin_init', 'envialiaCarrierSettings');
add_filter('wc_order_statuses', 'addSendedShipmentToOrderStatuses');
add_filter('init', 'registerSendedShipmentOrderStatus');
add_action('init', 'envialiaCarrierInit');

/////////////// Configuracion del plugin ///////////////////////

function envialiaCarrierSettings(){
	global $envialia_carrier_settings;
	foreach ($envialia_carrier_settings as $name) register_setting(ENVIALIA_PLUGIN_OPTIONS, $name);
}

/////////////// Lanzar el plugin ///////////////////////

function envialiaCarrierInit(){
	global $e_comm;

	if(!session_id()) session_start();
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