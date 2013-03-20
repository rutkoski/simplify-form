<div class="container-fluid well <?= $class ?>" id="<?= $id ?>">
  <div class="row-fluid">
    <div class="span3">
      <input id="<?= $id ?>-upload" name="<?= $name ?>[input]" type="file"/>
    </div>

    <div class="span9">
      <ul class="thumbnails">
        <?php foreach ($data as $row) { ?>
        <li style="width:100px; position:relative;" class="img-polaroid">
          <input type="hidden" name="<?= $row['name'] ?>[_id]" value="<?= $row['_id'] ?>"/>
          <input type="hidden" name="<?= $row['name'] ?>[filename]" value="<?= $row['image_filename'] ?>"/>

          <a href="<?= $row['image_src'] ?>" class="lightbox"><img src="<?= $row['thumb_src'] ?>"/></a>

          <a href="#" class="btn-delete"><i class="icon-trash"></i></a>
          <a href="#<?= $row['_id'] ?>-lightbox" class="btn-edit lightbox"><i class="icon-edit"></i></a>

          <div class="popup-holder" style="display:none;">
            <div class="popup" id="<?= $row['_id'] ?>-lightbox">
              <?php foreach ($row['elements'] as $element) { echo $element; } ?>
            </div>
          </div>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="dummy" style="display:none;">
    <li style="width:100px; position:relative;" class="img-polaroid">
      <input type="hidden" name="<?= $dummy['name'] ?>[_id]" value=""/>
      <input type="hidden" name="<?= $dummy['name'] ?>[filename]" value=""/>

      <a href="" class="btn-lightbox lightbox"><img src=""/></a>

      <a href="#" class="btn-delete"><i class="icon-trash"></i></a>
      <a href="#<?= $dummy['id'] ?>-lightbox" class="btn-edit lightbox"><i class="icon-edit"></i></a>

      <div class="popup-holder" style="display:none;">
        <div class="popup" id="<?= $dummy['id'] ?>-lightbox">
          <?php foreach ($dummy['elements'] as $element) { echo $element; } ?>
        </div>
      </div>
    </li>
  </div>
</div>

<?= $this->html->css('../uploadify/uploadify.css') ?>
<?= $this->html->js('swfobject') ?>
<?= $this->html->js('../uploadify/jquery.uploadify.min.js') ?>

<script>
(function() {
  var id = '<?= $id ?>';
  var jsonData = '<?= $jsonData ?>';
  var n = 0;

  $('#'+id+'-upload').uploadify({
    formData: { sid: '<?= session_id() ?>' },

    method: 'get',

    onUploadError: function(file, errorCode, errorMsg, errorString) {
      alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
    },

    onUploadSuccess: function(file, data, response) {
      data = $.parseJSON(data);

      if (data.error) {
        alert(data.error);
      } else {
        addImage(data.image);
      }
    },

    swf: '<?= s::config()->get('theme_url') ?>/uploadify/uploadify.swf',
    uploader: '<?= $uploaderUrl ?>',
  });

  function addImage(data)
  {
    var li = $('#'+id+' .dummy li').clone();

    n++;

    li.find(':input').each(function() {
      $(this).attr('name', $(this).attr('name').replace(/dummy/, 'new-' + n));
    });

    li.find(':input[name$=\\[_id\\]]').val('new-' + n);
    li.find(':input[name$=\\[filename\\]]').val(data.image_filename);

    li.find('.btn-lightbox').attr('href', data.image_src).find('img').attr('src', data.thumb_src);

    $('#'+id+' .thumbnails').append(li);

    $('.lightbox').fancybox();
  }

  $('#' + id + ' .btn-delete').click(function() {
    $(this).parent().remove();
  });

  $('.lightbox').fancybox();

  <?php if ($sortable) { ?>
  $('#' + id + ' .thumbnails').sortable();
  <?php } ?>
})();
</script>