<?php
	function set_html_content_type() {
		return 'text/html';
	}

	function cambiarEstadoOrden($idOrden, $nuevoEstado, $nota=''){
		$order = new WC_Order($idOrden);
		$order->update_status($nuevoEstado, $nota);
	}

	function cargarPagina($pagina){
		global $wpdb, $e_comm, $envialia_carrier_servicios;

		$fichero = "paginas/$pagina.php";
		$js_fichero = dirname(__FILE__)."/paginas/js/$pagina.php";
		require($fichero);
		if (file_exists($js_fichero)) include($js_fichero);
	}

	function obtenerOpcion($nombre){
		global $envialia_carrier_defaults;
		return (isset($envialia_carrier_defaults[$nombre]))? get_option($nombre, $envialia_carrier_defaults[$nombre]):get_option($nombre);
	}

	function xml2array($contents, $get_attributes = 1, $priority = 'tag'){
	    if (!function_exists('xml_parser_create')) return array();
	    $parser = xml_parser_create();
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	    if (!$xml_values) return;
	    $xml_array = array ();
	    $parents = array ();
	    $opened_tags = array ();
	    $arr = array ();
	    $current = & $xml_array;
	    $repeated_tag_index = array ();
	    foreach ($xml_values as $data){
	        unset ($attributes, $value);
	        extract($data);
	        $result = array ();
	        $attributes_data = array ();
	        if (isset ($value)){
	            if ($priority == 'tag') $result = $value;
	            else $result['value'] = $value;
	        }
	        if (isset ($attributes) and $get_attributes){
	            foreach ($attributes as $attr => $val){
	                if ($priority == 'tag') $attributes_data[$attr] = $val;
	                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
	            }
	        }
	        if ($type == 'open'){
	            $parent[$level -1] = & $current;
	            if (!is_array($current) or (!in_array($tag, array_keys($current)))){
	                $current[$tag] = $result;
	                if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                $current = & $current[$tag];
	            }else{
	                if (isset ($current[$tag][0])){
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }else{
	                    $current[$tag] = array($current[$tag], $result);
	                    $repeated_tag_index[$tag . '_' . $level] = 2;
	                    if (isset ($current[$tag . '_attr'])){
	                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                        unset ($current[$tag . '_attr']);
	                    }
	                }
	                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
	                $current = & $current[$tag][$last_item_index];
	            }
	        }
	        elseif ($type == 'complete'){
	            if (!isset ($current[$tag])){
	                $current[$tag] = $result;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;
	            }else{
	                if (isset ($current[$tag][0]) and is_array($current[$tag])){
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    if ($priority == 'tag' and $get_attributes and $attributes_data) $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }else{
	                    $current[$tag] = array($current[$tag], $result);
	                    $repeated_tag_index[$tag . '_' . $level] = 1;
	                    if ($priority == 'tag' and $get_attributes){
	                        if (isset ($current[$tag . '_attr'])){
	                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                            unset ($current[$tag . '_attr']);
	                        }
	                        if ($attributes_data) $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                    }
	                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
	                }
	            }
	        }elseif ($type == 'close') $current = & $parent[$level -1];
	    }
	    return $xml_array;
	}

	function curl_post($post, $url, array $options = array()) {
		if (!function_exists('curl_version')) return false;

	    $defaults = array(
	    	CURLOPT_URL => $url.'/soap',
	        CURLOPT_POST => 1,
	        CURLOPT_POSTFIELDS => $post,
	        CURLOPT_RETURNTRANSFER => 1,
	        CURLOPT_FRESH_CONNECT => 1
	    );

	    $ch = curl_init();
	    curl_setopt_array($ch, ($options + $defaults));
	    $result = curl_exec($ch);

	    curl_close($ch);
	    return $result;
	}

	function envialiaPost($xml, $url){
		$respuesta = curl_post($xml, $url);
		$arrOutput = xml2array($respuesta);
	    $body_data = $arrOutput['SOAP-ENV:Envelope']['SOAP-ENV:Body']; //Recibir solo lo necesario
	    if (!is_array($body_data)) return $body_data;
	    $body_numeric = array_values($body_data);
	    return $body_numeric[0];
	}

	// Metodo para ordenar arrays con arrays asociativos dentro
	function ordenarAsoc($clave) {
	    return function ($x, $y) use ($clave) {
		    if ($x[$clave] == $y[$clave]) return 0;
		    return ($x[$clave] < $y[$clave]) ? -1 : 1;
	    };
	}

	/////////////// Funciones de Woocommerce Envialia ///////////////////////

	function addEnvialiaShippingMethods($methods) {
		$methods[] = 'WC_Envialia_Shipping_Method';
		return $methods;
	}

	function envialiaShippingMethod(){
		if (!class_exists('WC_Envialia_Shipping_Method')){
			class WC_Envialia_Shipping_Method extends WC_Shipping_Method{

				public function __construct(){
					$this->init_settings();
					$this->id = 'envialia_carrier';
					$this->title = __('Envialia');
					$this->method_description = __('Método de envío del plugin Envialia Carrier');
					$this->init();
				}

				function init(){
					$this->init_form_fields();
					$this->init_settings();
					$this->enabled = $this->settings['active'];

					add_action('woocommerce_update_options_shipping_'. $this->id, array($this, 'process_admin_options'));
				}

				function admin_options() {
					?>
						<h2><?php _e('Settings', 'woocommerce'); ?></h2>
						<table class="form-table">
							<?php $this->generate_settings_html(); ?>
						</table>
						<p><a href="<?php echo admin_url('admin.php?page=envialia-carrier-options&tab=package') ?>">Configurar el plugin</a></p>
					<?php
				}

				function init_form_fields() {
				     $this->form_fields = array(
					     'active' => array(
					          'title' => __('Enable', 'woocommerce'),
					          'type' => 'checkbox',
					          'label' => __('Habilitar envío por Envialia Carrier', 'woocommerce'),
					          'default' => '1'
					     )
				     );
				}

				public function calculate_shipping($package){
					global $e_comm, $woocommerce, $envialia_carrier_servicios;

					$peso = wc_get_weight($woocommerce->cart->cart_contents_weight, 'kg');
					$total = $woocommerce->cart->cart_contents_total;
					$numero = $woocommerce->cart->cart_contents_count;
					//$paises_se_vende = $woocommerce->countries->get_shipping_countries(); // Ej: ['ES'] => Spain
					$pais = $woocommerce->customer->get_shipping_country(); //Ej: ES
					$cod_postal = $woocommerce->customer->get_shipping_postcode();

					$solo_mas_economico = (boolean) obtenerOpcion('mostrar_economico');

					foreach ($envialia_carrier_servicios as $servId => $servName) {
						if (obtenerOpcion('servicio_'.$servId)){ // El servicio está activo

							// Comprobamos si es gratis
							$envio_gratis = $e_comm->esGratis($servId, $pais, $total);

							// Saltamos los servicios que no interesan
							if (!$e_comm->servicioValido($servId, $pais)) continue;

							// Se calcula el precio para este envio
		        			$coste_envio = $e_comm->calcularCostesEnvio($servId, $pais, $cod_postal, $peso, $total, $numero);

			        		// Si el envío no puede ser gratuito y el valor es 0  lo descartamos
			        		if (!$envio_gratis && $coste_envio == 0) continue;

							$rate = array(
								'id' => $servId,
								'label' => $servName,
								'cost' => $coste_envio,
								'taxes' => false,
								'calc_tax' => 'per_order'
							);

							// Register the rate
							$this->add_rate($rate);

							// Si solo se quiere mostrar el más económico
							if ($solo_mas_economico) break;
						}
					}
				}
			}
		}
	}

	/////////////// Funciones de Envialia ///////////////////////

	/**** CLASE ENVIALIA QUE REALIZA LAS OPERACIONES CON EL SERVIDOR (SOLO SE CARGA EN ÁREA DE ADMINISTRACIÓN) ****/
	if (!class_exists('envialia')) {
		class envialia {
			protected $url, $agencia, $cliente, $contras, $conectado;
			static $msgNum = 0;

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

				if (isset($respuesta['v1:Result']) && $respuesta['v1:Result'] && $respuesta['v1:strError']==0){
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
			function realizaEnvio($idOrden, $servicio='E72'){
				global $wpdb, $woocommerce;

				/**** RECONECTAMOS, LOGIN EXPIRA EN 10MIN ****/
				$this->login();

				$is_sended = get_post_meta($idOrden, '_sended');
				if ($is_sended) $this->printMessage('Lo orden #'.$idOrden.' ya está enviada!', 0);
				else{
					$numero = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'line_item' AND order_id = '$idOrden'");
					$num_paquetes = $this->bultosPorEnvio($numero);
					$is_sended = get_post_meta($idOrden, '_sended');
					$cod_provincia_origen = substr(get_option('cli_codpostal'), 0, 2);
					$codpostal = implode(get_post_meta($idOrden, '_shipping_postcode'));
					$cod_provincia = substr($codpostal, 0, 2);
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
											<strCodTipoServ>'.$servicio.'</strCodTipoServ>
											<strCodCli>'.$this->cliente.'</strCodCli>

											<strNomOri>'.get_option('cli_nombre').'</strNomOri>
											<strDirOri>'.get_option('cli_direccion').'</strDirOri>
											<strPobOri>'.get_option('cli_poblacion').'</strPobOri>
											<strCPOri>'.get_option('cli_codpostal').'</strCPOri>
											<strCodProOri>'.$cod_provincia_origen.'</strCodProOri>

											<strNomDes>'.implode(get_post_meta($idOrden, '_shipping_first_name')).' '.implode(get_post_meta($idOrden, '_shipping_last_name')).'</strNomDes>
											<strDirDes>'.implode(get_post_meta($idOrden, '_shipping_address_1')).' '.implode(get_post_meta($idOrden, '_shipping_address_2')).' - '.implode(get_post_meta($idOrden, '_shipping_city')).'</strDirDes>
											<strCPDes>'.$codpostal.'</strCPDes>
											<strCodProDes>'.$cod_provincia.'</strCodProDes>
											<strTlfDes>'.implode(get_post_meta($idOrden, '_billing_phone')).'</strTlfDes>

											<intPaq>'.$num_paquetes.'</intPaq>
											<strObs>'.$observaciones.'</strObs>

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

							$this->printMessage('Se ha enviado la orden #'.$idOrden, 1);

							add_post_meta($idOrden, '_sended', true);
							cambiarEstadoOrden($idOrden, 'sended');

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
								cambiarEstadoOrden($idOrden, 'completed');
								$this->printMessage('La orden ya no se puede cancelar pero se ha eliminado de su listado', 0);
								$erase = true;
								break;
							default:
								$erase = true;
						}

						/**** SE ELIMINA ****/
						if ($erase){
							delete_post_meta($idOrden, '_sended');
							cambiarEstadoOrden($idOrden, 'completed');
							$wpdb->query("DELETE FROM $tabla WHERE `id_envio_order` = $idOrden");
							if (file_exists($pdf)) unlink($pdf);
							$this->printMessage('Se ha cancelado la orden #'.$idOrden, 1);
						}

					}else $this->printMessage($respuesta['faultstring'], 0);

				}else{
					$this->printMessage('Lo orden #'.$idOrden.' no existe!', 0);
				}
			}

			/**** OBTIENE EL ESTADO DE UN ENVÍO ****/
			function obtenerEstado($albaran){

				/**** RECONECTAMOS, LOGIN EXPIRA EN 10MIN ****/
				$this->login();

				$xml = '<?xml version="1.0" encoding="utf-8"?>
							<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
								<soap:Header>
									<ROClientIDHeader xmlns="http://tempuri.org/">
										<ID>'.$_SESSION['envialia']['idSesion'].'</ID>
									</ROClientIDHeader>
								</soap:Header>
								<soap:Body>
									<WebServService___ConsEnvEstados>
										<strCodAgeCargo>'.$this->agencia.'</strCodAgeCargo>
										<strCodAgeOri>'.$this->agencia.'</strCodAgeOri>
										<strAlbaran>'.$albaran.'</strAlbaran>
									</WebServService___ConsEnvEstados>
								</soap:Body>
							</soap:Envelope>';

				$respuesta = envialiaPost($xml, $this->url);
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
					}else $this->printMessage('La etiqueta no está disponible', 0);
				}
			}

			/**** IMPRIME UN MENSAJE EN PANTALLA CON LOS ESTILOS DE WORDPRESS ****/
			function printMessage($mensaje, $success= false){
				$this->msgNum++;
				$first = ($this->msgNum==1)? 'first':'';
				echo ($success)? '<div class="updated fade '.$first.'"><p>'.$mensaje.'</p></div>':'<div class="error fade '.$first.'"><p>'.$mensaje.'</p></div>';
			}

			/**** DEVUELVE ARRAY CON LAS TARIFAS ****/
		    protected function tarifas($tipo='peso'){ // peso | importe
		    	$nom_var = ($tipo=='importe')? 'precio_carrito':$tipo;
		    	$archivo = obtenerOpcion('ruta_tarifas').'/tarifas.'.$tipo.'.csv';
		        $tarifas = Array();

		        if($fp = fopen ($archivo , "r")){
		            while (( $data = fgetcsv ($fp, 1000, ";")) !== FALSE){
		                $tarifas[] = Array('servicio'	=> $data[0],
		                                   'pais'		=> $data[1],
		                                   'cp_origen'	=> $data[2],
		                                   'cp_destino'	=> $data[3],
		                                   'vcomp'  	=> $data[4],
		                                   'importe'	=> $data[5]
		                                  );
		            }

		            fclose ($fp);
		            return $tarifas;

		        }else return false;
		    }

		    /**** DEVUELVE LA TARIFA ADECUADA ****/
		    function dameTarifa($tipo, $servicio, $pais, $cp, $valor){ // peso | importe
		    	$tarifas = $this->tarifas($tipo);
		        $max=count($tarifas);
		        $cp=intval($cp);
		        $valor = ceil($valor);
		        $segmento = Array();

		        for($i=1;$i<$max;$i++){
		            // Si es un envio para ES-PT-AD
		            if($servicio == 'E24' || $servicio == 'E72'){
		                if($tarifas[$i]['servicio'] == $servicio){
		                    if($tarifas[$i]['pais'] == $pais){
		                        $cp_origen=intval($tarifas[$i]['cp_origen']);
		                        $cp_destino=intval($tarifas[$i]['cp_destino']);
		                        if($cp >= $cp_origen){
		                            if($cp <= $cp_destino){
		                                $segmento[]=Array('valor' => floatval($tarifas[$i]['vcomp']), 'precio' => floatval($tarifas[$i]['importe']));
		                            }
		                        }
		                    }
		                }
		            }else{ //Servico Europeo o Internacional
		                if($tarifas[$i]['servicio'] == $servicio){
		                    if($tarifas[$i]['pais'] == $pais){
		                        $segmento[]=Array('valor' => floatval($tarifas[$i]['vcomp']), 'precio' => floatval($tarifas[$i]['importe']));
		                    }
		                }
		            }
		        }

		        // Ordenamos el segmento
		        usort($segmento, ordenarAsoc('valor'));

		        // Preparamos los datos para el minimo y maximo
		        $precio_envio = 0;
		        $max=count($segmento);
		        $valor_min = floatval($segmento[0]['valor']);
		        $precio_min = floatval($segmento[0]['precio']);
		        $valor_max = floatval($segmento[$max-2]['valor']);
		        $precio_max = floatval($segmento[$max-2]['precio']);
		        $precio_despues_max = floatval($segmento[$max-1]['precio']);

		        if($valor <= $valor_min) $precio_envio = $precio_min;

		        // Sacamos precio
	    		for($i=0;$i<$max;$i++){
	    			if($valor != $segmento[$i]['valor']){
	    				if($valor < $segmento[$i]['valor']){
	    					$precio_envio = $segmento[$i]['precio'];
	    					$i=$max;
	    				}
	    			}else{ //es igual
	    				$precio_envio = $segmento[$i]['precio'];
	    				$i=$max;
	    			}
	    		}

		        // Calculamos el excedente según peso
		        if($tipo=='peso' && $valor > $valor_max){
		            $peso_restante = $valor-$valor_max;
		            $precio_restante = $peso_restante*$precio_despues_max;
		            $precio_envio += $precio_max+$precio_restante;
		        }

		        return $precio_envio;
		    }

		    /**** DETERMINA SI SE DEBE APLICAR IMPUESTOS A UN PAIS ****/
		    function bultosPorEnvio($numArticulos=10){
				$tipo_bultos = (boolean) obtenerOpcion('bultos_envio'); // 0 uno | 1 varible
				$num_articulos_bultos = (int) obtenerOpcion('articulos_bulto');
				return ($tipo_bultos>0)? ceil($numArticulos / $num_articulos_bultos):1;
		    }

			/**** DETERMINA SI SE DEBE APLICAR IMPUESTOS A UN PAIS ****/
		    function agregarImpuesto($pais){
		    	$string = str_replace(' ', '', obtenerOpcion('paises_iva'));
		    	$paises = explode(',', $string);
		        return in_array($pais, $paises);
		    }

		    /**** DETERMINA SI UN PAIS PERTENECE A LA PENÍNSULA ****/
		    function esPeninsular($pais){
		        $paises = Array("ES","PT","AD","GI");
		        return in_array($pais, $paises);
		    }

		    /**** DETERMINA SI UN PAIS PERTENECE A LA UE ****/
		    function esEuropeo($pais){
		        $paises = Array("DE","AT","BE","BG","CC","DK","SK","SI","EE","FI","FR","GR","GG","NL","HU","IE","IT","LV","LI","LT","LU","MC","NO","PL","GB","CZ","RO","SM","SE","CH","VA");
		        return in_array($pais, $paises);
		    }

		    /**** DETERMINA SI UN SERVICIO ES VALIDO PARA UN PAIS ****/
		    function servicioValido($servId, $pais){
				if (!$this->esPeninsular($pais) && ($servId=='E24' || $servId=='E72')) return false;
				else if ($this->esPeninsular($pais) && ($servId=='EEU' || $servId=='EWW')) return false;
				else if (!$this->esEuropeo($pais) && $servId=='EEU') return false;
				return true;
		    }

		    /**** DETERMINA SI UN ENVÍO ES GRATIS ****/
		    function esGratis($servId, $pais, $total){
				$servicio_grat_nacional = (string) obtenerOpcion('servicio_gratuito');
				$servicio_grat_internacional = (string) obtenerOpcion('servicio_gratuito_internacional');
				$importe_minimo_nacional = (float) (obtenerOpcion('importe_minimo_gratuito') > 0)? obtenerOpcion('importe_minimo_gratuito'):0;
				$importe_minimo_internacional = (float) (obtenerOpcion('importe_minimo_gratuito_internacional') > 0)? obtenerOpcion('importe_minimo_gratuito_internacional'):0;

				return (
					($this->esPeninsular($pais) && $servicio_grat_nacional===$servId && $total > $importe_minimo_nacional) ||
					($this->esEuropeo($pais) && $servicio_grat_internacional===$servId && $total > $importe_minimo_internacional) ||
					($servicio_grat_internacional==='EWW' && $servicio_grat_internacional===$servId && $total > $importe_minimo_internacional)
				)? true:false;
		    }

		    /**** DEVUELVE EL PRECIO DE UN PEDIDO MEDIANTE UN SERVICIO ****/
			function calcularCostesEnvio($servId, $pais, $cod_postal, $peso, $total, $numero){
				$tipo_tarifa = (boolean) obtenerOpcion('calcular_precio'); // 0 peso | 1 importe
				$impuesto = (float) (obtenerOpcion('impuesto') > 0)? obtenerOpcion('impuesto'):0;
				$coste_fijo = (float) (obtenerOpcion('coste_fijo') > 0)? obtenerOpcion('coste_fijo'):0;
				$tipo_manipulacion = (boolean) obtenerOpcion('manipulacion'); // 0 fijo | 1 varible
				$coste_manipulacion = (float) ($tipo_manipulacion)? (($total * intval(obtenerOpcion('manipulacion_margen')))/100):floatval(obtenerOpcion('manipulacion_coste_fijo'));

				if($peso<1) $peso=1;

				// Inizializamos el importe del envio a 0
				$coste_envio = 0.00;

				//Envio gratis
				$envio_gratis = $this->esGratis($servId, $pais, $total);

				if (!$envio_gratis){ // El envio no es gratis
					if($coste_fijo>0){ // El envio tiene un precio fijo

						$coste_envio = $coste_fijo;

					}else{

						//Si no hay coste_fijo buscamos en el csv el precio
						//necesitamos el tipo_servicio, cp_cliente, pais y peso
						if($tipo_tarifa) $coste_envio = $this->dameTarifa('importe', $servId, $pais, $cod_postal, $total);
						else $coste_envio = $this->dameTarifa('peso', $servId, $pais, $cod_postal, $peso);

						//Sumamos el MARGEN SOBRE COSTE DE ENVÍO
						$coste_envio += $coste_manipulacion;

					}

					$coste_envio *= $this->bultosPorEnvio($numero); // Multiplicamos el coste por el número de bultos
					if ($this->agregarImpuesto($pais)) $coste_envio += ($coste_envio * $impuesto)/100; // Sumamos los impuestos si procede
				}

				return $coste_envio;
			}
		}
	}

	function extrasPanel(){
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/envialia-carrier-for-woocomerce';
		$plugin = obtenerInformacionPlugin(ENVIALIA_PLUGIN_ID, ENVIALIA_PLUGIN_TABLE);
		$rate = esc_attr(str_replace(',', '.', $plugin['rating']));

		return '<div id="envialiaExtras">

				    <p>Por favor, vota el plugin</p>
				    <div class="star-holder rate">
						<div style="width: '.$rate.'px" class="star-rating"></div>
						<div class="star-rate">
							<a title="Malo" href="'.$rate_url.'?rate=1#postform" target="_blank"><span></span></a>
							<a title="Funciona" href="'.$rate_url.'?rate=2#postform" target="_blank"><span></span></a>
							<a title="Bueno" href="'.$rate_url.'?rate=3#postform" target="_blank"><span></span></a>
							<a title="Muy Bueno" href="'.$rate_url.'?rate=4#postform" target="_blank"><span></span></a>
							<a title="Fantástico" href="'.$rate_url.'?rate=5#postform" target="_blank"><span></span></a>
				    	</div>
				    </div>

				    <br/><hr/><br/>

					<p class="justify">Si te parece útil considera hacer una donación. Hemos invertido mucho tiempo y esfuerzo en el</p>

					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="D2VA3BETM3RYQ">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
					</form>

					<br/><hr/><br/>

					<!-- p class="justify">Disponemos de una <b>versión premium</b> con más opciones de personalización que incluye actualizaciones, configuración y soporte.</p>

					<!-- p class="center"><a href="https://netsis.es/downloads/envialia-woocommerce-plugin/" class="button" target="_blank">Envialia Premium</a></p -->

				</div>

				<script>
				    hasScrollBar = function() {
				        return jQuery("body").get(0).scrollHeight > jQuery("body").height();
				    }

					function redimensionar(){
						var altoVentana = jQuery(window).height();
						var altoPanel = jQuery("#envialiaExtras").outerHeight();
						var restante = (altoVentana - altoPanel) / 2;

						if (restante>80 && hasScrollBar()){
							restante += 60;
							jQuery("#envialiaExtras").css({"position": "fixed", "top": restante+"px"});
						}else{
							jQuery("#envialiaExtras").css({"position": "relative", "top": "50px"});
						}
					}

					jQuery(window).on("load resize", redimensionar)
				</script>';
	}
/* FIN FUNCIONES */