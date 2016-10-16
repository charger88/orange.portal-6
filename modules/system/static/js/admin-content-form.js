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
    // TODO Refactor this... But I did it... After 10 years of shame with textarea...
    // TODO Use action and block arguments for "block" checkbox
    // TODO Drag and drop sorting
    var $commandsSource = $('#multirow-content_commands').hide();
    var $commandsView = $('<div>')
        .attr('id', 'commands-view')
        .append($('<label>').text($commandsSource.find('legend').eq(0).text()))
    ;
    var $commandsViewList = $('<ul>').attr('id', 'commands-view-list');
    var createLineFromFormRow = function(){
        if ($(this).find('[name="content_commands[module][]"]').val().length > 0){
            var $command = $('<li>');
            var module = $(this).find('[name="content_commands[module][]"]').val();
            var controller = $(this).find('[name="content_commands[controller][]"]').val();
            var method = $(this).find('[name="content_commands[method][]"]').val();
            var static = $(this).find('[name="content_commands[static][]"]').val();
            var args_values = JSON.parse($(this).find('[name="content_commands[args][]"]').val());
            var command_info = commands[module][controller][method];
            static = (!static || static == '0') ? 0 : 1;
            $command.data('module', module);
            $command.data('controller', controller);
            $command.data('method', method);
            $command.data('static', static);
            $command.data('args', command_info.args);
            $command.data('args-values', args_values);
            var $argsObject = $('<dl>').addClass('command-arguments');
            $argsObject.append($('<dt>').text('Use as block'));
            $argsObject.append($('<dd>').text(static ? 'Yes' : 'No'));
            $.each(command_info.args, function(key, val){
                if (args_values[key]) {
                    $argsObject.append($('<dt>').text(val.name));
                    $argsObject.append($('<dd>').text(args_values[key]));
                }
            });
            $command.append($('<span>').addClass('command-name').text(command_info.name))
                .append($('<span>').text(' / '))
                .append($('<a>').attr('href', '#').text('Edit').on('click', editArguments))
                .append($('<span>').text(' / '))
                .append($('<a>').attr('href', '#').text('Delete').on('click', deleteCommand));
            $command.append($argsObject);
            $commandsViewList.append($command);
        }
    };
    var rebuildCommandsForm = function () {
        $commandsSource.find('*').remove();
        $('#commands-view-list li').each(function () {
            $commandsSource.append($('<input>').attr('type', 'hidden').attr('name', 'content_commands[module][]').val($(this).data('module')));
            $commandsSource.append($('<input>').attr('type', 'hidden').attr('name', 'content_commands[controller][]').val($(this).data('controller')));
            $commandsSource.append($('<input>').attr('type', 'hidden').attr('name', 'content_commands[method][]').val($(this).data('method')));
            $commandsSource.append($('<input>').attr('type', 'hidden').attr('name', 'content_commands[static][]').val($(this).data('static')));
            $commandsSource.append($('<input>').attr('type', 'hidden').attr('name', 'content_commands[args][]').val(JSON.stringify($(this).data('args-values'))));
        });
    };
    var editArguments = function(e){
        e.preventDefault();
        var $object = $(this).parents('li').eq(0);
        if (!$(this).hasClass('opened')) {
            // Edit
            $(this).text('Apply');
            $(this).addClass('opened');
            $object.find('dl').remove();
            var $dl = $('<dl>');
            $dl.append($('<dt>').text('Use as block')); //$object.data('static')
            var $selector = $('<input>').attr('type', 'checkbox').addClass('static').val(1);
            if ($object.data('static')){
                $selector.prop('checked', 'checked');
            }
            $dl.append($('<dd>').append($selector));
            $.each($object.data('args'), function (key, val) {
                var args_values = $object.data('args-values');
                $dl.append($('<dt>').text(val.name));
                var value = args_values[key] ? args_values[key] : null;
                var $selector = $('<input>').attr('type', 'text').addClass('arg-value').data('arg-name', key).val(value).attr('placeholder', val.default);
                $dl.append($('<dd>').append($selector));
            });
            $object.append($dl);
        } else {
            // Save
            $(this).text('Edit');
            $(this).removeClass('opened');
            var static = $object.find('input.static').is(':checked');
            $object.data('static', static ? 1 : 0);
            var args_values = {};
            $object.find('.arg-value').each(function(){
                if ($(this).val().length > 0) {
                    args_values[$(this).data('arg-name')] = $(this).val();
                }
            });
            $object.data('args-values', args_values);
            // Show
            $object.find('dl').remove();
            var $dl = $('<dl>');
            $dl.append($('<dt>').text('Use as block'));
            $dl.append($('<dd>').text($object.data('static') ? 'Yes' : 'No'));
            $.each($object.data('args'), function (key, val) {
                if (args_values[key]) {
                    $dl.append($('<dt>').text(val.name));
                    $dl.append($('<dd>').text(args_values[key]));
                }
            });
            $object.append($dl);
            rebuildCommandsForm();
        }
    };
    var deleteCommand = function(e){
        e.preventDefault();
        $(this).parents('li').eq(0).remove();
        rebuildCommandsForm();
    }
    $commandsSource.find('.orange-forms-rows-container .orange-forms-row-wrapper').each(createLineFromFormRow);
    var $addBtn = $('<a>').attr('href', '#').text('Add command').on('click', function (e) {
        e.preventDefault();
        $('#commands-new-list').slideToggle(100);
    });
    var $argsForm = $('<div>');
    var $newCommands = $('<ul>').attr('id', 'commands-new-list').hide();
    $.each(commands, function(module, controllers){
        $.each(controllers, function(controller, methods){
            $.each(methods, function(method, info){
                $newCommands.append($('<li>').append($('<a>').attr('href', '#').text(info.name).data('info', info).on('click', function(e){
                    e.preventDefault();
                    var $command = $('<li>');
                    var args = {};
                    var command_info = commands[module][controller][method];
                    $command.data('module', module);
                    $command.data('controller', controller);
                    $command.data('method', method);
                    $command.data('static', commandsDefaultStatic);
                    $command.data('args', command_info.args);
                    $command.data('args-values', args);
                    var $argsObject = $('<dl>').addClass('command-arguments');
                    $command.append($('<span>').addClass('command-name').text(command_info.name))
                        .append($('<span>').text(' / '))
                        .append($('<a>').attr('href', '#').text('Edit').on('click', editArguments))
                        .append($('<span>').text(' / '))
                        .append($('<a>').attr('href', '#').text('Delete').on('click', deleteCommand))
                    ;
                    $command.append($argsObject);
                    $commandsViewList.append($command);
                    rebuildCommandsForm();
                })));
            });
        });
    });
    $commandsView.append($commandsViewList);
    $commandsView.append($('<div>').attr('id', 'commands-add-wrapper').append($addBtn));
    $commandsView.append($newCommands);
    $commandsSource.after($commandsView);
});