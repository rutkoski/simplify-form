<?php foreach($options as $option => $label) { ?>
<label class="radio">
  <input type="radio" name="<?= $inputName ?>" value="<?= $option ?>"<?= $option == $value ? ' checked' : '' ?>/> <?= $label ?>
</label>
<?php } ?>