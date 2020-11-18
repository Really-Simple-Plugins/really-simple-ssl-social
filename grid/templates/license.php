<?php defined('ABSPATH') or die("you do not have access to this page!");
global $rsssl_soc_licensing;
$license = trim( get_option( 'rsssl_soc_license_key' ) );
$license_data = $rsssl_soc_licensing->get_latest_license_data();
$status = get_site_transient('rsssl_soc_license_status');

wp_nonce_field('rsssl_pro_nonce', 'rsssl_pro_nonce');
if (!is_network_admin()) {
	settings_fields('rsssl_soc_license');
} else { ?>
	<input type="hidden" name="option_page" value="rsssl_network_options">
<?php } ?>
<table class="form-table rsssl-license-table">
	<tbody>
	<tr>
		<td class="rsssl-license-field">
			<input id="rsssl_soc_license_key" class="rsssl_license_key" name="rsssl_soc_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
			<?php if ($license) { ?>
			<?php } ?>
		</td>
	</tr>
	<?php
	$message = $rsssl_soc_licensing->get_error_message($license_data);
	if ($status=='valid' || $license_data->license=='site_inactive') {
		$upgrade = $license_data->license_limit == 1 ? __("a 5 sites or unlimited sites license", "really-simple-ssl-pro") : __("an unlimited sites license", "really-simple-ssl-pro");
		if ($license_data->activations_left < $license_data->license_limit) {
			$rsssl_soc_licensing->rsssl_notice(sprintf(__('You have %d activations left on your license. If you need more activations you can upgrade your current license to %s on your %saccount%s page.', "really-simple-ssl-pro"), $license_data->activations_left, $upgrade, '<a href="https://really-simple-ssl.com/account" target="_blank">', '</a>'), 'warning');
		}
	}

	if ($message) {
		$rsssl_soc_licensing->rsssl_notice($message,'warning');
	} elseif ($license_data->license == 'deactivated'){
		if ($status=='valid'){
			$rsssl_soc_licensing->rsssl_notice(__("Your license is valid, but not activated on this site", 'really-simple-ssl-pro'), 'open');
		} elseif(!empty($status)) {
			$rsssl_soc_licensing->rsssl_notice(__("Your license does not seem to be valid. Please check your license key", 'really-simple-ssl-pro'), 'open');
		}
	} elseif ($status == 'valid') {
		$date = $license_data->expires;
		$date = strtotime($date);
		$date = date(get_option('date_format'), $date);
		$rsssl_soc_licensing->rsssl_notice(sprintf(__("Your license is valid, and expires on: %s", 'really-simple-ssl-pro'), $date), 'success');
	} elseif ($license_data->license == 'expired') {
		$link = '<a target="_blank" href="' . $rsssl_soc_licensing->website . "/account/" . '">';
		$rsssl_soc_licensing->rsssl_notice(sprintf(__("Your license key has expired. Please renew your license key on %syour account page%s", 'really-simple-ssl-pro'), $link, '</a>'), 'warning');
	} elseif ($license_data->activations_left == '0') {
		$rsssl_soc_licensing->rsssl_notice(sprintf(__("Your license key cannot be activated because you have no activations left. Check on which site your license is currently activated or upgrade to a 5 site or unlimited license on your %saccount%s page.", "really-simple-ssl-pro"), '<a href="https://really-simple-ssl.com/account" target="_blank">', '</a>'), 'warning');
	}
	else {
		$rsssl_soc_licensing->rsssl_notice(__("Enter your license here so you keep receiving updates and support.", 'really-simple-ssl-pro'), 'open');
	}
	?>
	</tbody>
</table>
