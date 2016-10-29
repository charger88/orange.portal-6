$(document).ready(function(){
    $('#pages-tree ul').sortable({
        connectWith: '#pages-tree ul',
        start: function(){
            $('#pages-tree').addClass('op-sortable');
        },
        update: function(event){
            var request = {};
            request.root = $(event.target).attr('data-root');
            request.order = [];
            $(event.target).children().each(function(){
                request.order.push($(this).attr('data-id'));
            });
            $.ajax($('#pages-tree').attr('data-reorder-url'),{
                data: request,
                type: 'post'
            });
        },
        stop: function(){
            $('#pages-tree').removeClass('op-sortable');
        }
    });
});