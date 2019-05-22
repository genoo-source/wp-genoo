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

use WPMKTENGINE\CTA,
    WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\WidgetForm,
    WPMKTENGINE\Wordpress\Attachment,
    WPMKTENGINE\Wordpress\Post;


/**
 * Class WidgetCTA
 * @package WPMKTENGINE
 */

class WidgetCTA extends \WP_Widget
{

    /** @var \WPMKTENGINE\CTA  */
    var $cta;
    /** @var \WPMKTENGINE\WidgetForm  */
    var $widgetForm;
    /** @var bool */
    var $isSingle = false;
    /** @var bool  */
    var $skipSet = false;
    /** @var bool  */
    var $skipMobileButton = false;
    /** @var array  */
    var $shortcodeAtts = array();
    /** @var bool */
    var $canHaveMobile = false;
    /** @var bool */
    public $isWidgetCTA = false;


    /**
     * Constructor registers widget in WordPress
     *
     * @param bool $constructParent
     */
    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct(
                'genoocta',
                apply_filters('genoo_wpme_widget_title_cta', 'WPMKTGENGINE: CTA'),
                array(
                    'description' =>
                        apply_filters(
                            'genoo_wpme_widget_description_cta',
                            __('WPMKTGENGINEff Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'wpmktengine')
                        )
                )
            );
        }
    }


    /**
     * Construct Dynamic Widget
     *
     * @param $id_base
     * @param $name
     * @param array $widget_options
     * @param array $control_options
     */
    function __constructDynamic($id_base, $name, $widget_options = array(), $control_options = array())
    {
        parent::__construct($id_base, $name, $widget_options, $control_options);
    }


    /**
     * Set
     */
    public function set($id = null)
    {
        global $post;
        if(is_object($post) && ($post instanceof \WP_Post)){
            global $post;
            $this->isSingle = true;
            $this->cta = new CTA($post);
            $this->widgetForm = new WidgetForm(false);
            $this->widgetForm->id = $this->id;
        }
    }


    /**
     * Set Widget Through Shortcode
     *
     * @param $id
     * @param $posr
     * @param $atts
     */

    public function setThroughShortcode($id, $post, $atts = array())
    {
        $this->isSingle = true;
        $this->skipSet = true;
        $this->canHaveMobile = false;
        $this->cta = new CTA();
        $this->cta->setCta($post);
        $this->id = $this->id_base . 'Shortcode' . $id;
        $this->widgetForm = new WidgetForm(false);
        $this->widgetForm->id = $this->id;
        $this->skipMobileButton = true;
        $this->shortcodeAtts = $atts;
        return $this;
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        $this->set(0);
        // we only care about single post
        echo $this->getHtmlInner($args, $instance);
    }


    /**
     * Return CTA
     *
     * @return CTA
     */
    public function getCta(){ return $this->cta; }


    /**
     * Get HTML
     *
     * @param $a
     * @param $i
     * @return null|string
     */
    public function getHtml($a = null, $i = null)
    {
        $instance = !is_null($i) ? $i : $this->getInnerInstance();
        if(is_object($this->widgetForm) && method_exists($this->widgetForm, 'getHtml')){
            return $this->widgetForm->getHtml(array(), $instance);
        }
        return null;
    }

    /**
     * Get CTA Modal Class
     *
     * @param array $instance
     * @return string
     */
    public function getCTAModalClass($instance = array())
    {
        $r = '';
        if(is_array($instance) && array_key_exists('theme', $instance)){
            $r .= 'genooModal' . Strings::firstUpper($instance['theme']) . ' ';
        }
        if(isset($instance['popup']['image-on']) && !empty($instance['popup']['image-on'])){
            $image = wp_get_attachment_image($instance['popup']['image'], 'medium', FALSE);
            if($image){
                $r .= 'genooModalPopBig';
            }
        }
        return $r;
    }


    /**
     * Get Inner Instance - for modal window processing.
     *
     * @param bool $skip
     * @return array
     */
    public function getInnerInstance()
    {
        $instance = array();
        if($this->isSingle){
            if($this->skipSet == false) $this->set();
            if($this->cta->has){
                $instance = array();
                $instance['modal'] = 0;
                $instance['choice'] = $this->cta->isHtml ? 'html' : 'img';
                if($this->cta->isImage){
                    $instance['img'] = $this->cta->image;
                    $instance['imgHover'] = $this->cta->imageHover;
                } else {
                    $instance['button'] = $this->cta->linkText;
                }
                $instance['form'] = $this->cta->formId;
                $instance['theme'] = $this->cta->formTheme;
                $instance['desc'] = $this->cta->desc;
                $instance['title'] = $this->cta->title;
                $instance['displayTitle'] = $this->cta->displayTitle;
                $instance['displayDesc'] = $this->cta->displayDesc;
                $instance['msgSuccess'] = $this->cta->messageSuccess;
                $instance['msgFail'] = $this->cta->messageError;
                $instance['skipMobileButton'] = $this->skipMobileButton;
                $instance['shortcodeAtts'] = $this->shortcodeAtts;
                $instance['popup'] = $this->cta->popup;
            }
        }
        return $instance;
    }


    /**
     * Get inner HTML
     *
     * @param $args
     * @param $instance
     * @return string
     */
    public function getHtmlInner($args, $instance)
    {
        $r = '';
        if($this->isSingle){
            $bid = 'button'. $this->id;
            if($this->cta->has){
                $instance = array();
                $instance['modal'] = 1;
                $instance['choice'] = $this->cta->isHtml ? 'html' : 'img';
                if($this->cta->isImage){
                    $instance['img'] = $this->cta->image;
                    $instance['imgHover'] = $this->cta->imageHover;
                } else {
                    $instance['button'] = $this->cta->linkText;
                }
                $instance['form'] = $this->cta->formId;
                $instance['lumen'] = $this->cta->classList;
                $instance['theme'] = '';
                $instance['desc'] = $this->cta->desc;
                $instance['title'] = $this->cta->title;
                $instance['displayTitle'] = $this->cta->displayTitle;
                $instance['displayDesc'] = $this->cta->displayDesc;
                $instance['skipMobileButton'] = $this->skipMobileButton;
                $instance['shortcodeAtts'] = $this->shortcodeAtts;
                $instance['canHaveMobile'] = $this->canHaveMobile;
                $instance['popup'] = $this->cta->popup;
                $instance['isPopOver'] = $this->cta->isPopOver;
                $instance['popOverTime'] = $this->cta->popOverTime;
                $instance['popOverHide'] = $this->cta->popOverHide;
                $instance['hideButton'] = isset($this->shortcodeAtts['time']) ? TRUE : FALSE;
                $instance['hideButtonTIME'] = isset($this->shortcodeAtts['time']) ? $this->shortcodeAtts['time'] : 0;
                $instance['followOriginalUrl'] = $this->cta->followOriginalUrl;
                $instance['originalCTA'] = $this->cta;
                $isHidePopOver = $instance['isPopOver'] && $instance['popOverHide'] ? TRUE : FALSE;
                if($this->cta->isForm || $this->cta->isClasslist){
                    $r .= $this->widgetForm->getHtml($args, $instance);
                    // Append to footer modals
                    // Combine instances
                    $instances = array_merge($instance, $this->getInnerInstance());
                    $injectingArray = new \stdClass();
                    $injectingArray->widget = $this;
                    $injectingArray->instance = $instances;
                    $injectingArray->instance['modal'] = 0;
                    $GLOBALS['WPME_MODALS'][$this->id] = $injectingArray;
                } elseif($this->cta->isLink){
                    // before widget
                    $r .= isset($args['before_widget']) ? $args['before_widget'] : '';
                    // title and data
                    if(isset($instance['displayTitle']) && $instance['displayTitle'] == true){ $r .= '<div class="genooTitle">' . $args['before_title'] . $instance['title'] . $args['after_title'] . '</div>'; }
                    if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){ $r .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>'; }
                    // only links
                    if($this->cta->isLink){
                        // if is aligned
                        $isInlineBlock = FALSE;
                        // If there are any additional attributes append later
                        $isAdditionalAttributes = $this->cta->isNewWindow === TRUE ? 'onclick="if(event.preventDefault) event.preventDefault(); else event.returnValue = false; var w = window.open(\''. $this->cta->link .'\', \'_blank\'); w.focus();"' : 'onclick="if(event.preventDefault) event.preventDefault(); else event.returnValue = false; window.location.href = \''. $this->cta->link .'\';"';
                        $blank = $this->cta->isNewWindow ? 'target="_blank"' : '';
                        $hidden = (isset($instance['hideButton']) && $instance['hideButton'] == TRUE) ? 'style="display:none"' : '';
                        if(isset($instance['shortcodeAtts']) && is_array($instance['shortcodeAtts'])){
                            if(isset($instance['shortcodeAtts']['align'])){
                                $isInlineBlock = TRUE;
                            }
                        }
                        if($isInlineBlock){
                            $alignClass = is_string($instance['shortcodeAtts']['align']) ? $instance['shortcodeAtts']['align'] : '';
                            $r .= '<div class="genooInlineBlock '. $alignClass .'">';
                        }
                        // Set attributes
                        $attributes = '';
                        if(isset($this->cta->button_class) || isset($this->cta->button_id)){
                            // Added filters to apply custom class and id from plugins or
                            // theme modifications
                            $attribute_id = apply_filters(
                                'wpmktengine_cta_button_css_id',
                                $this->cta->button_id,
                                $this->id,
                                $this->cta
                            );
                            $attribute_class = apply_filters(
                                'wpmktengine_cta_button_css_class',
                                $this->cta->button_class,
                                $this->id,
                                $this->cta
                            );
                            $attributes = 'id="'. $attribute_id .'" class="'. $attribute_class .'"';
                        }
                        $r .= '<form '. $blank .' method="POST" action="'. $this->cta->link .'" class="genooGenrated genooGenratedForm">';
                            $r .= '<span id="'. $bid .'" '. $hidden .'>';
                                $r .= '<input '. $attributes .' type="submit" value="'. $this->cta->linkText .'" '. $isAdditionalAttributes .' />';
                            $r .= '</span>';
                        $r .= '</form>';
                        if($isInlineBlock){
                            $r .= '</div>';
                        }
                        if($this->cta->isImage && (!empty($this->cta->image) || !empty($this->cta->imageHover))){
                            $r .= Attachment::generateCss($this->cta->image, $this->cta->imageHover, $bid, 'full');
                        }
                    } elseif($this->cta->isClasslist){

                    }
                    $r .= isset($args['after_widget']) ? $args['after_widget'] : '';
                    if(isset($instance['hideButton']) && $instance['hideButton'] == TRUE){
                        $r .= WidgetForm::getModalFullScrollJavascript($bid, (int)$instance['hideButtonTIME']);
                    }
                }
            }
        }
        return $r;
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */
    public function form($instance){ echo '&nbsp;'; }
}
