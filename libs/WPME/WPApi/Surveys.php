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

namespace WPME\WPApi;

/**
 * Class Surveys
 * /?feed=wpmktengine_surveys
 * /wp-json/wpmktengine/surveys
 *
 * @package WPME\WPApi
 */
class Surveys extends \WPME\WPApi\Base
{
    /**
     * @param null $request
     */
    public static function getData($request = null)
    {
        global $WPME_API;
        global $WPME_CACHE;
        $data = array();
        try {
            $surveys = new \WPME\Extensions\RepositorySurveys($WPME_CACHE, $WPME_API);
            return $surveys->getSurveys();
        } catch(\Exception $e){
            return $data;
        }
    }

    /**
     * @return string
     */
    public static function getEndpoint()
    {
        return 'surveys';
    }
}