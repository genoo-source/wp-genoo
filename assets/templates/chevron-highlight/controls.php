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

// Background color
$customizerModalSettings->addField('gn-modal-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Form Background Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ff5555')
    ->bindDynamicChange()
    ->setFunction(function($that){
        $attributes = $that->getAttributes();
        return
            "
                  wp.customize('{$that->fieldId}', function(value){
                      value.bind(function(to){
                          jQuery('.genooPopFull').css('background', to);
                          jQuery('.genooPopFull--arrow-down').css({
                            'border-right-color': to,
                            'border-left-color': to
                          });
                      });
                  });
              ";
    });

// Color
$customizerModalSettings->addField('gn-modal-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Form Labels Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#000000')
    ->bindDynamicChange()
    ->setFunction('color')
    ->setSelector('.gn-form label:not(.gn-btn label):not(.gn-btn), .checkbox-control span');

$customizerModalSettings->addField('gn-hide-required-label')
    ->setFieldType('checkbox')
    ->setFieldLabel('Hide "* = required" text?')
    ->setFieldDescription('This will hide the "required" text on your modal window.')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('yes')
    ->bindDynamicChange()
    ->setFunction('checkbox-switch-hide')
    ->setSelector('span.req');

// Submit
$customizerModalSettings->addField('gn-modal-submit-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Font Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
    ->setFunction('color')
    ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, button.gn-btn.gn-btn-primary, .g-recaptcha, .gn-btn.gn-btn-primary');

// Submit bg
$customizerModalSettings->addField('gn-modal-submit-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#0a0000')
    ->bindDynamicChange()
    ->setFunction('background-color')
    ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, button.gn-btn.gn-btn-primary, .g-recaptcha, .gn-btn.gn-btn-primary');

// Attach!
$customizer->attach();
