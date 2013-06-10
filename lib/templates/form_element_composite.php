<div class="composite">
<?php foreach ($elements as $i => $element) { ?>
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
