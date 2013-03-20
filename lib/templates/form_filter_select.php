<div class="control-group">
  <label for="<?= $name ?>"><?= $label ?></label>

  <div class="controls">
    <select name="<?= "{$name}" ?>" onchange="submit();">
      <?php if ($showEmpty) { echo "<option value=\"$emptyValue\">$emptyLabel</option>"; } ?>
      <?= $this->form->options($options, $value) ?>
    </select>
  </div>
</div>