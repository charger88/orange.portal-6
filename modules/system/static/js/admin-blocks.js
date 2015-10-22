$(document).ready(function(){
    $('#blocks-list ul').sortable({
        connectWith: '#blocks-list ul',
        start: function(){
            $('#blocks-list').addClass('op-sortable');
        },
        update: function(event){
            var request = {};
            request.root = $(event.target).attr('data-root');
            request.order = new Array();
            $(event.target).children().each(function(){
                request.order.push($(this).attr('data-id'));
            });
            $.ajax($('#blocks-list').attr('data-reorder-url'),{
                data: request,
                type: 'post'
            });
        },
        stop: function(){
            $('#blocks-list').removeClass('op-sortable');
        }
    });
});