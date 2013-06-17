<div class="container-fluid">
  <div class="row-fluid">
    <div class="span12">
      <h1>Select image</h1>

      <ul class="thumbnails">
        <?php foreach ($files as $file) { ?>
        <li>
          <div class="img-polaroid">
            <a href="<?= $file['selectUrl'] ?>" oonclick="selectFile('<?= $file['url'] ?>');">
              <img src="<?= $file['thumbUrl'] ?>" class="image" />
            </a>
          </div>
        </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</div>