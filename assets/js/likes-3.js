jQuery(document).ready(function($) {
	 'use strict';

//Shows the pinterest overlay when clicking on the icon
$(document).on('click', '.pinterest .rsssl_count', function(e){
	PinUtils.pinAny();
});

$(document).on('click', '.icon-pinterest', function(e){
	PinUtils.pinAny();
});

$(document).on('click', '.post-share', function(e){
	console.log("sharing");
	var container = $(this).find('span');

	var counter = container.html();
	if (counter.indexOf('k')>=0 || counter.indexOf('m')>=0) {
		return;
	}

	if (isNaN(counter) || counter=='') counter=0;
	counter++;
	container.html(counter);
	console.log(counter);

	//now, clear the share cache for this url
	var post_id = $(this).closest('.rsssl_soc').data('rsssl_post_id');
	$.ajax({
			type: "GET",
			url: rsssl_soc_ajax.ajaxurl,
			dataType: 'json',
			data: ({
				action: 'rsssl_clear_likes',
				post_id: post_id,
			}),
			success: function(data){
				console.log('successfully cleared cached shares!');
			}
	});
});

function rsssl_soc_get_likes(){
  var data;
	if (rsssl_soc_ajax.use_cache) return;

  $(".rsssl_soc").each(function(i, obj) {
		var post_id = $(this).data('rsssl_post_id');
		var button_container = $('[data-rsssl_post_id="'+post_id+'"]');
		$.ajax({
        type: "GET",
        url: rsssl_soc_ajax.ajaxurl,
        dataType: 'json',
        data: ({
          action: 'rsssl_get_likes',
          post_id: post_id,
        }),
        success: function(data){
					button_container.find('a.post-share.twitter span').html(data.twitter);
          button_container.find('a.post-share.facebook span').html(data.facebook);
          button_container.find('a.post-share.gplus span').html(data.gplus);
          button_container.find('a.post-share.stumble span').html(data.stumble);
					button_container.find('a.post-share.linkedin span').html(data.linkedin);
					button_container.find('a.post-share.pinterest span').html(data.pinterest);
        }
    });
  });
}

	rsssl_soc_get_likes();
});
