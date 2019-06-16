<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * production: npn run build
 * dev: npm start
 * translation: wp i18n make-pot . config/languages/complianz.pot --exclude="pro-assets, core/assets"
 *
 * */

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
// Hook: Frontend assets.
//handled in documents class

//add_action( 'enqueue_block_assets', 'cmplz_block_assets' );
//function cmplz_block_assets() { // phpcs:ignore
//	// Styles.
//	wp_enqueue_style(
//		'my_block-cgb-style-css', // Handle.
//		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
//		array( 'wp-editor' ) // Dependency to include the CSS after it.
//		// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
//	);
//}

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
// Hook: Editor assets.
add_action( 'enqueue_block_editor_assets', 'rsssl_social_editor_assets' );
function rsssl_social_editor_assets() { // phpcs:ignore
    // Scripts.
    wp_enqueue_script(
        'rsssl-social-block', // Handle.
        plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-api' ), // Dependencies, defined above.
        filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: File modification time.
        true // Enqueue the script in the footer.
    );

    wp_localize_script(
        'rsssl-social-block',
        'complianz',
        array(
            'site_url' => site_url(),
        )
    );
    //https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
    //wp package install git@github.com:wp-cli/i18n-command.git
    //wp i18n make-pot . config/languages/complianz.pot --exclude="pro-assets, core/assets"
    wp_set_script_translations( 'rsssl-social-block', 'really-simple-ssl-social' , rsssl_soc_path . 'config/languages');

    // Styles.
    $theme = get_option('rsssl_buttons_theme');
    if (!get_option('rsssl_button_type') === 'native') {
        wp_enqueue_style('rsssl_social_buttons_style', rsssl_soc_url . "assets/css/$theme.min.css", array(), rsssl_soc_version);
    }

    if (get_option('rsssl_button_type') === 'native') {
        wp_register_style('rsssl_social_native_style', rsssl_soc_url . "assets/css/native.min.css", array(), rsssl_soc_version);
        wp_enqueue_style('rsssl_social_native_style');
    }

    wp_enqueue_style('rsssl_social_fontello', rsssl_soc_url . 'assets/font/fontello-icons/css/fontello.css', array(), rsssl_soc_version);

    //Add any custom CSS defined in the custom CSS settings section
    $custom_css = get_option('rsssl_custom_css');

    if ($custom_css) {
        global $rsssl_soc_native;
        $custom_css = $rsssl_soc_native->sanitize_custom_css($custom_css);
        if (!empty($custom_css)) {
            wp_add_inline_style('rsssl_social_buttons_style', $custom_css);
        }
    }

}


/**
 * Handles the front end rendering of the complianz block
 *
 * @param $attributes
 * @param $content
 * @return string
 */
function rsssl_social_render_document_block($attributes, $content)
{
    $html = '';
    global $post;
    if ($post) {
        global $rsssl_soc_native;
        $html = $rsssl_soc_native->generate_like_buttons($post->ID);
    }

    return $html;
}

register_block_type('rsssl/block-rsssl-social', array(
    'render_callback' => 'rsssl_social_render_document_block',
));
