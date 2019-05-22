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


class MetaboxStyles extends Metabox
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
        $this->id = 'styler_' . Strings::webalize($title);
        $this->postType = $postType;
        $this->nonceKey =  $this->id . 'Nonce';
        $this->context = 'side';
        $this->priority = 'default';
        Action::add('add_meta_boxes',    array($this, 'register'));
        Action::add('save_post',         array($this, 'save'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
    }



    /**
     * Render
     *
     * @param $post
     */
    public function render($post)
    {
        ?>
        <div id="editor-wrapper">
            <!-- Put Editors here -->
            <div class="inside" id="modal-editor"></div>
            <div class="inside" id="drop-down-editor"></div>
            <div class="inside" id="req-editor"></div>
            <div class="inside" id="text-editor"></div>
            <div class="inside" id="checkbox-editor"></div>
            <div class="inside" id="radio-editor"></div>
            <div class="inside" id="image-editor"></div>
            <div class="inside" id="button-editor"></div>
            <div class="inside" id="text-input-editor"></div>
            <div class="inside" id="progress-bar-editor"></div>
            <div class="inside" id="countdown-counter-editor"></div>
        </div>
        <?php
    }
}
