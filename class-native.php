<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_native {
  private static $_this;
  public $facebook;
  public $linkedin;
  public $twitter;
  public $google;
  public $pinterest;
  public $stumble;
  public $debug = false;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;
  add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 100);
  add_action('wp_ajax_nopriv_rsssl_get_likes', array($this, 'get_likes') );
  add_action('wp_ajax_rsssl_get_likes', array($this, 'get_likes') );
  add_action('wp_ajax_rsssl_clear_likes', array($this, 'clear_likes') );
  add_action('wp_ajax_nopriv_rsssl_clear_likes', array($this, 'clear_likes') );
  add_filter('the_content',  array($this, 'like_buttons_content_filter'));
  add_shortcode('rsssl_share_buttons', array($this, 'insert_shortcode_buttons') );
  add_filter('script_loader_tag', array($this, 'filter_pinterest_script'), 10,3);
  add_filter('rsssl_soc_share_buttons', array($this, 'social_share_buttons_html'), 10, 1);
  add_action('plugins_loaded',array($this, 'initialize'));

}

static function this() {
  return self::$_this;
}



public function initialize(){
  $services = get_option('rsssl_social_services');
  $this->facebook   = (isset($services['facebook']) && $services['facebook']) ? true : false;
  $this->linkedin   = (isset($services['linkedin']) && $services['linkedin']) ? true : false;
  $this->twitter    = (isset($services['twitter']) && $services['twitter']) ? true : false;
  $this->google     = (isset($services['google']) && $services['google']) ? true : false;
  $this->stumble     = (isset($services['stumble']) && $services['stumble']) ? true : false;
  $this->pinterest  = (isset($services['pinterest']) && $services['pinterest']) ? true : false;
}


public function get_likes(){
  if (!isset($_GET['post_id'])) return;
  $post_id = intval($_GET['post_id']);

  if ($post_id == 0) {
    $url = home_url();
  } else {
    $url = get_permalink($post_id);
  }

  if ($this->debug) $url = "https://really-simple-ssl.com";

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
  if ($this->facebook) {
    if ($get_http)      $fb_likes = $this->retrieve_fb_likes($url_http);
    if ($get_https)     $fb_likes += $this->retrieve_fb_likes($url_https);
    if ($get_httpwww)   $fb_likes += $this->retrieve_fb_likes($url_httpwww);
    if ($get_httpswww)  $fb_likes += $this->retrieve_fb_likes($url_httpswww);
  }

  $twitter_likes = 0;
  if ($this->twitter) {
    if ($get_http)      $twitter_likes = $this->retrieve_twitter_likes($url_http);
    if ($get_https)     $twitter_likes += $this->retrieve_twitter_likes($url_https);
    if ($get_httpwww)   $twitter_likes += $this->retrieve_twitter_likes($url_httpwww);
    if ($get_httpswww)  $twitter_likes += $this->retrieve_twitter_likes($url_httpswww);
  }

  $google_likes = 0;
  //google seems to return the correct likes anyway.
  if ($this->google) {
    $google_likes = $this->retrieve_google_likes($url_https);
    if ($get_http)      $google_likes = $this->retrieve_google_likes($url_http);
    if ($get_https)     $google_likes += $this->retrieve_google_likes($url_https);
    if ($get_httpwww)   $google_likes += $this->retrieve_google_likes($url_httpwww);
    if ($get_httpswww)  $google_likes += $this->retrieve_google_likes($url_httpswww);
  }

  $linkedin_likes = 0;
  if ($this->linkedin) {
    if ($get_http)      $linkedin_likes = $this->retrieve_linkedin_likes($url_http);
    if ($get_https)     $linkedin_likes += $this->retrieve_linkedin_likes($url_https);
    if ($get_httpwww)   $linkedin_likes += $this->retrieve_linkedin_likes($url_httpwww);
    if ($get_httpswww)  $linkedin_likes += $this->retrieve_linkedin_likes($url_httpswww);
  }

  $stumble_likes = 0;
  if ($this->stumble) {
    if ($get_http)      $stumble_likes = $this->retrieve_stumbleupon_likes($url_http);
    if ($get_https)     $stumble_likes += $this->retrieve_stumbleupon_likes($url_https);
    if ($get_httpwww)   $stumble_likes += $this->retrieve_stumbleupon_likes($url_httpwww);
    if ($get_httpswww)  $stumble_likes += $this->retrieve_stumbleupon_likes($url_httpswww);
  }

  $pinterest_likes = 0;

  if ($this->pinterest) {
    if ($get_http)      $pinterest_likes = $this->retrieve_pinterest_likes($url_http);
    if ($get_https)     $pinterest_likes += $this->retrieve_pinterest_likes($url_https);
    if ($get_httpwww)   $pinterest_likes += $this->retrieve_pinterest_likes($url_httpwww);
    if ($get_httpswww)  $pinterest_likes += $this->retrieve_pinterest_likes($url_httpswww);
  }

  $out = array(
        'facebook'  => $this->convert_nr($fb_likes),
        'twitter'   => $this->convert_nr($twitter_likes),
        'gplus'     => $this->convert_nr($google_likes),
        'stumble'   => $this->convert_nr($stumble_likes),
        'linkedin'  => $this->convert_nr($linkedin_likes),
        'pinterest' => $this->convert_nr($pinterest_likes),
      );
  die(json_encode($out));

}


