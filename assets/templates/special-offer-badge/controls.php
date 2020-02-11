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

// Image
$customizerModalSettings->addField('gn-modal-image')
    ->setFieldType('image')
    ->setFieldIsJavascript(true)
    ->setFieldLabel('Featured image')
    // Get background image from current folder
    ->setFieldDefaultValue($templateStorage->getThemeFileUrl('special-offer-badge', 'special-offer.png'))
    ->setFieldDescription('Image must be square to display correctly.')
      ->bindDynamicChange()
          ->setSelector('.top-left-featured-image')
          ->setAttributes(
              array(
                  'element-for-image' => '.top-left-featured-image',
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
                          jQuery('.top-left-featured-image').attr('src', to);
                      });
                  });
              ";
      });
    // ->bindDynamicChange()
    //     ->setFunction('background-image')
    //     ->setSelector('.gn-modal-background');

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
// $customizerModalSettings->addField('gn-field-translucent')
//     ->setFieldType('checkbox')
//     ->setFieldLabel('Make form fields translucent?')
//     ->setFieldDescription('This will make form fields translucent.')
//     ->setFieldIsJavascript(true)
//     ->setFieldDefaultValue('yes')
//     ->bindDynamicChange()
//         ->setFunction('toggle-class')
//         ->setSelector('.gn-form')
//         ->setTargetSwitch('gn-translucent');

// Image
// $customizerModalSettings->addField('gn-modal-background')
//     ->setFieldType('image')
//     ->setFieldIsJavascript(true)
//     ->setFieldLabel('Background image')
//     // Get background image from current folder
//     ->setFieldDefaultValue($templateStorage->getThemeFileUrl('sign-up-with-us', 'img-background.jpg'))
//     ->setFieldDescription('Add image that is BIG!')
//     ->bindDynamicChange()
//         ->setFunction('background-image')
//         ->setSelector('.gn-modal-background');

// Background color
// $customizerModalSettings->addField('gn-modal-background-color')
//     ->setFieldType('color')
//     ->setFieldLabel('Background Color')
//     ->setFieldIsJavascript(true)
//     ->setFieldDefaultValue('#000000')
//     ->bindDynamicChange()
//         ->setFunction('background-color')
//         ->setSelector('.gn-modal-background');

// Color
// $customizerModalSettings->addField('gn-modal-text-color')
//     ->setFieldType('color')
//     ->setFieldLabel('Main text and labels color')
//     ->setFieldIsJavascript(true)
//     ->setFieldDefaultValue('#ffffff')
//     ->bindDynamicChange()
//         ->setFunction('color')
//         ->setSelector('.gn-custom-modal #gn-modal-title, .gn-custom-modal #gn-modal-description, .gn-custom-modal .gn-form label, .gn-custom-modal span.req, body .themeResetDefault.gn-form label');

// Submit
$customizerModalSettings->addField('gn-modal-submit-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Font Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, .g-recaptcha');

// Submit bg
$customizerModalSettings->addField('gn-modal-submit-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#4FAC2A')
    ->bindDynamicChange()
        ->setFunction('background-color')
        ->setSelector('.gn-custom-modal .gn-btn, .g-recaptcha');

// Attach!
$customizer->attach();
