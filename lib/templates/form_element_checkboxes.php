<?php foreach($options[0] as $option => $label) { ?>
<label class="checkbox">
  <input type="checkbox" name="<?= $inputName ?>[]" value="<?= $option ?>"<?= in_array($option, $options[1]) ? ' checked' : '' ?>/> <?= $label ?>
</label>
<?php } ?>

<?php if ($action->show(Simplify_Form::ACTION_LIST)) { ?>
<script>
$(document).ready(function() {
  var name = '<?= str_replace(array('[', ']'), array('\\\[', '\\\]'), "{$inputName}[]") ?>';
  $(':checkbox[name=' + name + ']').change(function() {
    var url = '<?= Simplify_URL::make(null, array('formAction' => 'services', 'serviceName' => $name, 'serviceAction' => 'toggle', Simplify_Form::ID => ${Simplify_Form::ID}))->format(Simplify_URL::JSON) ?>';

    var data = {
      '<?= Simplify_Form::ID ?>' : '<?= ${Simplify_Form::ID} ?>',
      '<?= $name ?>' : $(this).val()
    }

    $.amp.loadBegin();

    $.post(url, data, function(response) {
      console.log(url, data, response);
      $.amp.loadEnd();
    });
  });
});
</script>
<?php } ?>