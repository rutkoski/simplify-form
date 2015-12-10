{% for option, label in options['options'] %}
<div class="checkbox">
  <label>
    <input type="checkbox" name="{{ inputName }}[]" value="{{ option }}" {{ option in options['checked'] ? 'checked' : '' }}>
    {{ label }}
  </label>
</div>
{% endfor %}

{% if useAjax %}
<script>
$(document).ready(function() {
  $(':checkbox[name={{ jsName }}]').change(function() {
    var url = '{{ ajaxUrl }}';

    var data = {
      '_id' : '{{ _id }}',
      '{{ name }}' : $(this).val()
    }

    //$.amp.loadBegin();

    $.post(url, data, function(response) {
      console.log(url, data, response);
      //$.amp.loadEnd();
    });
  });
});
</script>
{% endif %}