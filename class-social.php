<?php
defined('ABSPATH') or die("you do not have acces to this page!");
class rsssl_soc_social {
  private static $_this;

function __construct() {
  if ( isset( self::$_this ) )
      wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-soc' ), get_class( $this ) ) );

  self::$_this = $this;

  add_filter("rsssl_fixer_output",array($this, "fix_social"));
  add_filter('update_easy_social_share_url', array($this, 'fix_easy_social_share'));
}

static function this() {
  return self::$_this;
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

public function use_http(){
  $use_http = TRUE;
  //if we have a post currently selected, use the date to decide on http/https.
  global $post;
  if ($post) {
    $start_date = strtotime(get_option("rsssl_soc_start_date_ssl"));
    $publish_date = get_post_time('U', false, $post->ID);
    //$publish_date = strtotime(get_the_date(get_option('date_format'), $post->ID));
    // error_log("start date ".get_option("rsssl_soc_start_date_ssl"));
    // error_log("start date unix time ".$start_date);
    // error_log("publish date unix time".$publish_date);
    //when publish date is after migration to ssl date, use the https url.
    if ($start_date && ($publish_date > $start_date)){
      $use_http = FALSE;
    }
  }

  return $use_http;
}

public function fix_social($html) {
  if ($this->use_http()) {

    $preg_url = str_replace(array("/", "."),array("\/", "\."),home_url());

    $http_url = str_replace("https://", "http://", home_url());
    $https_url = home_url();
    $http_url_encoded = urlencode($http_url);
    $https_url_encoded = urlencode($https_url);
    $preg_url_encoded = str_replace(array("/", ".") , array("\/", "\."),$https_url_encoded);

    /*<meta og:url */
    $replace_ogurl = get_option('rsssl_soc_replace_ogurl');
    if ($replace_ogurl) {
      $html = str_replace('<meta property="og:url" content="'.$https_url, '<meta property="og:url" content="'.$http_url, $html);
      $html = str_replace('rel="canonical" href="'.$https_url, 'rel="canonical" href="'.$http_url, $html);
    }

    /*default facebook like button */
    $html = str_replace('data-href="'.$https_url, 'data-href="'.$http_url, $html);
    $html = str_replace('<fb:like href="'.$https_url, '<fb:like href="'.$http_url, $html);

    $pattern = '/fb-like.*?data-href=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, str_replace("https://", "http://", home_url()), $html, -1, $count);

    /*Add to any */
    $html = str_replace('add_to/facebook?linkurl='.$https_url_encoded, 'add_to/facebook?linkurl='.$http_url_encoded, $html);
    $html = str_replace('data-a2a-url="'.$https_url, 'data-a2a-url="'.$http_url, $html);
    $html = str_replace('addtoany_share_save" href="https://www.addtoany.com/share#url='.$https_url_encoded, 'addtoany_share_save" href="https://www.addtoany.com/share#url='.$http_url_encoded, $html);
    $html = str_replace('addtoany_special_service" data-url="'.$https_url, 'addtoany_special_service" data-url="'.$http_url, $html);

    /* Digg Digg */
    $html = str_replace('facebook.com/plugins/like.php?href='.$https_url_encoded, 'facebook.com/plugins/like.php?href='.$http_url_encoded, $html);
    $html = str_replace('twitter-share-button" data-url="'.$https_url, 'twitter-share-button" data-url="'.$http_url, $html);

    /*Add this */
    $html = str_replace('addthis:url="https://', 'addthis:url="http://', $html);
    $pattern = '/addthis_sharing_toolbox.*?data-url=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, str_replace("https://", "http://", home_url()), $html, -1, $count);
    $html = str_replace('graph.facebook.com/?id=' . $https_url_encoded, 'graph.facebook.com/?id=' . $http_url_encoded, $html);

    /*Jetpack */
    $pattern = '/fb-share-button.*?data-href=[\'"]\K('.$preg_url.')/i';
    $html = preg_replace($pattern, str_replace("https://", "http://", home_url()), $html, -1, $count);

    //Easy Social Share Buttons 3
    $html = str_replace('data-essb-url="'.$https_url ,'data-essb-url="'.$http_url , $html);
    $html = str_replace('data-essb-twitter-url="'.$https_url ,'data-essb-twitter-url="'.$http_url , $html);

    /*Directly integrated in code */
    $html = str_replace('href="http://www.facebook.com/sharer.php?u=' . $https_url_encoded ,'href="http://www.facebook.com/sharer.php?u=' . $http_url_encoded , $html);
    $html = str_replace('www.facebook.com/plugins/like.php?href=' . $https_url , 'www.facebook.com/plugins/like.php?href='. $http_url , $html);

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
