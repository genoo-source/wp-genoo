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

use WPMKTENGINE\Utils\Strings;

class Utils
{
    /**
     * Wordpress human_time_diff, returns "2 hours ago" etc.
     * - takes unix timestamp
     *
     * @param $from
     * @param $to
     * @return mixed
     */

    public static function timeToString($from, $to){ return human_time_diff($from, $to); }


    /**
     * For our porpuses, we just need to add API key to each call
     *
     * @param $url
     * @param $key
     * @param $value
     * @return mixed
     */

    public static function addQueryParam($url, $key, $value = null){ return add_query_arg($key, $value, $url); }


    /**
     * Add query params, in array
     *
     * @param $url
     * @param array $params
     * @return mixed
     */

    public static function addQueryParams($url, array $params = array()){ return add_query_arg($params, $url); }


    /**
     * Remove query parameter
     *
     * @param $url
     * @param $key
     * @return mixed
     */

    public static function removeQueryParam($url, $key){ return remove_query_arg($key, $url); }


	/**
	 * Get Real URL
	 *
	 * @param bool $withPort
	 * @return string
	 */

    public static function getRealUrl($withPort = FALSE)
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : (($_SERVER["HTTPS"] == "on") ? "s" : "");
        $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
        $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
        if($withPort){
	        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
        } else {
	        $port = '';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
    }

    /**
     * Move menu to submenu
     *
     * @param $menu
     * @param $submenu
     * @param $key
     * @param $where
     * @return object
     */
    public static function moveMenuToSubmenu($menu, $submenu, $key, $where)
    {
        // Return
        $r = array();
        // Magic
        if($menu){
            foreach($menu as $k => $m){
                if(Strings::contains($m[2], $key)){
                    $del = $k;
                    break;
                }
            }
        }
        if(isset($del)){ unset($menu[$del]); }
        // Admin submenu, assing to WPMKTENGINE
        if($submenu){
            // find correct submenu
            foreach($submenu as $k => $m){
                if(Strings::contains($k, $key)){
                    $ctaSubMenu = $m;
                }
            }
            // remove it
            if(isset($submenu[$key])){ unset($submenu[$key]); }
            // assign it to genoo
            foreach($submenu as $k => $m){
                if(Strings::contains($k, $where)){
                    if($ctaSubMenu){
                        foreach($ctaSubMenu as $sMenuItem){
                            // Assign
                            $submenu[$k][] = $sMenuItem;
                        }
                    }
                }
            }
        }
        // Set
        $r['menu'] = $menu;
        $r['submenu'] = $submenu;
        // Return
        return (object)$r;
    }


    /**
     * Does what it says, converts camel case to underscore
     *
     * @param $string
     * @return string
     */

    public static function camelCaseToUnderscore($string){ return strtolower(preg_replace('/(?!^)[[:upper:]]/','_\0', $string)); }


    /**
     * Does what it says, converts underscore to camelcase
     *
     * @param $string
     * @param bool $firstCaps
     * @return mixed
     */

    public static function underscoreToCamelCase($string, $firstCaps = true)
    {
        if($firstCaps == true){$string[0] = strtoupper($string[0]); } $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $string);
    }


    /**
     * String to udnerscore
     *
     * @param $string
     * @return string
     */

    public static function toUnderscore($string){ return strtolower(preg_replace('/([a-z])([A-Z])/','$1_$2', $string)); }


    /**
     * Debug to console
     *
     * @param $data
     */

    public static function debugConsole($data){
        if (is_array($data)){
            $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
        } else {
            $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
        }
        echo $output;
    }

    /**
     * Is iterable?
     *
     * @param $var
     * @return bool
     */
    public static function isIterable($var)
    {
        return $var !== null
                && (is_array($var)
                || $var instanceof \Traversable
                || $var instanceof \Iterator
                || $var instanceof \IteratorAggregate
        );
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function nonProtocolUrl($url)
    {
        $http = self::isSecure() ? 'https://' : 'http://';
        return str_replace(
            array(
                'http://',
                'https://',
            ),
            array(
                $http,
                'https://'
            ),
            $url
        );
    }

    /**
     * @return bool
     */
    public static function isSecure()
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }


    /**
     * Does what it says
     *
     * @param $array
     * @return bool
     */
    public static function definedAndFalse($array)
    {
        $r = $array;
        if(is_array($r)){
            foreach($r as $constant){
                if (defined($constant) && constant($constant) == TRUE){
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * @param $param
     * @return bool
     */
    public static function getParamIsset($param)
    {
        if(isset($_GET) && is_array($_GET) && array_key_exists($param, $_GET)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @param $param
     * @return bool
     */
    public static function getParamGet($param)
    {
        if(isset($_GET) && is_array($_GET) && array_key_exists($param, $_GET)){
            return $_GET[$param];
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public static function isSafeFrontend()
    {
        return self::definedAndFalse(
            array(
                'DOING_AJAX',
                'DOING_AUTOSAVE',
                'DOING_CRON',
                'WP_ADMIN',
                'WP_IMPORTING',
                'WP_INSTALLING',
                'WP_UNINSTALL_PLUGIN',
                'IFRAME_REQUEST',
                '#WP_INSTALLING_NETWORK',
                'WP_NETWORK_ADMIN',
                'WP_LOAD_IMPORTERS',
                'WP_REPAIRING',
                'WP_UNINSTALL_PLUGIN',
                'WP_USER_ADMIN',
                'XMLRPC_REQUEST'
            )
        );
    }
}