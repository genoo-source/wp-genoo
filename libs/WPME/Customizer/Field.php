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
 * Class Field
 *
 * @package WPME\Customizer
 */
class Field
{

    /**
     * @var \WPME\Customizer\Customizer
     */
    protected $customizer;

    /**
     * @var mixed
     */
    public $id;

    /**
     * @var mixed
     */
    public $idView;

    /**
     * @var \WPME\Customizer\View
     */
    public $view;

    /**
     * @var array
     */
    protected $dataArray = array();

    /**
     * @var array
     */
    protected $fieldArray = array();

    /**
     * @var string
     */
    protected $dataCapability = 'edit_theme_options';

    /**
     * @var
     */
    protected $dataSection;

    /**
     * Options are:
     *  - option
     *  - theme_mod
     *  - post_meta (this one is unique to our plugin)
     *
     * @var string
     */
    protected $dataType = 'post_meta';

    /**
     * @var
     */
    protected $fieldDefaultValue;

    /**
     * @var
     */
    protected $fieldType;

    /**
     * @var array
     */
    protected $fieldChoices = array();

    /**
     * @var
     */
    protected $fieldLabel;

    /**
     * @var
     */
    protected $fieldIsJavascript = false;

    /**
     * @var
     */
    protected $fieldId = '';

    /**
     * @var string
     */
    protected $fieldDescription = '';

    /**
     * @var array
     */
    protected $fieldAttrs = array();

    /**
     * @var array
     */
    protected $binder = array();


