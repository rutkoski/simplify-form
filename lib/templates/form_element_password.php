<input type="password" name="<?= $inputName ?>[a]" value="" />
<?php if ($askForConfirmation) { ?>
  </div>
</div>
<div class="control-group">
  <label class="control-label">Repeat password</label>
  <div class="controls">
    <input type="password" name="<?= $inputName ?>[b]" value="" />
<?php } ?>