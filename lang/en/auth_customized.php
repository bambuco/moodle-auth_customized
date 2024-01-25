<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_customized', language 'en'.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Customized self-registration';
$string['privacy:metadata'] = 'The Customized self-registration authentication plugin does not store any personal data.';

$string['confirmpassword'] = 'Confirm password';
$string['confirmpassword_help'] = 'If enabled, the user will be required to confirm their password in the signup form.';
$string['description'] = '<p>Customized self-registration enables a user to create their own account via a \'Create new account\' button on the login page. The user then receives an email containing a secure link to a page where they can confirm their account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>Note: In addition to enabling the plugin, customized-based self-registration must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['fieldsorder'] = 'Fields order';
$string['fieldsorder_help'] = 'A custom fields order. Use the fields name separated by commas. Example: firstname,lastname,email.<br />
If empty, the fields will be displayed in the default order.<br />
Available fields and default order: username, password, email, requirednames, city, country.';
$string['noemail'] = 'Tried to send you an email but failed!';
$string['passwordagain'] = 'Password (again)';
$string['passwordsdiffer'] = 'The password confirmation does not match the password you entered.';
$string['recaptcha'] = 'Adds a visual/audio confirmation form element to the sign-up page for customized self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See https://www.google.com/recaptcha for more details.';
$string['recaptcha_key'] = 'Enable reCAPTCHA element';
$string['requirecountryandcity'] = 'Require country and city';
$string['requirecountryandcity_help'] = 'If enabled, the user will be required to enter their country and city in the signup form.
If disabled, the country field is set to the default country.';
$string['settings'] = 'Settings';
$string['usernameisemail'] = 'Use email as username';
$string['usernameisemail_help'] = 'If enabled, the username will be the same as the email address.
If disabled, the username will be required in the signup form.';
