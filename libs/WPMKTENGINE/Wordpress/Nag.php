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

use WPMKTENGINE\RepositoryUser,
    WPMKTENGINE\Wordpress\Utils;


class Nag
{
    /** @var should hold user repository */
    public static $repositaryUser;


    /**
     * Instance makes sure user repository is set
     */

    public static function instance()
    {
        if(!self::$repositaryUser instanceof RepositoryUser){
            self::$repositaryUser = new RepositoryUser();
        }
    }


    /**
     * Check GET keys
     *
     * @param $keys
     * @return Nag
     */

    public static function check($keys)
    {
        // global GET
        global $_GET;
        // get user repository
        self::instance();
        // check
        if(is_string($keys)){
            if(array_key_exists($keys, $_GET) && ($_GET[$keys] == '1') && (current_user_can('install_plugins'))){
                self::hide($keys);
            }
        } elseif(is_array($keys)){
            foreach($keys as $key){
                if(array_key_exists($key, $_GET) && ($_GET[$key] == '1') && (current_user_can('install_plugins'))){
                    self::hide($key);
                }
            }
        }
        return new static;
    }


    /**
     * Hide nag (set user meta)
     *
     * @param $key
     */

    public static function hide($key)
    {
        // get user repository
        self::instance();
        // hide nag
        self::$repositaryUser->updateOption($key, 1);
        return new static;
    }


    /**
     * Returns back hiding generated hiding nag hideLink.
     *
     * @param $text
     * @param $key
     * @return string
     */

    public static function hideLink($text, $key)
    {
        $linkCurrentUrl = Utils::getRealUrl();
        $linkUrl = admin_url(Utils::addQueryParam(basename($linkCurrentUrl), $key, 1));
        return (string)'<a href="'. $linkUrl .'">' . $text . '</a>';
    }


    /**
     * Admin hideLink for nag
     *
     * @param $text
     * @param $page
     * @return string
     */

    public static function adminLink($text, $page)
    {
        return (string)'<a href="'. admin_url('admin.php?page=' . $page) .'">' . $text . '</a>';
    }


    /**
     * Is this nag visible?
     *
     * @param $key
     * @return bool
     */

    public static function visible($key)
    {
        // get user repository
        self::instance();
        return self::$repositaryUser->hideNag($key);
    }

}