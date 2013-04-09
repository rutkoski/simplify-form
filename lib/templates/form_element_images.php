<div class="<?= $class ?>" id="<?= $id ?>">
  <div class="row-fluid">
    <div class="span12">
      <div id="<?= $uploaderId ?>"></div>
    </div>
  </div>

  <div class="row-fluid">
    <div class="span12">
      <ul class="thumbnails">
        <?php foreach ($data as $i => $row) { ?>
        <li>
          <div class="img-polaroid">
            <img src="<?= $row['thumbUrl'] ?>" />

            <div class="caption">
              <p>
                <a href="<?= $row['imageUrl'] ?>" class="lightbox"><i class="icon-zoom-in"></i></a>
                <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
              </p>
            </div>
          </div>

          <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>" />
          <input type="hidden" name="<?= $row['baseName'] ?>[filename]" value="<?= $row['filename'] ?>"/>
        </li>
        <?php } ?>

        <li class="dummy" style="display: none;">
          <div class="img-polaroid">
            <img src="" class="image" style="width: 100px; height: 100px;" />

            <div class="caption">
              <p>
                <a href="" class="lightbox"><i class="icon-zoom-in"></i></a>
                <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
              </p>
            </div>
          </div>

          <input type="hidden" name="<?= $dummy['name'] ?>" value=""/>
          <input type="hidden" name="<?= $dummy['baseName'] ?>[filename]" value=""/>
        </li>
      </ul>
    </div>
  </div>
</div>

<?= $this->html->js('/fineuploader/jquery.fineuploader-3.4.1.min.js')?>

<script>
$(function() {
  var _id = '#<?= $id ?>';
  var n = 0;

  var dummy = $(_id + ' .dummy');

  $(_id + ' .dummy :input').attr('disabled', 'disabled');

  $(_id).on('click', '.btn-delete', function() {
    $(this).parents('li').first().remove();
  });

  $('#<?= $uploaderId ?>').fineUploader({
    request: {
      endpoint: '<?= $uploaderUrl ?>',
      inputName: '<?= $name ?>'
    }
  })
  .on('error', function(event, id, name, reason) {
     //do something
  })
  .on('complete', function(event, id, name, response){
    var el = dummy.clone().removeClass('dummy');

    $(_id + ' .qq-upload-list > li').eq(id).fadeOut();

    ++n;

    el.find(':input').removeAttr('disabled').each(function() {
      $(this).attr('name', $(this).attr('name').replace(/dummy/, 'new-' + n));
    });

    el.find(':input[name$=\\[_id\\]]').val('new-' + n);
    el.find(':input[name$=\\[filename\\]]').val(response.image.filename);

    el.find('a.lightbox').attr('href', response.image.imageUrl);
    el.find('img.image').attr('src', response.image.thumbUrl);

    el.show();

    dummy.before(el);
  });
});
</script>