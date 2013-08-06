<?php
/*
  $Id: html_output.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * Generate an internal URL address for the catalog side
 *
 * @param string $page The page to link to
 * @param string $parameters The parameters to pass to the page (in the GET scope)
 * @param string $connection The connection type for the page (NONSSL, SSL, AUTO)
 * @param boolean $add_session_id Conditionally add the session ID to the URL
 * @param boolean $search_engine_friendly Convert the URL to be search engine friendly
 * @param boolean $use_full_address Add the full server address to the URL
 * @access public
 */

  function osc_href_link($page = null, $parameters = null, $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $use_full_address = false) {
    global $request_type, $osC_Session, $osC_Services;

    if (!in_array($connection, array('NONSSL', 'SSL', 'AUTO'))) {
      $connection = 'NONSSL';
    }

    if (!is_bool($add_session_id)) {
      $add_session_id = true;
    }

    if (!is_bool($search_engine_safe)) {
      $search_engine_safe = true;
    }

    if (!is_bool($use_full_address)) {
      $use_full_address = false;
    }

    if (($search_engine_safe === true) && ($use_full_address === false) && isset($osC_Services) && $osC_Services->isStarted('sefu')) {
      $use_full_address = true;
    }

    $link = $page;

    if (!empty($parameters)) {
      $link .= '?' . osc_output_string($parameters);
      $separator = '&';
    } else {
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) {
      $link = substr($link, 0, -1);
    }
    
// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id === true) && $osC_Session->hasStarted() && (SERVICE_SESSION_FORCE_COOKIE_USAGE == '-1') ) {
      if ( (SID !== '')) {
        $_sid = SID;
      } elseif ( (($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL === true)) || (($request_type == 'SSL') && ($connection != 'SSL')) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = $osC_Session->getName() . '=' . $osC_Session->getID();
        }
      }
    }

    if (isset($_sid)) {
      $link .= $separator . osc_output_string($_sid);
    }

    while (strstr($link, '&&')) {
      $link = str_replace('&&', '&', $link);
    }

    if ( ($search_engine_safe === true) && isset($osC_Services) && $osC_Services->isStarted('sefu')) {
      global $toC_Sefu;
      $link = $toC_Sefu->generateURL($link, $page, $parameters);
    }
    
    $link = str_replace('&', '&amp;', $link);
    
    //check the link prefix
    if ($connection == 'AUTO') {
      if ( ($request_type == 'SSL') && (ENABLE_SSL === true) ) {
        $link_prefix = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link_prefix = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } elseif ( ($connection == 'SSL') && (ENABLE_SSL === true) ) {
      if ($request_type == 'SSL') {
        $link_prefix = ($use_full_address === false) ? '' : HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link_prefix = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      }
    } else {
      if ($request_type == 'NONSSL') {
        $link_prefix = ($use_full_address === false) ? '' : HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      } else {
        $link_prefix = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    }

    return $link_prefix . $link;
  }

/**
 * Links an object with a URL address
 *
 * @param string $link The URL address to link the object to
 * @param string $object The object to set the link on
 * @param string $parameters Additional parameters for the link
 * @access public
 */

  function osc_link_object($link, $object, $parameters = null) {
    return '<a href="' . $link . '"' . (!empty($parameters) ? ' ' . $parameters : '') . '>' . $object . '</a>';
  }

/**
 * Outputs an image
 *
 * @param string $image The image filename to display
 * @param string $title The title of the image button
 * @param int $width The width of the image
 * @param int $height The height of the image
 * @param string $parameters Additional parameters for the image
 * @access public
 */

  function osc_image($image, $title = null, $width = 0, $height = 0, $parameters = null) {
    if (IMAGE_REQUIRED == '-1' || is_dir($image) || !file_exists($image) ) {
      return false;
    }

    if (!is_numeric($width)) {
      $width = 0;
    }

    if (!is_numeric($height)) {
      $height = 0;
    }

    $image = '<img src="' . osc_output_string($image) . '" alt="' . osc_output_string($title) . '"';

    if (!empty($title)) {
      $image .= ' title="' . osc_output_string($title) . '"';
    }

    if ($width > 0) {
      $image .= ' width="' . (int)$width . '"';
    }

    if ($height > 0) {
      $image .= ' height="' . (int)$height . '"';
    }

    if (!empty($parameters)) {
      $image .= ' ' . $parameters;
    }

    $image .= ' />';

    return $image;
  }

/**
 * Outputs an image submit button
 *
 * @param string $image The image filename to display
 * @param string $title The title of the image button
 * @param string $parameters Additional parameters for the image submit button
 * @access public
 */

  function osc_draw_image_submit_button($image, $title = null, $parameters = null) {
    global $osC_Language, $osC_Template;

    $default_image_file = 'includes/languages/' . $osC_Language->getCode() . '/images/buttons/' . $image;
    $image_file = 'templates/' . $_SESSION['template']['code'] . '/images/buttons/languages/' . $osC_Language->getCode() . '/' . $image;
    if(file_exists($image_file))
      $image_submit = '<input type="image" src="' . osc_output_string($image_file) . '"';
    else
      $image_submit = '<input type="image" src="' . osc_output_string($default_image_file) . '"';

    if (!empty($title)) {
      $image_submit .= ' alt="' . osc_output_string($title) . '" title="' . osc_output_string($title) . '"';
    }

    if (!empty($parameters)) {
      $image_submit .= ' ' . $parameters;
    }

    $image_submit .= ' />';

    return $image_submit;
  }

/**
 * Outputs an image button
 *
 * @param string $image The image filename to display
 * @param string $title The title of the image button
 * @param string $parameters Additional parameters for the image button
 * @access public
 */

  function osc_draw_image_button($image, $title = null, $parameters = null) {
    global $osC_Language;

    $default_image_file = 'includes/languages/' . $osC_Language->getCode() . '/images/buttons/' . $image;
    $image_file = 'templates/' . $_SESSION['template']['code'] . '/images/buttons/languages/' . $osC_Language->getCode() . '/' . $image;
    if(file_exists($image_file))
      return osc_image($image_file, $title, null, null, $parameters);
    else
      return osc_image($default_image_file, $title, null, null, $parameters);
  }

/**
 * Outputs a form input field (text/password)
 *
 * @param string $name The name and ID of the input field
 * @param string $value The default value for the input field
 * @param string $parameters Additional parameters for the input field
 * @param boolean $override Override the default value with the value found in the GET or POST scope
 * @param string $type The type of input field to use (text/password/file)
 * @access public
 */

  function osc_draw_input_field($name, $value = null, $parameters = null, $override = true, $type = 'text') {
    if (!is_bool($override)) {
      $override = true;
    }

    if ($override === true) {
      if ( strpos($name, '[') !== false ) {
        $name_string = substr($name, 0, strpos($name, '['));
        $name_key = substr($name, strpos($name, '[') + 1, strlen($name) - (strpos($name, '[') + 2));

        if ( isset($_GET[$name_string][$name_key]) ) {
          $value = $_GET[$name_string][$name_key];
        } elseif ( isset($_POST[$name_string][$name_key]) ) {
          $value = $_POST[$name_string][$name_key];
        }
      } else {
        if ( isset($_GET[$name]) ) {
          $value = $_GET[$name];
        } elseif ( isset($_POST[$name]) ) {
          $value = $_POST[$name];
        }
      }
    }

    if (!in_array($type, array('text', 'password', 'file'))) {
      $type = 'text';
    }

    $field = '<input type="' . osc_output_string($type) . '" name="' . osc_output_string($name) . '"';

    if (strpos($parameters, 'id=') === false) {
      $field .= ' id="' . osc_output_string($name) . '"';
    }

    if (trim($value) != '') {
      $field .= ' value="' . osc_output_string($value) . '"';
    }

    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= ' />';

    return $field;
  }

/**
 * Outputs a form password field
 *
 * @param string $name The name and ID of the password field
 * @param string $parameters Additional parameters for the password field
 * @access public
 */

  function osc_draw_password_field($name, $parameters = null) {
    return osc_draw_input_field($name, null, $parameters, false, 'password');
  }

/**
 * Outputs a form file upload field
 *
 * @param string $name The name and ID of the file upload field
 * @param boolean $show_max_size Show the maximum file upload size beside the field
 * @access public
 */

  function osc_draw_file_field($name, $show_max_size = false, $parameters = null) {
    global $osC_Language;

    static $upload_max_filesize;

    if (!is_bool($show_max_size)) {
      $show_max_size = false;
    }

    $field = osc_draw_input_field($name, null, $parameters, false, 'file');

    if ($show_max_size === true) {
      if (!isset($upload_max_filesize)) {
        $upload_max_filesize = @ini_get('upload_max_filesize');
      }

      if (!empty($upload_max_filesize)) {
        $field .= '&nbsp;' . sprintf($osC_Language->get('maximum_file_upload_size'), osc_output_string($upload_max_filesize));
      }
    }

    return $field;
  }

/**
 * Outputs a form selection field (checkbox/radio)
 *
 * @param string $name The name and indexed ID of the selection field
 * @param string $type The type of the selection field (checkbox/radio)
 * @param mixed $values The value of, or an array of values for, the selection field
 * @param string $default The default value for the selection field
 * @param string $parameters Additional parameters for the selection field
 * @param string $separator The separator to use between multiple options for the selection field
 * @access public
 */

  function osc_draw_selection_field($name, $type, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    if (!is_array($values)) {
      $values = array($values);
    }

    if ( strpos($name, '[') !== false ) {
      $name_string = substr($name, 0, strpos($name, '['));

      if ( isset($_GET[$name_string]) ) {
        $default = $_GET[$name_string];
      } elseif ( isset($_POST[$name_string]) ) {
        $default = $_POST[$name_string];
      }
    } else {
      if ( isset($_GET[$name]) ) {
        $default = $_GET[$name];
      } elseif ( isset($_POST[$name]) ) {
        $default = $_POST[$name];
      }
    }

    $field = '';

    $counter = 0;

    foreach ($values as $key => $value) {
      $counter++;

      if (is_array($value)) {
        $selection_value = $value['id'];
        $selection_text = '&nbsp;' . $value['text'];
      } else {
        $selection_value = $value;
        $selection_text = '';
      }

      $field .= '<input type="' . osc_output_string($type) . '" name="' . osc_output_string($name) . '"';

      if (strpos($parameters, 'id=') === false) {
        $field .= ' id="' . osc_output_string($name) . (sizeof($values) > 1 ? $counter : '') . '"';
      }

      if (trim($selection_value) != '') {
        $field .= ' value="' . osc_output_string($selection_value) . '"';
      }

      if ((is_bool($default) && $default === true) || ((is_string($default) && (trim($default) == trim($selection_value))) || (is_array($default) && in_array(trim($selection_value), $default)))) {
        $field .= ' checked="checked"';
      }

      if (!empty($parameters)) {
        $field .= ' ' . $parameters;
      }

      $field .= ' />';

      if (!empty($selection_text)) {
        $field .= '<label for="' . osc_output_string($name) . (sizeof($values) > 1 ? $counter : '') . '" class="fieldLabel">' . $selection_text . '</label>';
      }

      $field .= $separator;
    }

    if (!empty($field)) {
      $field = substr($field, 0, strlen($field)-strlen($separator));
    }

    return $field;
  }

/**
 * Outputs a form checkbox field
 *
 * @param string $name The name and indexed ID of the checkbox field
 * @param mixed $values The value of, or an array of values for, the checkbox field
 * @param string $default The default value for the checkbox field
 * @param string $parameters Additional parameters for the checkbox field
 * @param string $separator The separator to use between multiple options for the checkbox field
 * @access public
 */

  function osc_draw_checkbox_field($name, $values = null, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    return osc_draw_selection_field($name, 'checkbox', $values, $default, $parameters, $separator);
  }

/**
 * Outputs a form radio field
 *
 * @param string $name The name and indexed ID of the radio field
 * @param mixed $values The value of, or an array of values for, the radio field
 * @param string $default The default value for the radio field
 * @param string $parameters Additional parameters for the radio field
 * @param string $separator The separator to use between multiple options for the radio field
 * @access public
 */

  function osc_draw_radio_field($name, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    return osc_draw_selection_field($name, 'radio', $values, $default, $parameters, $separator);
  }

/**
 * Outputs a form textarea field
 *
 * @param string $name The name and ID of the textarea field
 * @param string $value The default value for the textarea field
 * @param int $width The width of the textarea field
 * @param int $height The height of the textarea field
 * @param string $parameters Additional parameters for the textarea field
 * @param boolean $override Override the default value with the value found in the GET or POST scope
 * @access public
 */

  function osc_draw_textarea_field($name, $value = null, $width = 60, $height = 5, $parameters = null, $override = true) {
    if (!is_bool($override)) {
      $override = true;
    }

    if ($override === true) {
      if (isset($_GET[$name])) {
        $value = $_GET[$name];
      } elseif (isset($_POST[$name])) {
        $value = $_POST[$name];
      }
    }

    if (!is_numeric($width)) {
      $width = 60;
    }

    if (!is_numeric($height)) {
      $width = 5;
    }

    $field = '<textarea name="' . osc_output_string($name) . '" cols="' . (int)$width . '" rows="' . (int)$height . '"';

    if (strpos($parameters, 'id=') === false) {
      $field .= ' id="' . osc_output_string($name) . '"';
    }

    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= '>' . osc_output_string_protected($value) . '</textarea>';

    return $field;
  }

/**
 * Outputs a form hidden field
 *
 * @param string $name The name of the hidden field
 * @param string $value The value for the hidden field
 * @param string $parameters Additional parameters for the hidden field
 * @access public
 */

  function osc_draw_hidden_field($name, $value = null, $parameters = null) {
    $field = '<input type="hidden" name="' . osc_output_string($name) . '"';

    if (trim($value) != '') {
      $field .= ' value="' . osc_output_string($value) . '"';
    }

    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= ' />';

    return $field;
  }

/**
 * Outputs a form hidden field containing the session name and ID if SID is not empty
 *
 * @access public
 */

  function osc_draw_hidden_session_id_field() {
    global $osC_Session;

    if ($osC_Session->hasStarted() && !osc_empty(SID)) {
      return osc_draw_hidden_field($osC_Session->getName(), $osC_Session->getID());
    }
  }

/**
 * Outputs a form pull down menu field
 *
 * @param string $name The name of the pull down menu field
 * @param array $values Defined values for the pull down menu field
 * @param string $default The default value for the pull down menu field
 * @param string $parameters Additional parameters for the pull down menu field
 * @access public
 */

  function osc_draw_pull_down_menu($name, $values, $default = null, $parameters = null) {
    $group = false;

    if (isset($_GET[$name])) {
      $default = $_GET[$name];
    } elseif (isset($_POST[$name])) {
      $default = $_POST[$name];
    }

    $field = '<select name="' . osc_output_string($name) . '"';

    if (strpos($parameters, 'id=') === false) {
      $field .= ' id="' . osc_output_string($name) . '"';
    }

    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= '>';

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      if (isset($values[$i]['group'])) {
        if ($group != $values[$i]['group']) {
          $group = $values[$i]['group'];

          $field .= '<optgroup label="' . osc_output_string($values[$i]['group']) . '">';
        }
      }

      $field .= '<option value="' . osc_output_string($values[$i]['id']) . '"';
//os3 bug
      if ( (!is_null($default) && (strval($default) == strval($values[$i]['id']))) || (is_array($default) && in_array($values[$i]['id'], $default)) ) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . osc_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';

      if ( ($group !== false) && (($group != $values[$i]['group']) || !isset($values[$i+1])) ) {
        $group = false;

        $field .= '</optgroup>';
      }
    }

    $field .= '</select>';

    return $field;
  }

