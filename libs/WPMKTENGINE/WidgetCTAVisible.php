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
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\WidgetForm;
use WPMKTENGINE\Wordpress\Attachment;
use WPMKTENGINE\Wordpress\Post;


/**
 * Class WidgetCTA
 * @package WPMKTENGINE
 */

class WidgetCTAVisible extends \WPMKTENGINE\WidgetCTA
{
    /**
     * Constructor registers widget in WordPress
     */
    function __construct()
    {
        parent::__constructDynamic(
            'genooctaVisible',
            apply_filters('genoo_wpme_widget_title_cta', 'WPMKTGENGINE: CTA'),
            array(
                'description' =>
                    apply_filters(
                        'genoo_wpme_widget_description_cta',
                        __('WPMKTGENGINEff Call-To-Action widget is empty widget, that displays CTA when its set up on single post / page.', 'wpmktengine')
                    )
            )
        );
    }


    /**
     * Set
     */
    public function set($id = null)
    {
        $this->isSingle = true;
        $this->cta = new CTA(get_post($id));
        $this->cta->setCta($id);
        $this->widgetForm = new WidgetForm(false);
        $this->widgetForm->id = $this->id;
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        if(isset($instance['cta'])){
            $this->set($instance['cta']);
            $this->skipSet = true;
            // Add to globals
            if($this->cta->isForm){
                $GLOBALS['WPME_MODALS'][$this->id] = new \stdClass();
                $GLOBALS['WPME_MODALS'][$this->id]->widget = $this;
                $GLOBALS['WPME_MODALS'][$this->id]->instance = $this->getInnerInstance();
            }
            // we only care about single post
            echo $this->getHtmlInner($args, $instance);
        }
    }


    /**
     * Admin Form
     *
     * @param $instance
     */
    public function form($instance)
    {
        $ctas = new RepositoryCTA();
        $this->ctas = $ctas->getArray();
        $this->selected = array();
        if(isset($instance['cta'])){
            $this->selected[$instance['cta']] = $instance['cta'];
        }
        $visible = false;
        if(isset($instance['hide_button']) && $instance['hide_button'] == '1'){
            $visible = true;
        }
        $time = 0;
        if(isset($instance['hide_button_time'])){
            $time = $instance['hide_button_time'];
        }
        try {
            ?>
            <p>
                <label for="cta">CTA:</label><br/>
                <?php if(isset($this->ctas) && !empty($this->ctas)){ ?>
                    <select name="<?= $this->get_field_name('cta') ?>">
                        <option value="">Select CTA</option>
                        <?php
                        foreach($this->ctas as $key => $value){
                            $selectedVal = is_array($this->selected) && in_array($key, $this->selected) ? ' selected' : '';
                            echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                        }
                        ?>
                    </select>
                <?php } else { ?>
                    <strong>You don't have any CTA's in your WordPress installation.</strong>
                <?php } ?>
            </p>
            <p>
                <label>
                    <input
                        type="checkbox"
                        name="<?= $this->get_field_name('hide_button') ?>"
                        value="1"
                        onchange="Tool.switchClass(document.getElementById('hidden<?= $this->get_field_id('time_box') ?>'), 'genooHidden');"
                        <?= $visible ? 'checked="checked"' : ''; ?>
                    /> Allow CTA to appear after a time interval?</label>
            </p>
            <p id="hidden<?= $this->get_field_id('time_box') ?>" class="<?= $visible ? "" : "genooHidden" ?>s">
                <label for="align">CTA appearance interval:</label><br/>
                <input
                    type="number"
                    name="<?= $this->get_field_name('hide_button_time') ?>"
                    class="text"
                    value="<?= $time; ?>"
                />
            </p>
            <?php
        } catch (\Exception $e){
            echo '<span class="error">';
            echo $e->getMessage();
            echo '</span>';
        }
    }
}
