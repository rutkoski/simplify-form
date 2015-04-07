<h3>{{ title }}</h3>

<form action="" method="POST" class="form-horizontal" role="form">
{% for row in data %}
  {% for element in row['elements'] %}
    {% if element['label'] != false %}
      <div class="form-group {{ element['class'] }} {{ element['state'] }}" id="{{ element['id'] }}">
        <label class="col-sm-2 control-label" for="{{ element['name'] }}">{{ element['label'] }}</label>
        <div class="col-sm-10 help-block">
          {{ element['controls'] }}
        </div>
      </div>
    {% else %}
      <div class="col-sm-12 help-block">
        {{ element['controls'] }}
      </div>
    {% endif %}
  {% endfor %}

  {% if row['menu'] %}
    {% include 'form_menu.php' with { 'menu' : row['menu'].getItemAt(0) } %}
  {% endif %}
{% endfor %}
</form>