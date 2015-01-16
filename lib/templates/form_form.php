<h3>{{ title }}</h3>

<form action="" method="POST" enctype="multipart/form-data" class="form-horizontal" role="form">
  {% for row in data %}
    <input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}"/>

    {% for element in row['elements'] %}
      {% if element['label'] != false %}
        <div class="form-group {{ element['class'] }} {{ element['state'] }}" id="{{ element['id'] }}">
          <label class="col-sm-2 control-label" for="{{ element['name'] }}">{{ element['label'] }}</label>
          <div class="col-sm-10">
            {{ element['controls'] }}

            {% if element['stateMessage'] %}
              <span class="help-block">{{ element['stateMessage'] }}</span>
            {% endif %}
          </div>
        </div>
      {% else %}
        {{ element['controls'] }}
      {% endif %}
    {% endfor %}

    {% if menu %}
      {% include 'form_menu.php' with { 'menu' : row['menu'].getItemAt(0) } %}
    {% endif %}
  {% endfor %}

  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>