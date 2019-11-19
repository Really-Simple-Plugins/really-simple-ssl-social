<?php defined('ABSPATH') or die("you do not have access to this page!");
if (get_option('rsssl_fb_button_type') == 'shares') { ?>
<a class="post-share facebook"
    href="#"
    onClick = "rssslPopupCenter('https://www.facebook.com/share.php?u={url}&width&layout=standard&action=share&show_faces=true&share=true&height=80','Facebook','{width}','{height}'); return false;">
    <div class="rsssl_count">
        {color_round}
        <i class="icon-facebook"></i>{label}<span class="rsssl_likes_shares">{shares}</span>
    </div>
</a>
<?php } else { ?>
<a class="post-share facebook"
   href="#"
   onClick = "rssslPopupCenter('https://www.facebook.com/plugins/like.php?href={url}&layout=standard&action=like&size=large&show_faces=true&share=false&height=65','Facebook','{width}','{height}'); return false;">
	<div class="rsssl_count">
		{color_round}
		<i class="icon-facebook"></i>Like<span class="rsssl_likes_shares">{shares}</span>
	</div>
</a>
<?php }


