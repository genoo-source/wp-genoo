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

use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Notice;
use WPMKTENGINE\Wordpress\TableLite;
use WPMKTENGINE\Wordpress\Utils;

abstract class Table extends \WPMKTENGINE\Wordpress\TableLite
{
    /** @var array() */
    var $notices;
    /** @var string */
    var $url;
    /** @var string */
    var $tableName;
    /** @var string */
    var $tableSingleName;
    /** @var \WPMKTENGINE\RepositoryUser */
    var $user;
    /** @var \WP_Screen */
    var $screen;
    /** @var string */
    var $searchQuery;

    /**
     * Constructor
     */

    public function __construct($args = array())
    {
        // real url
        $this->url = Utils::getRealUrl();
        // vars
        preg_match('#Table(\w+)$#', get_class($this), $class);
        $this->tableName = Utils::camelCaseToUnderscore($class[1]);
        $this->tableSingleName = Strings::firstUpper($class[1]);
        // user repo
        $this->user = new RepositoryUser();
        $this->screen = get_current_screen();
        $this->screenId = str_replace('genoo_page_', '', $this->screen->id);
        $this->screenOptions = $this->screenId == 'Genoo' . $this->tableSingleName ? true : false;
        $this->userPerpage = $this->user->getOption('genoo_per_page');
        $this->perPage = $this->userPerpage ? $this->userPerpage : 50;
        $this->searchQuery = strtolower($this->get_search_query());
        // bam
        parent::__construct(array_merge(array('singular'=> 'log', 'plural' => 'logs', 'ajax' => false, 'screen' => $this->screen),$args));
    }


    /**
     * Get current perpage
     *
     * @return int
     */

    public function getPerPage()
    {
        if(isset($_POST['wp_screen_options'])){
            if(isset($_POST['wp_screen_options']['option']) && $_POST['wp_screen_options']['option'] == 'genoo_per_page'){
                return $_POST['wp_screen_options']['value'];
            }
        }
        return $this->perPage;
    }


    /**
     * No Items notices
     */
    function no_items(){ __('We are sorry, but there are no items to be listed.', 'wpmktengine'); }


    /**
     * Prepare items
     */
    public function prepare_items(){}

    /**
     * Reordering function.
     *
     * @param $a
     * @param $b
     * @return int
     */
    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }


    /**
     * Default columns display behaviour
     *
     * @param $item
     * @param $column_name
     * @return mixed
     */
    function column_default($item, $column_name)
    {
        if(is_array($item) && array_key_exists($column_name, $item)){
            return $item[$column_name];
        }
        return '';
    }

    /**
     * Attach class name
     */
    public function attach_class_name(&$item, $level){
      // Drafts don't count
      if(method_exists($this, 'isDrafts') && $this->isDrafts($item)){
        return;
      }
      // Extract original classname
      $className = array_key_exists('className', $item) ? $item['className'] : '';
      // Add className
      $item['className'] = $level !== 0 ? $className . ' nested ' . 'nested-level-' . (int)$level : $className;
      $item['className'] = str_replace('--', '-', $item['className']);
      $item['level'] = $level;
    }

    public function single_row($item) {
      // Keep the alternating class
      static $level = 0;
      $this->attach_class_name($item, $level);
      // Normal rows follow previous logic
      if(!$this->isFolder($item) || $this->isDrafts($item)){
        // Render old way
        parent::single_row($item);
        // Reset leveling
        $level = 0;
        return;
      }
      // First level folder
      parent::single_row($item);
      // Create a sub-loop of internal items
      foreach($item as $innerName => $innerValue){
        if($innerName === \WPMKTENGINE\RepositoryPages::REPO_SORT_NAME){
          continue;
        }
        if($innerName === 'className' || $innerName === 'level'){
          continue;
        }
        $goingDeeper = $this->isFolder($innerValue);
        $level++;
        $this->attach_class_name($innerValue, $level);
        $this->single_row($innerValue);
      }
    }

    public function isFolder($item){
      return !array_key_exists('id', $item);
    }


    /**
     * Get notices
     *
     * @return mixed
     */
    public function getNotices(){ return $this->notices; }


    /**
     * Has Messages
     *
     * @return bool
     */

    public function hasNotices(){ if(!empty($this->notices)){ return TRUE; } return FALSE; }


    /**
     * Adds Message to be returned later - anywhere we want
     *
     * @param $key
     * @param $msg
     */

    public function addNotice($key, $msg){ $this->notices[] = array($key, $msg); }


    /**
     * Sends notices to renderer
     */

    public function renderTableNotices()
    {
        if($this->notices){
            foreach($this->notices as $key => $value){
                $this->displayAdminNotice($value[0], $value[1]);
            }
        }
    }


    /**
     * Display admin notices
     *
     * @param null $class
     * @param null $text
     */

    private function displayAdminNotice($class = NULL,$text = NULL){ echo Notice::type($class)->text($text); }


    /**
     * Runs default renderer, but displays notices just before that.
     */

    public function display()
    {
        if(method_exists($this, 'process')){ $this->process(); }
        $this->prepare_items();
        $this->renderTableNotices();
        parent::display();
    }
}
