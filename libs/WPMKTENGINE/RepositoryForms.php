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

use WPMKTENGINE\Wordpress\Utils;

class RepositoryForms extends Repository
{
    /** @var \WPMKTENGINE\Cache */
    private $cache;
    /** @var \WPMKTENGINE\Api */
    private $api;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'forms';


    /**
     * @param Cache $cache
     */

    function __construct(\WPMKTENGINE\Cache $cache, \WPME\ApiFactory $api)
    {
        $this->cache = $cache;
        $this->api = $api;
        parent::__construct();
    }


    /**
     * @return object|string
     */

    public function getForms()
    {
        $prepForms = '';
        try{
            if (!$prepForms = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)){
                $prepForms = $this->api->getForms();
                $this->setFormsFromAPIResult($prepForms);
                $this->cache->set(self::REPO_NAMESPACE, $prepForms, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepForms;
    }

    /**
     * Set all forms if they have HTML
     *
     * @param $forms
     * @throws \WPMKTENGINE\CacheException
     */
    public function setFormsFromAPIResult($forms)
    {
        if($forms && is_array($forms) && !empty($forms)){
            foreach($forms as $form){
                $form = (object)$form;
                $id = $form->id;
                if(isset($form->html)){
                    $this->cache->set((string)$id, $form, self::REPO_TIMER, self::REPO_NAMESPACE);
                }
            }
        }
    }

    /**
     * Get form, cached / or from API
     *
     * @param $id
     * @return bool|mixed
     */

    public function getForm($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->getForm($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){
            $prepForm = $e->getMessage();
        }
        // Legacy form?
        if(is_string($prepForm)){
            // Get Lumen class without "http://" and "https://" here already
            return Utils::nonProtocolUrl($prepForm);
        }
        if(is_object($prepForm)){
            return Utils::nonProtocolUrl($prepForm->html);
        } elseif(is_array($prepForm)){
            return Utils::nonProtocolUrl($prepForm['html']);
        }
        return '';
    }



    /**
     * @param $id
     * @return object|string
     */
    public function getFormObject($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->getForm($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){
            return $prepForm = $e->getMessage();
        }
        return (object)$prepForm;
    }

    /**
     * Get form style
     *
     * @param $id
     * @return string
     */
    public function getFormStyle($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->getForm($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        // Legacy form?
        if(is_object($prepForm)){
            return $prepForm->style;
        } else if(is_array($prepForm)){
            return $prepForm['style'];
        }
        return '';
    }


    /**
     * Get styles for form, prefixed
     * - this is used for forms cta's etc, to have styles
     *   prefixed with #id needed.
     *
     * @param $formId
     * @param $prefix
     * @return mixed|string
     */
    public function getFormStylePrefixd($formId, $prefix)
    {
        // Style
        $css = $this->getFormStyle($formId);
        return $this->prefixStyle($css, $prefix, true);
    }


    /**
     * Prefix style?
     *
     * @param $css
     * @param $prefix
     * @param bool $autoAppend
     * @return mixed|string
     */
    public static function prefixStyle($css, $prefix, $autoAppend = true)
    {
        if(!empty($css) || $css !== ''){
            // Prefix
            $parts = explode('}', $css);
            $mediaQueryStarted = false;
            foreach ($parts as &$part) {
                if (empty($part)) {
                    continue;
                }
                $partDetails = explode('{',$part);
                if(substr_count($part,"{")==2){
                    $mediaQuery = $partDetails[0]."{";
                    $partDetails[0] = $partDetails[1];
                    $mediaQueryStarted = true;
                }

                $subParts = explode(',', $partDetails[0]);
                foreach ($subParts as &$subPart) {
                    if(trim($subPart)=="@font-face") continue;
                    $subPart = $prefix . ' ' . trim($subPart);
                }

                if(substr_count($part,"{")==2){
                    $part = $mediaQuery."\n".implode(', ', $subParts)."{".$partDetails[2];
                }elseif(empty($part[0]) && $mediaQueryStarted){
                    $mediaQueryStarted = false;
                    $part = implode(', ', $subParts)."{".$partDetails[2]."}\n"; //finish media query
                }else{
                    $part = implode(', ', $subParts)."{".$partDetails[1];
                }
            }
            $prefixedCss = implode("}\n", $parts);
            $prefixedCss = str_replace("}", "}" . PHP_EOL, $prefixedCss);
            // Remove comments
            $regex = array(
                "`^([\t\s]+)`ism"=>'',
                "`^\/\*(.+?)\*\/`ism"=>"",
                "`([\n\A;]+)\/\*(.+?)\*\/`ism"=>"$1",
                "`([\n\A;\s]+)//(.+?)[\n\r]`ism"=>"$1" . PHP_EOL,
                "`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism"=> PHP_EOL
            );
            $prefixedCss = preg_replace(array_keys($regex), $regex, $prefixedCss);
            // Auto add to global styles
            if($autoAppend){
                global $WPME_STYLES;
                $WPME_STYLES .= $prefixedCss;
            }
            // Return
            return $prefixedCss;
        }
        return '';
    }


    /**
     * Get Forms Array
     *
     * @return array
     */

    public function getFormsArray()
    {
        $formsVars = array();
        try{
            $forms = $this->getFormsTable();
            if($forms){
                foreach($forms as $form){ $formsVars[$form['id']] = $form['name']; }
            }
        } catch(\Exception $e){}
        return $formsVars;
    }

    /**
     * TinyMCE has different Arrays
     *
     * @return array
     */
    public function getFormsArrayTinyMCE()
    {
        $formsVars = array();
        $formsVars[] = array(
            'text' => __('Select a Form'),
            'value' => '',
        );
        try{
            $forms = $this->getFormsTable();
            if($forms){
                foreach($forms as $form){
                    $formsVars[] = array(
                        'text' => $form['name'],
                        'value' => (string)$form['id'],
                    );
                }
            }
        } catch(\Exception $e){}
        return $formsVars;
    }


    /**
     * Get forms for listing table
     *
     * @return array
     */

    public function getFormsTable()
    {
        $forms = array();
        $prepForms = $this->getForms();
        if(!empty($prepForms) && is_array($prepForms)){
            foreach($prepForms as $form){
                $form = (object)$form;
                $forms[] = array(
                    'id' => $form->id,
                    'name' => $form->name,
                );
            }
        }
        return $forms;
    }


    /**
     * @return bool
     */

    public function flush(){ return $this->cache->flush(self::REPO_NAMESPACE); }
}