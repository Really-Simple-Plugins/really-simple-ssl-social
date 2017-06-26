<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_native {
  private static $_this;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;
  add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 100);
  add_action('wp_ajax_nopriv_rsssl_get_likes', array($this, 'get_likes') );
  add_action('wp_ajax_rsssl_get_likes', array($this, 'get_likes') );
  add_filter('the_content',  array($this, 'like_buttons_content_filter'));
  add_shortcode('rsssl_share_buttons', array($this, 'insert_shortcode_buttons') );
}

static function this() {
  return self::$_this;
}

public function get_likes(){
  if (!isset($_GET['post_id'])) return;
  $post_id = intval($_GET['post_id']);

  if ($post_id == 0) {
    $url = home_url();
  } else {
    $url = get_permalink($post_id);
  }

  //make sure the current home_url is https, as this is a really simple ssl add on.
  $url_https = str_replace("http://", "https://", $url);

  $url_https = str_replace("https://www.", "https://", $url_https);
  $url_httpswww = str_replace("https://", "https://www.", $url_https);
  $url_httpwww = str_replace("https://", "http://", $url_httpswww);
  $url_http = str_replace("http://www.", "http://", $url_httpwww);

  $domains = get_option('rsssl_retrieval_domains');
  $get_http = isset($domains['http']) ? $domains['http'] : false;
  $get_https = isset($domains['https']) ? $domains['https'] : false;
  $get_httpwww = isset($domains['httpwww']) ? $domains['httpwww'] : false;
  $get_httpswww = isset($domains['httpswww']) ? $domains['httpswww'] : false;

  //if nothing set, set them all.
  if (!$get_http && !$get_https && !$get_httpwww && !$get_httpswww) {
    $get_http = true;
    $get_https = true;
    $get_httpwww = true;
    $get_httpswww = true;
  }

  //get likes for both http and https
  $fb_likes = 0;
  if ($get_http)      $fb_likes = $this->retrieve_fb_likes($url_http);
  if ($get_https)     $fb_likes += $this->retrieve_fb_likes($url_https);
  if ($get_httpwww)   $fb_likes += $this->retrieve_fb_likes($url_httpwww);
  if ($get_httpswww)  $fb_likes += $this->retrieve_fb_likes($url_httpswww);

  $twitter_likes = 0;
  if ($get_http)      $twitter_likes = $this->retrieve_twitter_likes($url_http);
  if ($get_https)     $twitter_likes += $this->retrieve_twitter_likes($url_https);
  if ($get_httpwww)   $twitter_likes += $this->retrieve_twitter_likes($url_httpwww);
  if ($get_httpswww)  $twitter_likes += $this->retrieve_twitter_likes($url_httpswww);

  $google_likes = 0;
  if ($get_http)      $google_likes = $this->retrieve_google_likes($url_http);
  if ($get_https)     $google_likes += $this->retrieve_google_likes($url_https);
  if ($get_httpwww)   $google_likes += $this->retrieve_google_likes($url_httpwww);
  if ($get_httpswww)  $google_likes += $this->retrieve_google_likes($url_httpswww);

  $stumble_likes = 0;
  if ($get_http)      $stumble_likes = $this->retrieve_stumbleupon_likes($url_http);
  if ($get_https)     $stumble_likes += $this->retrieve_stumbleupon_likes($url_https);
  if ($get_httpwww)   $stumble_likes += $this->retrieve_stumbleupon_likes($url_httpwww);
  if ($get_httpswww)  $stumble_likes += $this->retrieve_stumbleupon_likes($url_httpswww);

  $out = array(
        'facebook'  => $fb_likes,
        'twitter'   => $twitter_likes,
        'gplus'     => $google_likes,
        'stumble'   => $stumble_likes,
      );

  die(json_encode($out));

}


private function retrieve_fb_likes($url){

  $shares = 0;
  $share_cache = get_transient('rsssl_fb_shares');

//  if (!$share_cache || !isset($share_cache[$url])) {
      $fb_access_token = get_option('rsssl_soc_fb_access_token');

      $auth="";

      if ($fb_access_token) {
        $auth = '&access_token='.$fb_access_token;
      }
      $request = wp_remote_get('https://graph.facebook.com/v2.9/?fields=engagement&id='.$url.$auth);
      //https://developers.facebook.com/tools/accesstoken/

      //$request = wp_remote_get('https://graph.facebook.com/?fields=og_object%7Blikes.summary(true).limit(0)%7D,share&id='.$url.$auth);

      if ($request["response"]["code"]==200) {
        $json = wp_remote_retrieve_body($request);
        $output = json_decode( $json );
        $shares = $output->engagement->reaction_count + $output->engagement->comment_count + $output->engagement->share_count+ $output->engagement->comment_plugin_count;
        $share_cache[$url] = $shares;

        set_transient('rsssl_fb_shares', $share_cache, DAY_IN_SECONDS);
      } else {
        error_log("failed retrieving FB likes, auth error");
      }
  //  } else {
  //    $shares = $share_cache[$url];
  //  }
   //$shares = rand ( 10, 200);
  return $shares;
}



