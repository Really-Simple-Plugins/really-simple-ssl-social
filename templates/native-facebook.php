<?php defined('ABSPATH') or die("you do not have access to this page!");
if (get_option('rsssl_fb_button_type') == 'shares') { ?>
<div class="rsssl-soc-native-item">
    <div class="fb-share-button" data-href="{url}" data-layout="button" data-size="small" data-mobile-iframe="true"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore"></a></div>
    <div class="rsssl-soc-share-count">{shares}</div>
</div>
<?php } else { ?>
<div class="rsssl-soc-native-item">
    <div class="fb-like" data-href="{url}" data-width="" data-layout="button" data-action="like" data-size="small" data-show-faces="false" data-share="false"></div>
    <div class="rsssl-soc-share-count">{shares}</div>
</div>
<?php } ?>