/**
 * Outputs a label for form field elements
 *
 * @param string $text The text to use as the form field label
 * @param string $for The ID of the form field element to assign the label to
 * @param string $access_key The access key to use for the form field element
 * @param bool $required A flag to show if the form field element requires input or not
 * @access public
 */

  function osc_draw_label($text, $for, $access_key = null, $required = false) {
    if (!is_bool($required)) {
      $required = false;
    }
    
    return '<label' . (!empty($for) ? ' for="' . osc_output_string($for) . '"' : '') . (!empty($access_key) ? ' accesskey="' . osc_output_string($access_key) . '"' : '') . '>' . osc_output_string($text) . ($required === true ? '<em>*</em>' : '') . '</label>';
  }

/**
 * Outputs a form pull down menu for a date selection
 *
 * @param string $name The base name of the date pull down menu fields
 * @param array $value An array containing the year, month, and date values for the default date (year, month, date)
 * @param boolean $default_today Default to todays date if no default value is used
 * @param boolean $show_days Show the days in a pull down menu
 * @param boolean $use_month_names Show the month names in the month pull down menu
 * @param int $year_range_start The start of the years range to use for the year pull down menu
 * @param int $year_range_end The end of the years range to use for the year pull down menu
 * @access public
 */

  function osc_draw_date_pull_down_menu($name, $value = null, $default_today = true, $show_days = true, $use_month_names = true, $year_range_start = 0, $year_range_end = 1) {
    $year = date('Y');

    if (!is_bool($default_today)) {
      $default_today = true;
    }

    if (!is_bool($show_days)) {
      $show_days = true;
    }

    if (!is_bool($use_month_names)) {
      $use_month_names = true;
    }

    if (!is_numeric($year_range_start)) {
      $year_range_start = 0;
    }

    if (!is_numeric($year_range_end)) {
      $year_range_end = 1;
    }

    if (!is_array($value)) {
      $value = array();
    }

    if (!isset($value['year']) || !is_numeric($value['year']) || ($value['year'] < ($year - $year_range_start)) || ($value['year'] > ($year + $year_range_end))) {
      if ($default_today === true) {
        $value['year'] = $year;
      } else {
        $value['year'] = $year - $year_range_start;
      }
    }

    if (!isset($value['month']) || !is_numeric($value['month']) || ($value['month'] < 1) || ($value['month'] > 12)) {
      if ($default_today === true) {
        $value['month'] = date('n');
      } else {
        $value['month'] = 1;
      }
    }

    if (!isset($value['date']) || !is_numeric($value['date']) || ($value['date'] < 1) || ($value['date'] > 31)) {
      if ($default_today === true) {
        $value['date'] = date('j');
      } else {
        $value['date'] = 1;
      }
    }

    $params = '';

    $days_select_string = '';

    if ($show_days === true) {
      $params = 'onchange="updateDatePullDownMenu(this.form, \'' . $name . '\');"';

      $days_in_month = ($default_today === true) ? date('t') : 31;

      $days_array = array();
      for ($i=1; $i<=$days_in_month; $i++) {
        $days_array[] = array('id' => $i,
                              'text' => $i);
      }

      $days_select_string = osc_draw_pull_down_menu($name . '_days', $days_array, $value['date']);
    }

    $months_array = array();
    for ($i=1; $i<=12; $i++) {
      $months_array[] = array('id' => $i,
                              'text' => (($use_month_names === true) ? strftime('%B', mktime(0, 0, 0, $i, 1)) : $i));
    }

    $months_select_string = osc_draw_pull_down_menu($name . '_months', $months_array, $value['month'], $params);

    $years_array = array();
    for ($i = ($year - $year_range_start); $i <= ($year + $year_range_end); $i++) {
      $years_array[] = array('id' => $i,
                             'text' => $i);
    }

    $years_select_string = osc_draw_pull_down_menu($name . '_years', $years_array, $value['year'], $params);

    return $days_select_string . $months_select_string . $years_select_string;
  }
?>
