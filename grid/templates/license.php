<?php defined('ABSPATH') or die("you do not have access to this page!");
global $rsssl_soc_licensing;
$license = $rsssl_soc_licensing->license_key();
$status = get_site_transient('rsssl_pro_license_status');

wp_nonce_field('rsssl_soc_nonce', 'rsssl_soc_nonce');
if (!is_network_admin()) {
	settings_fields('rsssl_soc_license');
} else { ?>
	<input type="hidden" name="option_page" value="rsssl_network_options">
<?php } ?>

<table class="form-table rsssl-license-table">
	<tbody>
	<tr style="width:100%">
		<td class="rsssl-license-field" style="width:100%">
			<input style="width:100%" id="rsssl_soc_license_key" class="rsssl_license_key" name="rsssl_soc_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
			<?php if ($license) { ?>
			<?php } ?>
		</td>
	</tr>
	<?php echo $rsssl_soc_licensing->get_license_label() ?>
	</tbody>
</table>
