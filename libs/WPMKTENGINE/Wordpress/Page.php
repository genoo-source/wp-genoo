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

use WPMKTENGINE\Utils\Strings;

class Page
{
    /** @var string */
    var $renderBefore = '<div class="wrap">';
    /** @var string */
    var $renderAfter = '</div>';
    /** @var string */
    var $renderBeforeWidgets = '<div class="metabox-holder">';
    /** @var string */
    var $renderAfterWidgets = '</div>';
    /** @var string */
    var $title;
    /** @var string */
    var $guts;
    /** @var array */
    var $widgets = array();


    /**
     * Add page title
     *
     * @param $title
     * @return Page
     */

    public function addTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Add widget
     *
     * @param $title
     * @param $guts
     * @return Page
     */

    public function addWidget($title, $guts)
    {
        $this->widgets[] = (object)array('title' => $title, 'guts' => $guts);
        return $this;
    }


    /**
     * Add guts
     *
     * @param $guts
     */

    public function addContent($guts){ $this->guts = $guts; }


    /**
     * Render
     */

    public function __toString()
    {
        $output = '';
        $output .= $this->renderBefore;
            if(!class_exists('\Genoo\Api')){
                $output .= Helpscreen::getSupportHaderWithLogo($this->title);
            }
            $output .= $this->guts ? $this->guts : '';
            if($this->widgets){
                $counter = 1;
                $output .= $this->renderBeforeWidgets;
                foreach($this->widgets as $widget){
                    $id = str_replace('-', '', Strings::webalize($widget->title, null, true));
                    $output .= '<div class="postbox genooPostbox postbox'. $id .'"><div class="group">';
                    $output .= '<h3>'. $widget->title .'</h3>';
                    $output .= '<table class="form-table"><tbody>';
                    if(is_array($widget->guts)){
                        foreach($widget->guts as $key => $value){
                            $output .= '<tr valign="top"><th scope="row">'. $key .'</th><td>'. $value .'</td></tr>';
                        }
                    } else {
                        $output .= '<tr valign="top"><td>';
                            $output .= $widget->guts;
                        $output .= '</td></tr>';
                    }
                    $output .= '</tbody></table></div></div>';
                    if($counter % 2 == 0){ $output .= '<div class="clear"></div>'; }
                    ++$counter;
                }
                $output .= $this->renderAfterWidgets;
            }
        $output .= $this->renderAfter;
        // return string
        return $output;
    }
}