<?php
/**
 * Define for PPR.
 */
define('PPR_OPTIONS', 'passlogy_pwd_reminder_options');
/**
 * The parameter defined for options
 */
define('PPR_OPTIONS_VERSION',				'PprOptionsVersion');
define('PPR_OPTIONS_SERVER_URL',			'PprServerUrl');
define('PPR_OPTIONS_SERVER_API_URI',		'PprServerApiUri');

/**
 * This is the method that initialize the PPR option, it is stored in WP
 */
function ppr_options_init() {
	$ppr_options_default = array(
								PPR_OPTIONS_VERSION				=> PPR_VERSION,
								PPR_OPTIONS_SERVER_URL			=> 'https://leafintheforest.com/',
								PPR_OPTIONS_SERVER_API_URI		=> 'https://leafintheforest.com:8088/api?mode=auth',
							);

	/* Get current options setting */
	$ppr_options = get_option(PPR_OPTIONS);

	/* Set plugin default options */
	$ret = add_option(PPR_OPTIONS, $ppr_options_default);

	if (($ret != true) && ($ppr_options[PPR_OPTIONS_VERSION] != PPR_VERSION)) {
//		print('updatting...');
		$ppr_options_default[PPR_OPTIONS_SERVER_API_URI]	= $ppr_options[PPR_OPTIONS_SERVER_API_URI];
		update_option(PPR_OPTIONS, $ppr_options_default);
	}
}

/**
 * This is the method that get the PPR option from WP
 * 
 * @return PPR options
 */
function ppr_options_get() {
	$ppr_options = get_option(PPR_OPTIONS);
	return $ppr_options;
}

/**
 * This is the method that make the PPR options setting page
 */
function ppr_options_page() {
	$ppr_options = get_option(PPR_OPTIONS);

	if ((isset($_POST['ppr_action'])) && ($_POST['ppr_action'] == __('Save Changes'))) {
			$ppr_options[PPR_OPTIONS_SERVER_URL]		= $_POST[PPR_OPTIONS_SERVER_URL];
			$ppr_options[PPR_OPTIONS_SERVER_API_URI]	= $_POST[PPR_OPTIONS_SERVER_API_URI];
			update_option(PPR_OPTIONS, $ppr_options);
?>

<div id='setting-error-settings_updated' class='updated settings-error'> 
<p><strong><?php _e('Options saved.') ?></strong></p></div> 

<?php
	}
?>

<div class="wrap">
<h2><?php _e('Passlogy Password Reminder Options Setting', PPR_TEXTDOMAIN); ?></h2>

<form method="POST" action="options-general.php?page=passlogy_pwd_reminder.php">

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="<?php echo PPR_OPTIONS_SERVER_URL; ?>"><?php _e('PPR Server URL', PPR_TEXTDOMAIN); ?></label></th>
<td><input name="<?php echo PPR_OPTIONS_SERVER_URL; ?>" type="text" id="<?php echo PPR_OPTIONS_SERVER_URL; ?>" value="<?php echo $ppr_options[PPR_OPTIONS_SERVER_URL]; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo PPR_OPTIONS_SERVER_API_URI; ?>"><?php _e('PPR Server API URI', PPR_TEXTDOMAIN); ?></label></th>
<td><input name="<?php echo PPR_OPTIONS_SERVER_API_URI; ?>" type="text" id="<?php echo PPR_OPTIONS_SERVER_API_URI; ?>" value="<?php echo $ppr_options[PPR_OPTIONS_SERVER_API_URI]; ?>" class="regular-text" /></td>
</tr>
</table>

<p class="submit"><input type="submit" name="ppr_action" id="ppr_action" class="button button-primary" value="<?php _e('Save Changes'); ?>" /></p>
</form>
</div>

<?php
}
?>
