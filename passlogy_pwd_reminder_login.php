<?php

/**
 * This is the method that authenticate by PPR
 * 
 * @param user
 *				the user type
 * @param username
 *				the user name, it was input by user
 * @param password
 *				the password, it was input by user
 * @return authentication result
 */
function ppr_login_authenticate($user, $username, $password) {

	// login error occurred. and it is a invalid_username
	if (is_wp_error($user)) {
		if (!get_user_by('login', $username) && is_email($username)) {
			/* Authenticate by PPR server API */
			$result = ppr_login_authenticate_server_api($username, true);
			if (is_wp_error($result)) {
				// PPR Server failed
				$error = $user;
				$error->add('incorrect_password', __('Could not connect to Passlogy Password Reminder server.', PPR_TEXTDOMAIN));
			} else if ($result) {
				/* Authenticate success */
				if (!$password) {
					/* if password was not input, it generates a new password by PPR */
					$password = ppr_login_generate_password();
				}
				$user_id = wp_create_user($username, $password, $username);
				if (!$user_id || is_wp_error($user_id)) {
					/* email was registered. please confirm username */
					// TODO change the error display?
					$error = $user;
				} else {
					/* register success. send a email to user */
					wp_new_user_notification($user_id, $password);
					$error = new WP_User($user_id);
				}
			} else {
				/* Authenticate failed */
				$ppr_options = ppr_options_get();
				$error = new WP_Error(
									'incorrect_password',
									sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Signup to Passlogy Password Reminder" target="_blank">Signup to Passlogy Password Reminder</a>?', PPR_TEXTDOMAIN),
										$ppr_options[PPR_OPTIONS_SERVER_URL].'signup.php?email='.$username)
							);
			}
		} else {
			/* user is already registered or it is not a email, don't authenticate by PPR */
			$error = $user;
		}
	} else {

		if ($_POST['ppr_login_code']) {
			$result = ppr_compare_logincode($user->ID, $_POST['ppr_login_code']);
		} else {
			/* Authenticate by PPR server API */
			$result = ppr_login_authenticate_server_api($username, false);
		}

		if (is_wp_error($result)) {
			/* Authenticate failed */
			if ($user->caps['administrator'] == true) {
				// Create a login code for login temporarily, and save it to user meta
				ppr_create_logincode($user->ID);

				// Display the temporarily login link when an administrator login.
				$message = sprintf(
								__('<br><a href="%s" title="Login without Passlogy Password Reminder temporarily">Login without Passlogy Password Reminder temporarily</a> ?', PPR_TEXTDOMAIN),
								get_login_url('templogin', $username)
							);
			} else {
				$message = '';
			}
		
			$error = new WP_Error(
							'incorrect_password',
							__('Could not connect to Passlogy Password Reminder server.', PPR_TEXTDOMAIN).$message
						);
		} else if ($result) {
			/* Authenticate success */
			$error = $user;
		} else {
			/* Authenticate failed */
			if ($user->caps['administrator'] == true) {
				// Create a login code for login temporarily, and save it to user meta
				ppr_create_logincode($user->ID);

				// Display the temporarily login link when an administrator login.
				$message = sprintf(
								__('<br><a href="%s" title="Login without Passlogy Password Reminder temporarily">Login without Passlogy Password Reminder temporarily</a> ?', PPR_TEXTDOMAIN),
								get_login_url('templogin', $username)
							);
			} else {
				$message = '';
			}
			$error = new WP_Error(
							'incorrect_password',
							sprintf(__('<strong>ERROR</strong>: The password you entered for the username <strong>%s</strong> is not displayed on Passlogy Password Reminder. Display your password on Passlogy Password Reminder.', PPR_TEXTDOMAIN),
									$username
							).$message
						);
		}
	}

	return $error;
}

/**
 * This is the method that send an email with the Login Code to user
 * 
 * @param username
 *				the user name, it was input by user
 */

function ppr_send_login_code($username) {
	$user = get_user_by('login', $username);
	$args = get_user_meta($user->ID, "ppr_login_code", true);

	if (isset($args['email_sent']) && $args['email_sent'] == false) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		// Make the message
		$message  = __('A login code was sent for login without Passlogy Password Reminder temporarily.', PPR_TEXTDOMAIN)."\r\n\r\n";
		$message .= sprintf(__('Your temporarily login code is "%s".', PPR_TEXTDOMAIN), $args['login_code'])."\r\n";

		// Update the email_sent flag of user meta by email send result
		$args['email_sent'] = wp_mail(
									$user->user_email,
									sprintf(
										__('[%s] Your temporarily login code', PPR_TEXTDOMAIN),
										$blogname
									),
									$message
								);

		update_user_meta($user->ID, "ppr_login_code", $args);
	}
}


/**
 * This is the method that authenticate by PPR server API
 * 
 * @param username
 *				the user name, it was input by user
 * @param register
 *				is it register or not
 * @return authentication result
 *				WP_Error: server error
 *					true: success
 *				   false: failed
 */

function ppr_login_authenticate_server_api($username, $register=false) {
	$ppr_options = ppr_options_get();

	$result = ppr_login_authenticate_post(
					$ppr_options[PPR_OPTIONS_SERVER_API_URI],
					$username,
					$register?'':esc_url(home_url('/', is_ssl()?'https':'http'))
				);

	return $result;
}

