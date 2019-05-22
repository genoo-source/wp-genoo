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

class GenooTinyMCELumens extends TinyMCEHanlder
{
    /** @var array */
    public $lumens;


    /**
     * Constructor
     */

    public function __construct()
    {
        parent::__construct('genooLumens');
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
        var formVal = form.options[form.selectedIndex].value;
        // output
        output += '[<?php echo $this->shortcode; ?>';
        if(formVal){ output += ' id=\''+formVal+'\''; }
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
            <label for="form">Lumen Class:</label><br/>
            <select name="form" id="form">
                <?php
                if(isset($this->lumens) && !empty($this->lumens)){
                    foreach($this->lumens as $key => $value){
                        $selectedVal = '';
                        echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                    }
                }
                ?>
            </select>
        </p>
        <script type="text/javascript">
            jQuery(function() {
                var data = Popup.data();
                if(data){
                    var dataShort = '[' + data + ']';
                    var atts = window.parent.wp.shortcode.next('genooLumens', dataShort);
                    if(atts.shortcode.attrs.named.id){
                        jQuery('#form').val(atts.shortcode.attrs.named.id).change();
                    }
                }
            });
        </script>
    <?php
    }
}

new GenooTinyMCELumens();