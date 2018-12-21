<?php
defined('ABSPATH') or die("you do not have access to this page!");

class rsssl_soc_native
{
    private static $_this;
    public $facebook;
    public $linkedin;
    public $twitter;
    public $google;
    public $pinterest;
    public $yummly;
    public $debug = false;

    function __construct()
    {
        if (isset(self::$_this))
            wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'really-simple-ssl-soc'), get_class($this)));

        self::$_this = $this;
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 100);
        add_action('wp_ajax_nopriv_rsssl_get_likes', array($this, 'get_likes_ajax'));
        add_action('wp_ajax_rsssl_get_likes', array($this, 'get_likes_ajax'));
        add_action('wp_ajax_rsssl_clear_likes', array($this, 'ajax_clear_likes'));
        add_action('wp_ajax_nopriv_rsssl_clear_likes', array($this, 'ajax_clear_likes'));
        add_action('wp_footer', array($this, 'native_buttons_scripts'));

        //this hook can be used to clear the likes from another plugin.
        //do_action('rsssl_soc_clear_likes', $post_id);
        add_action('rsssl_soc_clear_likes', array($this, 'clear_likes'), 10, 1);

        add_filter('the_content', array($this, 'like_buttons_content_filter'));
        add_shortcode('rsssl_share_buttons', array($this, 'insert_shortcode_buttons'));
        add_shortcode('rsssl_share_count', array($this, 'get_raw_counts'));

        add_filter('script_loader_tag', array($this, 'filter_pinterest_script'), 10, 3);
        add_action('plugins_loaded', array($this, 'initialize'));

    }

    static function this()
    {
        return self::$_this;
    }


    public function get_raw_counts($atts = [], $content = null, $tag = '')
    {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);
        ob_start();

        $types = array('facebook', 'linkedin', 'twitter', 'google', 'pinterest', 'yummly');
        global $post;
        $post_id = false;
        if ($post) $post_id = $post->ID;

        // override default attributes with user attributes
        $atts = shortcode_atts(['type' => 'facebook', 'post_id' => $post_id], $atts, $tag);
        $type = $atts['type'];
        if (in_array($type, $types) && $post_id) {
            $shares = $this->get_cached_likes_total($type, $post_id);
            echo $shares;
        }

        return ob_get_clean();
    }


    public function initialize()
    {
        $services = get_option('rsssl_social_services');
        $this->facebook = (isset($services['facebook']) && $services['facebook']) ? true : false;
        $this->linkedin = (isset($services['linkedin']) && $services['linkedin']) ? true : false;
        $this->twitter = (isset($services['twitter']) && $services['twitter']) ? true : false;
        $this->google = (isset($services['google']) && $services['google']) ? true : false;
        $this->pinterest = (isset($services['pinterest']) && $services['pinterest']) ? true : false;
        $this->whatsapp = (isset($services['whatsapp']) && $services['whatsapp']) ? true : false;
        $this->yummly = (isset($services['yummly']) && $services['yummly']) ? true : false;

    }


    /**
     *
     * if the type is passed, only retrieve shares for this type
     *
     * @param int $post_id
     * @param bool $type
     * @return array shares
     */


    public function get_likes($post_id, $type = false)
    {
        $facebook = $type ? $type === 'facebook' : $this->facebook;
        $twitter = $type ? $type === 'twitter' : $this->twitter;
        $google = $type ? $type === 'google' : $this->google;
        $linkedin = $type ? $type === 'linkedin' : $this->linkedin;
        $pinterest = $type ? $type === 'pinterest' : $this->pinterest;
        $yummly = $type ? $type === 'yummly' : $this->yummly;

        if ($post_id == 0) {
            $url = home_url();
        } else {
            $url = get_permalink($post_id);
        }

        if ($this->debug) $url = "https://www.sharethis.com";

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
        if ($facebook) {
            if ($get_http) $fb_likes = $this->retrieve_fb_likes($url_http);
            if ($get_https) $fb_likes += $this->retrieve_fb_likes($url_https);
            if ($get_httpwww) $fb_likes += $this->retrieve_fb_likes($url_httpwww);
            if ($get_httpswww) $fb_likes += $this->retrieve_fb_likes($url_httpswww);
        }

        $twitter_likes = 0;
        if ($twitter) {
            if ($get_http) $twitter_likes = $this->retrieve_twitter_likes($url_http);
            if ($get_https) $twitter_likes += $this->retrieve_twitter_likes($url_https);
            if ($get_httpwww) $twitter_likes += $this->retrieve_twitter_likes($url_httpwww);
            if ($get_httpswww) $twitter_likes += $this->retrieve_twitter_likes($url_httpswww);
        }

        $google_likes = 0;
        //google seems to return the correct likes anyway.
        if ($google) {
            //$google_likes = $this->retrieve_google_likes($url_https);
            if ($get_http) $google_likes = $this->retrieve_google_likes($url_http);
            if ($get_https) $google_likes += $this->retrieve_google_likes($url_https);
            if ($get_httpwww) $google_likes += $this->retrieve_google_likes($url_httpwww);
            if ($get_httpswww) $google_likes += $this->retrieve_google_likes($url_httpswww);
        }

        $linkedin_likes = 0;
        if ($linkedin) {
            //only retrieve one domain, do not aggregate.
            if ($get_https) {
                $linkedin_likes = $this->retrieve_linkedin_likes($url_https);
            } else {
                $linkedin_likes = $this->retrieve_linkedin_likes($url_httpswww);
            }
        }

        $pinterest_likes = 0;

        if ($pinterest) {
            if ($get_http) $pinterest_likes = $this->retrieve_pinterest_likes($url_http);
            if ($get_https) $pinterest_likes += $this->retrieve_pinterest_likes($url_https);
            if ($get_httpwww) $pinterest_likes += $this->retrieve_pinterest_likes($url_httpwww);
            if ($get_httpswww) $pinterest_likes += $this->retrieve_pinterest_likes($url_httpswww);
        }

        $yummly_likes = 0;

        if ($yummly) {
            if ($get_http) $yummly_likes = $this->retrieve_yummly_likes($url_http);
            if ($get_https) $yummly_likes += $this->retrieve_yummly_likes($url_https);
            if ($get_httpwww) $yummly_likes += $this->retrieve_yummly_likes($url_httpwww);
            if ($get_httpswww) $yummly_likes += $this->retrieve_yummly_likes($url_httpswww);
        }

        $shares = array(
            'facebook' => $this->convert_nr($fb_likes),
            'twitter' => $this->convert_nr($twitter_likes),
            'gplus' => $this->convert_nr($google_likes),
            'linkedin' => $this->convert_nr($linkedin_likes),
            'pinterest' => $this->convert_nr($pinterest_likes),
            'yummly' => $this->convert_nr($yummly_likes),
        );

        error_log("Shares na convert nr");
        error_log(print_r($shares, true));

        return $shares;
    }


    public function get_likes_ajax($post_id = false)
    {
        if (!isset($_GET['post_id'])) return;
        $post_id = intval($_GET['post_id']);
        $out = $this->get_likes($post_id);

        die(json_encode($out));

    }


    public function retrieve_shares_total($service, $url){

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

        $shares = 0;
        if ($service==='facebook') {
            if ($get_http) $shares = $this->retrieve_fb_likes($url_http);
            if ($get_https) $shares += $this->retrieve_fb_likes($url_https);
            if ($get_httpwww) $shares += $this->retrieve_fb_likes($url_httpwww);
            if ($get_httpswww) $shares += $this->retrieve_fb_likes($url_httpswww);
        }

        if ($service==='twitter') {
            if ($get_http) $shares = $this->retrieve_twitter_likes($url_http);
            if ($get_https) $shares += $this->retrieve_twitter_likes($url_https);
            if ($get_httpwww) $shares += $this->retrieve_twitter_likes($url_httpwww);
            if ($get_httpswww) $shares += $this->retrieve_twitter_likes($url_httpswww);
        }

        //google seems to return the correct likes anyway.
        if ($service==='google') {
            //$google_likes = $this->retrieve_google_likes($url_https);
            if ($get_http) $shares = $this->retrieve_google_likes($url_http);
            if ($get_https) $shares += $this->retrieve_google_likes($url_https);
            if ($get_httpwww) $shares += $this->retrieve_google_likes($url_httpwww);
            if ($get_httpswww) $shares += $this->retrieve_google_likes($url_httpswww);
        }

        if ($service==='linkedin') {
            //only retrieve one domain, do not aggregate.
            if ($get_https) {
                $shares = $this->retrieve_linkedin_likes($url_https);
            } else {
                $shares = $this->retrieve_linkedin_likes($url_httpswww);
            }
        }

        if ($service==='pinterest') {
            if ($get_http) $shares = $this->retrieve_pinterest_likes($url_http);
            if ($get_https) $shares += $this->retrieve_pinterest_likes($url_https);
            if ($get_httpwww) $shares += $this->retrieve_pinterest_likes($url_httpwww);
            if ($get_httpswww) $shares += $this->retrieve_pinterest_likes($url_httpswww);
        }

        if ($service==='yummly') {
            if ($get_http) $shares = $this->retrieve_yummly_likes($url_http);
            if ($get_https) $shares += $this->retrieve_yummly_likes($url_https);
            if ($get_httpwww) $shares += $this->retrieve_yummly_likes($url_httpwww);
            if ($get_httpswww) $shares += $this->retrieve_yummly_likes($url_httpswww);
        }

        return $shares;
    }


    /*

      Clear the likes for a specific url

    */

    public function ajax_clear_likes()
    {
        if (!isset($_GET['post_id'])) {
            echo 'error';
            die();
        }

        $post_id = intval($_GET['post_id']);
        $this->clear_likes($post_id);
        die(json_encode('success'));
    }

    public function clear_likes($post_id)
    {

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
    }

    /*


    */

    public function get_cached_likes_total($type, $post_id)
    {

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

        //do not aggregate for linkedin
        if ($type === "linkedin") {
            $get_http = false;
            $get_httpwww = false;
            //only get one of the two:
            $get_httpswww = !$get_https;
        }

        //get likes for both http and https
        $likes = 0;
        if ($get_http) $likes = $this->get_cached_likes($type, $url_http, $post_id);
        if ($get_https) $likes += $this->get_cached_likes($type, $url_https, $post_id);
        if ($get_httpwww) $likes += $this->get_cached_likes($type, $url_httpwww, $post_id);
        if ($get_httpswww) $likes += $this->get_cached_likes($type, $url_httpswww, $post_id);

        if ($likes == 0) $likes = "";

        error_log("Get cached likes");
        error_log(print_r($likes, true));

        return $likes;

    }

    private function get_cached_likes($type, $url, $post_id){
        if ($this->debug) $url = "https://www.sharethis.com";
        $share_cache = get_transient("rsssl_" . $type . "_shares");

        if (!$share_cache || !isset($share_cache[$url])) {
            $this->get_likes($post_id, $type);

            $share_cache = get_transient("rsssl_" . $type . "_shares");
        }

        if (!isset($share_cache[$url])) return 0;

        return $share_cache[$url];
    }


    private function clear_cached_likes($url)
    {
        //* 3600 to convert user input to hours
        $expiration = (get_option('rsssl_share_cache_time') * 3600);

        $share_cache = get_transient('rsssl_facebook_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_facebook_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        $share_cache = get_transient('rsssl_twitter_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_twitter_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        $share_cache = get_transient('rsssl_google_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_google_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        $share_cache = get_transient('rsssl_linkedin_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_linkedin_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        $share_cache = get_transient('rsssl_pinterest_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_pinterest_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        $share_cache = get_transient('rsssl_yummly_shares');
        if ($share_cache) unset($share_cache[$url]);
        set_transient('rsssl_yummly_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));
    }


    private function convert_nr($nr)
    {

        if ($nr >= 1000000) {
            return round($nr / 1000000, 1) . "m";
        }

        if ($nr >= 1000) {
            return round($nr / 1000, 1) . "k";
        }

        if ($nr == 0) {
            return '';
        }

        return $nr;

    }

    private function retrieve_fb_likes($url)
     {
         $expiration = (get_option('rsssl_share_cache_time') * 3600);
         $shares = 0;
         $share_cache = get_transient('rsssl_facebook_shares');
         if (!$share_cache) $share_cache = array();
         $fb_access_token = get_option('rsssl_soc_fb_access_token');
         $auth = "";
         if ($fb_access_token) $auth = '&access_token=' . $fb_access_token;
         $request = wp_remote_get('https://graph.facebook.com/v2.9/?fields=engagement&id=' . $url . $auth);
//         https://developers.facebook.com/tools/accesstoken/
         if ($request["response"]["code"] == 200) {
             error_log("response code === 200");
             $json = wp_remote_retrieve_body($request);
             $output = json_decode($json);
             $shares = $output->engagement->reaction_count + $output->engagement->comment_count + $output->engagement->share_count + $output->engagement->comment_plugin_count;
         }
         error_log("Share cache");
         error_log(print_r($share_cache, true));
         $share_cache[$url] = $shares;
         set_transient('rsssl_facebook_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

         return intval($shares);
     }

    private function retrieve_twitter_likes($url)
    {
        $share_cache = get_transient('rsssl_twitter_shares');
        if (!$share_cache) $share_cache = array();
        $expiration = (get_option('rsssl_share_cache_time') * 3600);
        $request = wp_remote_get('http://opensharecount.com/count.json?url=' . $url);
        $json = wp_remote_retrieve_body($request);
        $output = json_decode($json);
        $shares = 0;
        if (!empty($output)) {
            $shares = $output->count;
        }
        $share_cache[$url] = $shares;

        set_transient('rsssl_twitter_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));
        return intval($shares);
    }

    private function retrieve_google_likes($url)
    {
        $share_cache = get_transient('rsssl_google_shares');
        if (!$share_cache) $share_cache = array();
        $expiration = (get_option('rsssl_share_cache_time') * 3600);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode($url) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $curl_results = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($curl_results, true);

        $shares = isset($json[0]['result']['metadata']['globalCounts']['count']) ? intval($json[0]['result']['metadata']['globalCounts']['count']) : 0;

        $share_cache[$url] = $shares;
        set_transient('rsssl_google_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        return intval($shares);
    }

    private function retrieve_linkedin_likes($url)
    {
        $share_cache = get_transient('rsssl_linkedin_shares');
        if (!$share_cache) $share_cache = array();
        $expiration = (get_option('rsssl_share_cache_time') * 3600);
        $request = wp_remote_get('https://www.linkedin.com/countserv/count/share?url=' . urlencode($url) . '&format=json');
        $json = wp_remote_retrieve_body($request);
        $output = json_decode($json);
        $shares = $output->count;
        $share_cache[$url] = $shares;
        set_transient('rsssl_linkedin_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        return intval($shares);
    }


//https://vk.com/share.php?act=count&url=https://really-simple-ssl.com

    private function retrieve_pinterest_likes($url)
    {
        $shares = 0;
        $share_cache = get_transient('rsssl_pinterest_shares');
        if (!$share_cache) $share_cache = array();
        $expiration = (get_option('rsssl_share_cache_time') * 3600);
        $request = wp_remote_get('http://api.pinterest.com/v1/urls/count.json?&url=' . $url);

        $json = wp_remote_retrieve_body($request);

        $json = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $json);
        $output = json_decode($json);

        if (!empty($output)) {
            $shares = $output->count;
        }
        $share_cache[$url] = $shares;
        set_transient('rsssl_pinterest_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        return intval($shares);
    }

    private function retrieve_yummly_likes($url)
    {
        $shares = 0;
        $share_cache = get_transient('rsssl_yummly_shares');
        if (!$share_cache) $share_cache = array();
        $expiration = (get_option('rsssl_share_cache_time') * 3600);
        $request = wp_remote_get('http://www.yummly.com/services/yum-count?url=%s' . $url);
        $json = wp_remote_retrieve_body($request);

        $output = json_decode($json);

        if (!empty($output)) {
            $shares = $output->count;
        }
        $share_cache[$url] = $shares;
        set_transient('rsssl_yummly_shares', $share_cache, apply_filters("rsssl_social_cache_expiration", $expiration));

        return intval($shares);
    }

    /*

        Add like buttons to bottom of all posts and pages.

    */

    public function like_buttons_content_filter($content)
    {
        if ($this->show_buttons()) {
            // show the buttons
            // not on homepage, but do show them on blogs overview page (is_home)
            // always when a sidebar theme is used
            if ((is_home() || !is_front_page()) || (get_option('rsssl_buttons_theme') == "sidebar-color") || (get_option('rsssl_buttons_theme') === 'sidebar-dark')) {

                $html = $this->generate_like_buttons();

                $position = get_option('rsssl_button_position');

                //position depending on setting
                if ($position == 'bottom') {
                    $content = $content . $html;
                } elseif ($position == 'both') {
                    $content = $html . $content . $html;
                } else {
                    $content = $html . $content;
                }

            }
        }

        return $content;
    }


    public function show_buttons()
    {
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


    /**
     * Generate like buttons to be used in either shortcode or content filter
     *
     * @param int $post_id
     * @return string
     */

    public function generate_like_buttons($post_id = false)
    {
        global $wp_query;
        $url = home_url();
        $title = "";
        $type = get_option('rsssl_button_type') === 'native' ? 'native' : 'builtin';

        if ($post_id) {
            $post = get_post($post_id);
        } else {
            $post_id = 0;
            if ($wp_query) {
                $post = $wp_query->post;
                if ($post) $post_id = $post->ID;
            }
        }

        if ($post) {
            $url = get_permalink($post);
            $title = $post->post_title;
        }

        //load template from theme directory if available
        $file = rsssl_soc_path . "templates/$type-template-wrap.php";
        $theme_file = get_stylesheet_directory() . '/' . dirname(rsssl_soc_plugin) . 'template-wrap.php';

        if (file_exists($theme_file)) {
            $file = $theme_file;
        }

        ob_start();
        require $file;
        $wrapper = ob_get_clean();

        $button_html = "";
        $services = get_option('rsssl_social_services');
        foreach($services as $service => $checked){
            $button_html .= $this->get_button_html($service, $url, $post_id, $title, $type);
        }

        return str_replace(array('{buttons}', '{post_id}'), array($button_html, $post_id), $wrapper);
    }

    /**
     * Get the html for a specific service button
     * @param $service
     * @param $url
     * @param $post_id
     * @param $title
     * @return string
     */

    public function get_button_html($service, $url, $post_id, $title, $type="builtin"){

        $file = rsssl_soc_path . "templates/$type-$service.php";
        $theme_file = get_stylesheet_directory() . '/' . dirname(rsssl_soc_plugin) . "/$type-$service.php";
        $shares = $this->get_cached_likes_total($service, $post_id);
        if (file_exists($theme_file)) {
            $file = $theme_file;
        }

        ob_start();
        require $file;
        $html = ob_get_clean();
        $html = str_replace(array("{post_id}", "{url}", "{title}", '{shares}'), array($post_id, $url, $title, $shares), $html);

        //Str_replace the FB template to either share or like, depending on the configured setting. Adjust width and height accordingly.
        if (get_option('rsssl_fb_button_type') == 'shares') {
            $html = str_replace(array('{like_or_share}' , '{height}' , '{width}'), array("share" , "600" , "900"), $html);
        } else {
            $html = str_replace(array('{like_or_share}' , '{height}' , '{width}'), array("like" , "350" , "450"), $html);
        }
        //Only replace the label for the 'color-new' and 'dark' themes.
        if ((get_option('rsssl_buttons_theme') === 'color-new') || (get_option('rsssl_buttons_theme') === 'dark') || (get_option('rsssl_buttons_theme') === 'sidebar-color') || (get_option('rsssl_buttons_theme') === 'sidebar-dark')) {
            $html = str_replace("{label}" , '<span class="rsssl-label">'.__("Share","really-simple-ssl-soc").'</span>', $html);
        } else {
            $html = str_replace("{label}", "", $html);
        }
        //And insert a div for the new color theme
        if (get_option('rsssl_buttons_theme') === 'color-new') {
            $html = str_replace('{color_round}' , '<div class="rsssl-color-round"></div>' , $html);
        } else
            $html = str_replace('{color_round}' , "" , $html);

        return $html;
    }

    public function enqueue_scripts()
    {
        $version = (strpos(home_url(), "localhost") === false) ? time() : rsssl_soc_version;

        $theme = get_option('rsssl_buttons_theme');

        wp_enqueue_style('rsssl_social_buttons_style', plugin_dir_url(__FILE__) . "assets/css/$theme.min.css", array(), $version);

        if (get_option('rsssl_button_type') === 'native') {
            wp_register_style('rsssl_social_native_style', plugin_dir_url(__FILE__) . "assets/css/native.min.css", array(), $version);
            wp_enqueue_style('rsssl_social_native_style');
        }

        wp_enqueue_style('rsssl_social_fontello', plugin_dir_url(__FILE__) . 'assets/font/fontello-icons/css/fontello.css', array(), $version);

        //Add any custom CSS defined in the custom CSS settings section
        $custom_css = get_option('rsssl_custom_css');

        if ($custom_css) {
            $custom_css = $this->sanitize_custom_css($custom_css);
            if (!empty($custom_css)) {
                wp_add_inline_style('rsssl_social_buttons_style', $custom_css);
            }
        }

        wp_enqueue_script('rsssl_social', plugin_dir_url(__FILE__) . "assets/js/likes.js", array('jquery'), $version, true);

        $url = home_url();
        global $post;
        if ($post) {
            $url = get_permalink($post);
        }
        $use_cache = true;
        //check a transient as well, if the transient has expired, we will set set usecache to true, so it will retrieve the shares fresh.
        $share_cache = get_transient('rsssl_facebook_shares');

        if ($this->debug || (defined('rsssl_social_no_cache') && rsssl_social_no_cache) || !$share_cache || !isset($share_cache[$url])) {
           $use_cache = false;
        }

        if ($this->pinterest) {
            wp_enqueue_script('rsssl_pinterest', "//assets.pinterest.com/js/pinit.js", array(), "", true);
        }

        if ($this->twitter) {
            wp_enqueue_script('rsssl_twitter', "https://platform.twitter.com/widgets.js", array(), "", true);
        }

        if ($this->google) {
            wp_enqueue_script('rsssl_google', "https://apis.google.com/js/platform.js", array(), "", true);
        }

        wp_localize_script('rsssl_social', 'rsssl_soc_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'token' => wp_create_nonce('rsssl_social_nonce', 'token'),
            'use_cache' => $use_cache,
        ));

    }

    public function native_buttons_scripts() {
        echo "<div id=\"fb-root\"></div>
                  <script>
                      (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = \"https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0\";
                        fjs.parentNode.insertBefore(js, fjs);
                      }(document, 'script', 'facebook-jssdk'));
                  </script>";

        echo "<script src=\"//platform.linkedin.com/in.js\" type=\"text/javascript\"> lang: en_US</script>";
    }


    public function sanitize_custom_css($css)
    {
        $css = preg_replace('/\/\*(.|\s)*?\*\//i', '', $css);
        $css = trim($css);
        return $css;
    }


    /*
        add attributes to pinterest src
    */

    public function filter_pinterest_script($tag, $handle, $src)
    {
        if ($handle == 'rsssl_pinterest') {
            return str_replace('<script', '<script async defer data-pin-hover="true" ', $tag);
        }
        return $tag;
    }

    /*

      Generate the editor html on a page with the shortcode.

    */

    public function insert_shortcode_buttons($atts, $content = null, $tag = '')
    {
        ob_start();

        echo $this->generate_like_buttons();

        return ob_get_clean();
    }

}//class closure