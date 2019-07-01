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
 * Class Form
 *
 * @package WPME\Extensions\Shortcodes
 */
class Form extends Base
{
    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->setButtonIcon('bgTinyMCECTA.png?v=2');
        $this->setButtonTitle('Form');
        $this->setModalTitle('Form');
        $this->setButtonIcon('');
        $this->setButtonImage($this->getSVGIconBase64Srouce('form.svg'));
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
                '<div class="loading-placeholder gn-sc-plc placeholder-form" title="Form">' +
                '<div class="dashicons <?= $this->buttonIcon; ?>" <?= $this->getStyleIconContent() ?>></div>' +
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
                    theme: '',
                    confirmation: false,
                    msgSuccess: '',
                    msgFail: ''
                };
            } else {
                <?= $this->getDefaultValuesJS(); ?>
                values.confirmation = JSON.parse(values.confirmation);
                values.msgSuccess = values.msgsuccess;
                values.msgFail = values.msgfail;
            }
            // Check if disabled
            var msgDisabled = !values.confirmation;
            // Onsubmit?
            if(typeof onsubmit != 'function'){
                onsubmit = function(e){
                    // Insert content when the window form is submitted (this also replaces during edit, handy!)
                    var s = '[' + shortcode;
                    // Data protection
                    if(e.data.confirmation == false){
                        e.data.msgSuccess = '';
                        e.data.msgFail = '';
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
                var success = element.target._eventsRoot.find('#msgSuccess')[0];
                var elemenetSuccess = document.getElementById(success._id);
                var elemenetSuccessLabel = document.getElementById(success._id + '-l');
                // Fail
                var fail = element.target._eventsRoot.find('#msgFail')[0];
                var elemenetFail = document.getElementById(fail._id);
                var elemenetFailLabel = document.getElementById(fail._id + '-l');
                // We're checked
                if(element.control.state.data.checked == true){
                    // Input
                    elemenetSuccess.removeAttribute('disabled');
                    elemenetSuccess.removeAttribute('aria-disabled');
                    elemenetSuccess.removeAttribute('hidefocus');
                    elemenetSuccess.className =
                        elemenetSuccess.className.replace(/\bmce-disabled\b/,'');
                    // Label
                    elemenetSuccessLabel.removeAttribute('aria-disabled');
                    elemenetSuccessLabel.className =
                        elemenetSuccessLabel.className.replace(/\bmce-disabled\b/,'');
                    // Input
                    elemenetFail.removeAttribute('disabled');
                    elemenetFail.removeAttribute('aria-disabled');
                    elemenetFail.removeAttribute('hidefocus');
                    elemenetFail.className =
                        elemenetFail.className.replace(/\bmce-disabled\b/,'');
                    // Label
                    elemenetFailLabel.removeAttribute('aria-disabled');
                    elemenetFailLabel.className =
                        elemenetFailLabel.className.replace(/\bmce-disabled\b/,'');
                } else {
                    // Input
                    elemenetSuccess.setAttribute('disabled', 'disabled');
                    elemenetSuccess.setAttribute('aria-disabled', true);
                    elemenetSuccess.setAttribute('hidefocus', true);
                    elemenetSuccess.className += "mce-disabled";
                    elemenetSuccess.setAttribute('value', '');
                    // Label
                    elemenetSuccessLabel.setAttribute('aria-disabled', true);
                    elemenetSuccessLabel.className += "mce-disabled";
                    // Input
                    elemenetFail.setAttribute('disabled', 'disabled');
                    elemenetFail.setAttribute('aria-disabled', true);
                    elemenetFail.setAttribute('hidefocus', true);
                    elemenetFail.className += "mce-disabled";
                    elemenetFail.setAttribute('value', '');
                    // Label
                    elemenetFailLabel.setAttribute('aria-disabled', true);
                    elemenetFailLabel.className += "mce-disabled";
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
                            label  : 'Form',
                            autofocus : true,
                            values : GenooVars.EDITOR.<?= substr(__CLASS__, strrpos(__CLASS__, '\\')+1) ?>,
                            value : values.id,
                            minWidth: 200
                        },
                        {
                            type   : 'listbox',
                            name   : 'theme',
                            label  : 'Style',
                            values : GenooVars.EDITOR.Themes,
                            value : values.theme,
                            minWidth: 200
                        },
                        {
                            type   : 'checkbox',
                            name   : 'confirmation',
                            label  : '',
                            text   : 'Display confirmation message inline?',
                            checked : values.confirmation,
                            onchange: <?= $this->shortocode ?>OnChange,
                            minWidth: 200
                        },
                        {
                            type   : 'textbox',
                            name   : 'msgSuccess',
                            label  : 'Form success message',
                            id     : 'msgSuccess',
                            value  : values.msgSuccess,
                            disabled: msgDisabled,
                            minWidth: 200,
                            multiline: true,
                            minHeight: 100
                        },
                        {
                            type   : 'textbox',
                            name   : 'msgFail',
                            label  : 'Form error message',
                            id     : 'msgFail',
                            value  : values.msgFail,
                            disabled: msgDisabled,
                            minWidth: 200,
                            multiline: true,
                            minHeight: 100
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