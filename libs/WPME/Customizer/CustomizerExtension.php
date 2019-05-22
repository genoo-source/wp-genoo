<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General public static License Ver. 2 (GPL)
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

namespace WPME\Customizer;

/**
 * Class CustomizerExtension
 * - This class handles customizer overall requests and
 *   template display
 *
 * @package WPME\Customizer
 */
class CustomizerExtension
{
    /**
     * @var \WPME\Customizer\TemplateStorage
     */
    public $templateStorage;

    /**
     * CustomizerExtension constructor.
     */
    public function __construct()
    {
        $this->templateStorage = new \WPME\Customizer\TemplateStorage();
        $this->defineConstants();
    }


    /**
     * Define constants used in templates
     */
    public function defineConstants()
    {
        define(
            'WPME_MODAL_SELECTOR__BUTTON',
            '.gn-custom-modal .form-button-submit, '
            . '.gn-custom-modal button.gn-btn.gn-btn-primary, '
            . '.gn-custom-modal button[type="submit"], '
            . '.gn-custom-modal input[type="submit"]'
        );
        define(
            'WPME_MODAL_SELECTOR__BUTTON_HOVER',
            '.gn-custom-modal .form-button-submit:hover, '
            . '.gn-custom-modal button.gn-btn.gn-btn-primary:hover, '
            . '.gn-custom-modal button[type="submit"]:hover, '
            . '.gn-custom-modal input[type="submit"]:hover'
        );
        define(
            'WPME_MODAL_SELECTOR__BUTTON_TEXT',
            '.gn-custom-modal .form-button-submit, '
            . '.gn-custom-modal button.gn-btn.gn-btn-primary, '
            . '.gn-custom-modal button, '
            . '.gn-custom-modal button label, '
            . '.gn-custom-modal input[type="submit"]'
        );
        define(
            'WPME_MODAL_SELECTOR__LABEL',
            '.gn-custom-modal .gn-form label,'
            . '.gn-custom-modal p > label, '
            . '.gn-custom-modal label.control-label'
        );
        define(
            'WPME_MODAL_SELECTOR__INPUT',
            '.gn-custom-modal .form-control, '
            . '.gn-custom-modal input[type="text"], '
            . '.gn-custom-modal .ext-form-input'
        );
        define(
            'WPME_MODAL_SELECTOR__TEXTAREA',
            '.gn-custom-modal .gn-form textarea,'
            . '.gn-custom-modal .gn-form textarea.ext-form-input'
        );
        define(
            'WPME_MODAL_SELECTOR__SELECT',
            '.gn-custom-modal .gn-form select,'
            . '.gn-custom-modal .gn-form select.form-control'
        );
        define(
            'WPME_MODAL_SELECTOR__ALL_FLAT_INPUTS',
            WPME_MODAL_SELECTOR__INPUT
            . ', '
            . WPME_MODAL_SELECTOR__TEXTAREA
            . ', '
            . WPME_MODAL_SELECTOR__SELECT
        );
    }

