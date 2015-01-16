<h3><?= $title ?></h3>

<form action="" method="POST">
  <input type="hidden" name="deleteAction" value="confirm" />

  <?php foreach ($data as $row) { ?>
  <input type="hidden" name="<?= $row['name'] ?>" value="<?= $row[\Simplify\Form::ID] ?>" />
  <?php } ?>

  <p>Do you really want to delete these items?</p>

  <ul>
    <?php foreach ($data as $row) { ?>
    <li><?= $row['label'] ?></li>
    <?php } ?>
  </ul>

  <input type="submit" value="Confirm" class="btn btn-primary" />
</form>