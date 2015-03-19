<div class="wrap">
	<div class="eHead">
		<img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia.png' ?>" alt="Envialia Logo" class="envialiaLogo"/>
		<h2>Configuración general</h2>
		<a href="https://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/ne-logo.png' ?>" /></a>
	</div>

	<br/>

	<?php echo extrasPanel() ?>

	<div id="envOptions">

		<?php $active_tab = (isset($_GET['tab']))? $_GET['tab']:'shop'; ?>

		<h2 class="nav-tab-wrapper">
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=shop') ?>" data-target="shop" class="nav-tab <?php if ($active_tab=='shop') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-businessman"></span> Comercio</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=api') ?>" data-target="api" class="nav-tab <?php if ($active_tab=='api') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-network"></span> API Envialia</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=services') ?>" data-target="services" class="nav-tab <?php if ($active_tab=='services') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-site"></span> Servicios</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=package') ?>" data-target="package" class="nav-tab <?php if ($active_tab=='package') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-archive"></span> Envíos</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=advanced') ?>" data-target="advanced" class="nav-tab <?php if ($active_tab=='advanced') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-settings"></span> Avanzado</a>
		</h2>

		<form method="post" action="options.php" width="100%">
		    <?php settings_fields(ENVIALIA_PLUGIN_OPTIONS); ?>
		    <?php do_settings_sections(ENVIALIA_PLUGIN_OPTIONS); ?>

		    <?php
		    	function activeTab(){ return (isset($_GET['tab']))? $_GET['tab']:'shop'; }
		    	function showIfTab($tab){ if (activeTab()!=$tab) echo 'style="display: none"'; }
		    ?>

		    <table class="form-table" <?php showIfTab('shop') ?> data-section="shop">
		    	<tr><td colspan="2"><h3>Datos de su comercio</h3></td></tr>

				<tr valign="top"><th scope="row">Nombre comercial</th><td><input type="text" name="cli_nombre" maxlength="80" value="<?php echo get_option('cli_nombre'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Dirección</th><td><input type="text" name="cli_direccion" maxlength="200" value="<?php echo get_option('cli_direccion'); ?>" class="big" /></td></tr>
				<tr valign="top"><th scope="row">Población</th><td><input type="text" name="cli_poblacion" maxlength="50" value="<?php echo get_option('cli_poblacion'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Código Postal</th><td><input type="text" size="5" name="cli_codpostal" maxlength="5" value="<?php echo get_option('cli_codpostal'); ?>" /></td></tr>
			</table>

			<table class="form-table" <?php showIfTab('api') ?>  data-section="api">
				<tr><td colspan="2"><h3 style="margin-bottom: 0">Cuenta de Envialia</h3></td></tr>
				<tr valign="top"><td scope="row" colspan="2" style="padding-bottom: 20px"><small>Si el plugin no está en línea con el servidor de Envialia <b>no aparecerá el botón</b> para enviar el pedido</small></td></tr>

				<tr valign="top"><th scope="row">Servidor API ENVIALIA<br/><small>Solicítelo en su centro de servicio ENVIALIA. 902400909</small></th><td><input type="text" name="url" maxlength="50" class="big" value="<?php echo get_option('url'); ?>" /></td></tr>
				<tr valign="top"><th scope="row" style="width: 50%">Centro de servicio</th><td><input type="text" name="codigo_agencia" size="6" maxlength="6" value="<?php echo get_option('codigo_agencia'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Código cliente</th><td><input type="text" name="codigo_cliente" size="6" maxlength="5" value="<?php echo get_option('codigo_cliente'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Password</th><td><input type="password" name="password_cliente" size="15" maxlength="15" value="<?php echo get_option('password_cliente'); ?>" /></td></tr>
			</table>

			<table class="form-table" <?php showIfTab('services') ?> data-section="services">
			    <tr><td colspan="2"><h3>Configuración de los servicios</h3></td></tr>

				<tr valign="top"><th scope="row" colspan="2"><b>Servicios disponibles</b></th></tr>
				<tr valign="top"><th scope="row">E-COMM 24H</th><td><input type="checkbox" id="ch_e24" name="servicio_E24" value="1" <?php if(obtenerOpcion('servicio_E24')) echo 'checked'; ?> /> <label for="ch_e24"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia_ecomm_24.jpg' ?>" class="service_logo" /></label></td></tr>
				<tr valign="top"><th scope="row">E-COMM 72H</th><td><input type="checkbox" id="ch_e72" name="servicio_E72" value="1" <?php if(obtenerOpcion('servicio_E72')) echo 'checked'; ?> /> <label for="ch_e72"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia_ecomm_72.jpg' ?>" class="service_logo" /></label></td></tr>
				<tr valign="top"><th scope="row">E-COMM EUROPE EXPRESS</th><td><input id="ch_eeu" type="checkbox" name="servicio_EEU" value="1" <?php if(obtenerOpcion('servicio_EEU')) echo 'checked'; ?> /> <label for="ch_eeu"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia_ecomm_EEU.jpg' ?>" class="service_logo" /></label></td></tr>
				<tr valign="top"><th scope="row">E-COMM WORLDWIDE</th><td><input id="ch_eww" type="checkbox" name="servicio_EWW" value="1" <?php if(obtenerOpcion('servicio_EWW')) echo 'checked'; ?> /> <label for="ch_eww"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia_ecomm_EWW.jpg' ?>" class="service_logo" /></label></td></tr>

				<tr><td colspan="2">&nbsp;</td></tr>
				<tr valign="top"><th scope="row" colspan="2"><label><input type="checkbox" name="mostrar_economico" value="1" <?php if(obtenerOpcion('mostrar_economico')) echo 'checked'; ?> /> Mostrar sólo el más económico al cliente</label></th></tr>
			</table>

			<table class="form-table" <?php showIfTab('package') ?> data-section="package">
				<tr><td colspan="2"><h3>Cálculo de importe del envío (Checkout)</h3></td></tr>

				<tr valign="top"><th scope="row">Coste fijo del envío<br/><small>Déjelo a 0 para que se calcule el precio</small></th><td><input type="text" name="coste_fijo" size="11" maxlength="10" value="<?php echo obtenerOpcion('coste_fijo') ?>" /></td></tr>

				<tr valign="top"><th scope="row">Calcular precio de envio por</th><td>
					<select name="calcular_precio">
						<option <?php if(obtenerOpcion('calcular_precio')=="0") echo "selected=\"selected\"" ?> value="0">Peso</option>
						<option <?php if(obtenerOpcion('calcular_precio')=="1") echo "selected=\"selected\"" ?> value="1">Importe Carrito</option>
					</select>
				</td></tr>

				<tr valign="top"><th scope="row">Impuesto sobre envío<br/><small>Introduzca el porcentaje sin el símbolo %</small></th><td><input type="text" name="impuesto" size="11" maxlength="10" value="<?php echo obtenerOpcion('impuesto') ?>" /></td></tr>
				<tr valign="top"><th scope="row">Países a los que se aplica el impuesto<br/><small>Valores separados por comas</small></th><td><input type="text" name="paises_iva" value="<?php echo obtenerOpcion('paises_iva') ?>" class="full" /></td></tr>
		    </table>

			<table class="form-table" <?php showIfTab('advanced') ?> data-section="advanced">
				<tr><td colspan="2"><h3>Ajustes avanzados</h3></td></tr>

				<tr valign="top"><th scope="row">Ruta de los ficheros CSV de tarifas<br/><small>Estes ficheros se encuentran por defecto dentro de la carpeta <b>tarifas</b> del plugin</small></th><td><input type="text" name="ruta_tarifas" value="<?php echo obtenerOpcion('ruta_tarifas') ?>" class="full" /></td></tr>
				<tr valign="top"><th scope="row"><label for="inputMostrarDatos">Mostrar datos del pedido<br/><small>Cuando se selecciona un servicio, si el cliente no lo ha elegido</small></label></th><td><input type="checkbox" id="inputMostrarDatos" name="mostrar_datos_pedido" value="1" <?php if(obtenerOpcion('mostrar_datos_pedido')) echo 'checked'; ?> /></td></tr>
				<tr valign="top"><th scope="row"><label for="inputEliminarDatos">Eliminar todos los datos<br/><small>Eliminar datos del plugin al desinstalarlo</small></label></th><td><input type="checkbox" id="inputEliminarDatos" name="eliminar_datos" value="1" <?php if(obtenerOpcion('eliminar_datos')) echo 'checked'; ?> /></td></tr>
		    </table>

		    <?php submit_button(); ?>
		</form>
	</div>
</div>