<?php
/*
Plugin Name: Envialia Carrier for Woocomerce
Version: 1.0
Plugin URI: http://wordpress.org/plugins/envialia-carrier/
Description: Add functions and control panel to WooCommerce for Envalia carrier
Author URI: http://www.netsis.es/
Author: PABLO LUACES
License: GPL2
*/

/////////////// Definimos las variables globales ///////////////////////

$e_comm = null;
$settings = array('url', 'codigo_agencia', 'codigo_cliente', 'password_cliente', 'servicio_activo', 'cli_nombre', 'cli_direccion', 'cli_poblacion', 'cli_codpostal', 'cli_provincia');
$uploads = wp_upload_dir();
define ('ENVIALIA_PLUGIN_TABLE', 'envialia_carrier');
define ('ENVIALIA_PLUGIN_OPTIONS', 'envialia_carrier_settings');
define ('ENVIALIA_UPLOADS', $uploads['basedir'].'/envialia-carrier/');
define ('ENVIALIA_UPLOADS_URL', $uploads['baseurl'].'/envialia-carrier/');

/////////////// Activando el Plugin ///////////////////////

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
	}
}

/////////////// Desintalando el Plugin ///////////////////////

function uninstallEnvialiaCarrier(){
	global $wpdb, $settings;

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
	$wpdb->query("DROP TABLE $tabla");

	if (is_dir(ENVIALIA_UPLOADS)) rmdir(ENVIALIA_UPLOADS);

	foreach ($settings as $name) unregister_setting(ENVIALIA_PLUGIN_OPTIONS, $name);
}

/////////////// Configuracion del plugin ///////////////////////

function envialiaCarrierSettings(){
	global $settings, $e_comm;
	foreach ($settings as $name) register_setting(ENVIALIA_PLUGIN_OPTIONS, $name);

	if(!session_id()) session_start();

	$e_comm = new envialia();
}

/////////////// Creando el menú ///////////////////////

function envialiaCarrierMenu(){
	add_menu_page('Servicio de paquetería Envialia', 'Envialia', 'publish_pages', 'envialia-carrier-panel', 'envialiaCarrierPanel', plugins_url('envialia-carrier/img/envialia-icon.png'), 66);
	add_submenu_page('envialia-carrier-panel', 'Configuración de envios', 'Configuración', 'manage_options', __FILE__, 'envialiaCarrierSettingsPage');
}

/////////////// Acciones de Envialia en pedidos ///////////////////////

function envialiaOrderActions($actions, $order){

	$is_sended = get_post_meta($order->id, '_sended');

	if ($order->status == 'completed' && !$is_sended){

		$actions['envialia'] = array(
			'url' 		=> admin_url('admin.php?page=envialia-carrier-panel&order=' . $order->id . '&action=send'),
			'name' 		=> 'Enviar',
			'action' 	=> "send"
		);

	}

	return $actions;
}

/////////////// Registrando las funciones ///////////////////////
register_activation_hook(__FILE__, 'installEnvialiaCarrier');
register_uninstall_hook(__FILE__, 'uninstallEnvialiaCarrier');
add_action('woocommerce_admin_order_actions', 'envialiaOrderActions', 20, 2);
add_action('admin_menu', 'envialiaCarrierMenu');
add_action('admin_init', 'envialiaCarrierSettings');

function register_plugin_styles_envialia() {
	wp_register_style('envialia-carrier', plugins_url('envialia-carrier/estilos.css'));
	wp_enqueue_style('envialia-carrier');
}
add_action( 'admin_enqueue_scripts', 'register_plugin_styles_envialia' );

require dirname(__FILE__).'/funciones.php';

/////////////// Header HTML para E-mail ///////////////////////

function set_html_content_type() {
	return 'text/html';
}

/////////////// Carga el panel de extras ///////////////////////

function extrasPanel(){
	return '<div id="envialiaExtras">
				<p class="justify">Si te parece útil, por favor, considera hacer una donación. Hemos invertido mucho tiempo y esfuerzo en el.</p>

				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="D2VA3BETM3RYQ">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
				</form>

			</div>';
}

/////////////// Página de configuración ///////////////////////

