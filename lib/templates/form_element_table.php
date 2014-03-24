<table class="table table-condensed">
  <thead>
    <tr>
      <?php foreach ($dummy['elements'] as $element) { ?>
      <th><?= $element['label'] ?></th>
      <?php } ?>
      <th>Actions</th>
    </tr>
  </thead>

  <tbody>
    <?php foreach ($data as $i => $row) { ?>
    <tr>
      <?php foreach ($row['elements'] as $element) { ?>
      <td><?= $element['controls'] ?></td>
      <?php } ?>

      <td>
        <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>" />
        <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
      </td>
    </tr>
    <?php } ?>

    <tr class="dummy" style="display:none;">
      <?php foreach ($dummy['elements'] as $element) { ?>
      <td><?= $element['controls'] ?></td>
      <?php } ?>

      <td>
        <input type="hidden" name="<?= $dummy['name'] ?>" value="" />
        <a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a>
      </td>
    </tr>
  </tbody>
</table>

<a href="javascript:" class="btn btn-create"><i class="icon-plus"></i> Create</a>

<script>
$(function() {
  var id = '<?= $id ?>'
  var n = 0;

  $('#' + id + ' .dummy :input').attr('disabled', 'disabled');

  $('#' + id + ' .btn-delete').live('click', function() {
    var div = $(this).parents('tr');

    div.remove();

    return false;
  });

  $('#' + id + ' .btn-create').click(function() {
    ++n;

    var dummy = $('#' + id + ' .dummy').clone().removeClass('dummy');

    dummy.find(':input').removeAttr('disabled').each(function() {
      $(this).attr('name', $(this).attr('name').replace(/dummy/, 'new-' + n));
    });

    dummy.show();

    $('#' + id + ' table tbody').append(dummy);

    return false;
  });
});
</script>