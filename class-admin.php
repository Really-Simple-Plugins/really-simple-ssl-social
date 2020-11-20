<?php
defined('ABSPATH') or die("you do not have access to this page!");

class rsssl_soc_admin
{
    private static $_this;

    public $capability = 'activate_plugins';

    function __construct()
    {
        if (isset(self::$_this))
            wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'really-simple-ssl-soc'), get_class($this)));

        self::$_this = $this;

        //Add social settings tab
        $core_plugin = 'really-simple-ssl/rlrsssl-really-simple-ssl.php';
        if ( is_plugin_active($core_plugin)) {
            add_filter('rsssl_grid_tabs', array($this, 'add_social_tab'), 10, 3);
            add_action('show_tab_social', array($this, 'add_social_page'));
        } else {
            add_action('admin_menu', array($this, 'add_settings_page'), 40);
        }

        if (is_admin()) {
	        add_action('admin_print_footer_scripts', array($this, 'insert_dismiss_og_url_notice'));
        }

	    add_action('wp_ajax_dismiss_og_url_notice', array($this, 'dismiss_og_url_notice_callback'));

	    $plugin = rsssl_soc_plugin;
        add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));

	    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        add_action('admin_init', array($this, 'init'), 15);

        register_activation_hook(rsssl_soc_plugin_file, array($this, 'install'));

        //Add_settings?
        add_action('admin_init', array($this, 'add_settings'), 40);
	    add_action( 'admin_notices', array ($this, 'maybe_show_og_url_notice' ));

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
        if (!current_user_can('manage_options')) return;

        if (!class_exists('rsssl_admin') && (!class_exists('rsssl_soc_admin'))) return;
        add_action('admin_init', array($this, 'add_settings'), 50);
        add_action('admin_init', array($this, 'listen_for_clear_share_cache'), 40);
        add_action('admin_init', array($this, 'check_upgrade'), 30 );

    }

    public function options_validate($input)
    {
        if (!current_user_can('manage_options')) return '';

        $validated_input = sanitize_text_field($input);
        return $validated_input;
    }

    public function options_validate_boolean($input)
    {
        if (!current_user_can('manage_options')) return '';

        return $input ? true : false;
    }

    public function options_validate_boolean_array($input)
    {
        if (!current_user_can('manage_options')) return '';

        if (is_array($input)) {
            $input = array_map(array($this, 'options_validate_boolean'), $input);
        } else {
            $input = $input ? true : false;
        }
        return $input;
    }

    public function add_settings()
    {

        //Whether a setting is shown for a button type depends on the class it is given in the get_option function.
        if (!class_exists("rsssl_admin") && (!class_exists('rsssl_soc_admin'))) return;

        add_settings_section('rlrsssl_settings', __("Settings", "really-simple-ssl"), array($this, 'section_text'), 'rlrsssl-social');

        add_settings_field('rsssl_button_type', __("Share button type", "really-simple-ssl-soc"), array($this, 'get_option_button_type'), 'rlrsssl-social', 'rlrsssl_settings');

        register_setting('rlrsssl_social_options', 'rsssl_button_type', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_soc_start_date_ssl', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_soc_replace_to_http_on_home', array($this, 'options_validate_boolean'));

        add_settings_field('id_start_date_social', __("SSL switch date", "really-simple-ssl-soc"), array($this, 'get_option_start_date_social'), 'rlrsssl-social', 'rlrsssl_settings');
        add_settings_field('id_replace_to_http_on_home', __("Recover shares on the homepage", "really-simple-ssl-soc"), array($this, 'get_option_replace_to_http_on_home'), 'rlrsssl-social', 'rlrsssl_settings');


        register_setting('rlrsssl_social_options', 'rsssl_social_services', array($this, 'options_validate_boolean_array'));
        add_settings_field('rsssl_social_services', __("Social services you want to use", "really-simple-ssl-soc"), array($this, 'get_option_social_services'), 'rlrsssl-social', 'rlrsssl_settings');
        register_setting('rlrsssl_social_options', 'rsssl_buttons_on_post_types', array($this, 'options_validate_boolean_array'));
        add_settings_field('rsssl_buttons_on_post_types', __("Which posttypes to use the buttons on", "really-simple-ssl-soc"), array($this, 'get_option_buttons_on_post_types'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('rsssl_sitewide_or_block', __("Show buttons on each selected posttype (sitewide) or per Gutenberg block", "really-simple-ssl-soc"), array($this, 'get_option_sitewide_or_block'), 'rlrsssl-social', 'rlrsssl_settings');
        register_setting('rlrsssl_social_options', 'rsssl_sitewide_or_block', array($this, 'options_validate'));

        add_settings_field('rsssl_button_position', __("Position of buttons", "really-simple-ssl-soc"), array($this, 'get_option_button_position'), 'rlrsssl-social', 'rlrsssl_settings');
        register_setting('rlrsssl_social_options', 'rsssl_button_position', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_buttons_theme', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_soc_fb_access_token', array($this, 'options_validate'));

        register_setting('rlrsssl_social_options', 'rsssl_retrieval_domains', array($this, 'options_validate_boolean_array'));
        register_setting('rlrsssl_social_options', 'rsssl_fb_button_type', array($this, 'options_validate'));
        register_setting('rlrsssl_social_options', 'rsssl_share_cache_time', array($this, 'options_validate'));
        if (!defined("RSSSL_SOC_NO_ACE")) {
            register_setting('rlrsssl_social_options', 'rsssl_use_custom_css', array($this, 'options_validate_boolean'));
            register_setting('rlrsssl_social_options', 'rsssl_custom_css', array($this, 'options_validate'));
        }

        add_settings_field('rsssl_buttons_theme', __("Share buttons theme", "really-simple-ssl-soc"), array($this, 'get_option_rsssl_buttons_theme'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('rsssl_fb_button_type', __("Use shares or likes for Facebook button", "really-simple-ssl-soc"), array($this, 'get_option_fb_button_type'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('rsssl_fb_access_token', __("Facebook app token", "really-simple-ssl-soc"), array($this, 'get_option_fb_access_token'), 'rlrsssl-social', 'rlrsssl_settings');
        add_settings_field('rsssl_retrieval_domains', __("Domains to retrieve shares", "really-simple-ssl-soc"), array($this, 'get_option_retrieval_domains'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('rsssl_use_custom_css', __("Use custom CSS", "really-simple-ssl-soc"), array($this, 'get_option_use_custom_css'), 'rlrsssl-social', 'rlrsssl_settings');
        add_settings_field('rsssl_custom_css', __("Custom CSS", "really-simple-ssl-soc"), array($this, 'get_option_rsssl_custom_css'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('rsssl_share_cache_time', __("Share cache time in hours", "really-simple-ssl-soc"), array($this, 'get_option_share_cache_time'), 'rlrsssl-social', 'rlrsssl_settings');

        add_settings_field('id_clear_share_cache', __("Clear share cache", "really-simple-ssl"), array($this, 'get_option_clear_share_cache'), 'rlrsssl-social', 'rlrsssl_settings');


	    add_settings_field('add_og_url', __("Add og url to page source", "really-simple-ssl-soc"), array($this, 'get_option_add_og_url'), 'rlrsssl-social', 'rlrsssl_settings');
	    register_setting('rlrsssl_social_options', 'add_og_url', array($this, 'options_validate'));

    }

    /**
     * Create tabs on the settings page
     *
     * @since  2.1
     *
     * @access public
     *
     */

    public function rsssl_soc_admin_tabs($current = 'homepage')
    {
        $tabs = array(
            'configuration' => __("Configuration", "really-simple-ssl")
        );

        $tabs = apply_filters("rsssl_grid_tabs", $tabs);

        echo '<h2 class="nav-tab-wrapper">';

        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=rlrsssl_really_simple_ssl&tab=$tab'>$name</a>";
        }
        echo '</h2>';
    }

    public function add_social_page()
    {
        if (!current_user_can($this->capability)) return;

        //If free is not active, add a license tab
        if (!defined('rsssl_plugin')) {
            if (isset ($_GET['tab'])) $this->rsssl_soc_admin_tabs($_GET['tab']); else $this->rsssl_soc_admin_tabs('configuration');
            if (isset ($_GET['tab'])) $tab = $_GET['tab']; else $tab = 'configuration';

            switch ($tab) {
                case 'configuration' :
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
                    break;
                default:
                    echo '';
            }
            if (!defined('rsssl_plugin')) {
                do_action("show_tab_{$tab}");
            }
        } else {
            //If free is active we only have to populate the social page
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
    }

    /**
     * Adds the admin options page
     *
     * @since  2.0
     *
     * @access public
     *
     */

    public function add_settings_page()
    {
        if (!current_user_can($this->capability)) return;

        add_options_page(
            __("SSL settings", "really-simple-ssl"), //link title
            __("Really Simple SSL Social", "really-simple-ssl"), //page title
            $this->capability, //capability
            'rlrsssl_really_simple_ssl', //url
            array($this, 'add_social_page')); //function
    }

    public function enqueue_admin_assets($hook)
    {
        //script to check for ad blockers
        if (isset($_GET['page']) && $_GET['page']=='rlrsssl_really_simple_ssl') {

            if (!defined("RSSSL_SOC_NO_ACE")) {
                wp_enqueue_script('rsssl-soc-ace', rsssl_soc_url . "assets/ace/ace.js", array(), 1, false);
            }

            if (is_admin()) {
                wp_register_style('rlrsssl-soc-css', trailingslashit(rsssl_soc_url) . '/assets/css/rsssl-soc-admin.css', "", rsssl_soc_version);
                wp_enqueue_style('rlrsssl-soc-css');
                if ($this->is_settings_page()) {
                    wp_enqueue_script('rsssl-soc-admin', rsssl_soc_url . 'assets/js/admin.min.js', array(), rsssl_soc_version, false);
                }
            }
        }

    }

    public function is_settings_page()
    {
        return (isset($_GET['page']) && $_GET['page'] == 'rlrsssl_really_simple_ssl') ? true : false;
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
        rsssl_soc_help::get_help_tip(__("The existing option recovers shares for your existing sharing plugin buttons. The built-in buttons use the Really Simple SSL Social button. Native option shows the native sharing widgets for each platform.", "really-simple-ssl-soc"));

    }

    public function get_option_rsssl_buttons_theme()
    {
        $theme = get_option('rsssl_buttons_theme');
        $options = array(
            'color' => __('Color square', 'really-simple-ssl-social'),
            'color-new' => __('Color rounded', 'really-simple-ssl-social'),
            'dark' => __('Dark square', 'really-simple-ssl-social'),
            'round' => __('Dark round', 'really-simple-ssl-social'),
            'sidebar-color' => __('Sidebar color', 'really-simple-ssl-social'),
            'sidebar-dark' => __('Sidebar dark', 'really-simple-ssl-social'),
        );
        ?>
        <select name="rsssl_buttons_theme" class="builtin button_type">
            <?php foreach($options as $key => $name) {?>
            <option value=<?php echo $key?> <?php if ($theme == $key) echo "selected" ?>><?php echo $name ?>
                <?php }?>
        </select>
        <?php
        rsssl_soc_help::get_help_tip(__("Choose the share button theme.", "really-simple-ssl-soc"));
    }

    public function get_option_start_date_social()
    {

        $start_date_social = get_option('rsssl_soc_start_date_ssl');

        echo '<input id="rsssl_soc_start_date_ssl" class="existing button_type" name="rsssl_soc_start_date_ssl" size="40" type="date" value="' . $start_date_social . '" />';
        rsssl_soc_help::get_help_tip(__("Enter the date on which you switched over to https. You can use the date format you use in the general WordPress settings.", "really-simple-ssl-soc"));
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
            <input class="button_type native builtin button_type" name="rsssl_buttons_on_post_types[<?php echo $post_type ?>]" size="40" type="checkbox"
                   value="1" <?php echo $checked ?> /> <?php echo $post_type ?><br>
            <?php
        }
    }

    public function get_option_button_position()
    {
        $rsssl_button_position = get_option('rsssl_button_position');
        ?>
        <select name="rsssl_button_position" class="native builtin button_type">
            <option value="top" <?php if ($rsssl_button_position == "top") echo "selected" ?>>Top
            <option value="bottom" <?php if ($rsssl_button_position == "bottom") echo "selected" ?>>Bottom
            <option value="both" <?php if ($rsssl_button_position == "both") echo "selected" ?>>Both
        </select>
        <?php
        rsssl_soc_help::get_help_tip(__("Choose where you want to position the share button(s)", "really-simple-ssl-soc"));
    }

    public function get_option_replace_to_http_on_home()
    {
        $replace_to_http_on_home = get_option('rsssl_soc_replace_to_http_on_home');
        echo '<input id="rsssl_soc_replace_to_http_on_home" class="existing button_type" name="rsssl_soc_replace_to_http_on_home" size="40" type="checkbox" value="1"' . checked(1, $replace_to_http_on_home, false) . " />";
        rsssl_soc_help::get_help_tip(__("Recover shares on the homepage", "really-simple-ssl-soc"));
    }

    public function get_option_fb_access_token()
    {
        $fb_access_token = get_option('rsssl_soc_fb_access_token');
        echo '<input id="rsssl_soc_fb_access_token" class="native builtin button_type" name="rsssl_soc_fb_access_token" size="40" type="text" value="' . $fb_access_token . '" />';
        //RSSSL()->rsssl_help->get_help_tip(__("To prevent rate limiting you need to create an app in facebook, then copy the user token here: https://developers.facebook.com/tools/accesstoken/", "really-simple-ssl-soc"));
        echo '<p>' . __('To prevent rate limiting you need to create an app in facebook, then copy the app token which you can find here: https://developers.facebook.com/tools/accesstoken/', 'really-simple-ssl-soc') . "</p>";
    }

    public function get_option_share_cache_time()
    {
        $share_cache_time = get_option('rsssl_share_cache_time');
        echo '<input id="rsssl_share_cache_time" name="rsssl_share_cache_time" class="builtin native button_type" size="40" type="number" min="0" max="24" value="' . $share_cache_time . '" />';
        rsssl_soc_help::get_help_tip(__("Set to a value between 1 and 24. Caching the shares will minimize the number of share retrieval request made to the social networks. Not caching shares can result in too many request (rate limiting) and thus shares not showing. Share counts will automatically update after the amount of time specified", "really-simple-ssl-soc"));

    }

    public function get_option_clear_share_cache()
    {

        $token = wp_create_nonce('rsssl_clear_share_cache');
        $clear_share_cache_link = admin_url("options-general.php?page=rlrsssl_really_simple_ssl&tab=social&action=clear_share_cache&token=" . $token);
        ?>
        <a class="button rsssl-button-clear-share-cache native builtin button_type" href="
             <?php echo $clear_share_cache_link ?>"><?php _e("Clear share cache", "really-simple-ssl-social") ?>
        </a>
        <?php
        rsssl_soc_help::get_help_tip(__("Clicking this button will clear the cache, forcing the shares to be retrieved on next pageload.", "really-simple-ssl-soc"));
    }

    public function get_option_add_og_url()
    {
	    $add_og_url = get_option('add_og_url');
	    echo '<input id="add_og_url" name="add_og_url" class="existing button_type" size="40" type="checkbox" value="1"' . checked(1, $add_og_url, false) . " />";
	    rsssl_soc_help::get_help_tip(__("Not having an og :url can cause issues with Facebook. Enable this option if instructed to do so. ", "really-simple-ssl-soc"));
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
        $tabs['social'] = __("Social", "'really-simple-ssl-soc'");
        return $tabs;
    }

    public function get_option_retrieval_domains() {
        $domains = get_option('rsssl_retrieval_domains');
        $http = isset($domains['http']) ? $domains['http'] : false;
        $https = isset($domains['https']) ? $domains['https'] : false;
        $httpwww = isset($domains['httpwww']) ? $domains['httpwww'] : false;
        $httpswww = isset($domains['httpswww']) ? $domains['httpswww'] : false;

        ?>
        <input type="checkbox" class="button_type native builtin" name="rsssl_retrieval_domains[http]" value="1" <?php checked( $http, "1"); ?>/><?php _e("Retrieve http://domain.com", "really-simple-ssl-soc")?><br>
        <input type="checkbox" class="button_type native builtin" name="rsssl_retrieval_domains[https]" value="1" <?php checked( $https, "1"); ?>/><?php _e("Retrieve https://domain.com", "really-simple-ssl-soc")?><br>
        <input type="checkbox" class="button_type native builtin" name="rsssl_retrieval_domains[httpwww]" value="1" <?php checked( $httpwww, "1"); ?>/><?php _e("Retrieve http://www.domain.com", "really-simple-ssl-soc")?><br>
        <input type="checkbox" class="button_type native builtin" name="rsssl_retrieval_domains[httpswww]" value="1" <?php checked( $httpswww, "1"); ?>/><?php _e("Retrieve https://www.domain.com", "really-simple-ssl-soc")?><br>
        <?php
        rsssl_soc_help::get_help_tip(__("Choose which domains you want to retrieve the shares for. Sometimes Facebook returns different shares for www and non www, but sometimes they are the same. Configure accordingly.", "really-simple-ssl-soc"));
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
        <input type="checkbox" name="rsssl_social_services[facebook]" class="button_type native builtin"
               value="1" <?php checked($facebook, "1"); ?>/><?php _e("Facebook share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[linkedin]" class="button_type native builtin"
               value="1" <?php checked($linkedin, "1"); ?>/><?php _e("Linkedin share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[twitter]" class="button_type native builtin"
               value="1" <?php checked($twitter, "1"); ?>/><?php _e("Twitter  share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[google]" class="button_type native builtin"
               value="1" <?php checked($google, "1"); ?>/><?php _e("Google share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[pinterest]" class="button_type native builtin"
               value="1" <?php checked($pinterest, "1"); ?>/><?php _e("Pinterest share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[whatsapp]" class="button_type native builtin"
               value="1" <?php checked($whatsapp, "1"); ?>/><?php _e("Whatsapp share button", "really-simple-ssl-soc") ?>
        <br>
        <input type="checkbox" name="rsssl_social_services[yummly]" class="button_type native builtin"
               value="1" <?php checked($yummly, "1"); ?>/><?php _e("Yummly share button", "really-simple-ssl-soc") ?>
        <br>

        <?php
        rsssl_soc_help::get_help_tip(__("Choose which social services you want to include sharing buttons.", "really-simple-ssl-soc"));
    }


    public function get_option_fb_button_type() {
        $rsssl_fb_button_type = get_option('rsssl_fb_button_type');
        ?>
        <select name="rsssl_fb_button_type" class="builtin native button_type">
            <option value="shares" <?php if ($rsssl_fb_button_type=="shares") echo "selected"?>>Shares
            <option value="likes" <?php if ($rsssl_fb_button_type=="likes") echo "selected"?>>Likes
        </select>
        <?php
        rsssl_soc_help::get_help_tip(__("Choose if you want to use the share or the like functionality of Facebook", "really-simple-ssl-soc"));
    }

    public function get_option_sitewide_or_block() {
        $sitewide_or_block = get_option('rsssl_sitewide_or_block');

        ?>
        <select name="rsssl_sitewide_or_block" id="sitewide_or_block" class="builtin native button_type">
            <option value="sitewide" <?php if ($sitewide_or_block=="sitewide") echo "selected"?>>Sitewide
            <option value="block" <?php if ($sitewide_or_block=="block") echo "selected"?>>Block
        </select>
        <?php
        rsssl_soc_help::get_help_tip(__("Choose if you want to show the sharing buttons sitewide, or add them manually to each Gutenberg block.", "really-simple-ssl-soc"));
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
        echo '<input id="rsssl_use_custom_css" name="rsssl_use_custom_css" class="builtin button_type" size="40" type="checkbox" value="1"' . checked(1, $rsssl_use_custom_css, false) . " />";
        rsssl_soc_help::get_help_tip(__("Insert any custom CSS for the sharing buttons here", "really-simple-ssl-soc"));
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
	    <style>
		    .rsssl-main {
			    margin-left:30px;
		    }
	    </style>
        <p><?php _e('Settings for Really Simple SSL Social', 'really-simple-ssl-soc'); ?></p>
        <?php
    }

    public function check_upgrade()
    {
        $prev_version = get_option('rsssl-soc-current-version', '1.0.0');

        if (version_compare($prev_version, '4.0', '<')) {
            if (get_option('rsssl_insert_custom_buttons') == 1) {
                update_option('rsssl_button_type', 'builtin');
                update_option('rsssl_buttons_theme', 'color');
                update_option('rsssl_share_cache_time' , '24');
            } else {
                update_option('rsssl_button_type', 'existing');
            }
            update_option('rsssl-soc-current-version', rsssl_soc_version);
        }
    }

    public function maybe_show_og_url_notice()
    {
	    $rsssl_button_type = get_option('rsssl_button_type');

    	if (!get_option('add_og_url') && $rsssl_button_type == "existing" && !get_option('rsssl_soc_og_url_notice_dismissed')) {
		    ?>
		    <div id="message" class="error fade notice is-dismissible rsssl-soc-dismiss-notice">
			    <p><?php echo sprintf(__( "Really Simple SSL Social hasn't found an og url property in your page source. The og url property is required before share retrieval can work correctly. Enable the og url option on the plugin %ssettings page%s", "really-simple-ssl-soc" ), "<a href=".admin_url('options-general.php?page=rlrsssl_really_simple_ssl').">" ,   "</a>");?></p>
		    </div>
		    <?php
	    }
    }

	public function insert_dismiss_og_url_notice()
	{
		$ajax_nonce = wp_create_nonce("really-simple-ssl");
		?>
		<script type='text/javascript'>
            jQuery(document).ready(function ($) {
                $(".rsssl-soc-dismiss-notice.is-dismissible").on("click", ".notice-dismiss", function (event) {
                    var data = {
                        'action': 'dismiss_og_url_notice',
                        'security': '<?php echo $ajax_nonce; ?>'
                    };
                    $.post(ajaxurl, data, function (response) {

                    });
                });
            });
		</script>
		<?php
	}

	public function dismiss_og_url_notice_callback()
	{
		if (!current_user_can($this->capability) ) return;
		check_ajax_referer('really-simple-ssl', 'security');
		update_option('rsssl_soc_og_url_notice_dismissed', true);
		wp_die(); // this is required to terminate immediately and return a proper response
	}

}//class closure
