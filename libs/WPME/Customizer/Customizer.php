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
 * Class Customizer
 *
 * @package WPME\Customizer
 */
class Customizer
{
    /**
     * @var array
     */
    public $views = array();

    /**
     * @var
     */
    public $template;

    /**
     * @var
     */
    public $wp_customize;

    /**
     * @var
     */
    public $ctaID;

    /**
     * @var
     */
    public $formID = false;

    /**
     * @var string
     */
    public $id = '';


    /**
     * Customizer constructor.
     *
     * @param $wp_customize
     */
    public function __construct($wp_customize)
    {
        $this->wp_customize = $wp_customize;
    }

    /**
     * Is it fake Customizer? Used for frontend
     * delivery of data?
     *
     * @return bool
     */
    public function isFakeCustomizer()
    {
        return $this->wp_customize instanceof \WPME\Customizer\DummyCustomize;
    }

    /**
     * @param $id
     * @param $title
     * @return \WPME\Customizer\View
     */
    public function addView($id, $title)
    {
        // Add view!
        return $this->views[$id] = new \WPME\Customizer\View(
            $id,
            $title,
            $this
        );
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Get view
     *
     * @param $id
     * @return bool|mixed
     */
    public function getView($id)
    {
        if(isset($this->views[$id])){
            return $this->views[$id];
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * Attach views
     *  -> it goes through fields
     *      -> attaches fields
     */
    public function attach()
    {
        // Attach views
        if(!empty($this->views)){
            foreach($this->views as $view){
                $view->attach();
            }
        }
        // Globalize customizer
        global $WPME_CUSTOMIZER;
        $WPME_CUSTOMIZER = $this;
    }

    /**
     * Render Javascript Renderers
     */
    public function attachJavascriptRenders()
    {
        if(!empty($this->views)){
            foreach($this->views as $view){
                $view->attachJavascriptRender();
            }
        }
    }

    /**
     * Render Javascript Renderers
     */
    public function attachCSSRenders()
    {
        if(!empty($this->views)){
            foreach($this->views as $view){
                $view->attachCSSRender();
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
     * @param $id_base
     * @return string
     */
    public static function getUniqueKey($id_base)
    {
        return 'gnmdl_' . $id_base;
    }

    /**
     * Get PostID
     *
     * @return bool|num
     */
    public function getPostId()
    {
        return
            isset
            ($_GET['gn-cust'])
                ? $_GET['gn-cust']
                : (
                    (
                    isset($this->ctaID)
                        ?
                        $this->ctaID
                        :
                        false
                    )
            );
    }

    /**
     * @return mixed
     */
    public function getCtaID()
    {
        return $this->ctaID;
    }

    /**
     * @return mixed
     */
    public function getFormID()
    {
        return $this->formID;
    }

    /**
     * @param $ctaID
     */
    public function setCtaID($ctaID){ $this->ctaID = $ctaID; }

    /**
     * @param $formID
     */
    public function setFormID($formID){ $this->formID = $formID; }

    /**
     * Reset after rendering
     */
    public function resetFormID(){ $this->formID = false; }


    /**
     * This is used to figure out,
     * if there is more then one "View" section.
     *
     * If there is just one, we auto_focus to it.
     *
     * @return bool
     */
    public function isSingleView()
    {
        return count($this->views) == 1;
    }

    /**
     * Get first view id
     *
     * @return mixed
     */
    public function getFirstViewId()
    {
        return reset($this->views)->getId();
    }

    /**
     * @param $block
     * @throws \InvalidArgumentException
     */
    public function getPartial($block)
    {
        $templateStorage = new \WPME\Customizer\TemplateStorage();
        try {
            switch($block){
                case 'form':
                    // If classic CTA
                    if($this->getFormID() == false){
                        return $templateStorage->getFormForCta(
                            $this->getPostId(),
                            !$this->isFakeCustomizer() // block elements? Only if rendered in customizer
                        );
                    }
                    // This might be form widget
                    // Get form id, and reset it straight away
                    // just so it's used only for this instance
                    $formId = $this->getFormID();
                    $this->resetFormID();
                    return $templateStorage->getFormFor(
                        $formId,
                        !$this->isFakeCustomizer() // block elements? Only if rendered in customizer
                    );
                    break;
            }
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }
}