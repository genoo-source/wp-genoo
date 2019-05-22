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
/*<?= $id ?> .genooForm {
  width: 100%;
  margin-right: 20px;
}
<?= $id ?> .genooGuts {
  padding: 0 !important;
}*/
<?= $id ?> .gn-form,
<?= $id ?> .gn-form * {
  box-sizing: border-box;
  font-family: Montserrat, Arial, helvetica, sans-serif;
  font-size: 15px;
  line-height: 1.42857143;
}

<?= $id ?> .gn-form .form-group {
  margin-bottom: 14px;
  display: inline-block;
  width: 100%;
}

<?= $id ?> .gn-generated {
  height: 100%;
}

<?= $id ?> .gn-form label.control-label {
  display: inline-block;
  max-width: 100%;
  margin-bottom: 5px;
  font-weight: 700;
}

<?= $id ?> .gn-form input.form-control,
<?= $id ?> .gn-form select.form-control,
<?= $id ?> .gn-form textarea.form-control{
  display: block;
  width: 100%;
  height: 34px;
  padding: 6px 12px;
  font-size: 14px;
  line-height: 1.42857143;
  color: #555;
  background-color: #fff;
  background-image: none;
  border: 1px solid #ccc;
  border-radius: 4px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
  -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}
<?= $id ?> .gn-form textarea.form-control { height: auto; }

<?= $id ?> .gn-form .gn-field-container {
  margin: 0 2px;
}

<?= $id ?> .gn-form input.form-control:focus,
<?= $id ?> .gn-form select.form-control:focus {
  border-color: #66afe9;
  outline: 0;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
  box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
}

/* The Button */
<?= $id ?>  .gn-btn,
<?= $id ?>  .g-recaptcha,
<?= $id ?>  .gn-form input[type="submit"] {
  display: inline-block;
  padding: 6px 12px;
  margin-bottom: 0;
  font-size: 14px;
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
  color: <?= $view->getValueOf('gn-modal-submit-text-color') ?>;
  background: <?= $view->getValueOf('gn-modal-submit-background-color') ?>;
}
<?= $id ?> .gn-btn label { color: <?= $view->getValueOf('gn-modal-submit-text-color') ?>; }

/* Start Custom CSS */

@import url('https://fonts.googleapis.com/css?family=Lato:400,500');
 <?= $id ?>.genooModal {
  width: 700px;
  margin-left: -350px;
  text-align: left;
  font-family: Lato, Arial, sans-serif;
  font-weight: 500;
  border-radius: 4px;
}

<?= $id ?>.genooModal .relative {
  /*height: 100%;*/
  display: block;
}

<?= $id ?> .genooForm{
  box-sizing: border-box;
  width: 200px;
  float: right;
  background: #ffffff;
  border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
}

body <?= $id ?> *:not(.gn-btn) > label,
<?= $id ?> .checkbox-control span {
  color: <?= $view->getValueOf('gn-modal-text-color') ?>;
}

@media screen and (max-width: 600px) {
   <?= $id ?>.genooModal {
    width: calc( 100% - 20px ) !important;
     margin-left: -335px;
    margin: 0 10px;
    left: 0;
  }
   <?= $id ?> .form-group {
    padding: 0 !important;
  }
   <?= $id ?> .gn-description,
  <?= $id ?> h2 {
    padding: 0 5px !important;
  }

  <?= $id ?> .genooForm{
    box-sizing: border-box;
    width: 100%;
    border-radius: 4px;
  }
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
  <?= $id ?> h2 {
    color: #000000 !important;
    font-size: 24px;
    font-weight: 500;
    margin: 0;
  }

  <?= $id ?> .gn-description {
   color: #000000 !important;
   font-size: 14px;
 }

 <?= $id ?> .genooForm__text {
   display: inline-block;
   width: calc(100% - 200px);
   padding: 20px;
   box-sizing: border-box;
 }

/* abc-def */

<?= $id ?>.genooModal {
 background: #fff !important;
}

<?= $id ?> .relative {
 text-align: left;
 display: block;
 height: <?= $view->getValueOf('gn-modal-height'); ?>px;
}

<?= $id ?> .genooForm__text {
 padding-right: 40px;
}

<?= $id ?> .genooForm{
 display: inline-block;
 overflow: hidden;
 float: right;
 width: 194px;
 height: 100%;
 max-height: 100% !important;
 background: <?= $view->getValueOf('gn-modal-background-color') ?>;
}
<?= $id ?> .genooForm .genooGuts {
 display: inline-block;
 position: absolute;;
 top: 50% !important;
 transform: translateY(-50%);
 left: 0;
 padding: 20px;
}
<?= $id ?> .genooForm {
    overflow: hidden;
    display: block;
    position: relative;
}
@media screen and (max-width: 700px) {
  <?= $id ?> .genooForm__text {
    width: 100% !important;
    text-align: center !important;
  }
  <?= $id ?> .genooForm,
  <?= $id ?> .genooForm {
    position: relative;
    width: 100%;
    float: left;
    clear: both;
  }
  <?= $id ?>.gn-modal-result-fail .genooForm,
  <?= $id ?>.gn-modal-result-success .genooForm { padding: 0; }
  <?= $id ?>.gn-modal-result-fail .genooGuts,
  <?= $id ?>.gn-modal-result-success .genooGuts { width: 100%; padding: 0; text-align: center; } { color: <?= $view->getValueOf('gn-modal-text-color') ?>; }
  <?= $id ?>.gn-modal-result-fail .genooGuts #genooMsg { padding-top: 25px; }
  <?= $id ?>.gn-modal-result-fail .genooGuts #genooMsg strong,
  <?= $id ?>.gn-modal-result-success .genooGuts #genooMsg strong { color: <?= $view->getValueOf('gn-modal-text-color') ?>; }
  <?= $id ?>.gn-modal-result-success .genooForm__text { display: none; }
  <?= $id ?>.gn-modal-result-fail #gn-modal-title { display: block !important; }
}

[id^="on-step-2"]{display: none;}[id^="on-step-2"]:not(:checked) ~ .gn-tier-0 {display: none !important;}[id^="on-step-2"]:checked ~ .gn-step-1-overlay {display: none !important;}[for="on-step-2"] {width: 100%;}
</style>
