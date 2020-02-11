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

use WPMKTENGINE\Utils\ArrayObject;
use WPMKTENGINE\Utils\Strings;

/**
 * Class TemplateStorage
 *
 * @package WPME\Customizer
 */
class TemplateStorage
{
    /** @var */
    public $themeDir;

    /**
     * TemplateStorage constructor.
     */
    public function __construct()
    {
        $dir = WPMKTENGINE_ROOT;
        $this->themeDir =  $dir . DIRECTORY_SEPARATOR . 'assets'. DIRECTORY_SEPARATOR .'templates'. DIRECTORY_SEPARATOR;
    }


    /**
     * @return array
     */
    public function getThemesData()
    {
        $r = array();
        // Get all tempalte files
        $files = glob($this->themeDir . '*');
        // Loop through if there are data
        if($files && is_array($files) && !empty($files)){
            foreach($files as $file){
                // layout.php is not dir, exclude
                if(is_dir($file)){
                    $themeId = basename($file);
                    $data = $this->getThemeData($themeId);
                    if($data !== false){
                        $r[] = $data;
                    }
                }
            }
        }
        // Return
        return $r;
    }


    /**
     * @param $themeId
     * @return array|bool
     */
    public function getThemeData($themeId)
    {
        $file =$this->themeDir . $themeId;
        if(is_dir($file)){
            $templateDir = $file;
            $templateInfo = require_once $file . DIRECTORY_SEPARATOR . 'theme.php';
            $templateImage = $this->getThemeFileUrl($themeId, 'theme.jpg');
            $templateTemplate = $this->getThemeFileUrl($themeId, 'index.html');
            $templateJs = $this->getThemeFileUrl($themeId, 'index.js');
            $templateStyle = $this->getThemeFileUrl($themeId, 'style.php');
            // Assign
            return array(
                'id' => basename($file),
                'template' => $templateTemplate,
                'js' => $templateJs,
                'style' => $templateStyle,
                'image' => $templateImage,
            ) + (array)$templateInfo;
        }
        return false;
    }

    /**
     * @param $themeId
     * @return bool|string
     */
    public function getThemeControls($themeId)
    {
        $file = $this->themeDir . $themeId;
        if(is_dir($file)){
            return $file . DIRECTORY_SEPARATOR . 'controls.php';
        }
        return false;
    }

    /**
     * Gets layout for Customizer
     */
    public function getThemeTemplateLayout($ctaTheme)
    {
        // Get theme data
        $customizer = new \stdClass();
        $customizer->title = get_post($_GET['gn-cust'])->post_title;
        // Headers
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: text/html; charset=utf-8');
        // Set ID of modal window
        $GLOBALS['WPME_MODAL_ID'] = '#modalWindowGncustomizer';
        // Get layout template
        include_once $this->themeDir . 'layout.php';
        die;
    }

    /**
     * Prepares Template for Customizer window.
     * This process is different in actuall mounting to modal on frontend.
     *
     * @param null $themeId
     * @return string|bool
     */
    public function prepareTemplateForCusomizer($themeId = null)
    {
        $themeId = $themeId == null ? \WPME\Customizer\Customizer::getThm() : $themeId;
        $themeFile = $this->themeDir . $themeId . DIRECTORY_SEPARATOR . 'template.php';
        // Get contentws
        if($themeId !== null){
            return $this->__getContentsOf($themeFile);
        }
        return false;
    }

