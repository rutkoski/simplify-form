<input id="{{ id }}" type="text" name="{{ inputName }}" value="{{ formatedValue }}" class="form-control" {{ disabled ? 'disabled' : '' }}/>

<script>
$(document).ready(function() {
	$('input#{{ id }}').datetimepicker({
		locale: 'pt-br'
	});
});
</script>