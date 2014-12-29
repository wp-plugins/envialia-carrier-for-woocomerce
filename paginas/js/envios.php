<script>
	jQuery('a.prompt').click(function(e){
	    var ask=confirm('¿Estás seguro/a ?');
	    if(!ask) e.preventDefault();
	});
</script>