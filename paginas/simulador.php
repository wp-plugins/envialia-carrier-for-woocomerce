<div class="wrap">
	<div class="eHead">
		<img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia.png' ?>" alt="Envialia Logo" class="envialiaLogo" />
		<h2>Simulador de checkout</h2>
		<a href="http://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/ne-logo.png' ?>" /></a>
	</div>

	<br/>

	<?php
		if (isset($_POST['simulador_pais'])){
			echo '<div class="metodos_envio">';
			echo '<p class="infoText">Estos son los envíos que podría elegir su cliente y el precio que pagaría con la configuración actual</p>';

			extract($_POST);
			$contador = 0;

			foreach ($envialia_carrier_servicios as $servId => $servName) {
				if (obtenerOpcion('servicio_'.$servId)){ // El servicio está activo

					// Saltamos los servicios que no interesan
					if (!$e_comm->servicioValido($servId, $simulador_pais)) continue;

					$envio_gratis = $e_comm->esGratis($servId, $simulador_pais, $simulador_total);
					$precio = $e_comm->calcularCostesEnvio($servId, $simulador_pais, $simulador_cp, $simulador_peso, $simulador_total, $simulador_numero);
					$precio_esp = number_format($precio, 2, ',', '.');

	        		// Si el envío no puede ser gratuito y el valor es 0  lo descartamos
	        		if (!$envio_gratis && $precio == 0) continue;

					// Se calcula el precio para este envio
        			echo '<p>● '.$servName.' <span class="precio">'.$precio_esp.get_woocommerce_currency_symbol().'</span></p>';

        			$contador++;

					// Si solo se quiere mostrar el más económico
					if (obtenerOpcion('mostrar_economico')) break;
				}
			}

			if($contador==0) echo '<p>¡No se ha encontrado una tarifa apropiada para el destino, compruebe los datos!</p>';

			echo '</div>';
		}
	?>


	<form method="post">
		<table class="tablaSimple">
			<tr valign="top"><th scope="row">Peso paquete (kg)</th><td><input type="text" name="simulador_peso" size="3" maxlength="3" value="<?php echo (isset($simulador_peso) && strlen($simulador_peso)>0)? $simulador_peso:1 ?>" /></td></tr>
			<tr valign="top"><th scope="row">Importe compra</th><td><i><input type="text" name="simulador_total" size="10" maxlength="10" value="<?php echo (isset($simulador_total) && strlen($simulador_total)>0)? $simulador_total:10.00 ?>" /></td></tr>
			<tr valign="top"><th scope="row">Nº de artículos</th><td><i><input type="text" name="simulador_numero" size="3" maxlength="3" value="<?php echo (isset($simulador_numero) && strlen($simulador_numero)>0)? $simulador_numero:10 ?>" /></td></tr>
			<tr valign="top"><th scope="row">Cód. Postal de destino</th><td><input type="text" name="simulador_cp" size="6" maxlength="6" value="<?php echo (isset($simulador_cp) && strlen($simulador_cp)>0)? $simulador_cp:'01000' ?>" /></td></tr>
			<tr valign="top"><th scope="row">País de destino</th><td><input type="text" name="simulador_pais" size="2" maxlength="2" value="<?php echo (isset($simulador_pais) && strlen($simulador_pais)>0)? $simulador_pais:'ES' ?>" /></td></tr>
		</table>

		<div style="clear: both"></div>

	    <?php submit_button('Calcular Precio'); ?>
	</form>
</div>