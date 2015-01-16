<input type="text" name="{{ inputName }}" value="{{ formatedValue }}"/>
<script>
(function() {
  $('#{{ id }} input').datetimepicker();
})();
</script>