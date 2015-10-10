/*
 MKFileManager v1.0.0
 Copyright (c) 2015 Mikhail Kelner
 Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */
jQuery.fn.mkfilemanager = function(options){

    options = $.extend({
        getDirDataURL: '',
        defaultPath: '',
        filesDataContainerID: '',
        fileTemplateElementID: '',
        newFolderLinkID: '',
        uploadFormID: '',
        textTreeRoot: '.',
        textTreeUpper: '..',
        textAreYouSure: 'Are you sure?',
        textNewFolder: 'New folder name:',
        sortKey: 'name',
        sortAsc: true,
        openFile: function(path){
            console.log('Open file',path);
        },
        editFile: function(){
            return false;
        },
        deleteFile: function(){
            if (confirm(options.textAreYouSure)) {
                var request = {
                    file: $(this).attr('data-file'),
                };
                $.ajax($(this).attr('href'), {
                    data: request,
                    type: 'post',
                    success: function () {
                        $plugin.reloadFiles();
                    }
                });
            }
            return false;
        },
        newFolder: function(){
            var folderName = prompt(options.textNewFolder);
            if (folderName.length > 0){
                var request = {
                    path: $(this).attr('data-path'),
                    folder: folderName
                }
                $.ajax($(this).attr('href'),{
                    data: request,
                    type: 'post',
                    success: function () {
                        $plugin.reloadFiles();
                    }
                });
            }
            return false;
        }
    }, options);

    var $plugin = this;
    var $uploadform = null;

    var make = function(){
        if (options.newFolderLinkID) {
            $(options.newFolderLinkID).on('click',options.newFolder);
        }
        if (options.uploadFormID) {
            $uploadform = $(options.uploadFormID);
            $uploadform.on('submit',$plugin.uploadFiles);
        }
        $plugin.readDir(sessionStorage.getItem('mkfilemanager_path') ? sessionStorage.getItem('mkfilemanager_path') : options.defaultPath);
    };

    $plugin.readDir = function(path){
        if (options.newFolderLinkID) {
            $(options.newFolderLinkID).attr('data-path',path);
        }
        sessionStorage.setItem('mkfilemanager_path',path);
        $uploadform.find('.mk-filemanager-path').eq(0).val(path);
        var request = {
            path: path
        };
        $.ajax(options.getDirDataURL,{
            data: request,
            method: 'get',
            success: function(response){
                $(options.filesDataContainerID).html('');
                $(options.fileTemplateElementID).hide();
                if (path != options.defaultPath){
                    $plugin.addToList({
                        name: options.textTreeRoot,
                        ext: '.',
                        mtime: '',
                        mtime_raw: '0',
                        size: '',
                        size_raw: '0',
                        path: options.defaultPath,
                        not_editable: true
                    });
                    var upperPath = path.split('/')
                    upperPath.pop();
                    upperPath = upperPath.join('/');
                    $plugin.addToList({
                        name: options.textTreeUpper,
                        ext: '.',
                        mtime: '',
                        mtime_raw: '0',
                        size: '',
                        size_raw: '0',
                        path: upperPath,
                        not_editable: true
                    });
                }
                $.each(response.dir, function (i, file){
                    $plugin.addToList(file);
                });
                options.sortAsc = !options.sortAsc;
                $plugin.sortFiles(options.sortKey);
            }
        });
    };

    $plugin.addToList = function(file){
        var $file = $(options.fileTemplateElementID).clone().removeAttr('id').show();
        $file.find('.mk-filemanager-name').text(file.name);
        $file.find('.mk-filemanager-mtime').text(file.mtime);
        if (file.not_editable){
            $file.find('.mk-filemanager-edit, .mk-filemanager-delete').remove();
        }
        $file.find('.mk-filemanager-edit, .mk-filemanager-delete').attr('data-file',file.path);
        $file.find('.mk-filemanager-edit').eq(0).on('click',options.editFile);
        $file.find('.mk-filemanager-delete').eq(0).on('click',options.deleteFile);
        if (file.ext == '.'){
            if (file.not_editable && (file.name == options.textTreeRoot)){
                $file.find('.mk-filemanager-ico').addClass('mk-filemanager-tree');
                $file.attr('data-sort-size',-3).attr('data-sort-time',-3).attr('data-sort-name','1:' + file.name);
            } else if (file.not_editable && (file.name == options.textTreeUpper)){
                $file.find('.mk-filemanager-ico').addClass('mk-filemanager-upper');
                $file.attr('data-sort-size',-2).attr('data-sort-time',-2).attr('data-sort-name','2:' + file.name);
            } else {
                $file.find('.mk-filemanager-ico').addClass('mk-filemanager-dir');
                $file.attr('data-sort-size',-1).attr('data-sort-time',file.mtime_raw).attr('data-sort-name','3:' + file.name);
            }
            $file.on('click',function(e){
                if (e.target.tagName.toUpperCase() != 'A'){
                    $plugin.readDir(file.path);
                }
            });
        } else {
            $file.find('.mk-filemanager-size').text(file.size);
            $file.find('.mk-filemanager-ico').addClass('mk-filemanager-' + file.ext.toLowerCase());
            $file.attr('data-sort-size',file.size_raw).attr('data-sort-time',file.mtime_raw).attr('data-sort-name','4:' + file.name);
            $file.on('click',function(e){
                if (e.target.tagName.toUpperCase() != 'A'){
                    options.openFile(file.path);
                }
            });
        }
        $(options.filesDataContainerID).append($file);
    };

    $plugin.sortFiles = function(mode){
        if (options.sortKey == mode){
            options.sortAsc = !options.sortAsc;
        }
        options.sortKey = mode;
        var $rows = $(options.filesDataContainerID).find('tr');
        $rows.sort(function(a, b) {
            var valueA = $(a).attr('data-sort-' + options.sortKey);
            var valueB = $(b).attr('data-sort-' + options.sortKey);
            if ( (options.sortKey == 'size') || (options.sortKey == 'time') ){
                valueA = parseInt(valueA);
                valueB = parseInt(valueB);
            }
            if (options.sortAsc) {
                return (valueA > valueB) ? 1 : 0;
            } else {
                return (valueA < valueB) ? 1 : 0;
            }
        });
        $.each($rows, function(){
            $(options.filesDataContainerID).append($rows);
        });
    };

    $plugin.uploadFiles = function(){
        var path = $uploadform.find('.mk-filemanager-path').eq(0).val();
        var files = $uploadform.find('.mk-filemanager-files').eq(0).prop('files');
        if (files.length > 0){
            var xhr = new XMLHttpRequest();
            xhr.open("POST", $uploadform.attr('action'), true);
            xhr.setRequestHeader("X-Requested-With", 'XMLHttpRequest');
            xhr.onreadystatechange = function() {
                var classname = '';
                switch (xhr.readyState) {
                    case 1:
                    case 2:
                    case 3:
                        classname = 'process';
                        break;
                    case 4:
                        classname = 'completed';
                        break;
                    default:
                        classname = 'error';
                        break;
                }
                if (classname == 'completed'){
                    $uploadform.trigger('reset');
                    $uploadform.find('.mk-filemanager-path').eq(0).val(path);
                }
                $uploadform.removeClass('mk-filemanager-process');
                $uploadform.removeClass('mk-filemanager-completed');
                $uploadform.removeClass('mk-filemanager-error');
                $uploadform.addClass('mk-filemanager-' + classname);
                if (classname == 'completed'){
                    $plugin.reloadFiles();
                }
            };
            var fd = new FormData();
            fd.append('path',path);
            $.each(files,function(){
                fd.append('uploads[]',this);
            });
            xhr.send(fd);
        }
        return false;
    };

    $plugin.reloadFiles = function(){
        $plugin.readDir(sessionStorage.getItem('mkfilemanager_path'));
    };

    return this.each(make);

};