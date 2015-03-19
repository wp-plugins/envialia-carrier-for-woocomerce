=== Envialia Carrier ===
Contributors: netsisEstudio
Donate link: http://netsis.es/section/donations.html
Tags: envialia, woocommerce, carrier, send order
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 2.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Todo lo que necesitas si trabajas con la empresa de transporte Envialia

== Description ==

**IMPORTANTE: *Envialia Carrier* requiere WooCommerce 2.1.0 o superior.**

Este plugin tiene dos funciones principales:

*Para sus clientes - Métodos de envío (FRONTEND)*

Muestra al cliente las distintas opciones de envío y su precio dependiendo de donde viva mediante las tarifas de Envialia durante el proceso de pago o checkout.

= Características =

* Cálculo de tarifa según tramos de peso o mediante el importe de la compra 
* Permite que el cliente elija entre los servicios Envialia 24h y Envialia 72h o entre Envialia Europe Express y Envialia Worlwide o muestra directamente el más económico sugún sus preferencias.
* Los servicios sólo se muestran en todo caso si están disponibles en el país del cliente.
* Seguimiento del pedido

*Para su negocio - Gestión integrada con Envialia (BACKEND)*

Complete un pedido en WooCommerce y después pulse el botón del camión (Enviar) y serán tramitado directamente con Envialia, sin introducir ningún dato.
El pedido pasará al estado "Enviado" (o Sended) y a partir de ese momento el cliente verá un botón en el resumen de pedidos de su cuenta para hacer el seguimiento del paquete.
En el panel de administrador de Envialia podrá generar automáticamente las etiquetas del paquete, hacer un seguimiento o cancelarlo si el transportista aún no ha salido a recogerlo.

= Características =

* Configuración de los servicios activos que verán sus clientes
* Configurar un precio fijo para todos los envíos
* Añade un nuevo estado para los pedidos, Enviado o Sended
* Añade un botón a los pedidos completados para tramitarlos con Envialia
* Genera las etiquetas que deberá pegar en su paquete
* Ver el estado de un envío en cualquier momento
* Puede cancelar la recogida, si el transportista aún no ha salido
* Puede comprobar si el precio de los envíos es correcto mediante la opción de Simulador

*Extras en la versión premium (BACKEND)*

* Permite configurar el envío gratuito mediante un servicio a partir de cierto importe de compra
* Puede calcular el número de bultos necesarios para un envío e incrementar el coste
* Es posible establecer un coste fijo de embalaje/manipulación o un margen sobre el coste del envío
* Permite enviar un e-mail personalizado a su cliente para que haga el seguimiento del paquete
* Sin nuestra publicidad, sólo se menciona a la empresa de transporte Envialia

= Notas =
* Para la funcionalidad de gestión integrada es necesario disponer de un usuario en el API de Envialia y el servidor tiene que tener habilitada la librería **cURL**, puede comprobar si su hosting cumple los requisitos instalándolo y yendo a la sección **Estado** dentro del plugin.

Plugin Envialia Carrier Premium: [Netsis Market](https://netsis.es/downloads/envialia-woocommerce-plugin/)
Visita nuestra web: [netsis.es](https://netsis.es/)

== Installation ==

1. Sube `envialia-carrier-for-woocomerce` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' de WordPress
3. Configurar y usar

== Frequently Asked Questions ==

= ¿Funciona con otras empresas de transporte? =

No, es exclusivo para Envialia

== Screenshots ==

1. Panel de Envialia
2. Configuración del plugin
3. Información sobre el estado del plugin

== Changelog ==

= 2.8 =
* Corregido conflicto con un nombre de variable.

= 2.7 =
* Ampliado a 20 el número de caracteres para Centro de servicio, Código cliente y Password.

= 2.6 =
* Correcciones en avisos internos.

= 2.5 =
* Actualizado para que funcione con Woocommerce 2.2 - Corregido estado de pedido (Order status)

= 2.4 =
* Solucionado un bug en los envíos gratuitos

= 2.3 =
* ¡Nueva función 'Simulador'!
* Añadida pestaña de configuración avanzada
* Comprueba si Woocommerce está instalado en la página de 'Estado'
* El Administrador puede seleccionar un servicio Envialia si el cliente no lo ha hecho
* Corregido un error al calcular las tarifas
* Corregida desinstalación

= 2.2 =
* Corregida agencia errónea en etiqueta

= 2.1 =
* Corregido nombre ID erróneo

= 2.0 =
* ¡Añadida funcionalidad en el área del cliente!
* Más opciones
* Mejor organización
* Nuevas capturas de pantalla

= 1.2 =
* ¡Corregido directorio de instalación erróneo!

= 1.1 =
* Corregido desarrollador
* Corregidas capturas de pantalla

= 1.0 =
* Versión inicial
