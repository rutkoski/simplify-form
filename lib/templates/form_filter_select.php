<div class="control-group">
  <label for="<?= $name ?>"><?= $label ?></label>
  <div class="controls">
    <select name="<?= $name ?>"<?= $editable ? '' : ' disabled' ?>>
    <?php foreach($options as $option => $label) { ?>
      <option value="<?= $option ?>"<?= '' . $value == '' . $option ? ' selected="selected"' : '' ?>><?= $label ?></option>
    <?php } ?>
    </select>
  </div>
</div>