    /**
     * Register all needed hooks
     */
    public function registerCustomizerPreview()
    {
        // - Request
        add_action('parse_request', array($this, 'registerRequestParse'), 10, 1);
        add_filter('query_vars', array($this, 'registerRequestVars'), 20, 1);

        // - Metaboxes
        add_action('add_meta_boxes', array($this, 'registerMetaboxes'), 10);
        add_action('save_post', array($this, 'registerMetaboxesSave'), 10, 2);

        // - Admin js
        add_action('admin_head', array($this, 'registerAdminJavascript'), 100, 1);

        // - Admin redirect
        add_filter('redirect_post_location', array($this, 'registerAdminSaveRedirect'), 100, 2);

        // - Post title modal fix, migration tool
        add_filter('get_post_metadata', function($metadata, $object_id, $meta_key, $single){
            if(null === $metadata && 'gn-post-title' === $meta_key){
                // Check if we saved this before
                $didBefore = get_post_meta($object_id, '_migration_post_title', true);
                if($didBefore === null || empty($didBefore)){
                    // Get title from post
                    $metadata = get_the_title($object_id);
                    // Save we did the migration and copy field over
                    update_post_meta($object_id, '_migration_post_title', '1');
                    update_post_meta($object_id, 'gn-post-title', $metadata);
                }
            }
            return $metadata;
        }, 10, 4);

        // - Customizer
        if($this->isCustomizer()){
            // Filters
            add_filter('customize_dynamic_setting_class', array($this, 'registerCustomizerSettingClass'), 999);
            add_action('customize_controls_init', array($this, 'registerCustomizerInit'), 999);
            add_action('customize_register', array($this, 'registerCustomizerRegister'), 100, 1);
            add_action('customize_controls_print_styles', array($this, 'registerCustomizerPrintStyles'), 100, 1);
            add_action('customize_controls_print_styles', array($this, 'registerCustomizerPrintStyles'), 100, 1);
            add_action('customize_controls_print_footer_scripts', array($this, 'registerCustomizerScripts'), 1001);
            add_action('wp_enqueue_scripts', array($this, 'registerWordPressScripts'), 999);
        }

        // - Customizer text
        if($this->isCustomizer()){
            add_filter('gettext', array($this, 'registerTextChanges'), 999, 3);
            add_filter('option_blogname', array($this, 'registerTitleChange'), 999, 1);
        }

        // - WordPress
        if($this->isCustomizer()){
            add_action('wp_head', array($this, 'registerCustomizerWordPressHead'), 999);
            add_action('wp_footer', array($this, 'registerCustomizerWordPressFooter'), 999);
        }

        // - Modals
        if($this->isCustomizer()){
            add_filter('wpmktengine_footer_modals', array($this, 'registerCustomizerFooterModals'), 100, 1);
            add_filter('wpmktengine_modal_window_close_url', function(){ return "#"; }, 100, 1);
            add_filter('wpmktengine_modal_window_string', array($this, 'registerCustomizerFooterModalsHrefReplacement'), 100, 1);
        }

        // - Selective refresh and others to remove,
        // that could cause issues with our customizer
        if($this->isCustomizer()){
            add_action('after_setup_theme', function(){
                // Get wp theme features
                global $_wp_theme_features;
                $_wp_theme_features = array();
            }, 999);
        }
    }


    /**
     * Redirect after customizer preview click
     *
     * @param $location
     * @param $post
     */
    public function registerAdminSaveRedirect($location, $post)
    {
        if(isset($_POST['previewModal']) && isset($_POST['previewModalUrl'])){
            $location = $_POST['previewModalUrl'];
        }
        return $location;
    }

    /**
     * Admin JS.
     */
    public function registerAdminJavascript()
    {
        // global
        global $post;
        // exit early
        if(!isset($_GET['post_type'])){
            if(!isset($post)) return;
            if($post->post_type !== 'cta') return;
        } else {
            if($_GET['post_type'] !== 'cta') return;
        }
        // Js
        ?>
      <script type="text/javascript">
        jQuery(document).on('click', '.preview-customizer', function(event){
          // Block preview
          event.returnValue = null;
          if(event.preventDefault){ event.preventDefault(); }
          // Add return value
          jQuery('form#post')
          .append('<input type="hidden" name="previewModal" value="true" />')
          .append('<input type="hidden" name="previewModalUrl" value="'+ event.target.getAttribute('data-href') +'" />');
          // Click save
          jQuery('#publish').click();
        });
      </script>
        <?php
    }

    /**
     * Replace hrefs
     *
     * @param $string
     * @return mixed
     */
    public function registerCustomizerFooterModalsHrefReplacement($string)
    {
        $string = str_replace(
            array(
                'onclick="Modal.close(event)"'
            ),
            array(
                ''
            ),
            $string
        );
        return $string;
    }

