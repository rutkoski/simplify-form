<select name="<?= $inputName ?>">
<?php foreach($options as $option => $label) { ?>
  <option value="<?= $option ?>"<?= '' . $option == '' . $value ? ' selected' : '' ?>><?= $label ?></option>
<?php } ?>
</select>