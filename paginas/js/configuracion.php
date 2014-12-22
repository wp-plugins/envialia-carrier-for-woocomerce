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

	jQuery('.nav-tab-wrapper a').click(function(e){
		e.preventDefault();
		var link = jQuery(this);
		var section = link.attr('data-target');
		if (typeof window.history.pushState != 'undefined') window.history.pushState(section, 'Configuración de Envialia', './admin.php?page=envialia-carrier-options&tab=' + section);

		jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
		jQuery('table.form-table').hide();
		jQuery('table[data-section=' + section + ']').show();
		jQuery(this).addClass('nav-tab-active');
	});

	window.onpopstate = function(e){
		if (e.state!=null){
			jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
			jQuery('table.form-table').hide();
			jQuery('table[data-section=' + e.state + ']').show();
			jQuery('a[data-target=' + e.state + ']').addClass('nav-tab-active');
		}
	};

	function enableDisableItems(){
		var section = '<?php echo activeTab() ?>';
		if (typeof window.history.replaceState != 'undefined') window.history.replaceState(section, 'Configuración de Envialia', './admin.php?page=envialia-carrier-options&tab=' + section);

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