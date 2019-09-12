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


/**
 * Class TableForms
 *
 * @package WPME\Extensions
 */
class TableSurveys extends \WPME\TableFactory
{
    /** @var \WPME\Extensions\RepositorySurveys */
    var $repositorySurveys;

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */
    function __construct(\WPME\Extensions\RepositorySurveys $repositorySurveys)
    {
        global $status, $page;
        $this->repositorySurveys = $repositorySurveys;
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
            'id' => 'ID',
            'name' => __('Name', 'wpmktengine'),
            'shortcode' => __('Shortcode', 'wpmktengine'),
            'published' => __('Published?', 'wpmktengine'),
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ return array('name' => array('name', false), 'id' => array('id', false)); }


    /**
     * Active column
     *
     * @param $item
     * @return string
     */

    function column_published($item)
    {
        return ($item['published'])
            ? '<span class="genooTick active">&nbsp;</span>'
            : '<span class="genooCross">&times;</span>';
    }


    /**
     * Shortcode
     *
     * @param $item
     * @return string
     */

    function column_shortcode($item){
        $wpmeSurveyShortcode = apply_filters('genoo_wpme_survey_shortcode', 'WPMKTENGINESurvey');
        return '<code>['. $wpmeSurveyShortcode .' id="'. $item['id'] .'"]</code>';
    }


    /**
     * Remove cached forms
     *
     * @param $which
     */

    function extra_tablenav($which)
    {
        if($which == 'top'){
            $link = '';
            if(!class_exists('\Genoo\Api')){
                $link = '<a href="'. admin_url('admin.php?page=WPMKTENGINELogin&subpage=surveys') .'" class="button alignCenter">'. __('Create a Survey', 'wpmktengine') .'</a>';
            }
            echo '<form style="display: inline; margin: 0" method="POST">
                    <input type="submit" name="genooSurveysFlushCache" id="submit" class="button alignCenter" value="'. __('Sync Surveys', 'wpmktengine') .'">
                    '. $link .'
                </form>';
        }
    }



    /**
     * No Items notices
     */

    function no_items(){ echo __('There are no Surveys in your account.', 'wpmktengine'); }


    /**
     *  Prepares, sorts, delets, all that stuff :)
     */

    public function prepare_items()
    {
        try {
            $perPage = parent::getPerPage();
            $allLogs = $this->repositorySurveys->getSurveysTable();
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            usort($allLogs, array(&$this, 'usort_reorder'));
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            $this->items = $this->found_data;
        } catch (\WPMKTENGINE\ApiException $e){
            $this->addNotice('error', 'Genoo API: ' . $e->getMessage());
        } catch (\Exception $e){
            $this->addNotice('error', $e->getMessage());
        }

    }


    /**
     * Process it!
     */
    public function process()
    {
        // sortof beforeRender, add thickbox, just to be sure
        if(function_exists('add_thickbox')){ add_thickbox(); }
        // process actions
        if(isset($_POST['genooSurveysFlushCache'])){
            try{
                $this->repositorySurveys->flush();
                $this->addNotice('updated', __('All survyes successfully synced.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
    }
}
