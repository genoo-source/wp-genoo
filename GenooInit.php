<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

use WPMKTENGINE\RepositoryUser;
use WPMKTENGINE\Cache;
use WPMKTENGINE\Wordpress\Widgets;
use WPMKTENGINE\Users;
use WPMKTENGINE\Wordpress\Action;
use WPMKTENGINE\Wordpress\Ajax;
use WPMKTENGINE\Wordpress\Debug;
use WPMKTENGINE\Wordpress\Comments;
use WPMKTENGINE\Wordpress\Cron;
use WPMKTENGINE\Wordpress\Sidebars;
use Genoo\Frontend;
use Genoo\Admin;
use Genoo\Shortcodes;
use Genoo\Api;
use Genoo\RepositorySettings;

class Genoo
{
    /** @var \Genoo\RepositorySettings */
    private $repositarySettings;
    /** @var \Genoo\Api */
    private $api;
    /** @var \WPMKTENGINE\Cache */
    private $cache;

    /**
     * Constructor, does all this beautiful magic, loads all libs
     * registers all sorts of funky hooks, checks stuff and so on.
     */

    public function __construct()
    {
        // start the engine last file to require, rest is auto
        // custom auto loader, PSR-0 Standard
        require_once('GenooRobotLoader.php');
        $classLoader = new GenooRobotLoader();
        $classLoader->setPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR);
        $classLoader->addNamespace('Genoo');
        $classLoader->addNamespace('WPME');
        $classLoader->addNamespace('WPMKTENGINE');
        $classLoader->register();
        // Cosntants define
        define('GENOO_KEY',     'genoo');
        define('GENOO_FILE',    'genoo/Genoo.php');
        define('GENOO_CRON',    'genoo_cron');
        define('GENOO_LEGACY',  FALSE);
        define('GENOO_HOME_URL',get_option('siteurl'));
        define('GENOO_FOLDER',  plugins_url(NULL, __FILE__));
        define('GENOO_ROOT',    dirname(__FILE__) . DIRECTORY_SEPARATOR);
        define('GENOO_ASSETS',  GENOO_FOLDER . '/assets/');
        define('GENOO_ASSETS_DIR', GENOO_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        // Storage
        define('GENOO_CACHE',   \WPMKTENGINE\RepositorySettings::getCacheDir());
        define('GENOO_DEBUG',   get_option('genooDebug'));
        define('GENOO_REFRESH', sha1('new-admin-ui'));
        define('GENOO_DOMAIN', '//api.genoo.com');
        // wp init
        Action::add('plugins_loaded', array($this, 'init'), 1);
    }


    /**
     * Initialize
     */

