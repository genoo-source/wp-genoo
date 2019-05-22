<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }
// Customizer
global $WPME_CUSTOMIZER;
// Customizer data for current view
$view = $WPME_CUSTOMIZER->getView(basename(dirname(__FILE__)));
?>

<div class="genooForm__text">
  <h2 id="gn-modal-title" class="hide-on-success hide-on-fail">
    <?= $view->getValueOf('gn-post-title') ?></h2>
  <p id="gn-modal-description" class="gn-description hide-on-success hide-on-fail"><?= $view->getValueOf('description') ?></p>
</div>
<div class="genooForm themeResetDefault themeDefault">
  <div class="clear"></div>
  <div class="genooGuts">
    <div id="genooMsg"></div>
    <div class="clear"></div>
    <div class="genooPop">
      <div class="genooPopFull">
          <?= $WPME_CUSTOMIZER->getPartial('form'); ?>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <div class="clear"></div>
</div>
