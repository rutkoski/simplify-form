{% if action.name == 'view' %}
  <div class="tabbable">
    <ul class="nav nav-tabs">
      {% for row in data %}
      <li class="{{ loop.first ? 'active' : '' }}">
        <a href="#tab{{ loop.index }}" data-toggle="tab"> {{ label }} ({{ loop.index }})</a>
      </li>
      {% endfor %}
    </ul>
    <div class="tab-content well">
      {% for row in data %}
      <div class="tab-pane{{ loop.first ? ' active' : '' }}" id="tab{{ loop.index }}">
        {% for element in row['elements'] %}
          {% include 'form_row.php' %}
        {% endfor %}
      </div>
      {% endfor %}
    </div>
  </div>
{% else %}
  <div class="tabbable">
    <ul class="nav nav-tabs">
      {% for row in data %}
      <li class="{{ loop.first ? 'active' : '' }}">
        <a href="#tab{{ row['_id'] }}" data-toggle="tab"> {{ label }} ({{ row['_id'] }})</a>
      </li>
      {% endfor %}
  
      <li class="dummy">
        <a href="#tab-0" class="btn-create" data-toggle="tab">
          <span class="glyphicon glyphicon-plus"></span>
        </a>
      </li>
    </ul>
  
    <div class="tab-content well">
      {% for row in data %}
      <div class="tab-pane{{ loop.first ? ' active' : '' }}" id="tab{{ row['_id'] }}">
        <input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" class="_id" />
  
        {% for element in row['elements'] %}
          {% include 'form_row.php' %}
        {% endfor %}

        {% include 'form_menu.php' with { 'menu' : row['menu'] } %}
      </div>
      {% endfor %}
  
      <div class="tab-pane dummy" id="tab-0">
        <input type="hidden" name="{{ dummy['name'] }}" value="" />
  
        {% for element in dummy['elements'] %}
          {% include 'form_row.php' %}
        {% endfor %} {% include 'form_menu.php' with { 'menu' :
        dummy['menu'] } %}
      </div>
    </div>
  </div>
  
  <script>
  $(function() {
    var n = 0, label = '{{ label }}', id = '{{ id }}', $elem = $('#' + id),
        $dummyTab = $elem.find('.nav .dummy'); 
        $dummyPane = $elem.find('.tab-content .dummy');
  
    $dummyPane.find(':input').attr('disabled', 'disabled');
  
    $elem.on('click', '.btn-delete', onDelete);
  
    $dummyTab.on('click', onCreate);
  
    function onDelete(event) {
  	  event.preventDefault();
  	  
  	  var $this = $(this),
  	      $pane = $this.parents('.tab-pane'),
          $tab = $this.parents('.tabbable').find('ul a[href=#' + $pane.attr('id') + ']').parents('li');
  
      if ($tab.prev().length) {
      	$tab.prev().find('a').tab('show');;
      } else if ($this.parents('.tabbable').find('ul:first li').length > 2) {
      	$tab.next().find('a').tab('show');;
      }
  
      $pane.remove();
      $tab.remove();
    }
  
    function onCreate(event) {
  	  var $new;
  
  	  event.preventDefault();
  	  
      ++n;
  
      var $tab = $dummyTab.clone().removeClass('dummy');
      var $pane = $dummyPane.clone().removeClass('dummy');
  
      $tab.insertBefore($dummyTab);
      $tab.find('a').html(label + ' (+)');
  
      $pane.insertBefore($dummyPane);
      $pane.attr('id', 'tab-' + n);
  
      $pane.find(':input').each(function() {
        var $this = $(this);
        $this.removeAttr('disabled');
        $this.attr('name', $this.attr('name').replace(/dummy/, 'new-' + n));
      });
  
      $tab.find('a').attr('href', '#tab-' + n).tab('show');
  
      return false;
    }
  
  });
  </script>
{% endif %}