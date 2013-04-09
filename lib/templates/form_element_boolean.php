<label class="radio">
  <input type="radio" name="<?= $inputName ?>"
    value="<?= $trueValue ?>"<?= $value == $trueValue ? ' checked="checked"' : '' ?>"/> <?= $trueLabel ?>
</label>
<label class="radio">
  <input type="radio" name="<?= $inputName ?>"
    value="<?= $falseValue ?>"<?= $value == $falseValue ? ' checked="checked"' : '' ?>"/> <?= $falseLabel ?>
</label>