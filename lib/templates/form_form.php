<h3>{{ title }}</h3>

<form action="{{ formUrl }}" method="POST" enctype="multipart/form-data" class="form-horizontal" role="form">
  {% for row in data %}
    <input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" class="_id"/>

    {% for element in row['elements'] %}
      {% include 'form_row.php' %}
    {% endfor %}
    
    {% if row['menu'] %}
      <div class="form-group">
        <div class="col-sm-12 text-right">
        {% include 'form_menu.php' with { 'menu' : row['menu'] } %}
        </div>
      </div>
    {% endif %}
  {% endfor %}

  <div class="col-sm-offset-2 col-sm-10">
    <div class="form-group">
      <button type="submit" class="btn btn-primary">
        <span class="glyphicon glyphicon-ok"></span>
        Save
      </button>
    </div>
  </div>
</form>

{% if formMode == 'ajax' %}
<script>
(function() {
	$(document).ready(init);

	function init() {
		  $('form').submit(onSubmit);
	}

	function onSubmit(e) {
		var $this = $(this), url, data;

		e.preventDefault();

		url = '{{ formAjaxUrl }}';
		data = $this.find(':input');
		
		$.post(url, data, function(response) {
			if (response.data) {
				$(':input._id').each(function(k, v) {
					$(this).val(response.data[k]._id);
				});
			}
		});
	}
}());
</script>
{% endif %}