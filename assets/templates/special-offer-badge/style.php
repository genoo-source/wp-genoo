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
<?= $id ?> .genooForm {
  width: 100%;
  margin-right: 20px;
}
<?= $id ?> .genooGuts {
  padding: 0 !important;
}
<?= $id ?> form,
<?= $id ?> form * {
  box-sizing: border-box;
  font-family: Montserrat, Arial, helvetica, sans-serif;
  font-size: 15px;
  line-height: 1.42857143;
}

<?= $id ?> form .form-group {
  margin-bottom: 14px;
  display: inline-block;
  width: 100%;
}

<?= $id ?> form label.control-label {
  display: inline-block;
  max-width: 100%;
  margin-bottom: 5px;
  font-weight: 700;
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
<?= $id ?> form select,
<?= $id ?> form textarea{
  display: block;
  width: 100%;
  height: 34px;
  padding: 22px 12px;
  font-size: 14px;
  line-height: 1.42857143;
  color: #555;
  background-color: #f3f3f3;
  background-image: none;
  border: 1px solid #ccc;
  border-radius: 4px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
  -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}
<?= $id ?> form textarea { height: auto; }

<?= $id ?> form .gn-field-container {
  margin: 0 2px;
}

<?= $id ?> form input:focus,
<?= $id ?> form select:focus {
  border-color: #66afe9;
  outline: 0;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
  box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
}

/* The Button */
<?= $id ?>  .gn-btn,
<?= $id ?>  .g-recaptcha,
<?= $id ?>  form input[type="submit"] {
  display: inline-block;
  padding: 12px;
  margin-bottom: 0;
  font-size: 20px;
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

/* Start Custom CSS */

@import url('https://fonts.googleapis.com/css?family=Dosis:400,500');
 <?= $id ?>.genooModal {
  width: 600px;
  margin-left: -300px;
  font-family: Montserrat, Arial, helvetica, sans-serif;
  text-align: center;
  font-weight: 500;
  /*@editable*/background-position: -10px -10px !important;
  border-radius: 4px;
}

<?= $id ?>.genooModal .relative {
  height: 100%;
  display: block;
}

<?= $id ?> .genooForm{
  box-sizing: border-box;
  float: right;
  background: #ffffff;
  border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
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
  <?= $id ?> form::before {
    border-width: 0 !important;
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
    font-size: 32px;
    font-weight: 500;
    margin: 0;
  }
   <?= $id ?> .gn-description {
    color: #000000 !important;
    font-size: 16px;
    font-weight: 300;
    font-family: arial, helvetica, sans-serif;
  }

  <?= $id ?>  .top-left-featured-image {display: none;}
  @media screen and (min-width: 800px) {
    <?= $id ?>.genooModal {
      width: 700px !important;
      border-radius: 12px;
    }

    <?= $id ?> .genooForm {
      width: 400px;
      margin: 20px 55px !important;
      padding-right: 0 !important;
      box-sizing: content-box;
      text-align: left;
    }
    <?= $id ?> .top-left-featured-image {
      display: block;
      border-radius: 200px;
      position: absolute;
      height: 265px;
      width: 265px;
      left: -35px;
      top: -35px;
    }

    <?= $id ?> .genooModalClose {
      background: transparent;
      color: #000;
      top: 0;
      right: 0;
      font-size: 16px;
      margin: 10px;
    }
  }

  .gn-btn label {
    float: left;
  }

  [id^="on-step-2"]{display: none;}[id^="on-step-2"]:not(:checked) ~ .gn-tier-0 {display: none !important;}[id^="on-step-2"]:checked ~ .gn-step-1-overlay {display: none !important;}[for="on-step-2"] {width: 100%;}
</style>
