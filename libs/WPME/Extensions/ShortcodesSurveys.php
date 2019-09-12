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

use WPME\ApiExtension\Surveys;
use WPME\ApiFactory;
use WPME\CacheFactory;
use WPME\RepositorySettingsFactory;

class ShortcodesSurveys
{
    /**
     * Register shortcodes
     */
    public static function register()
    {
        add_shortcode(
            apply_filters('genoo_wpme_survey_shortcode', 'WPMKTENGINESurvey'),
            array(__CLASS__, 'survey')
        );
    }

    /**
     * Survey
     *
     * @param $atts
     * @return null|string
     */
    public static function survey($atts)
    {
        try {
            // Get cache
            global $WPME_CACHE;
            // prep
            $repositorySettings = new RepositorySettingsFactory();
            $repositorySurveys = new RepositorySurveys(
                $WPME_CACHE,
                new Surveys($repositorySettings)
            );
            $surveyId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            // do we have a form ID?
            if(!empty($surveyId)){
                // prep html
                $h = '<div class="genooSurvey">';
                $h .= $repositorySurveys->getSurvey($surveyId);
                $h .= '</div>';
                // id
                // return html
                return $h;
            }
        } catch (\Exception $e){
            return null;
        }
    }
};