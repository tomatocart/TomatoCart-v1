<?php
/*
  $Id: general.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * Wrapper function for set_time_limit(), which can't be used in safe_mode
 *
 * @param int $limit The limit to set the maximium execution time to
 * @access public
 */

  function osc_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

/**
 * Redirect to a URL address
 *
 * @param string $url The URL address to redirect to
 * @access public
 */

  function osc_redirect_admin($url) {
    global $osC_Session;

    if ( (strpos($url, "\n") !== false) || (strpos($url, "\r") !== false) ) {
      $url = osc_href_link_admin(FILENAME_DEFAULT);
    }

    if (strpos($url, '&amp;') !== false) {
      $url = str_replace('&amp;', '&', $url);
    }

    header('Location: ' . $url);

    $osC_Session->close();

    exit;
  }

/**
 * Retrieve web server and database server information
 *
 * @access public
 */

  function osc_get_system_information() {
    global $osC_Database;

    $Qdb_date = $osC_Database->query('select now() as datetime');
    $Qdb_uptime = $osC_Database->query('show status like "Uptime"');

    @list($system, $host, $kernel) = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    $db_uptime = intval($Qdb_uptime->valueInt('Value') / 3600) . ':' . str_pad(intval(($Qdb_uptime->valueInt('Value') / 60) % 60), 2, '0', STR_PAD_LEFT);

    return array('date' => osC_DateTime::getShort(null, true),
                 'system' => $system,
                 'kernel' => $kernel,
                 'host' => $host,
                 'ip' => gethostbyname($host),
                 'uptime' => @exec('uptime'),
                 'http_server' => $_SERVER['SERVER_SOFTWARE'],
                 'php' => PHP_VERSION,
                 'zend' => (function_exists('zend_version') ? zend_version() : ''),
                 'db_server' => DB_SERVER,
                 'db_ip' => gethostbyname(DB_SERVER),
                 'db_version' => 'MySQL ' . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : ''),
                 'db_date' => osC_DateTime::getShort($Qdb_date->value('datetime'), true),
                 'db_uptime' => $db_uptime);
  }

/**
 * Parse file permissions to a human readable layout
 *
 * @param int $mode The file permission to parse
 * @access public
 */

  function osc_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

/*
 * Recursively remove a directory or a single file
 *
 * @param string $source The source to remove
 * @access public
 */

  function osc_remove($source) {
    global $osC_Language, $osC_MessageStack;

    if (is_dir($source)) {
      $dir = dir($source);

      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            osc_remove($source . '/' . $file);
          } else {
            $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_file_not_removable'), $source . '/' . $file), 'error');
          }
        }
      }

      $dir->close();

      if (is_writeable($source)) {
        return rmdir($source);
      } else {
        $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_directory_not_removable'), $source), 'error');
      }
    } else {
      if (is_writeable($source)) {
        return unlink($source);
      } else {
        $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_file_not_removable'), $source), 'error');
      }
    }
  }

/**
 * Return an image type that the server supports
 *
 * @access public
 */

  function osc_dynamic_image_extension() {
    static $extension;

    if (!isset($extension)) {
      if (function_exists('imagetypes')) {
        if (imagetypes() & IMG_PNG) {
          $extension = 'png';
        } elseif (imagetypes() & IMG_JPG) {
          $extension = 'jpeg';
        } elseif (imagetypes() & IMG_GIF) {
          $extension = 'gif';
        }
      } elseif (function_exists('imagepng')) {
        $extension = 'png';
      } elseif (function_exists('imagejpeg')) {
        $extension = 'jpeg';
      } elseif (function_exists('imagegif')) {
        $extension = 'gif';
      }
    }

    return $extension;
  }

