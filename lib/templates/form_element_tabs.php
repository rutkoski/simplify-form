<div class="tabbable">
  <ul class="nav nav-tabs">
    <?php foreach ($data as $i => $row) { ?>
    <li class="<?= $i == 0 ? 'active' : '' ?>"><a href="#tab<?= $row[Simplify_Form::ID] ?>" data-toggle="tab"><?= $label ?> (<?= $row[Simplify_Form::ID] ?>)</a></li>
    <?php } ?>

    <li class="dummy"><a href="#tab-0" data-toggle="tab">+</a></li>
  </ul>

  <div class="tab-content well">
    <?php foreach ($data as $i => $row) { ?>
    <div class="tab-pane<?= $i == 0 ? ' active' : '' ?>" id="tab<?= $row[Simplify_Form::ID] ?>">
      <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>" />

      <?php foreach ($row['elements'] as $element) { ?>
      <?= $element['controls'] ?>
      <?php } ?>

      <ul class="nav nav-pills">
        <li><a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a></li>
      </ul>
    </div>
    <?php } ?>

    <div class="tab-pane dummy-pane" id="tab-0">
      <input type="hidden" name="<?= $dummy['name'] ?>" value="" />

      <?php foreach ($dummy['elements'] as $element) { ?>
      <?= $element['controls'] ?>
      <?php } ?>

      <ul class="nav nav-pills">
        <li><a href="#" class="btn-delete"><?= $this->icon->show('remove') ?></a></li>
      </ul>
    </div>
  </div>
</div>

<script>
$(function() {
  var label = '<?= $label ?>';
  var n = 0;

  $('.tabbable .dummy-pane :input').attr('disabled', 'disabled');

  $('.tabbable .tab-pane .btn-delete').live('click', function() {
    var div = $(this).parents('.tab-pane');
    var li = $(this).parents('.tabbable').find('ul a[href=#'+div.attr('id')+']').parents('li');

    if (li.prev().length) {
      li.prev().find('a').tab('show');;
    } else if ($(this).parents('.tabbable').find('ul:first li').length > 2) {
      li.next().find('a').tab('show');;
    }

    div.remove();
    li.remove();

    return false;
  });

  $('.tabbable ul li.dummy a').click(function() {
    ++n;

    var li = $(this).parents('li').clone().removeClass('dummy');
    var _div = $(this).parents('.tabbable').find('.tab-content .dummy-pane');
    var div = _div.clone().removeClass('dummy-pane');

    li.insertBefore($(this).parents('li'));
    li.find('a').html(label + ' (+)');

    div.insertBefore(_div);
    div.attr('id', 'tab-' + n);

    div.find(':input').removeAttr('disabled').each(function() {
      $(this).attr('name', $(this).attr('name').replace(/dummy/, 'new-' + n));
    });

    li.find('a').attr('href', '#tab-' + n).tab('show');

    return false;
  });
});
</script>