    public function init()
    {
        // Dropins
        require_once GENOO_ROOT . '/extensions/dropins.php';
        if(\WPMKTENGINE\Wordpress\Utils::isSecure()){
            define('WPMKTENGINE_BUILDER', 'https://genoolabs.com/simplepagebuilder/');
        } else {
            define('WPMKTENGINE_BUILDER', 'http://genoolabs.com/simplepagebuilder/');
        }
        define('WPMKTENGINE_LEAD_COOKIE', '_gtld');
        define('GENOO_DEV', apply_filters('wpmktengine_dev', FALSE));
        // initialize
        $this->repositarySettings = new \WPME\RepositorySettingsFactory();
        $this->api = new \WPME\ApiFactory($this->repositarySettings);
        $this->cache = new \WPME\CacheFactory(GENOO_CACHE);
        // helper constants
        define('GENOO_PART_SETUP', $this->api->isSetup());
        define('GENOO_SETUP', $this->api->isSetupFull());
        define('GENOO_LUMENS', $this->api->isLumensSetup());
        if(GENOO_SETUP){
            define('WPMKTENGINE_BUILDER_NEW', WPMKTENGINE_BUILDER . 'index-login.php?api='. $this->repositarySettings->getApiKey() .'&domain=' . GENOO_HOME_URL);
        } else {
            define('WPMKTENGINE_BUILDER_NEW', '');
        }
        // Constant bridge
        $this->defineConstantBridge();
        $this->defineBridge();
        // Make globals global
        global $WPME_API;
        global $WPME_CACHE;
        global $WPME_STYLES;
        global $WPME_STYLES_JS;
        global $WPME_MODALS;
        $WPME_API = $this->api;
        $WPME_CACHE = $this->cache;
        $WPME_STYLES = '';
        $WPME_STYLES_JS = '';
        $WPME_MODALS = array();

        /**
         * 0. Text-domain
         */
        load_plugin_textdomain('genoo', false, dirname(plugin_basename(__FILE__)) . '/lang/');

        /**
         * 1. Debug call?
         */
        if(GENOO_DEBUG){ new Debug(); }

        /**
         * 2. Register Widgets / Shortcodes / Cron, etc.
         */

        if(GENOO_SETUP){
            Ajax::register();
            Comments::register();
            Users::register($this->repositarySettings, $this->api);
            Widgets::register();
            Shortcodes::register();
            // Extensions
            // Shortocde Surveys
            \WPME\Extensions\ShortcodesSurveys::register();
            // Ctas
            \WPME\Extensions\CTAs::register();
            \WPME\Extensions\ShortcodesInEditor::register();
            \WPME\Extensions\LandingPages\LandingPages::register();
            \WPME\Extensions\TrackingLink\Shortcode::register();
            // Clever plugins
            global $pagenow;
            if(current_user_can('manage_options')){
                $cleverPlugins = new \WPME\Extensions\Clever\Plugins();
                $cleverPlugins->register();
            }
            // Customizer
            $customizerExtension = new \WPME\Customizer\CustomizerExtension();
            $customizerExtension->registerCustomizerPreview();
            // Add Josh's webinar code
            require_once WPMKTENGINE_ROOT .  '/libs/WPME/Extensions/Webinar.php';
            add_filter('wpseo_accessible_post_types', function($post_types){
                $post_types[] = 'wpme-landing-pages';
                return $post_types;
            }, 10, 1);
        }

        /**
         * 3. Extensions
         */
        // This runs in plugin_loaded
        Action::run('wpmktengine_init', $this->repositarySettings, $this->api, $this->cache);

        /**
         * 4. Init RSS
         */

        if(GENOO_SETUP){
            Action::add('init', array($this, 'jsonApi'));
        }

        /**
         * 5. Admin | Frontend
         */

        if(is_admin()){
            global $WPME_ADMIN;
            $WPME_ADMIN = new Admin($this->api, $this->cache);
            return $WPME_ADMIN;
        }
        global $WPME_FRONTEND;
        $WPME_FRONTEND = new Frontend($this->repositarySettings, $this->api, $this->cache);
        return $WPME_FRONTEND;
    }

    /**
     * Redfine constatns for WPMKTENGINE files
     */
    public static function defineConstantBridge()
    {
        genoo_wpme_define('WPMKTENGINE_KEY',        GENOO_KEY);
        genoo_wpme_define('WPMKTENGINE_FILE',       GENOO_FILE);
        genoo_wpme_define('WPMKTENGINE_HOME_URL',   GENOO_HOME_URL);
        genoo_wpme_define('WPMKTENGINE_FOLDER',     GENOO_FOLDER);
        genoo_wpme_define('WPMKTENGINE_ROOT',       GENOO_ROOT);
        genoo_wpme_define('WPMKTENGINE_ASSETS',     GENOO_ASSETS);
        genoo_wpme_define('WPMKTENGINE_ASSETS_DIR', GENOO_ASSETS_DIR);
        genoo_wpme_define('WPMKTENGINE_CACHE',      GENOO_CACHE);
        genoo_wpme_define('WPMKTENGINE_DEBUG',      GENOO_DEBUG);
        genoo_wpme_define('WPMKTENGINE_REFRESH',    GENOO_REFRESH);
        genoo_wpme_define('WPMKTENGINE_PART_SETUP', GENOO_PART_SETUP);
        genoo_wpme_define('WPMKTENGINE_SETUP',      GENOO_SETUP);
        genoo_wpme_define('WPMKTENGINE_SETUP_LEAD_TYPES', GENOO_SETUP);
        genoo_wpme_define('WPMKTENGINE_LUMENS',     GENOO_LUMENS);
        genoo_wpme_define('WPMKTENGINE_DEV',        GENOO_DEV);
        genoo_wpme_define('WPMKTENGINE_DOMAIN',     GENOO_DOMAIN);
    }

