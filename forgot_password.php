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
 * Forgot password routine.
 *
 * Finds the user and calls the appropriate routine for their authentication type.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/login/lib.php');

$token = optional_param('token', false, PARAM_ALPHANUM);

$PAGE->set_url('/auth/customized/forgot_password.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

// Setup text strings.
$strforgotten = get_string('passwordforgotten');

$PAGE->set_pagelayout('login');
$PAGE->set_title($strforgotten);
$PAGE->set_heading($COURSE->fullname);

// If alternatepasswordurl is not defined, then we'll just head to the original forgot_pasword page.
// This is the default behavior for the standard Moodle login page.
// Only use this page as the password reset page if is defined in the alternatepasswordurl setting.
if (empty($CFG->forgottenpasswordurl) || $CFG->forgottenpasswordurl !== $CFG->wwwroot . '/auth/customized/forgot_password.php') {
    redirect($CFG->wwwroot . '/login/forgot_password.php');
}

// If you are logged in then you shouldn't be here!
if (isloggedin() and !isguestuser()) {
    redirect($CFG->wwwroot.'/index.php', get_string('loginalready'), 5);
}

// Fetch the token from the session, if present, and unset the session var immediately.
$tokeninsession = false;
if (!empty($SESSION->password_reset_token)) {
    $token = $SESSION->password_reset_token;
    unset($SESSION->password_reset_token);
    $tokeninsession = true;
}

if (empty($token)) {
    // This is a new password reset request.
    // Process the request; identify the user & send confirmation email.
    $mform = new \auth_customized\forms\forgot_password();

    if ($mform->is_cancelled()) {
        redirect(get_login_url());

    } else if ($data = $mform->get_data()) {

        $username = $email = '';
        if (!empty($data->username)) {
            $username = $data->username;
        } else {
            $email = $data->email;
        }
        list($status, $notice, $url) = \auth_customized\controller::process_password_reset($username, $email);

        // Plugins can perform post forgot password actions once data has been validated.
        core_login_post_forgot_password_requests($data);

        // Any email has now been sent.
        // Next display results to requesting user if settings permit.
        echo $OUTPUT->header();
        notice($notice, $url);
        die; // Never reached.
    }

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('passwordforgotteninstructions', 'auth_customized'), 'generalbox boxwidthnormal boxaligncenter');
    $mform->display();

    echo $OUTPUT->footer();

} else {
    // A token has been found, but not in the session, and not from a form post.
    // This must be the user following the original rest link, so store the reset token in the session and redirect to self.
    // The session var is intentionally used only during the lifespan of one request (the redirect) and is unset above.
    if (!$tokeninsession && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $SESSION->password_reset_token = $token;
        redirect($CFG->wwwroot . '/auth/customized/forgot_password.php');
    } else {
        // Continue with the password reset process.
        \auth_customized\controller::process_password_set($token);
    }
}
