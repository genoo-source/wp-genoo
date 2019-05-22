<?php
/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * Genoo TinyMCE plugin - CTA
 *
 * @version 1
 * @author latorante.name
 */

// Include parent class
if(file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TinyMCEHanlder.php')){
    require_once 'TinyMCEHanlder.php';
}


/**
 * Class GenooTinyMCECTA
 */

class GenooTinyMCEForm extends TinyMCEHanlder
{
    /** @var array */
    public $themes;
    /** @var array */
    public $forms;


    /**
     * Constructor
     */

    public function __construct()
    {
        parent::__construct('genooForm');
    }


    /**
     * Genoo CTA pop-up javascript
     */

    public function renderJavascript()
    {
        ?>
        // Output
        var output = '';
        // get vals
        var form = document.getElementById("form");
        var formTheme = document.getElementById("formTheme");
        var formVal = form.options[form.selectedIndex].value;
        var formThemeVal = formTheme.options[formTheme.selectedIndex].value;
        <?php if($this->version >= 4){ ?>
        var themeConfirm = document.getElementById("themeConfirm").value;
        var themeError = document.getElementById("themeError").value;
    <?php } ?>
        // output
        output += '[<?php echo $this->shortcode; ?>';
        if(formVal){ output += ' id=\''+formVal+'\''; }
        if(formThemeVal){ output += ' theme=\''+formThemeVal+'\''; }
        <?php if($this->version >= 4){ ?>
        if(document.getElementById("formInternal").checked){
        if(themeConfirm){ output += ' msgSuccess=\''+addSlashes(themeConfirm)+'\''; }
        if(themeError){ output += ' msgFail=\''+addSlashes(themeError)+'\''; }
        }
    <?php } ?>
        output += ']';        // bam
        <?php if($this->edit){ ?>
        tinyMCEPopup.execCommand('<?php echo $this->refresh; ?>Ref', false, output);
    <?php } else { ?>
        tinyMCEPopup.execCommand('mceReplaceContent', false, output);
    <?php } ?>
        tinyMCEPopup.execCommand('<?php echo $this->refresh; ?>');
        tinyMCEPopup.close();
    <?php
    }


    /**
     * Genoo CTA pop-up form
     */

    public function renderForm()
    {
        ?>
        <p>
            <label for="form">Form:</label><br/>
            <select name="form" id="form">
                <option value="">Default</option>
                <?php
                if(isset($this->forms) && !empty($this->forms)){
                    foreach($this->forms as $key => $value){
                        $selectedVal = is_array($this->selected) && in_array($key, $this->selected) ? ' selected' : '';
                        echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                    }
                }
                ?>
            </select>
        </p>
        <p>
            <label for="formTheme">Style:</label><br/>
            <select name="formTheme" id="formTheme">
                <option value="">Default</option>
                <?php
                if(isset($this->themes) && !empty($this->themes)){
                    foreach($this->themes as $key => $value){
                        $selectedVal = is_array($this->selected) && in_array($key, $this->selected) ? ' selected' : '';
                        echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                    }
                }
                ?>
            </select>
        </p>
        <?php if($this->version >= 4){ ?>
        <p>
            <label for="formInternal">Display confirmation message inline? <input onchange="checkChecked(this);" type="checkbox" id="formInternal" name="formInternal" <?php if($this->visible){ echo 'checked'; } ?>/></label>
            <br />
            <br />
        </p>
        <div id="formHidden" class="<?php if(!$this->visible){ echo 'hidden'; } ?>">
            <p>
                <label for="formConfirm">Form success message:</label><br/>
                <input class="text" type="text" id="themeConfirm" name="msgSuccess" value="<?php if(isset($this->selected['msgsuccess'])){ echo $this->selected['msgsuccess']; } ?>" />
            </p>
            <p>
                <label for="formError">Form error message:</label><br/>
                <input class="text" type="text" id="themeError" name="msgFail" value="<?php if(isset($this->selected['msgfail'])){ echo $this->selected['msgfail']; } ?>" />
            </p>
        </div>
    <?php } ?>
        <?php if($this->edit){ ?>
        <script type="text/javascript">
            jQuery(function() {
                var data = Popup.data();
                if(data){
                    var dataShort = '[' + data + ']';
                    var atts = window.parent.wp.shortcode.next('genooForm', dataShort);
                    if(atts.shortcode.attrs.named.id){
                        jQuery('#form').val(atts.shortcode.attrs.named.id).change();
                    }
                    if(atts.shortcode.attrs.named.theme){
                        jQuery('#formTheme').val(atts.shortcode.attrs.named.theme).change();
                    }
                    if(atts.shortcode.attrs.named.msgfail){
                        jQuery('#themeError').val(atts.shortcode.attrs.named.msgfail).change();
                    }
                    if(atts.shortcode.attrs.named.msgsuccess){
                        jQuery('#themeConfirm').val(atts.shortcode.attrs.named.msgsuccess).change();
                    }
                    if(atts.shortcode.attrs.named.msgfail && atts.shortcode.attrs.named.msgsuccess){
                        jQuery('#formInternal').prop('checked', true).change();
                    }
                }
            });
        </script>
    <?php } ?>
    <?php
    }
}

new GenooTinyMCEForm();