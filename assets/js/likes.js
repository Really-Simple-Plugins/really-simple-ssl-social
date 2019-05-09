function rssslPopupCenter(url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }
}

jQuery(document).ready(function ($) {
    'use strict';

    var monitor = setInterval(function(){
        var elem = document.activeElement;
        if(elem && elem.tagName == 'IFRAME'){
            // alert('Clicked');
            console.log("sharing");

            //var wrapper = $(this).closest('div.rsssl-soc-native-item');
            var wrapper = $(document).closest('div.rsssl-soc-native-item');

            console.log("Wrapper");
            console.log(wrapper);
            //if (!wrapper.length) return;
            var container = wrapper.find('.rsssl_likes_shares');

            var counter = container.html();
            return;
            if (counter.indexOf('k') >= 0 || counter.indexOf('m') >= 0) {
                return;
            }

            if (isNaN(counter) || counter == '') counter = 0;
            counter++;
            container.html(counter);

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
                success: function (data) {
                    console.log('successfully cleared cached shares!');
                }
            });
            clearInterval(monitor);
        }
    }, 3500);

//Shows the pinterest overlay when clicking on the icon
    $(document).on('click', '.pinterest .rsssl_count', function (e) {
        PinUtils.pinAny();
    });

    $(document).on('click', '.icon-pinterest', function (e) {
        PinUtils.pinAny();
    });

    $(document).on('click', '.post-share', function (e) {
        console.log("sharing");
        var container = $(this).find('span.rsssl_likes_shares');

        var counter = container.html();
        if (counter.indexOf('k') >= 0 || counter.indexOf('m') >= 0) {
            return;
        }

        if (isNaN(counter) || counter == '') counter = 0;
        counter++;
        container.html(counter);

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
            success: function (data) {
                console.log('successfully cleared cached shares!');
            }
        });
    });

    function rsssl_soc_get_likes() {
        var data;
        if (rsssl_soc_ajax.use_cache) return;

        $(".rsssl_soc").each(function (i, obj) {
            var post_id = $(this).data('rsssl_post_id');
            var button_container = $('[data-rsssl_post_id="' + post_id + '"]');
            $.ajax({
                type: "GET",
                url: rsssl_soc_ajax.ajaxurl,
                dataType: 'json',
                data: ({
                    action: 'rsssl_get_likes',
                    post_id: post_id,
                }),
                success: function (data) {
                    button_container.find('a.post-share.twitter span').html(data.twitter);
                    button_container.find('a.post-share.facebook span').html(data.facebook);
                    button_container.find('a.post-share.gplus span').html(data.gplus);
                    button_container.find('a.post-share.linkedin span').html(data.linkedin);
                    button_container.find('a.post-share.pinterest span').html(data.pinterest);
                }
            });
        });
    }

});