    /**
     * Prepare Template for Modal Window (CTA version)
     *
     * @param \WPME\Customizer\Customizer $customizer
     * @param \WP_Post $post
     * @param null $themeId
     * @param string $prefix
     * @param bool $withStyle
     * @return bool|string
     */
    public function prepareTemplateForModalWindow(
        \WPME\Customizer\Customizer $customizer,
        \WP_Post $post,
        $prefix = '',
        $themeId = null,    // you can overwrite the theme, but we mainly take it from
                            // post custom meta
        $withStyle = true   // assign global style and append to template?
    ){
        $themeCss = '';
        // Theme id from CTA
        if(is_null($themeId)){
            $themeId = $this->getModalTheme($post->ID);
        }
        // Theme template
        $themeFile = $this->themeDir . $themeId . DIRECTORY_SEPARATOR . 'template.php';
        // Set customizer cta
        $customizer->setCtaID($post->ID);
        // Generate CSS
        if($withStyle){
            // Set ID of modal window
            $GLOBALS['WPME_MODAL_ID'] = $prefix;
            // Get CSS
            $themeCss = $this->prepareTemplateStyleForCustomizer($themeId);
            // Remove Style thingy.
            $themeCss = str_replace(
                array(
                    '<style>',
                    '<style type="text/css">',
                    '</style>'
                ),
                array(
                    '',
                    '',
                    '',
                ),
                $themeCss
            );
            // Append global styles
            global $WPME_STYLES;
            $WPME_STYLES .= $themeCss;
            // Add scoped CSS
            $themeJustCss = $themeCss;
            $themeCss = '<style scoped>' . $themeJustCss . '</style>';
            // Add javascript version to it
            $themeCss .= '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . json_encode($themeJustCss) . '); }</script>';
        }
        // Return template
        if($themeId !== null){
            return $this->__getContentsOf($themeFile, $themeCss);
        }
        return '';
    }

    /**
     * Prepare Template for Modal Window (Form version)
     *
     * @param \WPME\Customizer\Customizer $customizer
     * @param string $prefix
     * @param bool $withStyle
     * @return bool|string
     */
    public function prepareTemplateForModalWindowForm(
        \WPME\Customizer\Customizer $customizer,
        $formId,
        $prefix = '',
        $withStyle = true   // assign global style and append to template?
    ){
        $themeId = 'default';
        $themeCss = '';
        // Theme template
        $themeFile = $this->themeDir . $themeId . DIRECTORY_SEPARATOR . 'template.php';
        // Set customizer cta
        $customizer->setFormID($formId);
        // Generate CSS
        if($withStyle){
            // Set ID of modal window
            $GLOBALS['WPME_MODAL_ID'] = $prefix;
            // Get CSS
            $themeCss = $this->prepareTemplateStyleForCustomizer($themeId);
            // Remove Style thingy.
            $themeCss = str_replace(
                array(
                    '<style>',
                    '<style type="text/css">',
                    '</style>'
                ),
                array(
                    '',
                    '',
                    '',
                ),
                $themeCss
            );
            // Append global styles
            global $WPME_STYLES;
            $WPME_STYLES .= $themeCss;
            // Add scoped CSS
            $themeJustCss = $themeCss;
            $themeCss = '<style scoped>' . $themeJustCss . '</style>';
            // Add javascript version to it
            $themeCss .= '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . json_encode($themeJustCss) . '); }</script>';
        }
        // Return template
        if($themeId !== null){
            return $this->__getContentsOf($themeFile, $themeCss);
        }
        return '';
    }

    /**
     * @param null $themeId
     * @return string
     */
    public static function modalIdentificator($themeId = null)
    {
        $themeId = $themeId == null ? \WPME\Customizer\Customizer::getThm() : $themeId;
        return 'gn-custom-modal-' . $themeId;
    }

    /**
     * Get's template style.php file to be printed in header.
     *
     * @param null $themeId
     * @return bool|string
     */
    public function prepareTemplateStyleForCustomizer($themeId = null)
    {
        $themeId = $themeId == null ? \WPME\Customizer\Customizer::getThm() : $themeId;
        $themeFile = $this->themeDir . $themeId . DIRECTORY_SEPARATOR . 'style.php';
        if($themeId !== null){
            return $this->__getContentsOf($themeFile);
        }
        return false;
    }


    /**
     * Get Form For
     *
     * @param $ctaFormId
     * @param $blockElements
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getFormFor($ctaFormId, $blockElements)
    {
        if(isset($ctaFormId)){
            // Globals
            global $WPME_API;
            global $WPME_CACHE;
            // Class name
            $formsRepositoryClassName =
                class_exists('\WPMKTENGINE\RepositoryForms')
                    ? '\WPMKTENGINE\RepositoryForms'
                    : '\Genoo\RepositoryForms';
            // Repository Forms
            $formsRepository = new $formsRepositoryClassName(
                $WPME_CACHE,
                $WPME_API
            );
            // Get form
            $form = $formsRepository->getForm((string)$ctaFormId);
            $formStyle = $formsRepository->getFormStyle((string)$ctaFormId);
            if($this->isMultiStepForm($formStyle)){
                $formPrefix = isset($GLOBALS['WPME_MODAL_ID']) ? $GLOBALS['WPME_MODAL_ID'] : '';
                $formStyle = $this->generateMultiStepCss($formPrefix);
            } else {
                $formStyle = '';
            }
            $formStyle = $this->appendFormStyles($formStyle);
            // Remove inline styling
            $this->removeSpecificStyleAttributes($form);
            if(isset($form) && $blockElements){
                return $this->__cleanUpForCustomizer($form) . $formStyle;
            }
            return $form . $formStyle;
        } else {
            throw new \InvalidArgumentException('No form set.');
        }
    }

    public function removeSpecificStyleAttributes(&$form)
    {
        // $form = preg_replace('%style="[^"]+"%i', '', $form, -1);
        $form = preg_replace('/background-color:[ ]{0,1}(#(?:[0-9a-f]{2}){2,4}|(#[0-9a-f]{3})|(rgb|hsl)a?\((-?\d+%?[,\s]+){2,3}\s*[\d\.]+%?\))[;]{0,1}/m', '', $form);
        $form = preg_replace('/color:[ ]{0,1}(#(?:[0-9a-f]{2}){2,4}|(#[0-9a-f]{3})|(rgb|hsl)a?\((-?\d+%?[,\s]+){2,3}\s*[\d\.]+%?\))[;]{0,1}/m', '', $form);
        // $form = preg_replace('~border:.+?;\s*~', '', $form);
        return $form;
    }

    /**
     *  Get Form For <ctaId />
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getFormForCta($ctaId = null, $blockElements = true)
    {
        $ctaId = $ctaId == null ? \WPME\Customizer\Customizer::getCust() : $ctaId;
        if($ctaId !== null){
            // form id
            $ctaFormId = get_post_meta($ctaId, 'form', true);
            if(isset($ctaFormId)){
                return $this->getFormFor($ctaFormId, $blockElements);
            } else {
                throw new \InvalidArgumentException('No form set for '. $ctaId .' CTA.');
            }
        }
        throw new \InvalidArgumentException('No CTA id set, can\'t retrieve form.');
    }



    /**
     * Clean up HTMl for customizer
     * - this is to disable inputs mainly.
     *
     * @param $html
     * @return mixed
     */
    public function __cleanUpForCustomizer($html, $blockElements = true)
    {
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        if($blockElements){
            $html = str_replace(
                array(
                    '<input ',
                    '<select ',
                    '<textarea ',
                ),
                array(
                    '<input disabled ',
                    '<select disabled ',
                    '<textarea disabled '
                ),
                $html
            );
        }
        return $html;
    }

    /**
     * @param string $style
     * @return bool
     */
    public function isMultiStepForm($style = '')
    {
        return Strings::contains($style, 'on-step-');
    }

    /**
     * @return string
     */
    public function generateMultiStepCss($formPrefix = '')
    {
        return "
        $formPrefix [id^=\"on-step-2\"]:not(:checked) ~ .gn-tier-0 { display: none !important; }
        $formPrefix [id^=\"on-step-2\"]:checked ~ .gn-step-1-overlay { display: none !important; }
        $formPrefix [for=\"on-step-2\"] { width: 100%; }
        ";
    }

    /**
     * @param $styles
     * @return string
     */
    public function appendFormStyles($styles)
    {
        if(!empty($styles)){
            $stylesCss = '<style scoped>' . $styles . '</style>';
            $stylesCss .= '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . json_encode($styles) . '); }</script>';
            global $WPME_STYLES;
            $WPME_STYLES .= $styles;
            return $stylesCss;
        }
        return '';
    }

    /**
     * Get file url
     *
     * @param $themeId
     * @param $file
     * @return string
     */
    public function getThemeFileUrl($themeId, $file)
    {
        // Folder
        $folder = $this->themeDir . $themeId . DIRECTORY_SEPARATOR . $file;
        // Get file url
        $folderClean = substr(strstr($folder, '/wp-content/'), strlen('/wp-content/'));
        $folderUrl = get_bloginfo('url') . '/wp-content/'. $folderClean;
        return $folderUrl;
    }


    /**
     * @param $file
     * @param $append
     * @return bool|string
     */
    public function __getContentsOf($file, $append = '')
    {
        if(file_exists($file)){
            ob_start();
            include $file;
            $contents = ob_get_contents(); // data is now in here
            ob_end_clean();
            return $contents . $append;
        }
        return false;
    }

    /**
     * Get Modal Theme for CTA by id
     *
     * @param $postId
     * @return mixed
     */
    public function getModalTheme($postId)
    {
        return get_post_meta($postId, '_wpme_modal_theme', true);
    }
}
