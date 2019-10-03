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

//Lets add Open Graph Meta Info
function rsssl_insert_fb_in_head() {

	if (src_contains_og_url() == true) return;

	$rsssl_button_type = get_option('rsssl_button_type');
	if ($rsssl_button_type == "builtin" || $rsssl_button_type == 'native') return;

	if(!get_option('add_og_url') ) return;

    global $rsssl_soc_social;
    $url = $rsssl_soc_social->use_http() ? str_replace("https://","http://",get_permalink()) : get_permalink();
    echo '<meta property="og:url" content="' . $url . '"/>
    ';

}
add_action( 'wp_head', 'rsssl_insert_fb_in_head', 5 );

function src_contains_og_url()
{
	$response = wp_remote_get(home_url());
	$web_source = wp_remote_retrieve_body( $response );

	if ( strpos( $web_source, 'property="og:url="') == true ) {
		return true;
	} else {
		add_action( 'wp_head', 'rsssl_insert_fb_in_head', 5 );
	}
}


//function rsssl_insert_upgrade_insecure_requests_header() {
//    echo '<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">';
//
//}
//add_action( 'wp_head', 'rsssl_insert_upgrade_insecure_requests_header', 5 );
