<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }

// Global me please
global $wp_customize;

// Customizer
$customizer = new \WPME\Customizer\Customizer($wp_customize);

// View (one tab)
$customizerModalSettings = $customizer->addView(basename(dirname(__FILE__)), 'Modal Settings');

// Storage
$templateStorage = new \WPME\Customizer\TemplateStorage();

// Text
$customizerModalSettings->addField('gn-post-title')
    ->setFieldType('text')
    ->setFieldLabel('Modal Title')
    ->setDataType('post_meta_single')
    ->setAutoDefaultValue()
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('text')
        ->setSelector('#gn-modal-title');

// Textarea
$customizerModalSettings->addField('description')
    ->setFieldType('textarea')
    ->setFieldLabel('Description')
    ->setDataType('post_meta_single')
    ->setAutoDefaultValue()
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('text')
        ->setSelector('#gn-modal-description');

// Checkbox
$customizerModalSettings->addField('gn-hide-required-label')
    ->setFieldType('checkbox')
    ->setFieldLabel('Hide "* = required" text?')
    ->setFieldDescription('This will hide the "required" text on your modal window.')
    ->setFieldDefaultValue('yes')
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('checkbox-switch-hide')
        ->setSelector('span.req');

// Submit
$customizerModalSettings->addField('gn-modal-submit-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Font Color')
    // ->setFieldDescription('Use a color that reflects with a submit button background color.')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.gn-custom-modal .form-button-submit, button.gn-btn.gn-btn-primary, .g-recaptcha');

// Submit bg
$customizerModalSettings->addField('gn-modal-submit-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Color')
    // ->setFieldDescription('This color also affects border color of form elements on active state.')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ac4e29')
    ->bindDynamicChange()
        ->setSelector('.gn-custom-modal .form-button-submit, button.gn-btn.gn-btn-primary, .g-recaptcha')
        ->setFunction('background-color');

// Image
$customizerModalSettings->addField('gn-modal-image')
    ->setFieldType('image')
    ->setFieldIsJavascript(true)
    ->setFieldLabel('Featured image')
    ->setFieldDescription('The recommended size should be 300px wide by 500px high.')
    // Get background image from current folder
    ->setFieldDefaultValue($templateStorage->getThemeFileUrl('join-us-side-image', 'img-background.jpg'))
    ->bindDynamicChange()
        ->setSelector('.gn-custom-modal-join-us-side-image .genooPop')
        ->setAttributes(
            array(
                'element-for-image' => '.gn-modal-left-image',
                'class-to-toggle' => 'gn-has-image gn-no-image',
                'image-class' => 'gn-image'
            )
        )
        // Dynamically created function using attributes from above that can be changed
        // if needed
        ->setFunction(function($that){
            $attributes = $that->getAttributes();
            return
                "
                    wp.customize('{$that->fieldId}', function(value){
                        value.bind(function(to){
                            // Toggle image / no image class
                            jQuery('{$that->selector}').toggleClass('{$attributes->{'class-to-toggle'}}');
                            // Append remove image
                            if(0 === jQuery.trim(to).length){
                                // No image
                                jQuery('{$attributes->{'element-for-image'}}').empty();
                            } else {
                                // Image
                                jQuery('{$attributes->{'element-for-image'}}').empty().append('<img src=\"'+ to +'\" class=\"{$attributes->{'image-class'}}\">');;
                            }
                        });
                    });
                ";
        });

// Attach!
$customizer->attach();
