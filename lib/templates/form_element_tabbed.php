<div class="tabbable">
  <ul class="nav nav-tabs">
    <?php foreach ($headers as $i => $header) { ?>
    <li class="<?= $i == $active ? 'active' : '' ?>"><a href="#tab<?= $i ?>" data-toggle="tab"><?= $header ?></a></li>
    <?php } ?>
  </ul>

  <div class="tab-content">
    <?php foreach ($elements as $i => $element) { ?>
    <div class="tab-pane<?= $i == $active ? ' active' : '' ?>" id="tab<?= $i ?>">
      <?= $element['controls'] ?>
    </div>
    <?php } ?>
  </div>
</div>