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

class TableForms extends Table
{
    /** @var \WPMKTENGINE\RepositoryForms */
    var $repositoryForms;
    /** @var \WPMKTENGINE\RepositorySettings */
    var $repositorySettings;
    /** @var string */
    var $activeForm;

    /**
     * Constructor
     *
     * @param RepositoryLog $log
     */

    function __construct(\WPMKTENGINE\RepositoryForms $repositoryForms, \WPME\RepositorySettingsFactory $repositorySettings)
    {
        global $status, $page;
        $this->repositoryForms = $repositoryForms;
        $this->repositorySettings = $repositorySettings;
        $this->activeForm = $repositorySettings->getActiveForm();
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
            'name' => __('Form name', 'wpmktengine'),
            'shortcode' => __('Shortcode', 'wpmktengine'),
            'active' => __('Current active subscription form?', 'wpmktengine'),
            'form' => __('Preview', 'wpmktengine'),
        );
    }


    /**
     * Basic setup, returns sortable columns
     *
     * @return array
     */

    function get_sortable_columns(){ return array( 'name' => array('name',false) ); }


    /**
     * Active column
     *
     * @param $item
     * @return string
     */

    function column_active($item)
    {
        $activeId = isset($_GET['genooFormId']) ? $_GET['genooFormId'] : $this->activeForm;
        $active = $activeId == $item['id'] ? ' active' : '';
        $default = $activeId != $item['id'] ? '&nbsp;&nbsp;&nbsp;<a data-genooFormId="'. $item['id'] .'" href="'. Utils::addQueryParam($this->url, 'genooFormId', $item['id']) .'">Set as default</a>' : '';
        return
            '<a data-genooFormId="'. $item['id'] .'" href="'. Utils::addQueryParam($this->url, 'genooFormId', $item['id']) .'"><span class="genooTick '. $active .'">&nbsp;</span></a>'
            . $default;
    }


    /**
     * Shortcode
     *
     * @param $item
     * @return string
     */

    function column_shortcode($item)
    {
        $wpmeFormShortcode = apply_filters('genoo_wpme_form_shortcode', 'WPMKTENGINEForm');
        return '<code>['. $wpmeFormShortcode .' id="'. $item['id'] .'"]</code>';
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
                $link = '<a href="'. admin_url('admin.php?page=WPMKTENGINELogin&subpage=form') .'" class="button alignCenter">'. __('Create a Form', 'wpmktengine') .'</a>';
            }
            echo '<form style="display: inline; margin: 0" method="POST">
                    <input type="submit" name="genooFormsFlushCache" id="submit" class="button alignCenter" value="'. __('Sync Forms', 'wpmktengine') .'">
                    '. $link .'
                </form>';
        }
    }


    /**
     * Form preview thickbox
     *
     * @param $item
     * @return string
     */

    function column_form($item)
    {
        $prepForm = '';
        $prepForm .= '<div id="genooForm'. $item['id'] .'" style="display:none;"><h2>'. $item['name'] .'</h2>';
            $prepForm .= $this->repositoryForms->getForm($item['id']);
        $prepForm .= '</div>';
        $prepForm .= '<a href="#TB_inline?width=600&height=550&inlineId=genooForm'. $item['id'] .'" class="thickbox">'. __('Preview form', 'wpmktengine') .'</a>';
        return $prepForm;
    }


    /**
     * No Items notices
     */

    function no_items(){ echo __('There are no Lead Capture Forms in your account.', 'wpmktengine'); }


    /**
     *  Prepares, sorts, delets, all that stuff :)
     */

    public function prepare_items()
    {
        try {
            $perPage = parent::getPerPage();
            $allLogs = $this->repositoryForms->getFormsTable();
            $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
            usort($allLogs, array(&$this, 'usort_reorder'));
            $this->found_data = array_slice($allLogs,(($this->get_pagenum()-1)* $perPage), $perPage);
            $this->set_pagination_args(array('total_items' => count($allLogs), 'per_page' => $perPage));
            $this->items = $this->found_data;
            $this->checkActiveForm($this->items);
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
        if(isset($_POST['genooFormsFlushCache'])){
            try{
                $this->repositoryForms->flush();
                $this->addNotice('updated', __('All forms successfully synced.', 'wpmktengine'));
            } catch (\Exception $e){
                $this->addNotice('error', $e->getMessage());
            }
        }
        if(isset($_GET['genooFormId']) && is_numeric($_GET['genooFormId'])){
            $this->repositorySettings->setActiveForm($_GET['genooFormId']);
            $this->addNotice('updated', __('Form set as primary Subscribe Form.', 'wpmktengine'));
        }
    }

    /**
     * Check if Active default form has been changed / removed
     *
     * @param $forms
     */
    public function checkActiveForm($forms)
    {
        if(!empty($this->activeForm)){
            $found = FALSE;
            if(is_array($forms) && !empty($forms) && !empty($this->activeForm)){
                foreach($forms as $form){
                    if(isset($form['id'])){
                        if($form['id'] == $this->activeForm){
                            $found = TRUE;
                            break;
                        }
                    }
                }
            }
            // Only if none of the forms inside matches active form
            if($found == FALSE){
                $this->addNotice('error', 'Have you recently changed your WPMKTGENGINE forms? Your default form seems to be missing, don’t forget to select a new one!');
            }
        }
    }
}
