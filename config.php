<?php

$uploads = wp_upload_dir();

define ('ENVIALIA_PLUGIN_ID', 'envialia-carrier-for-woocomerce');
define ('ENVIALIA_PLUGIN_TABLE', 'envialia_carrier');
define ('ENVIALIA_PLUGIN_OPTIONS', 'envialia_carrier_settings');
define ('ENVIALIA_PLUGIN_URI', plugins_url('envialia-carrier-for-woocomerce'));
define ('ENVIALIA_UPLOADS', $uploads['basedir'].'/envialia-carrier-for-woocomerce/');
define ('ENVIALIA_UPLOADS_URL', $uploads['baseurl'].'/envialia-carrier-for-woocomerce/');

$envialia_carrier_settings = array(
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
				'ruta_tarifas',
				'mostrar_datos_pedido',
				'eliminar_datos'
			);

$envialia_carrier_defaults = array(
				'servicio_E24' => 1,
				'servicio_E72' => 1,
				'servicio_EEU' => 1,
				'servicio_EWW' => 1,
				'mostrar_economico' => 0,

				'calcular_precio' => 0, // 0 peso | 1 importe
				'impuesto' => 21,
				'coste_fijo' => 0,

				'paises_iva' => 'AT, BE, BG, CC, CY, CZ, DK, EE, FI, FR, DE, GR, HU, IE, IT, LV, LT, LU, MT, NL, PL, PT, RO, SK, SI, ES, SE, GB',
				'ruta_tarifas' => dirname(__FILE__).'/tarifas',
				'mostrar_datos_pedido' => 1,
				'eliminar_datos' => 1
			);

$envialia_carrier_servicios = array(
				'E72' => 'Envialia 72h',
				'E24' => 'Envialia 24h',
				'EEU' => 'Envialia EU Express',
				'EWW' => 'Envialia Worldwide'
			 );

/* FIN CONFIGURACIÓN */