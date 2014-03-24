<div class="has_one">
<input type="hidden" name="<?= $data['name'] ?>" value="<?= $data[Simplify_Form::ID] ?>" />
<?php foreach ($data['elements'] as $i => $element) { ?>
  <?php if ($element['label'] !== false) { ?>
  <div class="control-group <?= $element['class'] ?>" id="<?= $element['id'] ?>">
    <label class="control-label" for="<?= $element['name'] ?>"><?= $element['label'] ?></label>
    <div class="controls">
      <?= $element['controls'] ?>
    </div>
  </div>
  <?php } else { ?>
  <?= $element['controls'] ?>
  <?php } ?>
<?php } ?>
</div>
