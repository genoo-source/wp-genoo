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

use Genoo\Api;
use WPMKTENGINE\RepositoryThemes;
use WPMKTENGINE\Wordpress\Post;
use WPMKTENGINE\Wordpress\Utils;


class RepositorySettings extends \WPMKTENGINE\Repository
{
    /** settings key */
    const KEY_SETTINGS = 'genooApiSettings';
    /** settings leads */
    const KEY_LEADS = 'genooLeads';
    /** general - used only by plugin calls */
    const KEY_GENERAL = 'genooApiGeneral';
    /** theme */
    const KEY_THEME = 'genooThemeSettings';
    /** form messages */
    const KEY_MSG = 'genooFormMessages';
    /** CTA settings */
    const KEY_CTA = 'genooCTA';
    /** Misc */
    const KEY_MISC = 'genooMisc';
    /** Lumens account? */
    const KEY_LUMENS = 'genooLumens';
    /** Landing Pages */
    const KEY_LANDING = 'WPMKTENGINELANDING';
    /** @var get_option key */
    var $key;


    /**
     * Constructor
     */

    public function __construct()
    {
        parent::__construct();
        $this->key = GENOO_KEY;
    }


    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */

    public static function getOption($option, $section, $default = '')
    {
        $options = get_option($section);
        if (isset($options[$option])){
            return $options[$option];
        }
        return $default;
    }


    /**
     * Get options namespace
     *
     * @param $namespace
     * @return mixed
     */

    public function getOptions($namespace){ return get_option($namespace); }


    /**
     * Set option
     *
     * @param $option
     * @param $value
     * @return mixed
     */

    public function setOption($option, $value){ return update_option($option, $value); }


    /**
     * Delete option
     *
     * @param $option
     * @return mixed
     */

    public function deleteOption($option){ return delete_option($option); }


    /**
     * Update options, we don't need to check if it exists, it will create it if not.
     *
     * @param $namespace
     * @param array $options
     * @return mixed
     */

    public function updateOptions($namespace, array $options = array()){ return update_option($namespace, $options); }


    /**
     * Get API key from settings
     *
     * @return string
     */

    public function getApiKey(){ return $this->getOption('apiKey', self::KEY_SETTINGS); }


    /**
     * Get active form id
     *
     * @return string
     */

    public function getActiveForm(){ return $this->getOption('activeForm', self::KEY_GENERAL); }


    /**
     * Get current active theme
     *
     * @return string
     */

    public function getActiveTheme(){ return $this->getOption('genooFormTheme', self::KEY_THEME); }


    /**
     * Check if sidebar protection is off
     *
     * @return bool
     */

    public function getDisableSidebarsProtection()
    {
        $var = $this->getOption('genooCheckSidebars', self::KEY_MISC);
        return $var == 'on' ? TRUE : FALSE;
    }

    /**
     * @return bool
     */
    public function getEnableHTTPPopUpProtocol()
    {
        $var = $this->getOption('genooCheckIframeURI', self::KEY_MISC);
        return $var == 'on' ? TRUE : FALSE;
    }


    /**
     * Sets active form
     *
     * @param $id
     * @return mixed
     */

    public function setActiveForm($id){ return $this->injectSingle('activeForm', $id, self::KEY_GENERAL); }


    /**
     * Add saved notice
     *
     * @param $key
     * @param $value
     */

    public function addSavedNotice($key, $value){ $this->injectSingle('notices', array($key => $value), self::KEY_GENERAL); }


    /**
     * Get saved notices
     *
     * @return null
     */

    public function getSavedNotices()
    {
        $general = $this->getOptions(self::KEY_GENERAL);
        if(isset($general['notices'])){
            return $general['notices'];
        }
        return null;
    }


    /**
     * Flush aaved notices - just rewrites with null value
     *
     * @return bool
     */

    public function flushSavedNotices()
    {
        $this->injectSingle('notices', null, self::KEY_GENERAL);
        return true;
    }


    /**
     * Get saved roles guide
     *
     * @return null
     */

