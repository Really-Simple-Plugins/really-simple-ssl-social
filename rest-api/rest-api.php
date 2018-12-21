<?php defined('ABSPATH') or die("you do not have acces to this page!");
/**
 *
 * API for Gutenberg blocks
 * @return buttons html
 *
 */

add_action('rest_api_init', 'rsssl_rest_route');
function rsssl_rest_route()
{
    register_rest_route('rsssl/v1/', 'buttons/id/(?P<id>[0-9]+)', array(
        'methods' => 'GET',
        'callback' => 'rsssl_social_buttons',
    ));
}

function rsssl_social_buttons(WP_REST_Request $request)
{
    $post_id = $request->get_param( 'id' );
    global $rsssl_soc_native;
    $output = $rsssl_soc_native->generate_like_buttons($post_id);

    return $output;
}

