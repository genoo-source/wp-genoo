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

/**
 * Class Base
 *
 * @package WPME\Extensions
 */
abstract class Base
{
    /** @var */
    public $shortocode;
    /** @var array */
    public $shortocodeAttributes = array();
    /** @var */
    public $buttonIcon;
    /** @var */
    public $buttonImage;
    /** @var */
    public $buttonText;
    /** @var */
    public $buttonTitle;
    /** @var */
    public $modalTitle;
    /** @var  */
    public $priority = 100;

    /**
     * @param mixed $shortocode
     */
    public function setShortocode($shortocode)
    {
        $this->shortocode = $shortocode;
    }

    /**
     * @param array $shortocodeAttributes
     */
    public function setShortocodeAttributes($shortocodeAttributes)
    {
        $this->shortocodeAttributes = $shortocodeAttributes;
    }

    /**
     * @return array
     */
    public function getShortocodeAttributes()
    {
        if(!empty($this->shortocodeAttributes)){
            $r = array();
            foreach($this->shortocodeAttributes as $key => $attribute){
                if(is_string($key)){
                    if($attribute == true || $attribute == false){
                        $attribute = $attribute == true ? 'true' : 'false';
                    }
                    $r[$key] = $attribute;
                } else {
                    $r[$attribute] = '';
                }
            }
            return $r;
        }
        return $this->shortocodeAttributes;
    }

    /**
     * @param mixed $buttonIcon
     */
    public function setButtonIcon($buttonIcon)
    {
        $this->buttonIcon = $buttonIcon;
    }

