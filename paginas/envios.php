<?php
	$tabla = $wpdb->prefix . ENVIALIA_PLUGIN_TABLE;
	$num = $wpdb->get_var("SELECT COUNT(*) FROM $tabla");

	$paginas = new Paginator;
	$paginas->items_total = $num;
	$paginas->items_per_page = 20;
	$paginas->page = '/envialia-carrier/wp-admin/admin.php?page=envialia-carrier-panel';

	$paginas->paginate();

	$select = $wpdb->get_results("SELECT *, DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fechaEsp FROM $tabla ORDER BY id_envio DESC {$paginas->limit}");

	$paginacion = $paginas->display_pages();
?>

<div class="wrap">
	<div class="eHead">
		<img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/envialia.png' ?>" alt="Envialia Logo" class="envialiaLogo" />
		<h2>Administración de envíos</h2>
		<a href="https://netsis.es" target="_blank" id="netsis-plugin"><img src="<?php echo ENVIALIA_PLUGIN_URI.'/img/ne-logo.png' ?>" /></a>
	</div>

	<table class="envialia">
		<tr><th>Pedido</th><th>Fecha envío</th><th>Código Envialia</th><th>Albarán</th><th>Opciones</th></tr>
		<tr><td class="separador" colspan="5"></td></tr>
		<?php
			foreach($select as $result){
				$rute = ENVIALIA_UPLOADS.$result->num_albaran.'.pdf';
				$linkLabel = (file_exists($rute))? $result->codigo_barras:admin_url('admin.php?page=envialia-carrier-panel&albaran='.$result->num_albaran.'&action=label');

				echo '<tr><td><a href="'.admin_url('post.php?post='.$result->id_envio_order.'&action=edit').'" title="Ver pedido">#'.$result->id_envio_order.'</a></td><td>'.$result->fechaEsp.'</td><td>'.$result->codigo_envio.'</td><td>'.$result->num_albaran.'</td><td>
						<a href="'.$linkLabel.'" title="Etiquetas" class="envAction"><img src="'.ENVIALIA_PLUGIN_URI.'/img/sticker.png" /></a>
						<a href="'.$result->url_track.'" target="_blank" title="Tracking" class="envAction"><img src="'.ENVIALIA_PLUGIN_URI.'/img/info.png" /></a>
						<a href="'.admin_url('admin.php?page=envialia-carrier-panel&order='.$result->id_envio_order.'&albaran='.$result->num_albaran.'&action=delete').'" title="Cancelar / Eliminar" class="envAction prompt"><img src="'.ENVIALIA_PLUGIN_URI.'/img/cancel.png" /></a>
					  </td></tr>';
			}

			if (!$num) echo '<tr><td class="noresults" colspan="6" align="center">No hay envíos en curso</td></tr>';
		?>
	</table>

	<br/>

	<div class="center">
		<?php echo $paginacion ?>
	</div>
</div>