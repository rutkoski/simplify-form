<div class="control-group">
  <label for="<?= $name ?>" class="control-label"><?= $label ?></label>

  <div class="controls">
    From: <input type="checkbox" name="<?= $name ?>_from_enabled"<?= $fromEnabled ? ' checked="checked"' : '' ?>/>
    <input type="text" name="<?= $name ?>_from" value="<?= $formatedFromValue ?>" class="datetime"/>
    To: <input type="checkbox" name="<?= $name ?>_to_enabled"<?= $toEnabled ? ' checked="checked"' : '' ?>/>
    <input type="text" name="<?= $name ?>_to" value="<?= $formatedToValue ?>" class="datetime"/>
  </div>

  <script>
  $(document).ready(function() {
    $("input[name=<?= $name ?>_from_enabled]").change(function() {
      if ($(this).is(':checked')) {
        $("input[name=<?= $name ?>_from]").removeAttr('disabled');
      } else {
        $("input[name=<?= $name ?>_from]").attr('disabled', 'disabled');
      }
    }).trigger('change');

    $("input[name=<?= $name ?>_to_enabled]").change(function() {
      if ($(this).is(':checked')) {
        $("input[name=<?= $name ?>_to]").removeAttr('disabled');
      } else {
        $("input[name=<?= $name ?>_to]").attr('disabled', 'disabled');
      }
    }).trigger('change');

    $("input[name=<?= $name ?>_from]").datetimepicker({
      onClose: function(dateText, inst) {
        var endDateTextBox = $("input[name=<?= $name ?>_to]");
        if (endDateTextBox.val() != '') {
            var testStartDate = new Date(dateText);
            var testEndDate = new Date(endDateTextBox.val());
            if (testStartDate > testEndDate)
                endDateTextBox.val(dateText);
        }
        else {
          endDateTextBox.val(dateText);
        }
      },
      onSelect: function (selectedDateTime){
          var start = $(this).datetimepicker('getDate');
          $("input[name=<?= $name ?>_to]").datetimepicker('option', 'minDate', new Date(start.getTime()));
      },
      timeSimplify_Format:'hh:mm:ss',
      showSecond:true
    });

    $("input[name=<?= $name ?>_to]").datetimepicker({
      onClose: function(dateText, inst) {
          var startDateTextBox = $("input[name=<?= $name ?>_from]");
          if (startDateTextBox.val() != '') {
              var testStartDate = new Date(startDateTextBox.val());
              var testEndDate = new Date(dateText);
              if (testStartDate > testEndDate)
                  startDateTextBox.val(dateText);
          }
          else {
              startDateTextBox.val(dateText);
          }
      },
      onSelect: function (selectedDateTime){
          var end = $(this).datetimepicker('getDate');
          $("input[name=<?= $name ?>_from]").datetimepicker('option', 'maxDate', new Date(end.getTime()) );
      },
      timeSimplify_Format:'hh:mm:ss',
      showSecond:true
    });
  });
  </script>
</div>