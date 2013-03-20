<?= $this->form->checkboxes("{$name}[]", $options[0], $options[1], null, array('itemSeparator' => '<br/>')) ?>

<?php if ($action->show(Simplify_Form::ACTION_LIST)) { ?>
<script>
$(document).ready(function() {
  var name = '<?= str_replace(array('[', ']'), array('\\\[', '\\\]'), "{$name}[]") ?>';
  $(':checkbox[name=' + name + ']').change(function() {
    var url = '<?= Simplify_URL::make(null, array('formAction' => 'services', 'serviceName' => $name, 'serviceAction' => 'toggle'))->format(Simplify_URL::JSON) ?>';

    var data = {
      id: <?= $id ?>,
      <?= $name ?>: $(this).val()
    }

    $.amp.loadBegin();

    $.post(url, data, function(response) {
      console.log(response);

      $.amp.loadEnd();
    });
  });
});
</script>
<?php } ?>