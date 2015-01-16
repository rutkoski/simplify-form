{% if display == 'pills' %}
<ul class="nav nav-pills">
  {% for item in menu.items() %}
  <li><a href="{{ item.url }}" title="{{ item.label }}">{{ item.label }}</a></li>
  {% endfor %}
</ul>
{% else %}
<div class="btn-group">
  {% for item in menu.items() %}
  <a href="{{ item.url }}" title="{{ item.label }}" class="btn btn-default">{{ item.label }}</a>
  {% endfor %}
</div>
{% endif %}