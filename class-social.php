<?php
defined('ABSPATH') or die("you do not have acces to this page!");

class rsssl_soc_social
{
    private static $_this;

    function __construct()
    {
        if (isset(self::$_this))
            wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'really-simple-ssl-soc'), get_class($this)));

        self::$_this = $this;

        add_filter("rsssl_fixer_output", array($this, "fix_social"));
        add_filter('update_easy_social_share_url', array($this, 'fix_easy_social_share'));

        /*
            Jetpack share recovery
        */

        add_filter('sharing_permalink', array($this, 'jetpack_sharing_permalink'), 10, 3);
        add_filter('jetpack_sharing_display_link', array($this, 'jetpack_sharing_display_link'), 10, 4);

        add_filter("rsssl_htaccess_output", array($this, "maybe_edit_htaccess"), 100, 1);
        add_filter("rsssl_wp_redirect_url", array($this, "maybe_no_ssl_redirection"), 100, 1);

        add_filter('ssba_url_current_page', array($this, 'simple_share_buttons_adder'), 10, 1);

        add_filter('apss_share_url', array($this, 'accesspress_sharing_permalink'), 10, 1);

        add_action('wp_head', array($this, 'rsssl_recover_addthis'), 10, 1);
    }

    static function this()
    {
        return self::$_this;
    }

    /*
     *      fixes add this recovery
     *
     *
     * */

    public function rsssl_recover_addthis()
    {
        if ($this->use_http()) {
            ?>
            <script type="text/javascript">
                var rsssl_share_url = window.location.href.replace('https://', 'http://');
                var addthis_share = {url: '' + rsssl_share_url + ''};
            </script>
            <?php
        }
    }

    public function maybe_edit_htaccess($rules)
    {
        $fb_rule = "RewriteCond %{HTTP_USER_AGENT} !facebookexternalhit/[0-9]|Facebot" . "\n";
        if (strlen($rules) > 0) {
            $rsssl_rewrite_rule = "RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]" . "\n";
            if (strpos($rules, $rsssl_rewrite_rule) !== false) {
                $rules = str_replace($rsssl_rewrite_rule, $fb_rule . $rsssl_rewrite_rule, $rules);
            }
        }

        return $rules;
    }

    public function maybe_no_ssl_redirection($url)
    {

        if (strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false) {
            $url = str_replace("https://", "http://", $url);
        }

        return $url;
    }


    /*
        filter url for simple share buttons adder
        https://nl.wordpress.org/plugins/simple-share-buttons-adder/
    */


    public function simple_share_buttons_adder($url)
    {
        if ($this->use_http()) {
            $url = str_replace("https://", "http://", $url);
        }
        return $url;
    }


    /**
     *       Filter Jetpack Facebook sharing link, for non FB services
     *       https://github.com/Automattic/jetpack/blob/98c78e2cdd7ad6bb4461cb0c67417bdea5311d2e/modules/sharedaddy/sharing-sources.php#L82
     *
     */

    public function jetpack_sharing_display_link($url, $obj, $post_id, $args)
    {

        if ($this->use_http($post_id)) {
            $url = str_replace("https://", "http://", $url);
        }

        return $url;

    }

    /**
     *       Filter Jetpack Facebook sharing link
     *       For the native FB like button, we use this function to pass to the button:
     *       https://github.com/Automattic/jetpack/blob/98c78e2cdd7ad6bb4461cb0c67417bdea5311d2e/modules/sharedaddy/sharing-sources.php#L45
     */

    public function jetpack_sharing_permalink($url, $post_id, $sharing_id)
    {

        if ($this->use_http($post_id)) {
            $url = str_replace("https://", "http://", $url);
        }

        return $url;

    }


    /**
     *       AccessPress Facebook sharing link
     *       For the native FB like button, we use this function to pass to the button:
     */

    public function accesspress_sharing_permalink($url)
    {
        if ($this->use_http()) {
            $url = str_replace("https://", "http://", $url);
        }

        return $url;

    }


    /*
        Fix for easy social share buttons WordPress
    */
    public function fix_easy_social_share($url)
    {

        <<<<
        <<< HEAD
  if ($this->use_http()) {
    $url = str_replace("https://", "http://", $url);
  }
  return $url;
}

