<?= $this->html->js('/ckeditor/ckeditor.js') ?>

<textarea name="<?= $inputName ?>"><?= $value ?></textarea>

<script>
CKEDITOR.replace('<?= $inputName ?>', {
  filebrowserBrowseUrl: '<?= $browserUrl ?>',
  filebrowserUploadUrl: '<?= $uploaderUrl ?>'
});
</script>