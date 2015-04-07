<div class="control-group">
  <label for="{{ name }}">{{ label }}</label>
  <div class="controls">
    {% for option, label in options %}
    <label class="radio inline">
      <input type="checkbox" name="{{ name }}[]" value="{{ option }}"{{ editable ? '' : ' disabled' }}{{ option is in_array(value) ? ' checked' : '' }}/>
      {{ label }}
    </label>
    {% endfor %}
  </div>
</div>