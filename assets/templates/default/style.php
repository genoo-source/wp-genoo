<?php
// Go back to your own country
if(!defined('ABSPATH')){ exit; }
// Customizer
global $WPME_CUSTOMIZER;
global $WPME_MODAL_ID;
// Customizer data
$view = $WPME_CUSTOMIZER->getView(basename(dirname(__FILE__)));
$id = isset($WPME_MODAL_ID) ? $WPME_MODAL_ID : '';
?>
<style>
    /* Hide req*? */
    <?php if($view->getValueOf('gn-hide-required-label') == 'yes' || $view->getValueOf('gn-hide-required-label') == true){ ?>
    <?= $id ?> span.req { display: none; }
    <?php } ?>
    /* Hide Title*? */
    <?php if($view->getValueOf('gn-hide-modal-title') == 'yes' || $view->getValueOf('gn-hide-modal-title') == true){ ?>
    <?= $id ?> .genooForm .genooTitle { display: none; }
    <?php } ?>
    /* Hide Title*? */
    <?php if($view->getValueOf('gn-hide-modal-desc') == 'yes' || $view->getValueOf('gn-hide-modal-desc') == true){ ?>
    <?= $id ?> .genooForm .genooDesc { display: none; }
    <?php } ?>
    /* Simple Styles */
    form input[type="radio"] {
      margin: 6px 8px !important;
    }
    form .checkbox-control {
      padding: 10px 0 !important;;
    }
    [id^="on-step-2"] { display: none; }
    .gn-btn {
      padding: 10px !important;
      text-align: center !important;
      border-radius: 2px;
      border: none;
      display: block;
      width: 100%;
    }
    /* Description show hide */
    /* Default state, no title and description */
    <?= $id ?> .genooForm .genooDesc,
    <?= $id ?> .genooForm .genooTitle { display: none !important; }
    /* Title and Description */
    <?= $id ?> .genooForm.titledesc .genooDesc,
    <?= $id ?> .genooForm.titledesc .genooTitle { display: block !important; }
    /* Title only */
    <?= $id ?> .genooForm.title .genooDesc { display: none !important; }
    <?= $id ?> .genooForm.title .genooTitle { display: block !important; }
    /* Description only */
    <?= $id ?> .genooForm.desc .genooDesc { display: block !important; }
    <?= $id ?> .genooForm.desc .genooTitle { display: none !important; }
</style>
