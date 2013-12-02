jQuery(function($) {
	$('.field.qacaptcha a.qacaptcha-otherquestion').live('click', function(e) {
		e.preventDefault();

		var $a = $(this);
		var $field = $(this).parents('.field.qacaptcha');
		$a.html('Loading, please wait...');

		$.ajax({
			url: $(this).attr('href'),
			success: function(data) {
				$field.replaceWith(data);
				$('.field.qacaptcha a.qacaptcha-otherquestion').parent().show();
			},
			error: function() {
				$a.html('Could not fetch new question. Click here to try again.');
			}
		});

		return false;
	});
	$('.field.qacaptcha a.qacaptcha-otherquestion').parent().show();
});
