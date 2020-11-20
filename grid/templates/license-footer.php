<?php defined('ABSPATH') or die("you do not have access to this page!");
global $rsssl_soc_licensing;
    $license_data = $rsssl_soc_licensing->get_latest_license_data();
    $status = get_site_transient('rsssl_pro_license_status');

    $current_user = get_current_user_id();
    $allowed_user = intval( get_option('rsssl_licensing_allowed_user_id') );

    if ($status && $status == 'valid') { ?>
        <input type="submit" class="button-secondary" name="rsssl_soc_license_deactivate" value="<?php _e('Deactivate license'); ?>"/>
	<?php } else { ?>
        <input type="submit" class="button-secondary" name="rsssl_soc_license_activate" value="<?php _e('Activate license'); ?>"/>
	<?php }


