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

/**
 * Class Helpscreen
 * @package WPMKTENGINE\Wordpress
 */
class Helpscreen
{

    /**
     * Register Helpscreen action
     */
    public static function register()
    {
        Action::add('current_screen', function($screen){
            // Pages that will help help tab
            $pages = array(
                'toplevel_page_WPMKTENGINE',
                'wpmktgengine_page_WPMKTENGINELogin',
                'wpmktgengine_page_WPMKTENGINETools',
                'wpmktgengine_page_WPMKTENGINEForms',
                'cta',
                'edit-cta'
            );
            // Current screen add help
            $screen->add_help_tab( array(
                'id'	=> 'wpmktgengine_help',
                'title'	=> __('Help &amp; Support'),
                'content'	=> \WPMKTENGINE\Wordpress\Helpscreen::getSupportText(),
            ) );
        }, 10, 1);
    }

    /**
     * @return string
     */
    public static function getSupportText()
    {
        return '<p>
                    If you need any help with WPMKTGENGINE please <a target="_blank" href="http://wpmktgengine.com/members/">visit our support forum.</a>
                </p>';
    }

    /**
     * @param $title
     * @return string
     */
    public static function getSupportHaderWithLogo($title = '')
    {
        return '<div class="WPMKTENGINElogo">
                    <img src="'. WPMKTENGINE_ASSETS .'/logo.png" />
                </div>
                <div class="WPMKTENGINESupport postbox genooPostbox">
                    <table class="form-table"><tbody><tr valign="top"><td>
                    '. self::getSupportText() .'
                    </td></tr></tbody></table>
                </div>
                <div class="clear"></div>
                <h2>' . $title . '</h2>';
    }
}