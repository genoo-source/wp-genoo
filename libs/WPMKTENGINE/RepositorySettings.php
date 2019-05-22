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

namespace WPMKTENGINE;

use WPMKTENGINE\Wordpress\Post;
use WPMKTENGINE\Utils\Strings;


class RepositorySettings extends Repository
{
    /** settings key */
    const KEY_SETTINGS = 'WPMKTENGINEApiSettings';
    /** settings leads */
    const KEY_LEADS = 'genooLeads';
    /** general - used only by plugin calls */
    const KEY_GENERAL = 'WPMKTENGINEApiGeneral';
    /** theme */
    const KEY_THEME = 'WPMKTENGINEThemeSettings';
    /** form messages */
    const KEY_MSG = 'WPMKTENGINEFormMessages';
    /** CTA settings */
    const KEY_CTA = 'WPMKTENGINECTA';
    /** Misc */
    const KEY_MISC = 'WPMKTENGINEMISC';
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
        $this->key = WPMKTENGINE_KEY;
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
     * @return string
     */
    public static function getCacheDir(){
        if(class_exists('\Genoo\RepositorySettings')){
            $option = \Genoo\RepositorySettings::getOption(
                'genooCTASave',
                \Genoo\RepositorySettings::KEY_MISC,
                false
            );
        } else {
            $option = \WPMKTENGINE\RepositorySettings::getOption(
                'genooCTASave',
                \WPMKTENGINE\RepositorySettings::KEY_MISC,
                false
            );
        }
        switch ($option){
            case 'plugin':
                return GENOO_ROOT . 'cache' . DIRECTORY_SEPARATOR;
            case 'uploads':
                return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cache_wpme' . DIRECTORY_SEPARATOR;
            default:
                return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache_wpme' . DIRECTORY_SEPARATOR;
        }
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
        $arr[] = __('- Select commenter lead type', 'wpmktengine');
        if(WPMKTENGINE_PART_SETUP){
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
                'label' => __('Blog commenter lead type', 'wpmktengine'),
                'type' => 'select',
                'desc' => __('You control your Lead Types in: Lead Management > Leads.', 'wpmktengine'),
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

    public function getTrackingCode(){ return $this->getOption('apiExternalTrackingCode', self::KEY_SETTINGS); }


    /**
     * Get lead type
     *
     * @return string
     */

    public function getLeadType(){ return $this->getOption('commenter', self::KEY_SETTINGS); }

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
     * @param Api $api
     * @param bool $return
     */

    public function setFirstLeadTypes(\WPMKTENGINE\Api $api, $return = FALSE)
    {
        try {
            $leadTypes = $api->getLeadTypes();
            if(is_array($leadTypes)){
                $r = array();
                foreach($leadTypes as $leadType){
                    $lower = Strings::lower($leadType->name);
                    if(Strings::contains($lower, Strings::lower('Blog Commenter'))){
                        $r['commenter'] = $leadType->id;
                    }
                    if(Strings::contains($lower, Strings::lower('Blog Subscriber'))){
                        $r['subscriber'] = $leadType->id;
                    }
                }
                if(isset($r['commenter'])){
                    $this->injectSingle('commenter', $r['commenter'],self::KEY_SETTINGS);
                }
                if(isset($r['subscriber'])){
                    $this->injectSingle('subscriber', $r['subscriber'], self::KEY_LEADS);
                }
                if($return){
                    return $r;
                }
            }
        } catch (\Exception $e){
            if($return){
                return $e->getMessage();
            } else {
                switch($e->getMessage()){
                    case 'Wordpress HTTP Api: error:14077410:SSL routines:SSL23_GET_SERVER_HELLO:sslv3 alert handshake failure':
                        $this->addSavedNotice('error', 'Error setting up Commenter and Subscriber leads: ' . $e->getMessage());
                        break;
                    default:
                        $this->addSavedNotice('error', 'Error setting up Commenter and Subscriber leads: ' . $e->getMessage());
                        break;
                }
            }
        }
    }


    /**
     * Gets settings page sections
     *
     * @return array
     */

    public function getSettingsSections()
    {
        if(WPMKTENGINE_SETUP){
            return apply_filters(
                'wpmktengine_settings_sections',
                array(
                    array(
                        'id' => self::KEY_LEADS,
                        'title' => __('Leads', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_MSG,
                        'title' => __('Form messages', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_SETTINGS,
                        'title' => __('Form styles', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_CTA,
                        'title' => __('CTA', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_MISC,
                        'title' => __('Miscellaneous', 'wpmktengine')
                    ),
                    array(
                        'id' => self::KEY_LANDING,
                        'title' => __('Landing Pages', 'wpmktengine')
                    )
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
            $this->setOption('WPMKTENGINEDebug', true);
        } else {
            $this->deleteOption('WPMKTENGINEDebug');
        }
    }


    /**
     * Debug check removal
     *
     * @return mixed
     */

    public function flushDebugCheck(){ return $this->deleteOption('WPMKTENGINEDebugCheck'); }


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
        $r = array(0 => __('Select CTA', 'wpmktengine'));
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
            'desc' => __('Set default lead types for newly registered user roles.', 'wpmktengine'),
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
        $arr[] = __('- Don\'t save', 'wpmktengine');
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
            if($key !== 'subscriber'){
                $r[] = array(
                    'name' => 'genooLeadUser' . $key,
                    'label' => $role,
                    'type' => 'select',
                    'options' => $arr
                );
            }
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
                self::KEY_LEADS => $this->getUserRolesDropdonws(),
                self::KEY_MSG => array(
                    array(
                        'name' => 'sucessMessage',
                        'label' => __('Successful form submission message', 'wpmktengine'),
                        'type' => 'textarea',
                        'desc' => __('This is default message displayed upon form success.', 'wpmktengine'),
                        'default' => __('Thank your for your subscription.', 'wpmktengine')
                    ),
                    array(
                        'name' => 'errorMessage',
                        'label' => __('Failed form submission message', 'wpmktengine'),
                        'type' => 'textarea',
                        'desc' => __('This is default message displayed upon form error.', 'wpmktengine'),
                        'default' => __('There was a problem processing your request.', 'wpmktengine')
                    ),
                ),
                self::KEY_SETTINGS => array(
                    array(
                        'desc' => __('Set the theme to use for your forms. “Default” means that WPMKTGENGINE forms will conform to the default form look associated with your WordPress theme.', 'wpmktengine'),
                        'type' => 'desc',
                        'name' => 'genooForm',
                        'label' => '',
                    ),
                    array(
                        'name' => 'genooFormTheme',
                        'label' => __('Form theme', 'wpmktengine'),
                        'type' => 'select',
                        'attr' => array(
                            'onchange' => 'Genoo.switchToImage(this)'
                        ),
                        'options' => $this->getSettingsThemes()
                    ),
                    array(
                        'name' => 'genooFormPrev',
                        'type' => 'html',
                        'label' => __('Form preview', 'wpmktengine'),
                    ),
                ),
                self::KEY_CTA => array(
                    array(
                        'name' => 'genooCTAPostTypes',
                        'label' => __('Enable CTA for', 'wpmktengine'),
                        'type' => 'multicheck',
                        'checked' => array('post' => 'true', 'page' => 'true'),
                        'options' => $this->getPostTypes()
                    ),
                ),
                self::KEY_MISC => array(
                    array(
                        'name' => 'genooCheckIframeURI',
                        'label' => __('WordPress editor pop-up', 'wpmktengine'),
                        'type' => 'checkbox',
                        'desc' => __('Force using HTTP protocol for pop-up window in WordPress TinyMCE.', 'wpmktengine'),
                    ),
                    array(
                        'name' => 'genooCTASave',
                        'label' => __('Use WP-CONTENT/ folder for Caching', 'wpmktengine'),
                        'type' => 'checkbox',
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
                        'name' => 'globalFooter',
                        'label' => __('Global Footer scripts / html', 'wpmktengine'),
                        'type' => 'textarea',
                        'sanatize' => false,
                        'desc' => __('<strong style="color: red;">Please note,</strong> that all input placed here will be outputed on <strong>all your landing pages</strong> and any invalid html, non-working javascript, protocol invalid script can break the output rendering as a result.', 'wpmktengine'),
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
     * Get settings themes
     *
     * @param bool $appendCustom
     * @return array
     */
    public static function getSettingsThemes($appendCustom = true)
    {
        $repositoryThemes = new RepositoryThemes();
        $array = $appendCustom ? $repositoryThemes->getDropdownArray() : array();
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
            'link' => __('Link', 'wpmktengine'),
            'form' => __('Form in Pop-up', 'wpmktengine'),
        );
        if(WPMKTENGINE_LUMENS){
            $r['class'] = __('Class List', 'wpmktengine');
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
        if(WPMKTENGINE_LUMENS && isset($repo)){
            try {
                $lumensPlaceohlder = array('' =>  __('-- Select Class List', 'wpmktengine'));
                $lumens = $repo->getLumensArray();
                return array(
                    'type' => 'select',
                    'label' => __('Class List', 'wpmktengine'),
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
        delete_option(self::KEY_LANDING );
        delete_option('WPMKTENGINEDebug');
        delete_option('WPMKTENGINEDebugCheck');
    }

    /**
     * Reset installation
     */
    public function resetInstallation()
    {
        delete_option(self::KEY_SETTINGS);
    }
}