jQuery(document).ready(function($) {
	 'use strict';

$(document).on('click', '.post-share', function(e){
	var counter = $(this).find('div').html();
	if (counter.indexOf('k')>=0 || counter.indexOf('m')>=0) {
		return;
	}

	if (counter=='') counter=0;
	counter++;
	$(this).find('div').html(counter);

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
			}
	});
});

console.log("hoi");

function rsssl_soc_get_likes(){
  var data;
	console.log("retrieve likes");
	if (rsssl_soc_ajax.use_cache) return;
	console.log("get fresh results");

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
					console.log(data);
					button_container.find('a.post-share.twitter div').html(data.twitter);
          button_container.find('a.post-share.facebook div').html(data.facebook);
          button_container.find('a.post-share.gplus div').html(data.gplus);
          button_container.find('a.post-share.stumble div').html(data.stumble);
					button_container.find('a.post-share.linkedin div').html(data.linkedin);
					button_container.find('a.post-share.pinterest div').html(data.pinterest);
        }
    });
  });
}

	rsssl_soc_get_likes();
});
