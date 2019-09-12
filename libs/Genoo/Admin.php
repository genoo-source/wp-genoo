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
use Genoo\Tools;
use WPME\Extensions\RepositorySurveys;
use WPME\ApiExtension\Surveys;
use WPME\Extensions\TableSurveys;
use WPMKTENGINE\Cache;
use WPMKTENGINE\RepositoryUser;
use WPMKTENGINE\RepositoryForms;
use WPMKTENGINE\RepositoryLandingPages;
use WPMKTENGINE\RepositoryLumens;
use WPMKTENGINE\RepositoryPages;
use WPMKTENGINE\RepositoryThemes;
use WPMKTENGINE\RepositoryCTA;
use WPMKTENGINE\TableForms;
use WPMKTENGINE\TableLumens;
use WPMKTENGINE\TablePages;
use WPMKTENGINE\Wordpress\MetaboxBuilder;
use WPMKTENGINE\Wordpress\MetaboxArea;
use WPMKTENGINE\Wordpress\MetaboxStyles;
use WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\Wordpress\Settings;
use WPMKTENGINE\Wordpress\Page;
use WPMKTENGINE\Wordpress\Notice;
use WPMKTENGINE\Wordpress\Nag;
use WPMKTENGINE\Wordpress\Metabox;
use WPMKTENGINE\Wordpress\PostType;
use WPMKTENGINE\Wordpress\Action;
use WPMKTENGINE\Wordpress\Filter;
use WPMKTENGINE\Wordpress\TinyMCE;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Debug;
use WPMKTENGINE\Wordpress\MetaboxCTA;


class Admin
{
    /** @var bool */
    private static $instance = false;
    /** @var array Admin Messages */
    var $notices = array();
    /** @var \Genoo\RepositorySettings */
    var $repositarySettings;
    /** @var \WPMKTENGINE\RepositoryForms */
    var $repositaryForms;
    /** @var \WPMKTENGINE\RepositoryPages */
    var $repositaryPages;
    /** @var \WPMKTENGINE\RepositoryLumens */
    var $repositaryLumens;
    /** @var \WPMKTENGINE\RepositoryCTA  */
    var $repositaryCTAs;
    /** @var \WPME\Extensions\RepositorySurveys */
    var $repositorySurveys;
    /** @var \WPMKTENGINE\RepositoryUser */
    var $user;
    /** @var \Genoo\Api */
    var $api;
    /** @var \WPMKTENGINE\Wordpress\Settings */
    var $settings;
    /** @var \WPMKTENGINE\Cache */
    var $cache;
    /** @var \WPMKTENGINE\TableForms */
    var $tableForms;
    /** @var \WPMKTENGINE\TableLumens */
    var $tableLumens;
    /** @var \WPMKTENGINE\TablePages */
    var $tablePages;


    /**
     * Constructor
     */