    /**
     * @param $title
     * @return string
     */
    public function registerTitleChange($title)
    {
        $cta = get_post($_GET['gn-cust']);
        return $cta->post_title;
    }

    /**
     * Register Text Changes
     *
     * @param $translated_text
     * @param $text
     * @param $domain
     * @return string
     */
    public function registerTextChanges($translated_text, $text, $domain)
    {
        switch($text){
            case 'The Customizer allows you to preview changes to your site before publishing them. You can navigate to different pages on your site within the preview. Edit shortcuts are shown for some editable elements.':
                $translated_text = 'The Customizer allows you to preview changes to our CTA before publishing them.';
                break;
            case 'Customizing';
                $cta = get_post($_GET['gn-cust']);
                $cta = get_post($_GET['gn-cust']);
                $translated_text = 'Customizing: ' . $cta->post_title;
                break;
        }
        return $translated_text;
    }

    /**
     * Customizer CSS renderer for Customizer Admin
     */
    public function registerCustomizerWordPressHead()
    {
        // Get template stylesheet
        echo $this->templateStorage->prepareTemplateStyleForCustomizer();
        // Get customizer
        global $WPME_CUSTOMIZER;
        // Attach
        if(isset($WPME_CUSTOMIZER)){
            ?>
          <style type="text/css">
            <?php $WPME_CUSTOMIZER->attachCSSRenders(); ?>
          </style>
            <?php
        }
    }

    /**
     * Customizer JS
     */
    public function registerCustomizerWordPressFooter()
    {
        // Get customizer
        global $WPME_CUSTOMIZER;
        // Attach
        if(isset($WPME_CUSTOMIZER)){
            ?>
          <script>
            jQuery(window).ready(function(){
                <?php $WPME_CUSTOMIZER->attachJavascriptRenders(); ?>
            });
          </script>
            <?php
        }
    }

    /**
     * Used to add modal window to the customizer
     * preview with contents of prepared template.
     *
     * @param $footerModals
     */
    public function registerCustomizerFooterModals($footerModals)
    {
        // Get customizer
        global $WPME_CUSTOMIZER;
        $echo = false;
        if(null === $footerModals || !is_object($footerModals)){
            $echo = true;
            $footerModals = new \WPMKTENGINE\ModalWindow();
        }
        // Attach modal window
        $footerModals->addModalWindow(
            'gn-customizer',
            $this->templateStorage->prepareTemplateForCusomizer(),
            true,
            'gn-customizer gn-custom-modal ' . $this->templateStorage->modalIdentificator()
        );
        $footerModals->setVisibility(true);
        if($echo){
            echo $footerModals;
        }
    }

