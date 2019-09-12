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

namespace WPME\Affiliates;

/**
 * Class Links
 * Serves as persistent parameter class, to keep selected params in
 * links for another call.
 *
 * @package WPME\Affiliates
 */
class Links
{
    /** @type array */
    public static $watch = array('upid', 'ref');
    /** @type array list of external domains */
    public static $watchExternal = array();
    /** @type bool persistant in internal links */
    public static $useInternal = TRUE;
    /** @type bool persistant in external links */
    public static $useExternal = TRUE;
    /** @type \Genoo\RepositorySettings|\WPMKTENGINE\RepositorySettings */
    public $settings;

    /**
     * Links constructor. Could be used, or it could run as static register
     * without setting up. We will set up though. There will be settings.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        if(is_object($settings)
            &&
            ($settings instanceof \WPMKTENGINE\RepositorySettings || $settings instanceof \Genoo\RepositorySettings)
        ){
            $this->settings = $settings;
        }
    }

    /**
     * @param bool $true
     */
    public static function watchExternal($true = TRUE){ self::$useExternal = $true; }

    /**
     * @param bool $true
     */
    public static function watchInternal($true = TRUE){ self::$useExternal = $true; }


    /**
     * Adds param to watch in URLS
     *
     * @param $param
     */
    public static function addParamaterToWatch($param)
    {
        if(!in_array($param, self::$watch)){
            array_push(self::$watch, $param);
        }
    }

    /**
     * Adds external website to track
     *
     * @param $website
     */
    public static function addExternalWebsite($website)
    {
        if(!in_array($website, self::$watchExternal)){
            array_push(self::$watchExternal, $website);
        }
    }

    /**
     * Registers WordPress filter to add needed attributes to carry
     * on with the links on next pages etc.
     */
    public static function register()
    {
        // Links internal
        //add_filter('bb_stylesheet_uri', array(__CLASS__, 'attributeAddToString'), 99, 1);
        //add_filter('bb_forum_posts_rss_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        //add_filter('bb_forum_topics_rss_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('forum_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('bb_tag_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('tag_rss_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('topic_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('topic_rss_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('post_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('page_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('post_anchor_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('user_profile_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('profile_tab_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('favorites_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('view_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('term_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('_get_page_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        add_filter('post_type_link', array(__CLASS__, 'attributeAddToString'), 99, 1);
        // Nav menu links
        add_filter('nav_menu_link_attributes', array(__CLASS__, 'attributeAddToArray'), 99, 3);
        // Content + excerpt
        add_filter('the_content', array(__CLASS__, 'attributeAddToText'), 99, 1);
        add_filter('get_the_excerpt', array(__CLASS__, 'attributeAddToText'), 99, 1);
    }

    /**
     * Method for filters that use "string" as paramater
     *
     * @param string $link
     * @return string
     */
    public static function attributeAddToString($link = '')
    {
        // Get params
        $params = self::getWatchedParamsFromUrl();
        // Is internal URL?
        $isInternal = self::isInternal($link);
        // Internal filter?
        if($isInternal && self::$useInternal){
            // If we indeed have watched params, crawl through them
            if(!empty($params)){
                // Add params
                $link = add_query_arg($params, $link);
            }
            // External?
        } elseif(!$isInternal && self::$useExternal){
            // Now with external, we have to check if site is in
            // externals we track and add value to, before appending.
            $isExternallyWatched = self::isExternallyWatched($link);
            // Is it externally watched?
            if($isExternallyWatched){
                // Add params
                $link = add_query_arg($params, $link);
            }
        }
        // Return link for sure
        return $link;
    }

    /**
     * Method for filters that user "array" as parameter, to add the tracking params
     *
     * @param $atts
     * @param $item
     * @param $args
     * @return array
     */
    public static function attributeAddToArray($atts, $item, $args)
    {
        // Get href
        $href = array_key_exists('href', $atts) ? $atts['href'] : NULL;
        // Get params
        $params = self::getWatchedParamsFromUrl();
        // Is internal URL?
        $isInternal = self::isInternal($href);
        // Internal filter?
        if($isInternal && self::$useInternal && !is_null($href)){
            // If we indeed have watched params, crawl through them
            if(!empty($params)){
                // Crawl
                $href = add_query_arg($params, $href);
                $atts['href'] = $href;
            }
            // External?
        } elseif(!$isInternal && self::$useExternal && !is_null($href)){
            // Now with external, we have to check if site is in
            // externals we track and add value to, before appending.
            $isExternallyWatched = self::isExternallyWatched($href);
            // Is it externally watched?
            if($isExternallyWatched){
                // Add params
                $href = add_query_arg($params, $href);
                $atts['href'] = $href;
            }
        }
        // Return attributes for sure
        return $atts;
    }

