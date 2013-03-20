<select name="<?= $name ?>">
  <?php if ($showEmpty) { echo "<option value=\"$emptyValue\">$emptyLabel</option>"; } ?>
  <?= $this->form->options($options, $value) ?>
</select>