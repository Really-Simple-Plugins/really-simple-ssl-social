<?php
if (isMobileDevice()) { ?>
    <a href="whatsapp://send?text={url}">
        <div class="rsssl-soc-native-item native-wa">
        <div class="rsssl_count"><i class="icon-whatsapp"></i></div>
    </div>
    </a>
<?php } else { ?>
    <a href="whatsapp://send?text={url}" data-action="share/whatsapp/share">
        <div class="rsssl-soc-native-item native-wa">
        <i class="icon-whatsapp"></i>
        </div>
    </a>
    <?php }

function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>