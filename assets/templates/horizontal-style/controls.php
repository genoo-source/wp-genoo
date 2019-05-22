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

// Background color
$customizerModalSettings->addField('gn-modal-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Background Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#E87055')
    ->bindDynamicChange()
        ->setFunction('background-color')
        ->setSelector('.genooForm');

// Color
$customizerModalSettings->addField('gn-modal-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Main text and labels color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.control-label, label, .checkbox-control span');

// Height
// When this loads, there's also something that automatically makes the modal the size of the form.
$customizerModalSettings->addField('gn-modal-height')
    ->setFieldType('number')
    ->setFieldLabel('Modal Height')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('200')
    ->bindDynamicChange()
      ->setFunction(function($that){
          $attributes = $that->getAttributes();
          return
              "   var formHeight = parseInt(getComputedStyle(document.querySelector('.gn-form')).height);
                  var modalHeightElem = parent.window.document.querySelector('[data-customize-setting-link=\"horizontal-style[gn-modal-height]\"]');
                  var modalHeight = parseInt(window.getComputedStyle(modalHeightElem).height);
                  if ( formHeight+20 > modalHeight ) { jQuery(modalHeightElem).attr('value', formHeight + 40).val(formHeight + 40); document.querySelector('.gn-modal-background').style.height = (formHeight + 40)+'px'}
                  wp.customize('{$that->fieldId}', function(value){
                      value.bind(function(to){
                          jQuery('.gn-modal-background').css({
                            'height': to + 'px',
                          });
                      });
                  });
              ";
      });

// Submit
$customizerModalSettings->addField('gn-modal-submit-text-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Font Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#ffffff')
    ->bindDynamicChange()
        ->setFunction('color')
        ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, button.gn-btn.gn-btn-primary, .g-recaptcha');

// Submit bg
$customizerModalSettings->addField('gn-modal-submit-background-color')
    ->setFieldType('color')
    ->setFieldLabel('Button Color')
    ->setFieldIsJavascript(true)
    ->setFieldDefaultValue('#0a0000')
    ->bindDynamicChange()
        ->setFunction('background-color')
        ->setSelector('.gn-custom-modal .gn-btn, .gn-custom-modal .gn-btn *, button.gn-btn.gn-btn-primary, .g-recaptcha');

// Attach!
$customizer->attach();
