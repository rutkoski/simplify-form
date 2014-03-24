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
            <img src="<?= $row['thumbUrl'] ?>" class="image" />

            <div class="caption">
              <p>
                <a href="<?= $row['imageUrl'] ?>" class="lightbox"><i class="icon-zoom-in"></i></a>
                <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
                <?php if (! empty($row['elements'])) { ?>
                <a href="#" class="btn-edit"><?= $this->icon->show('edit') ?></a>
                <?php } ?>
              </p>
            </div>

            <?php if (! empty($row['elements'])) { ?>
            <div style="display:none;">
              <div class="extra-fields">
                <?php foreach ($row['elements'] as $i => $element) { ?>
                  <?php if ($element['label'] !== false) { ?>
                  <div class="control-group <?= $element['class'] ?>" id="<?= $element['id'] ?>">
                    <label class="control-label" for="<?= $element['name'] ?>"><?= $element['label'] ?></label>
                    <div class="controls">
                      <?= $element['controls'] ?>
                    </div>
                  </div>
                  <?php } else { ?>
                  <?= $element['controls'] ?>
                  <?php } ?>
                <?php } ?>
                <input type="submit" value="Close" class="btn btn-primary" onclick="$.fancybox.close();" />
              </div>
            </div>
            <?php } ?>
          </div>

          <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>" />
          <input type="hidden" name="<?= $row['baseName'] ?>[filename]" value="<?= $row['filename'] ?>"/>
        </li>
        <?php } ?>

        <li class="dummy" style="display: none;">
          <div class="img-polaroid">
            <img src="" class="image" />

            <div class="caption">
              <p>
                <a href="" class="lightbox"><i class="icon-zoom-in"></i></a>
                <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
                <?php if (! empty($dummy['elements'])) { ?>
                <a href="#" class="btn-edit"><?= $this->icon->show('edit') ?></a>
                <?php } ?>
              </p>
            </div>

            <?php if (! empty($dummy['elements'])) { ?>
            <div style="display:none;">
              <div class="extra-fields">
                <?php foreach ($dummy['elements'] as $i => $element) { ?>
                  <?php if ($element['label'] !== false) { ?>
                  <div class="control-group <?= $element['class'] ?>" id="<?= $element['id'] ?>">
                    <label class="control-label" for="<?= $element['name'] ?>"><?= $element['label'] ?></label>
                    <div class="controls">
                      <?= $element['controls'] ?>
                    </div>
                  </div>
                  <?php } else { ?>
                  <?= $element['controls'] ?>
                  <?php } ?>
                <?php } ?>
                <input type="submit" value="Close" class="btn btn-primary" onclick="$.fancybox.close();" />
              </div>
            </div>
            <?php } ?>
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

  $(_id).on('click', '.btn-edit', function() {
    $.fancybox.open({
      content: $(this).parents('li').find('.extra-fields'),
      modal: true
    });
  });

  <?php if ($sortable) { ?>
  $(_id + ' .thumbnails').sortable();
  <?php } ?>

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

    el.find(':input').removeAttr('disabled').not(':submit').each(function() {
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