/*

  Clear the likes for a specific url

*/

public function clear_likes(){
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

  $this->clear_cached_likes($url_https);
  $this->clear_cached_likes($url_httpswww);
  $this->clear_cached_likes($url_httpwww);
  $this->clear_cached_likes($url_http);

  return 'success';
  die();
}

/*


*/

public function get_cached_likes_total($type, $post_id){

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

  $likes = 0;
  if ($get_http)      $likes = $this->get_cached_likes($type, $url_http);
  if ($get_https)     $likes += $this->get_cached_likes($type,$url_https);
  if ($get_httpwww)   $likes += $this->get_cached_likes($type,$url_httpwww);
  if ($get_httpswww)  $likes += $this->get_cached_likes($type,$url_httpswww);

  if ($likes==0) $likes="";
  return $likes;

}

private function get_cached_likes($type, $url){

  if ($type=="facebook") $share_cache = get_transient('rsssl_fb_shares');
  if ($type=="twitter") $share_cache = get_transient('rsssl_twitter_shares');
  if ($type=="google") $share_cache = get_transient('rsssl_google_shares');
  if ($type=="linkedin") $share_cache = get_transient('rsssl_linkedin_shares');
  if ($type=="stumble") $share_cache = get_transient('rsssl_stumble_shares');
  if ($type=="pinterest") $share_cache = get_transient('rsssl_pinterest_shares');

  if (!$share_cache || !isset($share_cache[$url])) {
     return 0;
   } else {
     return $share_cache[$url];
   }
}

