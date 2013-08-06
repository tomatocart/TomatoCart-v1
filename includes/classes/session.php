<?php
/*
  $Id: session.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Session {

/* Private variables */
    var $_cookie_parameters,
        $_is_started = false,
        $_id,
        $_name,
        $_save_path;

// class constructor
    function osC_Session($name = 'sid') {
      $this->setName($name);
      $this->setSavePath(DIR_FS_WORK);
      $this->setCookieParameters();

      if (STORE_SESSIONS == 'mysql') {
        session_set_save_handler(array(&$this, '_open'),
                                 array(&$this, '_close'),
                                 array(&$this, '_read'),
                                 array(&$this, '_write'),
                                 array(&$this, '_destroy'),
                                 array(&$this, '_gc'));

        register_shutdown_function('session_write_close');
      }
    }

// class methods
    function start() {
      $sane_session_id = true;

      if (isset($_GET[$this->_name]) && (empty($_GET[$this->_name]) || (ctype_alnum($_GET[$this->_name]) === false))) {
        $sane_session_id = false;
      } elseif (isset($_POST[$this->_name]) && (empty($_POST[$this->_name]) || (ctype_alnum($_POST[$this->_name]) === false))) {
        $sane_session_id = false;
      } elseif (isset($_COOKIE[$this->_name]) && (empty($_COOKIE[$this->_name]) || (ctype_alnum($_COOKIE[$this->_name]) === false))) {
        $sane_session_id = false;
      }

      if ($sane_session_id === false) {
        if (isset($_COOKIE[$this->_name])) {
          setcookie($this->getName(), '', time()-42000, $this->getCookieParameters('path'), $this->getCookieParameters('domain'));
        }

        osc_redirect(osc_href_link(FILENAME_DEFAULT, null, 'NONSSL', false));
      } elseif (session_start()) {
        $this->setStarted(true);
        $this->setID();

        return true;
      }

      return false;
    }

    function hasStarted() {
      return $this->_is_started;
    }

    function close() {
      return session_write_close();
    }

    function destroy() {
      if (isset($_COOKIE[$this->_name])) {
        unset($_COOKIE[$this->_name]);
        
        setcookie($this->getName(), '', time()-42000, $this->getCookieParameters('path'), $this->getCookieParameters('domain'));
      }
      
      if (STORE_SESSIONS == '') {
        if (file_exists($this->_save_path . $this->_id)) {
          @unlink($this->_save_path . $this->_id);
        }
      }

      return session_destroy();
    }
    
    function recreate() {
      $session_backup = $_SESSION;

      $this->destroy();

      $this->osC_Session($this->getName());

      $this->start();
      
      session_regenerate_id(true);

      $_SESSION = $session_backup;

      unset($session_backup);
    }

    function getSavePath() {
      return $this->_save_path;
    }

    function getID() {
      return $this->_id;
    }

    function getName() {
      return $this->_name;
    }

    function setName($name) {
      session_name($name);

      $this->_name = session_name();
    }

    function setID() {
      $this->_id = session_id();
    }

    function setSavePath($path) {
      if (substr($path, -1) == '/') {
        $path = substr($path, 0, -1);
      }

      session_save_path($path);

      $this->_save_path = session_save_path();
    }

    function setStarted($state) {
      if ($state === true) {
        $this->_is_started = true;
      } else {
        $this->_is_started = false;
      }
    }

    function setCookieParameters($lifetime = 0, $path = false, $domain = false, $secure = false) {
      global $request_type;

      if ($path === false) {
        $path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);
      }

      if ($domain === false) {
        $domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $domain : false;
      }

      return session_set_cookie_params($lifetime, $path, $domain, $secure);
    }

    function getCookieParameters($key = '') {
      if (isset($this->_cookie_parameters) === false) {
        $this->_cookie_parameters = session_get_cookie_params();
      }

      if (isset($this->_cookie_parameters[$key])) {
        return $this->_cookie_parameters[$key];
      }

      return $this->_cookie_parameters;
    }

    function _open() {
      return true;
    }

    function _close() {
      return true;
    }

    function _read($key) {
      global $osC_Database;

      $Qsession = $osC_Database->query('select value from :table_sessions where sesskey = :sesskey and expiry > :expiry');
      $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qsession->bindValue(':sesskey', $key);
      $Qsession->bindRaw(':expiry', time());
      $Qsession->execute();

      if ($Qsession->numberOfRows() > 0) {
        $value = $Qsession->value('value');

        $Qsession->freeResult();

        return $value;
      }

      return false;
    }

    function _write($key, $value) {
      global $osC_Database;

      if (defined('SERVICE_SESSION_MAX_LIFETIME') && ((int)SERVICE_SESSION_MAX_LIFETIME > 0))
      {
        $expiry = time() + (int)SERVICE_SESSION_MAX_LIFETIME * 60;
      }
      else
      {
        if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
          $SESS_LIFE = 1440;
        }
  
        $expiry = time() + $SESS_LIFE;
      }
     
      $Qsession = $osC_Database->query('select count(*) as total from :table_sessions where sesskey = :sesskey');
      $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qsession->bindValue(':sesskey', $key);
      $Qsession->execute();

      if ($Qsession->valueInt('total') > 0) {
        $Qsession = $osC_Database->query('update :table_sessions set expiry = :expiry, value = :value where sesskey = :sesskey');
      } else {
        $Qsession = $osC_Database->query('insert into :table_sessions values (:sesskey, :expiry, :value)');
      }
      $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qsession->bindValue(':sesskey', $key);
      $Qsession->bindValue(':expiry', $expiry);
      $Qsession->bindValue(':value', $value);

      if ($Qsession->execute()) {
        $write = true;
      } else {
        $write = false;
      }

      $Qsession->freeResult();

      return $write;
    }

    function _destroy($key) {
      global $osC_Database;

      $Qsession = $osC_Database->query('delete from :table_sessions where sesskey = :sesskey');
      $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qsession->bindValue(':sesskey', $key);
      $Qsession->execute();

      $Qsession->freeResult();
    }

    function _gc($maxlifetime) {
      global $osC_Database;

      $Qsession = $osC_Database->query('delete from :table_sessions where expiry < :expiry');
      $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
      $Qsession->bindValue(':expiry', time());
      $Qsession->execute();

      $Qsession->freeResult();
    }
  }
?>
