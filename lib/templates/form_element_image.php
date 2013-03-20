<div class="container-fluid well <?= $class ?>" id="<?= $id ?>">
  <div class="row-fluid">
    <div class="span4">
      <input type="file" name="<?= $name ?>" value="<?= $value ?>"/>
    </div>
    <div class="span8">
      <?php if ($value) { ?>
      <div class="img-polaroid" style="width:100px;">
        <a href="<?= $image_src ?>" class="lightbox"><img src="<?= $thumb_src ?>"/></a>
        <a href="#" class="btn-delete"><i class="icon-trash"></i></a>
      </div>
      <?php } ?>
    </div>
  </div>
</div>

<script>
(function() {
  $('#<?= $id ?> .btn-delete').click(function() {
    $(this).parent().parent().html('<input type="hidden" name="<?= $name ?>[delete]" value="true"/>');
  });
})();
</script>