    /**
     * Styles to hide return button, if there is only one
     * view.
     */
    public function registerCustomizerPrintStyles()
    {
        // Get customizer
        global $WPME_CUSTOMIZER;
        // Is single
        $singleViewId = $WPME_CUSTOMIZER->getFirstViewId();
        ?>
      <style type="text/css">
        <?php if(isset($WPME_CUSTOMIZER) && $WPME_CUSTOMIZER->isSingleView()){ ?>
        body.wp-customizer .customize-section-back,
        <?php } ?>
        body.wp-customizer .wp-full-overlay #customize-theme-controls ul.customize-pane-parent .accordion-section { display: none !important; }
        <?php foreach($WPME_CUSTOMIZER->getViews() as $view){ ?>
        body.wp-customizer .wp-full-overlay #customize-theme-controls ul.customize-pane-parent #accordion-section-<?= $view->id ?>.accordion-section { display: block !important; }
        <?php } ?>
        body.wp-customizer .customize-control .attachment-media-view .placeholder,
        body.wp-customizer .customize-control-header .placeholder { margin-left: 0; backgorund: #fff; }
      </style>
        <?php
    }

    /**
     * Register Customizer and attach Controls for current
     * modal template. This will also create a global Customizer
     * accessible when needed.
     *
     * @param $wp_customize
     */
    public function registerCustomizerRegister($wp_customize)
    {
        if($this->isCustomizer()){
            // Remove all other things
            $this->detachSections($wp_customize);
            // If our page, we will attach controls we need
            $this->attachControls($wp_customize);
            // Set up fake data
            $this->setUpFakeData();
        }
    }

    /**
     * Modify Customizer control settings
     *  - iframe url
     *  - autofocus field
     *  - return url
     */
    public function registerCustomizerInit()
    {
        // Get WordPress Customizer and our Customizer
        global $wp_customize;
        global $WPME_CUSTOMIZER;
        // ONLY IF THEME URL
        // Add template and theme to the customizer URL for
        // iframe, that way we know what to load
        $urlTemplate = add_query_arg(
            array(
                'gn-cust' => $_GET['gn-cust'],
                'gn-thm' => $_GET['gn-thm'],
            ),
            home_url('/')
        );
        // Return URL will be to the post itself
        // - in future will probably be just to post listing page
        $urlReturn = add_query_arg(
            array(
                'post' => $_GET['gn-cust'],
                'action' => 'edit'
            ),
            admin_url('post.php')
        );
        // Autofocus field
        $urlAutofocus = $WPME_CUSTOMIZER->isSingleView() ? array('section' => $WPME_CUSTOMIZER->getFirstViewId()) : array();
        // Set urls
        $wp_customize->set_return_url(wp_unslash($urlReturn));
        // Set autofocus if only one section
        $wp_customize->set_autofocus(wp_unslash($urlAutofocus));
        // Set preview URL
        $wp_customize->set_preview_url(wp_unslash($urlTemplate));
    }


    /**
     * Customizer Setting Class
     * - we hijack this and use our own, for the purpose of saving
     * the customizer data as post_meta
     *
     * @param $class
     * @return string
     */
    public function registerCustomizerSettingClass($class)
    {
        return '\WPME\Customizer\CustomizerSetting';
    }

    /**
     * Customier js that overwrieds URL for AJAX, to have our data appended
     * for each ajax call to admin-ajax.php witout tempering with js funcitons.
     */
    public function registerCustomizerScripts()
    {
        ?>
      <script type="text/javascript">
        // Appended string
        var _appendCustomizerUrl = '?gn-cust=<?= self::getCust(); ?>&gn-thm=<?= self::getThm(); ?>';
        // Customizer settings
        var _wpCustomizeSettings = _wpCustomizeSettings || {};
        _wpCustomizeSettings.url.ajax = _wpCustomizeSettings.url.ajax + _appendCustomizerUrl;
        // Ajax Url
        var ajaxurl = ajaxurl + _appendCustomizerUrl;
        // WP Utils ajax url
        var _wpUtilSettings = _wpUtilSettings || {};
        _wpUtilSettings.ajax.url = _wpUtilSettings.ajax.url + _appendCustomizerUrl
      </script>
        <?php
    }

    /**
     * Unregister WordPress scripts (non-wordpress actually)
     * in customizer preview.
     */
    public function registerWordPressScripts()
    {
        // Get global scripts
        global $wp_scripts;
        global $wp_styles;

        // Block
        $bloackJs = array(
            'customize-preview-nav-menus'
        );

        // Parent and child theme dirs
        $themeDirecotryParent = get_template_directory_uri();
        $themeDirecotryCurrent = get_stylesheet_directory_uri();

        // Go through each style and remove if not ours
        // or wordpress
        foreach($wp_styles->queue as $style){
            // /wp-admin/
            // /wp-includes/
            $src = $wp_styles->registered[$style]->src;
            if (strpos($src, '/wp-admin/') !== false) {
                // All good
            } else if(strpos($src, '/wp-includes/') !== false) {
                // All good
            } else if(strpos($src, $themeDirecotryParent) !== false && $this->isCustomizerDefaultTheme()) {
                // All good, if it's default theme, we need the styles
            } else if(strpos($src, $themeDirecotryCurrent) !== false && $this->isCustomizerDefaultTheme()) {
                // All good, if it's default theme, we need the styles
            } else if($style !== 'genooFrontend'){
                // Here comes trouble
                $wp_styles->dequeue($style);
                $wp_styles->remove($style);
            }
        }
        // Go through each script
        // and remove if not wordpress
        foreach($wp_scripts->queue as $scripts){
            // /wp-admin/
            // /wp-includes/
            $src = $wp_scripts->registered[$scripts]->src;
            if($scripts == 'jquery'){
                continue;
            }
            if (strpos($src, '/wp-admin/') !== false) {
                // All good if not blocking
                if(in_array($scripts, $bloackJs)){
                    $wp_scripts->dequeue($scripts);
                    $wp_scripts->remove($scripts);
                }
            } else if(strpos($src, '/wp-includes/') !== false){
                // All good if not blocking
                if(in_array($scripts, $bloackJs)){
                    $wp_scripts->dequeue($scripts);
                    $wp_scripts->remove($scripts);
                }
            } else {
                // Here comes trouble
                $wp_scripts->dequeue($scripts);
                $wp_scripts->remove($scripts);
            }
        }
    }

    /**
     * Register Metaboxes
     */
    public function registerMetaboxes()
    {
        add_meta_box('popup-style', 'Pop-up Style', function($post){
            // metabox
            $themes = $this->templateStorage->getThemesData();
            $themeCurrent = get_post_meta($post->ID, '_wpme_modal_theme', true);
            $themeNotSelected = empty($themeCurrent) ? true : false;
            if(isset($themes) && is_array($themes)){
                $counterAll = count($themes);
                $counter = 1;
                $radios = '';
                foreach($themes as $theme){
                    // Counter vars
                    $counterNext = $counter == $counterAll ? 1 : ($counter + 1);
                    $counterPrev = $counter == 1 ? $counterAll : $counter - 1;
                    $counterId = $theme['id'];
                    // Current class?
                    $counterClass = $themeNotSelected
                        ? ($counter == 1 ? '' : 'hidden')
                        : ($theme['id'] == $themeCurrent ? '' : 'hidden');
                    // Current theme saved selected?
                    $counterCurrentSelected = $counterClass == '' ? true : false;
                    $counterURL = add_query_arg(
                        array(
                            'gn-cust' => $post->ID,
                            'gn-thm' => $theme['id']
                        ),
                        admin_url('customize.php')
                    );
                    $radioChecked = $counterCurrentSelected ? 'checked="checked"' : '';
                    // Radio Vars
                    $radios .= "<input 
                                    type=\"radio\" 
                                    name=\"_wpme_modal_theme\" 
                                    id=\"_wpme_modal_theme_$counter\" 
                                    value=\"$counterId\" 
                                    $radioChecked />";
                    ?>
                  <div id="wpme_row_id_<?= $counter ?>" class="<?= $counterClass ?>">
                    <div class="wpme_row">
                      <div class="wpme_popname">
                        <h4><?= $theme['title'] ?></h4>
                        <p><?= $theme['description'] ?></p>
                      </div>
                    </div>
                    <div class="wpme_row wpme_action_row">
                      <div class="wpme_popswitch">
                        <a class="prev" href="#" data-current="<?= $counter ?>" onclick="WPMESlide.goTo(this, event, <?= $counterPrev ?>);">&lsaquo;</a><!--
                                 --><a class="next" href="#" data-current="<?= $counter ?>" onclick="WPMESlide.goTo(this, event, <?= $counterNext ?>);">&rsaquo;</a>
                      </div>

                      <a id="preview-button-customize-<?= $counter ?>"
                         data-href="<?= $counterURL ?>"
                         href="<?= $counterURL ?>"
                         class="button button-primary preview preview-customizer">Preview & Customize</a>
                        <?php if($counterId == 'default'){ ?>
                            <?php $this->generateDefaultStyle(); ?>
                        <?php } ?>
                      <div class="cf clear clearfix"></div>
                    </div>
                      <?php if($counterId !== 'default'){ ?>
                        <div class="wpme_row wpme_swithcer">
                          <img src="<?= $theme['image'] ?>" alt="">
                        </div>
                      <?php } ?>
                  </div>
                    <?php
                    $counter++;
                }
                // This is where we select the theme by sliding
                echo "<div id=\"wpme_switcher_radios\" style='display: none !important;'>";
                echo $radios;
                echo "</div>";
            }
        }, 'cta');
    }


    /**
     * Default pop-up has an option for theme
     * option.
     */
    public function generateDefaultStyle()
    {
        // Get globals
        global $WPME_API;
        global $post;
        // WPME api is needed
        if(!isset($WPME_API)){
            return;
        }
        // Get settings themes
        $settingsRepo = $WPME_API->settingsRepo;
        $settingsRepoThemes = $settingsRepo->getSettingsThemes();
        $currentTheme = get_post_meta($post->ID, 'form_theme', true);
        ?>
      <div class="preview">
        <table>
          <tbody>
          <tr class="themeMetaboxRow" id="themeMetaboxRowform_theme_second" style="display: table-row;">
            <td class="genooLabel" style="text-align: right;"><label for="form_theme_second">Form Style</label></td>
            <td>
              <select id="form_theme_second" name="form_theme" style="width: 100%;">
                  <?php
                  foreach($settingsRepoThemes as $key => $theme){
                      // Selected
                      $selected = $key == $currentTheme ? "selected='selected'" : "";
                      // Go
                      echo "<option value='$key' $selected>$theme</option>";
                  }
                  ?>
              </select>
              <div class="clear"></div>
            </td>
          </tr>
          </tbody>
        </table>

      </div>
        <?php
    }


    /**
     * Save post meta
     *
     * @param $post_id
     * @param $post
     * @return mixed
     */
    public function registerMetaboxesSave($post_id, $post)
    {
        // Key
        $meta_key = '_wpme_modal_theme';
        // Get the post type object.
        $post_type = get_post_type_object( $post->post_type );
        // Check if the current user has permission to edit the post.
        if ( !current_user_can($post_type->cap->edit_post, $post_id ))
            return $post_id;
        // Get the posted data and sanitize it for use as an HTML class.
        $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_html_class($_POST[$meta_key]) : '' );
        $meta_value = get_post_meta($post_id, $meta_key, true);
        // Data
        if ($new_meta_value && '' == $meta_value)
            add_post_meta( $post_id, $meta_key, $new_meta_value, true);
        elseif ($new_meta_value && $new_meta_value != $meta_value)
            update_post_meta( $post_id, $meta_key, $new_meta_value);
        elseif ('' == $new_meta_value && $meta_value)
            delete_post_meta( $post_id, $meta_key, $meta_value);
    }


