=== Plugin Name ===
Contributors: Passlogy
Donate link: 
Tags: login, security
Requires at least: 3.0.1
Tested up to: 3.8.1
Stable tag: 0.1.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin confirms to Passlogy Password Reminder whether you may login, when you login to wordpress.


== Description ==
It is not necessary to memorize the difficult password by using Passlogy Password Reminder.<br>
Passlogy Password Reminder display your password into a random matrix table,<br>
you can read your password by "pattern", it was set before.<br>
It is noteworthy, Passlogy Password Reminder server has not stored your password and setting.<br>
Those are stored in "localStorage", it is dependent on a browser or device.

<em>NOTE:</em><br>
Currently, this plugin is a trial version, because Passlogy Password Reminder server is still developing.<br>
We will release a product version when Passlogy Password Reminder service has started.

= If this plugin has activated, =
* It recives a icon and link from Passlogy Password Reminder server, and display the icon and link on wordpress login window.
* It sends username and URL of wordpress to Passlogy Password Reminder server when you login to wordpress, the server returns a result that the password was displayed or not.
* It does not send password to Passlogy Password Reminder server, because the server does not have authentication service.

= How to sign up to Passlogy Password Reminder =
1. Please visit to <a href="https://leafintheforest.com/signup.php" target="_blank">Passlogy Password Reminder signup page</a>.
2. Input your email to the text box, and click the next button of orange color.
3. Access to the URL that it was sent to your email. Because the URL is your "login URL", don't lose it.
4. Access your "login URL" again to use Passlogy Password Reminder. Or click the "Service start" button.

<em>NOTE:</em><br>
By clicking the button of orange color, you agree to the terms.

= How to use Passlogy Password Reminder =
1. Access your "login URL" to login Passlogy Password Reminder.
2. Click the "Register pattern" link for setting the "pattern", it is used for reading your password from a random matrix table.
3. Click the "Register password" link for register a password.
4. Input each item. And click the "Send" button, and "Save" button.
5. Click the setting, your password is displayed into a random matrix table.

Refer to "<a href="http://www.leafintheforest.com/?page_id=100" target="_blank">How to setting Passlogy Password Reminder</a>" for details.

<em>NOTE:</em><br>
If you will use the password setting with plugin, it is necessary to input all items in editing. 

= How to login to wordpress when this plugin has activated =
1. Please display your password on the random matrix table of Passlogy Password Reminder.
2. Input your username/password to wordpress login window.
3. Click the "Log in" button.

Refer to "<a href="http://www.leafintheforest.com/?page_id=98" target="_blank">How to use the plugin</a>" for details.

<em>NOTE:</em><br>
If you did not displayed your password on the random matrix table of Passlogy Password Reminder, login is not successful.


== Installation ==
= Installation =
1. Unzip the downloaded Passlogy password reminder plugin zip file.
2. Upload `passlogy-pwd-reminder` directory and its contents to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

Refer to "<a href="http://www.leafintheforest.com/?page_id=92" target="_blank">How to install the plugin</a>" for details.

= How to setting the plugin =
1. Please login to wordpress by an administrator.
2. Go to the "Settings" section and click the "PPR Options" link.
3. Input each item as below.<br>
PPR Server URL: <em>https://leafintheforest.com/</em><br>
PPR Server API URI: <em>https://leafintheforest.com:8088/api?mode=auth</em>
4. Click the "Save Changes" button to save those settings.

Refer to "<a href="http://www.leafintheforest.com/?page_id=96" target="_blank">How to setting the plugin</a>" for details.


== Frequently Asked Questions ==
= Although I entered the correct password, I can not login to wordpress, after activated this plugin. =
Please retry when the matrix with your password was displayed by Passlogy Password Reminder.


== Screenshots ==
1. This screen shot is the plugin Options setting panel.
2. This screen shot is login window, when the plugin was activated.
3. The login password was displayed on Passlogy Password Reminder.
4. The login error when the password is not displayed on Passlogy Password Reminder.
5. The login error, the username is not registered in wordpress and Passlogy Password Reminder.
6. If administrators login failed, the login error when the password is not displayed on Passlogy Password Reminder.
7. The login window for login without Passlogy Password Reminder temporarily.


== Changelog ==
= 0.1.7 =
* The version for trial.
* Added the curl option "CURLOPT_SSLVERSION" for SSLv3.
= 0.1.6 =
* The version for trial.
* Changed URL to "leafintheforest.com".
= 0.1.5 =
* The version for trial.
* Added an error message when Passlogy Password Reminder server connection has failed.
* Replaced the function call "file_get_contents()" to "wp_remote_get()".
* Changed Passlogy Password Reminder server default URL options to "leafintheforest.com".
* Supported SSL.
* Updated screenshot-1.
= 0.1.4 =
* The version for trial.
* Added the temporarily login function for administrator. If Passlogy Password Reminder server has downed, the administrators can login by a "Login Code" that was sent to your email.
* Added two screenshorts. (screenshot-6 and screenshot-7)
* Improved the password generation API of Passlogy Password Reminder server.
* Update language file.
= 0.1.3 =
* The version for trial.
* Readme and screenshorts were updated.
= 0.1.2 =
* The first version for trial.

== Upgrade Notice ==
none.

