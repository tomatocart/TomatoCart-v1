<?php
/*
  $Id: upload.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename, $message_location;

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {

      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);

      $this->set_output_messages('direct');
    }

    function exists() {
      $file = array();

      if ( is_array($this->file) ) {
        $file = $this->file;
      } elseif ( isset($_FILES[$this->file]) ) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      }

      if ( isset($file['tmp_name']) && !empty($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        return true;
      }

      return false;
    }

    function parse() {
      global $osC_Language, $osC_MessageStack;

      $file = array();

      if ( is_array($this->file) ) {
        $file = $this->file;
      } elseif ( isset($_FILES[$this->file]) ) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      }

      if ( isset($file['tmp_name']) && !empty($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            if ($this->message_location == 'direct') {
              $osC_MessageStack->add('header', $osC_Language->get('ms_error_upload_file_type_prohibited'), 'error');
            } else {
              $osC_MessageStack->add_session('header', $osC_Language->get('ms_error_upload_file_type_prohibited'), 'error');
            }

            return false;
          }
        }

        $this->set_file($file);
        $file_name = strtolower($file['name']);
        $this->set_filename($file_name);
        $this->set_tmp_filename($file['tmp_name']);

        if (!empty($this->destination)) {
          return $this->check_destination();
        } else {
          return true;
        }
      } else {
        if ($this->message_location == 'direct') {
          $osC_MessageStack->add('header', $osC_Language->get('ms_warning_upload_no_file'), 'warning');
        } else {
          $osC_MessageStack->add_session('header', $osC_Language->get('ms_warning_upload_no_file'), 'warning');
        }

        return false;
      }
    }

    function save() {
      global $osC_Language, $osC_MessageStack;

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);

        if ($this->message_location == 'direct') {
          $osC_MessageStack->add('header', $osC_Language->get('ms_success_upload_file_saved'), 'success');
        } else {
          $osC_MessageStack->add_session('header', $osC_Language->get('ms_success_upload_file_saved'), 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $osC_MessageStack->add('header', $osC_Language->get('ms_error_upload_file_not_saved'), 'error');
        } else {
          $osC_MessageStack->add_session('header', $osC_Language->get('ms_error_upload_file_not_saved'), 'error');
        }

        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (!empty($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function check_destination() {
      global $osC_Language, $osC_MessageStack;

      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_upload_destination_not_writable'), $this->destination), 'error');
          } else {
            $osC_MessageStack->add_session('header', sprintf($osC_Language->get('ms_error_upload_destination_not_writable'), $this->destination), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $osC_MessageStack->add('header', sprintf($osC_Language->get('ms_error_upload_destination_non_existant'), $this->destination), 'error');
          } else {
            $osC_MessageStack->add_session('header', sprintf($osC_Language->get('ms_error_upload_destination_non_existant'), $this->destination), 'error');
          }
        }

        return false;
      } else {
        return true;
      }
    }

    function set_output_messages($location) {
      switch ($location) {
        case 'session':
          $this->message_location = 'session';
          break;
        case 'direct':
        default:
          $this->message_location = 'direct';
          break;
      }
    }
  }
?>
