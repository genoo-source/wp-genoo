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

use WPMKTENGINE\CTA;

/**
 * Class WidgetCTADynamic
 * @package WPMKTENGINE
 */
class WidgetCTADynamic extends WidgetCTA
{
    /** @var CTA|int  */
    var $preCta;


    /**
     * Construct me up!
     *
     * @param bool $base
     * @param $number
     * @param CTA $cta
     */
    public function __construct($base, $number, CTA $cta)
    {
        parent::__constructDynamic(
            $base,
            apply_filters('genoo_wpme_widget_title_cta_dynamic', 'WPMKTGENGINE: CTA (dynamic)'),
            array(
                'description' =>
                    apply_filters(
                        'genoo_wpme_widget_description_cta_dynamic',
                        __('WPMKTGENGINE Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'wpmktengine')
                    )
            )
        );
        $this->id =  $base . '-' . $number;
        $this->number = $number;
        $this->preCta = $cta;
        $this->isWidgetCTA = TRUE;
        $this->set();
    }


    /**
     * Overwrite the set method of parent
     */

    public function set($id = NULL)
    {
        global $post;
        if(is_object($post) && ($post instanceof \WP_Post)){
            global $post;
            $this->isSingle = true;
            $this->cta = $this->preCta;
            $this->canHaveMobile = false;
            // Classlist has different rendering
            if($this->cta->isClasslist){
                $this->widgetForm = new WidgetLumen(false);
            } else {
                $this->widgetForm = new WidgetForm(false, true);
            }
            $this->widgetForm->id = $this->id;
        }
    }


    /**
     * Nope, we don't want to run this one
     *
     * @throws \Exception
     */

    public function setThroughShortcode($id, $post, $atts = array()){
        throw new \Exception('Dynamic CTA Widget cannot be initiated through Shortcode');
    }
}