public function use_http($post_id=false){
  $use_http = TRUE;
  $have_post = false;

  if (is_front_page()){
    if (get_option('rsssl_soc_replace_to_http_on_home') ){
      return true;
    }else{
      return false;
=======
        if ($this->use_http()) {
            $url = str_replace("https://", "http://", $url);
        }
        return $url;
>>>>>>> master
    }

    public function use_http($post_id = false)
    {
        $use_http = TRUE;
        $have_post = false;

        if (is_front_page()) {
            if (get_option('rsssl_soc_replace_to_http_on_home')) {
                return true;
            } else {
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

            if ($start_date && ($publish_date > $start_date)) {
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

//add_filter("rsssl_fixer_output", "rsssl_fix_encoding", 100, 1);
//function rsssl_fix_encoding($html){
//    $http_url = str_replace("https://", "http://", home_url());
//    $preg_url_http = str_replace(array("/", "."),array("\/", "\."), $http_url);
//    $pattern = '/(data-url|data-urlalt)\s*=\s*(\'|")\K('.$preg_url_http.')/i';
//    $html = preg_replace($pattern, $http_url, $html, -1, $count);
//    return $html;
//}


    public function fix_social($html)
    {
        if ($this->use_http()) {

            $http_url = str_replace("https://", "http://", home_url());
            $https_url = home_url();

            $preg_url_https = str_replace(array("/", "."), array("\/", "\."), $https_url);
            $preg_url_http = str_replace(array("/", "."), array("\/", "\."), $http_url);

            $http_url_encoded = urlencode($http_url);
            $https_url_encoded = urlencode($https_url);

            /*<meta og:url */
            $html = str_replace('<meta property="og:url" content="' . $https_url, '<meta property="og:url" content="' . $http_url, $html);

            //generic:
            $pattern = '/(data-url|data-urlalt)\s*=\s*(\'|")\K(' . $preg_url_https . ')/i';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);

            //sharif
            $pattern = '/(data-url|data-urlalt)\s*=\s*(\'|")\K(' . $https_url_encoded . ')/i';
            $html = preg_replace($pattern, $http_url_encoded, $html, -1, $count);

            /*shareaholic*/
            $pattern = '/shareaholic-canvas.*?data-link=[\'"]\K(' . $preg_url_https . ')/';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);
            $html = str_replace("name='shareaholic:url' content='" . $https_url, "name='shareaholic:url' content='" . $http_url, $html);
            $html = str_replace('share_counts_url":"https:', 'share_counts_url":"http:', $html);

            /*default facebook like button */
            $html = str_replace('data-href="' . $https_url, 'data-href="' . $http_url, $html);
            $html = str_replace('<fb:like href="' . $https_url, '<fb:like href="' . $http_url, $html);

            $pattern = '/facebook\.com\/plugins\/like\.php\?.*?href=\K(' . $https_url_encoded . ')/';
            $html = preg_replace($pattern, $http_url_encoded, $html, -1, $count);

            $pattern = '/facebook\.com\/plugins\/like\.php\?.*?href=\K(' . $preg_url_https . ')/';
            $html = preg_replace($pattern, $preg_url_http, $html, -1, $count);

            $pattern = '/fb-iframe-plugin-query=?.*?href=\K(' . $https_url_encoded . ')/';
            $html = preg_replace($pattern, $http_url_encoded, $html, -1, $count);

            $pattern = '/fb-iframe-plugin-query=?.*?href=\K(' . $preg_url_https . ')/';
            $html = preg_replace($pattern, $preg_url_http, $html, -1, $count);

            $pattern = '/fb-like.*?data-href=[\'"]\K(' . $preg_url_https . ')/';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);

            /*Add to any */
            $html = str_replace('add_to/facebook?linkurl=' . $https_url_encoded, 'add_to/facebook?linkurl=' . $http_url_encoded, $html);
            $html = str_replace('add_to/twitter?linkurl=' . $https_url_encoded, 'add_to/twitter?linkurl=' . $http_url_encoded, $html);
            $html = str_replace('add_to/pinterest?linkurl=' . $https_url_encoded, 'add_to/pinterest?linkurl=' . $http_url_encoded, $html);
            $html = str_replace('add_to/google_plus?linkurl=' . $https_url_encoded, 'add_to/google_plus?linkurl=' . $http_url_encoded, $html);
            $html = str_replace('data-a2a-url="' . $https_url, 'data-a2a-url="' . $http_url, $html);
            $html = str_replace('addtoany.com/share#url=' . $https_url_encoded, 'addtoany.com/share#url=' . $http_url_encoded, $html);
            $html = str_replace('addtoany_special_service" data-url="' . $https_url, 'addtoany_special_service" data-url="' . $http_url, $html);

            /*Pinterest "Pin It" Button Pro*/
            $pattern = '/data-pin-url=[\'"]\K(' . $preg_url_https . ')/i';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);

            /*Add this */
            $html = str_replace('addthis:url="https://', 'addthis:url="http://', $html);
            $html = str_replace("addthis:url='https://", "addthis:url='http://", $html);
            $pattern = '/addthis_sharing_toolbox.*?data-url=[\'"]\K(' . $preg_url_https . ')/i';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);
            $html = str_replace('graph.facebook.com/?id=' . $https_url_encoded, 'graph.facebook.com/?id=' . $http_url_encoded, $html);

            /*Jetpack */
            $pattern = '/fb-share-button.*?data-href=[\'"]\K(' . $preg_url_https . ')/i';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);

            /*sharedaddy*/
            $pattern = '/data-shared.*?href=[\'"]\K(' . $preg_url_https . ')/i';
            $html = preg_replace($pattern, $http_url, $html, -1, $count);

            //Easy Social Share Buttons 3
            $html = str_replace('data-essb-url="' . $https_url, 'data-essb-url="' . $http_url, $html);
            $html = str_replace('data-essb-twitter-url="' . $https_url, 'data-essb-twitter-url="' . $http_url, $html);

            /*Directly integrated in code */
            $html = str_replace('facebook.com/sharer.php?u=' . $https_url_encoded, 'facebook.com/sharer.php?u=' . $http_url_encoded, $html);
            $html = str_replace('facebook.com/sharer/sharer.php?u=' . $https_url_encoded, 'facebook.com/sharer/sharer.php?u=' . $http_url_encoded, $html);

            //not encoded
            $html = str_replace('facebook.com/sharer.php?u=' . $https_url, 'facebook.com/sharer.php?u=' . $http_url, $html);
            $html = str_replace('facebook.com/sharer/sharer.php?u=' . $https_url, 'facebook.com/sharer/sharer.php?u=' . $http_url, $html);

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
