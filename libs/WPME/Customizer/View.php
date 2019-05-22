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

use WPMKTENGINE\Utils\Strings;

/**
 * Class View
 *
 * @package WPME\Customizer
 */
class View
{
    /**
     * @var array
     */
    public $fields = array();

    /**
     * @var \WPME\Customizer\Customizer
     */
    public $customizer;

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $title;

    /**
     * @var
     */
    protected $template;

    /**
     * @var
     */
    protected $storageThemeMod;

    /**
     * @var
     */
    protected $storagePostMeta;

    /**
     * @var
     */
    protected $storageThemeOptions;

    /**
     * @var
     */
    protected $storageThemeObject;

    /**
     * @var array
     */
    protected $storageInjected = array();

    /**
     * View constructor.
     *
     * @param $id
     * @param $title
     * @param \WPME\Customizer\Customizer $that
     */
    public function __construct(
        $id,
        $title,
        \WPME\Customizer\Customizer &$that
    ){
        $this->id = $id;
        $this->title = $title;
        $this->customizer = $that;
    }


    /**
     * @param \WPME\Customizer\Generator\Template $template
     */
    public function setTemplate(\WPME\Customizer\Generator\Template $template)
    {
        $this->template = $template;
    }


    /**
     * @param $key
     * @return \WPME\Customizer\Field
     */
    public function addField($key)
    {
        return $this->fields[$key] = new \WPME\Customizer\Field($this->customizer, $this, $key);
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param $key
     * @return bool
     */
    public function getField($key)
    {
        return isset($this->fields[$key]) ? $this->fields[$key] : false;
    }

    /**
     * Sets value - if we inject it in from
     * Frontend controller.
     *
     * @param $key
     * @param $value
     */
    public function setValueOf($key, $value)
    {
        $this->storageInjected[$key] = $value;
    }

    /**
     * Get current value of field from either default value
     * or saved in DB depending on storage type.
     *
     * @param $key
     * @return mixed|null
     */
    public function getValueOf($key)
    {
        // Lazy loading of data?
        $field = $this->getField($key);
        if($field !== false){
            // This could be injected from "above" using ->setValueOf()
            // if that happens, we return this preset value
            if(isset($this->storageInjected[$key])){
                // Exit early with injected value
                return $this->storageInjected[$key];
            }
            // Apart from that, field storage?
            $fieldType = $field->getDataType();
            // Prep field storage
            $fieldStorage = array();
            // Depending on field type, get data
            switch($fieldType){
                // Lazyload data
                case 'option':
                    $fieldStorage = $this->storageThemeOptions = get_option($this->getId());
                    break;
                case 'theme_mod':
                    $fieldStorage = $this->storageThemeMod = get_theme_mod($this->getId());
                    break;
                case 'post_meta':
                case 'post_meta_single':
                    if($this->customizer->getPostId() !== false){
                        // Get unique id
                        $uniqueId =
                            // If it's single meta, unique key is just key :)
                            ($fieldType == 'post_meta_single')
                                ? $key : $this->customizer->getUniqueKey($this->getId());
                        // Get value
                        $fieldStorage = $this->storagePostMeta = get_post_meta($this->customizer->getPostId(), $uniqueId, true);
                    }
                    break;
                case 'post_object':
                    $fieldStorage = $this->storageThemeObject = get_post($this->customizer->getPostId());
                    break;
            }
            // Get me the data if it's in.
            if($fieldType == 'post_meta_single' && isset($fieldStorage)){
                return $fieldStorage;
            }
            // Get me the data if it's in.
            if($fieldType == 'post_object' && isset($fieldStorage->{$key})){
                return $fieldStorage->{$key};
            }
            // Get data for multi storage
            if(!$fieldStorage instanceof \WP_Post && isset($fieldStorage[$key])){
                return $fieldStorage[$key];
            }
            // If no data, get default value
            return $field->getFieldDefaultValue();
        }
        return null;
    }

    /**
     * Get Switch class of field
     * - this gives us class that is swtiching if selected yes
     *
     * @param $key
     * @param string $value
     * @return string
     */
    public function getSwitchClassOf($key, $value = 'yes')
    {
        $valueTarget = $this->getValueOf($key);
        $field = $this->getField($key);
        if($valueTarget == $value && isset($field)){
            // Field binder?
            $binder = $field->getBinder();
            return $binder->getTargetSwitch();
        }
        return '';
    }

    /**
     * Get Binder Selector
     *
     * @param $key
     * @return null
     */
    public function getBinderSelectorOf($key)
    {
        // Lazy loading of data?
        $field = $this->getField($key);
        if($field !== false){
            // Field binder?
            $binder = $field->getBinder();
            if(isset($binder)){
                return $binder->getSelector();
            }
            return null;
        }
        return null;
    }

    /**
     * Prefixed styles
     *
     * @param $key
     * @param $prefix
     * @return mixed|null|string
     */
    public function getBinderSelectorOfPrefixedWith($key, $prefix)
    {
        $selector = $this->getBinderSelectorOf($key);
        if(!is_null($selector)){
            $selectorExploded = explode(',', $selector);
            if(is_array($selectorExploded) && count($selectorExploded) > 1){
                $returnString = '';
                foreach($selectorExploded as $selector2){
                    $selector2 = ltrim($selector2);
                    if(\WPMKTENGINE\Utils\Strings::startsWith($selector2, 'body')){
                        // If not customizer
                        if(!\WPME\Customizer\Customizer::isCustomizer()){
                            continue;
                        } else {
                            $returnString .= $selector2;
                        }
                    } elseif(\WPMKTENGINE\Utils\Strings::startsWith($selector2, '.gn-custom-modal')){
                        $returnString .= $prefix . $selector2;
                    } else {
                        $returnString .= $prefix . ' ' . $selector2;
                    }
                    if(end($selectorExploded) !== $selector2){
                        $returnString .= ', ';
                    }
                }
                return rtrim(rtrim($returnString), ',');
            }
            // Clean
            $selector = ltrim($selector);
            // Return good reuslt
            if(\WPMKTENGINE\Utils\Strings::startsWith($selector, '.gn-custom-modal')){
                return $prefix . $selector;
            }
            return $prefix . ' ' . $selector;
        }
        return $selector;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Attach Fields
     */
    public function attach()
    {
        // Attach section
        $this->customizer->wp_customize->add_section(
            $this->id,
            array(
                'title' => $this->title
            )
        );
        // Attach fields
        if(!empty($this->fields)){
            foreach($this->fields as $field){
                $field->attach();
            }
        }
    }

    /**
     * Render Javascript
     */
    public function attachJavascriptRender()
    {
        // If we have fields
        if(!empty($this->fields)){
            // Go through fields
            foreach($this->fields as $field){
                if($field->hasBinder()){
                    // Has binders?
                    foreach($field->getBinders() as $binder){
                        $binder->renderJavascript();
                    }
                }
            }
        }
    }

    /**
     * Render Javascript
     */
    public function attachCSSRender()
    {
        // If we have fields
        if(!empty($this->fields)){
            // Go through fields
            foreach($this->fields as $field){
                if($field->hasBinder()){
                    // Has binders?
                    foreach($field->getBinders() as $binder){
                        $binder->renderCSS();
                    }
                }
            }
        }
    }
}