<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo;

use Genoo\RepositorySettings;
use Genoo\Api;
use WPME\RepositorySettingsFactory;
use WPMKTENGINE\RepositoryForms;
use WPMKTENGINE\RepositoryLumens;
use WPMKTENGINE\WidgetCTA;
use WPMKTENGINE\CTA;
use WPMKTENGINE\Cache;
use WPMKTENGINE\ModalWindow;
use WPMKTENGINE\HtmlForm;
use WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Post;


class Shortcodes
{
    /* Shortcode get parameter */
    const SHORTCODE_ID = 'gs';


    /**
     * Register shortcodes
     */

    public static function register()
    {
        add_shortcode('genooForm',   array(__CLASS__, 'form'));
        add_shortcode('WPMKTENGINEForm',   array(__CLASS__, 'form'));
        add_shortcode('genooLumens', array(__CLASS__, 'lumens'));
        add_shortcode('genooCTA',    array(__CLASS__, 'cta'));
        add_shortcode('genooCta',    array(__CLASS__, 'cta'));
        add_shortcode('WPMKTENGINECTA',    array(__CLASS__, 'cta'));
    }


    /**
     * Return url for shortcode
     *
     * @param $id
     * @return mixed
     */

    public static function getReturnUrlShortcode($id)
    {
        return Utils::addQueryParams(ModalWindow::closeUrl(),array(self::SHORTCODE_ID => self::getShortcodeId($id)));
    }


    /**
     * Get shortcode id
     *
     * @param $id
     * @return mixed
     */

    public static function getShortcodeId($id)
    {
        return str_replace('-', '', self::SHORTCODE_ID . Strings::firstUpper($id));
    }


    /**
     * Is modal visible, static
     *
     * @param $id
     * @return bool
     */

    public static function isShortcodeVisible($id)
    {
        $modalId = self::getShortcodeId($id);
        if((isset($_GET[self::SHORTCODE_ID]) && $_GET[self::SHORTCODE_ID] == $modalId)){
            return true;
        }
        return false;
    }


    /**
     * Shortcode form result
     *
     * @param $id
     * @return bool|null
     */

    public static function shortcoeFormResult($id)
    {
        if(self::isShortcodeVisible($id)){
            if(isset($_GET['formResult'])){
                if($_GET['formResult'] == 'true'){
                    return true;
                } elseif($_GET['formResult'] == 'false'){
                    return false;
                }
            }
        }
        return null;
    }


    /**
     * Forms
     *
     * @param $atts
     * @return null|string
     */

