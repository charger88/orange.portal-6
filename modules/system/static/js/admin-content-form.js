$(document).ready(function(){
	if ($('#opmx_system_contentedit-content_slug').val().length == 0){
        $('#opmx_system_contentedit-content_title').on('change keyup',function(){
            if (!$('#opmx_system_contentedit-content_slug').hasClass('edited')) {
                var postfix = $('#opmx_system_contentedit-content_slug').attr('data-postfix') ? $('#opmx_system_contentedit-content_slug').attr('data-postfix') : '';
                $('#opmx_system_contentedit-content_slug').val($('#opmx_system_contentedit-content_title').val().toLowerCase().split(' ').join('-') + postfix);
            }
        });
        $('#opmx_system_contentedit-content_slug').on('change keyup',function(){
            $('#opmx_system_contentedit-content_slug').addClass('edited')
        });
    }
});