// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * To component validation.
 *
 * @@module    auth_customized
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Log from 'core/log';
import {get_strings as getStrings} from 'core/str';

// Global variables.

// Load strings.
var strings = [
    {key: 'passwordsdiffer', component: 'auth_customized'},
];
var s = [];

/**
 * Load strings from server.
 */
function loadStrings() {

    strings.forEach(one => {
        s[one.key] = one.key;
    });

    getStrings(strings).then(function(results) {
        var pos = 0;
        strings.forEach(one => {
            s[one.key] = results[pos];
            pos++;
        });
        return true;
    }).fail(function(e) {
        Log.debug('Error loading strings');
        Log.debug(e);
    });
}

// End of Load strings.

/**
 * Initialize the passwords validation.
 *
 */
export const passwordAgain = async () => {

    loadStrings();

    var $pwd1 = $('#id_password');
    var $pwd2 = $('#id_password2');
    var $error = $('#id_error_password2');

    if ($pwd1.length < 1 || $pwd2.length < 1) {
        return;
    }

    var validatePasswordsMatch = function() {

        if ($pwd1.val() == '' || $pwd2.val() == '') {
            return;
        }

        if ($pwd1.val() !== $pwd2.val()) {
            $error.html(s.passwordsdiffer);
            $error.show();
        } else {
            $error.hide();
            $pwd2.find('#id_error_password2').html('');
        }
    };

    $pwd1.on('input', function() {
        validatePasswordsMatch();
    });

    $pwd2.on('input', function() {
        validatePasswordsMatch();
    });

};
