(function($) {
	$(document).ready(function() {
		$('textarea[data-sy-form-wysiwyg]').each(function() {
			var $this = $(this), options = $this.data('sy-form-wysiwyg');
			CKEDITOR.replace($this[0], options);
		});
		
		$('input[data-sy-form-datetime]').datetimepicker({
			language: 'pt',
			useSeconds: true
		});
		
		$('.lightbox').fancybox();
	});
}(jQuery));