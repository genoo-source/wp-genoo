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

use WPME\RepositorySettingsFactory;


/**
 * Class WidgetLumen
 * @package WPMKTENGINE
 */

class WidgetLumen extends \WP_Widget
{

    /**
     * Constructor registers widget in WordPress
     */

    function __construct($constructParent = true)
    {
        if($constructParent){
            parent::__construct(
                'genoolumen',
                apply_filters('genoo_wpme_widget_title_lumens', 'WPMKTGENGINE: Class List'),
                array(
                    'description' =>
                        apply_filters(
                            'genoo_wpme_widget_description_lumens',
                            __('WPMKTGENGINE widget class list.', 'wpmktengine')
                        )
                )
            );
        }
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     * @param array $echo
     */

    public function widget($args, $instance, $echo = true)
    {
        try {
            $repositorySettings = new RepositorySettingsFactory();
            $api =  new \WPME\ApiFactory($repositorySettings);
            $repositoryLumens = new RepositoryLumens(new Cache(WPMKTENGINE_CACHE), $api);
            $formId = !empty($instance['lumen']) && is_numeric($instance['lumen']) ? $instance['lumen'] : null;
            $formTitle = !empty($instance['title']) ? $instance['title'] : __('Classlist', 'wpmktengine');
            $r = '';
            if(!is_null($formId)){
                $r .= $args['before_widget'];
                $r .= '<div class="themeResetDefault">';
                $r .= '<div class="genooTitle">' . $args['before_title'] . $formTitle . $args['after_title'] . '</div>';
                if(isset($instance['displayDesc']) && $instance['displayDesc'] == true){
                    $r .= '<div class="genooGuts"><p class="genooPadding">' . $instance['desc'] . '</p></div>';
                }
                $r .= '<div class="clear"></div>';
                $r .= '<div class="genooGuts">';
                $r .= $repositoryLumens->getLumen($formId);
                $r .= '</div>';
                $r .= '<div class="clear"></div>';
                $r .= '</div>';
                $r .= $args['after_widget'];
            }
        } catch (\Exception $e){
            $r .= '<span class="error">';
            $r .= $e->getMessage();
            $r .= '</span>';
        }
        if($echo){
            echo $r;
            return true;
        }
        return $r;
    }


    /**
     * Get HTML
     *
     * @param $args
     * @param $instance
     * @return string
     */

    public function getHtml($args, $instance)
    {
        return $this->widget($args, $instance, false);
    }


    /**
     * Widget settings form
     *
     * @param $instance
     */

    public function form($instance)
    {
        try {
            // prep stuff
            $repoSettings = new RepositorySettingsFactory();
            $repoLumens = new RepositoryLumens(new Cache(WPMKTENGINE_CACHE), new \WPME\ApiFactory($repoSettings));
            $widgetLumens = $repoLumens->getLumensTable();
            $instance = wp_parse_args((array) $instance, array('title' => __('Classlist', 'wpmktengine'), 'lumen' => 0));
            $widgetTitle = !empty($instance['title']) ? strip_tags($instance['title']) : __('Classlist', 'wpmktengine');
            $widgetLumen = strip_tags($instance['lumen']);
            // widget form
            echo '<div class="genooParagraph">';
            echo '<label for="'. $this->get_field_id('title') .'">' . __('Genoo form title:', 'wpmktengine') . ' </label>';
            echo '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" value="'. esc_attr($widgetTitle) .'" type="text" />';
            echo '</div>';
            echo '<div class="genooParagraph">';
            echo '<label for="'. $this->get_field_id('lumen') .'">' . __('Classlist:', 'wpmktengine') . ' </label>';
            echo '<select name="'. $this->get_field_name('lumen') .'" id="'. $this->get_field_id('lumen') .'">';
            foreach($widgetLumens as $value){
                echo '<option value="'. $value['id'] .'" '. selected($value['id'], $widgetLumen, false) .'>' . $value['name'] . '</option>';
            }
            echo '</select>';
            echo '</div>';
        } catch (\Exception $e){
            echo '<span class="error">';
            echo $e->getMessage();
            echo '</span>';
        }
    }
}