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


class Redirect
{
    /** @var int */
    public static $code = 302;

    /**
     * Set code first before redirect
     *
     * @param $code
     * @return Redirect
     */

    public static function code($code)
    {
        static::$code = $code;
        return new static;
    }


    /**
     * Where do we redirect
     *
     * @param $url
     * @throws \InvalidArgumentException
     */

    public static function to($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)){
            wp_redirect($url, static::$code); exit;
        } else {
            throw new \InvalidArgumentException('Provided URL is not valid.');
        }
    }

}