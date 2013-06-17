<h3><?= $title ?></h3>

<form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
  <?php foreach ($data as $row) { ?>
  <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>"/>

  <?php foreach ($row['elements'] as $element) { ?>
  <?php if ($element['label'] !== false) { ?>
  <div class="control-group <?= $element['class'] ?><?= $element['state'] ? " {$element['state']}" : '' ?>" id="<?= $element['id'] ?>">
    <label class="control-label" for="<?= $element['name'] ?>"><?= $element['label'] ?></label>
    <div class="controls">
      <?= $element['controls'] ?>
      <?= $element['stateMessage'] ? "<span class=\"help-inline\">{$element['stateMessage']}</span>" : '' ?>
    </div>
  </div>
  <?php } else { ?>
  <?= $element['controls'] ?>
  <?php } ?>
  <?php } ?>

  <?= $this->menu->show($row['menu']) ?>
  <?php } ?>

  <input type="submit" value="Save" class="btn btn-primary" />
</form>