<?php if (!$e_comm->login()) $e_comm->printMessage('El plugin no está conectado al servidor, revise la configuración de la cuenta en la pestaña "API Envialia"', 0); ?>

<div class="wrap">

	<div class="eHead">
		<img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/envialia.png') ?>" alt="Envialia Logo" class="envialiaLogo"/>
		<h2>Configuración general</h2>
		<a href="http://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/ne-logo.png') ?>" /></a>
	</div>

	<?php echo extrasPanel() ?>

	<div id="envOptions">

		<?php $active_tab = (isset($_GET['tab']))? $_GET['tab']:'shop'; ?>

		<h2 class="nav-tab-wrapper">
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=shop') ?>" class="nav-tab <?php if ($active_tab=='shop') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-businessman"></span> Comercio</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=api') ?>" class="nav-tab <?php if ($active_tab=='api') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-network"></span> API Envialia</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=services') ?>" class="nav-tab <?php if ($active_tab=='services') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-site"></span> Servicios</a>
		    <a href="<?php echo get_admin_url(null, 'admin.php?page=envialia-carrier-options&tab=package') ?>" class="nav-tab <?php if ($active_tab=='package') echo 'nav-tab-active' ?>"><span class="dashicons dashicons-admin-settings"></span> Envíos</a>
		</h2>

		<form method="post" action="options.php" width="100%">
		    <?php settings_fields(ENVIALIA_PLUGIN_OPTIONS); ?>
		    <?php do_settings_sections(ENVIALIA_PLUGIN_OPTIONS); ?>

		    <?php
		    	function showIfTab($tab){
		    		$active_tab = (isset($_GET['tab']))? $_GET['tab']:'shop';
		    		if ($active_tab!=$tab) echo 'style="display: none"';
		    	}
		    ?>

		    <table class="form-table" <?php showIfTab('shop') ?>>
		    	<tr><td colspan="2"><h3>Datos de su comercio</h3></td></tr>

				<tr valign="top"><th scope="row">Nombre comercial</th><td><input type="text" name="cli_nombre" maxlength="80" value="<?php echo get_option('cli_nombre'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Dirección</th><td><input type="text" name="cli_direccion" maxlength="200" value="<?php echo get_option('cli_direccion'); ?>" class="big" /></td></tr>
				<tr valign="top"><th scope="row">Población</th><td><input type="text" name="cli_poblacion" maxlength="50" value="<?php echo get_option('cli_poblacion'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Código Postal</th><td><input type="text" size="5" name="cli_codpostal" maxlength="5" value="<?php echo get_option('cli_codpostal'); ?>" /></td></tr>
			</table>

			<table class="form-table" <?php showIfTab('api') ?>>
				<tr><td colspan="2"><h3 style="margin-bottom: 0">Cuenta de Envialia</h3></td></tr>
				<tr valign="top"><td scope="row" colspan="2" style="padding-bottom: 20px"><small>Si el plugin no está en línea con el servidor de Envialia <b>no aparecerá el botón</b> para enviar el pedido</small></td></tr>
				
				<tr valign="top"><th scope="row">Servidor API ENVIALIA<br/><small>Solicítelo en su centro de servicio ENVIALIA. 902400909</small></th><td><input type="text" name="url" maxlength="50" class="big" value="<?php echo get_option('url'); ?>" /></td></tr>
				<tr valign="top"><th scope="row" style="width: 50%">Centro de servicio</th><td><input type="text" name="codigo_agencia" size="6" maxlength="6" value="<?php echo get_option('codigo_agencia'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Código cliente</th><td><input type="text" name="codigo_cliente" size="6" maxlength="5" value="<?php echo get_option('codigo_cliente'); ?>" /></td></tr>
				<tr valign="top"><th scope="row">Password</th><td><input type="text" name="password_cliente" size="15" maxlength="15" value="<?php echo get_option('password_cliente'); ?>" /></td></tr>
			</table>

			<table class="form-table" <?php showIfTab('services') ?>>
			    <tr><td colspan="2"><h3>Configuración de los servicios</h3></td></tr>

				<tr valign="top"><th scope="row" colspan="2"><b>Servicios disponibles</b></th></tr>
				<tr valign="top"><th scope="row">E-COMM 24H</th><td><input type="checkbox" id="ch_e24" name="servicio_E24" value="1" <?php if(obtenerOpcion('servicio_E24')) echo 'checked'; ?> /> <label for="ch_e24"><img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/envialia_ecomm_24.jpg') ?>" class="service_logo" /></label></td></tr>	
				<tr valign="top"><th scope="row">E-COMM 72H</th><td><input type="checkbox" id="ch_e72" name="servicio_E72" value="1" <?php if(obtenerOpcion('servicio_E72')) echo 'checked'; ?> /> <label for="ch_e72"><img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/envialia_ecomm_72.jpg') ?>" class="service_logo" /></label></td></tr>	
				<tr valign="top"><th scope="row">E-COMM EUROPE EXPRESS</th><td><input id="ch_eeu" type="checkbox" name="servicio_EEU" value="1" <?php if(obtenerOpcion('servicio_EEU')) echo 'checked'; ?> /> <label for="ch_eeu"><img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/envialia_ecomm_EEU.jpg') ?>" class="service_logo" /></label></td></tr>	
				<tr valign="top"><th scope="row">E-COMM WORLDWIDE</th><td><input id="ch_eww" type="checkbox" name="servicio_EWW" value="1" <?php if(obtenerOpcion('servicio_EWW')) echo 'checked'; ?> /> <label for="ch_eww"><img src="<?php echo plugins_url('envialia-carrier-for-woocomerce/img/envialia_ecomm_EWW.jpg') ?>" class="service_logo" /></label></td></tr>
				
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr valign="top"><th scope="row" colspan="2"><label><input type="checkbox" name="mostrar_economico" value="1" <?php if(obtenerOpcion('mostrar_economico')) echo 'checked'; ?> /> Mostrar sólo el más económico al cliente</label></th></tr>
			</table>

			<table class="form-table" <?php showIfTab('package') ?>>
				<tr><td colspan="2"><h3>Cálculo de importe del envío</h3></td></tr>

				<tr valign="top"><th scope="row">Coste fijo del envío<br/><small>Déjelo a 0 para que se calcule el precio</small></th><td><input type="text" name="coste_fijo" size="11" maxlength="10" value="<?php echo obtenerOpcion('coste_fijo') ?>" /></td></tr>

				<tr valign="top"><th scope="row">Calcular precio de envio por</th><td>
					<select name="calcular_precio">
						<option <?php if(obtenerOpcion('calcular_precio')=="0") echo "selected=\"selected\"" ?> value="0">Peso</option>
						<option <?php if(obtenerOpcion('calcular_precio')=="1") echo "selected=\"selected\"" ?> value="1">Importe Carrito</option>
					</select>
				</td></tr>

				<tr valign="top"><th scope="row">Impuesto sobre envío<br/><small>Introduzca el porcentaje sin el símbolo %</small></th><td><input type="text" name="impuesto" size="11" maxlength="10" value="<?php echo obtenerOpcion('impuesto') ?>" /></td></tr>
				<tr valign="top"><th scope="row">Países a los que se aplica el impuesto<br/><small>Valores separados por comas</small></th><td><input type="text" name="paises_iva" value="<?php echo obtenerOpcion('paises_iva') ?>" class="full" /></td></tr>

				<tr valign="top"><th scope="row">Ruta de los ficheros CSV de tarifas<br/><small>Estes ficheros se encuentran por defecto dentro de la carpeta <b>tarifas</b> del plugin</small></th><td><input type="text" name="ruta_tarifas" value="<?php echo obtenerOpcion('ruta_tarifas') ?>" class="full" /></td></tr>
		    </table>

		    <?php submit_button(); ?>
		</form>
	</div>

	<script>
	    jQuery.fn.getCursorPosition = function(){
	        var el = jQuery(this).get(0);
	        var pos = 0;

	        if ('selectionStart' in el) pos = el.selectionStart;
	        else if('selection' in document){
	            el.focus();
	            var Sel = document.selection.createRange();
	            var SelLength = document.selection.createRange().text.length;
	            Sel.moveStart('character', -el.value.length);
	            pos = Sel.text.length - SelLength;
	        }

	        return pos;
	    }

		jQuery('#linksTxtDin a').click(function(e){
			e.preventDefault();
			var textArea = jQuery('textarea[name=mail_text]');
			var position = textArea.getCursorPosition();
			var text = jQuery(this).attr('data-val');
			var content = textArea.val();
			var newContent = content.substr(0, position) + text + content.substr(position);
			textArea.val(newContent);
		});

		function enableDisableItems(){
			jQuery('select[name=servicio_gratuito]').on('change', function(){
				var destin_el = jQuery('input[name=importe_minimo_gratuito]');
				if (jQuery(this).val()=='0') destin_el.attr('disabled', true);
				else destin_el.attr('disabled', false);
			}).trigger('change');

			jQuery('select[name=servicio_gratuito_internacional]').on('change', function(){
				var destin_el = jQuery('input[name=importe_minimo_gratuito_internacional]');
				if (jQuery(this).val()=='0') destin_el.attr('disabled', true);
				else destin_el.attr('disabled', false);
			}).trigger('change');

			jQuery('select[name=bultos_envio]').on('change', function(){
				var destin_el = jQuery('input[name=articulos_bulto]');
				if (jQuery(this).val()=='0') destin_el.attr('disabled', true);
				else destin_el.attr('disabled', false);
			}).trigger('change');

			jQuery('select[name=manipulacion]').on('change', function(){
				var destin_el_1 = jQuery('input[name=manipulacion_margen]');
				var destin_el_2 = jQuery('input[name=manipulacion_coste_fijo]');
				if (jQuery(this).val()=='0'){
					destin_el_1.attr('disabled', true);
					destin_el_2.attr('disabled', false);
				}else{
					destin_el_1.attr('disabled', false);
					destin_el_2.attr('disabled', true);
				}
			}).trigger('change');
		}

		jQuery(document).ready(enableDisableItems);
	</script>
</div>