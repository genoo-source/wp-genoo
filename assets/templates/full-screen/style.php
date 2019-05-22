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

    /* Start Custom CSS */
    @import url('https://fonts.googleapis.com/css?family=Dosis');

    <?= $id ?> .gn-form {
        width: 100% !important;
        text-align: center;
        max-width: none !important;
        display: table !important;
    }
    <?= $id ?> .gn-form,
    <?= $id ?> .gn-form * {
        box-sizing: border-box;
    }
    <?= $id ?> .gn-form .form-group {
        margin-bottom: 14px;
        display: inline-block;
        width: 100%;
    }
    <?= $id ?> .gn-form label {
        display: inline-block;
        max-width: 100%;
        float: left;
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 13px;
        text-align: left;
    }
    <?= $id ?> .gn-form span.req {
        font-weight: 500;
        font-size: 13px;
    }

    <?= $id ?> .gn-form .checkbox-control {
      text-align: left;
      clear: both;
    }
    <?= $id ?> input[type="radio"],
    <?= $id ?> input[type="checkbox"] {
      margin-right: 4px;
    }

    <?= $id ?> .gn-form input:not([type="submit"]):not([type="radio"]):not([type="checkbox"]),
    <?= $id ?> .gn-form select,
    <?= $id ?> .gn-form select,
    <?= $id ?> .gn-form textarea.form-control,
    <?= $id ?> .gn-form textarea,
    <?= $id ?> .gn-form textarea.ext-form-input {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        border: 0;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }
    <?= $id ?> .gn-form input:focus,
    <?= $id ?> .gn-form select:focus {
        border-color: #66afe9;
        outline: 0;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
    }
    <?= $id ?> ::-webkit-input-placeholder,
    <?= $id ?> ::-moz-placeholder,
    <?= $id ?> :-ms-input-placeholder,
    <?= $id ?> :-moz-placeholder { /* Firefox 18- */
        color: #fff;
        font-weight: 100;
        font-family: Avenir, Dosis, Arial, sans-serif;
    }
    /* The Button */
    <?= $id ?>.gn-custom-modal .form-button-submit,
    <?= $id ?>.gn-custom-modal .g-recaptcha,,
    <?= $id ?> .gn-btn,
    <?= $id ?> .gn-form input[type="submit"] {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 15px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    <?= $id ?>.genooModal {
        width: 600px;
        margin-left: -300px;
        text-align: center;
        font-family: Avenir, Dosis, Arial, sans-serif;
        border-radius: 4px;
    }
    <?= $id ?> .gn-secondary-text {
        background: #fff;
        display: block;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        font-size: 12px;
        padding: 8px;
        font-weight: 900;
    }
    <?= $id ?>.gn-custom-modal h2 {
        font-size: 25px;
        font-weight: 100;
        padding: 0 20px;
        margin: 0;
    }
    <?= $id ?> .gn-description {
        font-size: 14px;
        padding: 0 40px;
    }
    <?= $id ?> .form-group {
        padding: 0 20px;
    }
    <?= $id ?> .form-control { font-weight: 100 !important; }
    <?= $id ?>.gn-custom-modal .form-button-submit,
    <?= $id ?>.gn-custom-modal .g-recaptcha,
    <?= $id ?>.gn-custom-modal .gn-btn {
        width: auto !important;
        border: 0;
        font-size: 14px;
        text-transform: uppercase;
    }
    <?= $id ?> .gn-step-1-overlay label.gn-btn.gn-btn-primary {
         margin: 0 auto;
         display: inline-block;
         float: none;
    }
    <?= $id ?>.gn-custom-modal {}
    <?= $id ?>.gn-custom-modal .genooGuts { padding: 30px 50px !important; }
    <?= $id ?>.gn-custom-modal #gn-modal-description { padding: 5px 0 !important; }

    <?= $id ?>.gn-custom-modal .gn-translucent textarea,
    <?= $id ?>.gn-custom-modal .gn-translucent select,
    <?= $id ?>.gn-custom-modal .gn-translucent .ext-form-input {
        color: #ffffff;
        background-color: rgba(255,255,255,0.25);
        background-image: none;
    }

    /**
     * Custom
     */

    <?= $id ?>.gn-custom-modal-full-screen,
    <?= $id ?>.gn-custom-modal-full-screen * {
        max-width: none !important;
    }
    <?= $id ?>.gn-custom-modal-full-screen {
        margin: 0 !important;
        left: 0;
        top: 0;
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
    }
    body <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background { min-height: 100% !important; }
    body <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background { display: table !important; }
    <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background,
    <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background .gn-form {
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
    }
    <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background .gn-form { display: table-row; }
    <?= $id ?>.gn-custom-modal-full-screen .gn-modal-background .genooGuts {
        display: table-cell;
        vertical-align: middle;
    }
    <?= $id ?>.gn-custom-modal-full-screen .gn-modal-close {
        top: 20px;
        right: 20px;
        z-index: 999999;
    }
    <?= $id ?> .gn-modal-background-under {
        display: block;
        width: 100%;
        height: 100%;
        min-width: 100%;
        min-height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0.8;
        z-index: -10;
    }
    <?= $id ?> .gn-generated {
        width: 100% !important;
        height: 100%;
        min-width: 100%;
        min-height: 100%;
        position: absolute;
        display: table;
    }

    <?= $id ?> label.gn-btn {
      text-align: center !important;
      padding: 6px 12px;
    }

    <?= $id ?> [id^="on-step-2"] {
      display: none;
    }

    /**
     * Mobile
    */

    @media screen and (max-width: 320px){
        #genooOverlay { height: auto; min-height: 100%; }
        <?= $id ?>.genooModal.visible {
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            right: auto;
            margin: 0;
            width: 100%;
            margin: 0;
            padding: 10px;
        }
        <?= $id ?> .form-group {
            padding: 0 !important;
        }
        <?= $id ?> .gn-description,
        <?= $id ?> h2 {
            padding: 0 5px !important;
        }
    }


    /**
     * Generated
    */

    /* Modal Background image? */
    <?= $id ?> .gn-modal-background {
        <?php if($view->getValueOf('gn-modal-background') == ''){ ?>
        background-image: none;
        <?php } else { ?>
        background-image: url("<?= $view->getValueOf('gn-modal-background') ?>");
        <?php } ?>
        background-size: cover;
    }
    /* Hide req*? */
    <?php if($view->getValueOf('gn-hide-required-label') == 'yes' || $view->getValueOf('gn-hide-required-label') == true){ ?>
    <?= $id ?> span.req { display: none; }
    <?php } ?>
    /* Modal text color */
    <?= $id ?>.gn-modal-result-success #genooMsg,
    <?= $id ?>.gn-modal-result-fail #genooMsg,
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-text-color', $id); ?> {
        color: <?= $view->getValueOf('gn-modal-text-color') ?>;
    }
    /* Modal background color */
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-background-color', $id); ?> {
        background-color: <?= $view->getValueOf('gn-modal-background-color') ?>;
    }
    /* Submib button color */
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-submit-text-color', $id); ?> {
        color: <?= $view->getValueOf('gn-modal-submit-text-color') ?>;
    }
    /* Submib button background color */
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-submit-background-color', $id); ?> {
        background-color: <?= $view->getValueOf('gn-modal-submit-background-color') ?>;
    }
    <?= $id ?>.gn-modal-result-success #genooMsg strong,
    <?= $id ?>.gn-modal-result-fail #genooMsg strong {
        border-bottom: 1px solid <?= $view->getValueOf('gn-modal-text-color') ?>;
        padding-bottom: 10px;
        margin-bottom: 20px;
        color: <?= $view->getValueOf('gn-modal-text-color') ?>;
    }
</style>
