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
use WPMKTENGINE\RepositoryLandingPages;

class RepositoryPages extends Repository
{
    /** @var \WPMKTENGINE\Cache */
    private $cache;
    /** @var \WPMKTENGINE\Api */
    public $api;
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'pages';


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

    public function getPages()
    {
        $prepForms = '';
        try{
            if (!$prepForms = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)){
                $prepForms = $this->api->getPages();
                $this->cache->set(self::REPO_NAMESPACE, $prepForms, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        return $prepForms;
    }

    /**
     * Rename Page
     *
     * @param $id
     * @param $name
     * @return bool
     */
    public function renamePage($id, $name)
    {
        return $this->api->renamePage($id, $name);
    }


    /**
     * Get page, cached / or from API
     *
     * @param $id
     * @return bool|mixed
     */

    public function getPage($id)
    {
        $prepForm = '';
        try{
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)){
                $prepForm = $this->api->getPage($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch(\Exception $e){}
        // Get Lumen class without "http://" and "https://" here already
        return $prepForm;
    }


    /**
     * Get Pages Array
     *
     * @return array
     */

    public function getPagesArray()
    {
        $formsVars = array();
        try{
            $forms = $this->getPagesTable();
            if($forms){
                foreach($forms as $form){ $formsVars[$form['id']] = $form['name']; }
            }
        } catch(\Exception $e){}
        return $formsVars;
    }

    /**
     * @return array
     */
    public function getPagesArrayDropdown()
    {
        $arr = $this->getPagesArray();
        $arr[0] = __('Select page template', 'wpmktengine');
        return $arr;
    }


    /**
     * Get pages for listing table
     *
     * @return array
     */

    public function getPagesTable()
    {
        $forms = array();
        $prepForms = $this->getPages();
        $prepDependencies = RepositoryLandingPages::findDependenciesForTemplateWithPosts();
        if(!empty($prepForms) && is_array($prepForms)){
            foreach($prepForms as $form){
                $form = (object)$form;
                $dependency = array();
                if(array_key_exists($form->id, $prepDependencies)){
                    $dependency = $prepDependencies[$form->id];
                }
                $forms[] = array(
                    'id' => $form->id,
                    'name' => $form->name,
                    'created' => $form->create_date,
                    'landing' => $dependency
                );
            }
        }
        return $forms;
    }


    /**
     * Delete Page
     *
     * @param $id
     * @return bool
     */
    public function deletePage($id)
    {
        $result = $this->api->deletePage($id);
        return $result;
    }


    /**
     * @return bool
     */

    public function flush(){ return $this->cache->flush(self::REPO_NAMESPACE); }
}