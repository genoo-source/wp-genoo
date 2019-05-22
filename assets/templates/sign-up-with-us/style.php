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

    <?= $id ?> label.gn-btn {
      position: relative;
      left: 50%;
      transform: translateX( -50% );
    }

    <?= $id ?> .gn-form input:not([type="submit"]):not([type="radio"]):not([type="checkbox"]),
    <?= $id ?> .gn-form select,
    <?= $id ?> .gn-form select.form-control,
    <?= $id ?> .gn-form select.ext-form-input,
    <?= $id ?> .gn-form textarea,
    <?= $id ?> .gn-form textarea.form-control,
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
    <?= $id ?> .gn-form input.form-control:focus,
    <?= $id ?> .gn-form select.form-control:focus {
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
    <?= $id ?> .gn-btn,
    <?= $id ?> .g-recaptcha,
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
        color: <?= $view->getValueOf('gn-modal-submit-text-color') ?>;
        background: <?= $view->getValueOf('gn-modal-submit-background-color') ?>;
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
    <?= $id ?> .g-recaptcha,
    <?= $id ?>.gn-custom-modal .gn-btn {
        border: 0;
        font-size: 14px;
        text-transform: uppercase;
    }
    <?= $id ?>.gn-custom-modal {}
    <?= $id ?>.gn-custom-modal .genooGuts { padding: 30px 50px !important; }
    <?= $id ?>.gn-custom-modal #gn-modal-description { padding: 5px 0 !important; }
    <?= $id ?>.gn-custom-modal .gn-translucent textarea,
    <?= $id ?>.gn-custom-modal .gn-translucent input:not([type="submit"]):not([type="button"]) {
        color: #ffffff;
        background-color: rgba(255,255,255,0.25);
        background-image: none;
    }

    /**
     * Mobile
    */

    @media screen and (max-width: 605px){
        #genooOverlay { height: auto; min-height: 100%; }
        <?= $id ?>.genooModal.visible {
            display: block !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: auto !important;
            margin: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 10px !important;
            transform: none !important;
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

    [id^="on-step-2"]{display: none;}[id^="on-step-2"]:not(:checked) ~ .gn-tier-0 {display: none !important;}[id^="on-step-2"]:checked ~ .gn-step-1-overlay {display: none !important;}[for="on-step-2"] {width: 100%;}
</style>
