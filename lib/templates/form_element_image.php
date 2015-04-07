<div class="sy-form-image well {{ class }}" id="{{ id }}">
  {% if imageUrl is sameas(false) %}
  <div class="row">
    <div class="col-md-12">
      <span class="help-block">
        <span class="glyphicon glyphicon-remove"></span> File is missing. Value will be set to empty on save.
      </span>
    </div>
  </div>
  {% endif %}

  <div class="row">
    <div class="col-xs-8 col-md-10">
      <input type="file" name="{{ inputName }}[file]" value="{{ value }}" class="form-control"/>
      <input type="hidden" name="{{ inputName }}[delete]" value=""/>
    </div>
    <div class="thumb-container col-xs-4 col-md-2">
      {% if imageUrl %}
      <div class="thumbnail">
        <a href="{{ imageUrl }}" class="lightbox"><img src="{{ thumbUrl }}"/></a>
        <div>
          <p><a href="#" class="btn btn-delete"><span class="glyphicon glyphicon-remove"></span> Remove</a></p>
        </div>
      </div>
      {% endif %}
    </div>
  </div>
</div>