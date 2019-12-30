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

use WPME\RepositorySettingsFactory;
use WPMKTENGINE\HtmlForm;
use WPMKTENGINE\ModalWindow;
use WPMKTENGINE\RepositoryThemes;
use WPMKTENGINE\Utils\CSS;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Action;
use WPMKTENGINE\Wordpress\Filter;
use WPMKTENGINE\Wordpress\Post;
use WPMKTENGINE\Wordpress\Redirect;
use WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\Wordpress\Widgets;
use WPMKTENGINE\CTA;
use WPMKTENGINE\RepositoryLandingPages;

/**
 * Class Frontend
 * @package Genoo
 */
class Frontend
{
    /** @var RepositorySettings */
    var $repositorySettings;
    /** @var array */
    var $footerCTAModals = array();
    /** @var Api */
    var $api;
    /** @var Cache */
    var $cache;

    /**
     * Construct Frontend
     *
     * @param RepositorySettings $repositorySettings
     * @param Api $api
     * @param Cache $cache
     */
    public function __construct(\WPME\RepositorySettingsFactory $repositorySettings, \WPME\ApiFactory $api, \WPMKTENGINE\Cache $cache)
    {
        // Settings
        $this->repositorySettings = $repositorySettings;
        $this->api = $api;
        $this->cache = $cache;
        // Init
        Action::add('init',  array($this, 'init'));
        // wp
        Action::add('wp',    array($this, 'wp'), 999, 1);
        // Enqueue scripts
        Action::add('wp_enqueue_scripts', array($this, 'enqueue'), 1, 1);
        Action::add('wp_head', array($this, 'enqueueFirst'), 1, 1);
        // Footer
        Action::add('wp_footer', array($this, 'footerFirst'), 1);
        Action::add('wp_footer', array($this, 'footerLast'), 999);
        Action::add('shutdown', array($this, 'shutdown'), 10, 1);
        Action::add('template_redirect', array($this, 'template_redirect'), 10, 1);
        // Custom landing pages link
        Filter::add('post_type_link', array($this, 'getPostTypeLink'), 99, 2);
        Filter::add('post_link', array($this, 'getPostTypeLink'), 99, 2);
        // Global header && footer
        Action::add('wp_head', array('\Genoo\RepositorySettings', 'getWordPressGlobalHeader'), 100);
        Action::add('wp_footer', array('\Genoo\RepositorySettings', 'getWordPressGlobalFooter'), 100);
    }

    /**
     * Fix URL's for landing pages
    */
    public function getPostTypeLink($url, $post){
      // Exit early
      if($post && $post->post_type !== 'wpme-landing-pages'){
        return $url;
      }
      // This is a landing page
      $link = get_post_meta($post->ID, 'wpmktengine_landing_url', TRUE);
      return RepositoryLandingPages::base() . $link;
    }

