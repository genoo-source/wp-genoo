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

namespace WPMKTENGINE\Wordpress;

use WPMKTENGINE\Api,
    WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\Wordpress\Notice,
    WPMKTENGINE\Wordpress\Action,
    WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\RepositorySettings;

class Settings
{
    /** if true, displays erros as notices, if false, displays them underneath the field */
    const DISPLAY_NOTICE = false;
    /** @var array */
    private $sections = array();
    /** @var array */
    private $fields = array();
    /** @var \WPMKTENGINE\RepositorySettings */
    private $settingsRepo;
    /** @var \WPMKTENGINE\Api */
    private $api;


    /**
     * Constructor
     */

    public function __construct(\WPME\RepositorySettingsFactory $settingsRepo, \WPME\ApiFactory $api)
    {
        $this->settingsRepo = $settingsRepo;
        $this->api = $api;
        $this->sections = $this->settingsRepo->getSettingsSections();
        $this->fields = $this->settingsRepo->getSettingsFields();
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        Action::add('admin_init', array($this, 'adminInit'));
    }


    /**
     * Enqueue scripts and styles
     */

    public function adminEnqueueScripts()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
    }


    /**
     * Set settings sections
     *
     * @param $sections
     * @return Settings
     */

    public function setSections($sections){ $this->sections = $sections; return $this; }


    /**
     * Add a single section
     *
     * @param $section
     * @return Settings
     */

    public function addSection($section){ $this->sections[] = $section; return $this; }


    /**
     * Set settings fields
     *
     * @param $fields
     * @return Settings
     */

    public function addFields($fields){ $this->fields = $fields; return $this; }


    /**
     * Add single field
     *
     * @param $section
     * @param $field
     * @return Settings
     */

    public function addField($section, $field)
    {
        $defaults = array(
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text'
        );
        $arg = wp_parse_args($field, $defaults);
        $this->fields[$section][] = $arg;
        return $this;
    }


    /**
     * Initialize and registers the settings sections and fileds to WordPress
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */

    public function adminInit()
    {
        //register settings sections
        if(Utils::isIterable($this->sections)){
            foreach($this->sections as $section){
                if (false == get_option($section['id'])){ add_option($section['id']); }
                if (isset($section['desc']) && !empty($section['desc'])){
                    $section['desc'] = '<div class="inside">'.$section['desc'].'</div>';
                    $callback = create_function('', 'echo "'.str_replace('"', '\"', $section['desc']).'";');
                } else {
                    $callback = '__return_false';
                }
                add_settings_section($section['id'], $section['title'], $callback, $section['id']);
            }
        }
        //register settings fields
        if(Utils::isIterable($this->fields)){
            foreach($this->fields as $section => $field){
                if(Utils::isIterable($field)){
                    foreach ($field as $option){
                        if(!empty($option)){
                            $type = isset($option['type']) ? $option['type'] : 'text';
                            $args = array(
                                'type' => $type,
                                'id' => $option['name'],
                                'desc' => isset($option['desc']) ? $option['desc'] : null,
                                'name' => $option['label'],
                                'section' => $section,
                                'options' => isset($option['options']) ? $option['options'] : '',
                                'std' => isset($option['default']) ? $option['default'] : '',
                                'attr' => isset($option['attr']) ? $option['attr'] : '',
                                'checked' =>isset($option['checked']) ? $option['checked'] : '',
                            );
                            add_settings_field($section . '[' . $option['name'] . ']', $option['label'], array($this, 'renderer'), $section, $section, $args);
                        }
                    }
                }
            }
        }
        // creates our settings in the options table
        if(Utils::isIterable($this->sections)){
            foreach ($this->sections as $section){
                register_setting($section['id'], $section['id'], array($this, 'validate'));
            }
        }
    }


    /**
     * Renderer
     *
     * @param $args
     */

    public function renderer($args)
    {
        // prep
        $fieldHtml = '';
        $fieldValue = @esc_attr($this->settingsRepo->getOption($args['id'], $args['section'], $args['std'])); // had to silence the inner wp function here
        $fieldValueUn = $this->settingsRepo->getOption($args['id'], $args['section'], $args['std']);
        $fieldClass = $this->hasError($args['id']) ? ' genooError' : '';
        $fieldError = $this->hasError($args['id']) ? $this->getError($args['id']) : false;
        $fieldErrorHtml = $fieldError ? '<br /><div class="clear"></div><span class="genooDescriptionError">' . $fieldError . '</span>' : '';
        $fieldDesc = $args['desc'] ? sprintf('<br /><div class="clear"></div><span class="description"> %s</span>', $args['desc']) : null;
        $fieldAttr = '';
        // add attributes
        if(is_array($args['attr'])){
            foreach($args['attr'] as $key => $value){
                $fieldAttr .= ' ' . $key . '="' . $value .'"';
            }
        }
        // switch renderer
        switch($args['type']){
            case 'text':
            case 'password':
                $fieldHtml .= sprintf('<input type="%1$s" class="regular-text gn-full %2$s" id="%3$s-%4$s" name="%3$s[%4$s]" value="%5$s" %6$s />', $args['type'], $fieldClass, $args['section'], $args['id'], $fieldValue, $fieldAttr);
                break;
            case 'checkbox':
                $fieldHtml .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
                $fieldHtml .= sprintf('<input type="checkbox" class="checkbox" id="%1$s-%2$s" name="%1$s[%2$s]" value="on"%4$s %5$s />', $args['section'], $args['id'], $fieldValue, checked($fieldValue, 'on', false), $fieldAttr);
                $fieldHtml .= sprintf('<label for="%1$s-%2$s"> %3$s</label>', $args['section'], $args['id'], $args['desc']);
                $fieldDesc = NULL;
                break;
            case 'multicheck':
                foreach ($args['options'] as $key => $label){
                    $checked = isset($fieldValueUn[$key]) ? $fieldValueUn[$key] : '0';
                    $checked = isset($args['checked'][$key]) ? 'checked' : checked($checked, $key, false);
                    $fieldHtml .= sprintf('<input type="checkbox" class="checkbox" id="%1$s-%2$s-%3$s" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, $checked);
                    $fieldHtml .= sprintf('<label data-key="'. $key .'" for="%1$s-%2$s-%4$s"> %3$s</label><br />', $args['section'], $args['id'], $label, $key);
                }
                break;
            case 'radio':
                foreach ($args['options'] as $key => $label){
                    $fieldHtml .= sprintf('<input type="radio" class="radio" id="%1$s-%2$s-%3$s" name="%1$s[%2$s]" value="%3$s"%4$s %5$s />', $args['section'], $args['id'], $key, checked($fieldValueUn, $key, false), $fieldAttr);
                    $fieldHtml .= sprintf('<label for="%1$s[%2$s][%4$s]"> %3$s</label><br />', $args['section'], $args['id'], $label, $key);
                }
                $fieldDesc = sprintf( '<br /><div class="clear"></div><span class="description"> %s</label>', $args['desc']);
                break;
            case 'select':
                $fieldHtml .= sprintf('<select class="regular gn-full %1$s" name="%2$s[%3$s]" id="%2$s-%3$s" %4$s>', $fieldClass, $args['section'], $args['id'], $fieldAttr);
                foreach ($args['options'] as $key => $label){
                    $fieldHtml .= sprintf( '<option value="%s"%s>%s</option>', $key, selected($fieldValue, $key, false), $label);
                }
                $fieldHtml .= sprintf('</select>');
                break;
            case 'textarea':
                $fieldHtml .= sprintf('<textarea rows="12" cols="55" class="regular-text gn-full %1$s" id="%2$s-%3$s" name="%2$s[%3$s]" %5$s>%4$s</textarea>', $fieldClass, $args['section'], $args['id'], esc_textarea($fieldValueUn), $fieldAttr);
                break;
            case 'html':
                $fieldHtml .= sprintf('<div id="%1$s-%2$s">%3$s</div>', $args['section'], $args['id'], $args['desc']);
                break;
            case 'color':
                $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
                $fieldHtml .= sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s-%3$s" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" %6$s />', $size, $args['section'], $args['id'], $fieldValue, $args['std'], $fieldAttr);
                break;
            case 'file':
                $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
                $id = $args['section']  . '[' . $args['id'] . ']';
                $js_id = $args['section']  . '\\\\[' . $args['id'] . '\\\\]';
                $fieldHtml .= sprintf( '<input type="text" class="%1$s-text" id="%2$s-%3$s" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $fieldValue);
                $fieldHtml .= '<input type="button" class="button wpsf-browse" id="'. $id .'_button" value="Browse" />
                            <script type="text/javascript">
                            jQuery(document).ready(function($){
                                $("#'. $js_id .'_button").click(function() {
                                    tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
                                    window.original_send_to_editor = window.send_to_editor;
                                    window.send_to_editor = function(html) {
                                        var url = $(html).attr(\'href\');
                                        if ( !url ) {
                                            url = $(html).attr(\'src\');
                                        };
                                        $("#'. $js_id .'").val(url);
                                        tb_remove();
                                        window.send_to_editor = window.original_send_to_editor;
                                    };
                                    return false;
                                });
                            });
                            </script>';
                break;
            case 'desc':
                $fieldDesc = '';
                $fieldHtml = '<span class="description descriptionAbsolute">'.  $args['desc'] .'</span>';
                break;
            // wysiwyg is bit different
            case 'wysiwyg':
                echo '<div style="width: 500px;">';
                    wp_editor(wpautop($fieldValueUn), $args['section'] . '[' . $args['id'] . ']', array('teeny' => true, 'textarea_rows' => 10));
                echo '</div>';
                echo $fieldHtml . (!self::DISPLAY_NOTICE ? $fieldErrorHtml : '') . $fieldDesc;
                break;
        }
        // render, if not wysiwyg, echo
        echo $args['type'] != 'wysiwyg' ? ($fieldHtml . (!self::DISPLAY_NOTICE ? $fieldErrorHtml : '') . $fieldDesc) : '';
    }


    /**
     * Has tabs?
     *
     * @return bool
     */

    private function hasTabs()
    {
        if(count($this->sections) > 1){
            return true;
        }
        return false;
    }


    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */

    public function render()
    {
        // render before
        if($this->hasTabs()){
            echo '<h2 class="nav-tab-wrapper">';
            foreach ($this->sections as $tab){
                echo sprintf('<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title']);
            }
            echo '</h2>';
        }
        // render content
        echo '<div class="metabox-holder">';
            echo '<div class="postbox">';
                // display errors as notices, if set
                if(self::DISPLAY_NOTICE){
                    foreach(get_settings_errors() as $err){
                        Notice::type('error')->text($err['message']);
                    }
                }
                // go through sections
                if(Utils::isIterable($this->sections)){
                    foreach($this->sections as $form){
                        echo '<div id="'. $form['id'] .'" class="group">';
                            echo '<form method="post" action="options.php">';
                                do_action('wsa_form_top_' . $form['id'], $form);
                                settings_fields($form['id']);
                                do_settings_sections($form['id']);
                                do_action('wsa_form_bottom_' . $form['id'], $form);
                                echo '<div style="padding-left: 10px">';
                                    submit_button();
                                echo '</div>';
                            echo '</form>';
                        echo '</div>';
                    }
                }
            echo '</div>';
        echo '</div>';
        // render after
        echo '<script type="text/javascript">';
            echo 'jQuery(document).ready(function($){';
                echo '$(\'.wp-color-picker-field\').wpColorPicker();';
                if($this->hasTabs()){
                    echo '$(".group").hide();var activetab="";if(typeof(localStorage)!="undefined"){activetab=localStorage.getItem("activetab")}if(activetab!=""&&$(activetab).length){$(activetab).fadeIn()}else{$(".group:first").fadeIn()}$(".group .collapsed").each(function(){$(this).find("input:checked").parent().parent().parent().nextAll().each(function(){if($(this).hasClass("last")){$(this).removeClass("hidden");return false}$(this).filter(".hidden").removeClass("hidden")})});if(activetab!=""&&$(activetab+"-tab").length){$(activetab+"-tab").addClass("nav-tab-active")}else{$(".nav-tab-wrapper a:first").addClass("nav-tab-active")}$(".nav-tab-wrapper a").click(function(a){$(".nav-tab-wrapper a").removeClass("nav-tab-active");$(this).addClass("nav-tab-active").blur();var b=$(this).attr("href");if(typeof(localStorage)!="undefined"){localStorage.setItem("activetab",$(this).attr("href"))}$(".group").hide();$(b).fadeIn();a.preventDefault()});';
                }
            echo '});';
        echo '</script>';
        echo '<style type="text/css">.form-table th{padding: 20px 10px} #wpbody-content .metabox-holder {padding-top: 5px}</style>';
    }

    /**
     * Validate | Sanatize
     *
     * @param $options
     * @return mixed
     */

    public function validate($options)
    {
        // check fields
        foreach($options as $key => $value){
            switch($key){
                // we need the tracking code as well
                case 'apiExternalTrackingCode':
                    if(empty($options[$key])){
                        $this->addError($key, __('Unfortunately Genoo plugin requires tracking code to work properly.', 'wpmktengine'));
                        $options[$key] = '';
                    }
                    break;
                // non-empty values
                case 'sucessMessage':
                case 'errorMessage':
                    if(empty($options[$key])){
                        $this->addError($key, __('Form message is a required field.', 'wpmktengine'));
                    }
                    break;
                // default, if it's not array, sanatize
                default:
                    $node = $this->findNode($key);
                    if(is_array($node) && (array_key_exists('sanatize', $node) && $node['sanatize'] == false)){
                        $options[$key] = $options[$key];
                        break;
                    }
                    if(!is_array($value)){
                        $options[$key] = sanitize_text_field($value);
                    }
                    break;
            }
        }
        return $options;
    }

    /**
     * @param $nodeName
     * @param string $fieldBy
     * @return mixed|null|void
     */
    public function findNode($nodeName, $fieldBy = 'name'){
        // Early exit
        if(empty($this->fields) || !is_array($this->fields)){
            return;
        }
        foreach ($this->fields as $key => $value){
            if(is_array($value) && !empty($value)){
                foreach ($value as $field => $fieldData){
                    if($fieldData[$fieldBy] == $nodeName){
                        return $fieldData;
                    }
                }
            }
        }
        return null;
    }


    /**
     * Has error
     *
     * @param $key
     * @return bool
     */

    private function hasError($key)
    {
        foreach(get_settings_errors() as $err){
            if($key == $err['setting']){
                return true;
            }
        }
        return false;
    }


    /**
     * Get error
     *
     * @param $key
     * @return bool
     */

    private function getError($key)
    {
        foreach(get_settings_errors() as $err){
            if($key == $err['setting']){
                return $err['message'];
            }
        }
        return false;
    }


    /**
     * Add error
     *
     * @param $key
     * @param $error
     * @return mixed
     */

    public function addError($key, $error){ return add_settings_error($key, 'genooId' . $key, $error, 'error'); }
}