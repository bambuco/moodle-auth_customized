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
 * Forgot password form.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_customized\forms;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/login/lib.php');

use moodleform;

/**
 * Forgot password form implementation.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forgot_password extends moodleform {

    /**
     * Define the forgot password form.
     */
    function definition() {
        global $USER;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $config = get_config('auth_customized');

        // Hook for plugins to extend form definition.
        core_login_extend_forgot_password_form($mform);

        if (!$config->usernameisemail) {
            $mform->addElement('header', 'searchbyusername', get_string('searchbyusername'), '');

            $purpose = user_edit_map_field_purpose($USER->id, 'username');
            $mform->addElement('text', 'username', get_string('username'), 'size="20"' . $purpose);
            $mform->setType('username', PARAM_RAW);

            $submitlabel = get_string('search');
            $mform->addElement('submit', 'submitbuttonusername', $submitlabel);
        }

        $mform->addElement('header', 'searchbyemail', get_string('searchbyemail'), '');

        $purpose = user_edit_map_field_purpose($USER->id, 'email');
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"' . $purpose);
        $mform->setType('email', PARAM_RAW_TRIMMED);

        $submitlabel = get_string('search');
        $mform->addElement('submit', 'submitbuttonemail', $submitlabel);
    }

    /**
     * Validate user input from the forgot password form.
     * @param array $data array of submitted form fields.
     * @param array $files submitted with the form.
     * @return array errors occuring during validation.
     */
    function validation($data, $files) {

        $errors = parent::validation($data, $files);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_forgot_password_form($data));

        $errors += core_login_validate_forgot_password_data($data);

        return $errors;
    }

}
