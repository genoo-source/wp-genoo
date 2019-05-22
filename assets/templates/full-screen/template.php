<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }
// Customizer
global $WPME_CUSTOMIZER;
// Customizer data for current view
$view = $WPME_CUSTOMIZER->getView(basename(dirname(__FILE__)));
?>
<div class="gn-modal-background-under"></div>
<div class="genooForm themeResetDefault gn-form <?= $view->getSwitchClassOf('gn-field-translucent'); ?>">
    <div class="clear"></div>
    <div class="genooGuts">
        <div class="clear"></div>
        <div class="genooPop">
            <div id="genooMsg"></div>
            <h2 id="gn-modal-title" class="hide-on-success"><?= $view->getValueOf('gn-post-title') ?></h2>
            <p id="gn-modal-description" class="gn-description hide-on-success"><?= $view->getValueOf('description') ?></p>
            <div class="genooPopFull hide-on-success">
                <?= $WPME_CUSTOMIZER->getPartial('form'); ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
</div>