    public function getSavedRolesGuide()
    {
        $r = null;
        $guide = $this->getOptions(self::KEY_LEADS);
        if($guide && is_array($guide) && !empty($guide)){
            foreach($guide as $key => $value){
                if($value !== 0){
                    $r[str_replace('genooLeadUser', '', $key)] = (int)$value;
                }
            }
        }
        $r = apply_filters('wpmktengine_saved_roles_lead_ids', $r);
        return $r;
    }


    /**
     * Get lead types
     *
     * @return array
     */

    public function getSettingsFieldLeadTypes()
    {
        $api = new \WPME\ApiFactory($this);
        $arr = array();
        $arr[] = __('- Select commenter lead type', 'genoo');
        if(GENOO_PART_SETUP){
            try{
                $leadTypes = $api->getLeadTypes();
                if($leadTypes && is_array($leadTypes)){
                    foreach($leadTypes as $lead){
                        $arr[$lead->id] = $lead->name;
                    }
                }
            } catch (\Exception $e){}
            return array(
                'name' => 'apiCommenterLeadType',
                'label' => __('Blog commenter lead type', 'genoo'),
                'type' => 'select',
                'desc' => __('You control your Lead Types in: Lead Management > Leads.', 'genoo'),
                'options' => $arr
            );
        }
        return null;
    }


    /**
     * Set single
     *
     * @param $key
     * @param $value
     * @param $namespace
     * @param $unique
     * @return mixed
     */

    public function injectSingle($key, $value, $namespace, $unique = true)
    {
        $inject = array();
        $original = $this->getOptions($namespace);
        if(is_array($value)){
            // Probably notices, search unique first, don't resinsert
            if($unique == true){
                // Search for
                $searchedKey = key($value);
                $searchedValue = current($value);
                $searchFound = false;
                $searchArray = $original[$key];
                // Go thgough array
                if($searchArray){
                    foreach($searchArray as $array){
                        if($array === $value){
                            // If arrays are the same, return
                            return true;
                        }
                    }
                }
                // Inject if not founds
                $inject[$key] = array_merge((array)$original[$key], array($value));
            } else {
                $inject[$key] = array_merge((array)$original[$key], array($value));
            }
        } else {
            $inject[$key] = $value;
        }
        return $this->updateOptions($namespace, array_merge((array)$original, (array)$inject));
    }


    /**
     * Get's tracking code
     *
     * @return string
     */

    public function getTrackingCode()
    {
        $code = $this->getOption('apiExternalTrackingCode', self::KEY_SETTINGS);
        $code = Utils::nonProtocolUrl($code);
        return $code;
    }


    /**
     * Get lead type
     *
     * @return string
     */

    public function getLeadType(){ return $this->getOption('apiCommenterLeadType', self::KEY_SETTINGS); }

    /**
     * @return string
     */
    public function getLeadTypeSubscriber(){ return $this->getOption('subscriber', self::KEY_LEADS); }

    /**
     * Success message
     *
     * @return string
     */

    public function getSuccessMessage()
    {
        $o = $this->getDefaultValue(self::KEY_MSG, 'sucessMessage');
        $s = $this->getOption('sucessMessage', self::KEY_MSG);
        if(isset($s) && !empty($s)){
            return $s;
        }
        return $o;
    }


    /**
     * Error message
     *
     * @return string
     */

    public function getFailureMessage()
    {
        $o = $this->getDefaultValue(self::KEY_MSG, 'errorMessage');
        $s = $this->getOption('errorMessage', self::KEY_MSG);
        if(isset($s) && !empty($s)){
            return $s;
        }
        return $o;
    }


    /**
     * Get field default value
     *
     * @param $section
     * @param $field
     * @return null
     */

    public function getDefaultValue($section, $name)
    {
        $settings = $this->getSettingsFields();
        if(isset($settings[$section])){
            foreach($settings[$section] as $field){
                if($field['name'] == $name){
                    if(isset($field['default']) && !empty($field['default'])){
                        return $field['default'];
                    }
                }
            }
        }
        return null;
    }


    /**
     * Gets settings page sections
     *
     * @return array
     */

