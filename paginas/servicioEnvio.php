<div class="wrap">
	<div class="eHead">
		<img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia.png' ?>" alt="Envialia Logo" class="envialiaLogo" />
		<h2>Seleccione un servicio</h2>
	</div>

	<?php
		$pais_cliente = implode(get_post_meta($_GET['order'], '_shipping_country'));
	?>

	<form method="post" width="100%">
		<table class="form-table">
			<tr valign="top"><th scope="row" colspan="2"><i>El cliente no eligió un servicio de Envialia al realizar el pago, seleccione el servicio con el que realizará el envío</i></th></tr>

			<?php
				if (obtenerOpcion('mostrar_datos_pedido')):
					$direccion = implode(get_post_meta($_GET['order'], '_shipping_address_1')).' '.implode(get_post_meta($_GET['order'], '_shipping_address_2'));
					$ciudad = implode(get_post_meta($_GET['order'], '_shipping_city'));
					$cod_postal = implode(get_post_meta($_GET['order'], '_shipping_postcode'));
					$shipment_name = $wpdb->get_var("SELECT order_item_name FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'shipping' AND order_id = '{$_GET['order']}'");
					$shipment_number = $wpdb->get_var("SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'shipping' AND order_id = '{$_GET['order']}'");
					$precio_envio = $wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = 'cost' AND order_item_id = '$shipment_number'");
			?>
				<tr valign="top"><td scope="row" colspan="2">
					<small>
						Nº Pedido: <?php echo $_GET['order'] ?><br/>
						Dirección: <?php echo $direccion ?><br/>
						Ciudad: <?php echo $ciudad ?><br/>
						Código Postal: <?php echo $cod_postal ?><br/>
						País: <?php echo $pais_cliente ?><br/><br/>
						Envío elegido: <?php echo $shipment_name ?><br/>
						Importe envío: <?php echo $precio_envio ?>
					</small>
				</td></tr>
			<?php endif ?>

			<tr valign="top"><th scope="row"><label for="selectServicio">Servicio</label></th><td>
				<select id="selectServicio" name="servicio_elegido">
				<?php
					foreach ($envialia_carrier_servicios as $id_serv => $de_serv):
						if (!$e_comm->servicioValido($id_serv, $pais_cliente)) continue;
				?>
						<option value="<?php echo $id_serv ?>"><?php echo $de_serv ?></option>
					<?php endforeach ?>
				</select>
			</td></tr>
		</table>

	    <?php submit_button('Procesar Envío'); ?>
	</form>
</div>