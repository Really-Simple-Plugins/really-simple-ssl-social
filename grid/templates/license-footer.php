<?php defined('ABSPATH') or die("you do not have access to this page!");
	global $rsssl_soc_licensing;
	$status = $rsssl_soc_licensing->get_license_status();

	?>
	<input type="submit" class="button button-secondary" name="rsssl_soc_license_save" value="<?php _e('Save', "really-simple-ssl-social"); ?>"/>
<?php

    if ($status && $status == 'valid') { ?>
        <input type="submit" class="button-secondary" name="rsssl_soc_license_deactivate" value="<?php _e('Deactivate license'); ?>"/>
	<?php } else { ?>
        <input type="submit" class="button-secondary" name="rsssl_soc_license_activate" value="<?php _e('Activate license'); ?>"/>
	<?php }


