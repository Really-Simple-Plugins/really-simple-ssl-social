<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_admin {
  private static $_this;
  public $setup_built_in_buttons=false;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;

  add_action('admin_init', array($this, 'init'), 15 );

  register_activation_hook( rsssl_soc_plugin_file , array( $this, 'install' ) );
  add_action( 'plugins_loaded', array($this, 'check_for_upgrade') );

  add_action("update_option_rsssl_insert_custom_buttons", array($this, "maybe_install_built_in_buttons"), 10,3);

  add_action("update_option_rsssl_use_30_styling", array($this, "maybe_init_styling_3"), 10,3);
  add_action("update_option_rsssl_fb_button_type", array($this, "maybe_init_fb_button_types"), 10,3);
  add_action("update_option_rsssl_buttons_on_post_types", array($this, "maybe_init_buttons_on_post_types"), 10,3);
  add_action("update_option_rsssl_retrieval_services", array($this, "maybe_init_retrieval_services"), 10,3);
  }


static function this() {
  return self::$_this;
}

public function maybe_init_styling_3($oldvalue, $newvalue, $option){
  if ($this->setup_built_in_buttons) {
    $this->setup_built_in_buttons_settings();
  }
}

/*set the date to an inital value of today. */

public function maybe_init_fb_button_types($oldvalue, $newvalue, $option){
  if ($this->setup_built_in_buttons) {
    $this->setup_built_in_buttons_settings();
  }
}

public function maybe_init_buttons_on_post_types($oldvalue, $newvalue, $option){
  if ($this->setup_built_in_buttons) {
    $this->setup_built_in_buttons_settings();
  }
}

public function maybe_init_retrieval_services($oldvalue, $newvalue, $option){
  if ($this->setup_built_in_buttons) {
    $this->setup_built_in_buttons_settings();
  }
}

/**
 *
 * Setup default option values after switching to built in buttons
 *
 * @since  3.0
 *
 * @access public
 *
 */

public function setup_built_in_buttons_settings(){
  if (!get_option('rsssl_use_30_styling')) {
    update_option("rsssl_use_30_styling", true);
  }

  if (!get_option('rsssl_fb_button_type')) {
    update_option("rsssl_fb_button_type", 'share');
  }

    $rsssl_buttons_on_post_types = get_option('rsssl_buttons_on_post_types');
    $rsssl_buttons_on_post_types['post'] = true;
    update_option("rsssl_buttons_on_post_types", $rsssl_buttons_on_post_types);

    $http = false;
    $https = false;
    $httpwww = false;
    $httpswww = false;

    if (strpos(home_url(), "www.")!==FALSE) {
      $httpwww = true;
      $httpswww = true;
    } else {
      $http = true;
      $https = true;
    }

    $domains = array(
      'http' => $http,
      'https' => $https,
      'httpwww' => $httpwww,
      'httpswww' => $httpswww,
    );
    update_option("rsssl_retrieval_domains",$domains );

    if (!get_option('rsssl_social_services')) {
      $services = array(
        'facebook' => true,
        'linkedin' => true,
        'google' => true,
        'pinterest' => true,
      );

      update_option("rsssl_social_services",$services );
    }

}


/**
 *
 * check if the built in buttons should be installed/configured.
 * @hooked update_option_$option
 *
 * @since  3.0
 *
 * @access public
 *
 */

public function maybe_install_built_in_buttons($oldvalue, $newvalue, $option){

  if ($newvalue && ($newvalue != $oldvalue)) {
    //keep track of this switch, so we can override the saving of these options
    $this->setup_built_in_buttons = true;
    $this->setup_built_in_buttons_settings();
  }
}


/*set the date to an inital value of today. */

