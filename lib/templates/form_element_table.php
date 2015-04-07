{% if action.name == 'view' %}
  <h4>{{label}}</h4>
  <table class="table table-condensed">
    <thead>
      <tr>
        {% for element in dummy['elements'] %}
        <th>{{ element['label'] }}</th>
        {% endfor %}
      </tr>
    </thead>
  
    <tbody>
      {% for i, row in data %}
      <tr>
        {% for element in row['elements'] %}
        <td>{{ element['controls'] }}</td>
        {% endfor %}
        <td><input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" class="_id" />
      </tr>
      {% endfor %}
    </tbody>
  </table>
{% else %}
  <table class="table table-condensed">
    <thead>
      <tr>
        {% for element in dummy['elements'] %}
        <th>{{ element['label'] }}</th> {% endfor %}
        <th>Actions</th>
      </tr>
    </thead>
  
    <tbody>
      {% for i, row in data %}
      <tr>
        {% for element in row['elements'] %}
        <td>{{ element['controls'] }}</td> {% endfor %}
  
        <td><input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" class="_id" />
        {% include 'form_menu.php' with { 'menu' : row['menu'] } %}
      </tr>
      {% endfor %}
  
      <tr class="dummy" style="display: none;">
        {% for element in dummy['elements'] %}
        <td>{{ element['controls'] }}</td> {% endfor %}
  
        <td><input type="hidden" name="{{ dummy['name'] }}" value="" class="_id" />
        {% include 'form_menu.php' with { 'menu' : dummy['menu'] } %}
        </td>
      </tr>
    </tbody>
  </table>
  
  {% include 'form_menu.php' with { 'menu' : menu } %}
  
  <script>
  $(function() {
    var id = '{{ id }}', n = 0, $elem = $('#' + id), $dummy = $elem.find('.dummy');
  
    $elem.find('.dummy :input').attr('disabled', 'disabled');
  
    $elem.on('click', '.btn-delete', onDelete);
    $elem.on('click', '.btn-create', onCreate);
    
    function onDelete(event) {
  	  event.preventDefault();
  	  
      $(this).parents('tr').remove();
    }
  
    function onCreate(event) {
  	  var $new;
  
  	  event.preventDefault();
  	  
      ++n;
  
      $new = $dummy.clone().removeClass('dummy');
  
      $new.find('._id').val('new-' + n);
      
      $new.find(':input').removeAttr('disabled').each(function() {
        var $this = $(this);
        $this.attr('name', $this.attr('name').replace(/dummy/, 'new-' + n));
      });
  
      $new.show();
  
      $elem.find('table tbody').append($new);
    }
  
  });
  </script>
{% endif %}