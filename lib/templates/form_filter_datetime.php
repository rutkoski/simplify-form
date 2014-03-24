<div class="control-group">
  <label for="<?= $name ?>" class="control-label"><?= $label ?></label>

  <div class="controls">
    Since: <input type="checkbox" class="<?= $name ?>_since_enabled"<?= $sinceEnabled ? ' checked="checked"' : '' ?>/>
    <input type="text" name="<?= $name ?>_since" value="<?= $formatedSince ?>" class="datetime"/>
    Until: <input type="checkbox" class="<?= $name ?>_until_enabled"<?= $untilEnabled ? ' checked="checked"' : '' ?>/>
    <input type="text" name="<?= $name ?>_until" value="<?= $formatedUntil ?>" class="datetime"/>
  </div>

  <script>
  $(document).ready(function() {
    var since = $("input[name=<?= $name ?>_since]");
    var until = $("input[name=<?= $name ?>_until]");

    $(".<?= $name ?>_since_enabled").change(function() {
      if ($(this).is(':checked')) {
        since.removeAttr('disabled');
      } else {
        since.attr('disabled', 'disabled');
      }
    }).trigger('change');

    $(".<?= $name ?>_until_enabled").change(function() {
      if ($(this).is(':checked')) {
        until.removeAttr('disabled');
      } else {
        until.attr('disabled', 'disabled');
      }
    }).trigger('change');

    since.datetimepicker();
    until.datetimepicker();
  });
  </script>
</div>