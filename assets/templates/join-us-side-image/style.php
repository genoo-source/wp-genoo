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

    <?= $id ?> form,
    <?= $id ?> form * {
        box-sizing: border-box;
    }
    <?= $id ?> form .form-group {
        margin-bottom: 14px;
        display: inline-block;
        width: 100%;
    }
    <?= $id ?> form label {
        display: inline-block;
        max-width: 100%;
        float: left;
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 14px;
        text-align: left;
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
    <?= $id ?> form select.ext-form-input,
    <?= $id ?> form select,
    <?= $id ?> form textarea.form-control,
    <?= $id ?> form textarea,
    <?= $id ?> form textarea.ext-form-input {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        border: 0;
        border-radius: 5px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        background: #EDEDED;
        color: #000;
        border: 2px solid #EDEDED;
    }
    <?= $id ?> ::-webkit-input-placeholder,
    <?= $id ?> ::-moz-placeholder,
    <?= $id ?> :-ms-input-placeholder,
    <?= $id ?> :-moz-placeholder {
        color: #000;
        font-weight: 100;
        font-family: Avenir, Dosis, Arial, sans-serif;
    }
    /* The Button */
    <?= $id ?>.gn-custom-modal .form-button-submit,
    <?= $id ?> .gn-btn,
    <?= $id ?> .g-recaptcha,
    <?= $id ?> form input[type="submit"] {
        display: inline-block;
        padding: 6px 12px;
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
    }

    <?= $id ?>.genooModal {
        width: 600px;
        margin-left: -300px;
        text-align: center;
        font-family: Avenir, Dosis, Arial, sans-serif;
        border-radius: 4px;
    }
    <?= $id ?> h2 {
        font-size: 22px;
        font-weight: 100;
        padding-top: 10px;
        margin: 0;
    }
    <?= $id ?> .gn-description {
        font-size: 14px;
        padding: 10px 0;
    }
    <?= $id ?> .form-group {
        padding: 0 20px;
    }

    <?= $id ?> .form-control { font-weight: 100 !important; }
    <?= $id ?>.gn-custom-modal .form-button-submit,
    <?= $id ?>.gn-custom-modal ..g-recaptcha,
    <?= $id ?>.gn-custom-modal .gn-btn {
        max-width: 200px;
        border: 0;
        font-size: 14px;
        text-transform: uppercase;
    }
    <?= $id ?>.gn-custom-modal {}
    <?= $id ?>.gn-custom-modal .genooGuts { padding: 0 !important; }
    <?= $id ?>.gn-custom-modal #gn-modal-description { padding: 5px 0 !important; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-left,
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-right {
        display: inline-block;
        width: 50%;
        vertical-align: top;
    }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-left { position: relative; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-right { padding: 20px 30px; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-image {
        max-width: 100%;
        width: 100%;
        height: auto;
        min-height: 100%;
        max-height: none;
        display: block;
        position: absolute;
        width: auto;
    }
    <?= $id ?>.gn-custom-modal-join-us-side-image form-inner {
        margin-top: 15px;
    }
    <?= $id ?> .genooGuts { overflow: auto; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-has-image { position: relative; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-left-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: auto;
        width: 50%;
        max-height: 100%;
        overflow: hidden;
        min-height:100%;
    }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-no-image { position: relative; }
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-no-image .gn-modal-left-image,
    <?= $id ?>.gn-custom-modal-join-us-side-image .gn-no-image .gn-modal-left {
        display: none;
    }

    /**
     * Generated
    */

    <?= $id ?>.gn-custom-modal .form-button-submit,
    <?= $id ?> .g-recaptcha,
    <?= $id ?> .gn-btn,
    <?= $id ?> form input[type="submit"] {
        width: auto !important;
        max-width: none;
    }

    /* Hide req*? */
    <?php if($view->getValueOf('gn-hide-required-label') == 'yes' || $view->getValueOf('gn-hide-required-label') == true){ ?>
    <?= $id ?> span.req { display: none; }
    <?php } ?>
    /* Submib button color */
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-submit-text-color', $id); ?> {
        color: <?= $view->getValueOf('gn-modal-submit-text-color') ?>;
    }
    /* Submib button background color */
    <?php echo $view->getBinderSelectorOfPrefixedWith('gn-modal-submit-background-color', $id); ?> {
        background-color: <?= $view->getValueOf('gn-modal-submit-background-color') ?>;
    }

    <?= $id ?> form input.form-control:hover,
    <?= $id ?> form input.ext-form-input:hover,
    <?= $id ?> form select:hover,
    <?= $id ?> form select.form-control:hover,
    <?= $id ?> form select.ext-form-input:hover,
    <?= $id ?> form textarea:hover,
    <?= $id ?> form textarea.form-control:hover,
    <?= $id ?> form textarea.ext-form-input:hover,
    <?= $id ?> form input.form-control:active,
    <?= $id ?> form input.ext-form-input:active,
    <?= $id ?> form select:active,
    <?= $id ?> form select.form-control:active,
    <?= $id ?> form select.ext-form-input:active,
    <?= $id ?> form textarea:active,
    <?= $id ?> form textarea.form-control:active,
    <?= $id ?> form textarea.ext-form-input:active,
    <?= $id ?> form input.form-control:focus,
    <?= $id ?> form input.ext-form-input:focus,
    <?= $id ?> form select:focus,
    <?= $id ?> form select.form-control:focus,
    <?= $id ?> form select.ext-form-input:focus,
    <?= $id ?> form textarea:focus,
    <?= $id ?> form textarea.form-control:focus,
    <?= $id ?> form textarea.ext-form-input:focus {
        border-color: <?= $view->getValueOf('gn-modal-submit-background-color') ?>;
    }

    /**
     * Hide Image upon failure as it won't likely fit the window anyways
     */

	  <?= $id ?>.gn-modal-result-success .gn-modal-right {
      width: 100%;
    }
    <?= $id ?>.gn-modal-result-success .gn-modal-left,
    <?= $id ?>.gn-modal-result-success .gn-modal-left-image {
      display: none;
    }

    <?= $id ?> .gn-modal-left-image img {
      left: 50%;
      transform: translateX(-50%);
    }

    /**
     * Mobile
    */

    @media all and (max-width: 605px){
        #genooOverlay {
            height: auto; min-height: 100%;
        }
        <?= $id ?>.genooModal.visible {
            display: block;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: auto !important;
            margin: 0 !important;
            width: 100%;
            margin: 0;
            padding: 10px;
            transform: none !important;
        }
        <?= $id ?>.gn-custom-modal-join-us-side-image .form-group {
                   padding: 0 !important;
        }
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-description,
        <?= $id ?>.gn-custom-modal-join-us-side-image h2 {
                   padding: 0 5px !important;
               }
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-no-image .gn-modal-left-image,
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-no-image .gn-modal-left,
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-has-image .gn-modal-left,
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-has-image .gn-modal-left-image {
            display: none !important;
        }
        <?= $id ?>.gn-custom-modal-join-us-side-image .gn-modal-right { width: 100%; }
    }

</style>