/**
 * This is the method that hash the password
 * 
 * @param algo
 *				hash algorithm
 * @param password
 *				password
 * @param secret
 *				secret key
 * @return hashed password
 */
function ppr_login_pwd_hash($algo, $password, $secret=NULL) {

	switch ($algo) {
	case 'SHA256':
		if ($secret) {
			$pwd = hash_hmac('sha256', $password, $secret);
		} else {
			$pwd = hash('sha256', $password);
		}
		break;
	case 'SHA512':
		if ($secret) {
			$pwd = hash_hmac('sha512', $password, $secret);
		} else {
			$pwd = hash('sha512', $password);
		}
		break;
	case 'SHA1':
	default:
		if ($secret) {
			$pwd = hash_hmac('sha1', $password, $secret);
		} else {
			$pwd = hash('sha1', $password);
		}
		break;
	}

	return $pwd;
}

/**
 * This is the method that post the authenitication info to PPR server API
 * 
 * @param uri
 *				URL to PPR server API
 * @param username
 *				user name
 * @param url
 *				wordpress server URL
 * @return authentication result
 *				WP_Error: server error
 *					true: success
 *				   false: failed
 */
function ppr_login_authenticate_post($uri, $username, $url=NULL) {

	$response = wp_remote_post(
						$uri,
						array(
							'method'	=> 'POST',
							'sslverify'	=> false,
							'cookies'	=> array(),
							'headers'	=> array(),
							'body'		=> array(
								'uid'	=> $username,
								'url'	=> $url,
							)
						)
					);

	if (is_wp_error($response)) {
		/* Response failed */
//		$error_message = $response->get_error_message();
//		echo "Something went wrong: $error_message";
		return $response;
	} else if ($response['response']['code'] != '200') {
		return new WP_Error('http_request_failed');
	} else {
		/* Response success */
//		echo 'response:<pre>';
//		print_r($response);
//		echo '</pre>';
		return ppr_login_response_purse($response['body']);
	}
}

/**
 * Define for PPR server API response "code"
 */
define('PPR_SVRAPI_RESPCODE_SUCCESS',			'010');
define('PPR_SVRAPI_RESPCODE_NO_MATRIX',			'011');
define('PPR_SVRAPI_RESPCODE_AUTH_NG',			'012');
define('PPR_SVRAPI_RESPCODE_UNKNOW_ERROR',		'019');
/**
 * This is the method that purse the response from PPR server API
 * 
 * @param body
 *				response body data
 * @return authentication result
 *				 true: success
 *				false: failed
 */
function ppr_login_response_purse($body) {
	$enements = new SimpleXMLElement($body);

	switch ($enements->code) {
	case PPR_SVRAPI_RESPCODE_SUCCESS:
		$result = true;
		break;
	case PPR_SVRAPI_RESPCODE_NO_MATRIX:
	case PPR_SVRAPI_RESPCODE_AUTH_NG:
	case PPR_SVRAPI_RESPCODE_UNKNOW_ERROR:
	default:
		$result = false;
		break;
	}

	return $result;
}

/**
 * This is the method that generate a password by PPR server API
 * 
 * @return a password, it was generated by PPR
 */
function ppr_login_generate_password() {
	$ppr_options = ppr_options_get();
	$url = str_replace("?mode=auth", "?mode=genepass", $ppr_options[PPR_OPTIONS_SERVER_API_URI]);
	$args = array(
				'lc'	=> 1,
				'len'	=> 6,
			);

	$url = add_query_arg($args, $url);
	$response = wp_remote_get($url, array('sslverify' => false));

	if (!is_wp_error($response) && $response['response']['code'] == '200') {
		$enements = new SimpleXMLElement($response['body']);
		$password = pack("H*", $enements->data->password);
	}

	// If the PPR server can not generate a password, use WordPress function to generate it
	if (strlen($password) != 6) {
		$password = wp_generate_password(6, false);
	}

	return $password;
}

/**
 * This is the method that get the login URL with PPR mode
 * 
 * @param ppr_mode
 *				PPR mode
 * @param username
 *				user name
 * @return the login URL with PPR mode
 */
function get_login_url($ppr_mode = '', $username='') {
	$args = array(
				'pprmode' => $ppr_mode,
				'log' => $username,
			);

	$login_url = add_query_arg($args, wp_login_url());

	return $login_url;
}

/**
 * This is the method that create a logincode save it to user meta, and sent it to user by email
 * 
 * @param user_id
 *				user ID
 */
function ppr_create_logincode($user_id) {
	// Generate a password by PPR server
	$code = ppr_login_generate_password();

	$args = array(
				'email_sent' => false,
				'login_code' => $code,
			);

	// Save the logincode to user meta
	if (update_user_meta($user_id, "ppr_login_code", $args) != true) {
		add_user_meta($user_id, "ppr_login_code", $args);
	}
}

/**
 * This is the method that compare the logincode
 * 
 * @param user_id
 *				user ID
 * @param logincode
 *				login code
 * @return compare result
 */
function ppr_compare_logincode($user_id, $logincode) {
	$args = get_user_meta($user_id, "ppr_login_code", true);
	delete_user_meta($user_id, "ppr_login_code");

	return (strcmp($args['login_code'], $logincode) == 0);
}

?>