private function retrieve_twitter_likes($url){
  $share_cache = get_transient('rsssl_twitter_shares');
  if (!$share_cache || !isset($share_cache[$url])) {
    $request = wp_remote_get('http://opensharecount.com/count.json?url='.$url);
    $json = wp_remote_retrieve_body($request);
    $output = json_decode( $json );
    $shares = $output->count;
    $share_cache[$url] = $shares;
    set_transient('rsssl_twitter_shares', $share_cache, DAY_IN_SECONDS);
  } else {
     $shares = $share_cache[$url];
  }
  return intval($shares);
}

private function retrieve_google_likes($url){
  $share_cache = get_transient('rsssl_google_shares');
  if (!$share_cache || !isset($share_cache[$url])) {
    $request = wp_remote_get('https://plusone.google.com/_/+1/fastbutton?url='.urlencode($url).'&count=true');
    $json = wp_remote_retrieve_body($request);
    preg_match('/c: ([0-9.]+) /', $json, $matches);
    $shares = 0;
    if (isset($matches[1])) $shares = $matches[1];
    $share_cache[$url] = $shares;
    set_transient('rsssl_google_shares', $share_cache, DAY_IN_SECONDS);
  } else {
     $shares = $share_cache[$url];
  }

  return intval($shares);
}


private function retrieve_stumbleupon_likes($url){
  $share_cache = get_transient('rsssl_stumble_shares');
  if (!$share_cache || !isset($share_cache[$url])) {
    $request = wp_remote_get('https://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$url);
    $json = wp_remote_retrieve_body($request);
    $output = json_decode( $json );
    $shares = 0;
    if ($output->result->in_index==1) {
      $shares = $output->result->views;
    }
    $share_cache[$url] = $shares;
    set_transient('rsssl_stumble_shares', $share_cache, DAY_IN_SECONDS);
  } else {
     $shares = $share_cache[$url];
  }
  return intval($shares);
}


/*

    Add like buttons to bottom of all posts and pages.

*/

public function like_buttons_content_filter($content){
    //check if this posttype needs the buttons.
    if ($this->show_buttons()) {
      $html = $this->generate_like_buttons();
      $position = get_option('rsssl_button_position');

      //position depending on setting
      if ($position == 'bottom') {
        $content = $content.$html;
      } elseif($position == 'both'){
        $content = $html.$content.$html;
      } else {
        $content = $html.$content;
      }

    }

    return $content;
}


public function show_buttons(){
  //$post_type = "rsssl_homepage";

  global $post;
  $post_id = 0;
  if ($post) {
    $post_id = $post->ID;
  }

  $post_type = get_post_type($post_id);

  $rsssl_buttons_on_post_types = get_option('rsssl_buttons_on_post_types');
  if (isset($rsssl_buttons_on_post_types[$post_type]) && $rsssl_buttons_on_post_types[$post_type]) {
    return true;
  }

  return false;
}
/*

    Generate like buttons to be used in either shortcode or content filter

*/

public function generate_like_buttons($single = true){
  $html = "";
  $url = home_url();

  global $post;
  $post_id = 0;
  if ($post) {
    $url = get_permalink($post);
    $post_id = $post->ID;
  }

  $url_http = str_replace("https://", "http://", $url);
  $url_https = $url;

  $fb_share_url = 'https://www.facebook.com/share.php?u=';

  if (get_option('rsssl_fb_button_type')=='like') {
    $fb_share_url = 'https://www.facebook.com/plugins/like.php?href=';
  }

  //load template from theme directory if available
  $file = rsssl_soc_path . 'templates/sharing-buttons.html';
  $theme_file = get_stylesheet_directory() . '/' . dirname(rsssl_soc_plugin) . '/sharing-buttons.html';
  if (file_exists($theme_file)) {
    $file = $theme_file;
  }
  $html = file_get_contents($file);
  $html = str_replace(array("[POST_ID]", "[FB_SHARE_URL]", "[URL]"), array($post_id, $fb_share_url, $url), $html);
  $html = apply_filters('rsssl_soc_share_buttons', $html);

  return $html;
}




public function enqueue_scripts() {
    $version = (strpos(home_url(), "localhost")===false) ? time() : "";
    wp_enqueue_style( 'rsssl_social',plugin_dir_url( __FILE__ ).'/assets/css/style.css', array(), rsssl_soc_version);
    $url = home_url();

    global $post;
    if ($post) {
      $url = get_permalink($post);
    }
    wp_enqueue_script('rsssl_social', plugin_dir_url( __FILE__ )."assets/js/likes.js", array('jquery'),rsssl_soc_version, true);
    wp_localize_script('rsssl_social','rsssl_soc_ajax', array(
      'ajaxurl'=> admin_url( 'admin-ajax.php' ),
      'token' => wp_create_nonce('rsssl_social_nonce', 'token'),
    ));

  }


  /*

    Genereate the editor html on a page with the shortcode.

  */

  public function insert_shortcode_buttons($atts = [], $content = null, $tag = '') {

    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    ob_start();

    // override default attributes with user attributes
    //$atts = shortcode_atts(['post_type' => 'post',], $atts, $tag);

    echo $this->generate_like_buttons();


    return ob_get_clean();
}




}//class closure
