<input type="radio" name="<?= $name ?>" value="<?= $trueValue ?>"<?= $value == $trueValue ? ' checked="checked"' : '' ?>"/> <?= $trueLabel ?> 
<input type="radio" name="<?= $name ?>" value="<?= $falseValue ?>"<?= $value == $falseValue ? ' checked="checked"' : '' ?>"/> <?= $falseLabel ?>