public function install(){

  if (!get_option('rsssl_soc_start_date_ssl')) {
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

public function options_validate_boolean_array($input){

  if (is_array($input)) {
    $input = array_map(array($this, 'options_validate_boolean'), $input);
  } else {
    $input = $input ? true : false;
  }
  return $input;
}

public function add_settings(){
  if (!class_exists("rsssl_admin")) return;

  //add_settings_section('section_rssslpp', __("Pro", "really-simple-ssl-soc"), array($this, "section_text"), 'rlrsssl');
  register_setting( 'rlrsssl_options', 'rsssl_soc_start_date_ssl', array($this,'options_validate') );
  register_setting( 'rlrsssl_options', 'rsssl_soc_replace_ogurl', array($this,'options_validate_boolean') );
  register_setting( 'rlrsssl_options', 'rsssl_soc_replace_to_http_on_home', array($this,'options_validate_boolean') );
  register_setting( 'rlrsssl_options', 'rsssl_insert_custom_buttons', array($this,'options_validate_boolean') );
  register_setting( 'rlrsssl_options', 'rsssl_soc_fb_access_token', array($this,'options_validate') );

  register_setting( 'rlrsssl_options', 'rsssl_buttons_on_post_types', array($this,'options_validate_boolean_array') );
  register_setting( 'rlrsssl_options', 'rsssl_fb_button_type', array($this,'options_validate') );
  register_setting( 'rlrsssl_options', 'rsssl_button_position', array($this,'options_validate') );
  register_setting( 'rlrsssl_options', 'rsssl_retrieval_domains', array($this,'options_validate_boolean_array') );
  register_setting( 'rlrsssl_options', 'rsssl_social_services', array($this,'options_validate_boolean_array') );
  register_setting( 'rlrsssl_options', 'rsssl_inline_or_left', array($this,'options_validate') );
  register_setting( 'rlrsssl_options', 'rsssl_use_30_styling', array($this,'options_validate') );

  if (!get_option('rsssl_insert_custom_buttons')) {
    add_settings_field('id_start_date_social', __("SSL switch date","really-simple-ssl-soc"), array($this,'get_option_start_date_social'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('id_replace_ogurl', __("Use http:// for the meta og:url","really-simple-ssl-soc"), array($this,'get_option_replace_ogurl'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('id_replace_to_http_on_home', __("Recover shares on the homepage","really-simple-ssl-soc"), array($this,'get_option_replace_to_http_on_home'), 'rlrsssl', 'rlrsssl_settings');
  }

  add_settings_field('rsssl_insert_custom_buttons', __("Use the built in share buttons","really-simple-ssl-soc"), array($this,'get_option_insert_custom_buttons'), 'rlrsssl', 'rlrsssl_settings');
  if (get_option('rsssl_insert_custom_buttons')) {
    add_settings_field('rsssl_social_services', __("Social services you want to use","really-simple-ssl-soc"), array($this,'get_option_social_services'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_fb_access_token', __("Facebook app token","really-simple-ssl-soc"), array($this,'get_option_fb_access_token'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_buttons_on_post_types', __("Which posttypes to use the buttons on","really-simple-ssl-soc"), array($this,'get_option_buttons_on_post_types'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_fb_button_type', __("Use share or like","really-simple-ssl-soc"), array($this,'get_option_fb_button_type'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_retrieval_domains', __("Domains to retrieve shares","really-simple-ssl-soc"), array($this,'get_option_retrieval_domains'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_button_position', __("Position of buttons","really-simple-ssl-soc"), array($this,'get_option_button_position'), 'rlrsssl', 'rlrsssl_settings');
    add_settings_field('rsssl_use_30_styling', __("Use 3.0 styling for buttons","really-simple-ssl-soc"), array($this,'get_option_rsssl_use_30_styling'), 'rlrsssl', 'rlrsssl_settings');
  if (get_option('rsssl_use_30_styling')) {
    add_settings_field('rsssl_inline_or_left', __("Show buttons inline or as left sidebar","really-simple-ssl-soc"), array($this,'get_option_rsssl_inline_or_left'), 'rlrsssl', 'rlrsssl_settings');
  }

  }

}

public function get_option_start_date_social() {

  $start_date_social = get_option('rsssl_soc_start_date_ssl');

  echo '<input id="rsssl_soc_start_date_ssl" name="rsssl_soc_start_date_ssl" size="40" type="date" value="'.$start_date_social.'" />';
  RSSSL()->rsssl_help->get_help_tip(__("Enter the date on which you switched over to https. You can use the date format you use in the general WordPress settings.", "really-simple-ssl-soc"));
}

public function get_option_replace_ogurl() {

  $replace_ogurl = get_option('rsssl_soc_replace_ogurl');
  echo '<input id="rsssl_soc_replace_ogurl" name="rsssl_soc_replace_ogurl" size="40" type="checkbox" value="1"' . checked( 1, $replace_ogurl, false ) ." />";
  RSSSL()->rsssl_help->get_help_tip(__("Use with caution. This can cause errors in the FB console in combination with a 301 redirect.", "really-simple-ssl-soc"));
}

public function get_option_insert_custom_buttons() {
  $insert_custom_buttons = get_option('rsssl_insert_custom_buttons');
  echo '<input id="rsssl_insert_custom_buttons" name="rsssl_insert_custom_buttons" size="40" type="checkbox" value="1"' . checked( 1, $insert_custom_buttons, false ) ." />";
  RSSSL()->rsssl_help->get_help_tip(__("Enable to use the built in share buttons that retrieve the shares for both http and https domain. To get the sharecounts for Twitter, you can register at http://opensharecount.com/.", "really-simple-ssl-soc"));
}

public function get_option_buttons_on_post_types() {
  $rsssl_buttons_on_post_types = get_option('rsssl_buttons_on_post_types');

  $args = array(
     'public'   => true,
  );
  $post_types = get_post_types( $args);
  $post_types_query = array();

  foreach ( $post_types as $post_type ) {
    $checked = false;
    if (isset($rsssl_buttons_on_post_types[$post_type])) {
      $checked = checked( 1, $rsssl_buttons_on_post_types[$post_type], false );
    }
    ?>
    <input name="rsssl_buttons_on_post_types[<?php echo $post_type?>]" size="40" type="checkbox" value="1" <?php echo $checked ?> /> <?php echo $post_type?><br>
    <?php
  }
}

public function get_option_button_position() {
  $rsssl_button_position = get_option('rsssl_button_position');
  ?>
  <select name="rsssl_button_position">
    <option value="top" <?php if ($rsssl_button_position=="top") echo "selected"?>>Top
    <option value="bottom" <?php if ($rsssl_button_position=="bottom") echo "selected"?>>Bottom
    <option value="both" <?php if ($rsssl_button_position=="both") echo "selected"?>>Both
  </select>
  <?php
  RSSSL()->rsssl_help->get_help_tip(__("Choose where you want to position the share button(s)", "really-simple-ssl-soc"));
}


public function get_option_retrieval_domains() {
  $domains = get_option('rsssl_retrieval_domains');
  $http = isset($domains['http']) ? $domains['http'] : false;
  $https = isset($domains['https']) ? $domains['https'] : false;
  $httpwww = isset($domains['httpwww']) ? $domains['httpwww'] : false;
  $httpswww = isset($domains['httpswww']) ? $domains['httpswww'] : false;

  ?>
  <input type="checkbox" name="rsssl_retrieval_domains[http]" value="1" <?php checked( $http, "1"); ?>/><?php _e("Retrieve http://domain.com", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_retrieval_domains[https]" value="1" <?php checked( $https, "1"); ?>/><?php _e("Retrieve https://domain.com", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_retrieval_domains[httpwww]" value="1" <?php checked( $httpwww, "1"); ?>/><?php _e("Retrieve http://www.domain.com", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_retrieval_domains[httpswww]" value="1" <?php checked( $httpswww, "1"); ?>/><?php _e("Retrieve https://www.domain.com", "really-simple-ssl-soc")?><br>
  <?php
  RSSSL()->rsssl_help->get_help_tip(__("Choose which domains you want to retrieve the shares for. Sometimes Facebook returns different shares for www and non www, but sometimes they are the same. Configure accordingly.", "really-simple-ssl-soc"));
}

public function get_option_social_services() {
  $services = get_option('rsssl_social_services');
  $facebook = isset($services['facebook']) ? $services['facebook'] : false;
  $linkedin = isset($services['linkedin']) ? $services['linkedin'] : false;
  $twitter = isset($services['twitter']) ? $services['twitter'] : false;
  $google = isset($services['google']) ? $services['google'] : false;
  $stumble = isset($services['stumble']) ? $services['stumble'] : false;
  $pinterest = isset($services['pinterest']) ? $services['pinterest'] : false;
  ?>
  <input type="checkbox" name="rsssl_social_services[facebook]" value="1" <?php checked( $facebook, "1"); ?>/><?php _e("Facebook share button", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_social_services[linkedin]" value="1" <?php checked( $linkedin, "1"); ?>/><?php _e("Linkedin share button", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_social_services[twitter]" value="1" <?php checked( $twitter, "1"); ?>/><?php _e("Twitter  share button", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_social_services[google]" value="1" <?php checked( $google, "1"); ?>/><?php _e("Google share button", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_social_services[stumble]" value="1" <?php checked( $stumble, "1"); ?>/><?php _e("Stumble share button", "really-simple-ssl-soc")?><br>
  <input type="checkbox" name="rsssl_social_services[pinterest]" value="1" <?php checked( $pinterest, "1"); ?>/><?php _e("Pinterest share button", "really-simple-ssl-soc")?><br>

  <?php
  RSSSL()->rsssl_help->get_help_tip(__("Choose which social services you want to include sharing buttons.", "really-simple-ssl-soc"));
}


public function get_option_fb_button_type() {
  $rsssl_fb_button_type = get_option('rsssl_fb_button_type');
  ?>
  <select name="rsssl_fb_button_type">
    <option value="share" <?php if ($rsssl_fb_button_type=="share") echo "selected"?>>Share
    <option value="like" <?php if ($rsssl_fb_button_type=="like") echo "selected"?>>Like
  </select>
  <?php
  RSSSL()->rsssl_help->get_help_tip(__("Choose if you want to use the share or the like functionality of Facebook", "really-simple-ssl-soc"));
}

public function get_option_replace_to_http_on_home() {
  $replace_to_http_on_home = get_option('rsssl_soc_replace_to_http_on_home');
  echo '<input id="rsssl_soc_replace_to_http_on_home" name="rsssl_soc_replace_to_http_on_home" size="40" type="checkbox" value="1"' . checked( 1, $replace_to_http_on_home, false ) ." />";
  RSSSL()->rsssl_help->get_help_tip(__("When you enable this, share buttons generated by Really Simple Social will be inserted, which will retrieve likes from both the http and the https domain.", "really-simple-ssl-soc"));
}

public function get_option_fb_access_token() {
  $fb_access_token = get_option('rsssl_soc_fb_access_token');
  echo '<input id="rsssl_soc_fb_access_token" name="rsssl_soc_fb_access_token" size="40" type="text" value="'.$fb_access_token.'" />';
  //RSSSL()->rsssl_help->get_help_tip(__("To prevent rate limiting you need to create an app in facebook, then copy the user token here: https://developers.facebook.com/tools/accesstoken/", "really-simple-ssl-soc"));
  echo '<p>'.__('To prevent rate limiting you need to create an app in facebook, then copy the app token which you can find here: https://developers.facebook.com/tools/accesstoken/','really-simple-ssl-soc')."</p>";
}

public function get_option_rsssl_inline_or_left() {
  $rsssl_inline_or_left = get_option('rsssl_inline_or_left');
 ?>
  <select name="rsssl_inline_or_left">
    <option value="inline" <?php if ($rsssl_inline_or_left=="inline") echo "selected"?>>Inline
    <option value="left" <?php if ($rsssl_inline_or_left=="left") echo "selected"?>>Left
  </select>
 <?php
}

public function get_option_rsssl_use_30_styling() {
  $rsssl_use_30_styling = get_option('rsssl_use_30_styling');
  echo '<input id="rsssl_soc_replace_ogurl" name="rsssl_use_30_styling" size="40" type="checkbox" value="1"' . checked( 1, $rsssl_use_30_styling, false ) ." />";
  RSSSL()->rsssl_help->get_help_tip(__("Use the old or new look", "really-simple-ssl-soc"));
}

}//class closure
