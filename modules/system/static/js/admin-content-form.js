$(document).ready(function(){
    var $slugElement = $('#opmx_system_contentedit-content_slug');
	if ($slugElement.length && ($slugElement.val().length == 0)){
        $('#opmx_system_contentedit-content_title').on('change keyup',function(){
            if (!$slugElement.hasClass('edited')) {
                var postfix = $slugElement.attr('data-postfix') ? $slugElement.attr('data-postfix') : '';
                $slugElement.val($('#opmx_system_contentedit-content_title').val().toLowerCase().split(' ').join('-') + postfix);
            }
        });
        $slugElement.on('change keyup',function(){
            $slugElement.addClass('edited')
        });
    }
});