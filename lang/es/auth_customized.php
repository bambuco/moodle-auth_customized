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

$string['pluginname'] = 'Auto-registro personalizado';
$string['privacy:metadata'] = 'El plugin de auto-registro personalizado no almacena datos personales.';

$string['confirmpassword'] = 'Confirmar contraseña';
$string['confirmpassword_help'] = 'Si se habilita, se solicitará al usuario que confirme su contraseña en el formulario de registro.';
$string['description'] = '<p>El auto-registro personalizado permite al usuario crear su propia cuenta mediante el botón \'Crear nueva cuenta\' en la página de inicio de sesión. El usuario recibe a continuación un correo electrónico con un enlace seguro a una página donde puede confirmar su cuenta. En futuros accesos, solamente se compara el usuario y contraseña respecto a los almacenados en la base de datos de Moodle.</p><p>Nota: Además de activar el plugin, para el auto-registro basado en correo electrónico también se debe seleccionar este método en el menú desplegable de la página \'Administrar autentificación\' </p>';
$string['fieldsorder'] = 'Orden de los campos';
$string['fieldsorder_help'] = 'Un orden personalizado para los campos en el formulario de registro. Use los nombres de los campos separados por comas.<br />
Si no se especifica nungun orden, se mostrarán los campos en el orden por defecto.<br />
Los campos disponibles y en el orden por defecto, son: username, password, email, requirednames, city, country.';
$string['noemail'] = 'Se ha intentado enviarle un correo electrónico sin éxito.';
$string['passwordagain'] = 'Contraseña (de nuevo)';
$string['passwordsdiffer'] = 'La confirmación de la contraseña no coincide con la contraseña que ha introducido.';
$string['recaptcha'] = 'Agrega elemento de formulario de confirmación visual o auditiva a la página de acceso para los usuarios auto-registrados vía email. Esta opción protege su sitio contra los creadores de spam y contribuye a una buena causa. Para más detalles, visite http://www.google.com/recaptcha.';
$string['recaptcha_key'] = 'Habilitar elemento reCAPTCHA';
$string['requirecountryandcity'] = 'Requerir país y ciudad';
$string['requirecountryandcity_help'] = 'Si se habilita, se solicitará al usuario que introduzca su país y ciudad en el formulario de registro.
Si se deshabilita, el campo de país se establece en el país por defecto.';
$string['settings'] = 'Ajustes';
$string['usernameisemail'] = 'Usar correo electrónico como nombre de usuario';
$string['usernameisemail_help'] = 'Si se habilita, el nombre de usuario será el mismo que la dirección de correo electrónico.
Si se deshabilita, se solicitará al usuario que introduzca su nombre de usuario en el formulario de registro.';
