<h3><?= $title ?></h3>

<?php if (! empty($filters)) { ?>
<form action="" method="GET" class="form-inline">
  <fieldset>
    <legend>Filters</legend>

    <?php foreach ($filters as $filter) { ?>
    <?= $filter['controls'] ?>
    <?php } ?>

    <input type="submit" value="Apply" class="btn"/>
  </fieldset>
</form>
<?php } ?>

<form action="" method="GET" class="form-inline">
  <?= $this->pager->show($pager) ?>

  <br/><br/>

  <table class="table table-condensed">
    <thead>
      <tr>
        <th style="width:1%;"></th>

        <?php foreach ($headers as &$header) { ?>
        <th><?= $header ?></th>
        <?php } ?>

        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($data as &$row) { ?>
      <tr>
        <td><input type="checkbox" name="<?= $row['name'] ?>" value="<?= $row['_id'] ?>"/></td>

        <?php foreach ($row['elements'] as &$element) { ?>
        <td><?= $element['controls'] ?></td>
        <?php } ?>

        <td>
          <?= $this->menu->show($row['menu']) ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

  <?= $this->pager->show($pager) ?>

  <select name="formAction">
    <option value=""></option>
    <?php foreach ($bulk as $value => $label) { ?>
    <option value="<?= $value ?>"><?= $label ?></option>
    <?php } ?>
  </select>

  <input type="submit" value="Ok" class="btn" />
</form>