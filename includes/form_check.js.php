<?php
/*
  $Id: form_check.js.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
<script type="text/javascript"><!--
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_radio(field_name, message) {
  var isChecked = false;

  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var radio = form.elements[field_name];

    for (var i=0; i<radio.length; i++) {
      if (radio[i].checked == true) {
        isChecked = true;
        break;
      }
    }

    if (isChecked == false) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password = form.elements[field_name_1].value;
    var confirmation = form.elements[field_name_2].value;

    if (password == '' || password.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password != confirmation) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    }
  }
}

function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password_current = form.elements[field_name_1].value;
    var password_new = form.elements[field_name_2].value;
    var password_confirmation = form.elements[field_name_3].value;

    if (password_current == '' || password_current.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password_new == '' || password_new.length < field_size) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    } else if (password_new != password_confirmation) {
      error_message = error_message + "* " + message_3 + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo $GLOBALS['osC_Language']->get('js_error_already_submitted'); ?>");
    return false;
  }

  error = false;
  form = form_name;
  error_message = "<?php echo $GLOBALS['osC_Language']->get('js_error'); ?>";

<?php
  if (ACCOUNT_GENDER > 0) {
    echo '  check_radio("gender", "' . $GLOBALS['osC_Language']->get('field_customer_gender_error') . '");' . "\n";
  }
?>

  check_input("firstname", <?php echo ACCOUNT_FIRST_NAME; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_first_name_error'), ACCOUNT_FIRST_NAME); ?>");
  check_input("lastname", <?php echo ACCOUNT_LAST_NAME; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_last_name_error'), ACCOUNT_LAST_NAME); ?>");
  check_input("email_address", <?php echo ACCOUNT_EMAIL_ADDRESS; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_email_address_error'), ACCOUNT_EMAIL_ADDRESS); ?>");

<?php
  if (ACCOUNT_COMPANY > 0) {
    echo '  check_input("company", ' . ACCOUNT_COMPANY . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_company_error'), ACCOUNT_COMPANY) . '");' . "\n";
  }
?>

  check_input("street_address", <?php echo ACCOUNT_STREET_ADDRESS; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_street_address_error'), ACCOUNT_STREET_ADDRESS); ?>");

<?php
  if (ACCOUNT_SUBURB > 0) {
    echo '  check_input("suburb", ' . ACCOUNT_SUBURB . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_suburb_error'), ACCOUNT_SUBURB) . '");' . "\n";
  }

  if (ACCOUNT_POST_CODE > 0) {
    echo '  check_input("postcode", ' . ACCOUNT_POST_CODE . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_post_code_error'), ACCOUNT_POST_CODE) . '");' . "\n";
  }
?>

  check_input("city", <?php echo ACCOUNT_CITY; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_city_error'), ACCOUNT_CITY); ?>");

<?php
  if (ACCOUNT_STATE > 0) {
    echo '  check_input("state", ' . ACCOUNT_STATE . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_state_error'), ACCOUNT_STATE) . '");' . "\n";
  }
?>

  check_select("country", "", "<?php echo $GLOBALS['osC_Language']->get('field_customer_country_error'); ?>");

<?php
  if (ACCOUNT_TELEPHONE > 0) {
    echo '  check_input("telephone", ' . ACCOUNT_TELEPHONE . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_telephone_number_error'), ACCOUNT_TELEPHONE) . '");' . "\n";
  }

  if (ACCOUNT_FAX > 0) {
    echo '  check_input("fax", ' . ACCOUNT_FAX . ', "' . sprintf($GLOBALS['osC_Language']->get('field_customer_fax_number_error'), ACCOUNT_FAX) . '");' . "\n";
  }
?>

  check_password("password", "confirmation", <?php echo ACCOUNT_PASSWORD; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_password_error'), ACCOUNT_PASSWORD); ?>", "<?php echo $GLOBALS['osC_Language']->get('field_customer_password_mismatch_with_confirmation'); ?>");
  check_password_new("password_current", "password_new", "password_confirmation", <?php echo ACCOUNT_PASSWORD; ?>, "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_password_error'), ACCOUNT_PASSWORD); ?>", "<?php echo sprintf($GLOBALS['osC_Language']->get('field_customer_password_new_error'), ACCOUNT_PASSWORD); ?>", "<?php echo $GLOBALS['osC_Language']->get('field_customer_password_new_mismatch_with_confirmation_error'); ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
//--></script>
