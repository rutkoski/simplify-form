<div class="composite">
{% for element in elements %}
  {% if element['label'] != false %}
  <div class="control-group {{ element['class'] }}" id="{{ element['id'] }}">
    <label class="control-label" for="{{ element['name'] }}">{{ element['label'] }}</label>
    <div class="controls">
      {{ element['controls'] }}
    </div>
  </div>
  {% else %}
    {{ element['controls'] }}
  {% endif %}
{% endfor %}
</div>