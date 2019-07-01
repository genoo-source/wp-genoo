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
 * Class Survey
 *
 * @package WPME\Extensions\Shortcodes
 */
class Survey extends Base
{
    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->setButtonIcon('bgTinyMCECTA.png?v=2');
        $this->setButtonTitle('Survey');
        $this->setModalTitle('Survey');
        $this->setButtonIcon('dashicons-clipboard');
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
                '<div class="loading-placeholder gn-sc-plc placeholder-survey" title="Survey">' +
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
                    id: ''
                };
            } else {
                <?= $this->getDefaultValuesJS(); ?>
            }
            // Check if disabled
            var msgDisabled = !values.confirmation;
            // Onsubmit?
            if(typeof onsubmit != 'function'){
                onsubmit = function(e){
                    // Insert content when the window form is submitted (this also replaces during edit, handy!)
                    var s = '[' + shortcode;
                    // Data protection
                    for(var i in e.data){
                        s += ' ' + i + '=\"' + e.data[i] + '\"';
                    }
                    s += ']';
                    editor.insertContent(s);
                };
            }
            // Open editor window
            editor.windowManager.open(
                {
                    title: '<?= $this->modalTitle ?>',
                    body: [
                        {
                            type   : 'listbox',
                            name   : 'id',
                            label  : 'Survey',
                            autofocus : true,
                            values : GenooVars.EDITOR.<?= substr(__CLASS__, strrpos(__CLASS__, '\\')+1) ?>,
                            value : values.id,
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