    public static function defineBridge()
    {
        // Shortcode keys
        add_filter('genoo_wpme_form_shortcode', function($shortcode){ return 'genooForm'; });
        add_filter('genoo_wpme_survey_shortcode', function($shortcode){ return 'genooSurvey'; });
        add_filter('genoo_wpme_cta_shortcode', function($shortcode){ return 'genooCTA'; });
        add_filter('genoo_wpme_tracking_link_shortcode', function($shortcode){ return 'genooLink'; });
        // Widget titles
        add_filter('genoo_wpme_widget_title_lumens', function($shortcode){ return 'Genoo: Lumens Class List'; });
        add_filter('genoo_wpme_widget_title_form', function($shortcode){ return 'Genoo: Lead Capture Form'; });
        add_filter('genoo_wpme_widget_title_cta', function($shortcode){ return 'Genoo: CTA'; });
        add_filter('genoo_wpme_widget_title_cta_dynamic', function($shortcode){ return 'Genoo: CTA (dynamic)'; });
        // Settings keys
        add_filter('genoo_wpme_repeatable_key', function($shortcode){ return 'repeatable_genoo-dynamic-cta'; });
        // Widget descriptions
        add_filter('genoo_wpme_widget_description_form', function(){ return __('Add Genoo forms to your pages.', 'wpmktengine'); });
        add_filter('genoo_wpme_widget_description_lumens', function(){ return __('Genoo widget class list.', 'wpmktengine'); });
        add_filter('genoo_wpme_widget_description_cta', function(){ return __('Genoo Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'wpmktengine'); });
        add_filter('genoo_wpme_widget_description_cta_dynamic', function(){ return __('Genoo Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'wpmktengine'); });
        // Clever plugin
        add_filter('genoo_wpme_clever_plugins_owner', function(){ return "<strong>GENOO: </strong>"; });
    }

    /**
     * Activation Hook
     */
    public static function activate()
    {
        // Save first settings, so pages and posts are always checked
        RepositorySettings::saveFirstSettings();
    }

    /**
     * API responses
     */
    public function jsonApi()
    {
        \WPME\WPApi\CTAs::register();
        \WPME\WPApi\Surveys::register();
        \WPME\WPApi\Forms::register();
        \WPME\WPApi\Pages::register();
    }
}

$genoo = new Genoo();

/**
 * Get Domain Name
 */
if(!function_exists('genoo_wpme_get_domain')){
  /**
   * Get Domain Nanem
   */
  function genoo_wpme_get_domain($url)
  {
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
      return $regs['domain'];
    }
    return false;
  }
}

/**
 * Genoo / WPME json return data
 */
if(!function_exists('genoo_wpme_on_return')){

    /**
     * @param $data
     */

    function genoo_wpme_on_return($data)
    {
        @error_reporting(0); // don't break json
        header('Content-type: application/json');
        die(json_encode($data));
    }
}

/**
 * Define if not defined
 */
if(!function_exists('genoo_wpme_define')){

    /**
     * @param $name
     * @param $value
     */
    function genoo_wpme_define($name, $value)
    {
        if(!defined($name) && !empty($value)){
            define($name, $value);
        }
    }
}