    /**
     * Attribute to whole content text
     * This crawls thorugh the text if it's HTML and finds anchor links,
     * upon their find, it checks the href attribute and does the typical
     * magic of appending attributes we search for if set to do so.
     *
     * @param $text
     * @return string
     */
    public static function attributeAddToText($text)
    {
        // Dom document get init
        libxml_use_internal_errors(TRUE);
        // Dom in globals?
        if(array_key_exists('DOM_DOCUMENT', $GLOBALS)){
            $dom = $GLOBALS['DOM_DOCUMENT'];
        } else {
            $dom = new \DOMDocument();
            $GLOBALS['DOM_DOCUMENT'] = $dom;
        }
        // Load html text if not empty
        if(!empty($text)){
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $text);
            $dom->preserveWhiteSpace = FALSE;
            // Find links
            $links = $dom->getElementsByTagName('a');
            // Go through if not empty and iterable
            if(!empty($links) && (is_array($links) || $links instanceof \DOMNodeList)){
                foreach($links as $element){
                    if(
                        method_exists($element, 'getAttribute')
                        &&
                        method_exists($element, 'setAttribute')
                        &&
                        method_exists($element, 'hasAttribute')
                    ){
                        // Do we have a link?
                        if($element->hasAttribute('href')){
                            // Get href
                            $href = $element->getAttribute('href');
                            // Get params
                            $params = self::getWatchedParamsFromUrl();
                            // Is internal URL?
                            $isInternal = self::isInternal($href);
                            if($isInternal && self::$useInternal && !empty($href)){
                                // If we indeed have watched params, crawl through them
                                if(!empty($params)){
                                    // Crawl
                                    $href = add_query_arg($params, $href);
                                    // Set attribute
                                    $element->setAttribute('href', $href);
                                }
                                // External?
                            } elseif(!$isInternal && self::$useExternal && !empty($href)){
                                // Now with external, we have to check if site is in
                                // externals we track and add value to, before appending.
                                $isExternallyWatched = self::isExternallyWatched($href);
                                // Is it externally watched?
                                if($isExternallyWatched){
                                    // Crawl
                                    $href = add_query_arg($params, $href);
                                    // Set attribute
                                    $element->setAttribute('href', $href);
                                }
                            }
                        }
                    }
                }
            }
            // Before return
            $buffer = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
            // UTF8 not needed anymore
            $buffer = str_replace('<?xml encoding="utf-8" ?>', '', $buffer);
            $text = $buffer;
            // Clena up
            unset($dom);
            unset($buffer);
        }
        return $text;
    }

    /**
     * Returns array param=>value of watched
     * params in current GET request
     *
     * @return array
     */
    public static function getWatchedParamsFromUrl()
    {
        $r = array();
        if(is_array($_GET)){
            if(!empty(self::$watch)){
                foreach(self::$watch as $param){
                    if(array_key_exists($param, $_GET)){
                        $r[$param] = sanitize_text_field($_GET[$param]);
                    }
                }
            }
        }
        return $r;
    }

    /**
     * Is link|href|url internal?
     *
     * @param string $href
     * @param null   $homeUrl
     * @return bool
     */
    public static function isInternal($href = '', $homeUrl = NULL)
    {
        // Get home url
        if(is_null($homeUrl)){
            if(defined('WPMKTENGINE_HOME_URL')){
                $homeUrl = WPMKTENGINE_HOME_URL;
            } elseif(defined('GENOO_HOME_URL')){
                $homeUrl = GENOO_HOME_URL;
            }
        }
        // If not empty home url
        if(!empty($homeUrl)){
            // Sanatize both
            if(substr($homeUrl, 0, strlen('http://')) === 'http://'){
                $homeUrl = str_replace('http://', '%PROTOCOL%', $homeUrl);
            }
            if(substr($homeUrl, 0, strlen('https://')) === 'http://'){
                $homeUrl = str_replace('https://', '%PROTOCOL%', $homeUrl);
            }
            if(substr($href, 0, strlen('http://')) === 'http://'){
                $href = str_replace('http://', '%PROTOCOL%', $href);
            }
            if(substr($href, 0, strlen('https://')) === 'http://'){
                $href = str_replace('https://', '%PROTOCOL%', $href);
            }
            // Check if it contains
            if(strpos($href, $homeUrl) !== false){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Is given link|url extarnally watched?
     *
     * @param $urlExt
     * @return bool
     */
    public static function isExternallyWatched($urlExt)
    {
        // Get host
        $host = \parse_url($urlExt, PHP_URL_HOST);
        // Remove www to get pure domain
        $host = str_replace('www.', '', $host);
        if($host){
            if(!empty(self::$watchExternal)){
                foreach(self::$watchExternal as $url){
                    // Get host
                    $hostExternal = self::addUrlScheme($url);
                    $hostExternal = \parse_url($hostExternal, PHP_URL_HOST);
                    // Remove www to get pure domain
                    $hostExternal = str_replace('www.', '', $hostExternal);
                    if($hostExternal){
                        if($hostExternal == $host){
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * Adds URL scheme just to be sure
     *
     * @param $url
     * @param string $scheme
     * @return string
     */
    public static function addUrlScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
    }
}