<input type="text" name="<?= $name ?>" value="<?= $formatedValue ?>"/>
<script>
(function() {
  $("#<?= $id ?> input").datetimepicker({ timeSimplify_Format:'hh:mm:ss', showSecond:true });
})();
</script>