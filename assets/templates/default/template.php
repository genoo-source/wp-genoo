<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }
// Customizer
global $WPME_CUSTOMIZER;
// Customizer data for current view
$view = $WPME_CUSTOMIZER->getView(basename(dirname(__FILE__)));
$viewTheme = $view->getValueOf('form_theme');
$viewShowTitle = $view->getValueOf('form_theme');
$viewShowDescription = $view->getValueOf('display_ctas');

?>
<div
    class="genooForm themeResetDefault <?= $viewTheme; ?> <?= $viewShowDescription ?>"
    data-switch-default-form-theme="<?= $viewTheme; ?>"
    data-switch-default-display-ctas="<?= $viewShowDescription; ?>">
    <div class="genooTitle hide-on-success"><?= $view->getValueOf('gn-post-title') ?></div>
    <div class="genooDesc hide-on-success"><?= $view->getValueOf('description') ?></div>
    <div class="clear"></div>
    <div class="genooGuts">
        <div id="genooMsg"></div>
        <div class="clear"></div>
        <div class="genooPop hide-on-success">
            <?= $WPME_CUSTOMIZER->getPartial('form'); ?>
        </div>
    </div>
    <div class="clear"></div>
</div>