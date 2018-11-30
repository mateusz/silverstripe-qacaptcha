document.addEventListener('DOMContentLoaded', function() {
    jQuery(function($) {
        $(document).on('click', '.field.qacaptcha a.qacaptcha-otherquestion', function(e) {
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
});
