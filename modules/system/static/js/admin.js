$(document).ready(function(){
	
	$('.orange-forms-form fieldset > legend').on('click',function(){
		var fid = $(this).parent().attr('id');
		if (fid){
			$(this).next().stop().clearQueue().slideToggle(100,function(){
				if ($(this).is(':hidden')){
					localStorage.setItem('fieldset_hidden_' + fid,'1');
				} else {
					localStorage.removeItem('fieldset_hidden_' + fid);
				}
			});
		}
	});
	
	legendsShowHide();
	
	$('.content-special-list-wrapper .page-in-tree-wrapper').each(function(){
		$(this).on('click',function(){
			document.location = $(this).find('a').eq(0).attr('href');
			return false;
		});
	});

    $('.form-multirow').sortable({
        axis: 'y'
    });
	
});

function legendsShowHide(){
	$('.orange-forms-form fieldset').each(function(){
		var fid = $(this).attr('id');
		if (Boolean(localStorage.getItem('fieldset_hidden_' + fid))){
			$(this).find('> div').hide();
		} else {
			$(this).find('> div').show();
		}
	});
}

window.addEventListener("storage", function(){
	legendsShowHide();
}, false);