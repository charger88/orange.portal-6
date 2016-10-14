$(document).ready(function(){
    var $slugElement = $('#content_slug');
	if ($slugElement.length && ($slugElement.val().length == 0)){
        $('#content_title').on('change keyup',function(){
            if (!$slugElement.hasClass('edited')) {
                var postfix = $slugElement.data('postfix') ? $slugElement.data('postfix') : '';
                $slugElement.val($('#content_title').val().toLowerCase().split(' ').join('-') + postfix);
            }
        });
        $slugElement.on('change keyup',function(){
            $slugElement.addClass('edited')
        });
    }
});