    public function getSettingsSections()
    {
        if(GENOO_SETUP){
            return apply_filters(
                'wpmktengine_settings_sections',
                array(
                    array(
                        'id' => 'genooApiSettings',
                        'title' => __('API settings', 'genoo')
                    ),
                    array(
                        'id' => 'genooLeads',
                        'title' => __('Leads', 'genoo')
                    ),
                    array(
                        'id' => 'genooFormMessages',
                        'title' => __('Form messages', 'genoo')
                    ),
                    array(
                        'id' => 'genooThemeSettings',
                        'title' => __('Form themes', 'genoo')
                    ),
                    array(
                        'id' => 'genooCTA',
                        'title' => __('CTA', 'genoo')
                    ),
                    array(
                        'id' => 'genooMisc',
                        'title' => __('Miscellaneous', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_LANDING,
                        'title' => __('Landing Pages', 'wpmktengine')
                    )
                ),
                $this
            );
        } else {
            return apply_filters(
                'wpmktengine_settings_sections',
                array(
                    array(
                        'id' => 'genooApiSettings',
                        'title' => __('API settings', 'genoo')
                    ),
                ),
                $this
            );
        }
    }


    /**
     * Set debug
     *
     * @param bool $val
     */

    public function setDebug($val = true)
    {
        if($val === TRUE){
            $this->setOption('genooDebug', true);
        } else {
            $this->deleteOption('genooDebug');
        }
    }


    /**
     * Debug check removal
     *
     * @return mixed
     */

    public function flushDebugCheck(){ return $this->deleteOption('genooDebugCheck'); }


    /**
     * Get post tpyes
     *
     * @return array
     */

    public static function getPostTypes()
    {
        $r = array();
        $types = Post::getTypes();
        foreach($types as $key => $type){
            if($key !== 'attachment'){
                $r[$key] = $type->labels->singular_name;
            }
        }
        return $r;
    }


    /**
     * Get CTA post types
     *
     * @return array|null
     */

    public function getCTAPostTypes()
    {
        $postTypes = $this->getOption('genooCTAPostTypes', self::KEY_CTA);
        if(!empty($postTypes)){
            return array_keys($postTypes);
        } else {
            return array(
                'post',
                'page'
            );
        }
        return null;
    }


    /**
     * Get CTA's
     *
     * @return array
     */

    public function getCTAs()
    {
        $r = array(0 => __('Select CTA', 'genoo'));
        $ctas = get_posts(array('posts_per_page'   => -1, 'post_type' => 'cta', ));
        if($ctas && !empty($ctas)){
            foreach($ctas as $cta){
                $r[$cta->ID] = $cta->post_title;
            }
        }
        return $r;
    }


    /**
     * @return array|void
     */

    public function getUserRolesDropdonws()
    {
        // wp roles
        global $wp_roles;
        // return
        $r = array();
        // first
        $r[] = array(
            'desc' => __('Set default lead types for newly registered user roles.', 'genoo'),
            'type' => 'desc',
            'name' => 'genooLeads',
            'label' => '',
        );

        // oh, return this boy
        if(!is_object($wp_roles) && (!$wp_roles instanceof \WP_Roles)) return;

        // prep
        $roles = $wp_roles->get_names();
        $api = new \WPME\ApiFactory($this);
        $arr = array();
        $arr[] = __('- Don\'t save', 'genoo');
        try{
            $leadTypes = $api->getLeadTypes();
            if($leadTypes && !empty($leadTypes) && is_array($leadTypes)){
                foreach($leadTypes as $lead){
                    $arr[$lead->id] = $lead->name;
                }
            }
        } catch (\Exception $e){}

        // finalize
        foreach($roles as $key => $role){
            $r[] = array(
                'name' => 'genooLeadUser' . $key,
                'label' => $role,
                'type' => 'select',
                'options' => $arr
            );
        }

        return $r;
    }


    /**
     * Gets settings page fields
     *
     * @return array
     */

    public function getSettingsFields()
    {
        return apply_filters(
            'wpmktengine_settings_fields',
            array(
                'genooApiSettings' => array(
                    array(
                        'name' => 'apiKey',
                        'label' => __('API key', 'genoo'),
                        'type' => 'text',
                        'default' => '',
                        'desc' => __('You can generate your API key in: Control panel > Settings > Api.', 'genoo')
                    ),
                    array(
                        'name' => 'apiExternalTrackingCode',
                        'label' => __('External tracking code', 'genoo'),
                        'type' => 'textarea',
                        'desc' => __('You can generate your tracking code in: Control panel > Settings > External tracking.', 'genoo')
                    ),
                    $this->getSettingsFieldLeadTypes()
                ),
                'genooLeads' => $this->getUserRolesDropdonws(),
                'genooFormMessages' => array(
                    array(
                        'name' => 'sucessMessage',
                        'label' => __('Successful form submission message', 'genoo'),
                        'type' => 'textarea',
                        'desc' => __('This is default message displayed upon form success.', 'genoo'),
                        'default' => __('Thank your for your subscription.', 'genoo')
                    ),
                    array(
                        'name' => 'errorMessage',
                        'label' => __('Failed form submission message', 'genoo'),
                        'type' => 'textarea',
                        'desc' => __('This is default message displayed upon form error.', 'genoo'),
                        'default' => __('There was a problem processing your request.', 'genoo')
                    ),
                ),
                'genooThemeSettings' => array(
                    array(
                        'desc' => __('Set the theme to use for your forms. “Default” means that Genoo forms will conform to the default form look associated with your WordPress theme.', 'genoo'),
                        'type' => 'desc',
                        'name' => 'genooForm',
                        'label' => '',
                    ),
                    array(
                        'name' => 'genooFormTheme',
                        'label' => __('Form theme', 'genoo'),
                        'type' => 'select',
                        'attr' => array(
                            'onchange' => 'Genoo.switchToImage(this)'
                        ),
                        'options' => $this->getSettingsThemes()
                    ),
                    array(
                        'name' => 'genooFormPrev',
                        'type' => 'html',
                        'label' => __('Form preview', 'genoo'),
                    ),
                ),
                'genooCTA' => array(
                    array(
                        'name' => 'genooCTAPostTypes',
                        'label' => __('Enable CTA for', 'genoo'),
                        'type' => 'multicheck',
                        'checked' => array('post' => 'true', 'page' => 'true'),
                        'options' => $this->getPostTypes()
                    ),
                ),
                'genooMisc' => array(
                    array(
                        'name' => 'genooCheckIframeURI',
                        'label' => __('WordPress editor pop-up', 'wpmktengine'),
                        'type' => 'checkbox',
                        'desc' => __('Force using HTTP protocol for pop-up window in WordPress TinyMCE.', 'wpmktengine'),
                    ),
                    array(
                        'name' => 'genooCTASave',
                        'label' => __('Caching Folder', 'wpmktengine'),
                        'type' => 'select',
                        'options' => array(
                            'wpcontent' => '/wp-content/cache_wpme',
                            'plugin' => '/wp-content/plugins/genoo/cache',
                            'uploads' => '/wp-content/uploads/cache_wpme'
                        )
                    ),
                ),
                self::KEY_LANDING => array(
                    array(
                        'name' => 'globalHeader',
                        'label' => __('Global Header scripts / html', 'wpmktengine'),
                        'type' => 'textarea',
                        'sanatize' => false,
                        'desc' => __('<strong style="color: red;">Please note,</strong> that all input placed here will be outputed on <strong>all your landing pages</strong> and any invalid html, non-working javascript, protocol invalid script can break the output rendering as a result.', 'wpmktengine'),
                    ),
                    array(
                        'name' => 'globalHeaderEverywhere',
                        'label' => __('Use Global Header on WordPress pages as well?', 'wpmktengine'),
                        'type' => 'checkbox',
                    ),
                    array(
                        'name' => 'globalFooter',
                        'label' => __('Global Footer scripts / html', 'wpmktengine'),
                        'type' => 'textarea',
                        'sanatize' => false,
                        'desc' => __('<strong style="color: red;">Please note,</strong> that all input placed here will be outputed on <strong>all your landing pages</strong> and any invalid html, non-working javascript, protocol invalid script can break the output rendering as a result.', 'wpmktengine'),
                    ),
                    array(
                        'name' => 'globalFooterEverywhere',
                        'label' => __('Use Global Footer on WordPress pages as well?', 'wpmktengine'),
                        'type' => 'checkbox',
                    ),
                )
            ),
            $this
        );
    }


    /**
     * Get landing pages global footer and header
     *
     * @param string $which
     * @return string
     */
    public static function getLandingPagesGlobal($which = 'header')
    {
        return self::getOption(
            ($which === 'header' ? 'globalHeader' : 'globalFooter'),
            self::KEY_LANDING,
            ''
        );
    }

    /**
     * @return bool|string
     */
    public static function getWordPressGlobalHeader()
    {
        $display = self::getOption('globalHeaderEverywhere', self::KEY_LANDING, false);
        $display = $display === 'on' ? true : false;
        // Exit early
        if(!$display){
            return;
        }
        // Return global header
        echo self::getLandingPagesGlobal('header');
    }

    /**
     * @return bool|string
     */
    public static function getWordPressGlobalFooter()
    {
        $display = self::getOption('globalFooterEverywhere', self::KEY_LANDING, false);
        $display = $display === 'on' ? true : false;
        // Exit early
        if(!$display){
            return;
        }
        // Return global header
        echo self::getLandingPagesGlobal('footer');
    }

    /**
     * Get settings themes
     *
     * @return array
     */

    public static function getSettingsThemes()
    {
        $repositoryThemes = new RepositoryThemes();
        $array = $repositoryThemes->getDropdownArray();
        return array(
            'themeDefault' => 'Default',
            'themeBlackYellow' => 'Black &amp; Yellow',
            'themeBlue' => 'Blue',
            'themeFormal' => 'Formal',
            'themeBlackGreen' => 'Black &amp; Green',
            'themeGreeny' => 'Greeny',
        ) + $array;
    }

    /**
     * Get themes for TinyMCE
     *
     * @return array
     */
    public static function getSettingsThemesArrayTinyMCE()
    {
        $r = array();
        $array = self::getSettingsThemes();
        if($array){
            foreach($array as $key => $value){
                $r[] = array(
                    'text' => $value,
                    'value' => (string)$key,
                );
            }
        }
        return $r;
    }

    /**
     * Get CTA Dropdown types
     *
     * @return array
     */

    public function getCTADropdownTypes()
    {
        $r = array(
            'link' => __('Link', 'genoo'),
            'form' => __('Form in Pop-up', 'genoo'),
        );
        if(GENOO_LUMENS){
            $r['class'] = __('Class List', 'genoo');
        }
        return $r;
    }


    /**
     * Get Lumens Dropdown
     *
     * @param RepositoryLumens $repo
     * @return array|null
     */

    public function getLumensDropdown(\WPMKTENGINE\RepositoryLumens $repo)
    {
        if(GENOO_LUMENS && isset($repo)){
            try {
                $lumensPlaceohlder = array('' =>  __('-- Select Class List', 'genoo'));
                $lumens = $repo->getLumensArray();
                return array(
                    'type' => 'select',
                    'label' => __('Class List', 'genoo'),
                    'options' => $lumensPlaceohlder + $lumens
                );
            } catch(\Exception $e){
                $this->addSavedNotice('error', 'Lumens Repository error:' . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Sets default Post Types
     */
    public static function saveFirstSettings()
    {
        $option = \get_option(self::KEY_CTA);
        if(empty($option)){
            \update_option(self::KEY_CTA, array(
                'genooCTAPostTypes' =>
                    array(
                        'post' => 'post',
                        'page' => 'page',
                    ),
            ));
        }
        // Remove lumens option if in place
        \delete_option(self::KEY_LUMENS);
    }


    /**
     * Flush all settings
     */

    public static function flush()
    {
        delete_option(self::KEY_SETTINGS);
        delete_option(self::KEY_GENERAL);
        delete_option(self::KEY_THEME);
        delete_option(self::KEY_MSG);
        delete_option(self::KEY_CTA);
        delete_option(self::KEY_LEADS);
        delete_option(self::KEY_MISC);
        delete_option(self::KEY_LANDING);
        delete_option('genooDebug');
        delete_option('genooDebugCheck');
    }
}