<div class="sy-form-images well {{ class }}" id="{{ id }}">
  <div class="row">
    <div class="col-md-12">
    
      <div class="uploader">
        <input type="file" name="{{ name }}" id="{{ uploaderId }}" />
      </div>
      
      <div class="images">
        {% for i, row in data %}
        <div class="image thumbnail col-md-2">
          <a href="{{ row['imageUrl'] }}" class="lightbox"><img src="{{ row['thumbUrl'] }}"/></a>
          
          <div>
            <p><a href="#" class="btn btn-delete"><span class="glyphicon glyphicon-remove"></span> Remove</a></p>
          </div>
          
          <input type="hidden" name="{{ row['name'] }}" value="{{ row['_id'] }}" />
          <input type="hidden" name="{{ row['baseName'] }}[filename]" value="{{ row['filename'] }}"/>
        </div>
        {% endfor %}
      </div>
      
      <div class="dummy thumbnail col-md-2" style="display: none;">
        <a href="" class="lightbox"><img src=""/></a>
        
        <div>
          <p><a href="#" class="btn btn-delete"><span class="glyphicon glyphicon-remove"></span> Remove</a></p>
        </div>
        
        <input type="hidden" name="{{ dummy['name'] }}" value=""/>
        <input type="hidden" name="{{ dummy['baseName'] }}[filename]" value=""/>
      </div>
    </div>
  </div>
</div>

<script src="{{ config.get('theme_url') }}scripts/uploadify/jquery.uploadify.js"></script>

<script>
$(function() {
	  var $elem = $('#{{ id }}'), $dummy = $('.dummy', $elem), n = 0;

	  $(':input', $dummy).attr('disabled', 'disabled');
		
    $('#{{ uploaderId }}').uploadify({
        'fileObjName' : '{{ name }}',
        'swf'      : '{{ config.get('theme_url') }}scripts/uploadify/uploadify.swf',
        'uploader' : '{{ uploaderUrl }}',
        'onUploadSuccess' : function(file, data, response) {
            addImage($.parseJSON(data));
        }
    });

    $elem.on('click', '.btn-delete', function() {
      $(this).parents('.image').first().remove();
    });

    {% if sortable %}
    $('.images', $elem).sortable();
    {% endif %}
    
    function addImage(data) {
        var $image = $dummy.clone().removeClass('dummy');

        $('.lightbox', $image).attr('href', data.image.imageUrl);
        $('.lightbox img', $image).attr('src', data.image.thumbUrl);
        $('.images', $elem).append($image);

        ++n;

        $(':input[name$=\\[_id\\]]', $image).val('new-' + n);
        $(':input[name$=\\[filename\\]]', $image).val(data.image.filename);

        $image.find(':input').removeAttr('disabled').not(':submit').each(function() {
          $(this).attr('name', $(this).attr('name').replace(/dummy/, 'new-' + n));
        });
    		
        $image.show();
    }
});
</script>