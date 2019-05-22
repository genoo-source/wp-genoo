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

class GenooTinyMCECTA extends TinyMCEHanlder
{
    /** @var string */
    public $cta;
    /** @var array */
    public $ctas;


    /**
     * Constructor
     */

    public function __construct()
    {
        parent::__construct('genooCTA');
    }



    /**
     * Resolve additional variables
     */

    public function resolveSecond()
    {
        $this->cta = (isset($_GET['cta']) && $_GET['cta'] == 'true') ? true : false;
    }


    /**
     * WPMKTENGINE CTA pop-up javascript
     */

    public function renderJavascript()
    {
        ?>
        // Output
        var output = '';
        // get vals
        var cta = document.getElementById("cta");
        var ctaVal = cta.options[cta.selectedIndex].value;
        var align = document.getElementById("align");
        var alignVal = align.options[align.selectedIndex].value;
        var timeChecked = document.getElementById('hide_button').checked;
        var time = document.getElementById('hide_button_time').value;
        // output
        output += '[<?php echo $this->shortcode; ?>';
        if(ctaVal){ output += ' id=\''+ctaVal+'\''; }
        if(alignVal){ output += ' align=\''+alignVal+'\''; }
        if(timeChecked && (time > 0)){ output += ' time=\''+time+'\''; }
        output += ']';
        // bam
        <?php if($this->edit){ ?>
        //tinyMCEPopup.execCommand('WPMKTGENGINERefresh', false, output);
        //tinyMCEPopup.execCommand('mceReplaceContent', false, output);
        tinyMCEPopup.execCommand('<?php echo $this->refresh; ?>Ref', false, output);
    <?php } else { ?>
        tinyMCEPopup.execCommand('mceReplaceContent', false, output);
    <?php } ?>
        tinyMCEPopup.execCommand('<?php echo $this->refresh; ?>');
        tinyMCEPopup.close();
    <?php
    }


    /**
     * WPMKTENGINE CTA pop-up form
     */

    public function renderForm()
    {
        ?>
        <p>
            <label for="cta">CTA:</label><br/>
            <?php if(isset($this->ctas) && !empty($this->ctas)){ ?>
                <select name="cta" id="cta">
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
            <label for="align">Align:</label><br/>
            <select name="align" id="align">
                <option value="">None</option>
                <option value="left" <?php echo is_array($this->selected) && in_array('left', $this->selected) ? 'selected' : '' ?>>Left</option>
                <option value="right" <?php echo is_array($this->selected) && in_array('right', $this->selected) ? 'selected' : '' ?>>Right</option>
                <option value="center" <?php echo is_array($this->selected) && in_array('center', $this->selected) ? 'selected' : '' ?>>Center</option>
            </select>
        </p>
        <p>
            <label for="hide_button"><input type="checkbox" name="hide_button" id="hide_button" /> Allow CTA to appear after a time interval?</label> <br /> <br />
        </p>
        <p id="hide_button_time_box" style="display: none;">
            <label for="align">CTA appearance interval:</label><br/>
            <input type="number" name="hide_button_time" id="hide_button_time" class="text" />
        </p>
        <script type="text/javascript">
            jQuery(function() {
                var data = Popup.data();
                if(data){
                    var dataShort = '[' + data + ']';
                    var atts = window.parent.wp.shortcode.next('genooCTA', dataShort);
                    if(atts.shortcode.attrs.named.id){
                        jQuery('#cta').val(atts.shortcode.attrs.named.id).change();
                    }
                    if(atts.shortcode.attrs.named.align){
                        jQuery('#align').val(atts.shortcode.attrs.named.align).change();
                    }
                    if(atts.shortcode.attrs.named.time){
                        if(atts.shortcode.attrs.named.time > 0){
                            jQuery('#hide_button').prop('checked', true).change();
                            jQuery('#hide_button_time_box').toggle();
                            jQuery('#hide_button_time').val(atts.shortcode.attrs.named.time).change();
                        }
                    }
                }
                jQuery('#hide_button').change(function(){
                    jQuery('#hide_button_time_box').toggle();
                });
            });
        </script>
    <?php
    }
}

new GenooTinyMCECTA();