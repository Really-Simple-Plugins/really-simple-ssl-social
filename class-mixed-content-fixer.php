<?php
defined('ABSPATH') or die("you do not have access to this page!");

if (!class_exists('rsssl_soc_mixed_content_fixer')) {
    class rsssl_soc_mixed_content_fixer
    {
        private static $_this;

        function __construct()
        {
            if (isset(self::$_this))
                wp_die(sprintf(__('%s is a singleton class and you cannot create a second instance.', 'really-simple-ssl'), get_class($this)));

            self::$_this = $this;

            //exclude admin here: for all well built plugins and themes, this should not be necessary.
            if (!is_admin() && is_ssl()) {
                $this->fix_mixed_content();
            }

        }

        static function this()
        {
            return self::$_this;
        }

        /**
         *
         * add action hooks at the start and at the end of the WP process.
         *
         * @since  2.3
         *
         * @access public
         *
         */

        public function fix_mixed_content()
        {

            /* Do not fix mixed content when call is coming from wp_api or from xmlrpc */
            if (defined('JSON_REQUEST') && JSON_REQUEST) return;
            if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) return;

            /*
                Take care with modifications to hooks here:
                hooks tend to differ between front and back-end.
            */

            if (is_admin()) {

                add_action("admin_init", array($this, "start_buffer"), 100);
                add_action("shutdown", array($this, "end_buffer"), 999);

            } else {

                if ( (defined('RSSSL_CONTENT_FIXER_ON_INIT') && RSSSL_CONTENT_FIXER_ON_INIT) ) {
                    add_action("init", array($this, "start_buffer"));
                } else {
                    add_action("template_redirect", array($this, "start_buffer"));
                }

                add_action("shutdown", array($this, "end_buffer"), 999);
            }
        }


        /**
         * Apply the mixed content fixer.
         *
         * @since  2.3
         *
         * @access public
         *
         */

        public function filter_buffer($buffer)
        {
            $buffer = $this->replace_insecure_links($buffer);
            return $buffer;
        }

        /**
         * Start buffering the output
         *
         * @since  2.0
         *
         * @access public
         *
         */

        public function start_buffer()
        {
            ob_start(array($this, "filter_buffer"));
        }

        /**
         * Flush the output buffer
         *
         * @since  2.0
         *
         * @access public
         *
         */

        public function end_buffer()
        {
            if (ob_get_length()) ob_end_flush();
        }

        /**
         * Just before the page is sent to the visitor's browser, all homeurl links are replaced with https.
         *
         * @since  1.0
         *
         * @access public
         *
         */

        public function replace_insecure_links($str)
        {
            //skip if file is xml
            if (substr($str, 0, 5) == "<?xml") return $str;

            return apply_filters("rsssl_fixer_output", $str);

        }

    }
}

