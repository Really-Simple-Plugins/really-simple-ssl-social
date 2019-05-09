<?php
?> <div class="rsssl-soc-native-item"> <?php
if (isMobileDevice()) { ?>
    <a href="whatsapp://send?text={url}><i class="icon-whatsapp">Share</a>
<?php } else { ?>
    <a href="whatsapp://send?text={url}" data-action="share/whatsapp/share"><i class="icon-whatsapp">Share</i>
</a>
    <div class="rsssl_count"><i class="icon-whatsapp"></i></div>
    <?php } ?>
</div>

<?php
function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>