function envialiaCarrierSettingsPage(){
	global $e_comm;
	if ($e_comm->is_logged()) $e_comm->printMessage('El plugin de ENVIALIA está en línea', 1);
	else $e_comm->printMessage('El plugin de ENVIALIA no estÁ funcionando, revise los datos de conexión', 0);
	?>
	<div class="wrap">

		<div class="eHead">
			<img src="<?php echo plugins_url('envialia-carrier/img/envialia.png') ?>" alt="Envialia Logo" class="envialiaLogo"/>
			<h2>Configuración de Envialia</h2>
			<a href="http://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo plugins_url('envialia-carrier/img/ne-logo.png') ?>" /></a>
		</div>

		<?php echo extrasPanel() ?>

		<form method="post" action="options.php" id="envOptions">
		    <?php settings_fields(ENVIALIA_PLUGIN_OPTIONS); ?>
		    <?php do_settings_sections(ENVIALIA_PLUGIN_OPTIONS); ?>
		    <table class="form-table">
			    <tr valign="top"><th scope="row">Servidor API ENVIALIA, solicítalo en tu centro de servicio ENVIALIA . 902400909</th><td><input type="text" name="url" maxlength="50" class="big" value="<?php echo get_option('url'); ?>" /></td></tr>
			    <tr valign="top"><th scope="row" style="width: 50%">Código de centro de servicio ENVIALIA</th><td><input type="text" name="codigo_agencia" size="6" maxlength="6" value="<?php echo get_option('codigo_agencia'); ?>" /></td></tr>
			    <tr valign="top"><th scope="row">Código de cuenta ENVIALIA</th><td><input type="text" name="codigo_cliente" size="5" maxlength="5" value="<?php echo get_option('codigo_cliente'); ?>" /></td></tr>
			    <tr valign="top"><th scope="row">Password de cuenta ENVIALIA</th><td><input type="text" name="password_cliente" size="15" maxlength="15" value="<?php echo get_option('password_cliente'); ?>" /></td></tr>

			    <tr><td colspan="2"><hr/></td></tr>

				<tr valign="top"><th scope="row">Servicio activo</th><td><select name="servicio_activo"><option <?php if(!get_option('servicio_activo')) echo "selected=\"selected\"" ?> value="0"> - elija un servicio - </option><option <?php if(get_option('servicio_activo')=="E24") echo "selected=\"selected\"" ?> value="E24">E-COMM 24H</option><option <?php if(get_option('servicio_activo')=="E72") echo "selected=\"selected\"" ?> value="E72">E-COMM 72H</option><option <?php if(get_option('servicio_activo')=="EEU") echo "selected=\"selected\"" ?> value="EEU">E-COMM EUROPE EXPRESS</option><option <?php if(get_option('servicio_activo')=="EWW") echo "selected=\"selected\"" ?> value="EWW">E-COMM WORLDWIDE</option></select></td></tr>

				<tr><td colspan="2"><hr/></td></tr>

				<tr valign="top"><th scope="row">Su nombre completo o empresa</th><td><input type="text" name="cli_nombre" maxlength="80" value="<?php echo get_option('cli_nombre'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Dirección</th><td><input type="text" name="cli_direccion" maxlength="200" value="<?php echo get_option('cli_direccion'); ?>" class="big" /></td></tr>
				<tr valign="top"><th scope="row">Población</th><td><input type="text" name="cli_poblacion" maxlength="50" value="<?php echo get_option('cli_poblacion'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Código Postal</th><td><input type="text" name="cli_codpostal" maxlength="5" value="<?php echo get_option('cli_codpostal'); ?>" /></td></tr>
		    </table>

		    <?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/////////////// Página de envíos ///////////////////////

function envialiaCarrierPanel(){
	global $wpdb, $e_comm;

	if (isset($_GET['action'])){
		switch($_GET['action']){
			case 'send':
				$e_comm->realizaEnvio($_GET['order']);
				break;
			case 'label':
				$e_comm->descargarEtiqueta($_GET['albaran']);
				break;
			case 'delete':
				$e_comm->cancelaEnvio($_GET['order'], $_GET['albaran']);
				break;
			default:
		}
	}

	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
	$select = $wpdb->get_results("SELECT *, DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fechaEsp FROM $tabla ORDER BY id_envio DESC");
	$num = count($select);

	?>
	<div class="wrap">

		<div class="eHead">
			<img src="<?php echo plugins_url('envialia-carrier/img/envialia.png') ?>" alt="Envialia Logo" class="envialiaLogo" />
			<h2>Administración de envíos</h2>
			<a href="http://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo plugins_url('envialia-carrier/img/ne-logo.png') ?>" /></a>
		</div>

		<table class="envialia">
			<tr><th>Pedido</th><th>Fecha envío</th><th>Código Envialia</th><th>AlbarÁn</th><th>Opciones</th></tr>
			<?php
				foreach($select as $result){
					$rute = ENVIALIA_UPLOADS.$result->num_albaran.'.pdf';
					$linkLabel = (file_exists($rute))? $result->codigo_barras:admin_url('admin.php?page=envialia-carrier-panel&albaran='.$result->num_albaran.'&action=label');

					echo '<tr><td><a href="'.admin_url('post.php?post='.$result->id_envio_order.'&action=edit').'" title="Ver pedido">#'.$result->id_envio_order.'</a></td><td>'.$result->fechaEsp.'</td><td>'.$result->codigo_envio.'</td><td>'.$result->num_albaran.'</td><td>
							<a href="'.$linkLabel.'" target="_blank" title="Etiquetas" class="envAction"><img src="'.plugins_url('envialia-carrier/img/sticker.png').'" /></a>
							<a href="'.$result->url_track.'" target="_blank" title="Tracking" class="envAction"><img src="'.plugins_url('envialia-carrier/img/info.png').'" /></a>
							<a href="'.admin_url('admin.php?page=envialia-carrier-panel&order='.$result->id_envio_order.'&albaran='.$result->num_albaran.'&action=delete').'" title="Cancelar" class="envAction prompt"><img src="'.plugins_url('envialia-carrier/img/cancel.png').'" /></a>
						  </td></tr>';
				}

				if (!$num) echo '<tr><td class="noresults" colspan="6" align="center">No hay envíos en curso</td></tr>';
			?>
		</table>

		<script>
			jQuery('a.prompt').click(function(e){
			    var ask=confirm('¿EstÁs seguro/a ?');
			    if(!ask) e.preventDefault();
			});
		</script>

	</div>
	<?php
}

/////////////// Funciones de Envialia ///////////////////////

if (is_admin()){
	/**** CLASE ENVIALIA QUE REALIZA LAS OPERACIONES CON EL SERVIDOR (SOLO SE CARGA EN ÁREA DE ADMINISTRACIÓN) ****/
	class envialia {
		protected $url, $agencia, $cliente, $contras, $conectado;

		function __construct() {
			$this->url = get_option('url');
			$this->agencia = get_option('codigo_agencia');
			$this->cliente = get_option('codigo_cliente');
			$this->contras = get_option('password_cliente');
			$this->conectado = (isset($_SESSION['envialia']))? true:false;
			/**** SI NO ESTA CONECTADO SE lOGUEA ****/
			if (!$this->is_logged()) $this->login();
		}

		/**** COMPRUEBA SI ESTÁ LOGUEADO ****/
		function is_logged(){
			return $this->conectado;
		}

		/**** LOGUEARSE ****/
		function login(){
			$xml = '<?xml version="1.0" encoding="utf-8"?>
						<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
							<soap:Body>
								<LoginWSService___LoginCli>
									<strCodAge>'.$this->agencia.'</strCodAge>
									<strCod>'.$this->cliente.'</strCod>
									<strPass>'.$this->contras.'</strPass>
								</LoginWSService___LoginCli>
							</soap:Body>
						</soap:Envelope>';

			$respuesta = envialiaPost($xml, $this->url);

			if ($respuesta['v1:Result'] && $respuesta['v1:strError']==0){
				$data['logueado'] = true;
				$data['tipoUsuario'] = $respuesta['v1:strTipo'];
				$data['nombreUsuario'] = $respuesta['v1:strNom'];
				$data['idSesion'] = $respuesta['v1:strSesion'];
				$data['codRegionalAgencia'] = $respuesta['v1:strCodCR'];
				$data['urlSeguimiento'] = $respuesta['v1:strURLDetSegEnv'];

				$_SESSION['envialia'] = $data;
				$this->conectado = true;

				return 1;

			}else return 0;
		}

		/**** PROCESAR UN PEDIDO ****/
		function realizaEnvio($idOrden){
			global $wpdb;

			/**** RECONECTAMOS, LOGIN EXPIRA EN 10MIN ****/
			$this->login();

			$is_sended = get_post_meta($idOrden, '_sended');
			if ($is_sended){
				$this->printMessage('Lo orden #'.$idOrden.' ya estÁ enviada!', 0);
			}else{
				$servicio = get_option('servicio_activo');

				$is_sended = get_post_meta($idOrden, '_sended');
				$cod_provincia_origen = substr(get_option('cli_codpostal'), 0, 2);
				$codpostal = implode(get_post_meta($idOrden, '_billing_postcode'));
				$cod_provincia = substr($codpostal, 0, 2);
				$num_paquetes = 1;
				$email = implode(get_post_meta($idOrden, '_billing_email'));
				$observaciones = implode(get_comments(array('number' => '2', 'post_id' => $idOrden)));
				$fecha = date("Y/m/d");
				$fechaTrack = date("d/m/Y");
				$fechaCompleta = date("Y-m-d H:i:s");

				$xml = '<?xml version="1.0" encoding="utf-8"?>
							<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
								<soap:Header>
									<ROClientIDHeader xmlns="http://tempuri.org/">
										<ID>'.$_SESSION['envialia']['idSesion'].'</ID>
									</ROClientIDHeader>
								</soap:Header>
								<soap:Body>
									<WebServService___GrabaEnvio7 xmlns="http://tempuri.org/">
										<strCodAgeCargo>'.$this->agencia.'</strCodAgeCargo>
										<strCodAgeOri>'.$this->agencia.'</strCodAgeOri>
										<dtFecha>'.$fecha.'</dtFecha>
										<strCodTipoServ>'.get_option('servicio_activo').'</strCodTipoServ>
										<strCodCli>'.$this->cliente.'</strCodCli>

										<strNomOri>'.get_option('cli_nombre').'</strNomOri>
										<strDirOri>'.get_option('cli_direccion').'</strDirOri>
										<strPobOri>'.get_option('cli_poblacion').'</strPobOri>
										<strCPOri>'.get_option('cli_codpostal').'</strCPOri>
										<strCodProOri>'.$cod_provincia_origen.'</strCodProOri>

										<strNomDes>'.implode(get_post_meta($idOrden, '_billing_first_name')).' '.implode(get_post_meta($idOrden, '_billing_last_name')).'</strNomDes>
										<strDirDes>'.implode(get_post_meta($idOrden, '_billing_address_1')).' '.implode(get_post_meta($idOrden, '_billing_address_2')).' - '.implode(get_post_meta($idOrden, '_shipping_city')).'</strDirDes>
										<strCPDes>'.$codpostal.'</strCPDes>
										<strCodProDes>'.$cod_provincia.'</strCodProDes>
										<strTlfDes>'.implode(get_post_meta($idOrden, '_billing_phone')).'</strTlfDes>

										<intPaq>'.$num_paquetes.'</intPaq>
										<strObs>'.$observaciones.'</strObs>

										<strCodPais>'.implode(get_post_meta($idOrden, '_shipping_country')).'</strCodPais>
										<strDesDirEmails>'.$email.'</strDesDirEmails>
										<boInsert>'.true.'</boInsert>
									</WebServService___GrabaEnvio7>
								</soap:Body>
							</soap:Envelope>';

				$respuesta = envialiaPost($xml, $this->url);

				if (!isset($respuesta['faultcode'])){
					if (isset($respuesta['v1:strGuidOut'])){
						/**** SI SE HA PROCESADO EL PEDIDO Y OBTENEMOS EL GUID LO GUARDAMOS Y GENERAMOS EL PDF ****/
						$cod = trim($respuesta['v1:strGuidOut'], '{}');
						$url_tracking = str_replace('{GUID}', $cod, $_SESSION['envialia']['urlSeguimiento']);
						$url_tracking = str_replace('{FECHA}', $fechaTrack, $url_tracking);
						$numAlbaran = $respuesta['v1:strAlbaranOut'];
						$pdf = ENVIALIA_UPLOADS_URL.$numAlbaran.'.pdf';

						$this->descargarEtiqueta($numAlbaran);

						$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
						$wpdb->query("INSERT INTO $tabla (`id_envio_order`, `codigo_envio`, `url_track`, `num_albaran`, `codigo_barras`, `fecha`) VALUES ($idOrden, '$cod', '$url_tracking', '$numAlbaran', '$pdf','$fechaCompleta')");

						$this->sendTrakingMail($email, $cod, $url_tracking);

						$this->printMessage('Se ha enviado la orden #'.$idOrden, 1);
						add_post_meta($idOrden, '_sended', true);

					}else $this->printMessage('Ha ocurrido un error y no se pudo enviar la orden', 0);

				}else $this->printMessage($respuesta['faultstring'], 0);
			}
		}

		/**** CANCELA UN ENVÍO SI ES POSIBLE ****/
		function cancelaEnvio($idOrden, $albaran){
			global $wpdb;

			/**** RECONECTAMOS, LOGIN EXPIRA EN 10MIN ****/
			$this->login();

			$is_sended = get_post_meta($idOrden, '_sended');
			$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
			$pdf = ENVIALIA_UPLOADS.$albaran.'.pdf';

			if ($is_sended){
				$xml = '<?xml version="1.0" encoding="utf-8"?>
							<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
								<soap:Header>
									<ROClientIDHeader xmlns="http://tempuri.org/">
										<ID>'.$_SESSION['envialia']['idSesion'].'</ID>
									</ROClientIDHeader>
								</soap:Header>
								<soap:Body>
									<WebServService___BorraEnvio xmlns="http://tempuri.org/">
										<strCodAgeCargo>'.$this->agencia.'</strCodAgeCargo>
										<strCodAgeOri>'.$this->agencia.'</strCodAgeOri>
										<strAlbaran>'.$albaran.'</strAlbaran>
									</WebServService___BorraEnvio>
								</soap:Body>
							</soap:Envelope>';

				$respuesta = envialiaPost($xml, $this->url);

				$erase = false;

				if (!isset($respuesta['faultcode'])){
					switch($respuesta['v1:intCodError']){
						case 1:
							$this->printMessage('La orden no existe en Envialia, pero se eliminó el registro', 0);
							$erase = true;
							break;
						case 2:
							$this->printMessage('El usuario no tiene permiso para borrar este envío', 0);
							$erase = false;
							break;
						case 3:
							$this->printMessage('La fecha estÁ fuera del rango permitido', 0);
							$erase = false;
							break;
						default:
							$erase = true;
					}

					/**** SE ELIMINA ****/
					if ($erase){
						delete_post_meta($idOrden, '_sended');
						$wpdb->query("DELETE FROM $tabla WHERE `id_envio_order` = $idOrden");
						if (file_exists($pdf)) unlink($pdf);
						$this->printMessage('Se ha cancelado la orden #'.$idOrden, 1);
					}

				}else $this->printMessage($respuesta['faultstring'], 0);

			}else{
				$this->printMessage('Lo orden #'.$idOrden.' no existe!', 0);
			}
		}

		/**** GENERA EL PDF CON LAS ETIQUETAS ****/
		function descargarEtiqueta($albaran){

			$rute = ENVIALIA_UPLOADS.$albaran.'.pdf';

			if (!file_exists($rute)){
				$xml = '<?xml version="1.0" encoding="utf-8"?>
							<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
								<soap:Header>
									<ROClientIDHeader xmlns="http://tempuri.org/">
										<ID>'.$_SESSION['envialia']['idSesion'].'</ID>
									</ROClientIDHeader>
								</soap:Header>
								<soap:Body>
									<WebServService___ConsEtiquetaEnvio5>
										<strCodAgeOri>'.$this->agencia.'</strCodAgeOri>
										<strAlbaran>'.$albaran.'</strAlbaran>
										<strBulto></strBulto>
										<boPaginaA4>true</boPaginaA4>
									</WebServService___ConsEtiquetaEnvio5>
								</soap:Body>
							</soap:Envelope>';

				$respuesta = envialiaPost($xml, $this->url);

				if (isset($respuesta['v1:strEtiqueta']) && strlen($respuesta['v1:strEtiqueta'])>10){
					$pdf = base64_decode($respuesta['v1:strEtiqueta']);
					file_put_contents($rute, $pdf);
				}else $this->printMessage('La etiqueta no estÁ disponible', 0);
			}
		}

		/**** ENVÍA UN MENSAJE AL USUARIO CON EL LINK DE SEGUIMIENTO ****/
		function sendTrakingMail($email, $order, $trackUrl){
			$return = "<br/>";
			add_filter('wp_mail_content_type', 'set_html_content_type');
			wp_mail($email, 'ENVIALIA - Se ha procesado su paquete', get_option('cli_nombre').$return.$return.'Se ha procesado su pedido, el número de envío es '.$order.$return.'Puede realizar el seguimiento de su pedido aquí <a href="'.$trackUrl.'">'.$trackUrl.'</a>');
			remove_filter('wp_mail_content_type', 'set_html_content_type');
		}

		/**** IMPRIME UN MENSAJE EN PANTALLA CON LOS ESTILOS DE WORDPRESS ****/
		function printMessage($mensaje, $success= false){
			echo ($success)? '<div class="updated fade"><p>'.$mensaje.'</p></div>':'<div class="error fade"><p>'.$mensaje.'</p></div>';
		}

	}
}
?>