    /**
     * Field constructor.
     *
     * @param \WPME\Customizer\Customizer $that
     * @param \WPME\Customizer\View $view
     * @param $fieldId
     */
    public function __construct(\WPME\Customizer\Customizer &$that, \WPME\Customizer\View &$view, $fieldId)
    {
        $this->customizer = $that;
        $this->view = $view;
        $this->id = $that->getId();
        $this->idView = $view->getId();
        $this->fieldId = $fieldId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->fieldId;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getFieldIdentificator()
    {
        return $this->idView . '[' . $this->fieldId . ']';
    }

    /**
     * @return mixed
     */
    public function getFieldDefaultValue()
    {
        return $this->fieldDefaultValue;
    }

    /**
     * @return null|mixed
     */
    public function getCurrentValue()
    {
        return $this->view->getValueOf($this->getId());
    }


    /**
     * @param $fieldChoices
     * @return $this
     */
    public function setFieldChoices($fieldChoices)
    {
        $this->fieldChoices = $fieldChoices;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $dataCapability
     * @return $this
     */
    public function setDataCapability($dataCapability)
    {
        $this->dataCapability = $dataCapability;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $dataSection
     * @return $this
     */
    public function setDataSection($dataSection)
    {
        $this->dataSection = $dataSection;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $fieldDefaultValue
     * @return $this
     */
    public function setFieldDefaultValue($fieldDefaultValue)
    {
        $this->fieldDefaultValue = $fieldDefaultValue;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * Sets auto default value
     *
     * @return $this
     */
    public function setAutoDefaultValue($valueFallback = '')
    {
        $this->fieldDefaultValue = $this->view->getValueOf($this->fieldId);
        if(empty($this->fieldDefaultValue)){
            $this->fieldDefaultValue = $valueFallback;
        }
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $fieldIsJavascript
     * @return $this
     */
    public function setFieldIsJavascript($fieldIsJavascript)
    {
        $this->fieldIsJavascript = $fieldIsJavascript;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $fieldLabel
     * @return $this
     */
    public function setFieldLabel($fieldLabel)
    {
        $this->fieldLabel = $fieldLabel;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $fieldType
     * @return $this
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
        $this->afterFieldSetting();
        return $this;
    }


    /**
     * @param array $fieldAttrs
     */
    public function setFieldAttrs($fieldAttrs)
    {
        $this->fieldAttrs = $fieldAttrs;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param string $fieldDescription
     */
    public function setFieldDescription($fieldDescription)
    {
        $this->fieldDescription = $fieldDescription;
        $this->afterFieldSetting();
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setFieldAttribute($key, $value)
    {
        $this->fieldAttrs[$key] = $value;
        return $this;
    }


    /**
     * Attach
     */
    public function attach()
    {
        // Before attaching
        $this->beforeAttaching();
        // Return if fake customizer
        if($this->customizer->wp_customize instanceof \WPME\Customizer\DummyCustomize) return;
        // Attach setting
        $this->customizer->wp_customize->add_setting($this->getFieldIdentificator(), $this->dataArray);
        // Attach control
        switch($this->fieldType){
            case 'date';
            case 'hidden';
            case 'number';
            case 'url';
            case 'email';
            case 'dropdown-pages';
            case 'textarea';
            case 'text';
            case 'select';
            case 'radio';
            case 'checkbox';
                $this->fieldArray['type'] = $this->fieldType;
                $this->customizer->wp_customize->add_control($this->fieldId, $this->fieldArray);
                break;
            case 'image';
                $this->customizer->wp_customize->add_control(
                    new \WP_Customize_Image_Control(
                        $this->customizer->wp_customize,
                        $this->fieldId,
                        array(
                            'label'    => $this->fieldLabel,
                            'section'  => $this->idView,
                            'description' => $this->fieldDescription,
                            'settings' => $this->getFieldIdentificator(),
                        )
                    )
                );
                break;
            case 'upload';
                $this->customizer->wp_customize->add_control(
                    new \WP_Customize_Upload_Control(
                        $this->customizer->wp_customize,
                        $this->fieldId,
                        array(
                            'label'    => $this->fieldLabel,
                            'section'  => $this->idView,
                            'description' => $this->fieldDescription,
                            'settings' => $this->getFieldIdentificator(),
                        )
                    )
                );
                break;
            case 'color';
                $this->customizer->wp_customize->add_control(
                    new \WP_Customize_Color_Control(
                        $this->customizer->wp_customize,
                        $this->fieldId,
                        array(
                            'label'    => $this->fieldLabel,
                            'section'  => $this->idView,
                            'description' => $this->fieldDescription,
                            'settings' => $this->getFieldIdentificator(),
                        )
                    )
                );
                break;
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        switch($key){
            case 'fieldDefaultValue';
                $this->dataArray['default'] = $value;
                break;
            case 'fieldIsJavascript';
                // Javascript field?
                if($value === true){
                    $this->dataArray['transport'] = 'postMessage';
                } else {
                    unset($this->dataArray['transport']);
                }
                break;
            case 'fieldLabel';
                $this->fieldArray['label'] = $value;
                break;
            case 'fieldChoices':
                $this->fieldArray['choices'] = $value;
                break;
            case 'fieldDescription':
                $this->fieldArray['description'] = $value;
                break;
            case 'fieldAttrs':
                $this->fieldArray['input_attrs'] = $value;
                break;
        }
    }

    /**
     * After Field value
     */
    public function afterFieldSetting()
    {
        $vars = get_object_vars($this);
        foreach($vars as $key => $value){
            $this->set($key, $value);
        }
    }

    /**
     * Before Attaching
     */
    public function beforeAttaching()
    {
        // Empty id?
        if(empty($this->fieldId)){
            throw new \InvalidArgumentException('Missing field ID');
        }
        // Set data array
        $this->dataArray['capability'] = $this->dataCapability;
        $this->dataArray['type'] = $this->dataType;
        // Set field array
        $this->fieldArray['section'] = $this->idView;
        $this->fieldArray['settings'] = $this->getFieldIdentificator();
    }

    /**
     * @return bool
     */
    public function hasBinder()
    {
        return !empty($this->binder);
    }

    /**
     * @return array
     */
    public function getBinders()
    {
        return $this->binder;
    }

    /**
     * For now, we get only the first binder,
     * because right now we only bind one dynamic changer
     *
     * @return array
     */
    public function getBinder()
    {
        return $this->binder[0];
    }

    /**
     * This goes one above and does
     * the binder thing.
     *
     * @return \WPME\Customizer\Generator\Binder
     */
    public function bindDynamicChange()
    {
        return $this->binder[] = new \WPME\Customizer\Generator\Binder($this->getFieldIdentificator(), $this);
    }
}