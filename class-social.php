<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_social {
  private static $_this;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;

  add_filter("rsssl_fixer_output", array($this, "fix_social"));
  add_filter('update_easy_social_share_url', array($this, 'fix_easy_social_share'));

  /*
      Jetpack share recovery
  */

  add_filter( 'sharing_permalink', array($this, 'jetpack_fb_sharing_link' ), 10, 3);
  add_filter( 'jetpack_sharing_display_link', array($this, 'jetpack_fb_sharing_link' ), 10, 4);

}

static function this() {
  return self::$_this;
}


/**
*       Filter Jetpack Facebook sharing link
*
*/

public function jetpack_fb_sharing_link($url, $post_id, $id){

  if ($this->use_http($post_id)) {
    $url = str_replace("https://", "http://", $url);
  }

  return $url;

}

/**
*       Filter JetPack sharing links for non FB services.
*
*
*
*/

public function jetpack_sharing_display_link($url, $sharing_obj, $id, $args ){
  if ($this->use_http()) {
    $url = str_replace("https://", "http://", $url);
  }
  return $url;
}


/*
    Fix for easy social share buttons WordPress
*/
public function fix_easy_social_share($url) {

  if ($this->use_http()) {
    $url = str_replace("https://", "http://", $url);
  }
  return $url;
}

public function use_http($post_id=false){
  $use_http = TRUE;
  $have_post = false;

  if (is_home() || is_front_page()){
    if (get_option('rsssl_soc_replace_to_http_on_home') ){
      return true;
    }else{
      return false;
    }
  }

  //if an id was passed, get post by id
  if ($post_id) {
    $post = get_post($post_id);
    $have_post = true;
  }

  //if don't have a post yet by id, get the global one.
  if (!$have_post) {
    global $post;
  }

  if ($post) {
    $start_date = strtotime(get_option("rsssl_soc_start_date_ssl"));
    $publish_date = get_post_time('U', false, $post->ID);

    if ($start_date && ($publish_date > $start_date)){
      $use_http = FALSE;
    }

  }

  return apply_filters("rsssl_soc_use_http", $use_http);
}

// add_filter("rsssl_soc_use_http", "my_function_use_http");
// function my_function_use_http($use_http){
//   global $post;
//   $postid=0;
//   if ($post) $postid=$post->ID;
//
//   if(($postid==12) || ($postid==13)) {
//     $use_http = false;
//   }
//   return $use_http;
// }

public function fix_social($html) {
  if ($this->use_http()) {

    $preg_url = str_replace(array("/", "."),array("\/", "\."), home_url());

    $http_url = str_replace("https://", "http://", home_url());
    $https_url = home_url();
    $http_url_encoded = urlencode($http_url);
    $https_url_encoded = urlencode($https_url);
    $preg_url_encoded = str_replace(array("/", ".") , array("\/", "\."),$https_url_encoded);

    /*<meta og:url */
    $replace_ogurl = get_option('rsssl_soc_replace_ogurl');
    if ($replace_ogurl) {
      $html = str_replace('<meta property="og:url" content="'.$https_url, '<meta property="og:url" content="'.$http_url, $html);
      //$html = str_replace('rel="canonical" href="'.$https_url, 'rel="canonical" href="'.$http_url, $html);
    }

    //generic:
    $html = str_replace('data-url="'.$https_url, 'data-url="'.$http_url, $html);
    $html = str_replace('data-urlalt="'.$https_url, 'data-urlalt="'.$http_url, $html);


    /*shareaholic*/
    $pattern = '/shareaholic-canvas.*?data-link=[\'"]\K('.$preg_url.')/';
    $html = preg_replace($pattern, $http_url, $html, -1, $count);
    $html = str_replace("name='shareaholic:url' content='".$https_url, "name='shareaholic:url' content='".$http_url, $html);

    $html = str_replace('share_counts_url":"https:', 'share_counts_url":"http:', $html);

    /*default facebook like button */
    $html = str_replace('data-href="'.$https_url, 'data-href="'.$http_url, $html);
    $html = str_replace('<fb:like href="'.$https_url, '<fb:like href="'.$http_url, $html);

    $pattern = '/fb-like.*?data-href=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, $http_url, $html, -1, $count);

    /*Add to any */
    $html = str_replace('add_to/facebook?linkurl='.$https_url_encoded, 'add_to/facebook?linkurl='.$http_url_encoded, $html);
    $html = str_replace('add_to/twitter?linkurl='.$https_url_encoded, 'add_to/twitter?linkurl='.$http_url_encoded, $html);
    $html = str_replace('add_to/pinterest?linkurl='.$https_url_encoded, 'add_to/pinterest?linkurl='.$http_url_encoded, $html);
    $html = str_replace('add_to/google_plus?linkurl='.$https_url_encoded, 'add_to/google_plus?linkurl='.$http_url_encoded, $html);
    $html = str_replace('data-a2a-url="'.$https_url, 'data-a2a-url="'.$http_url, $html);
    $html = str_replace('addtoany.com/share#url='.$https_url_encoded, 'addtoany.com/share#url='.$http_url_encoded, $html);
    $html = str_replace('addtoany_special_service" data-url="'.$https_url, 'addtoany_special_service" data-url="'.$http_url, $html);

    /* Digg Digg */
    $html = str_replace('facebook.com/plugins/like.php?href='.$https_url_encoded, 'facebook.com/plugins/like.php?href='.$http_url_encoded, $html);

    /*Add this */
    $html = str_replace('addthis:url="https://', 'addthis:url="http://', $html);
    $html = str_replace("addthis:url='https://", "addthis:url='http://", $html);
    $pattern = '/addthis_sharing_toolbox.*?data-url=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, $http_url, $html, -1, $count);
    $html = str_replace('graph.facebook.com/?id=' . $https_url_encoded, 'graph.facebook.com/?id=' . $http_url_encoded, $html);

    /*Jetpack */
    $pattern = '/fb-share-button.*?data-href=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, $http_url, $html, -1, $count);

    /*sharedaddy*/
    $pattern = '/data-shared.*?href=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, $http_url, $html, -1, $count);

    //Easy Social Share Buttons 3
    $html = str_replace('data-essb-url="'.$https_url ,'data-essb-url="'.$http_url , $html);
    $html = str_replace('data-essb-twitter-url="'.$https_url ,'data-essb-twitter-url="'.$http_url , $html);

    /*Directly integrated in code */
    $html = str_replace('facebook.com/sharer.php?u=' . $https_url_encoded ,'facebook.com/sharer.php?u=' . $http_url_encoded , $html);
    $html = str_replace('facebook.com/plugins/like.php?href=' . $https_url , 'facebook.com/plugins/like.php?href='. $http_url , $html);

    //not encoded
    $html = str_replace('facebook.com/sharer.php?u=' . $https_url ,'facebook.com/sharer.php?u=' . $http_url , $html);
    $html = str_replace('facebook.com/plugins/like.php?href=' . $https_url , 'facebook.com/plugins/like.php?href='. $http_url , $html);

    $html = apply_filters('rsssl_social_use_http_urls', $html);
    //verification
    $html = str_replace('data-rsssl', 'data-rssslsocial=1 data-rsssl', $html);
  } else {
    //verifciation of not doing anything
    $html = str_replace('data-rsssl', 'data-rssslsocial=0 data-rsssl', $html);
  }

  return $html;
}


}//class closure
