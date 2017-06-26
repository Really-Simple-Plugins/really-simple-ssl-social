jQuery(document).ready(function($) {
	 'use strict';

function rsssl_soc_get_likes(){
  var data;
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
					if (data.twitter>0) button_container.find('a.post-share.twitter span').html(data.twitter);
          if (data.facebook>0) button_container.find('a.post-share.facebook span').html(data.facebook);
          if (data.gplus>0) button_container.find('a.post-share.gplus span').html(data.gplus);
          if (data.stumble>0) button_container.find('a.post-share.stumble span').html(data.stumble);
        }
    });
  });
}

	rsssl_soc_get_likes();
});
