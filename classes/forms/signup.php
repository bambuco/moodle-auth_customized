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
 * User sign-up form.
 *
 * @package    auth_customized
 * @copyright  2023 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_customized\forms;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');

use moodleform;
use renderable;
use templatable;

/**
 * Signup form implementation.
 *
 * @package    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup extends moodleform implements renderable, templatable {

    function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;

        $config = get_config('auth_customized');

        if (empty($config->usernameisemail)) {
            $availablefields = ['username', 'password', 'email', 'requirednames', 'city', 'country'];
        } else {
            $availablefields = ['email', 'password', 'requirednames', 'city', 'country'];
        }

        $fieldsorder = explode(',', $config->fieldsorder);
        $fieldsorder = array_map('trim', $fieldsorder);
        $fieldsorder = array_filter($fieldsorder, function ($field) use ($availablefields) {
            return in_array($field, $availablefields);
        });

        if (empty($fieldsorder)) {
            $fieldsorder = $availablefields;
        } else {
            // Add fields that are not in the order.
            foreach ($availablefields as $field) {
                if (!in_array($field, $fieldsorder)) {
                    $fieldsorder[] = $field;
                }
            }
        }

        foreach ($fieldsorder as $currentfield) {

            if ($currentfield == 'email') {

                $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
                $mform->setType('email', \core_user::get_property_type('email'));
                $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
                $mform->setForceLtr('email');

                $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
                $mform->setType('email2', \core_user::get_property_type('email'));
                $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
                $mform->setForceLtr('email2');

            } else if ($currentfield == 'username') {

                if (empty($config->usernameisemail)) {
                    $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
                    $mform->setType('username', PARAM_RAW);
                    $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');
                }
            } else if ($currentfield == 'password') {

                if (!empty($CFG->passwordpolicy)){
                    $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
                }

                $mform->addElement('password', 'password', get_string('password'), [
                    'maxlength' => 32,
                    'size' => 12,
                    'autocomplete' => 'new-password'
                ]);
                $mform->setType('password', \core_user::get_property_type('password'));
                $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');

                if ($config->confirmpassword) {
                    $mform->addElement('password', 'password2', get_string('passwordagain', 'auth_customized'), [
                        'maxlength' => 32,
                        'size' => 12,
                        'autocomplete' => 'new-password2'
                    ]);
                    $mform->setType('password', \core_user::get_property_type('password'));
                    $mform->addRule('password2', get_string('missingpassword'), 'required', null, 'client');

                    $PAGE->requires->js_call_amd('auth_customized/validations', 'passwordAgain');

                }
            } else if ($currentfield == 'requirednames') {

                $namefields = useredit_get_required_name_fields();
                foreach ($namefields as $field) {
                    $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
                    $mform->setType($field, \core_user::get_property_type('firstname'));
                    $stringid = 'missing' . $field;
                    if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                        $stringid = 'required';
                    }
                    $mform->addRule($field, get_string($stringid), 'required', null, 'client');
                }
            } else if ($currentfield == 'city') {
                if (!empty($config->requirecountryandcity)) {
                    $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
                    $mform->setType('city', \core_user::get_property_type('city'));
                    if (!empty($CFG->defaultcity)) {
                        $mform->setDefault('city', $CFG->defaultcity);
                    }
                }
            } else if ($currentfield == 'requirednames') {
                if (!empty($config->requirecountryandcity)) {
                    $country = get_string_manager()->get_list_of_countries();
                    $default_country[''] = get_string('selectacountry');
                    $country = array_merge($default_country, $country);
                    $mform->addElement('select', 'country', get_string('country'), $country);

                    if(!empty($CFG->country)){
                        $mform->setDefault('country', $CFG->country);
                    }else{
                        $mform->setDefault('country', '');
                    }
                }
            }
        }

        profile_signup_fields($mform);

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        // Hook for plugins to extend form definition.
        core_login_extend_signup_form($mform);

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        $manager = new \core_privacy\local\sitepolicy\manager();
        $manager->signup_form($mform);

        // buttons
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    /**
     * Perform extra validation before data is processed.
     *
     */
    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $config = get_config('auth_customized');

        if (!empty($config->usernameisemail)) {
            $data['username'] = $data['email'];
        }

        $errors = parent::validation($data, $files);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_signup_form($data));

        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        $errors += signup_validate_data($data, $files);

        if (!empty($config->confirmpassword)) {
            if ($data['password'] !== $data['password2']) {
                $errors['password2'] = get_string('passwordsdiffer', 'auth_customized');
            }
        }

        if (!empty($config->usernameisemail)) {
            if (isset($errors['username']) && !isset($errors['email'])) {
                $errors['email'] = $errors['username'];
            }
        }

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }
}