    public function __construct(\WPME\ApiFactory $api = null, \WPMKTENGINE\Cache $cache = null)
    {
        // vars
        $this->cache = $cache ? $cache : new \WPME\CacheFactory(GENOO_CACHE);
        $this->repositarySettings = new \WPME\RepositorySettingsFactory();
        $this->api = $api ? $api : new \WPME\ApiFactory($this->repositarySettings);
        $this->repositaryForms = new RepositoryForms($this->cache, $this->api);
        $this->repositaryPages = new RepositoryPages($this->cache, $this->api);
        $this->repositaryLumens = new RepositoryLumens($this->cache, $this->api);
        $this->repositaryCTAs = new RepositoryCTA($this->cache);
        $this->repositarySurveys = new RepositorySurveys($this->cache, new Surveys($this->repositarySettings));
        // initialise settings and users
        Action::add('init', array($this, 'init'), 1);
        // admin constructor
        Action::add('current_screen', array($this, 'adminCurrentScreen'), 10, 1);
        Action::add('admin_init', array($this, 'adminInit'));
        Action::add('init', array($this, 'adminUI'));
        Action::add('init', array($this, 'adminPostTypes'));
        Action::add('admin_menu', array($this, 'adminMenu'), 99);
        Action::add('admin_notices', array ($this, 'adminNotices'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
        Action::add('admin_head', array($this, 'adminHead'), 10);
        Action::add('do_meta_boxes', array($this, 'removeMetaboxes'), 10000); // remove metaboxes
        Action::add('wp_print_scripts', array($this, 'removeDequeue'), 10000); // remove hooks colliding
        // we need this for dashicons fallback
        Filter::add('admin_body_class', array($this, 'adminBodyClass'), 10, 1);
        // Post edit and Preview Modal
        Filter::add('redirect_post_location', function($location, $post){
            if(isset($_POST['previewModal'])){
                $location = Utils::addQueryParam($location, 'previewModal', 'true');
            }
            if(isset($_POST['previewLandingPage'])){
                $location = Utils::addQueryParam($location, 'previewLandingPage', 'true');
            }
            return $location;
        }, 10, 2);
    }

    /**
     * Init variables
     */
    public function init()
    {
        global $WPME_API;
        // We first need to get these, but only after init,
        // to retrieve all custom post types correclty
        $this->user = new RepositoryUser();
        $this->settings = new Settings($this->repositarySettings, $this->api);
    }

    /**
     * Admin Head used only to open new preview landing page window
     */

    public function adminHead()
    {
        // Global post
        global $post;
        // Is preview landing page?
        if(isset($_GET) && is_array($_GET) && array_key_exists('previewLandingPage', $_GET)){
            if(isset($post) && $post instanceof \WP_Post && isset($post->post_type)){
                if($post->post_type == 'wpme-landing-pages'){
                    // url
                    $url = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
                    $url = RepositoryLandingPages::base() . $url;
                    $location = $url;
                    // We have lanidng page URL, let's see
                    ?>
                    <script type="text/javascript"> var win = window.open('<?php echo $url; ?>', '_blank'); win.focus(); </script>
                    <?php
                }
            }
        }
    }


    /**
     * Enqueue Scripts
     */

    public function adminEnqueueScripts($hook)
    {
        // scripts
        wp_enqueue_style('core', GENOO_ASSETS . 'GenooAdmin.css', null, GENOO_REFRESH);
        wp_enqueue_script('Genoo', GENOO_ASSETS . 'Genoo.js', null, GENOO_REFRESH, true);
        // if post edit or add screeen
        if($hook == 'post-new.php' || $hook == 'post.php'){
            wp_enqueue_script('GenooEditPost', GENOO_ASSETS . 'GenooEditPosts.js', array('jquery'), GENOO_REFRESH);
        }
        // if setup up add vars
        if(GENOO_SETUP){
            wp_localize_script('Genoo', 'GenooVars', array(
                'GenooSettings' => array(
                    'GENOO_PART_SETUP' => GENOO_PART_SETUP,
                    'GENOO_SETUP' => GENOO_SETUP,
                    'GENOO_LUMENS' => GENOO_LUMENS
                ),
                'DOMAIN' => GENOO_DOMAIN,
                'AJAX' => admin_url('admin-ajax.php'),
                'GenooPluginUrl' => GENOO_ASSETS,
                'GenooMessages'  => array(
                    'importing'  => __('Importing...', 'genoo'),
                ),
                'EDITOR' => array(
                    'CTA' => $this->repositaryCTAs->getArrayTinyMCE(),
                    'Form' => $this->repositaryForms->getFormsArrayTinyMCE(),
                    'Lumens' => $this->repositaryLumens->getLumensArrayTinyMCE(),
                    'Survey' => $this->repositarySurveys->getSurveysArrayTinyMCE(),
                    'Themes' => $this->repositarySettings->getSettingsThemesArrayTinyMCE(),
                )
            ));
            // Register editor styles
            add_editor_style(WPMKTENGINE_ASSETS . 'GenooEditor.css?v=' . WPMKTENGINE_REFRESH);
//            TinyMCE::register($this->repositarySettings->getCTAPostTypes());
        } else {
            wp_localize_script('Genoo', 'GenooVars', array(
                'GenooSettings' => array(
                    'GENOO_PART_SETUP' => GENOO_PART_SETUP,
                    'GENOO_SETUP' => GENOO_SETUP,
                    'GENOO_LUMENS' => GENOO_LUMENS
                ),
                'DOMAIN' => GENOO_DOMAIN,
                'AJAX' => admin_url('admin-ajax.php'),
                'GenooPluginUrl' => GENOO_ASSETS,
                'GenooMessages'  => array(
                    'importing'  => __('Importing...', 'genoo'),
                )
            ));
        }
    }


    /**
     * Admin body class
     * - used to add lower than 3.8, dashicons edit
     *
     * @param $classes
     * @return mixed
     */
    public function adminBodyClass($classes)
    {
        global $wp_version;
        if (version_compare($wp_version, '3.8', '<' )){
            // note that admin body classes use string, instead of an array
            // but that might change one day so ...
            if(empty($classes)){
                $classes = 'version-lower-than-3-8';
            } elseif(is_string($classes)){
                $classes .= 'version-lower-than-3-8';
            } elseif(is_array($classes)) {
                $classes[] = 'version-lower-than-3-8';
            }
        }
        return $classes;
    }


    /**
     * Current screen
     */

    public function adminCurrentScreen($currentScreen)
    {
        // Load libs on excact screens only
        switch($currentScreen->id){
            case 'genoo_page_GenooForms':
                $this->tableForms = new TableForms($this->repositaryForms, $this->repositarySettings);
                break;
            case 'genoo_page_GenooLumens':
                $this->tableLumens = new TableLumens($this->repositaryLumens, $this->repositarySettings);
                break;
            case 'genoo_page_GenooSurveys':
                $this->tableSurveys = new TableSurveys($this->repositarySurveys);
                break;
            case 'genoo_page_WPMKTENGINEPages':
                $this->tablePages = new TablePages($this->repositaryPages, $this->repositarySettings);
                break;
            case 'widgets':
                wp_enqueue_media();
                break;
        }
    }


    /**
     * Admin Init
     */

    public function adminInit()
    {
        /**
         * 1. Check and hide user nag, if set + Check tool's requests
         */

        Nag::check(array('hideGenooNag', 'hideGenooApi', 'hideGenooSidebar'));
        Tools::check(array('genooActionImport', 'genooActionFlush', 'genooActionDelete', 'genooActionValidate', 'genooActionCheck'));

        /**
         * 2. Check if set up, display nag if not
         */

        if(!GENOO_SETUP && !Nag::visible('hideGenooNag')){
            $msgPluginLink = ' ' . Nag::adminLink(__('Genoo settings page.', 'genoo'), 'Genoo') . ' | ';
            $msgHideLink = Nag::hideLink(__('Hide this warning.', 'genoo'), 'hideGenooNag');
            $this->addNotice('error', sprintf(__('Genoo plugin requires setting up your API key, tracking code and comment lead type to run correctly.', 'genoo')) . $msgPluginLink . $msgHideLink);
        }

        /**
         * 3. Check sideber ID compatibility
         */

        $this->adminCheckSidebars();

        /**
         * 4. Plugin meta links
         */

        Filter::add('plugin_action_links',   array($this, 'adminPluginLinks'), 10, 2);
        Filter::add('plugin_row_meta',       array($this, 'adminPluginMeta'),  10, 2);
    }


    /**
     * Check sidebars compatibility
     */

    public function adminCheckSidebars()
    {
        global $wp_registered_sidebars;
        $errors = array();
        // Go through sidebars
        if(isset($wp_registered_sidebars) && is_array($wp_registered_sidebars)){
            // We have sidebars
            foreach($wp_registered_sidebars as $sidebar_id => $sidebar_info){
                if(strtolower($sidebar_id) != $sidebar_id){
                    $errors[] = $sidebar_id;
                }
            }
        }
        if(!empty($errors)){
            if(!Nag::visible('hideGenooSidebar')){
                $msgHideLink = Nag::hideLink(__('Hide this warning.', 'genoo'), 'hideGenooSidebar');
                $this->addNotice('error', sprintf(__('Genoo plugin has found that some of your sidebars use camel-case style as their ID.  This might cause a conflict and make your widgets dissapear. We recommend that you change the sidebar ID to all lower case.  The sidebars in question are: ', 'genoo')) . '<span style="text-decoration: underline;">' . substr(implode(', ', $errors), 0, -2) . '</span>' . ' | ' . $msgHideLink);
            }
        }
    }

    /**
     * Admin Menu
     */

    public function adminMenu()
    {
        // Admin menus
        global $menu;
        global $submenu;
        // Admin Pages
        add_menu_page('Settings', 'Genoo', 'manage_options', 'Genoo', array($this, 'renderGenooSettings'), GENOO_ASSETS . 'bgMenuIconSingle.png', '71');
        if(GENOO_SETUP){
            add_submenu_page('Genoo', 'Forms', 'Forms', 'manage_options', 'GenooForms', array($this, 'renderGenooForms'));
            add_submenu_page('Genoo', 'Surveys', 'Surveys', 'manage_options', 'GenooSurveys', array($this, 'renderGenooSurveys'));
            add_submenu_page('Genoo', 'Page Builder', 'Page Builder', 'manage_options', 'WPMKTENGINEPages', array($this, 'renderGenooPages'));
            if(GENOO_LUMENS){ add_submenu_page('Genoo', 'Lumens', 'Lumens', 'manage_options', 'GenooLumens', array($this, 'renderGenooLumens')); }
            add_submenu_page('Genoo', 'Tools', 'Tools', 'manage_options', 'GenooTools', array($this, 'renderGenooTools'));
        }
        // Admin top menu order, find where are we
        if(GENOO_SETUP && isset($submenu['Genoo'])){
            // Reapend first menu
            $wpmkteMenu = array();
            $wpmkteMenu[] = 'Genoo';
            $wpmkteMenu[] = 'manage_options';
            $wpmkteMenu[] = 'Genoo';
            $wpmkteMenu[] = 'Settings';
            // Add login
            array_unshift($submenu['Genoo'], $wpmkteMenu);
            // Moving Page Builder
            $wpmkteMenu = $submenu['Genoo'][4];
            unset($submenu['Genoo'][4]);
            $submenu['Genoo'] = \WPMKTENGINE\Utils\ArrayObject::appendTo($submenu['Genoo'], 2, $wpmkteMenu);
            // Order game
            \WPMKTENGINE\Utils\ArrayObject::moveFromPositionToPosition($submenu['Genoo'], 2, 1);
            \WPMKTENGINE\Utils\ArrayObject::moveFromPositionToPosition($submenu['Genoo'], 4, 3);
            \WPMKTENGINE\Utils\ArrayObject::moveFromPositionToPosition($submenu['Genoo'], 5, 4);
            \WPMKTENGINE\Utils\ArrayObject::moveFromPositionToPosition($submenu['Genoo'], 6, 5);
        }
    }

    /**
     * Remove metaboxes from our post types
     */
    public function removeMetaboxes()
    {
	    // Listly
        remove_meta_box('ListlyMetaBox', 'wpme-landing-pages', 'side');
        remove_meta_box('ListlyMetaBox', 'cta', 'side');
        remove_meta_box('ListlyMetaBox', 'wpme-styles', 'side');
	    // Redirect
        remove_meta_box('edit-box-ppr', 'wpme-styles', 'normal');
        remove_meta_box('edit-box-ppr', 'cta', 'normal');
        remove_meta_box('edit-box-ppr', 'wpme-landing-pages', 'normal');
	    // Yoast SEO
	    remove_meta_box('wpseo_meta', 'cta', 'normal');
	    remove_meta_box('wpseo_meta', 'wpme-styles', 'normal');
    }


	/**
	 * Remove hooks colliding
	 */
	public function removeDequeue()
	{
		// Yoast SEo
		global $post;
		if(is_admin()){
			if((is_array($_GET) && array_key_exists('post_type', $_GET) && $_GET['post_type'] == 'wpme-styles')
				||
				(is_object($post) && $post->post_type == 'wpme-styles')
			){
				wp_dequeue_script('wp-seo-metabox');
				wp_dequeue_script('wpseo-admin-media');
				wp_dequeue_script('yoast-seo');
				wp_dequeue_script('wp-seo-post-scraper');
				wp_dequeue_script('wp-seo-replacevar-plugin');
				wp_dequeue_script('wp-seo-shortcode-plugin');
				wp_dequeue_script('wp-seo-post-scraper');
				wp_dequeue_script('wp-seo-replacevar-plugin');
				wp_dequeue_script('wp-seo-shortcode-plugin');
				wp_dequeue_script('wp-seo-featured-image');
				wp_dequeue_script('wp-seo-metabox');
			}
		}
	}


    /**
     * Admin post types
     */

    public function adminPostTypes()
    {
        if(GENOO_SETUP){
            // Post Type
            new PostType('wpme_landing_pages',
                array(
                    'supports' => array('title'),
                    'label' => __('Landing Pages', 'genoo'),
                    'labels' => array(
                        'add_new' => __('New Landing Page', 'genoo'),
                        'not_found' => __('No Landing Pages found', 'genoo'),
                        'not_found_in_trash' => __('No Landing Pages found in Trash', 'genoo'),
                        'edit_item' => __('Edit Landing Page', 'genoo'),
                        'add_new_item' => __('Add new Landing Page', 'genoo'),
                    ),
                    'public' => true,
                    'exclude_from_search' => false,
                    'publicly_queryable' => false,
                    'show_ui' => true,
                    'show_in_nav_menus' => false,
                    'show_in_menu' => 'Genoo',
                    'show_in_admin_bar' => false,
                )
            );
            // Custom updated messages
            Filter::add('post_updated_messages', function($messages){
                global $post;
                $link = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
                $link = RepositoryLandingPages::base() . $link;
                $linkAppend = '&nbsp;|&nbsp;<a href="'. $link .'">' . __('View Landing Page.', 'wpmktengine') . '</a>';
                $messages['wpme-landing-pages'][1] = __('Landing Page updated.', 'genoo') . $linkAppend;
                $messages['wpme-landing-pages'][4] = __('Landing Page updated.', 'genoo');
                $messages['wpme-landing-pages'][6] = __('Landing Page published.', 'genoo') . $linkAppend;
                $messages['wpme-landing-pages'][7] = __('Landing Page saved.', 'genoo');
                $messages['wpme-landing-pages'][8] = __('Landing Page submitted.', 'genoo') . $linkAppend;
                $messages['wpme-landing-pages'][9] = __('Landing Page scheduled.', 'genoo');
                $messages['wpme-landing-pages'][10] = __('Landing Page updated.', 'genoo') . $linkAppend;
                // Return
                return $messages;
            }, 10, 1);
            // Post Type
            new PostType('wpme_styles',
                array(
                    'supports' => array('title'),
                    'label' => __('Styles', 'wpmktengine'),
                    'labels' => array(
                        'add_new' => __('New Style', 'wpmktengine'),
                        'not_found' => __('No Styles found', 'wpmktengine'),
                        'not_found_in_trash' => __('No Styles found in Trash', 'wpmktengine'),
                        'edit_item' => __('Edit Style', 'wpmktengine'),
                        'add_new_item' => __('Add new Style', 'wpmktengine'),
                    ),
                    'public' => true,
                    'exclude_from_search' => false,
                    'publicly_queryable' => false,
                    'show_ui' => true,
                    'show_in_nav_menus' => false,
                    'show_in_menu' => 'Genoo',
                    'show_in_admin_bar' => false,
                )
            );
            // Add Post Type Columns
            PostType::columns('cta', array('cta_type' => 'Type'), __('CTA Title', 'wpmktengine'));
            PostType::columns('wpme-landing-pages', array('wpmktengine_landing_url' => 'Url', 'wpmktengine_landing_template' => 'Page ID', 'setup' => 'Correctly Setup', 'wpmktengine_landing_active' => 'Active', 'wpmktengine_landing_homepage' => 'Homepage'), __('Title', 'genoo'));
            // Add Post Type Columns Content
            PostType::columnsContent('cta', array('cta_type'));
            PostType::columnsContent('wpme-landing-pages', array('wpmktengine_landing_url', 'wpmktengine_landing_template', 'setup', 'wpmktengine_landing_active', 'wpmktengine_landing_homepage', 'wpmktengine_landing_redirect_active'), function($column, $post){
                $meta = get_post_meta($post->ID, $column, TRUE);
                if($column == 'wpmktengine_landing_url'){
                    echo RepositoryLandingPages::base() . $meta;
                } elseif($column == 'setup'){
                    $metaTemplate = get_post_meta($post->ID, 'wpmktengine_landing_template', TRUE);
                    $metaUrl = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
                    $validTemplate = !empty($metaTemplate) ? TRUE : FALSE;
                    $validUrl = !empty($metaUrl) && filter_var(RepositoryLandingPages::base() . $metaUrl, FILTER_VALIDATE_URL) === FALSE ? FALSE : TRUE;
                    if($validUrl && $validTemplate){
                        echo '<span class="genooTick active">&nbsp;</span>';
                    } else {
                        echo '<span class="genooCross">&times;</span>';
                    }
                } elseif($column == 'wpmktengine_landing_active'){
                    if($meta == 'true'){
                        echo '<span class="genooTick active">&nbsp;</span>';
                    } else {
                        echo '<span class="genooCross">&times;</span>';
                    }
                } elseif($column == 'wpmktengine_landing_redirect_active'){
                    $metaUrl = get_post_meta($post->ID, 'wpmktengine_landing_redirect_url', TRUE);
                    if($meta == 'true'){
                        echo '<span class="genooTick active">&nbsp;</span>';
                        echo '<br />Redirects to: <strong>'. $metaUrl  .'</strong>';
                    } else {
                        echo '<span class="genooCross">&times;</span>';
                    }
                } elseif($column == 'wpmktengine_landing_homepage'){
                    if($meta == 'true'){
                        $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
                        $realUrl = $realUrlEmpty . "?post_type=wpme-landing-pages";
                        $link = Utils::addQueryParam($realUrl, 'genooDisableLandingHomepage', $post->ID);
                        echo '<span class="genooTick active">&nbsp;</span>&nbsp;|&nbsp;<a href="'. $link .'">'. __('Disable homepage', 'wpmktengine') .'</a>';
                    } else {
                        $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
                        $realUrl = $realUrlEmpty . "?post_type=wpme-landing-pages";
                        $link = Utils::addQueryParam($realUrl, 'genooMakeLandingHomepage', $post->ID);
                        echo '<a href="'. $link .'">'. __('Make this landing page WordPress default homepage.', 'wpmktengine') .'</a>';
                    }
                } else {
                    echo $meta;
                }
            });
            Action::add('manage_posts_extra_tablenav', function($which){
                if(Utils::getParamIsset('post_type') && $_GET['post_type'] == 'wpme-landing-pages' && $which == 'top'){
                    echo '<div class="alignleft actions"><a target="_blank" class="button button-primary genooExtraNav" href="'. WPMKTENGINE_BUILDER_NEW .'">'. __('Add new Template', 'wpmktengine') .'</a></div>';
                }
            }, 10, 1);
            Filter::add('post_row_actions', function($actions, $post){
                if(isset($post) && $post instanceof \WP_Post && isset($post->post_type)){
                    if($post->post_type == 'wpme-landing-pages'){
                        // url
                        $url = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
                        $url = RepositoryLandingPages::base() . $url;
                        // Action link
                        $actions['view'] = '<a target="_blank" href="'. $url .'">'. __('View', 'wpmktengine') .'</a>';
                    }
                }
                return $actions;
            }, 10, 2);
            Action::add('current_screen', function($screen){
                if(is_object($screen) && $screen->post_type == 'wpme-landing-pages' && $screen->base == 'edit'){
                    if(array_key_exists('genooMakeLandingHomepage', $_GET) && is_numeric($_GET['genooMakeLandingHomepage'])){
                        $id = sanitize_text_field($_GET['genooMakeLandingHomepage']);
                        RepositoryLandingPages::makePageHomepage($id);
                        Action::add('admin_notices', function(){ echo Notice::type('updated')->text('Default homepage changed.'); }, 10, 1);
                    }
                    if(array_key_exists('genooDisableLandingHomepage', $_GET) && is_numeric($_GET['genooDisableLandingHomepage'])){
                        RepositoryLandingPages::removeHomepages();
                        Action::add('admin_notices', function(){ echo Notice::type('updated')->text('Default homepage turned off.'); }, 10, 1);
                    }
                }
                return;
            }, 10, 1);
        }
    }


    /**
     * Metaboxes
     */

    public function adminUI()
    {
        if(GENOO_SETUP){
            // Metaboxes
            new Metabox('Genoo CTA Info', 'cta',
                array(
                    array(
                        'type' => 'select',
                        'label' => __('CTA type', 'genoo'),
                        'options' => $this->repositarySettings->getCTADropdownTypes()
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Display CTAs', 'genoo'),
                        'options' => array(
                            '0' => __('No title and description', 'genoo'),
                            'titledesc' => __('Title and Description', 'genoo'),
                            'title' => __('Title only', 'genoo'),
                            'desc' => __('Description only', 'genoo'),
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Description', 'genoo'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Form', 'genoo'),
                        'options' => (array('' => '-- Select Form') + $this->repositaryForms->getFormsArray()),
                        'atts' => array(
                            'class' => 'bTargeted',
                            'data-target' => 'block-form'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'id' => 'form_theme',
                        'label' => __('Form Style', 'wpmktengine'),
                        'options' => ($this->repositarySettings->getSettingsThemes())
                    ),
                    array(
                        'type' => 'html',
                        'label' => __('If none of the styles fits your needs, you can create your own styles. ', 'wpmktengine') . '<a target="_blank" href="'. admin_url('post-new.php?post_type=wpme-styles') .'">' . __('Would you like to use a custom style?', 'wpmktengine') . '</a><br />',
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Follow original return URL', 'wpmktengine'),
                        'options' => (
                            array(
                                '' => 'Disable',
                                '1' => 'Enable'
                            )
                        )
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Form success message', 'genoo'),
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Form error message', 'genoo'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button URL', 'genoo'),
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => __('Open in new window?', 'genoo')
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Button Type', 'genoo'),
                        'options' => array(
                            'html' => __('HTML', 'genoo'),
                            'image' => __('Image', 'genoo'),
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button Text', 'genoo'),
                    ),
                    array(
                        'type' => 'image-select',
                        'label' => __('Button Image', 'genoo')
                    ),
                    array(
                        'type' => 'image-select',
                        'label' => __('Button Hover Image', 'genoo')
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button CSS ID', 'wpmktengine'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Button CSS Class', 'wpmktengine'),
                    ),
                    $this->repositarySettings->getLumensDropdown($this->repositaryLumens)
                ), 'normal', 'high'
            );
            // CTA metabox is now only legacy
            if(GENOO_LEGACY === TRUE){
                new Metabox('Genoo CTA', $this->repositarySettings->getCTAPostTypes(),
                    array(
                        array(
                            'type' => 'checkbox',
                            'label' => __('Enable CTA for this post', 'genoo')
                        ),
                        array(
                            'type' => 'select',
                            'label' => 'Select CTA',
                            'options' => $this->repositarySettings->getCTAs(),
                            'atts' => array('onChange' => 'Metabox.changeCTALink(this.options[this.selectedIndex].value)',)
                        ),
                    )
                );
            }
            new MetaboxCTA('Genoo Dynamic CTA', $this->repositarySettings->getCTAPostTypes(), array(), $this->repositarySettings->getCTAs());
            // Dynamic PopOver
            new Metabox('Genoo Dynamic Pop-Over', $this->repositarySettings->getCTAPostTypes(),
                array(
                    array(
                        'type' => 'select',
                        'label' => __('Enable Pop-Over to open automatically', 'genoo'),
                        'options' => array('Disable', 'Enable')
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('CTA', 'genoo'),
                        'id' => 'pop_over_cta_id',
                        'options' => $this->repositaryCTAs->getArray()
                    ),
                    array(
                        'type' => 'number',
                        'label' => __('Open Pop-Up after delay (seconds)', 'genoo'),
                        'id' => 'number_of_seconds_to_open_the_pop_up_after'
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => __('Only display to unknown leads?', 'wpmktengine'),
                        'id' => 'pop_over_only_for_unknown'
                    ),
                )
            );
            // Referer URL redirect
            new Metabox('Genoo Referer URL Redirect', array('post', 'page'),
                array(
                    array(
                        'type' => 'select',
                        'label' => __('Enable Referer Redirect', 'genoo'),
                        'options' => array('Disable', 'Enable'),
                        'id' => 'genoo_referer_redirect'
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Enable when', 'genoo'),
                        'options' => array(
                            'referer_not' => __('user has not come from referer', 'genoo'),
                            'referer_yes' => __('user has come from referer', 'genoo'),
                        ),
                        'id' => 'genoo_referer_redirect_when'
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Referer URL', 'genoo'),
                        'id' => 'genoo_referer_redirect_from_url'
                    ),
                    array(
                        'type' => 'text',
                        'label' => __('Redirect to URL', 'genoo'),
                        'id' => 'genoo_referer_redirect_url'
                    )
                )
            );
            // Landing pages UI
            // Required homepage?
            new Metabox('Settings', array('wpme-landing-pages'),
                array(
                    array(
                        'type' => 'checkbox',
                        'label' => __('Active?', 'wpmktengine'),
                        'id' => 'wpmktengine_landing_active'
                    ),
                    array(
                        'type' => 'html',
                        'label' => '<strong>' . __('Landing page URL', 'wpmktengine') . '</strong>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => RepositoryLandingPages::base(),
                        'before' => '',
                        'id' => 'wpmktengine_landing_url',
                        'atts' => array(
                            'required' => 'required',
                            'pattern' => '^[a-zA-Z0-9/_-]*$' //^[a-zA-Z0-9/_.-]*$
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'label' => __('Allowed URL characters are: ', 'wpmktengine') . '[<strong>a-z</strong>][<strong>0-9</strong>][<strong>/</strong>][<strong>_</strong>][<strong>-</strong>]'
                    ),
                    array(
                        'type' => 'html',
                        'label' => '<span style="color: red">' . __('WARNING! Make sure URL is unique across all pages and posts.', 'wpmktengine') . '</span>'
                    ),
                    array(
                        'type' => 'select',
                        'label' => __('Page template', 'wpmktengine'),
                        'options' => $this->repositaryPages->getPagesArrayDropdown(),
                        'id' => 'wpmktengine_landing_template'
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Additional header data', 'wpmktengine'),
                        'id' => 'wpmktengine_data_header'
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => __('Additional footer data', 'wpmktengine'),
                        'id' => 'wpmktengine_data_footer'
                    )
                )
            );
            // Redirect for Landing page
            new Metabox('Redirect', array('wpme-landing-pages'),
                array(
                    array(
                        'type' => 'checkbox',
                        'label' => __('Active?', 'wpmktengine'),
                        'id' => 'wpmktengine_landing_redirect_active'
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Redirect URL',
                        'id' => 'wpmktengine_landing_redirect_url',
                        'atts' => array(
                            'pattern' => '\bhttps?://[.0-9a-z-]+\.[a-z]{2,6}(?::[0-9]{1,5})?(?:/[!$\'()*+,.0-9_a-z-]+){0,9}(?:/[!$\'()*+,.0-9_a-z-]*)?(?:\?[!$&\'()*+,.0-9=_a-z-]*)?'
                        ),
                    ),

                ),
                'side'
            );
            new Metabox('Preview', array('wpme-landing-pages'),
                array(
                    array(
                        'type' => 'html',
                        'label' => '<a href="#" onclick="Metabox.appendAndFire(event, \'previewLandingPage\', \'true\');" class="button">'. __('Preview this landing page.', 'wpmktengine') .'</a>',
                    ),
                ),
                'side',
                'high'
            );
            // Metabox with content in for styler
            new MetaboxArea('Elements to style - <span style="font-weight:300;">Click on something to set how you want it to show up</span>', array('wpme-styles'));
            // Metabox in the sidebar for styler
            new Metabox('Form Properties', array('wpme-styles'), array(
                array(
                    'type' => 'checkbox',
                    'label' => __('Make labels input placeholders?', 'wpmktengine'),
                    'id' => 'wpmktengine_style_make_placeholders',
                    'atts' => array(
                        'onclick' => 'Customizer.updateLabels(event, this);',
                    ),
                ),
            ), 'side', 'default');
            new MetaboxStyles('<span class="selectedElem">Applied Style</span>', array('wpme-styles'));
        }
        return null;
    }


    /** ----------------------------------------------------- */
    /**                      Renderers                        */
    /** ----------------------------------------------------- */

    /**
     * Renders Admin Page
     */

    public function renderGenooSettings()
    {
        echo '<div class="wrap"><h2>' . __('Genoo Settings', 'genoo') . '</h2>';
            $this->settings->render();
        echo '</div>';
    }

    /**
     * Genooo sureys
     */
    public function renderGenooSurveys()
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Surveys</h1>';
        $this->tableSurveys->display();
        echo '</div>';
    }


    /**
     * Renders Admin Page
     */

    public function renderGenooForms()
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . __('Genoo Lead Capture Forms', 'genoo') . '</h1>';
            $this->tableForms->display();
        echo '</div>';
    }

    /**
     * Render Pages
     */
    public function renderGenooPages()
    {
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . __('Layout Pages', 'wpmktengine') . '</h1>';
        $this->tablePages->display();
        echo '</div>';
    }

    /**
     * Renders Lumens page
     */

    public function renderGenooLumens()
    {
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . __('Class Lists', 'genoo') . '</h1>';
            $this->tableLumens->display();
        echo '</div>';
    }


    /**
     * Renders Tools page
     */

    public function renderGenooTools()
    {
        $page = new Page();
        $page->addTitle(__('Genoo Tools', 'genoo'));
        $page->addWidget('Create Genoo Leads from WordPress Approved Comments.', Tools::getWidgetImport());
        $page->addWidget('Create Genoo Leads from WordPress	blog subscribers.', Tools::getWidgetImportSubscribers($this->api));
        $page->addWidget('Delete all cached files.', Tools::getWidgetDelete());
        $page->addWidget('Clear plugin Settings.', Tools::getWidgetFlush());
        $page->addWidget('Validate API key.', Tools::getWidgetValidate());
        $page->addWidget('Theme check.', Tools::getWidgetCheck());
        $page->addWidget('Bug Report Info.', Tools::getWidgetBug());
        $page->addWidget('Active Extensions', Tools::getActiveExtensions());
        if(isset($_GET['debug']) || isset($_COOKIE['debug'])){
            $page->addWidget('Sidebar Report', Tools::getSidebarReport());
        }
        // Add custom widgets
        apply_filters('wpmktengine_tools_widgets', $page);
        echo $page;
    }


    /** ----------------------------------------------------- */
    /**                 Plugin meta links                     */
    /** ----------------------------------------------------- */

    /**
     * Plugin action links
     *
     * @param $links
     * @param $file
     * @return mixed
     */

    public function adminPluginLinks($links, $file)
    {
        if ($file == GENOO_FILE){
            array_push($links, '<a href="' . admin_url('admin.php?page=Genoo') . '">'. __('Settings', 'genoo') .'</a>');
        }
        return $links;
    }


    /**
     * Plugin meta links
     *
     * @param $links
     * @param $file
     * @return mixed
     */

    public function adminPluginMeta($links, $file)
    {
        if ($file == GENOO_FILE){
            array_push($links, '<a target="_blank" href="http://wordpress.org/support/plugin/genoo">'. __('Support forum', 'genoo') .'</a>');
        }
        return $links;
    }


    /** ----------------------------------------------------- */
    /**               Notification system                     */
    /** ----------------------------------------------------- */

    /**
     * Adds notice to the array of notices
     *
     * @param string $tag
     * @param string $label
     */

    public function addNotice($tag = 'updated', $label = ''){ $this->notices[] = array($tag, $label); }

    /**
     * Add saved notice
     *
     * @param string $tag
     * @param string $label
     */
    public function addSavedNotice($tag = 'updated', $label = ''){ $this->repositarySettings->addSavedNotice($tag, $label); }


    /**
     * Returns all notices
     *
     * @return array
     */

    public function getNotices(){ return $this->notices; }


    /**
     * Sends notices to renderer
     */

    public function adminNotices()
    {
        // notices saved in db
        $savedNotices = $this->repositarySettings->getSavedNotices();
        if($savedNotices){
            foreach($savedNotices as $value){
                if(array_key_exists('error', $value)){
                    $this->displayAdminNotice('error', $value['error']);
                } elseif(array_key_exists('updated', $value)){
                    $this->displayAdminNotice('updated', $value['updated']);
                }
                // flush notices after display
                $this->repositarySettings->flushSavedNotices();
            }
        }
        // notices saved in this object
        foreach($this->notices as $key => $value){
            $this->displayAdminNotice($value[0], $value[1]);
        }
    }


    /**
     * Display admin notices
     *
     * @param null $class
     * @param null $text
     */

    private function displayAdminNotice($class = NULL, $text = NULL){ echo Notice::type($class)->text($text); }


    /** ----------------------------------------------------- */
    /**                    Get instance                       */
    /** ----------------------------------------------------- */

    /**
     * Does what it says, get's instance
     *
     * @return bool|Admin
     */

    public static function getInstance()
    {
        if (!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }
}