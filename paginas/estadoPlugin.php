<div class="wrap">
	<div class="eHead">
		<img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia.png' ?>" alt="Envialia Logo" class="envialiaLogo" />
		<h2>Estado del plugin</h2>
		<a href="https://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/ne-logo.png' ?>" /></a>
	</div>

	<?php echo extrasPanel() ?>

	<div id="envOptions">

		<h3 id="system">Requisitos</h3>

		<?php

		function getWooVersion(){
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
				// If the plugin version number is set, return it
				return WOOCOMMERCE_VERSION;
			}

			return false;
		}

		$wc = in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')));
		$memory_limit = function_exists('wc_let_to_num')? wc_let_to_num( WP_MEMORY_LIMIT ):woocommerce_let_to_num( WP_MEMORY_LIMIT );
		$curl_info = curl_version();
		$server_configs = array(
			"Versión de PHP" => array(
				"required" => "5.0",
				"value"    => phpversion(),
				"result"   => version_compare(phpversion(), "5.0")
			),
			"WooCommerce" => array(
				"required" => "2.1.0",
				"value"    => getWooVersion(),
				"result"   => version_compare(getWooVersion(), "2.1.0")
			),
			"Librería cURL" => array(
				"required" => true,
				"value"    => $curl_info['version'],
				"result"   => function_exists('curl_version'),
				"fallback" => "Necesario para hacer las llamadas al servidor de Envialia"
			),
			"Límite de memoria WP" => array(
				"required" => '64MB',
				"value"    => WP_MEMORY_LIMIT.' <a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Aumentar memoria</a>',
				"result"   => $memory_limit >= 64,
				"fallback" => "Recomendado para un rendimiento óptimo"
			),
			"Servidor Envialia en línea" => array(
				"required" => 'No',
				"value"    => '',
				"result"   => $e_comm->login(),
				"fallback" => "Requerido para tramitar la recogida de los pedidos con Envialia"
			)
		);

		?>

		<table cellspacing="1px" cellpadding="4px" width="100%" style="background-color: white; padding: 5px; border: 1px solid #ccc;">
			<tr>
				<th align="left">&nbsp;</th>
				<th align="left">Requerido</th>
				<th align="left">Presente</th>
			</tr>

			<?php foreach($server_configs as $label => $server_config) {
				if ($server_config["result"]) {
					$background = "#9e4";
					$color = "black";
				} elseif (isset($server_config["fallback"])) {
					$background = "#FCC612";
					$color = "black";
				} else {
					$background = "#f43";
					$color = "white";
				}
				?>
				<tr>
					<td class="title"><?php echo $label; ?></td>
					<td><?php echo ($server_config["required"] === true ? "Si" : $server_config["required"]); ?></td>
					<td style="background-color:<?php echo $background; ?>; color:<?php echo $color; ?>">
						<?php
						echo $server_config["value"];
						if ($server_config["result"] && !$server_config["value"]) echo "Si";
						if (!$server_config["result"]) {
							if (isset($server_config["fallback"])) {
								echo "<div>No. ".$server_config["fallback"]."</div>";
							}
							if (isset($server_config["failure"])) {
								echo "<div>".$server_config["failure"]."</div>";
							}
						}
						?>
					</td>
				</tr>
			<?php } ?>

		</table>

		<?php
		$permissions = array(
			'TEMP_DIR'				=> array (
					'description'		=> 'Directorio para la generación de etiquetas',
					'value'				=> ENVIALIA_UPLOADS,
					'status'			=> (is_writable(ENVIALIA_UPLOADS) ? "ok" : "failed"),
					'status_message'	=> (is_writable(ENVIALIA_UPLOADS) ? "Correcto" : "Problema de escritura")
				),
			'TARIFAS'				=> array (
					'description'		=> 'Directorio de Tarifas',
					'value'				=> obtenerOpcion('ruta_tarifas'),
					'status'			=> (is_writable(obtenerOpcion('ruta_tarifas')) ? "ok" : "failed"),
					'status_message'	=> (is_writable(obtenerOpcion('ruta_tarifas')) ? "Correcto" : "Problema de escritura")
				),
			'TARIFAS_PESO'			=> array (
					'description'		=> 'Fichero CSV de tarifas según peso',
					'value'				=> 'tarifas.peso.csv',
					'status'			=> (is_file(obtenerOpcion('ruta_tarifas').'/tarifas.peso.csv') ? "ok" : "failed"),
					'status_message'	=> (is_file(obtenerOpcion('ruta_tarifas').'/tarifas.peso.csv') ? "Existe" : "No existe")
				),
			'TARIFAS_IMPORTE'		=> array (
					'description'		=> 'Fichero CSV de tarifas según importe',
					'value'				=> 'tarifas.importe.csv',
					'status'			=> (is_file(obtenerOpcion('ruta_tarifas').'/tarifas.importe.csv') ? "ok" : "failed"),
					'status_message'	=> (is_file(obtenerOpcion('ruta_tarifas').'/tarifas.importe.csv') ? "Existe" : "No existe")
				)
			);

		?>
		<br />
		<h3 id="system">Permisos y ficheros</h3>
		<table cellspacing="1px" cellpadding="4px" width="100%" style="background-color: white; padding: 5px; border: 1px solid #ccc;">
			<tr>
				<th align="left">Descripción</th>
				<th align="left">Valor</th>
				<th align="left">Estado</th>
			</tr>
			<?php
			foreach ($permissions as $permission) {
				if ($permission['status'] === 'ok') {
					$background = "#9e4";
					$color = "black";
				} else {
					$background = "#f43";
					$color = "white";
				}
				?>
			<tr>
				<td><?php echo $permission['description']; ?></td>
				<td><?php echo $permission['value']; ?></td>
				<td style="background-color:<?php echo $background; ?>; color:<?php echo $color; ?>"><?php echo $permission['status_message']; ?></td>
			</tr>

			<?php } ?>

		</table>
	</div>
</div>