private function clear_cached_likes($url){

  $share_cache = get_transient('rsssl_fb_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_fb_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $share_cache = get_transient('rsssl_twitter_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_twitter_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $share_cache = get_transient('rsssl_google_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_google_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $share_cache = get_transient('rsssl_linkedin_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_linkedin_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $share_cache = get_transient('rsssl_stumble_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_stumble_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $share_cache = get_transient('rsssl_pinterest_shares');
  if ($share_cache) unset($share_cache[$url]);
  set_transient('rsssl_pinterest_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));
}



private function convert_nr($nr){

  if ($nr>=1000000) {
    return round($nr/1000000, 1)."m";
  }

  if ($nr>=1000) {
    return round($nr/1000, 1)."k";
  }

  if ($nr==0){
    return '';
  }

  return $nr;

}

private function retrieve_fb_likes($url){
  $shares = 0;
  $share_cache = get_transient('rsssl_fb_shares');
  $fb_access_token = get_option('rsssl_soc_fb_access_token');

  $auth="";
  if ($fb_access_token) $auth = '&access_token='.$fb_access_token;
  $request = wp_remote_get('https://graph.facebook.com/v2.9/?fields=engagement&id='.$url.$auth);

  //https://developers.facebook.com/tools/accesstoken/

  if ($request["response"]["code"]==200) {
    $json = wp_remote_retrieve_body($request);
    $output = json_decode( $json );
    $shares = $output->engagement->reaction_count + $output->engagement->comment_count + $output->engagement->share_count+ $output->engagement->comment_plugin_count;
  }

  $share_cache[$url] = $shares;
  set_transient('rsssl_fb_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));
  return $shares;
}

private function retrieve_twitter_likes($url){
  $share_cache = get_transient('rsssl_twitter_shares');
  $request = wp_remote_get('http://opensharecount.com/count.json?url='.$url);
  $json = wp_remote_retrieve_body($request);
  $output = json_decode( $json );
  $shares = 0;
  if (!empty($output)){
    $shares = $output->count;
  }
  $share_cache[$url] = $shares;
  set_transient('rsssl_twitter_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));
  return intval($shares);
}

private function retrieve_google_likes($url){
  $share_cache = get_transient('rsssl_google_shares');

  // $request = wp_remote_get('https://plusone.google.com/_/+1/fastbutton?url='.urlencode($url).'&count=true');
  // $json = wp_remote_retrieve_body($request);
  // preg_match('/c: ([0-9.]+) /', $json, $matches);
  // $shares = 0;
  // if (isset($matches[1])) $shares = $matches[1];
  // $share_cache[$url] = $shares;
  // set_transient('rsssl_google_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode($url).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
  $curl_results = curl_exec ($curl);
  curl_close ($curl);
  $json = json_decode($curl_results, true);

  $shares = isset($json[0]['result']['metadata']['globalCounts']['count'])?intval( $json[0]['result']['metadata']['globalCounts']['count'] ):0;

  $share_cache[$url] = $shares;
  set_transient('rsssl_google_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  return intval($shares);
}

private function retrieve_linkedin_likes($url){
  $share_cache = get_transient('rsssl_linkedin_shares');
  $request = wp_remote_get('http://www.linkedin.com/countserv/count/share?url='.urlencode($url).'&format=json');
  $json = wp_remote_retrieve_body($request);
  $output = json_decode( $json );
  $shares = $output->count;
  $share_cache[$url] = $shares;
  set_transient('rsssl_linkedin_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  return intval($shares);
}

private function retrieve_stumbleupon_likes($url){
  $share_cache = get_transient('rsssl_stumble_shares');
  $request = wp_remote_get('https://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$url);
  $json = wp_remote_retrieve_body($request);
  $output = json_decode( $json );
  $shares = 0;

  if (!empty($output) && $output->result->in_index==1) {
    $shares = $output->result->views;
  }
  $share_cache[$url] = $shares;
  set_transient('rsssl_stumble_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  return intval($shares);
}

//https://vk.com/share.php?act=count&url=https://really-simple-ssl.com

private function retrieve_pinterest_likes($url) {
  $shares = 0;
  $share_cache = get_transient('rsssl_pinterest_shares');
  $request = wp_remote_get('http://api.pinterest.com/v1/urls/count.json?&url='.$url);

  $json = wp_remote_retrieve_body($request);

  $json = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $json);
  $output = json_decode( $json );

  if (!empty($output)) {
    $shares = $output->count;
  }
  $share_cache[$url] = $shares;
  set_transient('rsssl_pinterest_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", DAY_IN_SECONDS));

  return intval($shares);
}


/*

    Add like buttons to bottom of all posts and pages.

*/

public function like_buttons_content_filter($content){
    if ($this->show_buttons()) {
      // show the buttons
      // not on homepage, but do show them on blogs overview page (is_front_page)
      // always when left is enabled.
        if ((is_home() || !is_front_page() ) || get_option('rsssl_inline_or_left') == "left" )  {
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
  $title = "";
  if ($post) {
    $url = get_permalink($post);
    $post_id = $post->ID;
    $title = $post->post_title;
  }

  $fb_share_url = 'https://www.facebook.com/share.php?u=';

  if (get_option('rsssl_fb_button_type')=='like') {
    $fb_share_url = 'https://www.facebook.com/plugins/like.php?href=';
  }

  //load template from theme directory if available

  $old_or_new_look = get_option('rsssl_use_30_styling');

  if ($old_or_new_look == FALSE) {
    $file = rsssl_soc_path . 'templates/sharing-buttons.html';
    $theme_file = get_stylesheet_directory() . '/' . dirname(rsssl_soc_plugin) . '/sharing-buttons.html';
  } else {
    $file = rsssl_soc_path . 'templates/sharing-buttons-3.html';
    $theme_file = get_stylesheet_directory() . '/' . dirname(rsssl_soc_plugin) . '/sharing-buttons-3.html';
  }

  if (file_exists($theme_file)) {
    $file = $theme_file;
  }
  $html = file_get_contents($file);

  $fb_shares = $this->get_cached_likes_total('facebook', $post_id);

  $linkedin_shares = $this->get_cached_likes_total('linkedin', $post_id);
  $twitter_shares = $this->get_cached_likes_total('twitter', $post_id);
  $google_shares = $this->get_cached_likes_total('google', $post_id);
  $stumble_shares = $this->get_cached_likes_total('stumble', $post_id);
  $pinterest_shares = $this->get_cached_likes_total('pinterest', $post_id);

  $html = str_replace(array("[POST_ID]", "[FB_SHARE_URL]", "[URL]", "[TITLE]"), array($post_id, $fb_share_url, $url, $title), $html);
  $html = str_replace(array("[fb_shares]", "[linkedin_shares]", "[twitter_shares]", "[google_shares]", "[stumble_shares]", "[pinterest_shares]"), array($fb_shares, $linkedin_shares, $twitter_shares, $google_shares, $stumble_shares, $pinterest_shares), $html);
  $html = apply_filters('rsssl_soc_share_buttons', $html);

  if(get_option('rsssl_inline_or_left') == "left") {
    $html = str_replace('rsssl_soc' , 'rsssl_soc rsssl_left' , $html);
  }

  return $html;
}


/*
    Remove buttons that are not used currently
*/

public function social_share_buttons_html($html){
  $services = get_option('rsssl_social_services');


  $patterns = array();
  if (!$this->facebook)  $patterns[] = '/<a class="post-share facebook".*<\/a>/i';
  if (!$this->linkedin)  $patterns[] = '/<a class="post-share linkedin".*<\/a>/i';
  if (!$this->twitter)   $patterns[] = '/<a class="post-share twitter".*<\/a>/i';
  if (!$this->google)    $patterns[] = '/<a class="post-share gplus".*<\/a>/i';
  if (!$this->stumble)   $patterns[] = '/<a class="post-share stumble".*<\/a>/i';
  if (!$this->pinterest) $patterns[] = '/<a class="post-share pinterest".*<\/a>/i';

  $html = preg_replace($patterns, '', $html);

  return $html;
}


public function enqueue_scripts() {
    $version = (strpos(home_url(), "localhost")===false) ? time() : "";
    $services = get_option('rsssl_social_services');
    $old_or_new_look = get_option('rsssl_use_30_styling');

if ($old_or_new_look == FALSE) {
    wp_enqueue_style( 'rsssl_social',plugin_dir_url( __FILE__ ).'assets/css/style.css', array(), rsssl_soc_version);
    wp_enqueue_script('rsssl_social', plugin_dir_url( __FILE__ )."assets/js/likes.js", array('jquery'),rsssl_soc_version, true);
  } else {
    wp_enqueue_style( 'rsssl_social',plugin_dir_url( __FILE__ ).'assets/css/style-3.css', array(), rsssl_soc_version);
    wp_enqueue_style( 'rsssl_social_fontello' ,plugin_dir_url( __FILE__ ).'assets/font/fontello-icons/css/fontello.css', array(), rsssl_soc_version);
    wp_enqueue_script('rsssl_social', plugin_dir_url( __FILE__ )."assets/js/likes-3.js", array('jquery'),rsssl_soc_version, true);
  }

    $url = home_url();
    global $post;
    if ($post) {
      $url = get_permalink($post);
    }

    $use_cache = true;

    //check a transient as well, if the transient has expired, we will set set usecache to true, so it will retrieve the shares fresh.
    $share_cache = get_transient('rsssl_fb_shares');
    if ($this->debug || (defined('rsssl_social_no_cache') && rsssl_social_no_cache) || !$share_cache || !isset($share_cache[$url])) {
      $use_cache = false;
    }

    if ($this->pinterest) {
      wp_enqueue_script('rsssl_pinterest', "//assets.pinterest.com/js/pinit.js", array(),"", true);
    }

    wp_localize_script('rsssl_social','rsssl_soc_ajax', array(
      'ajaxurl'=> admin_url( 'admin-ajax.php' ),
      'token' => wp_create_nonce('rsssl_social_nonce', 'token'),
      'use_cache' => $use_cache,
    ));

  }


/*
    add attributes to pinterest src
*/

public function filter_pinterest_script($tag, $handle, $src){
  if ($handle=='rsssl_pinterest'){
    return str_replace('<script', '<script async defer data-pin-hover="true" ', $tag);
  }
  return $tag;
}

  /*

    Genereate the editor html on a page with the shortcode.

  */

  public function insert_shortcode_buttons($atts, $content = null, $tag = '') {
    ob_start();

    echo $this->generate_like_buttons();

    return ob_get_clean();
}




}//class closure
