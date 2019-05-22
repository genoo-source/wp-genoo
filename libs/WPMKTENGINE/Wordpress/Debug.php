<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
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

namespace WPMKTENGINE\Wordpress;

use WPMKTENGINE\Wordpress\Action;

class Debug
{
    /** debug key */
    const DEBUG_KEY = 'WPMKTENGINEDebugCheck';


    /**
     * Hooks check function
     */

    public function __construct(){ Action::add('shutdown', array(__CLASS__, 'checkFiredHooks')); }


    /**
     * Check fired hoooks
     */

    public static function checkFiredHooks()
    {
        // we only test front-end for these
        if(!is_admin()){
            $hooks['wp_footer'] = did_action('wp_footer') ? true : false;
            $hooks['wp_head'] = did_action('wp_head') ? true : false;
            update_option(self::DEBUG_KEY, $hooks);
        }
    }
}