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

namespace WPME\Extensions\TrackingLink;

/**
 * Class Shortcode
 *
 * @package WPME\Extensions\TrackingLink
 */
class Shortcode
{
    /**
     * Register
     */
    public static function register()
    {
        add_shortcode(
            apply_filters('genoo_wpme_tracking_link_shortcode', 'WPMKTENGINE_LINK'),
            array(__CLASS__, 'tracking')
        );
    }

    /**
     * @param $atts
     * @param string $content
     * @return string
     */
    public static function tracking($atts, $content = '')
    {
        // Atts
        $atts = shortcode_atts(array(
            // Html attributes
            'href' => '',
            'class' => '',
            'onclick' => '',
            'id' => '',
            'data' => '',
            'data-target' => '',
            'target' => '',
            'onclick' => '',
            // Element attributes
            'parameter' => 'UPID',
            'parameterSource' => 'cookie',
            'newWindow' => false,
            'ebid' => true,
            'ebslid' => true
        ), $atts);

        // Add href if empty
        if(!isset($atts['href'])){
            $atts['href'] = '';
        }

        // Get info
        $parameter = $atts['parameter'];
        $parameterSource = $atts['parameterSource'];
        $parameterToSearch = WPMKTENGINE_LEAD_COOKIE;
        $parameterData = '';
        $newWindow = $atts['newWindow'];

        unset($atts['parameter']);
        unset($atts['parameterSource']);

        // Source of data
        switch($parameterSource){
            case 'cookie':
                $parameterData = isset($_COOKIE[$parameterToSearch])
                    ? $_COOKIE[$parameterToSearch] : null;
                break;
            case 'url':
                $parameterData = isset($_GET[$parameterToSearch])
                    ? $_GET[$parameterToSearch] : null;
                break;
            // Default is any of them
            default:
                $parameterData = isset($_GET[$parameterToSearch])
                    ? $_GET[$parameterToSearch]
                    : (isset($_COOKIE[$parameterToSearch])
                        ? $_COOKIE[$parameterToSearch] : null);
        }
        $arguments = array();
        $arguments[$parameter] = $parameterData;

        // Ebid
        if($atts['ebid']){
            if($atts['ebid'] === true || $atts['ebid'] === 'true'){
                $parameterEbid = self::getFromAnySource('_gtebid');
                if($parameterEbid !== false){
                    $arguments['ebid'] = $parameterEbid;
                }
            }
        }

        // Ebslid
        if($atts['ebslid']){
            if($atts['ebslid'] === true || $atts['ebslid'] === 'true'){
                $parameterEbid = self::getFromAnySource('_gtebslid');
                if($parameterEbid !== false){
                    $arguments['ebslid'] = $parameterEbid;
                }
            }
        }

        // Remove params
        unset($atts['ebid']);
        unset($atts['ebslid']);


        // Append
        $atts['href'] = add_query_arg($parameters, $atts['href']);

        // New window?
        if($newWindow && (!isset($atts['target']) || empty($atts['target']))){
            $atts['target'] = '_blank';
        }

        // Generate
        $attributes = '';
        if(is_array($atts)){
            foreach($atts as $key => $value){
                // Only attributes that do have value
                if(!empty($value)){
                    $attributes .= $key.'="'.htmlspecialchars($value).'" ';
                }
            }
        }

        return "<a $attributes>$content</a>";
    }

    /**
     * Get value from anywhere
     *
     * @param null $key
     * @return null
     */
    public static function getFromAnySource($key = null)
    {
        if($key === null){
            return null;
        }
        if($key){
            return isset($_GET[$key])
                ? $_GET[$key]
                : (
                   isset($_COOKIE[$key])
                       ? $_COOKIE[$key]
                       : null
                );
        }
    }
}