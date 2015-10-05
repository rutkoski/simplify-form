<h3>{{ title }}</h3>

{% if filters %}
<form action="" method="GET" class="form-inline">
  <fieldset>
    <legend>Filters</legend>

    {% for filter in filters %}
      {{ filter['controls'] }}
    {% endfor %}

    <input type="submit" value="Apply" class="btn"/>
  </fieldset>
</form>
{% endif %}

<form action="" method="GET">
  {% include 'form_pagination.php' with { 'pager' : pager } %}

  <table class="table table-condensed">
    <thead>
      <tr>
        <th style="width:1%;"></th>

        {% for header in headers %}
        <th>{{ header }}</th>
        {% endfor %}

        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      {% for row in data %}
      <tr>
        <td><input type="checkbox" name="{{ row['name'] }}" value="{{ row['_id'] }}"/></td>

        {% for element in row['elements'] %}
        <td>{{ element['controls'] }}</td>
        {% endfor %}

        <td class="sy-form-list-action-menu">
          <div class="nowrap-flex">
          {% include 'form_menu.php' with { 'menu' : row['menu'], 'hideLabels' : true } %}
          </div>
        </td>
      </tr>
      {% endfor %}
    </tbody>
  </table>

  {% include 'form_pagination.php' with { 'pager' : pager } %}

  {% if bulk %}
  <div class="form-group">
    <div class="input-group col-sm-4">
      <span class="input-group-addon">Actions</span>

      <select name="formAction" class="form-control">
        <option value=""></option>
        {% for value, label in bulk %}
        <option value="{{ value }}">{{ label }}</option>
        {% endfor %}
      </select>

      <span class="input-group-btn">
        <input type="submit" value="Ok" class="btn" />
      </span>
    </div>  
  </div>
  {% endif %}
</form>

<style>
.sy-form-list-action-menu .nowrap-flex, .sy-form-list-action-menu .btn-group {
  display: flex;
}
</style>