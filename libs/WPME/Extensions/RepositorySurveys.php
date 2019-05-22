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

namespace WPME\Extensions;

use Genoo\Utils\Strings;
use WPME\ApiFactory;
use WPME\CacheFactory;
use WPME\RepositoryFactory;

/**
 * Class RepositorySurveys
 *
 * @package WPME\Extensions
 */
class RepositorySurveys extends RepositoryFactory
{
    /** @var \WPMKTENGINE\Cache */
    private $cache;
    /** @var \WPMKTENGINE\Api */
    private $api;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'surveys';


    /**
     * @param Cache $cache
     */

    function __construct(CacheFactory $cache, ApiFactory $api)
    {
        $this->cache = $cache;
        $this->api = $api;
        parent::__construct();
    }


    /**
     * @return object|string
     */

    public function getSurveys()
    {
        $prepForms = '';
        try{
            if (!$prepForms = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)){
                $prepForms = $this->api->getAll();
                $this->cache->set(self::REPO_NAMESPACE, $prepForms, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepForms;
    }


    /**
     * Get survey
     *
     * @param $id
     * @return bool|mixed
     */

    public function getSurvey($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->get((string)$id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){
            $prepForm = $e->getMessage();
        }
        // Inject url
        if(!\WPMKTENGINE\Utils\Strings::contains('src="http://', $prepForm) || !\WPMKTENGINE\Utils\Strings::contains('src="https://', $prepForm)){
            $prepForm = str_replace('src="/js', 'src="//api.genoo.com/js', $prepForm);
        }
        // Inject lead id if there is one
        try {
            $leadIdCookie =  isset($_COOKIE['_gtld']) ? $_COOKIE['_gtld'] : false;
            $leadIdUrl = isset($_GET['upid']) ? $_GET['upid'] : false;
            $arrayOfValues = [];
            if($leadIdCookie){
                $arrayOfValues['_gtld'] = $leadIdCookie;
            }
            if($leadIdUrl){
                $arrayOfValues['upid'] = $leadIdUrl;
            }
            if (count($arrayOfValues) > 0){
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument;
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $prepForm);
                $dom->preserveWhiteSpace = false;
                $urlOld = $dom->getElementsByTagName('script')->item(0)->getAttribute('src');
                $urlNew = add_query_arg($arrayOfValues, $urlOld);
                $prepForm = str_replace($urlOld, $urlNew, $prepForm);
            }
        } catch (\Exception $e){
            // We do nothing
        }
        return $prepForm;
    }


    /**
     * Get Forms Array
     *
     * @return array
     */
    public function getSurveysArray()
    {
        $formsVars = array();
        try{
            $forms = $this->getSurveysTable();
            if($forms){
                foreach($forms as $form){ $formsVars[$form['id']] = $form['name']; }
            }
        } catch(\Exception $e){}
        return $formsVars;
    }

    /**
     * TinyMCE has different type of arrays
     *
     * @return array
     */
    public function getSurveysArrayTinyMCE()
    {
        $formsVars = array();
        $formsVars[] = array(
            'text' => __('Select a Survey'),
            'value' => '',
        );
        try{
            $forms = $this->getSurveysTable();
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

    public function getSurveysTable()
    {
        $forms = array();
        $prepForms = $this->getSurveys();
        if(!empty($prepForms) && is_array($prepForms) && count($prepForms) > 0){
            foreach($prepForms as $form){
                $form = (object)$form;
                $forms[] = array(
                    'id' => $form->id,
                    'name' => $form->name,
                    'published' => $form->published,
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
