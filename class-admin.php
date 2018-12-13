<?php
defined('ABSPATH') or die("you do not have access to this page!");

class rsssl_soc_admin
{
    private static $_this;

    function __construct()
    {
        if (isset(self::$_this))
            wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'really-simple-ssl-soc'), get_class($this)));

        self::$_this = $this;

        //Add social settings tab
        add_filter('rsssl_tabs', array($this, 'add_social_tab'), 10, 3);
        add_action('show_tab_social', array($this, 'add_social_page'));
        $plugin = rsssl_soc_plugin;
        add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        add_action('admin_init', array($this, 'init'), 15);

        register_activation_hook(rsssl_soc_plugin_file, array($this, 'install'));

        //Add_settings?
        add_action('admin_init', array($this, 'add_settings'), 40);

    }

    static function this()
    {
        return self::$_this;
    }


    /*set the date to an initial value of today. */

    public function install()
    {

        if (!get_option('rsssl_soc_start_date_ssl')) {
            update_option("rsssl_soc_startdate", date(get_option('date_format')));
        }

        //Set the default share cache time in hours
        update_option('rsssl_share_cache_time' , '24');

        $rsssl_buttons_on_post_types = array('post' => true, 'page' => true);
        update_option("rsssl_buttons_on_post_types", $rsssl_buttons_on_post_types);

        $http = false;
        $https = false;
        $httpwww = false;
        $httpswww = false;

        if (strpos(home_url(), "://www.") !== FALSE) {
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
        update_option("rsssl_retrieval_domains", $domains);


        $services = array(
            'facebook' => true,
            'linkedin' => true,
            'google' => true,
            'pinterest' => true,
        );

        update_option("rsssl_social_services", $services);
        //update_option('rsssl_insert_custom_buttons', false);

    }


    public function init()
    {

        if (!class_exists('rsssl_admin')) return;
        add_action('admin_init', array($this, 'add_settings'), 50);
        add_action('admin_init', array($this, 'listen_for_clear_share_cache'), 40);

    }


    public function options_validate($input)
    {
        $validated_input = sanitize_text_field($input);
        return $validated_input;
    }

    public function options_validate_boolean($input)
    {

        return $input ? true : false;
    }

    public function options_validate_boolean_array($input)
    {

        if (is_array($input)) {
            $input = array_map(array($this, 'options_validate_boolean'), $input);
        } else {
            $input = $input ? true : false;
        }
        return $input;
    }

    public function add_settings()
    {
        if (!class_exists("rsssl_admin")) return;

        add_settings_section('rlrsssl_settings', __("Settings", "really-simple-ssl"), array($this, 'section_text'), 'rlrsssl-social');

        add_settings_field('rsssl_button_type', __("Share button type", "really-simple-ssl-soc"), array($this, 'get_option_button_type'), 'rlrsssl-social', 'rlrsssl_settings');
        register_setting('rlrsssl_social_options', 'rsssl_button_type', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_soc_start_date_ssl', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_soc_replace_to_http_on_home', array($this, 'options_validate_boolean'));
        //register_setting('rlrsssl_social_options', 'rsssl_insert_custom_buttons', array($this, 'options_validate_boolean'));

        if (get_option('rsssl_button_type') === 'existing') {
            add_settings_field('id_start_date_social', __("SSL switch date", "really-simple-ssl-soc"), array($this, 'get_option_start_date_social'), 'rlrsssl-social', 'rlrsssl_settings');
            add_settings_field('id_replace_to_http_on_home', __("Recover shares on the homepage", "really-simple-ssl-soc"), array($this, 'get_option_replace_to_http_on_home'), 'rlrsssl-social', 'rlrsssl_settings');
        }

        //add_settings_field('rsssl_insert_custom_buttons', __("Use the built in share buttons", "really-simple-ssl-soc"), array($this, 'get_option_insert_custom_buttons'), 'rlrsssl-social', 'rlrsssl_settings');


        if ((get_option('rsssl_button_type') === 'builtin') || (get_option('rsssl_button_type') === 'native')) {
            register_setting('rlrsssl_social_options', 'rsssl_social_services', array($this, 'options_validate_boolean_array'));
            add_settings_field('rsssl_social_services', __("Social services you want to use", "really-simple-ssl-soc"), array($this, 'get_option_social_services'), 'rlrsssl-social', 'rlrsssl_settings');
            register_setting('rlrsssl_social_options', 'rsssl_buttons_on_post_types', array($this, 'options_validate_boolean_array'));
            add_settings_field('rsssl_buttons_on_post_types', __("Which posttypes to use the buttons on", "really-simple-ssl-soc"), array($this, 'get_option_buttons_on_post_types'), 'rlrsssl-social', 'rlrsssl_settings');
        }

        if ((get_option("rsssl_buttons_theme") == 'color') || (get_option("rsssl_buttons_theme") == 'color-new') || (get_option("rsssl_buttons_theme") == 'dark') || (get_option("rsssl_buttons_theme") == 'round') || (get_option('rsssl_button_type') === 'native')) {
            register_setting('rlrsssl_social_options', 'rsssl_button_position', array($this, 'options_validate'));
            add_settings_field('rsssl_button_position', __("Position of buttons", "really-simple-ssl-soc"), array($this, 'get_option_button_position'), 'rlrsssl-social', 'rlrsssl_settings');
        }

        if (get_option('rsssl_button_type') === 'builtin') {

            register_setting('rlrsssl_social_options', 'rsssl_buttons_theme', array($this, 'options_validate'));

            register_setting('rlrsssl_social_options', 'rsssl_soc_fb_access_token', array($this, 'options_validate'));

            register_setting('rlrsssl_social_options', 'rsssl_retrieval_domains', array($this, 'options_validate_boolean_array'));
            register_setting('rlrsssl_social_options', 'rsssl_fb_button_type', array($this, 'options_validate'));
            //register_setting('rlrsssl_social_options', 'rsssl_inline_or_left', array($this, 'options_validate'));
            register_setting('rlrsssl_social_options', 'rsssl_share_cache_time', array($this, 'options_validate'));
            register_setting('rlrsssl_social_options', 'rsssl_use_custom_css', array($this, 'options_validate_boolean'));
            register_setting('rlrsssl_social_options', 'rsssl_custom_css', array($this, 'options_validate'));

            add_settings_field('rsssl_buttons_theme', __("Share buttons theme", "really-simple-ssl-soc"), array($this, 'get_option_rsssl_buttons_theme'), 'rlrsssl-social', 'rlrsssl_settings');

            add_settings_field('rsssl_fb_button_type', __("Use shares or likes for Facebook button", "really-simple-ssl-soc"), array($this, 'get_option_fb_button_type'), 'rlrsssl-social', 'rlrsssl_settings');

            add_settings_field('rsssl_fb_access_token', __("Facebook app token", "really-simple-ssl-soc"), array($this, 'get_option_fb_access_token'), 'rlrsssl-social', 'rlrsssl_settings');
            add_settings_field('rsssl_retrieval_domains', __("Domains to retrieve shares", "really-simple-ssl-soc"), array($this, 'get_option_retrieval_domains'), 'rlrsssl-social', 'rlrsssl_settings');
            //add_settings_field('rsssl_inline_or_left', __("Show buttons inline or as left sidebar", "really-simple-ssl-soc"), array($this, 'get_option_rsssl_inline_or_left'), 'rlrsssl-social', 'rlrsssl_settings');

            add_settings_field('rsssl_use_custom_css', __("Use custom CSS", "really-simple-ssl-soc"), array($this, 'get_option_use_custom_css'), 'rlrsssl-social', 'rlrsssl_settings');
            add_settings_field('rsssl_custom_css', __("Custom CSS", "really-simple-ssl-soc"), array($this, 'get_option_rsssl_custom_css'), 'rlrsssl-social', 'rlrsssl_settings');


            add_settings_field('rsssl_share_cache_time', __("Share cache time in hours", "really-simple-ssl-soc"), array($this, 'get_option_share_cache_time'), 'rlrsssl-social', 'rlrsssl_settings');

            add_settings_field('id_clear_share_cache', __("Clear share cache", "really-simple-ssl"), array($this, 'get_option_clear_share_cache'), 'rlrsssl-social', 'rlrsssl_settings');
        }

    }

    public function add_social_page()
    {
        ?>
        <form action="options.php" method="post">
            <?php
            settings_fields('rlrsssl_social_options');
            do_settings_sections('rlrsssl-social');
            ?>

            <input class="button button-primary" name="Submit" type="submit"
                   value="<?php echo __("Save", "really-simple-ssl"); ?>"/>
        </form>

        <?php

    }

    public function enqueue_assets()
    {
        wp_enqueue_script('rsssl-soc-ace', rsssl_soc_url . "assets/ace/ace.js", array(), 1, false);



    }

    public function get_option_button_type()
    {
        $rsssl_button_type = get_option('rsssl_button_type');

            ?>
        <select name="rsssl_button_type">
            <option value="existing" <?php if ($rsssl_button_type == "existing") echo "selected" ?>><?php _e("Recover shares for existing buttons", "really-simple-ssl-soc"); ?>
            <option value="builtin" <?php if ($rsssl_button_type == "builtin") echo "selected" ?>> <?php _e("Built-in buttons", "really-simple-ssl-soc"); ?>
            <option value="native" <?php if ($rsssl_button_type == "native") echo "selected" ?>><?php _e("Native sharing buttons", "really-simple-ssl-soc"); ?>
        </select>
            <?php
        RSSSL()->rsssl_help->get_help_tip(__("The existing option recovers shares for your existing sharing plugin buttons. The built-in buttons use the Really Simple SSL Social button. Native option shows the native sharing widgets for each platform.", "really-simple-ssl-soc"));

    }

    public function get_option_rsssl_buttons_theme()
    {
        $theme = get_option('rsssl_buttons_theme');
        $options = array(
                'color' => __('Color', 'really-simple-ssl-social'),
                'color-new' => __('Color new', 'really-simple-ssl-social'),
                'dark' => __('Dark', 'really-simple-ssl-social'),
                'round' => __('Round', 'really-simple-ssl-social'),
                'sidebar-color' => __('Sidebar color', 'really-simple-ssl-social'),
                'sidebar-dark' => __('Sidebar dark', 'really-simple-ssl-social'),
        );
        ?>
        <select name="rsssl_buttons_theme">
            <?php foreach($options as $key => $name) {?>
                <option value=<?php echo $key?> <?php if ($theme == $key) echo "selected" ?>><?php echo $name ?>
            <?php }?>
        </select>
        <?php
        RSSSL()->rsssl_help->get_help_tip(__("Choose the share button theme. The 'Color' theme uses colorfull buttons in the social networks style, while the XXX.", "really-simple-ssl-soc"));
    }

    public function get_option_start_date_social()
    {

        $start_date_social = get_option('rsssl_soc_start_date_ssl');

        echo '<input id="rsssl_soc_start_date_ssl" name="rsssl_soc_start_date_ssl" size="40" type="date" value="' . $start_date_social . '" />';
        RSSSL()->rsssl_help->get_help_tip(__("Enter the date on which you switched over to https. You can use the date format you use in the general WordPress settings.", "really-simple-ssl-soc"));
    }

    public function get_option_buttons_on_post_types()
    {
        $rsssl_buttons_on_post_types = get_option('rsssl_buttons_on_post_types');

        $args = array(
            'public' => true,
        );
        $post_types = get_post_types($args);
        $post_types_query = array();

        foreach ($post_types as $post_type) {
            $checked = false;
            if (isset($rsssl_buttons_on_post_types[$post_type])) {
                $checked = checked(1, $rsssl_buttons_on_post_types[$post_type], false);
            }
            ?>
            <input name="rsssl_buttons_on_post_types[<?php echo $post_type ?>]" size="40" type="checkbox"
                   value="1" <?php echo $checked ?> /> <?php echo $post_type ?><br>
            <?php
        }
    }

    public function get_option_button_position()
    {
        $rsssl_button_position = get_option('rsssl_button_position');
        ?>
        <select name="rsssl_button_position">
            <option value="top" <?php if ($rsssl_button_position == "top") echo "selected" ?>>Top
            <option value="bottom" <?php if ($rsssl_button_position == "bottom") echo "selected" ?>>Bottom
            <option value="both" <?php if ($rsssl_button_position == "both") echo "selected" ?>>Both
        </select>
        <?php
        RSSSL()->rsssl_help->get_help_tip(__("Choose where you want to position the share button(s)", "really-simple-ssl-soc"));
    }

    public function get_option_replace_to_http_on_home()
    {
        $replace_to_http_on_home = get_option('rsssl_soc_replace_to_http_on_home');
        echo '<input id="rsssl_soc_replace_to_http_on_home" name="rsssl_soc_replace_to_http_on_home" size="40" type="checkbox" value="1"' . checked(1, $replace_to_http_on_home, false) . " />";
        RSSSL()->rsssl_help->get_help_tip(__("When you enable this, share buttons generated by Really Simple Social will be inserted, which will retrieve likes from both the http and the https domain.", "really-simple-ssl-soc"));
    }


    public function get_option_fb_access_token()
    {
        $fb_access_token = get_option('rsssl_soc_fb_access_token');
        echo '<input id="rsssl_soc_fb_access_token" name="rsssl_soc_fb_access_token" size="40" type="text" value="' . $fb_access_token . '" />';
        //RSSSL()->rsssl_help->get_help_tip(__("To prevent rate limiting you need to create an app in facebook, then copy the user token here: https://developers.facebook.com/tools/accesstoken/", "really-simple-ssl-soc"));
        echo '<p>' . __('To prevent rate limiting you need to create an app in facebook, then copy the app token which you can find here: https://developers.facebook.com/tools/accesstoken/', 'really-simple-ssl-soc') . "</p>";
    }

    public function get_option_share_cache_time()
    {
        $share_cache_time = get_option('rsssl_share_cache_time');
        echo '<input id="rsssl_share_cache_time" name="rsssl_share_cache_time" size="40" type="number" min="0" max="24" value="' . $share_cache_time . '" />';
        RSSSL()->rsssl_help->get_help_tip(__("Set to a value between 1 and 24. Caching the shares will minimize the number of share retrieval request made to the social networks. Not caching shares can result in too many request (rate limiting) and thus shares not showing. Share counts will automatically update after the amount of time specified", "really-simple-ssl-soc"));

    }

    public function get_option_clear_share_cache()
    {

        $token = wp_create_nonce('rsssl_clear_share_cache');
        $clear_share_cache_link = admin_url("options-general.php?page=rlrsssl_really_simple_ssl&tab=social&action=clear_share_cache&token=" . $token);
        ?>
        <a class="button rsssl-button-deactivate-keep-ssl" href="
             <?php echo $clear_share_cache_link ?>"><?php _e("Clear share cache", "really-simple-ssl-social") ?>
        </a>
        <?php
        RSSSL()->rsssl_help->get_help_tip(__("Clicking this button will clear the cache, forcing the shares to be retrieved on next pageload.", "really-simple-ssl-soc"));
    }


     public function listen_for_clear_share_cache()
    {
        //check nonce
        if (!isset($_GET['token']) || (!wp_verify_nonce($_GET['token'], 'rsssl_clear_share_cache'))) return;
        //check for action
        if (isset($_GET["action"]) && $_GET["action"] == 'clear_share_cache') {
            $this->clear_share_cache();
        }
    }

    public function clear_share_cache()
    {
        //Delete the transient directly, clear_cached_likes() in class native requires an URL.
        delete_transient('rsssl_facebook_shares');
        delete_transient('rsssl_twitter_shares');
        delete_transient('rsssl_google_shares');
        delete_transient('rsssl_linkedin_shares');
        delete_transient('rsssl_pinterest_shares');
        delete_transient('rsssl_yummly_shares');
    }


    public function add_social_tab($tabs)
    {
        $tabs['social'] = __("Social", "really-simple-ssl-pro");
        return $tabs;
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

    public function get_option_social_services()
    {
        $services = get_option('rsssl_social_services');
        $facebook = isset($services['facebook']) ? $services['facebook'] : false;
        $linkedin = isset($services['linkedin']) ? $services['linkedin'] : false;
        $twitter = isset($services['twitter']) ? $services['twitter'] : false;
        $google = isset($services['google']) ? $services['google'] : false;
        $pinterest = isset($services['pinterest']) ? $services['pinterest'] : false;
        $whatsapp = isset($services['whatsapp']) ? $services['whatsapp'] : false;
        $yummly = isset($services['yummly']) ? $services['yummly'] : false;

        ?>
        <input type="checkbox" name="rsssl_social_services[facebook]"
               value="1" <?php checked($facebook, "1"); ?>/><?php _e("Facebook share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[linkedin]"
               value="1" <?php checked($linkedin, "1"); ?>/><?php _e("Linkedin share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[twitter]"
               value="1" <?php checked($twitter, "1"); ?>/><?php _e("Twitter  share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[google]"
               value="1" <?php checked($google, "1"); ?>/><?php _e("Google share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[pinterest]"
               value="1" <?php checked($pinterest, "1"); ?>/><?php _e("Pinterest share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[whatsapp]"
               value="1" <?php checked($whatsapp, "1"); ?>/><?php _e("Whatsapp share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[yummly]"
               value="1" <?php checked($yummly, "1"); ?>/><?php _e("Yummly share button", "really-simple-ssl-soc") ?>
        <br>

        <?php
        RSSSL()->rsssl_help->get_help_tip(__("Choose which social services you want to include sharing buttons.", "really-simple-ssl-soc"));
    }


    public function get_option_fb_button_type() {
      $rsssl_fb_button_type = get_option('rsssl_fb_button_type');
      ?>
      <select name="rsssl_fb_button_type">
        <option value="shares" <?php if ($rsssl_fb_button_type=="shares") echo "selected"?>>Shares
        <option value="likes" <?php if ($rsssl_fb_button_type=="likes") echo "selected"?>>Likes
      </select>
      <?php
      RSSSL()->rsssl_help->get_help_tip(__("Choose if you want to use the share or the like functionality of Facebook", "really-simple-ssl-soc"));
    }

    public function get_option_use_custom_css()
    {
        $rsssl_use_custom_css = get_option('rsssl_use_custom_css');
        ?>
        <script>
            jQuery(document).ready(function ($) {
                function rsssl_check_custom_css() {
                    if ($("#rsssl_use_custom_css").is(":checked")) {
                        console.log("Checked");
                        $('#rsssl_custom_csseditor').closest('tr').show();
                    } else {
                        $('#rsssl_custom_csseditor').closest('tr').hide();
                    }
                }

                rsssl_check_custom_css();
                $(document).on("click", "#rsssl_use_custom_css", function () {
                    rsssl_check_custom_css();
                })
            });
        </script>
        <?php
        echo '<input id="rsssl_use_custom_css" name="rsssl_use_custom_css" size="40" type="checkbox" value="1"' . checked(1, $rsssl_use_custom_css, false) . " />";
        RSSSL()->rsssl_help->get_help_tip(__("Insert any custom CSS for the sharing buttons here", "really-simple-ssl-soc"));
    }

    public function get_option_rsssl_custom_css()
    {
            ?>
            <div id="rsssl_custom_csseditor"
                              style="height: 200px; width: 100%"><?php echo get_option('rsssl_custom_css') ?></div>
            <script>
                var rsssl_custom_css =
                ace.edit("rsssl_custom_csseditor");
                rsssl_custom_css.setTheme("ace/theme/monokai");
                rsssl_custom_css.session.setMode("ace/mode/css");
                jQuery(document).ready(function ($) {
                    var textarea = $('textarea[name="rsssl_custom_css"]');
                    rsssl_custom_css.
                    getSession().on("change", function () {
                        textarea.val(rsssl_custom_css.getSession().getValue()
                    )
                    });
                });
            </script>
            <textarea style="display:none" name="rsssl_custom_css"><?php echo get_option('rsssl_custom_css') ?></textarea>
            <?php
    }

    public function plugin_settings_link($links){

        $settings_link = '<a href="' . admin_url("options-general.php?page=rlrsssl_really_simple_ssl&tab=social") . '">' . __("Settings", "really-simple-ssl") . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }


    /**
     * Insert some explanation above the form
     *
     * @since  2.0
     *
     * @access public
     *
     */

    public function section_text()
    {
        ?>
        <p><?php _e('Settings for Really Simple SSL Social', 'really-simple-ssl-soc'); ?></p>
        <?php
    }

}//class closure
