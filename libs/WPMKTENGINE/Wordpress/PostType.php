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

namespace WPMKTENGINE\Wordpress;

use WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\Wordpress\Filter,
    WPMKTENGINE\Wordpress\Action;


class PostType
{

    /** @var string */
    public $postType;
    /** @var array */
    var $args;


    /**
     * Constructor
     *
     * @param $postType
     * @param array $args
     */


    function __construct($postType, array $args = array())
    {
        // webalize string, and truncate, max lenght according to specs 20 chars
        $this->postType = $this->purify($postType);
        $this->args = $this->mergeDefaults($postType, $args);
        $this->register();
    }


    /**
     * Purify post-type name
     *
     * @param $postType
     * @return string
     */

    public static function purify($postType){ return Strings::truncate(Strings::webalize($postType),20); }


    /**
     * Adds supports
     *
     * @param $feature
     */

    public function supports($feature){ $this->args['supports'][] = $feature; }


    /**
     * Sets name (really? :D)
     *
     * @param $name
     */

    public function setName($name){
        $this->args['label'] = $name;
        $this->args['labels']['name'] = $name;
    }


    /**
     * Set publicly visible
     *
     * @param $public
     */

    public function setPublic($public){ $this->args['public'] = $public; }


    /**
     * Can export?
     *
     * @param $export
     */

    public function setExport($export){ $this->args['can_export'] = $export; }


    /**
     * Set capabilities
     *
     * @param array $caps
     */

    public function setCapabilities(array $caps){ $this->args['capabilities'] = $caps; }


    /**
     * Has archive
     *
     * @param $archive
     */

    public function hasArchive($archive){ $this->args['has_archive'] = $archive; }


    /**
     * Merge with default
     *
     * @param array $args
     * @return array
     */

    private function mergeDefaults($postType, array $args = array()){
        $upperSingular = ucwords($postType);
        $upperPlural = ucwords($postType);
        $defaults = array(
            'label' =>  $postType,
            'labels' => array(
                'name' => $upperPlural,
                'singular_name' => $upperSingular,
                'add_new' => 'Add New',
                'add_new_item' => 'Add New '.$upperSingular,
                'edit_item' => 'Edit '.$upperSingular,
                'new_item' => 'New '.$upperSingular,
                'view_item' => 'View '.$upperSingular,
                'search_items' => 'Search '.$upperPlural,
                'not_found' =>  'No '.$upperPlural.' found',
                'not_found_in_trash' => 'No '.$upperPlural.' found in Trash',
                'parent_item_colon' => '',
                'menu_name' => $upperPlural),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'public' => true,
            'menu_position' => 70,
            'supports' => array('title'),
            'show_in_rest'       => true,
            'rest_base'          => $postType,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'public'             => true,
            'publicly_queryable' => true,
            'query_var'          => true,
        );
        return array_merge($defaults, $args);
    }


    /**
     * Regisgter's post type
     *
     * @return mixed
     */

    public function register(){ register_post_type($this->postType, $this->args); }


    /**
     * Unregister any post type
     *
     * @param $postType
     * @return bool
     */

    public static function unRegister($postType){
        global $wp_post_types;
        if (isset($wp_post_types[$postType])){
            unset($wp_post_types[$postType]);
            return true;
        }
        return false;
    }


    /**
     * Manage columns
     *
     * @param $postType
     * @param array $columnsCustom
     */

    public static function columns($postType, $columnsCustom = array(), $title = ''){
        $postType = self::purify($postType);
        Filter::add('manage_edit-'. $postType .'_columns', function($columns) use ($columnsCustom, $title){
            $columnsStart = array(
                'cb' => '<input type="checkbox" />',
                'title' => $title
            );
            $columnsEnd = array(
                'date' => __('Date', 'wpmktengine')
            );
            return array_merge($columnsStart, $columnsCustom,$columnsEnd);
        }, 10, 1);
    }


    /**
     * Simple helper with columsn content
     *
     * @param $postType
     * @param array $keys
     * @param $callback
     */

    public static function columnsContent($postType, $keys = array(), $callback = null){
        $postType = self::purify($postType);
        Action::add('manage_'. $postType .'_posts_custom_column', function($column, $post_id) use ($keys, $callback) {
            global $post;
            switch($column){
                default:
                    if(in_array($column, $keys)){
                        if(!empty($callback) && is_callable($callback)){
                            call_user_func_array($callback, array($column, $post));
                        } else {
                            echo Strings::firstUpper(get_post_meta($post->ID, $column, true));
                        }
                    }
                    break;
            }
        }, 10, 2);
    }
}