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

public function add_settings(){
  if (!class_exists("rsssl_admin")) return;
  global $really_simple_ssl;
  //add_settings_section('section_rssslpp', __("Pro", "really-simple-ssl-soc"), array($this, "section_text"), 'rlrsssl');
  register_setting( 'rlrsssl_options', 'rsssl_soc_start_date_ssl', array($this,'options_validate') );
  add_settings_field('id_start_date_social', __("Set the date when your site went https, so Really Simple Social can switch your social account between http and https","really-simple-ssl-soc"), array($this,'get_option_start_date_social'), 'rlrsssl', 'rlrsssl_settings');
}

public function get_option_start_date_social() {
  global $really_simple_ssl;
  $start_date_social = get_option('rsssl_soc_start_date_ssl');

  echo '<input id="rsssl_admin_mixed_content_fixer" name="rsssl_soc_start_date_ssl" size="40" type="date" value="'.$start_date_social.'" />';
}




}//class closure
