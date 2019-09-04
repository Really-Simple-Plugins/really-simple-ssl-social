<?php

if (!function_exists('rsssl_uses_gutenberg')) {
    function rsssl_uses_gutenberg()
    {

        if (function_exists('has_block') && !class_exists('Classic_Editor')) {
            return true;
        }
        return false;
    }
}



////Lets add Open Graph Meta Info
//function rsssl_insert_fb_in_head() {
//    global $rsssl_soc_social;
//    $url = $rsssl_soc_social->use_http() ? str_replace("https://","http://",get_permalink()) : get_permalink();
//    echo '<meta property="og:url" content="' . $url . '"/>
//    ';
//
//}
//add_action( 'wp_head', 'rsssl_insert_fb_in_head', 5 );
//
///


//function rsssl_insert_upgrade_insecure_requests_header() {
//    echo '<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">';
//
//}
//add_action( 'wp_head', 'rsssl_insert_upgrade_insecure_requests_header', 5 );