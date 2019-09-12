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

namespace WPME\Extensions\Shortcodes;

use WPME\Extensions\Shortcodes\Base;

/**
 * Class CTA
 *
 * @package WPME\Extensions\Shortcodes
 */
class CTA extends Base
{
    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->setButtonIcon('bgTinyMCECTA.png?v=2');
        $this->setButtonTitle('CTA');
        $this->setModalTitle('CTA');
        $this->setButtonIcon('dashicons-migrate');
    }

    /**
     * Get Placehodler
     *
     * @return string
     */
    public function getJavascriptPlaceHolder()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            this.setContent(
                '<div class="loading-placeholder gn-sc-plc placeholder-cta" title="CTA">' +
                '<div class="dashicons <?= $this->buttonIcon; ?>"></div>' +
                '</div>'
            );
        </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $this->clean($output);
    }

    /**
     * Get Javascript PopUp
     *
     * @return string
     */
    public function getJavascriptPopUp()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            // Values preset
            if(typeof values === 'undefined'){
                values = {
                    id: '',
                    align: '',
                    hastime: false,
                    time: ''
                };
            } else {
                <?= $this->getDefaultValuesJS(); ?>
                values.hastime = JSON.parse(values.hastime);
                if (typeof values.time !== 'undefined') {
                    values.time = '';
                }
            }
            // Check if disabled
            var msgDisabled = !values.hastime;
            // Onsubmit?
            if(typeof onsubmit != 'function'){
                onsubmit = function(e){
                    // Insert content when the window form is submitted (this also replaces during edit, handy!)
                    var s = '[' + shortcode;
                    // Data protection
                    if(e.data.hastime == false){
                        delete e.data.time;
                    }
                    for(var i in e.data){
                        s += ' ' + i + '=\"' + e.data[i] + '\"';
                    }
                    s += ']';
                    editor.insertContent(s);
                };
            }
            // On change
            function <?= $this->shortocode ?>OnChange(element){
                // Get elements
                // Success
                var modalTime =element.target._eventsRoot.find('.modaltime')[0];
                var elemenetmodalTime = document.getElementById(modalTime._id);
                var elemenetmodalTimeLabel = document.getElementById(modalTime._id + '-l');
                // We're checked
                if(element.control.state.data.checked == true){
                    // Input
                    elemenetmodalTime.removeAttribute('disabled');
                    elemenetmodalTime.removeAttribute('aria-disabled');
                    elemenetmodalTime.removeAttribute('hidefocus');
                    elemenetmodalTime.className =
                        elemenetmodalTime.className.replace(/\bmce-disabled\b/,'');
                    // Label
                    elemenetmodalTimeLabel.removeAttribute('aria-disabled');
                    elemenetmodalTimeLabel.className =
                        elemenetmodalTimeLabel.className.replace(/\bmce-disabled\b/,'');
                } else {
                    // Input
                    elemenetmodalTime.setAttribute('disabled', 'disabled');
                    elemenetmodalTime.setAttribute('aria-disabled', true);
                    elemenetmodalTime.setAttribute('hidefocus', true);
                    elemenetmodalTime.className += "mce-disabled";
                    elemenetmodalTime.setAttribute('value', '');
                    // Label
                    elemenetmodalTimeLabel.setAttribute('aria-disabled', true);
                    elemenetmodalTimeLabel.className += "mce-disabled";
                }
            }
            // Open editor window
            editor.windowManager.open(
                {
                    title: '<?= $this->modalTitle ?>',
                    body: [
                        {
                            type   : 'listbox',
                            name   : 'id',
                            label  : 'CTA',
                            autofocus : true,
                            values : GenooVars.EDITOR.<?= substr(__CLASS__, strrpos(__CLASS__, '\\') + 1) ?>,
                            value : values.id,
                            minWidth: 200
                        },
                        {
                            type   : 'listbox',
                            name   : 'align',
                            label  : 'Align',
                            values : [
                                { text: 'None', value: '' },
                                { text: 'Left', value: 'left' },
                                { text: 'Right', value: 'right' },
                                { text: 'Center', value: 'center' }
                            ],
                            value : values.align,
                            minWidth: 200
                        },
                        {
                            type   : 'checkbox',
                            name   : 'hastime',
                            label  : '',
                            text   : 'Allow CTA to appear after a time interval?',
                            checked : values.hastime,
                            onchange: <?= $this->shortocode ?>OnChange,
                            minWidth: 200
                        },
                        {
                            type   : 'textbox',
                            name   : 'time',
                            label  : 'CTA appearance interval',
                            id     : 'modalTime',
                            classes: 'modaltime',
                            value  : values.time,
                            disabled: msgDisabled,
                            minWidth: 200
                        }

                    ],
                    onsubmit: onsubmit
                }
            );
        </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $this->clean($output);
    }
}