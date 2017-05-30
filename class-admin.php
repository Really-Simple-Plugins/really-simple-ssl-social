<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_admin {
  private static $_this;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;

  add_action('admin_init', array($this, 'init'), 15 );

  register_activation_hook( __FILE__, array( $this, 'install' ) );
}

static function this() {
  return self::$_this;
}


/*set the date to an inital value of today. */

public function install(){
  $start_date_social = get_option('rsssl_soc_start_date_ssl');
  if (!$start_date_social) {
    update_option("rsssl_soc_startdate", date(get_option('date_format')));
  }
}

public function init(){
  if (!class_exists('rsssl_admin')) return;
  add_action('admin_init', array($this, 'add_settings'),50);
}


public function options_validate($input){
  $validated_input = sanitize_text_field($input);
  return $validated_input;
}

public function options_validate_boolean($input){
  return $input ? true : false;
}

public function add_settings(){
  if (!class_exists("rsssl_admin")) return;

  //add_settings_section('section_rssslpp', __("Pro", "really-simple-ssl-soc"), array($this, "section_text"), 'rlrsssl');
  register_setting( 'rlrsssl_options', 'rsssl_soc_start_date_ssl', array($this,'options_validate') );
  register_setting( 'rlrsssl_options', 'rsssl_soc_replace_ogurl', array($this,'options_validate_boolean') );
  register_setting( 'rlrsssl_options', 'rsssl_soc_replace_to_http_on_home', array($this,'options_validate_boolean') );
  add_settings_field('id_start_date_social', __("Set the date when your site went https, so Really Simple Social can switch your social account between http and https","really-simple-ssl-soc"), array($this,'get_option_start_date_social'), 'rlrsssl', 'rlrsssl_settings');

  add_settings_field('id_replace_ogurl', __("Replace &lt;meta og:url to http as well. This can cause errors in the FB console in combination with a 301 redirect.","really-simple-ssl-soc"), array($this,'get_option_replace_ogurl'), 'rlrsssl', 'rlrsssl_settings');

  add_settings_field('id_replace_to_http_on_home', __("Recover shares on the homepage","really-simple-ssl-soc"), array($this,'get_option_replace_to_http_on_home'), 'rlrsssl', 'rlrsssl_settings');


}

public function get_option_start_date_social() {

  $start_date_social = get_option('rsssl_soc_start_date_ssl');

  echo '<input id="rsssl_soc_start_date_ssl" name="rsssl_soc_start_date_ssl" size="40" type="date" value="'.$start_date_social.'" />';
}

public function get_option_replace_ogurl() {

  $replace_ogurl = get_option('rsssl_soc_replace_ogurl');
  echo '<input id="rsssl_soc_replace_ogurl" name="rsssl_soc_replace_ogurl" size="40" type="checkbox" value="1"' . checked( 1, $replace_ogurl, false ) ." />";
}

public function get_option_replace_to_http_on_home() {

  $replace_to_http_on_home = get_option('rsssl_soc_replace_to_http_on_home');
  echo '<input id="rsssl_soc_replace_to_http_on_home" name="rsssl_soc_replace_to_http_on_home" size="40" type="checkbox" value="1"' . checked( 1, $replace_to_http_on_home, false ) ." />";
}




}//class closure
