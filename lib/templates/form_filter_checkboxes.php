<div class="control-group">
  <label for="<?= $name ?>"><?= $label ?></label>
  <div class="controls">
    <?php foreach($options as $option => $label) { ?>
    <label class="radio inline">
      <input type="checkbox" name="<?= $name ?>[]" value="<?= $option ?>"<?= $editable ? '' : ' disabled' ?><?= '' . in_array($option, $value) ? ' checked' : '' ?>> <?= $label ?></input>
    </label>
    <?php } ?>
  </div>
</div>