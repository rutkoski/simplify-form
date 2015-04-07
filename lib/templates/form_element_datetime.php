<input id="{{ id }}" type="text" name="{{ inputName }}" value="{{ formatedValue }}"/>

<script>
$(document).ready(function() {
	$('input#{{ id }}').datetimepicker({
		locale: 'pt-br'
	});
});
</script>