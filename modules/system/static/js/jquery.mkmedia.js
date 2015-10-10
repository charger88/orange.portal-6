/*
MKMedia v1.0.0
Copyright (c) 2015 Mikhail Kelner
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
*/
jQuery.fn.mkmedia = function(options){
	
	options = $.extend({
		listURL:   '',
		oneURL:    '',
		uploadURL: '',
		updateURL: '',
		deleteURL: '',
		selectTxt: 'Select media file',
		uploadTxt: 'Upload',
		closeTxt:  '×',
		removeTxt: '×'
	}, options);

	var $plugin = this;
	
	var $input;
	var $media;
	var $mediaformupload;
	var $mediaformuploads;
	var $medialist;
	
	var make = function(){
		$input = $(this);
		var $wrapper = new $('<div></div>').addClass('mk-media-input-wrapper');
		$input.wrap($wrapper);
		$input.attr('type','hidden');
		$input.parent()
			.append($('<button></button>').text(options.selectTxt).attr('type','button').on('click',$plugin.open))
		;
		$plugin.syncImage();
	};
	
	$plugin.open = function(){
        $media = $('#mk-media');
        if ($media.length == 0){
			var $mediaforminput = $('<input />')
				.attr('type','file')
				.attr('name','upload')
				.attr('multiple','multiple')
			;
			var $mediaformsubmit = $('<button></button>')
				.text(options.uploadTxt)
			;
			$mediaformuploads = $('<ul></ul>')
				.attr('id','mk-media-uploads')
			;
			$mediaformupload = $('<form></form>')
				.attr('method','post')
				.attr('enctype','multipart/form-data')
				.attr('id','mk-media-form')
				.append($mediaforminput)
				.append($mediaformsubmit)
				.append($mediaformuploads)
				.on('submit',function(){
					var files = $(this).find('input[type="file"]').eq(0).prop('files');
					$plugin.uploadFiles(files);
					$(this).trigger('reset');
					return false;
				})
			;
			var $mediaformsContainer = $('<div></div>')
				.attr('id','mk-media-forms-container')
				.append($mediaformupload)
			;
			$medialist = $('<div></div>')
				.attr('id','mk-media-list')
			;
			var $close = $('<div></div>')
				.addClass('mk-media-close')
				.text(options.closeTxt)
				.on('click',function(){
					$media.hide();
				})
			;
			$media = $('<div></div>')
				.attr('id','mk-media')
				.append($mediaformsContainer)
				.append($medialist)
				.append($close)
			;
			$('body').append($media);
		} else {
			$mediaformupload = $('#mk-media-form');
			$medialist = $('#mk-media-list');
		}
		$mediaformupload.attr('action',options.uploadURL);
		$medialist.attr('data-source',options.listURL);
		$plugin.loadList(0);
		
		$media.on("dragover", function(e){
			e.stopPropagation();
		    e.preventDefault();
			$(this).addClass('mk-media-drag-over');
		});
		
		$media.on("dragleave", function(e){
			e.stopPropagation();
		    e.preventDefault();
			$(this).removeClass('mk-media-drag-over');
		});

		$media.on("drop", function(e){
			e.preventDefault();
			$(this).removeClass('mk-media-drag-over');
			var files = e.originalEvent.dataTransfer.files;
			$plugin.uploadFiles(files);
		});
		
		$media.show();
		
	};
	
	$plugin.uploadFiles = function(files){
		if (files.length > 0){
			var xhr = new XMLHttpRequest();
			xhr.open("POST", $mediaformupload.attr('action'), true);
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
				$mediaformuploads.attr('class','mk-media-uploads-' + classname);
				if ($mediaformuploads.hasClass('mk-media-uploads-completed')){
					$plugin.loadList(-1);
					$mediaformuploads.find('.mk-media-upload-item').fadeOut(500,function(){
						$(this).remove();
					});
				}
			};
			var fd = new FormData();
			$.each(files,function(){
				var itemID = 'mk-media-upload-item-' + ($mediaformuploads.find('li').length + 1);
				$mediaformuploads.prepend($('<li></li>')
					.attr('class','mk-media-upload-item')
					.attr('id',itemID)
					.text(this.name)
				);
				fd.append('uploads[]',this);
			});
			xhr.send(fd);
		}
	};
	
	$plugin.loadList = function(more){
		var request = '';
		if (more && ($medialist.find('.mk-media-list-item').length > 0)){
			if (more > 0){
				request = 'last_id=' + $medialist.find('.mk-media-list-item:last').attr('data-id');
			} else {
				request = 'first_id=' + $medialist.find('.mk-media-list-item:first').attr('data-id');
			}
		}
		$.ajax($medialist.attr('data-source'),{
			data: request,
			type: 'get',
			dataType: 'json',
			success: function(response){
				if (response.status > 3){
					$.each(response.list,function(){
						if ($medialist.find('.mk-media-list-item[data-id="' + this.id + '"]').length == 0){
							var $item = $('<div></div>')
								.addClass('mk-media-list-item')
								.attr('data-id',this.id)
								.append($('<h6></h6>').text(this.name))
								.on('click',function(){
									$input.val($(this).attr('data-id'));
									$plugin.syncImage();
									$media.hide();
								})
							;
							if (this.image.length > 0){
								$item.css('background-image','url(' + this.image + ')');
							}
							if (more < 0){
								$medialist.prepend($item);
							} else {
								$medialist.append($item);
							}
						}
					});
				}
			}
		});
	};
	
	$plugin.syncImage = function(){
		if (($input.val() != '0') && ($input.val() != '')){
			$input.parent().find('h6').remove();
			$input.parent().find('img').remove();
			$.ajax(options.oneURL,{
				data: 'id=' + encodeURIComponent($input.val()),
				dataType: 'json',
				success: function(response){
					if (response.one){
						if (response.one.image){
							var $img = $('<img/>').attr('alt',response.one.name).attr('src',response.one.image).on('click',$plugin.open);
							$input.parent().prepend($img);
						} else {
							var $h6 = $('<h6></h6>').text(response.one.name).on('click',$plugin.open);
							$input.parent().prepend($h6);
						}
					}
				}
			});
		}
	};
	
	return this.each(make);

};