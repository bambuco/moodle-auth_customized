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
 * Admin settings and defaults.
 *
 * @package    auth_customized
 * @copyright  2023 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_customized/pluginname', '',
        new lang_string('description', 'auth_customized')));

    $options = [
        new lang_string('no'),
        new lang_string('yes'),
    ];

    $settings->add(new admin_setting_configselect('auth_customized/recaptcha',
        new lang_string('recaptcha_key', 'auth_customized'),
        new lang_string('recaptcha', 'auth_customized'), 0, $options));

    $settings->add(new admin_setting_configselect('auth_customized/usernameisemail',
        new lang_string('usernameisemail', 'auth_customized'),
        new lang_string('usernameisemail_help', 'auth_customized'), 0, $options));

    $settings->add(new admin_setting_configselect('auth_customized/confirmpassword',
        new lang_string('confirmpassword', 'auth_customized'),
        new lang_string('confirmpassword_help', 'auth_customized'), 0, $options));

    $settings->add(new admin_setting_configselect('auth_customized/requirecountryandcity',
        new lang_string('requirecountryandcity', 'auth_customized'),
        new lang_string('requirecountryandcity_help', 'auth_customized'), 0, $options));

    $settings->add(new admin_setting_configtext('auth_customized/fieldsorder',
        new lang_string('fieldsorder', 'auth_customized'),
        new lang_string('fieldsorder_help', 'auth_customized'), ''));

    // Forgot password options.
    $settings->add(new admin_setting_heading('auth_customized/settingsheaderforgotpassword',
        new lang_string('settingsheaderforgotpassword', 'auth_customized'), '', ''));

    $settings->add(new admin_setting_configselect('auth_customized/forgotpasswordbyusername',
        new lang_string('forgotpasswordbyusername', 'auth_customized'),
        new lang_string('forgotpasswordbyusername_help', 'auth_customized'), 0, $options));

    $settings->add(new admin_setting_configselect('auth_customized/forgotpasswordbyemail',
        new lang_string('forgotpasswordbyemail', 'auth_customized'),
        new lang_string('forgotpasswordbyemail_help', 'auth_customized'), 1, $options));

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('customized');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            get_string('auth_fieldlocks_help', 'auth'), false, false);
}
