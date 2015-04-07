<h2>{{ title }}</h2>

{% if menu %}
{% include 'form_menu.php' with { 'menu' : menu } %}
{% endif %}

{{ actionBody }}