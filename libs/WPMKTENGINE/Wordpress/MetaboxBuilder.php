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


class MetaboxBuilder extends Metabox
{

    /** @var */
    var $title;
    /** @var string  */
    var $id;
    /** @var */
    var $postType;
    /** @var string  */
    var $nonceKey;
    /** @var  */
    var $forms;
    /** @var  */
    var $previous;
    /** @var  */
    VAR $form;

    /**
     * Constructor
     *
     * @param $title
     * @param $postType
     * @param $forms
     */
    function __construct($title, $postType, $forms)
    {
        // assign
        $this->title = $title;
        $this->id = 'builder_' . Strings::webalize($title);
        $this->postType = $postType;
        $this->nonceKey =  $this->id . 'Nonce';
        $this->forms = $forms;
        Action::add('add_meta_boxes',    array($this, 'register'));
        Action::add('save_post',         array($this, 'save'));
        Filter::add('admin_head',        array($this, 'adminJs'));
        Action::add('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
    }


    /**
     * Admin js
     */
    public function adminJs()
    {
        global $parent_file;
        global $post_type;
        // If the current post type doesn't match, return, ie. end execution here
        if ((is_array($this->postType) && in_array($post_type, $this->postType)) || (is_string($this->postType) && $this->postType == $post_type)){
            if (Strings::contains($parent_file, 'edit.php') || Strings::contains($parent_file, 'post-new.php')){
                ?>
                <script type="text/javascript">
                </script>
            <?php
            }
        }
    }


    /**
     * Render
     *
     * @param $post
     */
    public function render($post)
    {
        $this->previous = get_post_meta($post->ID, 'formpop', TRUE);
        $this->form = get_post_meta($post->ID, 'form', TRUE);
        $countdownPosition =  $this->formValue('countdown-position') == 'bottom' ? 'bottom' : 'top';
        ?>
        <div class="genooMetabox" id="genooMetaboxBuilder">
            <div class="builderHeader">
                <div class="builderLeft"><div class="inner">Editable Area</div></div>
                <div class="builderRight">
                    <div class="inner">
                        Preview
                        <a id="preview-button" data-href="<?php echo WPMKTENGINE_HOME_URL; ?>?genooIframeCTA=<?php echo $post->ID; ?>&?TB_iframe=true&width=640" class="button button-primary">Preview</a>
                        <a id="preview-button-hidden" href="<?php echo WPMKTENGINE_HOME_URL; ?>?genooIframeCTA=<?php echo $post->ID; ?>&?TB_iframe=true&width=640" class="thickbox" style="display: none !important;"></a>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="builderLeft">
                <div class="inner">
                    <div id="block-percentage" class="bBlock">
                        <div class="bRow">
                            <label for="visible-1">Visible <input name="formpop[percentage-on]" id="visible-1" type="checkbox" class="bCheckbox" <?php $this->formChecked('percentage-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('percentage-on') ? '' : 'bHidden'; ?>">
                            <label for="percentage">Percentage value</label>
                            <input name="formpop[percentage]" value="<?php echo $this->formValue('percentage'); ?>" class="bTargeted" data-target="block-percentage" type="range" id="percentage" min="1" max="100" step="1">
                        </div>
                    </div>
                    <div id="block-title" class="bBlock">
                        <div class="bRow">
                            <label for="visible-2">Visible <input name="formpop[title-on]" id="visible-2" type="checkbox" class="bCheckbox" <?php $this->formChecked('title-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('title-on') ? '' : 'bHidden'; ?>">
                            <?php self::getEditor($this->formValue('title'), 'textarea-block-title', 'formpop[title]'); ?>
                        </div>
                    </div>
                    <div id="block-intro" class="bBlock">
                        <div class="bRow">
                            <label for="visible-1">Visible <input name="formpop[intro-on]" id="visible-1" type="checkbox" class="bCheckbox" <?php $this->formChecked('intro-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('intro-on') ? '' : 'bHidden'; ?>">
                            <label for="intro-inside-on">Move intro inside the form <input name="formpop[intro-inside-on]" id="intro-inside-on" type="checkbox" <?php $this->formChecked('intro-inside-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('intro-on') ? '' : 'bHidden'; ?>">
                            <?php self::getEditor($this->formValue('intro'), 'textarea-block-intro', 'formpop[intro]'); ?>
                        </div>
                    </div>
                    <div id="block-image" class="bBlock">
                        <div class="bRow">
                            <label for="visible-3">Visible <input name="formpop[image-on]" id="visible-3" type="checkbox" class="bCheckbox" <?php $this->formChecked('image-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('image-on') ? '' : 'bHidden'; ?> bNoBorder">
                            <?php echo self::imageSelect('formpop[image]', 'image-select', 'Select Image', $post, $this->formValue('image')); ?>
                        </div>
                    </div>
                    <div id="block-form" class="bBlock">
                        <div class="bRow">
                            <select name="form" id="id" data-target="block-form" class="bTargeted">
                                <option value="">-- Select Form</option>
                                <?php
                                $forms = $this->forms->getFormsArray();
                                foreach($forms as $key => $value){
                                    echo '<option ';
                                    echo 'value="' .  $key . '""';
                                    echo $this->form == $key ? ' selected ' : '';
                                    echo '>';
                                    echo $value;
                                    echo '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="block-form-footer" class="bBlock">
                        <div class="bRow">
                            <label for="visible-4">Visible <input id="visible-4" name="formpop[footer-on]" type="checkbox" class="bCheckbox" <?php $this->formChecked('footer-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('footer-on') ? '' : 'bHidden'; ?>">
                            <?php self::getEditor($this->formValue('footer'), 'textarea-block-form-footer', 'formpop[footer]'); ?>
                        </div>
                    </div>
                    <div id="block-countdown" class="bBlock">
                        <div class="bRow">
                            <label for="visible-4">Visible <input id="visible-4" name="formpop[countdown-on]" type="checkbox" class="bCheckbox" <?php $this->formChecked('countdown-on'); ?>/></label>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('countdown-on') ? '' : 'bHidden'; ?>">
                            <label for="countdown-position">Position</label>
                            <select name="formpop[countdown-position]" id="countdown-position" class="bPosition">
                                <option value="top" <?php echo $this->formValue('countdown-position') == 'top' ? 'selected' : '' ?>>Top</option>
                                <option value="bottom" <?php echo $this->formValue('countdown-position') == 'bottom' ? 'selected' : '' ?>>Bottom</option>
                            </select>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('countdown-on') ? '' : 'bHidden'; ?>">
                            <label for="countdown-text">Text</label>
                            <?php self::getEditor($this->formValue('countdown-text'), 'countdown-text', 'formpop[countdown-text]'); ?>
                        </div>
                        <div class="bRow <?php echo $this->formIsChecked('countdown-on') ? '' : 'bHidden'; ?>">
                            <div class="clear">
                                <?php $y = isset($this->previous['countdown']['year']) ? $this->previous['countdown']['year'] : FALSE; ?>
                                <label for="bYear">Year</label>
                                <select class="bCounter" name="formpop[countdown][year]" id="bYear" data-target="block-countdown">
                                    <?php for($i = date('Y'); $i <= date('Y') + 10; $i++){
                                        $selected = $y == $i ? 'selected' : NULL;
                                        echo '<option value="'. $i .'" '. $selected .'>'. $i .'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="clear">
                                <?php $m = isset($this->previous['countdown']['month']) ? $this->previous['countdown']['month'] : FALSE; ?>
                                <label for="bMonth">Month</label>
                                <select class="bCounter" name="formpop[countdown][month]" id="bMonth" data-target="block-countdown">
                                    <?php for($i = 1; $i <= 12; $i++){
                                        $selected = $m == $i ? 'selected' : NULL;
                                        echo '<option value="'. $i .'" '. $selected .'>'. date("F", mktime(0, 0, 0, $i, 10)) .'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="clear">
                                <?php $d = isset($this->previous['countdown']['day']) ? $this->previous['countdown']['day'] : FALSE; ?>
                                <label for="bDay">Day</label>
                                <select class="bCounter" name="formpop[countdown][day]" id="bDay" data-target="block-countdown">
                                    <?php for($i = 1; $i <= 31; $i++){
                                        $selected = $d == $i ? 'selected' : NULL;
                                        echo '<option value="'. $i .'" '. $selected .'>'. $i .'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="clear">
                                <?php $h = isset($this->previous['countdown']['hours']) ? $this->previous['countdown']['hours'] : FALSE; ?>
                                <label for="bHours">Hour</label>
                                <select class="bCounter" name="formpop[countdown][hours]" id="bHours" data-target="block-countdown">
                                    <?php for($i = 0; $i <= 23; $i++){
                                        $selected = $h == $i ? 'selected' : NULL;
                                         echo '<option value="'. $i .'" '. $selected .'>'. sprintf("%02d", $i) .'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="clear">
                                <?php $n = isset($this->previous['countdown']['minute']) ? $this->previous['countdown']['minute'] : FALSE; ?>
                                <label for="bMinutes">Minute</label>
                                <select class="bCounter" name="formpop[countdown][minute]" id="bMinutes" data-target="block-countdown">
                                    <?php for($i = 0; $i <= 59; $i++){
                                        $selected = $n == $i ? 'selected' : NULL;
                                        echo '<option value="'. $i .'" '. $selected .'>'. sprintf("%02d", $i) .'</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="builderRight">
                <div class="inner bEditable">
                    <div class="bTitle bField"
                         title="Click to add Percentage Filler"
                         data-title="Click to add Percentage Filler"
                         data-content="<div class=&quot;bContent&quot;>&amp;nbsp;</div>"
                         data-block="block-percentage">
                        <?php $this->formContent('percentage', 'Click to add Percentage Filler'); ?>
                    </div>
                    <div id="countdown-top"
                         style="display: <?php echo $countdownPosition == 'top' ? 'block' : 'none'; ?>;"
                         class="bFormCountdown bField"
                         title="Click to add Countdown"
                         data-title="Click to add Countdown"
                         data-content="<div class=&quot;bContent&quot;>&amp;nbsp;</div>"
                         data-block="block-countdown">
                        <?php $this->formContent('countdown', 'Click to add Countdown'); ?>
                    </div>
                    <div class="bDescription bField"
                         title="Click to add Form Title"
                         data-title="Click to add Form Title"
                         data-content="<div class=&quot;bContent&quot;>&amp;nbsp;</div>"
                         data-block="block-title">
                        <?php $this->formContent('title', 'Click to add Form Title'); ?>
                    </div>
                    <div class="bPercentage bField"
                         title="Click to add Pop up Intro"
                         data-title="Click to add Pop up Intro"
                         data-content="<div class=&quot;bContent&quot;>&amp;nbsp;</div>"
                         data-block="block-intro">
                        <?php $this->formContent('intro', 'Click to add Pop up Intro'); ?>
                    </div>
                    <div class="bInner">
                        <div class="bLeft">
                            <div class="bImage bField"
                                 title="Click to Add Image"
                                 data-title="Click to Add Image"
                                 data-block="block-image"
                                id="image-selectTarget">
                                <?php $this->formContent('image', 'Click to Add Image'); ?>
                            </div>
                        </div>
                        <div class="bRight">
                            <div class="bForm bField"
                                 title="Click to Select a Form"
                                 data-title="Click to Select a Form"
                                 data-block="block-form">
                                <?php $this->formContent('form', 'Click to Select a Form'); ?>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="bFormFooter bField"
                             title="Click to add Footer Text"
                             data-title="Click to add Footer Text"
                             data-block="block-form-footer">
                            <?php $this->formContent('footer', 'Click to add Footer Text'); ?>
                        </div>
                        <div id="countdown-bottom"
                             style="display: <?php echo $countdownPosition == 'bottom' ? 'block' : 'none'; ?>;"
                             class="bFormCountdown bField"
                             title="Click to add Countdown"
                             data-title="Click to add Countdown"
                             data-content="<div class=&quot;bContent&quot;>&amp;nbsp;</div>"
                             data-block="block-countdown">
                            <?php $this->formContent('countdown', 'Click to add Countdown'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <script type="text/javascript">
            jQuery(function(){
                // Switch changer
                jQuery(document).on('click', '.bField', function(event){
                    // Show correct one
                    jQuery('.bBlock').hide();
                    jQuery('.bField').removeClass('active');
                    jQuery(this).addClass('active');
                    jQuery('#' + jQuery(this).attr('data-block')).show();
                });
                // Switch visibility
                jQuery(document).on('change', '.bCheckbox', function(event){
                    // Get this status
                    var Checked = jQuery(this).is(":checked");
                    // Get parent
                    var Parent = jQuery(this).closest('.bBlock');
                    var ParentId = Parent.attr('id');
                    // Get block
                    var Block = jQuery('.bField[data-block="'+ ParentId +'"]')[0];
                    var BlockPlaceholder = jQuery(Block).attr('data-title');
                    // Block action
                    if(Checked){
                        jQuery(Block).html('');
                        if(jQuery(Block).attr('data-content') !== ''){
                            jQuery(Block).append(jQuery(Block).attr('data-content'));
                        } else {
                            jQuery(Block).append('<div class="bContent">&nbsp;</div>');
                        }
                        jQuery(Parent).find('.bHidden').show();
                    } else {
                        jQuery(Block).attr('data-content', jQuery(Block).html());
                        jQuery(Block).html('');
                        jQuery(Block).append('<div class="placeholder">'+ BlockPlaceholder +'</div>');
                        jQuery(Parent).find('.bHidden').hide();
                    }
                });

                /**
                 * Input change hurray
                 */
                jQuery(document).on('input', '.bTargeted', function(event){
                    // Get this status
                    var Value = jQuery(this).val();
                    var Name = jQuery(this).attr('name');
                    var IsSelect = false;
                    var isPercentage = false;
                    if(jQuery(this).prop('tagName') == 'SELECT'){
                        var ValueOriginal = Value;
                        Value = jQuery(this).find('option:selected').text();
                        IsSelect = true;
                        // Append value to other selects
                        jQuery('select[name="'+ Name +'"]').val(ValueOriginal);
                    }
                    if(jQuery(this).prop('type') == 'range'){
                        isPercentage = true;
                        Value = Value + '%';
                    }
                    var Target = jQuery(this).attr('data-target');
                    // Get block
                    var Block = jQuery('.bField[data-block="'+ Target +'"]')[0];
                    var BlockPlaceholder = jQuery(Block).attr('data-title');
                    // Block action
                    if(IsSelect && jQuery(this).val() == ''){
                        jQuery(Block).html('');
                        jQuery(Block).append('<div class="placeholder">'+ BlockPlaceholder +'</div>');
                    } else {
                        jQuery(Block).html('');
                        jQuery(Block).append('<div class="bContent">'+ Value +'</div>');
                    }
                });
                // Counter
                jQuery(document).on('input', '.bCounter', function(event){
                    var Value = jQuery(this).val();
                    var Name = jQuery(this).attr('name');
                    var Target = jQuery(this).attr('data-target');
                    var Block = jQuery('.bField[data-block="'+ Target +'"]');
                    // Counter data
                    var Year = jQuery('#bYear.bCounter').val();
                    var Month = jQuery('#bMonth.bCounter > option:selected').text();
                    var Day = jQuery('#bDay.bCounter').val();
                    var Hour = jQuery('#bHours.bCounter > option:selected').text();
                    var Minute = jQuery('#bMinutes.bCounter > option:selected').text();
                    // Data
                    jQuery(Block).html('');
                    jQuery(Block).append('<div class="bContent bCenter">Countdown set to: <strong>'+ Day +' '+ Month +', '+ Year +' at '+ Hour +':'+ Minute +'</strong></div>');
                });
                jQuery(document).on('input', '.bPosition', function(event){
                    var Value = jQuery(this).val();
                    var Name = jQuery(this).attr('name');
                    if(Value == 'top'){
                        jQuery('#countdown-top').show();
                        jQuery('#countdown-bottom').hide();
                    } else {
                        jQuery('#countdown-bottom').show();
                        jQuery('#countdown-top').hide();
                    }
                });
                // Preview button trick
                jQuery(document).on('click', '#preview-button', function(event){
                    // Block preview
                    event.returnValue = null;
                    if(event.preventDefault){ event.preventDefault(); }
                    // Add return value
                    jQuery('form#post').append('<input type="hidden" name="previewModal" value="true" />');
                    // Click save
                    jQuery('#publish').click();
                });
                // Open modal if in url
                jQuery(function() {
                    if(window.location.href.indexOf("previewModal") > -1){
                        jQuery('#preview-button-hidden').click();
                    }
                });
                // On change bTextareas
                setTimeout(function(){
                    for (var i = 0; i < tinymce.editors.length; i++){
                        tinymce.editors[i].onChange.add(function (ed, e){
                            // Save
                            ed.save();
                            var Content = ed.getContent();
                            var Target = jQuery(ed.targetElm).attr('id').replace('textarea-', '');
                            var TargetElement = jQuery('.bField[data-block="' + Target + '"]')[0];
                            // Find textare, get its target and do
                            jQuery(TargetElement).html(
                                '<div class="bContent">' + Content + '</div>'
                            );
                        });
                    }
                }, 1000);
            });
        </script>
        <?php
    }


    /**
     * Image Select
     *
     * @param $name
     * @param $id
     * @param $label
     * @return string
     */
    public function imageSelect($name, $id, $label, $post, $value)
    {
        $fieldId = $id;
        $fieldRow = '<div class="themeMetaboxRow" id="themeMetaboxRow'. $fieldId .'" >';
        $fieldValue = $value;
        $fieldTarget =  $fieldId . 'Target';
        $fieldLabelButton = 'Select Image';
        $fieldLabel = '<div class="genooUploadSelect">';
        $fieldInput = '<input type="hidden" name="'. $name .'" id="'. $fieldId .'" value="'. $fieldValue .'" />';
        $fieldInput .= '<a href="#" onclick="Modal.open(event,this);" '
            . 'id="'. $fieldId . 'Btn' .'" '
            . 'data-current-id="'. $fieldValue .'" '
            . 'data-title="'. $fieldLabelButton .'" '
            . 'data-update-text="'. $fieldLabelButton .'" '
            . 'data-target="'. $fieldTarget .'" '
            . 'data-target-input="'. $fieldId .'" '
            . 'class="button">'. $fieldLabelButton .'</a>';
        $fieldInput .= ' | ';
        $fieldInput .= '<a href="#" onclick="Modal.emptyImagePlaceholder(event,'
            . '\''. $fieldTarget . '\', '
            . '\''. $fieldId . '\', '
            . '\''. $fieldId . 'Btn' . '\');'
            . '">'. __('Remove Image', 'wpmktengine') .'</a>';
        $fieldInput .= '<div class="clear"></div></div>';
        $fieldRow .= $fieldLabel;
        $fieldRow .= $fieldInput;
        $fieldRow .= '</div>';
        // Return!
        return $fieldRow;
    }


    /**
     * Save
     *
     * @param $post_id
     * @return mixed|void
     */
    public function save($post_id)
    {
        if(!current_user_can('edit_post', $post_id)) return;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if(isset($_POST['cta_type']) && $_POST['cta_type'] == 'form'){
            update_post_meta($post_id, 'formpop', sanitize_text_field($_POST['formpop']));
        } else {
            delete_post_meta($post_id, 'formpop');
        }
    }

    /**
     * @param $name
     * @param bool $echo
     * @return bool
     */
    public function formChecked($name, $echo = TRUE)
    {
        if(is_array($this->previous) && isset($this->previous[$name])){
            if($echo){
                echo 'checked="checked"';
            } else {
                return TRUE;
            }
        }
        if(!$echo){
            return FALSE;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function formIsChecked($name)
    {
        if(is_array($this->previous) && isset($this->previous[$name])){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Form content
     *
     * @param $name
     * @param $placeholder
     */
    public function formContent($name, $placeholder)
    {

        if($name == 'form' && $this->formValue($name) !== ''){
            $array = $this->forms->getFormsArray();
            $value = array_key_exists($this->formValue($name), $array) ? $array[$this->formValue($name)] : '';
            echo '<div class="bContent">'. $value .'</div>';
        } elseif($name == 'image' && $this->formIsChecked($name . '-on')){
            $value = $this->formValue($name);
            if(!empty($value)){
                echo wp_get_attachment_image($value, 'medium', FALSE);
            } else {
                echo '<div class="placeholder">'. $placeholder .'</div>';
            }
        } elseif($name == 'percentage' && $this->formIsChecked($name . '-on')){
            echo '<div class="bContent">';
                echo self::getHTMLPercentageRender($this->formValue($name));
            echo '</div>';
        } elseif($name == 'countdown' && $this->formIsChecked($name . '-on')){
            echo '<div class="bContent bCenter">';
            echo self::getHTMLCounterRender($this->formValue($name));
            echo '</div>';
        } else if($this->formIsChecked($name . '-on')){
            echo '<div class="bContent">'. $this->formValue($name) .'</div>';
        } else {
            echo '<div class="placeholder">'. $placeholder .'</div>';
        }
    }


    /**
     * @param $name
     * @return string
     */
    public function formValue($name)
    {
        if(is_array($this->previous) && isset($this->previous[$name])){
            return $this->previous[$name];
        } elseif($name == 'form'){
            return $this->form;
        }
        return '';
    }


    /**
     * @param $data
     * @param $formHtml
     * @return string
     */
    public static function getHTMLRenderer($data, $formHtml, $readyId = NULL)
    {
        // Prep values
        $settings['percentage-on'] = FALSE;
        $settings['percentage'] = '';
        $settings['title-on'] = FALSE;
        $settings['title'] ='';
        $settings['footer-on'] = FALSE;
        $settings['footer'] ='';
        $settings['intro-on'] = FALSE;
        $settings['intro'] ='';
        $settings['intro-inside-on'] = FALSE;
        $settings['image-on'] = FALSE;
        $settings['image'] ='';
        $settings['countdown-on'] = FALSE;
        $settings['countdown'] ='';
        $settings['countdown-position'] = 'top';
        $settings['countdown-text'] = '';
        // Iritate through
        if(is_array($data)){
            foreach($data as $key => $value){
                if(\WPMKTENGINE\Utils\Strings::endsWith($key, '-on') && $value == 'on'){
                    $settings[$key] = TRUE;
                } else {
                    $settings[$key] = $value;
                }
            }
        }
        // Settings to object
        $settings = (object)$settings;
        $image = FALSE;
        // Get to rendering
        $r = new \stdClass();
        $r->content = '';
        if($settings->{'percentage-on'}){
            $r->percentage = self::getHTMLPercentageRender($settings->percentage);
        }
        if($settings->{'title-on'}){
            $r->title = '<div class="genooPopIntro genooPopTitle">' . $settings->title . '</div>';
        }
        if($settings->{'footer-on'}){
            $r->footer = '<div class="genooPopFooter">'. $settings->footer .'</div>';
        }
        if($settings->{'intro-on'}){
            $r->intro = '<div class="genooPopIntro">'. $settings->intro .'</div>';
        }
        if($settings->{'image-on'}){
            $image = wp_get_attachment_image($settings->image, 'medium', FALSE);
            if($image){
                $r->image = '<div class="genooPopImage">'. wp_get_attachment_image($settings->image, 'medium', FALSE) .'</div>';
            }
        }
        if($settings->{'countdown-on'}){
            $id = 'countdown-' . uniqid();
            $time = strtotime(self::getTimeString($settings->countdown, TRUE)) * 1000;
            $text = '';
            if(!empty($settings->{'countdown-text'})){
                $text = '<div class="genooCountdownText">' . $settings->{'countdown-text'} . '</div><div class="clear"></div>';
            }
            $r->countdown = '<div class="genooCountdown" id="'. $id .'">'. $text .'<div class="timing days"></div><div class="timing hours"></div><div class="timing minutes"></div><div class="timing seconds"></div></div>';
            $r->countdown .= '<script type="text/javascript">Counter.attach('. $time  .', "'. $id .'");</script>';
        }
        // Form message inject
        if($settings->{'intro-inside-on'} && $settings->{'intro-on'}){
            $formHtml = $r->intro . $formHtml;
        }
        // Contentos
        $r->content .= '<div class="clear"></div>';
        // Put it all together
        $hideRest = FALSE;
        if(isset($_GET['modalWindow']) && !is_null($readyId) && $_GET['modalWindow'] == 'modalWindow' . ucfirst($readyId)){
            // This is active window
            $hideRest = TRUE;
        }
        if($hideRest === FALSE){
            $r->content .= '<div class="genooPop">';
            $r->content .= $settings->{'percentage-on'} ? $r->percentage : '';
            if($settings->{'countdown-position'} == 'top'){
                $r->content .= $settings->{'countdown-on'} ? $r->countdown : '';
            }
            if(!$settings->{'intro-inside-on'}){
                $r->content .= $settings->{'intro-on'} ? $r->intro : '';
            }
            $r->content .= $settings->{'title-on'} ? $r->title : '';
            if($settings->{'image-on'} && ($image)){
                $r->content .= '<div class="genooPopRight">';
                $r->content .= $formHtml;
                $r->content .= '</div>';
                $r->content .= '<div class="genooPopLeft">';
                $r->content .= $r->image;
                $r->content .= '</div>';
            } else {
                $r->content .= '<div class="genooPopFull">';
                $r->content .= $formHtml;
                $r->content .= '</div>';
            }
            $r->content .= '<div class="clear"></div>';
            $r->content .= $settings->{'footer-on'} ? $r->footer : '';
            if($settings->{'countdown-position'} == 'bottom'){
                $r->content .= $settings->{'countdown-on'} ? $r->countdown : '';
            }
            $r->content .= '</div>';
        }
        // Return
        return $r->content;
    }


    /**
     * Prograss
     *
     * @param int $percentage
     * @param string $cssClass
     * @return string
     */
    public static function getHTMLPercentageRender($percentage = 50, $cssClass = '')
    {
        return '<div class="genooPopProgress"><div class="progress '. $cssClass .'">
	                <span class="progress-bar" style="width: '. $percentage .'%"></span>
	                <span class="progress-per">' . $percentage . '%</span>
                </div></div>';
    }


    /**
     * @param string $data
     * @return string
     */
    public static function getHTMLCounterRender($data = '')
    {
        if(is_array($data)){
            return'Countdown set to: <strong>'. self::getTimeString($data) .'</strong>';
        }
        return '&nbsp;';
    }

    /**
     * @param string $data
     * @param bool $unix
     * @return string
     */
    public static function getTimeString($data = '', $unix = FALSE)
    {
        if(is_array($data)){
            if($unix){
                return $data['year'] . '-' . $data['month'] . '-' . $data['day'] . '  ' . sprintf("%02d", $data['hours']) .':'. sprintf("%02d", $data['minute']);
            }
            return $data['day'] .' '. date("F", mktime(0, 0, 0, $data['month'], 10)) .', '. $data['year'] .' at '. sprintf("%02d", $data['hours']) .':'. sprintf("%02d", $data['minute']);
        }
    }

    /**
     * @param string $value
     * @param string $id
     * @param $name
     */
    public static function getEditor($value = '', $id = '', $name)
    {
        wp_editor($value, $id, array(
            'wpautop' => false,
            'media_buttons' => false,
            'teeny' => true,
            'drag_drop_upload' => false,
            'quicktags' => false,
            'editor_class' => 'bTextarea',
            'textarea_name' => $name ,
            'tinymce' => array(
                'fontsize_formats' => '3px 4px 5px 6px 7px 8px 9px 10px 11px 12px 13px 14px 15px 16px 17px 18px 19px 20px 21px 22px 23px 24px 25px 26px 27px 28px 29px 30px 31px 32px 33px 34px 35px 36px 37px 38px 39px 40px 41px 42px 43px 44px 45px 46px 47px 48px 49px 50px',
                'toolbar1' => 'formatselect,fontselect,fontsizeselect,bold,italic,forecolor,underline,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,undo,redo',
                'plugins' => 'colorpicker,lists,fullscreen,image,wordpress,wpeditimage,wplink,textcolor'
            )
            //styleselect
        ));
    }

    /**
     * @param $form
     * @param $intro
     * @return string
     */
    public static function injectIntroIntoForm($form, $intro)
    {
        // suppress warnings of invalid html
        libxml_use_internal_errors(true);
        // prep
        $html = $form;
        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $dom->preserveWhiteSpace = false;
        $form = $dom->getElementsByTagName("form")->item(0);
        $msg = $dom->getElementById("genooMsg");
        $html .= '<strong class="'.$strongClass.'">' . strip_tags($msg, '<br><br/>') . '</strong>'; //htmlspecialchars
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML($html);
        $msg->appendChild($fragment);
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }
}
