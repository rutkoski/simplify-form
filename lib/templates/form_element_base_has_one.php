<div class="has_one">
  <input type="hidden" name="{{ data['name'] }}" value="{{ data['_id'] }}" />
  {% for element in data['elements'] %}
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