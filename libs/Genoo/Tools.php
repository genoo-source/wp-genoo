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

use Genoo\Admin;
use WPMKTENGINE\Wordpress\Widgets;
use WPMKTENGINE\Wordpress\Redirect;
use WPMKTENGINE\Wordpress\Debug;
use WPMKTENGINE\Wordpress\Http;
use WPMKTENGINE\Users;
use WPMKTENGINE\Import;


class Tools
{
    /** @var admin instance */
    public static $admin;


    /**
     * Instance makes sure user repository is set
     */

    public static function instance()
    {
        if(!static::$admin instanceof Admin){
            static::$admin = Admin::getInstance();
        }
        return new static;
    }


    /**
     * Check
     *
     * @return Tools
     */

    public static function check($keys)
    {
        // global GET
        global $_GET;
        // check
        if(is_string($keys)){
            if(array_key_exists($keys, $_GET) && ($_GET[$keys] == '1') && (current_user_can('install_plugins'))){
                self::instance();
                self::process($key);
            }
        } elseif(is_array($keys)){
            foreach($keys as $key){
                if(array_key_exists($key, $_GET) && ($_GET[$key] == '1') && (current_user_can('install_plugins'))){
                    self::instance();
                    self::process($key);
                }
            }
        }
        return new static;
    }


    /**
     * Process actions
     *
     * @param $key
     */

    public static function process($key)
    {
        switch($key){
            case 'genooActionFlush':
                // flush all settings
                static::$admin->repositarySettings->flush();
                // save notice so it's visible after the redirect
                static::$admin->repositarySettings->addSavedNotice('updated', 'All settings deleted.');
                // flush forms and lumns
                try{
                    static::$admin->repositaryForms->flush();
                    static::$admin->repositaryLumens->flush();
                } catch (\Exception $e){
                    static::$admin->repositarySettings->addSavedNotice('error', $e->getMessage());
                }
                // flush widgets
                Widgets::removeInstancesOf('genoo');
                // WordPress redirect
                Redirect::to(admin_url('admin.php?page=Genoo'));
                break;
            case 'genooActionDelete':
                try{
                    // flush forms
                    static::$admin->cache->flush('forms');
                    if(GENOO_LUMENS){ static::$admin->cache->flush('lumens'); }
                    static::$admin->addNotice('updated', 'All cache files cleared.');
                } catch (\Exception $e){
                    static::$admin->addNotice('error', $e->getMessage());
                }
                break;
            case 'genooActionValidate':
                try{
                    static::$admin->api->validate();
                    static::$admin->addNotice('updated', 'Your api key is valid.');
                } catch (\Exception $e){
                    static::$admin->addNotice('error', $e->getMessage());
                }
                break;
            case 'genooActionCheck':
                try{
                    // set debug mode on
                    static::$admin->repositarySettings->setDebug(true);
                    // load http wrapper and homepage remotely
                    $http = new Http(GENOO_HOME_URL);
                    $http->get();
                    unset($http);
                    // turn debug off
                    static::$admin->repositarySettings->setDebug(false);
                    // get debug value
                    $debugValues = static::$admin->repositarySettings->getOptions('genooDebugCheck');
                    if(isset($debugValues['wp_footer']) && $debugValues['wp_footer'] == 1){
                        static::$admin->addNotice('updated', __('Your theme uses wp_footer hook. Congratulations.', 'genoo'));
                    } else {
                        static::$admin->addNotice('error', __('It seems like your theme doesn\'t use wp_footer hook, please contact our support.', 'genoo'));
                    }
                    static::$admin->repositarySettings->flushDebugCheck();
                    // remove value
                } catch (\Exception $e){
                    static::$admin->addNotice('error', $e->getMessage());
                }
                break;
        }
    }

    /**
     * Tools link
     *
     * @param $key
     * @param $text
     * @return string
     */

    public static function toolsLink($key, $text, $js = null)
    {
        $js = $js ? 'onclick="'.$js.'"' : '';
        return (string)'<a id="submit" '. $js .' class="button button-primary" href="'. admin_url('admin.php?page=GenooTools&' . $key) .'=1">' . $text . '</a>';
    }


    /** ----------------------------------------------------- */
    /**                        Widgets                        */
    /** ----------------------------------------------------- */


    /**
     * Get Bug Report Widget
     *
     * @return array
     */

    public static function getWidgetBug()
    {
        // wp version
        global $wp_version;
        // plugin data
        $pluginData = get_plugin_data(GENOO_ROOT . 'Genoo.php', true, true);
        $themePosts = wp_count_posts();
        $themeComments = wp_count_comments();
        $rest = array();
        if(isset($_GET) && is_array($_GET) && array_key_exists('debug', $_GET)){
            $settings = new RepositorySettings();
            $rest['Api'] = $settings->getApiKey();
        }
        // Theme info
        $my_theme = \wp_get_theme();
        // return data
        return array(
            'Server Name' => get_bloginfo('name'),
            'PHP Server Name' => $_SERVER['SERVER_NAME'],
            'Server Software' => $_SERVER['SERVER_SOFTWARE'],
            'Server' => get_bloginfo('wpurl'),
            'PHP Version' => PHP_VERSION,
            'WordPress Version' => $wp_version,
            'Genoo Plugin Version' => $pluginData['Version'],
            'Maximum exc. time' => ini_get('max_execution_time'),
            'Published posts' => $themePosts->publish,
            'Approved comments' => $themeComments->approved,
            'Registered subscribers' => Users::getCount(),
            'Theme info' => $my_theme->get('Name') . ", " . $my_theme->get('Version'),
        ) + $rest;
    }