/**
 * Parse a category path to avoid loops with duplicate values
 *
 * @param string $cPath The category path to parse
 * @access public
 */

  function osc_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('intval', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

/**
 * Return an array as a string value
 *
 * @param array $array The array to return as a string value
 * @param array $exclude An array of parameters to exclude from the string
 * @param string $equals The equals character to symbolize what value a parameter is defined to
 * @param string $separator The separate to use between parameters
 */

  function osc_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();

    $get_string = '';
    if (sizeof($array) > 0) {
      while (list($key, $value) = each($array)) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }

/**
 * Return a variable value from a serialized string
 *
 * @param string $serialization_data The serialized string to return values from
 * @param string $variable_name The variable to return
 * @param string $variable_type The variable type
 */

  function osc_get_serialized_variable(&$serialization_data, $variable_name, $variable_type = 'string') {
    $serialized_variable = '';

    switch ($variable_type) {
      case 'string':
        $start_position = strpos($serialization_data, $variable_name . '|s');

        $serialized_variable = substr($serialization_data, strpos($serialization_data, '|', $start_position) + 1, strpos($serialization_data, '|', $start_position) - 1);
        break;
      case 'array':
      case 'object':
        if ($variable_type == 'array') {
          $start_position = strpos($serialization_data, $variable_name . '|a');
        } else {
          $start_position = strpos($serialization_data, $variable_name . '|O');
        }

        $tag = 0;

        for ($i=$start_position, $n=sizeof($serialization_data); $i<$n; $i++) {
          if ($serialization_data[$i] == '{') {
            $tag++;
          } elseif ($serialization_data[$i] == '}') {
            $tag--;
          } elseif ($tag < 1) {
            break;
          }
        }

        $serialized_variable = substr($serialization_data, strpos($serialization_data, '|', $start_position) + 1, $i - strpos($serialization_data, '|', $start_position) - 1);
        break;
    }

    return $serialized_variable;
  }

/**
 * Call a function given in string format used by configuration set and use functions
 *
 * @param string $function The complete function to call
 * @param string $default The default value to pass to the function
 * @param string $key The key value to use for the input field
 */

  function osc_call_user_func($function, $default = null, $key = null) {
    if (strpos($function, '::') !== false) {
      $class_method = explode('::', $function);

      return call_user_func(array($class_method[0], $class_method[1]), $default, $key);
    } else {
      $function_name = $function;
      $function_parameter = '';

      if (strpos($function, '(') !== false) {
        $function_array = explode('(', $function, 2);

        $function_name = $function_array[0];
        $function_parameter = substr($function_array[1], 0, -1);
      }

      if (!function_exists($function_name)) {
        include('includes/functions/cfg_parameters/' . $function_name . '.php');
      }

      if (!empty($function_parameter)) {
        return call_user_func($function_name, $function_parameter, $default, $key);
      } else {
        return call_user_func($function_name, $default, $key);
      }
    }
  }
  
/**
 * Create watermark image
 *
 * @param string $original_image origial image
 * @param string $dest_image dest image
 * @param string $watermark_image watermark image
 * @param string $watermark_position watermark position (0: Left/Top; 1:Left/Bottom; 2:Right/Top; 3:Right/Bottom)
 * @param string $watermark_opacity watermark position
 */
  
  function toc_draw_watermark($original_image, $dest_image, $watermark_image, $watermark_position, $watermark_opacity) {
    if ( !empty($original_image) && file_exists($original_image) ){
      $original_image_info = getimagesize($original_image);
      $original_image_width = $original_image_info[0];
      $original_image_height = $original_image_info[1];
    
      switch ($original_image_info[2]) {
        case 1: $res_original_img = imagecreatefromgif($original_image); break;
        case 2: $res_original_img = imagecreatefromjpeg($original_image); break;
        case 3: $res_original_img = imagecreatefrompng($original_image); break;
      }
    }
    
    if ( !empty($watermark_image) && file_exists($watermark_image) ) {
      $watermark_image_info = getimagesize($watermark_image);
      $watermark_image_width = $watermark_image_info[0];
      $watermark_image_height = $watermark_image_info[1];

      switch ($watermark_image_info[2]) {
        case 1: $res_watermark_img = imagecreatefromgif($watermark_image); break;
        case 2: $res_watermark_img = imagecreatefromjpeg($watermark_image); break;
        case 3: $res_watermark_img = imagecreatefrompng($watermark_image); break;
      }
    }
    
    $watermark = imagecreatetruecolor($watermark_image_width, $watermark_image_height);
    $col = imagecolorallocate($watermark, 255, 255, 255);
    imagecolortransparent($watermark, $col);
    imagefilledrectangle($watermark, 0, 0, $watermark_image_width, $watermark_image_height, $col);
    imagealphablending($watermark, true);
    imageSaveAlpha($watermark, true);
    imagecopyresampled($watermark, $res_watermark_img, 0, 0, 0, 0, $watermark_image_width, $watermark_image_height, imagesx($res_watermark_img), imagesy($res_watermark_img));
  
    switch ($watermark_position) {
      case '0':
        $posX = 0;
        $posY = 0;
        break;
      case '1':
        $posX = 0;
        $posY = $original_image_height - $watermark_image_height;
        break;
      case '2':
        $posX = $original_image_width - $watermark_image_width;
        $posY = 0;
        break;
      case '3':
        $posX = $original_image_width - $watermark_image_width;
        $posY = $original_image_height - $watermark_image_height;
        break;
    }
  
    imagealphablending($res_original_img, true);
    imageSaveAlpha($res_original_img, true);
    imagecopymerge($res_original_img, $watermark, $posX, $posY, 0, 0, $watermark_image_width, $watermark_image_height, $watermark_opacity);       
    
    switch ($original_image_info[2]) {
      case 1: imagegif($res_original_img, $dest_image); break;
      case 2: imagejpeg($res_original_img, $dest_image); break;
      case 3: imagepng($res_original_img, $dest_image); break;
    }
    
    if (isset($watermark_image_info)) unset($watermark_image_info);
    if (isset($original_image_info)) unset($original_image_info);
    if (isset($generate_info)) unset($generate_info);
    if (isset($watermark)) imagedestroy($watermark);

    imagedestroy($res_original_img);
    imagedestroy($res_watermark_img);
    
    return true;
  }
  
  function osc_gd_resize($original_image, $dest_image, $dest_width, $dest_height, $force_size = '0'){
    $img_type = false;

    switch (strtolower(substr(basename($original_image), (strrpos(basename($original_image), '.')+1)))) {
      case 'jpg':
      case 'jpeg':
        if (imagetypes() & IMG_JPG) {
          $img_type = 'jpg';
        }

        break;

      case 'gif':
        if (imagetypes() & IMG_GIF) {
          $img_type = 'gif';
        }

        break;

      case 'png':
        if (imagetypes() & IMG_PNG) {
          $img_type = 'png';
        }

        break;
    }

    if ($img_type !== false) {
      list($orig_width, $orig_height) = getimagesize($original_image);

      $width  = $dest_width;
      $height = $dest_height;

      $factor = max(($orig_width / $width), ($orig_height / $height));

      if ($force_size == '1') {
        $width = $dest_width;
      } else {
          $width  = round($orig_width / $factor);
          $height = round($orig_height / $factor);
      }
      
      $im_p = @imagecreatetruecolor($dest_width, $dest_height);
      @imagealphablending($im_p, true);
      $color = @imagecolortransparent($im_p, imagecolorallocatealpha($im_p, 255, 255, 255, 127));
      @imagefill($im_p, 0, 0, $color);
      @imagesavealpha($im_p, true);

      $x = 0;
      $y = 0;

      if ($force_size == '1') {
        $width = round($orig_width * $dest_height / $orig_height);

        if ($width < $dest_width) {
          $x = floor(($dest_width - $width) / 2);
        }
      } else {
        $x = floor(($dest_width - $width) / 2);
        $y = floor(($dest_height - $height) / 2);
      }

      switch ($img_type) {
        case 'jpg':
          $im = @imagecreatefromjpeg($original_image);
          break;

        case 'gif':
          $im = @imagecreatefromgif($original_image);
          break;

        case 'png':
          $im = @imagecreatefrompng($original_image);
          break;
      }
      
      @imagecopyresampled($im_p, $im, $x, $y, 0, 0, $width, $height, $orig_width, $orig_height);

      switch ($img_type) {
        case 'jpg':
          @imagejpeg($im_p, $dest_image);
          break;

        case 'gif':
          @imagegif($im_p, $dest_image);
          break;

        case 'png':
          @imagepng($im_p, $dest_image);
          break;
      }

      @imagedestroy($im_p);
      @imagedestroy($im);

      @chmod($dest_image, 0777);
    } else {
      return false;
    }
  }
  
  function toc_mkdir($path, $permission = 0755) {
    if (!file_exists($path)) {
      toc_mkdir(dirname($path), $permission);
      mkdir($path, $permission);
    }
    if (file_exists($path)) {
       return true;   
    } else {
      return false;      
    }
  }
  
  function toc_dircopy($src, $dest, $folder_permission = 0755, $file_permission = 0644) {
    $res = false;
   
    $src = str_replace('\\', '/', $src);
    $src = str_replace('//', '/', $src);
    $dest = str_replace('\\', '/', $dest);
    $dest = str_replace('//', '/', $dest);
    
    //file copy
    if ( is_file($src) ) {
      if(is_dir($dest)) {
        if ($dest[ strlen($dest)-1 ] != '/') {
          $__dest = $dest . "/";
        }
          
        $__dest .= basename($src);
      } else {
        $__dest = $dest;
      }

      $res = copy($src, $__dest);

      chmod($__dest, $file_permission);
    } 
    //directory copy
    elseif ( is_dir($src) ) {
      if ( !is_dir($dest) ) {
        toc_mkdir($dest, $folder_permission);
        chmod($dest, $folder_permission);
      }
      
      if ( $src[strlen($src)-1] != '/') {
        $__src = $src . '/';
      } else {
        $__src = $src;
      }
      
      if ($dest[strlen($dest) - 1]!='/') {
        $__dest = $dest . '/';
      } else {
        $__dest = $dest;
      }

      $res = true;
      $handle = opendir($src);
      while ( $file = readdir($handle) ) {
        if($file != '.' && $file != '..') {
          $res = toc_dircopy($__src . $file, $__dest . $file, $folder_permission, $file_permission);
        }
      }
      
      closedir($handle);
    } else {
      $res = false;
    }

    return $res;
  }

/**
 * Generate a token to prevent cross-site request forgery
 */
  function toc_generate_token() {
    $_SESSION["token"] = md5(uniqid(rand(), true)); 
    $_SESSION['token_time'] = time();
    
    return $_SESSION["token"];
  }
  
/**
 * Verify the given token with the token in session
 */
  function toc_verify_token() {
    if ( !(isset($_SESSION["token"]) && isset($_REQUEST["token"]) && ($_SESSION["token"] == $_REQUEST["token"])) ) {
      die ("Invalid Token");
    }
  }
?>