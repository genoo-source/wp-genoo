<?php
/*
    Plugin Name: Genoo
    Description: Genoo, LLC
    Author:  Genoo, LLC
    Author URI: http://www.genoo.com/
    Author Email: info@genoo.com
    Version: 5.7.5
    License: GPLv2
    Text Domain: genoo
*/
/*
    Copyright 2014  Genoo, LLC  (web : http://www.genoo.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * 1. If no Wordpress, go home
 */

if (!defined('ABSPATH')) { exit; }

/**
 * 2. Check minimum requirements (wp version, php version)
 * Reason behind this is, we just need PHP 5.3.1 at least,
 * and WordPress 3.3 or higher. We just can't run the show
 * on some outdated installation.
 */

require_once('GenooCheck.php');
GenooCheck::checkRequirements();

/**
 * 3. Activation / deactivation
 */

register_activation_hook(__FILE__, array('Genoo', 'activate'));

/**
 * 4. Go, and do Genoo!
 */

require_once('GenooInit.php');