    /**
     * Active extensions
     *
     * @return array
     */
    public static function getActiveExtensions()
    {
        $r = array();
        $r = apply_filters('wpmktengine_tools_extensions_widget', $r);
        if(empty($r)){
            $r['You have no active extensinos.'] = '';
        }
        return $r;
    }

    /**
     * Sidebar report
     *
     * @return array
     */
    public static function getSidebarReport()
    {
        $cacheFile = GENOO_FOLDER . '/cache/sidebars.cache';
        return array(
            'Sidebar Widgets' => '<pre>' . var_export(\get_option('sidebars_widgets'), TRUE) . '</pre>',
            'Cached Sidebar Widgets' => '<pre>' . var_export(\get_option('genoo_sidebars_widgets'), TRUE) . '</pre>',
            'File Cached Sidebar Widgets' => '<pre>' . '<a target="_blank" href="'. $cacheFile .'">sidebars.cache</a>',
            'File Cached Counter' => '<pre>' . var_export(\get_option('genoo_sidebars_counter', 1), TRUE) . '</pre>',
            //'retrieve_widgets()' => '<pre>' . var_export(\retrieve_widgets(), TRUE) . '</pre>',
            'wp_get_sidebars_widgets()' => '<pre>' . var_export(\wp_get_sidebars_widgets(), TRUE) . '</pre>',
        );
    }

    /**
     * Import leads
     *
     * @return string
     */

    public static function getWidgetImport()
    {
        $p = '<p>'. __('Note: Only do this import once. After that, approved commenters will be sent across to Genoo in real time as they are approved.', 'genoo') .'</p>';
        $p .= '<p>' . self::toolsLink('genooActionImport', __('Import Approved Commenters to Genoo.', 'genoo'), 'Genoo.startImport(event)') . '</p>';
        return $p;
    }


    /**
     * Import subscribers
     *
     * @return string
     */

    public static function getWidgetImportSubscribers(\Genoo\Api $api)
    {
        $selectBox = '';
        try{
            $leadTypes = $api->getLeadTypes();
            if(!empty($leadTypes)){
                $selectBox .= '<p><label for="toolsLeadTypes">';
                    $selectBox .= __('Select a lead type: ', 'genoo');
                $selectBox .= '</label>';
                $selectBox .= '<select id="toolsLeadTypes" name="leadTypes">';
                foreach($leadTypes as $lead){
                    $selectBox .= '<option value="'. $lead->id .'">'. $lead->name .'</option>';
                }
                $selectBox .= '</select></p>';
            }
        } catch (\Exception $e){}


        $p = '<p>'. __('Note: Only do this import once. After that, blog subscribers will be sent across to Genoo in real time. as they are approved.', 'genoo') .'</p>';
        $p .= $selectBox;
        $p .= '<p>' . self::toolsLink('genooActionSubscriberImport', __('Import subscribers to Genoo.', 'genoo'), 'Genoo.startSubscriberImport(event)') . '</p>';
        return $p;
    }


    /**
     * Reset widget
     *
     * @return string
     */

    public static function getWidgetFlush()
    {
        $p = '<p>'. __('Note: Will remove all data from the plugin. After clearing, you will need to enter your Genoo API-Key, and External Tracking Code, and reconfigure you settings. It will NOT remove any shortcodes from your site content. ', 'genoo') .'</p>';
        $p .= '<p>' . self::toolsLink('genooActionFlush', __('Clear plugin Settings.', 'genoo')) . '</p>';
        return $p;
    }


    /**
     * Clear cache widget
     *
     * @return string
     */

    public static function getWidgetDelete()
    {
        $p = '<p>'. __('Note: Deleting all cached files will result in slower load on first attempt to re-download all files.', 'genoo') .'</p>';
        $p .= '<p>' . self::toolsLink('genooActionDelete', __('Delete Cached Files.', 'genoo')) . '</p>';
        return $p;
    }


    /**
     * Check widget, checks theme support
     *
     * @return string
     */

    public static function getWidgetCheck()
    {
        $p = '<p>'. __('This will check if your theme uses the required wp_footer hook so we can add the tracking code in the footer of your pages.', 'genoo') .'</p>';
        $p .= '<p>' . self::toolsLink('genooActionCheck', __('Check theme.', 'genoo')) . '</p>';
        return $p;
    }



    /**
     * Validate widget
     *
     * @return string
     */

    public static function getWidgetValidate()
    {
        $p = '<p>'. __('Note: This will validate your API key, it also happens automatically everyday.', 'genoo') .'</p>';
        $p .= '<p>' . self::toolsLink('genooActionValidate', __('Validate API key.', 'genoo')) . '</p>';
        return $p;
    }


    /**
     * @return object
     */
    public static function parseLumenData($data)
    {
        // suppress warnings of invalid html
        libxml_use_internal_errors(true);
        // Dom document
        $dom = new \DOMDocument;
        $dom->loadHTML($data);
        $dom->preserveWhiteSpace = false;
        // Get script + get div
        $arr['id'] = $dom->getElementsByTagName("div")->item(0)->getAttribute('id');
        $arr['src'] = $dom->getElementsByTagName("script")->item(0)->getAttribute('src');
        return (object)$arr;
    }
}