<?php
defined('ABSPATH') or die("you do not have access to this page!");
if (!defined('REALLY_SIMPLE_SSL_URL')) define( 'REALLY_SIMPLE_SSL_URL', 'https://www.really-simple-ssl.com'); // you should use your own CONSTANT name, and be sure to replace it throughout this file

if( !class_exists( 'RSSSL_SOC_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

if (!class_exists("rsssl_soc_licensing")) {

class rsssl_soc_licensing {
	private static $_this;

	function __construct() {
		if ( isset( self::$_this ) )
				wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl' ), get_class( $this ) ) );

	    $this->website = 'https://really-simple-ssl.com';
	    $this->author = 'RogierLankhorst';

		self::$_this = $this;
		add_action('admin_init', array($this, 'plugin_updater'), 0 );
		add_action('admin_init', array($this, 'activate_license'), 10,3);
		add_action('admin_init', array($this, 'register_option'), 20,3);
		add_action('admin_init', array($this, 'deactivate_license'),30,3);
		add_action('admin_init', array($this, 'add_license_hooks'),40,4);
	}

	static function this() {
		return self::$_this;
	}

	public function add_license_hooks() {
		add_filter('rsssl_social_license_block', array($this, 'add_social_license_block'), 30 );
		if (defined('rsssl_pro_version')) return;
		add_filter( 'rsssl_grid_tabs', array( $this, 'add_license_tab' ), 20, 3 );

		// Standalone social compatibility, if free not found, add the standalone license page
		if (defined('rsssl_version')) {
			add_action( 'show_tab_license', array( $this, 'add_license_page' ) );
		} else {
			add_action( 'show_tab_license', array( $this, 'add_standalone_license_page' ) );
		}

		add_action('wp_ajax_rsssl_soc_dismiss_license_notice', array($this,'dismiss_license_notice') );
		add_action("admin_notices", array($this, 'show_notice_license'));
		add_action("network_admin_notices", array($this, 'show_multisite_notice_license'));
	}

	/**
	 * Get the license key
	 * @return string
	 */
	public function license_key(){
		return trim( get_option( 'rsssl_soc_license_key' ) );
	}

	public function add_social_license_block($grid_items=false) {

		$grid_items['social'] = array(
				'title' => __("Really Simple SSL Social license key", "really-simple-ssl"),
				'header' => rsssl_template_path.'/header.php',
				'content' => rsssl_soc_path.'/grid/templates/license.php',
				'footer' => rsssl_soc_path . '/grid/templates/license-footer.php',
				'class' => 'regular rsssl-license-grid',
				'type' => 'settings',
				'can_hide' => true,
				'instructions' => false,
		);

		return $grid_items;
	}

	public function add_standalone_license_page(){
		$license 	= get_option( 'rsssl_soc_license_key' );
		$status 	= get_option( 'rsssl_soc_license_status' );
		$status = $this->get_license_status();

		if (!defined('rsssl_pro_version')) { ?>
			<style>
				.rsssl-main {
					margin:30px;
				}
			</style>
			<form method="post" action="options.php">
		<?php }
		wp_nonce_field( 'rsssl_soc_nonce', 'rsssl_soc_nonce' );
		settings_fields('rsssl_soc_license');

		echo $this->get_license_label();


	if (!defined('rsssl_pro_version')) {
		?>
		<table class="form-table">
		<tbody>
	<?php } ?>
	<tr valign="top" style="margin-top: 10px">
		<th scope="row" valign="top">
			<?php _e('Really Simple SSL social license Key'); ?>
		</th>
		<td>
		<input id="rsssl_soc_license_key" class="rsssl_license_key" name="rsssl_soc_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
		<?php if( false !== $license ) { ?>
		<?php if( $status !== false && $status == 'valid' ) { ?>
			<span style="color:green;"><?php _e('active'); ?></span>
			<input type="submit" class="button-secondary" name="rsssl_soc_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
		<?php } else {?>
			<input type="submit" class="button-secondary" name="rsssl_soc_license_activate" value="<?php _e('Activate License'); ?>"/>

		<?php } ?>
		</td>
	</tr>
	<?php } else {
		?>
		<label class="description" for="rsssl_soc_license_key"><?php _e('Enter your license key'); ?></label>
		<?php
	}?>
		<?php if (!defined('rsssl_pro_version')) { ?>
			</tbody>
			</table>
			<input type="submit" name="rsssl_soc_license_activate" id="submit" class="button button-primary" value="<?php _e("Save changes", "really-simple-ssl-social"); ?>">
			</form>
		<?php } else {?>
			<input type="hidden" name="rsssl_soc_license_activate" id="submit" class="button button-primary" value="rsssl_soc_license_key">
		<?php }?>
		<?php
	}


	/**
	 * Add license page to grid block
	 */

	public function add_license_page() {
		if (!current_user_can('manage_options')) return;
		RSSSL()->really_simple_ssl->render_grid( $this->add_social_license_block() );
	}

	/**
	 * Show a license notice
	 */
	public function show_notice_license(){
		//prevent showing the review on edit screen, as gutenberg removes the class which makes it editable.
		$screen = get_current_screen();
		if ( $screen->parent_base === 'edit' ) return;

		add_action('admin_print_footer_scripts', array($this, 'dismiss_license_notice_script'));
		 $dismissed	= get_option('rsssl_soc_license_notice_dismissed');
		 if (!$this->license_is_valid() && !$dismissed) { ?>
			 <?php if (!is_multisite()) {?>
					<div class="error fade notice is-dismissible rsssl-soc-dismiss-notice">
				    <p>
				      <?php echo __("You haven't activated your Really Simple SSL social license yet. To get all future updates, enter your license on the settings page.","really-simple-ssl-social");?>
							<a href="options-general.php?page=rlrsssl_really_simple_ssl&tab=license"><?php echo __("Go to the settings page","really-simple-ssl-social");?></a>
							or <a target="blank" href="https://www.really-simple-ssl.com/premium">purchase a license</a>
						</p>
					</div>
				<?php } ?>
		<?php
		}
	}

	/**
	 * Process the ajax dismissal of the success message.
	 *
	 * @since  2.0
	 *
	 * @access public
	 *
	 */

	public function dismiss_license_notice() {
		$resp = check_ajax_referer( 'rsssl_soc-dismiss-license-notice', 'nonce' );
		update_option( 'rsssl_soc_license_notice_dismissed', true);
		wp_die();
	}

	public function dismiss_license_notice_script() {
	  $ajax_nonce = wp_create_nonce( "rsssl_soc-dismiss-license-notice" );
	  ?>
	  <script type='text/javascript'>
	    jQuery(document).ready(function($) {
	      $(".rsssl-soc-dismiss-notice.notice.is-dismissible").on("click", ".notice-dismiss", function(event){
	            var data = {
	              'action': 'rsssl_soc_dismiss_license_notice',
	              'nonce': '<?php echo $ajax_nonce; ?>'
	            };

	            $.post(ajaxurl, data, function(response) {

	            });
	        });
	    });
	  </script>
	  <?php
	}

	public function show_multisite_notice_license(){
	//prevent showing the review on edit screen, as gutenberg removes the class which makes it editable.
	$screen = get_current_screen();
	if ( $screen->parent_base === 'edit' ) return;

		if (is_multisite()) { ?>
	    <?php if (is_main_site(get_current_blog_id()) && !$this->license_is_valid()) { ?>

			  <div id="message" class="error fade notice">
			    <p>
			      <?php echo __("You haven't activated your Really Simple SSL social license yet on your main site. To get all future updates, enter your license on the settings page of your main site.","really-simple-ssl-social");?>
						<a href="<?php echo trailingslashit(admin_url())?>options-general.php?page=rlrsssl_really_simple_ssl&tab=license"><?php echo __("Go to the settings page","really-simple-ssl-social");?></a>
						or <a target="blank" href="https://www.really-simple-ssl.com/pro">purchase a license</a>
					</p>
				</div>
			<?php } ?>
	 <?php
		}
	}

	public function plugin_updater() {
		// retrieve our license key from the DB
		$license_key = trim( get_option( 'rsssl_soc_license_key' ) );

		// setup the updater
		$edd_updater = new RSSSL_SOC_SL_Plugin_Updater( REALLY_SIMPLE_SSL_URL, dirname(__FILE__)."/really-simple-social.php", array(
				'version' 	=> rsssl_soc_version, 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_id' => RSSSL_SOC_ITEM_ID,
				'author' 	=> 'Rogier Lankhorst'  // author of this plugin
			)
		);

	}

	public function add_license_tab($tabs){
		$tabs['license'] = __("License","really-simple-ssl-social");
		return $tabs;
	}

	public function register_option() {
		// creates our settings in the options table
		register_setting('rsssl_soc_license', 'rsssl_soc_license_key', array($this, 'sanitize_license') );
	}

	public function sanitize_license( $new ) {
		$old = get_option( 'rsssl_soc_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'rsssl_soc_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}


	/**
	 * Activate the license key
	 */

	public function activate_license() {
		if (!current_user_can('manage_options')) return;

		// listen for our activate button to be clicked
		if(isset($_POST['rsssl_soc_license_activate']) ) {
			// run a quick security check

		    if( ! check_admin_referer( 'rsssl_soc_nonce', 'rsssl_soc_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = sanitize_key(trim( $_POST['rsssl_soc_license_key']));
			update_option('rsssl_soc_license_key', $license);
			$this->get_license_status('activate_license', true );

		}
	}


	/**
	 * Deactivate the license
	 * @return bool|void
	 */

	public function deactivate_license() {
		if (!current_user_can('manage_options')) return;

		// listen for our deactivate button to be clicked
		if( isset( $_POST['rsssl_soc_license_deactivate'] ) ) {

			// run a quick security check
		    if( ! check_admin_referer( 'rsssl_soc_nonce', 'rsssl_soc_nonce' ) )
				return;

			$this->get_license_status('deactivate_license', true );
		}
	}

	/**
	 * Check if license is valid
	 * @return bool
	 */

	public function license_is_valid()
	{
		$status = $this->get_license_status();
		if ($status == "valid") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Save the license key
	 */

	public function save_license(){

		if (!current_user_can('manage_options')) return;

		if (!isset($_POST["rsssl_soc_license_save"]) || !isset($_POST["rsssl_soc_license_save"]) || !isset($_POST['rsssl_soc_nonce']) ) return;

		if( !wp_verify_nonce( $_POST['rsssl_soc_nonce'], 'rsssl_soc_nonce' ) ) return;

		$license = sanitize_text_field(trim($_POST['rsssl_soc_license_key']));
		$license = $this->sanitize_license($license);
		update_option('rsssl_soc_license_key', $license );

		if ( is_network_admin() ) {
			wp_redirect(add_query_arg(array('page' => "really-simple-ssl", 'tab' => 'license'), network_admin_url('settings.php')));
		} else {
			wp_redirect(add_query_arg(array('page' => "rlrsssl_really_simple_ssl", 'tab' => 'license'), admin_url('options-general.php')));
		}
		exit;
	}

	/**
	 * Get latest license data from license key
	 * @param string $action
	 * @param bool $clear_cache
	 * @return string
	 *   empty => no license key yet
	 *   invalid, disabled, deactivated
	 *   revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
	 *   inactive, expired, valid
	 */

	public function get_license_status($action = 'check_license', $clear_cache = false )
	{
		$status = get_site_transient('rsssl_soc_license_status');
		if ($clear_cache) $status = false;

		if (!$status || get_site_option('rsssl_soc_license_activation_limit') === FALSE ){
			$status = 'invalid';
			$license = get_option('rsssl_soc_license_key');

			if ( strlen($license) ===0 ) return 'empty';

			// data to send in our API request
			$api_params = array(
				'edd_action' => $action,
				'license' => $license,
				'item_id' => RSSSL_SOC_ITEM_ID,
				'url' => home_url()
			);

			$args = apply_filters('rsssl_license_verification_args', array('timeout' => 15, 'sslverify' => true, 'body' => $api_params) );
			$response = wp_remote_post(REALLY_SIMPLE_SSL_URL, $args);
			if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
				set_site_transient('rsssl_soc_license_status', 'error');
			} else {
				$license_data = json_decode(wp_remote_retrieve_body($response));
				if ( $license_data && ($license_data->license =='invalid' || $license_data->license === 'disabled') ){
					$status = $license_data->license; //invalid, disabled, deactivated
				} elseif ( $license_data && ( false === $license_data->success )) {
					$status = $license_data->error; //revoked, missing, invalid, site_inactive, item_name_mismatch, no_activations_left
				} elseif ( $license_data && ($license_data->license === 'failed' || $license_data->license === 'deactivated') ) {
					$status = 'empty';
					delete_site_option('rsssl_soc_license_expires' );
					delete_site_option('rsssl_soc_license_activation_limit' );
					delete_site_option('rsssl_soc_license_activations_left' );
				} elseif ( $license_data && ( true === $license_data->success )) {
					$status = $license_data->license; //inactive, expired, valid
					$date = $license_data->expires;
					if ( $date !== 'lifetime' ) {
						$date = strtotime($date);
						$date = date(get_option('date_format'), $date);
					}

					update_site_option('rsssl_soc_license_expires', $date);
					update_site_option('rsssl_soc_license_activation_limit', $license_data->license_limit);
					update_site_option('rsssl_soc_license_activations_left', $license_data->activations_left);
				} elseif ($license_data && $license_data->license == 'deactivated') {
					$status = 'inactive';
				}
			}

			set_site_transient('rsssl_soc_license_status', $status, WEEK_IN_SECONDS);
		}
		return $status;
	}

	/**
	 * Get license status label
	 * @return string
	 */

	public function get_license_label(){
		$status = $this->get_license_status();
		$support_link = '<a target="_blank" href="https://really-simple-ssl.com/support">';
		$account_link = '<a target="_blank" href="https://really-simple-ssl.com/account">';

		$activation_limit = get_site_option('rsssl_soc_license_activation_limit' );
		$activations_left = get_site_option('rsssl_soc_license_activations_left' );
		$expires_date = get_site_option('rsssl_soc_license_expires' );

		$expires_message = $expires_date === 'lifetime' ? __("You have a lifetime license.", 'really-simple-ssl-pro') : sprintf(__("Valid until %s.", 'really-simple-ssl-pro'), $expires_date);

		$next_upsell = '';
		if ($activations_left == 0) {
			switch ( $activation_limit ) {
				case 1:
					$next_upsell = sprintf(__( "Upgrade to a %s5 sites or unlimited%s license.", "really-simple-ssl-pro" ), $account_link, '</a>');
					break;
				case 5:
					$next_upsell = sprintf(__( "Upgrade to an %sAgency%s license.", "really-simple-ssl-pro" ), $account_link, '</a>');
					break;
				default:
					$next_upsell = sprintf(__( "You can renew your license on your %saccount%s.", "really-simple-ssl-pro" ), $account_link, '</a>');
			}
		}

		$messages = array();

		/**
		 * Some default messages, if the license is valid
		 */

		if ( $status === 'valid' || $status === 'no_activations_left' || $status === 'inactive' ) {
			$messages[] = array(
				'type' => 'success',
				'label' => __('Open', 'really-simple-ssl-pro'),
				'message' => $expires_message,
			);

			$messages[] = array(
				'type' => 'premium',
				'label' => __('Open', 'really-simple-ssl-pro'),
				'message' => sprintf(__("Valid license for %s.", 'really-simple-ssl-pro'), 'Really Simple SSL Social '.rsssl_soc_version),
			);

			$messages[] = array(
				'type' => 'premium',
				'label' => __('License', 'really-simple-ssl-pro'),
				'message' => sprintf(__("%s/%s activations available.", 'really-simple-ssl-pro'), $activations_left, $activation_limit ).' '.$next_upsell,
			);
		}

		switch ( $status ) {
			case 'error':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("The license information could not be retrieved at this moment. Please try again at a later time.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
			case 'empty':
				$messages[] = array(
					'type' => 'open',
					'label' => __('Open', 'really-simple-ssl-pro'),
					'message' => sprintf(__("Please enter your license key. Available in your %saccount%s.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
			case 'inactive':
			case 'deactivated':
			case 'site_inactive':
				$messages[] = array(
					'type' => 'open',
					'label' => __('Open', 'really-simple-ssl-pro'),
					'message' => sprintf(__("Please activate your license key.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
			case 'revoked':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("Your license has been revoked. Please contact %ssupport%s.", 'really-simple-ssl-pro'), $support_link, '</a>'),
				);
				break;
			case 'missing':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("Your license could not be found in our system. Please contact %ssupport%s.", 'really-simple-ssl-pro'), $support_link, '</a>'),
				);
				break;
			case 'invalid':
			case 'disabled':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("This license is not valid. Find out why on your %saccount%s.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
			case 'item_name_mismatch':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("This license is not valid for this product. Find out why on your %saccount%s.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
			case 'no_activations_left':
				$messages[] = array(
					'type' => 'open',
					'label' => __('License', 'really-simple-ssl-pro'),
					'message' => sprintf(__("%s/%s activations available.", 'really-simple-ssl-pro'), 0, $activation_limit ).' '.$next_upsell,
				);
				break;
			case 'expired':
				$messages[] = array(
					'type' => 'warning',
					'label' => __('Warning', 'really-simple-ssl-pro'),
					'message' => sprintf(__("Your license key has expired. Please renew your license key on your %saccount%s.", 'really-simple-ssl-pro'), $account_link, '</a>'),
				);
				break;
		}

		$html = '';
		foreach ( $messages as $message ) {
			$html .= $this->rsssl_notice( $message );
		}

		return $html;
	}

	/**
	 * Show a notice regarding the license
	 * @param array $message
	 *
	 * @return string
	 */

	public function rsssl_notice($message)
	{
		if ( !isset($message['message']) || $message['message'] == '') return '';

		ob_start();
		?>
		<style>
			.rsssl-progress-status {
				display: block;
				min-width: 60px;
				text-align: center;
				border-radius: 15px;
				padding: 4px 8px 4px 8px;
				font-size: 0.8em;
				font-weight: 600;
				height: 17px;
				line-height: 17px;
			}
			#rsssl_soc_license_key {
				<?php if (!defined('rsssl_pro_version') ) echo 'width:300px !important;'?>
			}
		</style>
		<tr style="width:100%">
			<td>
	                    <span class="rsssl-progress-status rsssl-license-status rsssl-<?php echo $message['type'] ?>">
                            <?php echo $message['label'] ?>
                        </span>
			</td>
			<td class="rsssl-license-notice-text">
				<?php echo $message['message'] ?>
			</td>
		</tr>
		<?php

		$contents = ob_get_clean();
		echo $contents;
	}

	//change deprecated function depending on version.

	public function get_sites_bw_compatible(){
		global $wp_version;
		$sites = ($wp_version >= 4.6 ) ? get_sites() : wp_get_sites();
		return $sites;
	}
	/**
		The new get_sites function returns an object.
	*/

	public function switch_to_blog_bw_compatible($site){
		global $wp_version;
		if ($wp_version >= 4.6 ) {
			switch_to_blog( $site->blog_id );
		} else {
			switch_to_blog( $site[ 'blog_id' ] );
		}
	}
}} //class closure