    /**
     * Add query vars to url
     *
     * @param $query_vars
     * @return array
     */
    public function registerRequestVars($query_vars)
    {
        $query_vars[] = 'gn-cust';
        $query_vars[] = 'gn-thm';
        return $query_vars;
    }

    /**
     * Display template for customizer
     *
     * @param $wp
     */
    public function registerRequestParse($wp)
    {
        // Customizer?
        if(is_array($wp->query_vars) &&
            (
                array_key_exists('gn-cust', $wp->query_vars)
                &&
                array_key_exists('gn-thm', $wp->query_vars)
            )
        ){
            // We have a customizer window
            $ctaId = $wp->query_vars['gn-cust'];
            $ctaTheme = $wp->query_vars['gn-thm'];
            // Alright, let's do this thing
            $ctaThemeData = $this->templateStorage->getThemeData($ctaTheme);
            if($ctaThemeData !== false){
                // Template
                $this->templateStorage->getThemeTemplateLayout($ctaTheme);
            }
        }
    }

    /**
     * @return bool
     */
    public static function isCustomizer()
    {
        if(
            isset($_GET['gn-cust'])
            &&
            isset($_GET['gn-thm'])
        ){
            return true;
        }
        return false;
    }

    /**
     * Is default theme?
     *
     * @return bool
     */
    public static function isCustomizerDefaultTheme()
    {
        return (
        (isset($_GET['gn-thm']) && $_GET['gn-thm'] == 'default')
        );
    }