    /**
     * Template Redirect
     */
    public function template_redirect()
    {
        // Get post
        global $post;
        if(isset($post) && ($post instanceof \WP_Post)){
            // Get rest of data
            $referer = (isset($_SERVER) && (!empty($_SERVER['HTTP_REFERER']))) ? $_SERVER['HTTP_REFERER'] : FALSE;
            // If no referer, no redirect too
            if($referer !== FALSE){
                // Post meta
                $referer_redirect = get_post_meta($post->ID, 'genoo_referer_redirect', TRUE);
                $referer_redirect_when = get_post_meta($post->ID, 'genoo_referer_redirect_when', TRUE);
                $referer_redirect_from_url = get_post_meta($post->ID, 'genoo_referer_redirect_from_url', TRUE);
                $referer_redirect_url = get_post_meta($post->ID, 'genoo_referer_redirect_url', TRUE);
                // If enabled
                if($referer_redirect == 1){
                    // Hello, enabled, let's check out the magic
                    // both urls, referer and
                    if(!empty($referer_redirect_from_url) && (!empty($referer_redirect_url)) && (!empty($referer_redirect_when))){
                        // Everything in place, let's check the logic of this action
                        if($referer_redirect_when == 'referer_not'){
                            // IF User has not come from a referer URL
                            if($referer !== $referer_redirect_from_url){
                                // Redirect
                                Redirect::code(302)->to($referer_redirect_url);
                            }
                        } elseif($referer_redirect_when == 'referer_yes'){
                            // IF user has come from a referer URL
                            if($referer == $referer_redirect_from_url){
                                // Redirect
                                Redirect::code(302)->to($referer_redirect_url);
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Init, rewrite rules for mobiles windows
     */
    public function init()
    {
        Filter::add('query_vars', function($query_vars){
            $query_vars[] = 'genooMobileWindow';
            $query_vars[] = 'genooIframe';
            $query_vars[] = 'genooIframeLumen';
            $query_vars[] = 'genooIframeCTA';
            $query_vars[] = 'genooIframeBuidler';
            $query_vars[] = 'genooFlushPages';
            $query_vars[] = 'genooFlushPagesKey';
            $query_vars[] = 'genooFlush';
            $query_vars[] = 'genooFlushKey';
            $query_vars[] = 'genooScriptsQueue';
            return $query_vars;
        }, 10, 1);
        $that = $this;
        Action::add('parse_request', function($wp) use($that){
            // Feed?
            if(is_array($wp->query_vars) && array_key_exists('feed', $wp->query_vars)){ return; }
            // Flush pages
            if(array_key_exists('genooFlushPages', $wp->query_vars) && array_key_exists('genooFlushPagesKey', $wp->query_vars)){
                if($wp->query_vars['genooFlushPagesKey'] == sha1('wpme_refresh_pages')){
                    header('Access-Control-Allow-Origin: *');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Content-Type: text/html; charset=utf-8');
                    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                    try {
                        header($protocol . ' 200 OK');
                        $GLOBALS['http_response_code'] = 200;
                        $pages = new \WPMKTENGINE\RepositoryPages($that->cache, $that->api);
                        $pages->flush();
                        $pages->getPages();
                    } catch (\Exception $e){
                        header($protocol . ' 400 Bad Request');
                        $GLOBALS['http_response_code'] = 400;
                        echo $e->getMessage();
                    }
                    header('X-Wpme-Flush-Happened: 1');
                    header('HTTP/1.0 204 No Content');
                    exit;
                }
                // Only when query parsed do this
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
            // Flush something
            if(array_key_exists('genooFlush', $wp->query_vars) && array_key_exists('genooFlushKey', $wp->query_vars)){
                if($wp->query_vars['genooFlushKey'] === sha1('wpme_refresh_pages')){
                    header('Access-Control-Allow-Origin: *');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Content-Type: text/html; charset=utf-8');
                    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                    try {
                        header($protocol . ' 200 OK');
                        $GLOBALS['http_response_code'] = 200;
                        switch($wp->query_vars['genooFlush']){
                          case 'forms';
                              $pages = new \WPMKTENGINE\RepositoryForms($that->cache, $that->api);
                              $pages->flush();
                              $pages->getForms();
                          break;
                        }
                    } catch (\Exception $e){
                        header($protocol . ' 400 Bad Request');
                        $GLOBALS['http_response_code'] = 400;
                        echo $e->getMessage();
                    }
                    header('X-Wpme-Flush-Happened: 1');
                    header('HTTP/1.0 204 No Content');
                    exit;
                }
                // Only when query parsed do this
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
            // Scripts JSON
            if(array_key_exists('genooScriptsQueue', $wp->query_vars) && array_key_exists('genooScriptsQueue', $wp->query_vars)){
                // Scripts JSON
                wp_dequeue_script('genooFrontend');
                wp_dequeue_script('genooFrontendJs');
                wp_deregister_script('genooFrontendJs');
                wp_deregister_script('genooFrontend');
                // Enquee
                ob_start();
                do_action('wp_enqueue_scripts');
                do_action('wp_enqueue_styles');
                do_action('wp_print_styles');
                do_action('wp_print_scripts');
                ob_end_clean();
                // Get current
                global $wp_scripts, $wp_styles;
                $q = new \stdClass();
                $q->styles = $wp_styles->queue;
                $q->scripts = $wp_scripts->queue;
                if(($key = array_search('genooFrontend', $q->styles)) !== false) {
                    unset($q->styles[$key]);
                }
                if(($key = array_search('genooFrontendJs', $q->scripts)) !== false) {
                    unset($q->scripts[$key]);
                }
                \WPME\WPApi\Base::sendJsonHeader();
                echo json_encode($q);
                exit;
            }
            // If is mobile window
            if(array_key_exists('genooMobileWindow', $wp->query_vars)){
                // Only when query parsed do this
                Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                Frontend::renderMobileWindow();
            }
            // If iframe load for backend, its safe to assume
            // that only logged in users will hava access to tinyMCE editor
            if(array_key_exists('genooIframe', $wp->query_vars) && is_user_logged_in()){
                // Only continue if file actually exists and iframe not empty
                if(!empty($wp->query_vars['genooIframe']) && file_exists(GENOO_ASSETS_DIR . $wp->query_vars['genooIframe'])){
                    // Since this could be potentionally hazardous, to display just any PHP file that is in the folder
                    // we will check if its GenooTinyMCE file first, just to be safe, and of course just those PHP iframe files, not any others.
                    if(Strings::startsWith($wp->query_vars['genooIframe'], 'GenooTinyMCE') && Strings::endsWith($wp->query_vars['genooIframe'], '.php')){
                        // No we have a winner.
                        Frontend::renderTinyMCEIframe($wp->query_vars['genooIframe']);
                    }
                }
            }
            // Genoo preview iframe
            if(array_key_exists('genooIframeLumen', $wp->query_vars) && is_user_logged_in()){
                // This workaround needs id and script source to dispaly the script
                if((isset($_GET['genooIframeLumenSrc']) && !empty($_GET['genooIframeLumenSrc'])) && (!empty($wp->query_vars['genooIframeLumen']))){
                    // Seems like a winner, display content
                    Frontend::renderPreviewLumenIframe($wp->query_vars['genooIframeLumen'], $_GET['genooIframeLumenSrc']);
                }
            }
            // Genoo preview iframe for CTA
            if(array_key_exists('genooIframeCTA', $wp->query_vars) && is_user_logged_in()){
                // This workaround needs id and script source to dispaly the script
                // Only when query parsed do this
                try {
                    error_reporting(0);
                    ini_set('error_reporting', 0);
                    // Set through widget
                    $widget = new \WPMKTENGINE\WidgetCTA(false);
                    $widget->setThroughShortcode(1, $wp->query_vars['genooIframeCTA'], array());
                    $class = '';
                    if($widget->cta->popup['image-on']){
                        $image = wp_get_attachment_image($widget->cta->popup['image'], 'medium', FALSE);
                        if($image){
                            $class = 'genooModalPopBig';
                        }
                    }
                    if(method_exists($widget, 'getCTAModalClass') && (method_exists($widget, 'getInnerInstance'))){
                        $class .= ' ' . $widget->getCTAModalClass($widget->getInnerInstance());
                    }
                    // Set HTML
                    $r = '<div aria-hidden="false" id="genooOverlay" class="visible">';
                    $r .= '<div id="modalWindowGenoodynamiccta1" tabindex="-5" role="dialog" class="genooModal '. $class .' visible renderedVisible "><div class="relative">';
                    $r .= $widget->getHtml();
                    $r .= '</div></div>';
                    $r .= '</div>';
                    // Display!
                    //Filter::removeFrom('wp_head')->everythingExceptLike(array('style', 'script'));
                    // TODO: Discover what's causing issue with the above line
                    Frontend::renderMobileWindow('Preview', $r, 'genooPreviewModal');
                } catch (\Exception $e){
                    echo $e->getMessage();
                }
                exit;
            }
            // WPMKTENGINE preview Builder
            if(array_key_exists('genooIframeBuidler', $wp->query_vars) && is_user_logged_in()){
                // This workaround needs id and script source to dispaly the script
                // Only when query parsed do this
                $id = $wp->query_vars['genooIframeBuidler'];
                $that->renderPageTemplate($id);
                exit;
            }
            // WPMKTENGINE Landing pages
            $redirects = new \WPMKTENGINE\RepositoryLandingPages();
            if($redirects->has() && !wp_doing_ajax()){
                $does = $redirects->fitsUrl(Utils::getRealUrl());
                if($does !== FALSE){
                    // OK, it seems like we have a winner
                    $page = $does->page;
                    // Only if landing page is active
                    if(isset($page->meta->wpmktengine_landing_active) && $page->meta->wpmktengine_landing_active == 'true'){
                        // If redirect?
                        if(isset($page->meta->wpmktengine_landing_redirect_active)
                            &&
                            $page->meta->wpmktengine_landing_redirect_active == 'true'
                            &&
                            isset($page->meta->wpmktengine_landing_redirect_url)
                            &&
                            !filter_var($page->meta->wpmktengine_landing_redirect_url, FILTER_VALIDATE_URL) === false
                        ){
                            // Ok we have a valid URl, and we can redirect indeeed
                            Redirect::code(301)->to($page->meta->wpmktengine_landing_redirect_url);
                            // Else if?
                        } else if(isset($does->page->meta->wpmktengine_landing_template) && !empty($does->page->meta->wpmktengine_landing_template)){
                            $pageTemplate = $does->page->meta->wpmktengine_landing_template;
                            $pageTemplateHeader = isset($does->page->meta->wpmktengine_data_header) ? $does->page->meta->wpmktengine_data_header : '';
                            $pageTemplateFooter = isset($does->page->meta->wpmktengine_data_footer) ? $does->page->meta->wpmktengine_data_footer : '';
                            // Render
                            $that->renderLandingPage(
                                $page,
                                $pageTemplate,
                                $pageTemplateHeader,
                                $pageTemplateFooter
                            );
                        }
                    }
                }
            }
        });
    }


    /**
     * On Wp, let's register our CTA widgets,
     * if they are present
     *
     * @param $wp
     */
    public function wp($wp)
    {
        // Global post
        global $post;
        // Firstly we do not run this anytime other then on real frontend
        if(Utils::isSafeFrontend()){
            // Do we have a post
            if($post instanceof \WP_Post){
                // We only run this on single posts
                if((Post::isSingle() || Post::isPage()) && Post::isPostType($post, $this->repositorySettings->getCTAPostTypes())){
                    // Dynamic cta
                    $cta = new \WPMKTENGINE\CTADynamic($post);
                    // If the post does have multiple ctas, continue
                    if($cta->hasMultiple()){
                        // Set we have multiple CTAs
                        $this->hasMultipleCTAs = true;
                        // Get CTA's
                        $ctas = $cta->getCtas();
                        $ctasRegister = $cta->getCtasRegister();
                        // Injects widgets, registers them
                        $ctasWidgetsRegistered = Widgets::injectRegisterWidgets($ctasRegister);
                        // Save for footer print
                        $this->footerCTAModals = $ctasWidgetsRegistered;
                        // Repositions them
                        Widgets::injectMultipleIntoSidebar($ctasWidgetsRegistered);
                        // Pre-option values
                        Widgets::injectMultipleValues($ctasWidgetsRegistered);
                    }
                }
            }
        }
    }


    /**
     * Enqueue
     */
    public function enqueue()
    {
        // Frontend css
        wp_enqueue_style('genooFrontend', GENOO_ASSETS . 'GenooFrontend.css', NULL, GENOO_REFRESH);
        // Frontend js, if not a mobile window
        if(!isset($_GET['genooMobileWindow'])){
            wp_register_script('genooFrontendJs', GENOO_ASSETS . "GenooFrontend.js", FALSE, GENOO_REFRESH, FALSE);
            wp_enqueue_script('genooFrontendJs');
        }
    }


    /**
     * First
     */
    public function enqueueFirst()
    {
        // Tracking code
        if(GENOO_SETUP){
            $inHeader = apply_filters('genoo_tracking_in_header', FALSE);
            if($inHeader == TRUE){
                // Get repo
                echo $this->repositorySettings->getTrackingCode();
            }
        }
        // Get header styles
        $repositoryThemes = new \WPMKTENGINE\RepositoryThemes();
        $css = $repositoryThemes->getAllThemesStyles();
        if(!empty($css)){
            echo CSS::START;
            echo $css;
            echo CSS::END;
        }
    }


    /**
     * Footer first
     */
    public function footerFirst()
    {
        // Tracking code
        if(GENOO_SETUP){
            $inHeader = apply_filters('genoo_tracking_in_header', FALSE);
            // Get repo
            if($inHeader == FALSE){
                echo $this->repositorySettings->getTrackingCode();
            }
            global $GENOO_STYLES;
            echo $GENOO_STYLES;
            // Prints JS if needed
            $repositoryThemes = new \WPMKTENGINE\RepositoryThemes();
            $repositoryThemes->getAllThemesJavascript();
        }
    }


    /**
     * Footer last
     */
    public function footerLast()
    {
        // Get post / page
        global $post;
        // Footer modals
        $footerPopOverData = CTA::getFooterPopOvers();
        $footerWidgetsDynamicForms = Widgets::getFooterDynamicModals($this->footerCTAModals);
        $footerForms = $footerPopOverData + $footerWidgetsDynamicForms;
        // If we have global ones, add them in.
        if(isset($GLOBALS['WPME_MODALS']) && !empty($GLOBALS['WPME_MODALS'])){
            $footerForms = $footerForms + $GLOBALS['WPME_MODALS'];
        }
        // Prepare modals
        $footerModals = new \WPMKTENGINE\ModalWindow();        // footer widgtes
        if(!empty($footerForms)){
            // go through widgers
            foreach($footerForms as $id => $widget){
                if(method_exists($widget->widget, 'getHtml')){

                    // Is custom theme
                    $isCustomTheme = true;
                    $isCTA = false;
                    $templater = new \WPME\Customizer\Generator\Template();

                    // CTA, or plain form?
                    if(isset($widget->widget->cta->id)){
                        // Cta?
                        $isCTA = true;
                        // Add cta id to instance
                        $ctaId = $widget->widget->cta->id;
                        $isCustomThemeName = $templater->getModalTheme($ctaId);
                        // Cta data
                        $cta = get_post($ctaId);
                        $ctaTemplate = $isCustomThemeName;
                        $ctaModalClass = 'gn-custom-modal gn-custom-modal-' . $isCustomThemeName . ' ';
                        if(method_exists($widget->widget, 'getCTAModalClass')){
                            $ctaModalClass .= $widget->widget->getCTAModalClass($widget->instance);
                        }
                        $ctaId = $cta->ID;
                    } else {
                        $isCustomThemeName = 'default';
                        $ctaTemplate = $isCustomThemeName;
                        $ctaModalClass = 'gn-custom-modal gn-custom-modal-' . $isCustomThemeName . ' ';
                    }

                    // This is custom modal them, hell yeah
                    // Get controls for this theme

                    // Create a dummy wp_customize, that we can use and make it global
                    $GLOBALS['wp_customize'] = new \WPME\Customizer\DummyCustomize();

                    // Get controls for given template
                    $controls = \WPME\Customizer\CustomizerExtension::getCustomizerTemplateControls($ctaTemplate);
                    if(!file_exists($controls)){
                        continue;
                    }

                    // Require it, which also inits the whole process
                    include $controls;

                    // WPME_CUSTOMZIER should now be a global object, use it
                    global $WPME_CUSTOMIZER;

                    // Now that we have global WPME_CUSTOMIZER with sections and data,
                    // we can use tempalte storage, to get the tempalte and it's styles.
                    // Let's check if we've created this object before or not.
                    global $WPME_TEMPLATE_STORAGE;
                    if(!isset($WPME_TEMPLATE_STORAGE) || (!$WPME_TEMPLATE_STORAGE instanceof \WPME\Customizer\TemplateStorage)){
                        // If we don't have template storage, or if it's not our Tempalte storage for some reason
                        $templateStorage = new \WPME\Customizer\TemplateStorage();
                        // Set as global for next call or next CTA using a template
                        $GLOBALS['WPME_TEMPLATE_STORAGE'] = $templateStorage;
                    } else {
                        $templateStorage = $WPME_TEMPLATE_STORAGE;
                    }

                    // We have a Customizer and we have a Template Storage, now we should be on our way
                    // Cta is easy, pregenrated
                    if($isCTA){
                        $template = $templateStorage->prepareTemplateForModalWindow(
                            $WPME_CUSTOMIZER,
                            $cta,
                            '#' . \WPMKTENGINE\ModalWindow::getModalId($id),
                            $ctaTemplate
                        );
                    } else {
                        // If it's a form, we need to inject some data in
                        // Get theme
                        $theme = isset($widget->instance['theme']) ? $widget->instance['theme'] : 'themeDefault';
                        $title = isset($widget->instance['title']) ? $widget->instance['title'] : '';
                        $formId = $widget->instance['form'];
                        // Inject data in
                        $view = $WPME_CUSTOMIZER->getView($WPME_CUSTOMIZER->getFirstViewId());
                        $view->setValueOf('form_theme', $theme);
                        $view->setValueOf('post_title', $title);
                        $view->setValueOf('description', '');
                        // Set form
                        $WPME_CUSTOMIZER->setFormID($formId);
                        // Get template
                        $template = $templateStorage->prepareTemplateForModalWindowForm(
                            $WPME_CUSTOMIZER,
                            $formId,
                            '#' . \WPMKTENGINE\ModalWindow::getModalId($id)
                        );
                    }

                    // If we have date, let's do it
                    if(!empty($template)){

                        // inject hidden inputs first
                        $modalGutsInject = new HtmlForm($template);
                        if(isset($widget->widget->cta) && isset($widget->widget->cta->followOriginalUrl) && ($widget->widget->cta->followOriginalUrl == TRUE)){
                            // do not inject anything
                        } else {
                            $modalGutsInject->appendHiddenInputs(array('popup' => 'true', 'returnModalUrl' => ModalWindow::getReturnUrl($id)));
                        }
                        // inject message
                        $modalResult = ModalWindow::modalFormResult($id);
                        $modalResultClass = '';
                        $repositorySettings = new \WPME\RepositorySettingsFactory();
                        // do we have a result?
                        if(($modalResult == true || $modalResult == false) && (!is_null($modalResult))){
                            // instance messages
                            $widgetMsgSuccess = !empty($widget->instance['msgSuccess']) ? $widget->instance['msgSuccess'] : $repositorySettings->getSuccessMessage();
                            $widgetMsgFail = !empty($widget->instance['msgFail']) ? $widget->instance['msgFail'] : $repositorySettings->getFailureMessage();
                            if($modalResult == false){
                                $modalResultClass = 'gn-modal-result-fail';
                                $modalGutsInject->appendMsg($widgetMsgFail, $modalResult);
                            } elseif($modalResult == true){
                                $modalResultClass = 'gn-modal-result-success';
                                $modalGutsInject->appendMsg($widgetMsgSuccess, $modalResult);
                            }
                            $modalGutsInject->hideRequired();
                        }

                        // Add modal window renderer
                        $footerModals->addModalWindow(
                            $id,
                            $modalGutsInject,
                            false,
                            $ctaModalClass . ' ' . $modalResultClass
                        );
                    }

                    // Unset these guys
                    unset($GLOBALS['wp_customize']);
                    unset($GLOBALS['WPME_MODAL_ID']);
                }
            }
            if(null === $footerModals){
                $footerModals = new \WPMKTENGINE\ModalWindow();
            }
            // Fire filter to add additional Modals if needed by extensions
            apply_filters('wpmktengine_footer_modals', $footerModals);
            // Add open modal javascript for PopOver (if set)
            if(isset($footerPopOverData) && !empty($footerPopOverData)){
                // There can be olny one popOver on post page, so it's always the same id, here:
                $footerModals = $footerModals . \WPMKTENGINE\WidgetForm::getModalOpenJavascript('modalWindowGenooctaShortcodepopover', FALSE, FALSE);
            }
            // print it out
            echo $footerModals;
        } else {
            if(null === $footerModals){
                $footerModals = new \WPMKTENGINE\ModalWindow();
            }
            // Fire filter to add additional Modals if needed by extensions
            apply_filters('wpmktengine_footer_modals', $footerModals);
            // print it out
            echo $footerModals;
        }
    }


    /**
     * Render mobile window
     *
     * @param string $subscribe
     * @param null $html
     */
    public static function renderMobileWindow($subscribe = 'Subscribe', $html = NULL, $bodyClass = '')
    {
        header('Content-Type: text/html; charset=utf-8');
        // Simple template
        echo '<!DOCTYPE html>'
            .'<html class="genooFullPage">'
            .'<head>'
            .'<meta charset="utf-8" />'
            .'<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">'
            .'<title>'. $subscribe .'</title>';
        wp_head();
        echo '</head>';
        echo '<body class="genooMobileWindow '. $bodyClass .'">';
        if(!is_null($html)){
            echo $html;
        }
        wp_footer();
        echo '</body></html>';
        // Kill it before WordPress does his shenanigans
        exit();
    }

    /**
     * Render Landing Page
     *
     * @param \WP_Post $landingPost
     * @param null $id
     * @param string $header
     * @param string $footer
     */
    public function renderLandingPage(\WP_Post $landingPost, $id = NULL, $header = '', $footer = '')
    {
	    // Turn off errors
	    error_reporting(0);
	    ini_set('error_reporting', 0);
        // This might hold output buffer
        $headerAdditional = $header;
        // Set title so we can manipulate it later on
        $title = $landingPost->post_title;
        // Set up post data for plugins / extensions, well setup, fake it
        $GLOBALS['wp_query'] = new \WP_Query(array('p' => $landingPost->ID, 'post_type' => 'wpme-landing-pages'));
        $GLOBALS['wp_query']->is_singular = true;
        $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
        $GLOBALS['post'] = $landingPost;
        $GLOBALS['landing_page'] = $landingPost;
        // Setup data and initiate
        setup_postdata($GLOBALS['post']);
        $title = apply_filters('wp_title', $title, '', '');
        // If there is Yoast SEO, use it
        if(defined('WPSEO_VERSION') && function_exists('wpseo_frontend_head_init') && function_exists('setup_postdata')){
            // There might be Yoast SEO custom header data
            // Start the output buffer
            ob_start();
            // Applyg globals
            wpseo_frontend_head_init();
            // Do header actions
            do_action('wpseo_head');
            $output = ob_get_contents();
            ob_end_clean();
            $headerAdditional .= $output;
        }
        // All in one SEO Pack? Use it
        if(defined('AIOSEOP_VERSION') && isset($GLOBALS['aiosp']) && $GLOBALS['aiosp'] instanceof \All_in_One_SEO_Pack && method_exists($GLOBALS['aiosp'], 'wp_head')){
            // There might be Yoast SEO custom header data
            // Start the output buffer
            ob_start();
            $aiosp = $GLOBALS['aiosp'];
            $aiosp->wp_head();
            $output = ob_get_contents();
            ob_end_clean();
            $headerAdditional .= $output;
        }
        // SEO Ultimate
        if(defined('SU_MINIMUM_WP_VER')){
            ob_start();
            do_action('su_head');
            $output = ob_get_contents();
            ob_end_clean();
            $headerAdditional .= $output;
        }
        // UTF-8 header
        header('Content-Type: text/html; charset=utf-8');
        // Lets see
        try {
            $pages = new \WPMKTENGINE\RepositoryPages($this->cache, $this->api);
            $page = $pages->getPage($id);
            // Affialites
            if(class_exists('\Affiliate_WP_Tracking')){
                add_filter('affwp_use_fallback_tracking_method', '__return_true', 999);
                $affiliates = new \Affiliate_WP_Tracking();
                if(method_exists($affiliates, 'fallback_track_visit')){
                    $affiliates->fallback_track_visit();
                }
            }
            // Id
            $page = (array)$page;
            $pageData = $page['page_data'];
            $pageName = $page['name'];
            if(is_string($pageData) && Strings::startsWith($pageData,'<!DOCTYPE')){
                $renderer = new \WPME\Extensions\TemplateRenderer($pageData);
                $renderer->prepare();
            } else {
                $renderer = new \WPMKTENGINE\TemplateRenderer($pageData);
                $renderer->prapare();
                $renderer->iterate();
            }
            $renderer->render(
                $title,
                $headerAdditional . $header,
                $footer
            );
        } catch (\Exception $e){
            echo $e->getMessage();
        }
        exit();
    }

    /**
     * Render page template
     *
     * @param $id
     */
    public function renderPageTemplate($id)
    {
        header('Content-Type: text/html; charset=utf-8');
        try {
            // Error reporting
            error_reporting(0);
            ini_set('error_reporting', 0);
            $pages = new \WPMKTENGINE\RepositoryPages($this->cache, $this->api);
            $page = $pages->getPage($id);
            $page = (array)$page;
            // if page data
            if(is_array($page) && array_key_exists('page_data', $page)){
                $pageData = $page['page_data'];
                $pageName = $page['name'];
                if(is_string($pageData) && Strings::startsWith($pageData,'<!DOCTYPE')){
                    $renderer = new \WPME\Extensions\TemplateRenderer($pageData);
                    $renderer->prepare();
                } else {
                    $renderer = new \WPMKTENGINE\TemplateRenderer($pageData);
                    $renderer->prapare();
                    $renderer->iterate();
                }
                $renderer->render($pageName);
            } elseif(is_object($page) && isset($page->page_data)){
                $page = (array)$page;
                $pageData = $page['page_data'];
                $pageName = $page['name'];
                if(is_string($pageData) && Strings::startsWith($pageData,'<!DOCTYPE')){
                    $renderer = new \WPME\Extensions\TemplateRenderer($pageData);
                    $renderer->prepare();
                } else {
                    $renderer = new \WPMKTENGINE\TemplateRenderer($pageData);
                    $renderer->prapare();
                    $renderer->iterate();
                }
                $renderer->render($pageName);
            }
        } catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * @param $file
     */
    public static function renderTinyMCEIframe($file)
    {
        header('Content-Type: text/html; charset=utf-8');
        include_once GENOO_ASSETS_DIR . $file;
        exit();
    }

    /**
     * @param $id
     * @param $src
     */
    public static function renderPreviewLumenIframe($id, $src)
    {
        $src = Utils::nonProtocolUrl(base64_decode($src));
        header('Content-Type: text/html; charset=utf-8');
        echo '<script src="'. $src .'" type="text/javascript"></script>';
        echo '<div id="'. $id .'"></div>';
        exit();
    }

    /**
     * Shutdown
     */
    public function shutdown(){}
}
