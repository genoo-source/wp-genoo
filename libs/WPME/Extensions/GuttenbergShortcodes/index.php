<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.
 * (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General Public License Ver. 2 (GPL)
 *  Licensed "As-Is"; all warranties are disclaimed.
 *  HTML: http://www.gnu.org/copyleft/gpl.html
 *  Text: http://www.gnu.org/copyleft/gpl.txt
 *
 * Proprietary Licensing:
 *  Remaining code elements, including without limitation:
 *  images, cascading style sheets, and JavaScript elements
 *  are licensed under restricted license.
 *  http://www.wpmktgengine.com/terms-of-service
 *  Copyright 2016 Genoo LLC. All rights reserved worldwide.
 */

$blocks = array('cta', 'form', 'survey');
if (
    defined('WPMKTENGINE_LUMENS') && WPMKTENGINE_LUMENS == true ||
    defined('GENOO_LUMENS') && GENOO_LUMENS == true
) {
    $blocks[] = 'lumens';
}

foreach($blocks as $block){
    // Register CTA
    require_once $block . DIRECTORY_SEPARATOR . 'index.php';
}