    /**
     * Get's customizer template file. In this case,
     * it's a template of controls used.
     *
     * @param null $template
     * @return bool|string
     * @throws \LogicException
     */
    public static function getCustomizerTemplateControls($template = null)
    {
        // Get template
        $template = $template == null ? self::getThm() : $template;
        if($template !== null){
            // Template storage
            $templateStorage = new \WPME\Customizer\TemplateStorage();
            // Locate template
            return $templateStorage->getThemeControls($template);
        }
        throw new \LogicException('Missing template in request URL, gn-thm');
    }

    /**
     * @return null
     */
    public static function getCust()
    {
        return isset($_GET['gn-cust']) ? $_GET['gn-cust'] : null;
    }

    /**
     * @return null
     */
    public static function getThm()
    {
        return isset($_GET['gn-thm']) ? $_GET['gn-thm'] : null;
    }

    /**
     * Attach controls specific for each template
     *
     * @param $wp_customize
     */
    public function attachControls(&$wp_customize)
    {
        // Get template
        $template = $this->getCustomizerTemplateControls();
        // Customizer
        include_once $template;
    }

    /**
     * Detach previous components, we only need
     * ours for this show.
     *
     * @param $wp_customize
     */
    public function detachSections(&$wp_customize)
    {
        // Get detachables
        $customizerSections = $wp_customize->sections();
        $customizerSettings = $wp_customize->settings();
        $customizerControls = $wp_customize->controls();
        // Allowed or breaks js
        $allowedOrbreaks = array(
            'menu_locations',
            'add_menu',
            'nav_menu',
            'nav_menus',
            'nav_menus_created_posts',
            // Settings section
            'nav_menu_locations',
            'nav_menu_item'
        );
        // Detach
        if(!empty($customizerSections)){
            foreach($customizerSections as $sectionId => $section){
                if(in_array($sectionId, $allowedOrbreaks)){
                    continue;
                }
                $wp_customize->remove_section($sectionId);
            }
        }
        if(!empty($customizerSettings)){
            foreach($customizerSettings as $sectionId => $section){
                $cleanSection = preg_replace(
                    '~\[(.+?)\]~',
                    '',
                    $sectionId
                );
                if(in_array($sectionId, $allowedOrbreaks) || in_array($cleanSection, $allowedOrbreaks)){
                    continue;
                }
                $wp_customize->remove_setting($sectionId);
            }
        }
        if(!empty($customizerControls)){
            foreach($customizerControls as $sectionId => $section){
                if(in_array($sectionId, $allowedOrbreaks)){
                    continue;
                }
                $wp_customize->remove_control($sectionId);
            }
        }
    }

    /**
     * Sets up fake post data for plugins that may hook
     * into wp_head or wp_footer on customizer.
     */
    public static function setUpFakeData()
    {
        // Fake post
        $post = new \stdClass();
        $post->ID = 1;
        $post->post_author = '1';
        $post->post_name = '';
        $post->post_type = 'post';
        $post->post_title = '';
        $post->post_date = '0000-00-00 00:00:00';
        $post->post_date_gmt = '0000-00-00 00:00:00';
        $post->post_content	= '';
        $post->post_excerpt	= '';
        $post->post_status 	= 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_password = '';
        $post->post_parent = 0;
        $post->post_modified = '0000-00-00 00:00:00';
        $post->post_modified_gmt = '0000-00-00 00:00:00';
        $post->comment_count = '0';
        $post->menu_order = 0;
        $GLOBALS['post'] = $post;
    }
}