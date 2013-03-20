<h3><?= $title ?></h3>

<form action="" method="POST">
  <input type="hidden" name="deleteAction" value="confirm" />

  <?php foreach ($id as $_id) { ?>
  <input type="hidden" name="<?= $name ?>" value="<?= $_id ?>" />
  <?php } ?>

  <p>Do you really want to delete these items?</p>

  <ul>
    <?php foreach ($data as $row) { ?>
    <li><?= $row['label'] ?></li>
    <?php } ?>
  </ul>

  <input type="submit" value="Confirm" />
</form>