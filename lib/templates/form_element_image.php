<div class="container-fluid well <?= $class ?>" id="<?= $id ?>">
  <?php if ($imageUrl === false) { ?>
  <div class="row-fluid">
    <div class="alert alert-error">
      <i class="icon-warning-sign"></i> File is missing. Value will be set to empty on save.
    </div>
  </div>
  <?php } ?>
  <div class="row-fluid">
    <div class="span10">
      <input type="file" name="<?= $inputName ?>[file]" value="<?= $value ?>" style="" class="span6"/>
      <input type="hidden" name="<?= $inputName ?>[delete]" value=""/>
    </div>
    <div class="span2">
      <?php if ($imageUrl) { ?>
      <div class="img-polaroid" style="width:100px;">
        <a href="<?= $imageUrl ?>" class="lightbox"><img src="<?= $thumbUrl ?>"/></a>
        <a href="#" class="btn-delete"><i class="icon-trash"></i></a>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<script>
(function() {
  $('#<?= $id ?> .btn-delete').click(function() {
    $('#<?= $id ?> input[type=hidden]').val('true');
    $(this).parent().parent().html('');
  });
})();
</script>