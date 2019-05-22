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


class TinyMCE
{
    /**
     * Register, extends TinyMCE
     */

    public static function register($postTypes = array())
    {
        global $typenow, $pagenow, $wp_screen;
        if(empty($typenow) && !empty($_GET['post'])){
            $post = get_post( $_GET['post'] );
            $typenow = $post->post_type;
        }

        // Cta?
        $cta = false;
        if(is_array($postTypes) && !empty($typenow)){
            $cta = in_array($typenow, $postTypes);
        }

        // Register external plugins
        Filter::add('mce_external_plugins', function($plugin_array) use($cta){
            // Form
            $plugin_array['WPMKTENGINEForm'] = WPMKTENGINE_ASSETS . 'GenooTinyMCEForm.js?v=' . WPMKTENGINE_REFRESH;
            // CTA
            if($cta) $plugin_array['WPMKTENGINECTA'] = WPMKTENGINE_ASSETS . 'GenooTinyMCECTA.js?v=' . WPMKTENGINE_REFRESH;
            // Surveys
//            $plugin_array['WPMKTENGINESurvey'] = WPMKTENGINE_ASSETS . 'GenooTinyMCESurvey.js?v=' . WPMKTENGINE_REFRESH;
            // Lumens
            if(WPMKTENGINE_LUMENS) $plugin_array['genooLumens'] = WPMKTENGINE_ASSETS . 'GenooTinyMCELumens.js?v=' . WPMKTENGINE_REFRESH;
            return $plugin_array;
        }, 10, 1);

        // Register external buttons
        Filter::add('mce_buttons', function($buttons) use($cta){
            // Form
            $buttons[] = 'WPMKTENGINEForm';
            // CTA
            if($cta) $buttons[] = 'WPMKTENGINECTA';
            // Surveys
//            $buttons[] = 'WPMKTENGINESurvey';
            // Lumens
            if(WPMKTENGINE_LUMENS) $buttons[] = 'genooLumens';
            return $buttons;
        }, 10, 1);

        // Add editor style
        add_editor_style(WPMKTENGINE_ASSETS . 'GenooEditor.css?v=' . WPMKTENGINE_REFRESH);
    }
}