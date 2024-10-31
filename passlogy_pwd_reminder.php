<?php
/*
Plugin Name: Passlogy Password Reminder
Description: Passlogy Password Reminder will show your random password in the matrix for login.
Version: 0.1.7
Author: Passlogy Co.,Ltd.
Author URI: http://www.leafintheforest.com/
Plugin URI: http://www.leafintheforest.com/?page_id=50
Text Domain: passlogy_pwd_reminder
Domain Path: /languages
*/

/**
 * Passlogy Password Reminder will show your random password in the matrix for login.
 * 
 * @package passlogy_pwd_reminder
 * @author naka
 * @copyright (C) Passlogy Co.,Ltd. 2000-2014
 */
if (!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL') )
	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR') )
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');


/**
 * Define for PPR.
 */
define('PPR_VERSION',			'0.1.7');
define('PPR_TEXTDOMAIN',		'passlogy_pwd_reminder');
define('PPR_OPTIONS_SETTING',	'ppr_options_setting');
define('PPR_LANG_DIR',			'languages');

require_once('passlogy_pwd_reminder_options.php');
require_once('passlogy_pwd_reminder_login.php');


/**
 * @package passlogy_pwd_reminder
 */
class passlogy_pwd_reminder {

	public static function passlogy_pwd_reminder_action_init() {
		add_filter('authenticate', 'ppr_login_authenticate', 100, 3);
	}

	public static function passlogy_pwd_reminder_login() {
		;
	}

} /* class end */
add_action('init', array('passlogy_pwd_reminder', 'passlogy_pwd_reminder_action_init'));


/**
 * This is the method that load the languages file
 */
function ppr_filter_init(){
	/* Load the languages .mo file */
	load_plugin_textdomain(PPR_TEXTDOMAIN, false, dirname(plugin_basename(__FILE__)).'/'.PPR_LANG_DIR);
}
add_filter('init', 'ppr_filter_init');


/**
 * This is the method that add the PPR options setting menu link to admin setting menu
 */
function ppr_action_admin_menu() {
	/* Add a options setting page for PPR */
	add_options_page(__('Passlogy Password Reminder Options Setting', PPR_TEXTDOMAIN),
					 __('PPR Options' ,PPR_TEXTDOMAIN),
					 'manage_options',
					 basename(__FILE__),
					 'ppr_options_page');

}
add_action('admin_menu', 'ppr_action_admin_menu');


/**
 * This is the method that callback form filter of plugin_action_links
 * 
 * @param links
 *				the tag form filter
 * @param file
 *				the value for the tag
 * @return a link to PPR options setting
 */
function ppr_filter_plugin_action_links($links, $file) {
	static $thisPlugin;

	if (!$thisPlugin) {
		$thisPlugin = plugin_basename(__FILE__);
	}

	if ($file == $thisPlugin){
		$settings_link = '<a href="options-general.php?page='.basename(__FILE__).'">'.__('Settings').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}
add_filter('plugin_action_links', 'ppr_filter_plugin_action_links', 10, 2);


/**
 * This is the method that callback form filter of http_api_curl
 * Set the SSL Version to "SSLv3".
 * 
 * @param handle
 *				the curl handle
 */
function ppr_action_http_api_curl($handle) {
	curl_setopt($handle, CURLOPT_SSLVERSION, 3);
}
add_action('http_api_curl', 'ppr_action_http_api_curl');


/* define the wp plugin url of PPR server */
define('PPR_SERVER_WPPLUGIN_PATH', 'wp_plugin/');
/**
 * This is the method is called when creating login form
 * 
 * @param newIf
 *				is new interface or not
 * @return the contents from ppr server
 */
function ppr_connect_button( $newIf = false ) {
	$ppr_options = ppr_options_get();
	$server_plugin_url  = $ppr_options[PPR_OPTIONS_SERVER_URL];

	if ($_GET['pprmode'] == 'templogin') {
		$username = esc_attr(wp_unslash($_GET['log']));
?>
	<p>
		<label for="ppr_login_code"><?php _e('Login code', PPR_TEXTDOMAIN) ?><br />
		<input type="password" name="ppr_login_code" id="ppr_login_code" class="input" value="" size="20" /></label>
	</p>
	<script type="text/javascript">document.getElementById("user_login").value = "<?php echo $username ?>";</script>
<?php
		// Sent the logincode to user by email
		ppr_send_login_code($username);
	}

	if (strlen($server_plugin_url)) {
		$server_plugin_url .= substr($server_plugin_url, -1) == '/' ? '' : '/';
		$server_plugin_url .= PPR_SERVER_WPPLUGIN_PATH;

		if (is_ssl()) {
			// replace the protocol to SSL
			$server_plugin_url = preg_replace('/^http:\/\//', 'https://', $server_plugin_url, 1);
		}

		$response = wp_remote_get($server_plugin_url, array('sslverify' => false));
		if (!is_wp_error($response) && $response['response']['code'] == '200') {
			$result = $response['body'];
		}
	}

	if (!$result) {
		$result = __('<p>protected by <strong><font color="#fa0">Passlogy Password Reminder</font></strong>.</p><br>', PPR_TEXTDOMAIN);
	}
	
	if ($newIf) {
		return $result;
	} else {
		echo $result;
	}
}
add_action('login_form', 'ppr_connect_button');

/**
 * This is the method that is errors filter on WordPress login page
 * 
 * @param errors
 *				WP Error object
 * @param redirect_to
 *				redirect destination URL
 * @return WP Error object
 */
function ppr_filter_login_errors($errors, $redirect_to) {

	if ($_GET['pprmode'] == 'templogin') {
		$errors->add('pprmode_templogin', __('Please input the login code that was sent to your email for login without Passlogy Password Reminder temporarily.', PPR_TEXTDOMAIN), 'message');
	}

	return $errors;
}
add_filter('wp_login_errors', 'ppr_filter_login_errors', 10, 2);

/**
 * This is the method is called when activate the plugin
 */
function ppr_activation(){
	/* Initialize the PPR ootions */
	ppr_options_init();
}
register_activation_hook(__FILE__, 'ppr_activation');

?>
