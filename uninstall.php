<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

rsssl_delete_all_options(
  array(
      'rsssl_soc_start_date_ssl',
      'rsssl_soc_replace_ogurl',
      'rsssl_soc_replace_to_http_on_home',
      'rsssl_insert_custom_buttons',
      'rsssl_soc_fb_access_token',
      'rsssl_buttons_on_post_types',
      'rsssl_fb_button_type',
      'rsssl_button_position',
      'rsssl_retrieval_domains',
      'rsssl_social_services',
      'rsssl_inline_or_left',
      'rsssl_use_30_styling',
    )
  );

function rsssl_delete_all_options($options) {
  foreach($options as $option_name) {
    delete_option( $option_name );
    // For site options in Multisite
    delete_site_option( $option_name );
  }
}
