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
use WPMKTENGINE\Wordpress\Filter;
use WPMKTENGINE\Wordpress\Action;

/**
 * Class MetaboxArea
 *
 * @package WPMKTENGINE\Wordpress
 */
class MetaboxArea extends Metabox
{
    /** @var */
    var $title;
    /** @var string  */
    var $id;
    /** @var */
    var $postType;
    /** @var string  */
    var $nonceKey;

    /**
     * Constructor
     *
     * @param $title
     * @param $postType
     */
    function __construct($title, $postType)
    {
        // assign
        $this->title = $title;
        $this->id = 'area_' . Strings::webalize($title);
        $this->postType = $postType;
        $this->nonceKey =  $this->id . 'Nonce';
        Action::add('add_meta_boxes',    array($this, 'register'));
        Action::add('save_post',         array($this, 'save'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 90, 1);
    }

    /**
     * Render
     *
     * @param $post
     */
    public function render($post)
    {
        ?>
        <div class="hidden" style="display: none !important;"><textarea autocomplete="off" name="content" id="content-textarea"><?php echo $post->post_content; ?></textarea></div>
        <div class="relative-genoo">
            <div aria-hidden="false" id="genooOverlay" class="visible">
                <div id="modalWindowGenoodynamiccta1" tabindex="-5" role="dialog" class="genooModal visible renderedVisible genooModalPopBig">
                    <div class="relative">
                        <div class="genooForm themeResetDefault themeDefault">
                            <div class="clear"></div>
                            <div class="genooGuts">
                                <div id="genooMsg"></div>
                                <div class="clear"></div>
                                <div class="genooPop">
                                    <div class="genooPopProgress">
                                        <div class="progress ">
                                            <span class="progress-bar"></span><span class="progress-per">43%</span>
                                        </div>
                                    </div>
                                    <div class="genooCountdown" id="countdown-56fcf2f06db7b">
                                        <div class="genooCountdownText"><p>Countdown Label</p></div>
                                        <div class="clear"></div>
                                        <div class="timing days"><span>3</span> Days</div>
                                        <div class="timing hours"><span>14</span> Hours</div>
                                        <div class="timing minutes"><span>15</span> Min</div>
                                        <div class="timing seconds"><span>9</span> Sec</div>
                                    </div>
                                    <div class="genooPopIntro">
                                        <p>Title Text</p>
                                    </div>
                                    <div class="genooPopIntro genooPopTitle">
                                        <p>Regular Text</p>
                                    </div>
                                    <div class="genooPopRight">
                                        <span id="reqform_570e9e7e916f9" class="req">* = required</span>
                                        <div id="errfirst_name11382510"></div>
                                        <p>
                                            <label>Label Text<span class="required">*</span></label>
                                            <input name="novalidate[no][]" id="first_name11382510" type="text" class="ext-form-input">
                                        </p>
                                        <div id="errlast_name11382510"></div>
                                        <p>
                                            <label>Label Text<span class="required">*</span></label>
                                            <select name="novalidate[no][]">
                                                <option value="">Value</option>
                                            </select>
                                        </p>
                                        <p>
                                            <label>Label Text</label>
                                            <textarea name="novalidate[no][]" id="comments_txt11382510" class="ext-form-textarea"></textarea>
                                        </p>
                                        <p>
                                            <input type="submit" name="novalidate[no][]" value="Send Message" class="form-button-submit" data-hover-style>
                                        </p>
                                    </div>
                                    <div class="genooPopLeft" style="width:auto">
                                        <div class="genooPopImage">
                                            <img 
                                              src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAEsBAMAAAB01OGNAAAAG1BMVEXMzMyWlpacnJyqqqrFxcWxsbGjo6O3t7e+vr6He3KoAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACrUlEQVR4nO3bwW+iQBgF8DcIwhHQ2j0CdXc92u422SNW0zN66JluTOwR3MS9ajfZv3tnBmydVI1R091N3i/xm8JQ3gwD3ACIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiI/ib75lMKzMOiLi9+h0/Y2nEEK4zbcIL4siov3OB7sNzWcYxx32lh0PXCqryGd1Hm2zqO8ZgiRFngeqkLUPZRZsAgg+UbHSdIgFglrTJd5BQ+qH0YLiEio+MEPyFnEsuR93UBvI5TrYB9nRkdJ3FbauiDXBe5/VhGOiNowew4xbgt5wIR6SK3V0FRhci7y+g4QSNcmgNudOqe5/xcM7HjKcxLb63vV88/15o05ZVXN1GZ6SI3yljVeQr4ZsfxSjVG43FI7tqyrgp47XM9J3KYMB5s58Jp6V24i871xMdJkhivKHnqWAY78t1VnOvdFUjGy3acVZdwfsa3MBERERERERERERERERERERERERERERERvRD1738JCcKvh4X4ckuoDxVCqI+dfL1zGM+A+2AKEelPV3bw7S+Hh+jaWWJRh9gf01+yjCapeIL7uDsEd7i5gpW68g8n6UNMfNU0krEKmVyhWciejZDLHLM6pJFVxemLBZrlvhBnep81igF+4GF0CzHVzcNIDUzILi/C7WaI3/OiOsRKq2JHwlo+715Aebms1I68/qT4hq4cs0h108VAhcguzORv/eGIClk0izpErIsv3Hy2O0QuvB5g1MsjdSL1P6rx12vi47lRGDOx5ng7E3yO9sxEHaWGmz/lcvj6TKrZmIk1gRHiXODtmmCc7Q1Ra4JJMc+wSIfqTKp5XRO4l2aI3tB14+7CvodKHSzvLqzSQQo37qlDVfN6d8GNtoRUl3QYrJ+TvSEHaL7HZ0Dzd8gQvXcIIfp3/AGkRYFwrNspcwAAAABJRU5ErkJggg==" 
                                              class="attachment-medium size-medium" 
                                              alt="radio-podcast" 
                                              style="display: inline;"
                                            >
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="genooPopFooter">
                                        <p>Footer text</p>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
                <div class="background-selected"></div>
            </div>
            <div class="clear cf"></div>
        </div>
        <div class="clear cf"></div>
        <?php
    }

    /**
     * @param $hook
     */
    public function adminEnqueueScripts($hook)
    {
        // Post
        global $post;
        // Append?
        if(
            ($hook == 'post-new.php' && (isset($_GET) && is_array($_GET) && array_key_exists('post_type', $_GET)) && $_GET['post_type'] == 'wpme-styles')
        ||
            ($hook == 'post.php' && isset($post) && ($post->post_type == 'wpme-styles'))
        ){
            // Frontend style
            wp_enqueue_style('genooFrontend', WPMKTENGINE_ASSETS . 'GenooFrontend.css', NULL, WPMKTENGINE_REFRESH);
            // Styler style to differntiate from Frontend
            wp_enqueue_style('wpmktgengine-styler', WPMKTENGINE_FOLDER . '/extensions/wpmktgengine-styler.css', NULL, WPMKTENGINE_REFRESH);
            // Javascript
            wp_enqueue_script('wpmktgengine-styler', WPMKTENGINE_FOLDER . '/extensions/wpmktgengine-styler.js', array('Genoo'), WPMKTENGINE_REFRESH);
            wp_enqueue_style('wp-color-picker');
        }
    }

    /**
     * Save
     *
     * @param $post_id
     */
    public function save($post_id){}
}
