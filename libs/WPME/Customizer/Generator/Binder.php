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

namespace WPME\Customizer\Generator;

/**
 * Class Binder
 *
 * @package WPME\Customizer\Generator
 */
class Binder
{

    /**
     * @var
     */
    public $fieldId;

    /**
     * @var
     */
    public $selector;

    /**
     * Used for jquery class switching
     *
     * @var
     */
    protected $targetSwitch;

    /**
     * @var string
     */
    protected $function = 'text';

    /**
     * @var string
     */
    protected $functionData = '';

    /**
     * Attributes used for dynamic function creation
     *
     * @var
     */
    protected $attributes;

    /**
     * Binder constructor.
     *
     * @param $fieldId
     */
    public function __construct($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    /**
     * @param $selector
     * @return $this
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param mixed $targetSwitch
     */
    public function setTargetSwitch($targetSwitch)
    {
        $this->targetSwitch = $targetSwitch;
    }

    /**
     * @return mixed
     */
    public function getTargetSwitch()
    {
        return $this->targetSwitch;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param $function
     * @param string $value
     * @return $this
     */
    public function setFunction($function, $value = '')
    {
        $this->function = $function;
        $this->functionData = $value;
        return $this;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes = array())
    {
        $attributes = is_array($attributes) ? (object) $attributes : $attributes;
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get Javascript Renderer
     *
     * @return string
     */
    public function getJavascriptRender()
    {
        if(is_string($this->function)){
            switch($this->function){
                case 'text':
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                jQuery('{$this->selector}').text(to);
                            });
                        });
                        ";
                    break;
                case 'background-image':
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                0 === jQuery.trim(to).length ?
                                    jQuery('{$this->selector}').css('background-image', 'none') :
                                    jQuery('{$this->selector}').css('background-image', 'url(' + to + ')');
                            });
                        });
                        ";
                    break;
                case 'checkbox-switch-display':
                case 'checkbox-switch-hide':
                    // Depending on field we either dispaly on switch, or hide on switch
                    $functionFirst = $this->function == 'checkbox-switch-hide' ? 'hide' : 'show';
                    $functionSecond = $this->function == 'checkbox-switch-hide' ? 'show' : 'hide';
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                if(to == true){
                                    jQuery('{$this->selector}').{$functionFirst}();
                                } else {
                                    jQuery('{$this->selector}').{$functionSecond}();
                                }   
                            });
                        });
                        ";
                    break;
                case 'select-switch-class':
                    $uniqueFieldName = str_replace(array('[', ']'), array('-', ''), $this->fieldId);
                    $uniqueFieldName = str_replace('_', '-', $uniqueFieldName);
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                // Prev class
                                var previousClass = jQuery('{$this->selector}').attr('data-switch-{$uniqueFieldName}');
                                // Non-empty is a no no
                                if(to === 0 || to === '0' || to === null || to === ''){
                                    jQuery('{$this->selector}')
                                        .removeClass(previousClass)
                                        .attr('data-switch-{$uniqueFieldName}', null);
                                    return;
                                }
                                jQuery('{$this->selector}')
                                    .removeClass(previousClass)
                                    .addClass(to)
                                    .attr('data-switch-{$uniqueFieldName}', to);
                            });
                        });
                        ";
                    break;
                case 'color':
                case 'background-color':
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                jQuery('{$this->selector}').css('{$this->function}', to);
                            });
                        });
                        ";
                    break;
                case 'toggle-class':
                    return
                        "
                        wp.customize('{$this->fieldId}', function(value){
                            value.bind(function(to){
                                jQuery('{$this->selector}').toggleClass('{$this->targetSwitch}');
                            });
                        });
                        ";
                    break;
            }
        } else if(is_callable($this->function)){
            // Here comes the fun part, dynamic function call
            return call_user_func_array($this->function, array($this));
        }
    }

    /**
     * @return string
     */
    public function getCSSRender()
    {
        if(is_string($this->function)){
            switch($this->function){
                case 'background-image':
                    return
                        "";
                    break;
                case 'checkbox-switch-display':
                case 'checkbox-switch-hide':
                    // Depending on field we either dispaly on switch, or hide on switch
                    $functionFirst = $this->function == 'checkbox-switch-display' ? 'show' : 'hide';
                    $functionSecond = $this->function == 'checkbox-switch-display' ? 'hide' : 'show';
                    return
                        "";
                    break;
                case 'color':
                case 'background-color':
                    return
                        "";
                    break;
            }
        }
    }


    /**
     * @return string
     */
    public function renderCSS()
    {
        echo $this->getCSSRender();
    }

    /**
     * @return string
     */
    public function renderJavascript()
    {
        echo $this->getJavascriptRender();
    }
}