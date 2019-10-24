<?php
/**
 * WPME Plugin
 *
 * PHP version 5.5
 *
 * @category WPMKTGENGINE
 * @package WPMKTGENGINE
 * @author  Genoo, LLC <info@genoo.com>
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License, version 2
 * @link    https://profiles.wordpress.org/genoo#content-about
 */
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.
 * (web: http://www.wpmktgengine.com/)
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
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\TablePages;

/**
 * @category WPME
 * @package RepositoryPages
 * @author Genoo, LLC <info@genoo.com>
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License, version 2
 * @link self
 */
class RepositoryPages extends Repository
{
    /**
     * @var \WPMKTENGINE\Cache
     */
    private $cache;
    /**
     * @var \WPMKTENGINE\Api
     */
    public $api;

    /**
     * Directory Key map
     */
    public $directoryTree;
    /**
     * 3600 seconds = hour
     * 3600 seconds = hour
    */
    const REPO_TIMER = '3600';
    /**
     * cache namespace
    */
    const REPO_NAMESPACE = 'pages';

    /**
     * Default folder
     */
    const REPO_DEFAULT_FOLDER = 'Uncategorised';

    /**
     * The depth of the tree
     */
    CONST REPO_SORT_NAME = '__sort_name';

    /**
     * Globals variable name
     */
    CONST FOLDER_STRUCTURE = 'WPME_LANDING_PAGES_FOLDER_STRUCTURE';
    CONST FOLDER_JS_STRUCTURE = 'WPME_LANDING_PAGES_JS_FOLDER_STRUCTURE';

    /**
     * @param Cache $cache
     */
    public function __construct(\WPMKTENGINE\Cache $cache, \WPME\ApiFactory $api)
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
        try {
            if (!$prepForms = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)) {
                $prepForms = $this->api->getPages();
                $this->cache->set(self::REPO_NAMESPACE, $prepForms, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch (\Exception $e) {
        }
        return $prepForms;
    }

    /**
     * Rename Page
     *
     * @param  $id
     * @param  $name
     * @return bool
     */
    public function renamePage($id, $name)
    {
        return $this->api->renamePage($id, $name);
    }


    /**
     * Get page, cached / or from API
     *
     * @param  $id
     * @return bool|mixed
     */
    public function getPage($id)
    {
        $prepForm = '';
        try {
            if (!$prepForm = $this->cache->get((string)$id, self::REPO_NAMESPACE)) {
                $prepForm = $this->api->getPage($id);
                $this->cache->set((string)$id, $prepForm, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch (\Exception $e) {
        }
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
        try {
            $forms = $this->getPagesTable();
            if ($forms) {
                foreach ($forms as $form) {
                    $formsVars[$form['id']] = $form['name'];
                }
            }
        } catch (\Exception $e) {
        }
        return $formsVars;
    }

    /**
     * @return array
     */
    public function getPagesArrayDropdown()
    {
        $arr = $this->getPagesArray();
        array_unshift($arr, __('Select page template', 'wpmktengine'));
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
        if (!empty($prepForms) && is_array($prepForms)) {
            foreach ($prepForms as $form) {
                $form = (object)$form;
                $dependency = array();
                if (is_array($prepDependencies) && @array_key_exists($form->id, $prepDependencies)) {
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

    public function pureSearchQuery($searchQuery = ''){
      if($searchQuery === '') return $searchQuery;
      $ret = str_replace(
        array(
          WPMKTENGINE_HOME_URL . '/',
          WPMKTENGINE_HOME_URL,
        ),
        array(
          '',
          '',
        ),
        $searchQuery
      );
      return $ret;
    }

    /**
     * Get pages for listing table
     *
     * @return array
     */
    public function getStructuredPagesTable($searchQuery = '')
    {
      $searchQuery = $this->pureSearchQuery($searchQuery);
      $pages = array();
      $pagesFromDatabase = $this->getPages();
      $pagesDependencies = RepositoryLandingPages::findDependenciesForTemplateWithPosts();
      $pagesTree = $this->explodeTree(
        $pagesFromDatabase,
        $pagesDependencies,
        $searchQuery,
        function($leafPart, $returnedValue) use ($pagesDependencies) {
          return array(
            self::REPO_SORT_NAME => $leafPart,
            'id' => $returnedValue->id,
            'name' => $returnedValue->name,
            'created' => $returnedValue->create_date,
            'landing' => $returnedValue->landing,
            // Turn on highlight if it's a searched item
            'className' => $returnedValue->highlight ? ' highlight ' : '',
          );
        } 
      );
      return $pagesTree;
    }

    /**
     * Explode Tree
     * - This does iterate one more time through all
     * but it is the fastest way this time. 
     */
    public function explodeTree($array, $pagesDependencies = array(), $searchQuery, $valueGenerator = false)
    {
      $delimiter = ' / ';
      if(!is_array($array)) return false;
      $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
      $returnArr = array();
      $generatedCollapseStructure = array();
      $returnFolderStructure = array(
        '' => __('No folder.', 'wpmktengine')
      );
      $canHide = $searchQuery !== '';
      foreach ($array as $key => $val) {
        $val = (object)$val;
        $name = strtolower($val->name);
        $id = strtolower($val->id);
        $parts	= preg_split($splitRE, $name, -1, PREG_SPLIT_NO_EMPTY);
        $partsCount = count($parts);
        $leafPart = Strings::trim(array_pop($parts));
        $parentArr = &$returnArr;
        $parentArrGen = &$generatedCollapseStructure;
        $folderName = '';
        $canHide = $searchQuery !== '';
        $highlight = false;
        $landingPages = is_array($pagesDependencies) && array_key_exists($val->id, $pagesDependencies)
                ? $pagesDependencies[$val->id]
                : array();
        $val->landing = $landingPages;
        $val->highlight = $highlight;
        // We will remove elements that don't match search if we search
        if($canHide){
          // 1. Post title
          if(Strings::contains($name, strtolower($searchQuery))){
            $highlight = true;
          }
          if(Strings::contains($id, strtolower($searchQuery)) || $id === $searchQuery){
            $highlight = true;
          }
          // 2. Landing pages title / URL
          if(count($landingPages) > 0){
            foreach($landingPages as $landingPage){
              $title = strtolower($landingPage->post_title);
              $url = strtolower(get_post_meta($landingPage->ID, 'wpmktengine_landing_url', true)); 
              if(Strings::contains($title, $searchQuery) || Strings::contains($url, $searchQuery)){
                $highlight = true;
              }
            }
          }
          $val->highlight = $highlight;
          if(!$highlight){
            continue;
          }
        }
        foreach ($parts as $part) {
          $part = Strings::trim($part);
          $id = $part;
          if($partsCount > 1){
            $folderName .= $part . ' / ';
            $returnFolderStructure[$folderName] = $folderName;
          }
          $initArray = array(self::REPO_SORT_NAME => $part);
          if (!isset($parentArr[$part])) {
            $parentArr[$part] = $initArray;
            $parentArrGen[$id] = array();
          } elseif (!is_array($parentArr[$part])) {
            $parentArr[$part] = $initArray;
            $parentArrGen[$id] = array();
          }
          $parentArr = &$parentArr[$part];
          $parentArrGen = &$parentArrGen[$id];
        }
        if (empty($parentArr[$leafPart])) {
          if(is_callable($valueGenerator)){
            $value = $valueGenerator($leafPart, $val);
            if($value){
              $parentArr[$leafPart] = $valueGenerator($leafPart, $val);
            }
          } else {
            $parentArr[$leafPart] = $val;
          }
        }
      }
      // Generate
      $generatedCollapseStructure = $this->generateCollapseFolder($returnArr);
      // Save folder structure
      $GLOBALS[self::FOLDER_STRUCTURE] = $returnFolderStructure;
      $GLOBALS[self::FOLDER_JS_STRUCTURE] = $generatedCollapseStructure;
      return $returnArr;
    }


    public function generateCollapseFolder($returnArr){
      $iterator = new \RecursiveIteratorIterator(
          new \RecursiveArrayIterator($returnArr),
          \RecursiveIteratorIterator::SELF_FIRST
      );
      $filtered = array();
      $filteredLastArrayHelper = array();
      foreach ($iterator as $key => $item) {
        // Get if we are deep down
        $currentDepth = $iterator->getDepth();
        $isNested = $currentDepth > 0;
        // We only care about this field
        if($key === '__sort_name'){
          $itemId = TablePages::get_row_id($item);
          if(!isset($filtered[$itemId])) $filtered[$itemId] = array();
          // Remember last level with key to match
          $filteredLastArrayHelper[$currentDepth] = $itemId;
          if($isNested){
            for ($x = $currentDepth - 1; $x >= 0; $x--) {
              if(is_array($filteredLastArrayHelper) && array_key_exists($x, $filteredLastArrayHelper)){
                $insertKey = $filteredLastArrayHelper[$x];
                array_push($filtered[$insertKey], $itemId);
              }
            } 
          } else {
            // Reset the array
            $filteredLastArrayHelper = array();
          }
        }
      }
      return $filtered;
    }

    /**
     * Delete Page
     *
     * @param  $id
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
    public function flush()
    {
        return $this->cache->flush(self::REPO_NAMESPACE);
    }
}
