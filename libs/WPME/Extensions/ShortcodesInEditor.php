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

namespace WPME\Extensions;

/**
 * Class ShortcodesInEditor
 *
 * @package WPME\Extensions
 */
class ShortcodesInEditor
{
    /**
     * Register Shortcodes in Editor
     */
    public static function register()
    {
        // Form
        $shortcodeForm = new \WPME\Extensions\Shortcodes\Form();
        $shortcodeForm->setShortocode(
            apply_filters('genoo_wpme_form_shortcode', 'WPMKTENGINEForm')
        );
        $shortcodeForm->setPriority(50);
        $shortcodeForm->setShortocodeAttributes(array('id', 'theme', 'confirmation' => false, 'msgSuccess', 'msgFails'));
        $shortcodeForm->init();

        // CTA
        $shortcodeCTA = new \WPME\Extensions\Shortcodes\CTA();
        $shortcodeCTA->setShortocode(
            apply_filters('genoo_wpme_cta_shortcode', 'WPMKTENGINECTA')
        );
        $shortcodeCTA->setPriority(60);
        $shortcodeCTA->setShortocodeAttributes(array('id', 'align', 'hastime' => false, 'time'));
        $shortcodeCTA->init();

        // Survey
        $shortcodeSurvey = new \WPME\Extensions\Shortcodes\Survey();
        $shortcodeSurvey->setShortocode(
            apply_filters('genoo_wpme_survey_shortcode', 'WPMKTENGINESurvey')
        );
        $shortcodeSurvey->setPriority(70);
        $shortcodeSurvey->setShortocodeAttributes(array('id'));
        $shortcodeSurvey->init();

        // Lumens classlist
        if(
            defined('WPMKTENGINE_LUMENS') && WPMKTENGINE_LUMENS == true
            ||
            defined('GENOO_LUMENS') && GENOO_LUMENS == true
        ){
            $shortcodeLumen = new \WPME\Extensions\Shortcodes\Lumens();
            $shortcodeLumen->setShortocode('genooLumens');
            $shortcodeLumen->setPriority(80);
            $shortcodeLumen->setShortocodeAttributes(array('id'));
            $shortcodeLumen->init();
        }
    }
}