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

use WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\Wordpress\Action;

class Metabox
{

    /** @var string */
    var $id;
    /** @var string */
    var $title;
    /** @var  */
    var $callback;
    /** @var string|array */
    var $postType;
    /** @var string */
    var $context = 'normal';
    /** @var string */
    var $priority = 'high';
    /** @var array */
    var $fields = array();
    /** @var string */
    var $nonceKey = '';

    /**
     * Metabox constructor.
     *
     * @param string $title
     * @param string $postType
     * @param array $fields
     * @param string $context
     * @param string $priority
     */
    function __construct($title, $postType, $fields, $context = 'normal', $priority = 'default')
    {
        // assign
        $this->title = $title;
        $this->id = Strings::webalize($title);
        $this->postType = $postType;
        $this->fields = $fields;
        $this->context = $context;
        $this->priority = $priority;
        $this->nonceKey = WPMKTENGINE_KEY . $this->id . 'Nonce';
        Action::add('add_meta_boxes',    array($this, 'register'), 1);
        Action::add('save_post',         array($this, 'save'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
    }


    /**
     * @param $hook
     */

    public function adminEnqueueScripts($hook)
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
    }


    /**
     * Register metaboxes
     */

    public function register(){
        if(is_array($this->postType)){
            foreach($this->postType as $postType){
                add_meta_box($this->id, $this->title, array($this, 'render'), $postType, $this->context, $this->priority);
            }
        } elseif (is_string($this->postType)) {
            add_meta_box($this->id, $this->title, array($this, 'render'), $this->postType, $this->context, $this->priority);
        }
    }


    /**
     * Save metabox
     *
     * @param $post_id
     * @return mixed
     */

    public function save($post_id){
        // check if our nonce is set.
        if (!isset($_POST[$this->nonceKey])){ return $post_id; }
        // nonce key
        $nonce = $_POST[$this->nonceKey];
        // verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, $this->id)){ return $post_id; }
        // if this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){ return $post_id; }
        // check the user's permissions.
        if (!current_user_can('edit_post', $post_id)){ return $post_id; }
        // Update the meta fields
        if(is_array($this->fields) && !empty($this->fields)){
            foreach($this->fields as $field){
                $fieldId = isset($field['id']) ? $field['id'] : str_replace('-', '_', Strings::lower(Strings::webalize($field['label'])));
                if(!empty($_POST[$fieldId])){
                    if(in_array($fieldId, array('wpmktengine_data_header', 'wpmktengine_data_footer'))){
                      update_post_meta($post_id, $fieldId, $_POST[$fieldId]);
                    } else {
                      update_post_meta($post_id, $fieldId, (sanitize_text_field($_POST[$fieldId])));
                    }
                  } elseif(empty($_POST[$fieldId])) {
                    delete_post_meta($post_id, $fieldId);
                }
            }
        }
    }


    /**
     * Form renderer
     *
     * @param $post
     */

    public function render($post){
        // set wp_nonce_field
        wp_nonce_field($this->id, $this->nonceKey);
        $metaboxForm = '<table class="themeMetabox">';
        $metaboxClear = '<div class="clear"></div>';
        // go through fields
        if(is_array($this->fields) && !empty($this->fields)){
            foreach($this->fields as $field){
                $fieldId = isset($field['id']) ? $field['id'] : str_replace('-', '_', Strings::lower(Strings::webalize($field['label'])));
                if(isset($field['type']) && (isset($field['label']))){
                    $fieldRow = '<tr class="themeMetaboxRow" id="themeMetaboxRow'. $fieldId .'" >';
                    $fieldValue = get_post_meta($post->ID, $fieldId, true);
                    $fieldLabel = '<td class="genooLabel"><label for="' . $fieldId . '">' . $field['label'] . '</label></td><td>';
                    $fieldOptions = isset($field['options']) ? $field['options'] : array();
                    $fieldAtts = '';
                }
                $fieldDesc = '';
                if(isset($field['desc'])){
                    $fieldDesc = $field['desc'];
                }
                if(isset($_GET) && is_array($_GET) && array_key_exists($fieldId, $_GET)){
                    // WP Reviewers - this prefills value for input, that gets sanatised later
                    // if sent to submit.
                    $fieldValue = $_GET[$fieldId];
                }
                $fieldBefore = isset($field['before']) ? $field['before'] : '';
                if(!empty($fieldBefore)){
                    $fieldBefore = '<span class="genooBefore">'. $fieldBefore .'</span>';
                }
                if(isset($field['atts']) && is_array($field['atts'])){ foreach($field['atts'] as $key => $value){ $fieldAtts .= ' '. $key .'="'. $value .'" '; } }
                switch($field['type']){
                    case 'text':
                    case 'number':
                    case 'tel':
                    case 'email':
                        $fieldInput = '<input id="'. $fieldId .'" name="'. $fieldId .'" type="' . $field['type'] . '" value="'. $fieldValue .'" '. $fieldAtts .' />';
                        $fieldInput .= $fieldDesc;
                        break;
                    case 'html':
                        $fieldLabel = '<tr class="themeMetaboxRow" id="themeMetaboxRow'. $fieldId .'" ><td colspan="2">';
                        $fieldInput = '<div id="' . $fieldId . '">' . $field['label'] . '</div></td>';
                        break;
                    case 'select':
                        $fieldInput = '<select id="'. $fieldId .'" name="'. $fieldId .'" '. $fieldAtts .'>';
                        if(!empty($fieldOptions) && is_array($fieldOptions)){
                            foreach($fieldOptions as $key => $option){
                                if($key == $fieldValue){ $selected = 'selected'; } else { $selected = ''; }
                                $fieldInput .= '<option value="' . $key . '" '. $selected .'>'. $option .'</option>';
                            }
                        }
                        $fieldInput .= '</select>';
                        break;
                    case 'textarea':
                        $fieldInput = '<textarea id="'. $fieldId .'" name="'. $fieldId .'" '. $fieldAtts .'>' . $fieldValue . '</textarea>';
                        break;
                    case 'checkbox':
                        if(true == $fieldValue){ $checked = 'checked'; } else { $checked = ''; }
                        $fieldInput = '<input '. $fieldAtts .' id="'. $fieldId .'" name="'. $fieldId .'" value="true" type="'. $field['type'] .'" '. $checked .' />';
                        break;
                    case 'radio':
                        $fieldLabel = '<span class="label">'. $field['label'] .'</span>';
                        $fieldInput = '<span class="radio">';
                        if(!empty($fieldOptions) && is_array($fieldOptions)){
                            foreach($fieldOptions as $key => $option){
                                if(Strings::webalize($option) == $fieldValue){ $selected = 'checked'; } else { $selected = ''; }
                                $fieldInputRadio = '<input type="radio" name="'. $fieldId .'" value="' . Strings::webalize($option) . '" '. $selected .' />';
                                $fieldInput .= '<label>' . $fieldInputRadio . ' ' . $option . '</label>';
                            }
                        }
                        $fieldInput .= '</span>';
                        break;
                    case 'image-select':
                        $fieldCurrent = is_numeric($fieldValue)
                            ? wp_get_attachment_image($fieldValue, 'medium', false)
                            : $fieldValue;
                        $fieldTarget =  $fieldId . 'Target';
                        $fieldLabelButton = 'Select Image';
                        $fieldLabel = '<td class="genooLabel"><label>'. $field['label'] .':</label></td><td>'
                            . '<div class="genooUploadSelect">'
                            . '<div class="genooWidgetImage" id="'. $fieldTarget .'">'
                            . $fieldCurrent
                            . '</div>';
                        $fieldInput = '<input type="hidden" name="'. $fieldId .'" id="'. $fieldId .'" value="'. $fieldValue .'" />';
                        $fieldInput .= '<a href="#" onclick="Modal.open(event,this);" '
                            . 'id="'. $fieldId . 'Btn' .'" '
                            . 'data-current-id="'. $fieldValue .'" '
                            . 'data-title="'. $fieldLabelButton .'" '
                            . 'data-update-text="'. $fieldLabelButton .'" '
                            . 'data-target="'. $fieldTarget .'" '
                            . 'data-target-input="'. $fieldId .'" '
                            . 'class="button">'. $fieldLabelButton .'</a>';
                        $fieldInput .= ' | ';
                        $fieldInput .= '<a href="#" onclick="Modal.emptyImage(event,'
                            . '\''. $fieldTarget . '\', '
                            . '\''. $fieldId . '\', '
                            . '\''. $fieldId . 'Btn' . '\');'
                            . '">'. __('Remove Image', 'wpmktengine') .'</a>';
                        $fieldInput .= '<div class="clear"></div>';
                        break;
                }
                if(isset($field['type']) && (isset($field['label']))){
                    // add elements to a row
                    $fieldRow .= $fieldLabel;
                    $fieldRow .= $fieldBefore . $fieldInput;
                    $fieldRow .= $metaboxClear . '</td>';
                    // add row to metabox form
                    $metaboxForm .= $fieldRow . '</tr>';
                }
            }
        }
        // render, well, echo
        echo $metaboxForm . '</table>';
    }
}
