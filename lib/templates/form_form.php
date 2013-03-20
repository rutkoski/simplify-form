<h3><?= $title ?></h3>

<form action="" method="POST" enctype="multipart/form-data" class="form-horizontal">
  <?php foreach ($data as $row) { ?>
  <fieldset>
    <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[Simplify_Form::ID] ?>"/>

    <?php foreach ($row['elements'] as $element) { ?>
    <div class="control-group <?= $element['class'] ?>" id="<?= $element['id'] ?>">
      <label class="control-label" for="<?= $element['name'] ?>"><?= $element['label'] ?></label>
      <div class="controls">
        <?= $element['controls'] ?>
      </div>
    </div>
    <?php } ?>

    <?= $this->menu->show($row['menu']) ?>
  </fieldset>
  <?php } ?>

  <input type="submit" value="Save" class="btn btn-primary" />
</form>