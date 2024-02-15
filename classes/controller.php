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
 * Auth customized external API
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_customized;
require_once($CFG->dirroot . '/login/set_password_form.php');

/**
 * Component controller.
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {

    /**
     * Process the password reset for the given user (via username or email).
     *
     * @param  string $username the user name
     * @param  string $email    the user email
     * @return array an array containing fields indicating the reset status, a info notice and redirect URL.
     * @since  Moodle 3.4
     */
    public static function process_password_reset($username, $email) {
        global $CFG, $DB;

        if (empty($username) && empty($email)) {
            throw new \moodle_exception('cannotmailconfirm');
        }

        // Next find the user account in the database which the requesting user claims to own.
        if (!empty($username)) {
            // Username has been specified - load the user record based on that.
            $username = \core_text::strtolower($username); // Mimic the login page process.
            $userparams = ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0, 'suspended' => 0];
            $user = $DB->get_record('user', $userparams);
        } else {
            // Try to load the user record based on email address.
            // This is tricky because:
            // 1/ the email is not guaranteed to be unique - TODO: send email with all usernames to select the account for pw reset
            // 2/ mailbox may be case sensitive, the email domain is case insensitive - let's pretend it is all case-insensitive.
            //
            // The case-insensitive + accent-sensitive search may be expensive as some DBs such as MySQL cannot use the
            // index in that case. For that reason, we first perform accent-insensitive search in a subselect for potential
            // candidates (which can use the index) and only then perform the additional accent-sensitive search on this
            // limited set of records in the outer select.
            $sql = "SELECT *
                    FROM {user}
                    WHERE " . $DB->sql_equal('email', ':email1', false, true) . "
                    AND id IN (SELECT id
                                    FROM {user}
                                WHERE mnethostid = :mnethostid
                                    AND deleted = 0
                                    AND suspended = 0
                                    AND " . $DB->sql_equal('email', ':email2', false, false) . ")";

            $params = [
                'email1' => $email,
                'email2' => $email,
                'mnethostid' => $CFG->mnet_localhost_id,
            ];

            $user = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
        }

        // Target user details have now been identified, or we know that there is no such account.
        // Send email address to account's email address if appropriate.
        $pwresetstatus = PWRESET_STATUS_NOEMAILSENT;
        if ($user and !empty($user->confirmed)) {
            $systemcontext = \context_system::instance();

            $userauth = get_auth_plugin($user->auth);
            if (!$userauth->can_reset_password() || !is_enabled_auth($user->auth)
            or !has_capability('moodle/user:changeownpassword', $systemcontext, $user->id)) {
                if (send_password_change_info($user)) {
                    $pwresetstatus = PWRESET_STATUS_OTHEREMAILSENT;
                } else {
                    throw new \moodle_exception('cannotmailconfirm');
                }
            } else {
                // The account the requesting user claims to be is entitled to change their password.
                // Next, check if they have an existing password reset in progress.
                $resetinprogress = $DB->get_record('user_password_resets', ['userid' => $user->id]);
                if (empty($resetinprogress)) {
                    // Completely new reset request - common case.
                    $resetrecord = core_login_generate_password_reset($user);
                    $sendemail = true;
                } else if ($resetinprogress->timerequested < (time() - $CFG->pwresettime)) {
                    // Preexisting, but expired request - delete old record & create new one.
                    // Uncommon case - expired requests are cleaned up by cron.
                    $DB->delete_records('user_password_resets', ['id' => $resetinprogress->id]);
                    $resetrecord = core_login_generate_password_reset($user);
                    $sendemail = true;
                } else if (empty($resetinprogress->timererequested)) {
                    // Preexisting, valid request. This is the first time user has re-requested the reset.
                    // Re-sending the same email once can actually help in certain circumstances
                    // eg by reducing the delay caused by greylisting.
                    $resetinprogress->timererequested = time();
                    $DB->update_record('user_password_resets', $resetinprogress);
                    $resetrecord = $resetinprogress;
                    $sendemail = true;
                } else {
                    // Preexisting, valid request. User has already re-requested email.
                    $pwresetstatus = PWRESET_STATUS_ALREADYSENT;
                    $sendemail = false;
                }

                if ($sendemail) {
                    $sendresult = self::send_password_change_confirmation_email($user, $resetrecord);
                    if ($sendresult) {
                        $pwresetstatus = PWRESET_STATUS_TOKENSENT;
                    } else {
                        throw new \moodle_exception('cannotmailconfirm');
                    }
                }
            }
        }

        $url = $CFG->wwwroot . '/index.php';
        if (!empty($CFG->protectusernames)) {
            // Neither confirm, nor deny existance of any username or email address in database.
            // Print general (non-commital) message.
            $status = 'emailpasswordconfirmmaybesent';
            $notice = get_string($status, 'auth_customized');
        } else if (empty($user)) {
            // Protect usernames is off, and we couldn't find the user with details specified.
            // Print failure advice.
            $status = 'emailpasswordconfirmnotsent';
            $notice = get_string($status);
            $url = $CFG->wwwroot.'/forgot_password.php';
        } else if (empty($user->email)) {
            // User doesn't have an email set - can't send a password change confimation email.
            $status = 'emailpasswordconfirmnoemail';
            $notice = get_string($status);
        } else if ($pwresetstatus == PWRESET_STATUS_ALREADYSENT) {
            // User found, protectusernames is off, but user has already (re) requested a reset.
            // Don't send a 3rd reset email.
            $status = 'emailalreadysent';
            $notice = get_string($status);
        } else if ($pwresetstatus == PWRESET_STATUS_NOEMAILSENT) {
            // User found, protectusernames is off, but user is not confirmed.
            // Pretend we sent them an email.
            // This is a big usability problem - need to tell users why we didn't send them an email.
            // Obfuscate email address to protect privacy.
            $protectedemail = preg_replace('/([^@]*)@(.*)/', '******@$2', $user->email);
            $status = 'emailpasswordconfirmsent';
            $notice = get_string($status, '', $protectedemail);
        } else {
            // Confirm email sent. (Obfuscate email address to protect privacy).
            $protectedemail = preg_replace('/([^@]*)@(.*)/', '******@$2', $user->email);
            // This is a small usability problem - may be obfuscating the email address which the user has just supplied.
            $status = 'emailresetconfirmsent';
            $notice = get_string($status, '', $protectedemail);
        }
        return [$status, $notice, $url];
    }

    /**
     * This function processes a user's submitted token to validate the request to set a new password.
     * If the user's token is validated, they are prompted to set a new password.
     *
     * Based in core_login_process_password_set function (login/lib.php).
     *
     * @param string $token the one-use identifier which should verify the password reset request as being valid.
     * @return void
     */
    public static function process_password_set($token) {
        global $DB, $CFG, $OUTPUT, $SESSION;
        require_once($CFG->dirroot.'/user/lib.php');

        $pwresettime = isset($CFG->pwresettime) ? $CFG->pwresettime : 1800;
        $sql = "SELECT u.*, upr.token, upr.timerequested, upr.id as tokenid
                FROM {user} u
                JOIN {user_password_resets} upr ON upr.userid = u.id
                WHERE upr.token = ?";
        $user = $DB->get_record_sql($sql, [$token]);

        $forgotpasswordurl = "{$CFG->wwwroot}/auth/customized/forgot_password.php";
        if (empty($user) or ($user->timerequested < (time() - $pwresettime - DAYSECS))) {
            // There is no valid reset request record - not even a recently expired one.
            // (suspicious)
            // Direct the user to the forgot password page to request a password reset.
            echo $OUTPUT->header();
            notice(get_string('noresetrecord'), $forgotpasswordurl);
            die; // Never reached.
        }
        if ($user->timerequested < (time() - $pwresettime)) {
            // There is a reset record, but it's expired.
            // Direct the user to the forgot password page to request a password reset.
            $pwresetmins = floor($pwresettime / MINSECS);
            echo $OUTPUT->header();
            notice(get_string('resetrecordexpired', '', $pwresetmins), $forgotpasswordurl);
            die; // Never reached.
        }

        if ($user->auth === 'nologin' or !is_enabled_auth($user->auth)) {
            // Bad luck - user is not able to login, do not let them set password.
            echo $OUTPUT->header();
            throw new \moodle_exception('forgotteninvalidurl');
            die; // Never reached.
        }

        // Check this isn't guest user.
        if (isguestuser($user)) {
            throw new \moodle_exception('cannotresetguestpwd');
        }

        // Token is correct, and unexpired.
        $mform = new \login_set_password_form(null, $user);
        $data = $mform->get_data();
        if (empty($data)) {
            // User hasn't submitted form, they got here directly from email link.
            // Next, display the form.
            $setdata = new \stdClass();
            $setdata->username = $user->username;
            $setdata->username2 = $user->username;
            $setdata->token = $user->token;
            $mform->set_data($setdata);
            echo $OUTPUT->header();
            echo $OUTPUT->box(get_string('setpasswordinstructions'), 'generalbox boxwidthnormal boxaligncenter');
            $mform->display();
            echo $OUTPUT->footer();
            return;
        } else {
            // User has submitted form.
            // Delete this token so it can't be used again.
            $DB->delete_records('user_password_resets', ['id' => $user->tokenid]);
            $userauth = get_auth_plugin($user->auth);
            if (!$userauth->user_update_password($user, $data->password)) {
                throw new \moodle_exception('errorpasswordupdate', 'auth');
            }
            user_add_password_history($user->id, $data->password);
            if (!empty($CFG->passwordchangelogout)) {
                \core\session\manager::kill_user_sessions($user->id, session_id());
            }
            // Reset login lockout (if present) before a new password is set.
            login_unlock_account($user);
            // Clear any requirement to change passwords.
            unset_user_preference('auth_forcepasswordchange', $user);
            unset_user_preference('create_password', $user);

            if (!empty($user->lang)) {
                // Unset previous session language - use user preference instead.
                unset($SESSION->lang);
            }
            complete_user_login($user); // Triggers the login event.

            \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

            $urltogo = core_login_get_return_url();
            unset($SESSION->wantsurl);

            // Plugins can perform post set password actions once data has been validated.
            core_login_post_set_password_requests($data, $user);

            redirect($urltogo, get_string('passwordset'), 1);
        }
    }

    /**
     * Sends a password change confirmation email.
     *
     * @param stdClass $user A {@link $USER} object
     * @param stdClass $resetrecord An object tracking metadata regarding password reset request
     * @return bool Returns true if mail was sent OK and false if there was an error.
     */
    public static function send_password_change_confirmation_email($user, $resetrecord) {
        global $CFG;

        if (empty($user->email)) {
            return false;
        }

        $site = get_site();
        $supportuser = \core_user::get_support_user();
        $pwresetmins = isset($CFG->pwresettime) ? floor($CFG->pwresettime / MINSECS) : 30;

        $data = new \stdClass();
        $data->firstname = $user->firstname;
        $data->lastname  = $user->lastname;
        $data->username  = $user->username;
        $data->sitename  = format_string($site->fullname);
        $data->link      = $CFG->wwwroot . '/auth/customized/forgot_password.php?token='. $resetrecord->token;
        $data->admin     = generate_email_signoff();
        $data->resetminutes = $pwresetmins;

        $message = get_string('emailresetconfirmation', '', $data);
        $subject = get_string('emailresetconfirmationsubject', '', format_string($site->fullname));

        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return email_to_user($user, $supportuser, $subject, $message);

    }

}
