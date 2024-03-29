<?php
/**
 * Plugin Name: Really Simple SSL social
 * Plugin URI: https://www.really-simple-ssl.com/pro
 * Description: Social sharing plugin. Can be used as an add on for Really Simple SSL or as a standalone plugin
 * Version: 4.1.2
 * Text Domain: really-simple-ssl-social
 * Domain Path: /languages
 * Author: Really Simple Plugins
 * Author URI: https://www.really-simple-plugins.com
 */

/*  Copyright 2014  Rogier Lankhorst  (email : rogier@rogierlankhorst.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or die("you do not have access to this page!");
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
$plugin_data = get_plugin_data(__FILE__);
define('rsssl_soc_url', plugin_dir_url(__FILE__));
define('rsssl_soc_path', plugin_dir_path(__FILE__));
define('rsssl_soc_plugin', plugin_basename(__FILE__));
define('RSSSL_SOC_ITEM_ID', 21562);

define('rsssl_soc_version', $plugin_data['Version']);

define('rsssl_soc_plugin_file', __FILE__);

require_once(plugin_dir_path(__FILE__) . 'functions.php');

if (!defined('REALLY_SIMPLE_SSL_URL')) define('REALLY_SIMPLE_SSL_URL', 'https://www.really-simple-ssl.com');

if (is_admin()) {
    require_once(dirname(__FILE__) . '/class-admin.php');
    $rsssl_soc_admin = new rsssl_soc_admin;
}

$core_plugin = '/really-simple-ssl/rlrsssl-really-simple-ssl.php';
if (!is_plugin_active($core_plugin)) {
    require_once(dirname(__FILE__) . '/class-mixed-content-fixer.php');
    $rsssl_soc_mixed_content_fixer = new rsssl_soc_mixed_content_fixer;
}

require_once(dirname(__FILE__) . '/class-help.php');
$rsssl_soc_help = new rsssl_soc_help;

if ((get_option('rsssl_button_type') === 'builtin') || (get_option('rsssl_button_type') === 'native')) {

    if (rsssl_uses_gutenberg()) {
        require_once plugin_dir_path(__FILE__) . 'src/init.php';
    }
    require_once(dirname(__FILE__) . '/rest-api/rest-api.php');

    require_once(dirname(__FILE__) . '/class-native.php');
    $rsssl_soc_native = new rsssl_soc_native;
} else {
    require_once(dirname(__FILE__) . '/class-social.php');
    $rsssl_soc_social = new rsssl_soc_social;
}