    public static function form($atts)
    {
        // set static counter
        static $count = 1;
        try {
            // prep
            $repositorySettings = new \WPME\RepositorySettingsFactory();
            $repositoryForms = new RepositoryForms(new Cache(GENOO_CACHE), new \WPME\ApiFactory($repositorySettings));
            $formId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            $formIdFinal = is_null($formId) ? $repositorySettings->getActiveForm() : $formId;
            $formTheme = !empty($atts['theme']) ? esc_attr($atts['theme']) : $repositorySettings->getActiveTheme();
            $formSuccess = !empty($atts['msgsuccess']) ? esc_html($atts['msgsuccess']) : null;
            $formFail = !empty($atts['msgfail']) ? esc_html($atts['msgfail']) : null;
            // do we have a form ID?
            if(!empty($formIdFinal)){
                // Get unique id
                $uniqueId = 'gn' . md5($count . $formIdFinal);
                // prep html
                $h = '<div id="'. $uniqueId .'" class="genooForm genooShortcode themeResetDefault '. $formTheme .'"><div class="genooGuts"><div id="genooMsg"></div>';
                $h .= $repositoryForms->getForm($formIdFinal);
                $h .= '</div></div>';
                // Add form styles using JS
                $formStyles = $repositoryForms->getFormStylePrefixd($formIdFinal, '#' . $uniqueId);
                $formStylesJs = '';
                if(!empty($formStyles) && $formStyles !== '' && $formStyles !== false){
                    $formStylesJs = '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . json_encode($formStyles) . '); }</script>';
                }
                // id
                $id = $formIdFinal;
                // inject inputs and message
                $inject = new HtmlForm($h);
                if(!empty($formSuccess) && !empty($formFail)){
                    $inject->appendHiddenInputs(array('popup' => 'true','returnModalUrl' => self::getReturnUrlShortcode($id)));
                }
                $result = self::shortcoeFormResult($id);
                // do we have a result?
                if(($result == true || $result == false) && (!is_null($result))){
                    if($result == false){
                        $inject->appendMsg($formFail, $result);
                    } elseif($result == true) {
                        $inject->appendMsg($formSuccess, $result);
                    }
                    // Hide required if any
                    $inject->hideRequired();
                }
                // Counter
                $count++;
                // return html
                return $inject . $formStylesJs;
            }
        } catch (\Exception $e){
            return null;
        }
    }


    /**
     * Lumen class list
     *
     * @param $atts
     * @return null|string
     */

    public static function lumens($atts)
    {
        try {
            $repositorySettings = new RepositorySettingsFactory();
            $repositoryLumens = new \WPMKTENGINE\RepositoryLumens(
                new Cache(GENOO_CACHE),
                new \WPME\ApiFactory($repositorySettings)
            );
            $formId = !empty($atts['id']) && is_numeric($atts['id']) ? $atts['id'] : null;
            if(!is_null($formId)){
                return '<div class="themeResetDefault"><div class="genooGuts">' . $repositoryLumens->getLumen($formId) . '</div></div>';
            }
        } catch (\Exception $e){
            return null;
        }
    }


    /**
     * Genoo CTA
     *
     * @param $atts
     */

    public static function cta($atts)
    {
        // get post
        global $post;
        // set static counter
        static $count = 1;
        if(isset($atts['id'])){
            try {
                // Get CTA
                $cta = new \WPMKTENGINE\WidgetCTA();
                $cta->setThroughShortcode($count, esc_attr($atts['id']), $atts);
                // Increase counter
                $count++;
                return $cta->getHtmlInner(array(), $cta->getInnerInstance());
            } catch(\Exception $e){
                return $e->getMessage();
            }
        }
    }


    /**
     * Get Shortcodes From Content
     *
     * @param $content
     * @param null $shortcode
     * @return array|bool
     */

    public static function getFromContent($content, $shortcode = null)
    {
        // Dont give back null
        $matches = array();
        $r = array();
        // Check if in post
        if (false === strpos($content, '[')){ return false; }
        // Parse Shortcodes from content
        $matches = self::findShortcodes($content, $shortcode);
        // Purify the result
        $count = 1;
        if($matches){
            foreach($matches as $match){
                // Arguments
                array_filter($match);
                $match = array_map('trim', $match);
                $matchLast = end($match);
                $actualShortcode = $match[0];
                $args = shortcode_parse_atts(str_replace(array('[',']'),'', $actualShortcode));
                // is shortcode set?
                if($shortcode){
                    // Is it here?
                    if(Strings::contains(Strings::lower($args[0]), Strings::lower($shortcode))){
                        $r[$count] = $args;
                        ++$count;
                    } else if (Strings::contains($matchLast, $shortcode)){
                        // Might be wrapped in another Shortcode
                        $tryFinding = self::findRecrusively($matchLast, $shortcode);
                        if(is_array($tryFinding)){
                            $r[$count] = $tryFinding;
                            ++$count;
                        }
                    }
                } else {
                    $r[$count] = $args;
                    ++$count;
                }
            }
        }
        // return
        return $r;
    }


    /**
     * Intersect
     *
     * @param null $shortcode
     * @return mixed
     */

    public static function intersectShortcodes($shortcode = null)
    {
        // global shortcodes
        global $shortcode_tags;
        $tags = $shortcode_tags;
        // Remove from global shortcodes
        if($shortcode){
            foreach($tags as $key => $shortcodedata){
                if(is_string($key) && is_string($shortcode)){
                    if(Strings::contains(Strings::lower($key), Strings::lower($shortcode))){
                        // keep
                    } else {
                        unset($tags[$key]);
                    }
                }
            }
        }
        return $tags;
    }


    /**
     * Find inside shortcodes
     *
     * @param $shortCodeData
     * @param $shortcodeSearch
     * @return null
     */

    public static function findRecrusively($shortCodeData, $shortcodeSearch)
    {
        $matches = self::findShortcodes($shortCodeData, $shortcodeSearch);
        $r = null;
        // Prep data
        if(is_array($matches)){
            foreach($matches as $match){
                array_filter($match);
                $match = array_map('trim', $match);
                $shortcodeActual = $match[0];
                $shortcodeActualParsed = shortcode_parse_atts(str_replace(array('[',']'),'', $shortcodeActual));
                $shortcodeLast = end($match);
                if(is_array($shortcodeActualParsed)){
                    // Presuming this has the shortcode.
                    $shortcode = $shortcodeActualParsed[0];
                    if(Strings::contains(Strings::lower($shortcode), Strings::lower($shortcodeSearch))){
                        $r = $shortcodeActualParsed;
                    } elseif (Strings::contains($shortcodeLast, $shortcodeSearch)){
                        $r = self::findRecrusively($shortcodeLast, $shortcodeSearch);
                    }
                }
            }
        }
        return $r;
    }


    /**
     * Remove empty arrays
     *
     * @param $sr
     * @param $shortcode
     * @return mixed
     */

    public static function findShortcodes($sr, $shortcode = null)
    {
        preg_match_all('/' . self::getShortcodeRegex($shortcode) . '/s', $sr, $arr, PREG_SET_ORDER);
        if(is_array($arr)){
            foreach($arr as $key => $value){
                if(is_array($value)){
                    foreach($value as $keyIn => $valueIn){
                        if(strlen($valueIn) == 0 || empty($valueIn) || $valueIn == '0'){
                            unset($arr[$key][$keyIn]);
                        }
                    }
                } else {
                    if(strlen($value) == 0 || empty($value) || $value == '0'){
                        unset($arr[$key]);
                    }
                }
            }
        }
        return $arr;
    }


    /**
     * Get shortcode regex, for only certain tags
     *
     * @param null $except
     * @return string
     */

    public static function getShortcodeRegex($except = null)
    {
        if($except){
            $tagnames = array_keys(self::intersectShortcodes($except));
            $tagregexp = join( '|', array_map('preg_quote', $tagnames));
            // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
            // Also, see shortcode_unautop() and shortcode.js.
            return
                '\\['                              // Opening bracket
                . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
                . "($tagregexp)"                     // 2: Shortcode name
                . '(?![\\w-])'                       // Not followed by word character or hyphen
                . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
                .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
                .     '(?:'
                .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
                .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
                .     ')*?'
                . ')'
                . '(?:'
                .     '(\\/)'                        // 4: Self closing tag ...
                .     '\\]'                          // ... and closing bracket
                . '|'
                .     '\\]'                          // Closing bracket
                .     '(?:'
                .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
                .             '[^\\[]*+'             // Not an opening bracket
                .             '(?:'
                .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
                .                 '[^\\[]*+'         // Not an opening bracket
                .             ')*+'
                .         ')'
                .         '\\[\\/\\2\\]'             // Closing shortcode tag
                .     ')?'
                . ')'
                . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        }
        return get_shortcode_regex();
    }


    /**
     * Get footer CTA modals for current post
     *
     * @return array
     */

    public static function getFooterCTAs()
    {
        global $post;
        $r = array();
        // Do we have a post?
        if($post){
            // Get shortcodes from content
            $shorcodes = self::getFromContent($post->post_content, 'genooCTA');
            if($shorcodes){
                // Go through shortcodes
                foreach($shorcodes as $id => $atts){
                    // Do we have CTA ID and does it exist?
                    if($atts['id'] && is_numeric($atts['id']) && Post::exists($atts['id'])){
                        // get CTA
                        $cta = new WidgetCTA();
                        $cta->setThroughShortcode($id, esc_attr($atts['id']));
                        // PUt in array if it is form CTA
                        if($cta->cta->isForm){
                            $r[$cta->id] = new \stdClass();
                            $r[$cta->id]->widget = $cta;
                            $r[$cta->id]->instance = $cta->getInnerInstance();
                        }
                    }
                }
            }
        }
        return $r;
    }
};