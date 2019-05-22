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
use WPMKTENGINE\Wordpress\Notice;
use WPMKTENGINE\Tools;

class TablePages extends Table
{
    /** @var \WPMKTENGINE\RepositoryPages */
    var $repositoryPages;
    /** @var \WPMKTENGINE\RepositorySettings */
    var $repositorySettings;
    /** @var string */
    var $activeForm;
    /** @var bool */
    var $set = FALSE;

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */

    function __construct(\WPMKTENGINE\RepositoryPages $repositoryPages, \WPME\RepositorySettingsFactory $repositorySettings)
    {
        global $status, $page;
        $this->repositoryPages = $repositoryPages;
        $this->repositorySettings = $repositorySettings;
        parent::__construct();
    }


    /**
     * Basic setup, returns table columns.
     *
     * @return array
     */

    function get_columns()
    {
        return array(
            'name' => __('Template name', 'wpmktengine'),
            'landing' => 'Used by Landing Pages',
            'id' => 'ID',
            'date' => __('Published', 'wpmktengine'),
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ return array('name' => array('name', false)); }


    /**
     * @param $item
     * @return string
     */
    function column_id($item)
    {
        return $item['id'];
    }


    public function column_landing($item)
    {
        if(!empty($item['landing'])){
            $id = "hidden-list-" . $item['id'];
            $r = "<ol id=\"$id\">";
            $counterHide = false;
            $counterMax = 2;
            $counter = 1;
            $counterRemaing = count($item['landing']) > $counterMax ? (count($item['landing']) - $counterMax) : 0;
            $counterJS = "onclick='Api.prolognedList(this, event, \"$id\");'";
            foreach($item['landing'] as $post){
                if($counter > $counterMax){
                    $counterHide = true;
                }
                $link = admin_url('post.php?post='. $post->ID .'&action=edit');
                $class = $counter > $counterMax ? "class='next hidden'" : "\"class='next'";
                $r .= "<li $class><a href=\"". $link ."\">". $post->post_title ."</a></li>";
                $counter++;
            }
            $r .= '</ol>';
            if($counterHide){
                $r .= "<a class='button' $counterJS href=\"#\"><span>Show</span> Remaining ($counterRemaing)</a>";
            }
            return $r;
        }
        return __('No Landing Pages are using this template.', 'wpmktengine');
    }


    /**
     * @param $item
     * @return string
     */
    public function column_name($item)
    {
        $actions = $this->row_actions(array(
            'edit' => $this->getLink('edit', $item['id']),
            'create' => $this->getLink('create', $item['id']),
            'prev' => $this->getLink('prev', $item['id']),
            'rename' => $this->getLink('rename', $item['id']),
            'trash' => $this->getLink('trash', $item['id'])
        ));
        return (isset($item['name']) && !empty($item['name']) ? $item['name'] : __('No title.', 'wpmktengine') ) . $actions;
    }


    /**
     * Get Link
     *
     * @param $which
     * @param null $id
     * @return string
     */
    function getLink($which, $id = NULL)
    {
        $r = new \stdClass();
        // Get url without params
        $realUrlEmpty = strtok(Utils::getRealUrl(), "?");
        $realUrlEmptyAdmin = rtrim(admin_url(), '/') . '/';
        $realUrl = $realUrlEmpty . "?page=WPMKTENGINEPages";
        $r->href = '';
        $r->other = '';
        $r->title = '';
        switch($which){
            case 'edit':
                $r->href =  Utils::addQueryParams(WPMKTENGINE_BUILDER_NEW, array(
                    'id' => $id,
                ));
                $r->title = 'Edit';
                $r->other = 'target="_blank"';
                break;
            case 'create':
                $r->href = Utils::addQueryParams($realUrlEmptyAdmin . 'post-new.php', array(
                    'post_type' => 'wpme-landing-pages',
                    'wpmktengine_landing_template' => $id,
                ));
                $r->title = 'Create a Landing Page';
                break;
            case 'prev':
                $r->href = Utils::addQueryParam(WPMKTENGINE_HOME_URL, 'genooIframeBuidler', $id);
                $r->title = 'Preview';
                $r->other = 'target="_blank"';
                break;
            case 'rename':
                $r->href = '#';
                $r->title = 'Rename';
                $name = __('How would you like rename this Page Template?.', 'wpmktengine');
                $url = Utils::addQueryParams($realUrl, array(
                    'genooPagesRename' => $id,
                    'genooPagesRenameTitle' => ''
                ));
                $r->other = 'onclick="Tool.promptGo(\''. $name .'\', \''. $url .'=\');"';
                break;
            case 'trash':
                $r->href = Utils::addQueryParams($realUrl, array(
                    'genooPagesDelete' => $id
                ));
                $r->other = 'onclick="Tool.promptBeforeGo(event, this, \'Are you sure you want to delete this Layout Page?\');"';
                $r->title = 'Delete';
                break;
        }
        return '<a href="'. $r->href .'" '. $r->other .'>'. $r->title .'</a>';
    }



    /**
     * Name column
     *
     * @param $item
     * @return string
     */

    function column_date($item)
    {
        return __('Published', 'wpmktengine') . '<br>' . date('Y/m/d', strtotime($item['created']));
    }


    /**
     * Remove cached forms
     *
     * @param $which
     */

    function extra_tablenav($which)
    {
        $where = strtok(Utils::getRealUrl(), "&");
        if($which == 'top'){
            echo '<div class="alignleft actions"><form style="display: inline; margin: 0" method="POST" action="'. $where .'">
                    <input type="submit" name="genooPagesFlushCache" id="submit" class="button alignCenter genooExtraNav" value="'. __('Sync Templates', 'wpmktengine') .'">
                    <a target="_blank" class="button button-primary genooExtraNav" href="'. WPMKTENGINE_BUILDER_NEW .'">'. __('Add new Template', 'wpmktengine') .'</a>
                </form></div>';
        }
    }


    /**
     * No Items notices
     */

    function no_items(){ echo __('There are no Pages created in your account.', 'wpmktengine'); }


    /**
     * Process it!
     */
    public function process()
    {
        // process actions
        if(isset($_POST['genooPagesFlushCache'])){
            try{
                $this->repositoryPages->flush();
                $this->addNotice('updated', __('All pages successfully synced.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        if(isset($_GET['genooPagesDelete'])){
            // Template id
            $template_id = $_GET['genooPagesDelete'];
            // Prepare
            $this->prepare_items();
            // Go through this
            $template_checks = $this->items;
            $template_checks_found = NULL;
            if(Utils::isIterable($template_checks)){
                foreach($template_checks as $item){
                    if($item['id'] == $template_id && !empty($item['landing'])){
                        $template_checks_found = $item['landing'];
                        break;
                    }
                }
            }
            if(!is_null($template_checks_found)){
                // We are not deleting, page has dependencies
                $landing = '<ol>';
                    foreach($template_checks_found as $template){
                        $landing .= '<li>'. $template->post_title .'</li>';
                    }
                $landing .= '</ol>';
                $landingAfter = '</p><p>If you wish to remove the template, please remove the Landing Pages first.';
                $this->addNotice('error', __('The template could not be removed. Because these Landing pages depend on it: ' . $landing . $landingAfter, 'wpmktengine'));
                return;
            }
            // Delete page
            try {
                $this->repositoryPages->deletePage($template_id);
                $this->repositoryPages->flush();
                $this->addNotice('updated', __('Template successfully removed.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        // Rename page
        if(isset($_GET) && array_key_exists('genooPagesRename', $_GET) && array_key_exists('genooPagesRenameTitle', $_GET)){
            // If all parameters present
            if(!empty($_GET['genooPagesRename']) && !empty($_GET['genooPagesRenameTitle'])){
                $id = $_GET['genooPagesRename'];
                $name = $_GET['genooPagesRenameTitle'];
                try {
                    $this->repositoryPages->renamePage($id, $name);
                    $this->repositoryPages->flush();
                    $this->addNotice('updated', __('Template successfully renamed.', 'wpmktengine'));
                } catch (\Exception $e){
                    $this->addNotice('error', $e->getMessage());
                }
            }
        }
    }

    /**
     *  Prepares, sorts, delets, all that stuff :)
     */

    public function prepare_items()
    {
        if($this->set == TRUE){ return; }
        try {
            $perPage = 100;
            $allLogs = $this->repositoryPages->getPagesTable();
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            if(!isset($_GET['orderby'])){
                $_GET['orderby'] = 'name';
            }
            usort($allLogs, array(&$this, 'usort_reorder'));
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            $this->items = $this->found_data;
            $this->set = TRUE;
        } catch (\WPMKTENGINE\ApiException $e){
            $this->addNotice('error', 'Genoo API: ' . $e->getMessage());
        } catch (\Exception $e){
            $this->addNotice('error', $e->getMessage());
        }
    }
}
