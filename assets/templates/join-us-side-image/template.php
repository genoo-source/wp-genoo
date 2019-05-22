<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }
// Customizer
global $WPME_CUSTOMIZER;
// Customizer data for current view
$view = $WPME_CUSTOMIZER->getView(basename(dirname(__FILE__)));
$class = ($view->getValueOf('gn-modal-image') != '') ? 'gn-has-image' : 'gn-no-image';
?>
<div class="genooForm themeResetDefault gn-form <?= $view->getSwitchClassOf('gn-field-translucent'); ?>">
    <div class="clear"></div>
    <div class="genooGuts">
        <div class="clear"></div>
        <div class="genooPop <?= $class ?>">
            <div class="gn-modal-left-image">
                <?php if($view->getValueOf('gn-modal-image') != ''){ ?>
                <img src="<?= $view->getValueOf('gn-modal-image') ?>" class="gn-image" alt="<?= $view->getValueOf('gn-post-title') ?>">
                <?php } ?>
            </div>
            <div class="gn-modal-left">
                &nbsp;
            </div><!--
            --><div class="gn-modal-right">
                <h2 id="gn-modal-title" class="hide-on-success"><?= $view->getValueOf('gn-post-title') ?></h2>
                <p id="gn-modal-description" class="gn-description hide-on-success"><?= $view->getValueOf('description') ?></p>
                <div id="genooMsg"></div>
                <div class="gn-form-inner hide-on-success">
                <?= $WPME_CUSTOMIZER->getPartial('form'); ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
</div>