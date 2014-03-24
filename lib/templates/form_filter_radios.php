<div class="control-group">
  <label for="<?= $name ?>"><?= $label ?></label>
  <div class="controls">
    <?php foreach($options as $option => $label) { ?>
    <label class="radio inline">
      <input type="radio" name="<?= $name ?>" value="<?= $option ?>"<?= '' . $option == '' . $value ? ' checked' : '' ?>> <?= $label ?></input>
    </label>
    <?php } ?>
  </div>
</div>