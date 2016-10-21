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
    $('#content_edit_submit').parents('form').eq(0).on('submit', function(){
        if ($('#commands-view-list li a.opened').length > 0){
            return confirm(commandsLanguage.COMMANDS_PROCEED_IF);
        } else {
            return true;
        }
    });
    // TODO Refactor this... But I did it... After 10 years of shame with textarea...
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
            $command.data('allow-action', command_info.action);
            $command.data('allow-block', command_info.block);
            var $argsObject = $('<dl>').addClass('command-arguments');
            $argsObject.append($('<dt>').text(commandsLanguage.COMMANDS_USE_AS_BLOCK));
            $argsObject.append($('<dd>').text(static ? commandsLanguage.COMMANDS_YES : commandsLanguage.COMMANDS_NO));
            $.each(command_info.args, function(key, val){
                if (args_values[key]) {
                    $argsObject.append($('<dt>').text(val.name));
                    $argsObject.append($('<dd>').text(args_values[key]));
                }
            });
            $command.append($('<span>').addClass('command-name').text(command_info.name))
                .append($('<span>').text(' / '))
                .append($('<a>').attr('href', '#').text(commandsLanguage.COMMANDS_EDIT).on('click', editArguments))
                .append($('<span>').text(' / '))
                .append($('<a>').attr('href', '#').text(commandsLanguage.COMMANDS_DELETE).on('click', deleteCommand));
            $command.append($argsObject);
            $commandsViewList.append($command);
            $commandsViewList.sortable({
                update: function(event, ui){
                    rebuildCommandsForm();
                }
            });
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
            $dl.append($('<dt>').text('Use as block'));
            var $selector = $('<input>').attr('type', 'checkbox').addClass('static').val(1);
            if ($object.data('static')){
                $selector.prop('checked', 'checked');
            }
            if (!($object.data('allow-action') && $object.data('allow-block'))){
                $selector.prop('disabled', true);
            }
            $dl.append($('<dd>').append($selector));
            $.each($object.data('args'), function (key, val) {
                var args_values = $object.data('args-values');
                $dl.append($('<dt>').text(val.name));
                var value = args_values[key] ? args_values[key] : null;
                var $selector = $('<input>');
                if (val.type == 'select'){
                    $selector = $('<select>');
                    if (Array.isArray(val.data)) {
                        $.each(val.data, function (k, v) {
                            $selector.append($('<option>').attr('value', k).text(v));
                        });
                        var tmp_value = !value ? 0 : value;
                        setTimeout(function () {
                            $selector.val(tmp_value);
                        }, 1);
                    } else {
                        $.ajax(val.data, {
                            success: function (response) {
                                $.each(response.data, function (k, v) {
                                    var option = $('<option>').attr('value', k).text(v);
                                    if (k.indexOf('{') == 0) {
                                        $selector.prepend(option);
                                    } else {
                                        $selector.append(option);
                                    }
                                });
                                $selector.val(value);
                            }
                        });
                    }
                } else if (val.type == 'number'){
                    $selector.attr('type', 'number');
                } else {
                    $selector.attr('type', 'text');
                }
                $selector.addClass('arg-value').data('arg-name', key).val(value).attr('placeholder', val.default);
                $dl.append($('<dd>').append($selector));
            });
            $object.append($dl);
        } else {
            // Save
            $(this).text(commandsLanguage.COMMANDS_EDIT);
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
            $dl.append($('<dt>').text(commandsLanguage.COMMANDS_USE_AS_BLOCK));
            $dl.append($('<dd>').text($object.data('static') ? commandsLanguage.COMMANDS_YES : commandsLanguage.COMMANDS_NO));
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
                if (info.block && commandsDefaultStatic || !commandsDefaultStatic) {
                    $newCommands.append($('<li>').append($('<a>').attr('href', '#').text(info.name).data('info', info).on('click', function (e) {
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
                        $command.data('allow-action', command_info.action);
                        $command.data('allow-block', command_info.block);
                        var $argsObject = $('<dl>').addClass('command-arguments');
                        $command.append($('<span>').addClass('command-name').text(command_info.name))
                            .append($('<span>').text(' / '))
                            .append($('<a>').attr('href', '#').text('Edit').on('click', editArguments))
                            .append($('<span>').text(' / '))
                            .append($('<a>').attr('href', '#').text('Delete').on('click', deleteCommand))
                        ;
                        $argsObject.append($('<dt>').text(commandsLanguage.COMMANDS_USE_AS_BLOCK));
                        $argsObject.append($('<dd>').text(commandsDefaultStatic ? commandsLanguage.COMMANDS_YES : commandsLanguage.COMMANDS_NO));
                        $command.append($argsObject);
                        $commandsViewList.append($command);
                        rebuildCommandsForm();
                    })));
                }
            });
        });
    });
    $commandsView.append($commandsViewList);
    $commandsView.append($('<div>').attr('id', 'commands-add-wrapper').append($addBtn));
    $commandsView.append($newCommands);
    $commandsSource.after($commandsView);
});