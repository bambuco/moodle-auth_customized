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
 * Authentication Plugin.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/auth/email/auth.php');

/**
 * Customized authentication plugin.
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_customized extends auth_plugin_email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'customized';
        $this->config = get_config('auth_customized');
    }

    /**
     * Sign up a new user ready for confirmation.
     *
     * Password is passed in plaintext.
     * A custom confirmationurl could be used.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @param string $confirmationurl user confirmation URL
     * @return boolean true if everything well ok and $notify is set to true
     * @throws moodle_exception
     */
    public function user_signup_with_confirmation($user, $notify = true, $confirmationurl = null) {
        global $CFG;
        $config = get_config('auth_customized');

        if (!empty($config->usernameisemail)) {
            $user->username = strtolower($user->email);
        }

        if (!empty($config->requirecountryandcity)) {
            $user->country = !empty($CFG->country) ? $CFG->country : '';
            $user->city = '';
        }

        return parent::user_signup_with_confirmation($user, $notify, $confirmationurl);

    }

    /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     * @return moodle_form A form which edits a record from the user table.
     */
    function signup_form() {
        return new \auth_customized\forms\signup(null, null, 'post', '', ['autocomplete'=>'on']);
    }

}
