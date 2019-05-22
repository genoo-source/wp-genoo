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
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('yes')
    ->bindDynamicChange()
        ->setFunction('checkbox-switch-hide')
        ->setSelector('span.req');

// Checkbox
$customizerModalSettings->addField('gn-field-translucent')
    ->setFieldType('checkbox')
    ->setFieldLabel('Make form fields translucent?')
    ->setFieldDescription('This will make form fields translucent.')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('yes')
    ->bindDynamicChange()
        ->setFunction('toggle-class')
        ->setSelector('.gn-form')
        ->setTargetSwitch('gn-translucent');

// Background color
$customizerModalSettings->addField('gn-modal-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Background Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#0058a5')
    ->bindDynamicChange()
        ->setFunction('background-color')
        ->setSelector('.gn-modal-background-under');

// Color
$customizerModalSettings->addField('gn-modal-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Main text and labels color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.gn-custom-modal #gn-modal-title, .gn-custom-modal #gn-modal-description, .gn-custom-modal .gn-form label:not(.gn-btn), .gn-custom-modal span.req, .gn-custom-modal span.required, body .themeResetDefault.gn-form label:not(.gn-btn), strong.genooError.fielderror, .checkbox-control');

// Submit
$customizerModalSettings->addField('gn-modal-submit-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Font Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, .gn-modal-close, button.gn-btn.gn-btn-primary, .g-recaptcha');

// Submit bg
$customizerModalSettings->addField('gn-modal-submit-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#0a0000')
    ->bindDynamicChange()
        ->setFunction('background-color')
        ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, .gn-modal-close, button.gn-btn.gn-btn-primary, .g-recaptcha');

// Attach!
$customizer->attach();