    /**
     * @param mixed $buttonText
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;
    }

    /**
     * @param mixed $modalTitle
     */
    public function setModalTitle($modalTitle)
    {
        $this->modalTitle = $modalTitle;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @param mixed $buttonTitle
     */
    public function setButtonTitle($buttonTitle)
    {
        $this->buttonTitle = $buttonTitle;
    }

    /**
     * @param mixed $buttonImage
     */
    public function setButtonImage($buttonImage)
    {
        $this->buttonImage = $buttonImage;
    }

    /**
     * Init
     */
    public function init()
    {
        add_action('admin_init', array($this, 'plugin'), $this->priority + 5);
    }

    /**
     * Admin Init
     */
    public function plugin()
    {
        add_action('admin_print_footer_scripts', array($this, 'footerScripts'), $this->priority + 20);
        add_action('wp_ajax_ajax' . $this->shortocode, array($this, 'ajax'));
        if(current_user_can('edit_posts') || current_user_can('edit_pages')){
            add_filter("mce_external_plugins", function($plugin_array){
                $plugin_array[$this->shortocode] = admin_url('admin-ajax.php?action=ajax' . $this->shortocode);
                return $plugin_array;
            }, 10, 1);
            add_filter("mce_buttons", function($buttons){
                array_push($buttons, $this->shortocode . 'Button');
                return $buttons;
            }, 10, 1);
        }
    }

    /**
     * Register button
     */
    public function ajax()
    {
        header("Content-type: text/javascript");
        ?>
            (function(){
                tinymce.PluginManager.add('<?= $this->shortocode ?>', function(editor, url){
                    editor.addButton('<?= $this->shortocode ?>Button', {
                        text: '<?= $this->buttonText ?>',
                        title: '<?= $this->buttonTitle ?>',
                        <?= $this->buttonImage ? 'image: \''. $this->buttonImage .'\'' : 'image: false'; ?>,
                        <?= $this->buttonIcon ? 'icon: \'icon '. $this->buttonIcon .'\'' : 'icon: false'; ?>,
                        //classes: 'dashicons <?= $this->buttonIcon; ?>',
                        onclick: function(){
                            wp.mce.<?= $this->shortocode ?>.popupwindow(editor);
                        }
                    });
                });
            })();
        <?php
        die();
    }

    /**
     * Main Script
     * - window paramters: http://stackoverflow.com/questions/24871792/tinymce-api-v4-windowmanager-open-what-widgets-can-i-configure-for-the-body-op#answer-30098931
     * - window parametrs: https://makina-corpus.com/blog/metier/2016/how-to-create-a-custom-dialog-in-tinymce-4
     */
    public function footerScripts()
    {
        if (!isset(get_current_screen()->id) || get_current_screen()->base != 'post') return;
        ?>
        <script type="text/javascript">
            (function($){

                // Shortcode string
                var shortcode = '<?= $this->shortocode ?>';

                // Shortcode defaults
                var defaults = <?= \WPMKTENGINE\Utils\Json::encode($this->getShortocodeAttributes()); ?>;

                // Continue
                wp.mce = wp.mce || {};

                // Plugin view
                wp.mce.<?= $this->shortocode ?> = {

                    /**
                     * Sets a loader for all view nodes tied to this view instance.
                     */
                    setLoader: function(){
                        <?= $this->getJavascriptPlaceHolder(); ?>
                    },

                    /**
                     * Edit window
                     * @param shortcode
                     */
                    edit: function(shortcode){
                        // Give to editing window
                        wp.mce.<?= $this->shortocode ?>.popupwindow(tinyMCE.activeEditor, this.shortcode.attrs.named);
                    },

                    /**
                     * Pop-up window
                     *
                     * @param editor
                     * @param values
                     * @param onsubmit
                     */
                    popupwindow: function(editor, values, onsubmit){
                        <?= $this->getJavascriptPopUp(); ?>
                    }
                };

                // Register new view
                wp.mce.views.register(shortcode, wp.mce.<?= $this->shortocode ?>);

            }(jQuery));
        </script>
        <?php
    }

    /**
     * Javascript Loader
     * @return string
     */
    public function getJavascriptPlaceHolder(){}

    /**
     * Javascript PopUp
     * @return string
     */
    public function getJavascriptPopUp(){}

    /**
     * Setup a default value
     *
     * @return mixed
     * @throws \WPMKTENGINE\Utils\JsonException
     */
    public function getDefaultValuesJS()
    {
        ob_start();
        ?>
        <script type="text/javascript">
            // Shortcode defaults
            var <?= $this->shortocode ?>defaults = <?= \WPMKTENGINE\Utils\Json::encode($this->getShortocodeAttributes()); ?>;
            for (var key in <?= $this->shortocode ?>defaults){
                if (<?= $this->shortocode ?>defaults.hasOwnProperty(key)){
                    // If values does not hold this property,
                    // preset it the default value
                    if(!values.hasOwnProperty(key)){
                        values[key] = <?= $this->shortocode ?>defaults[key];
                    }
                }
            }
        </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $this->clean($output);
    }

    /**
     * Used to clean up JS tags that are
     * inserted into js functions so the IDE formats
     * code as JS ... silly right?
     *
     * @param $code
     * @return mixed
     */
    public function clean($code)
    {
        $code = str_replace('<script type="text/javascript">', '', $code);
        $code = str_replace('</script>', '', $code);
        return $code;
    }

    /**
     * @param $fileName
     * @param bool $isPath
     * @return bool|string
     */
    public function getSVGIconBase64Srouce($fileName, $isPath = false)
    {
        $data = '';
        if(defined('WPMKTENGINE_ROOT')){
            $root = WPMKTENGINE_ROOT;
        } else if(defined('GENOO_ROOT')){
            $root = GENOO_ROOT;
        } else {
            return false;
        }
        // We have root, get te file
        if($isPath){
            $path = $fileName;
        } else {
            $path = $root . 'assets' . DIRECTORY_SEPARATOR . 'tinymce' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR;
            $path .= $fileName;
        }
        // Load SVG file
        $data = file_get_contents($path);
        $data = base64_encode($data);
        // Return back to sender
        return "data:image/svg+xml;base64," . $data;
    }

    /**
     * @return string
     */
    public function getStyleIconContent()
    {
        return "style=\"content: url(\'{$this->buttonImage}\');\"";
    }

    /**
     * @param $fileName
     * @param bool $isPath
     * @return bool|string
     */
    public function getIconUrl($fileName, $isPath = false)
    {
        if(defined('WPMKTENGINE_FOLDER')){
            $root = WPMKTENGINE_FOLDER;
        } else if(defined('GENOO_FOLDER')){
            $root = GENOO_FOLDER;
        } else {
            return false;
        }
        // We have root, get te file
        if($isPath){
            $path = $fileName;
        } else {
            $path = $root . 'assets/tinymce/icons/';
            $path .= $fileName;
        }
        return $path;
    }
}