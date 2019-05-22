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
 * Class Pages
 * /?feed=wpmktengine_pages
 * /wp-json/wpmktengine/pages
 *
 *
 * @package WPME\WPApi
 */
class Pages extends \WPME\WPApi\Base
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
            $page = new \WPMKTENGINE\RepositoryPages($WPME_CACHE, $WPME_API);
            return $page->getPages();
        } catch(\Exception $e){
            return $data;
        }
    }

    /**
     * @return string
     */
    public static function getEndpoint()
    {
        return 'pages';
    }
}