<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }

// Global me please
global $wp_customize;

// Customizer
$customizer = new \WPME\Customizer\Customizer($wp_customize);
$repositorySettings = new \WPME\RepositorySettingsFactory();

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
        ->setSelector('.genooTitle');

// Textarea
$customizerModalSettings->addField('description')
    ->setFieldType('textarea')
    ->setFieldLabel('Description')
    ->setDataType('post_meta_single')
    ->setAutoDefaultValue()
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('text')
        ->setSelector('.genooDesc');

// CTA Display
$customizerModalSettings->addField('display_ctas')
    ->setFieldType('select')
    ->setFieldLabel('Show')
    ->setDataType('post_meta_single')
    ->setFieldChoices(
        array(
            ''          =>  __('No title and description', 'genoo'),
            'titledesc' =>  __('Title and Description', 'genoo'),
            'title'     =>  __('Title only', 'genoo'),
            'desc'      =>  __('Description only', 'genoo'),
        )
    )
    ->setAutoDefaultValue()
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('select-switch-class')
        ->setSelector('.genooForm.themeResetDefault');

// Checkbox
$customizerModalSettings->addField('gn-hide-modal-desc')
    ->setFieldType('checkbox')
    ->setFieldLabel('Hide Modal Description?')
    ->setFieldDescription('This will hide the modal description.')
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('checkbox-switch-hide')
        ->setSelector('.genooForm .genooDesc');

// Theme
$customizerModalSettings->addField('form_theme')
    ->setFieldType('select')
    ->setFieldLabel('Theme')
    ->setDataType('post_meta_single')
    ->setFieldChoices($repositorySettings->getSettingsThemes())
    ->setAutoDefaultValue()
    ->setFieldIsJavascript(true)
    ->bindDynamicChange()
        ->setFunction('select-switch-class')
        ->setSelector('.genooForm.themeResetDefault');

// Checkbox
$customizerModalSettings->addField('gn-hide-required-label')
    ->setFieldType('checkbox')
    ->setFieldLabel('Hide "* = required" text?')
    ->setFieldDescription('This will hide the "required" text on your modal window.')
    ->setFieldDefaultValue('yes')
        ->setFieldIsJavascript(true)
        ->bindDynamicChange()
        ->setFunction('checkbox-switch-hide')
        ->setSelector('.genooPop span.req');

// Attach!
$customizer->attach();