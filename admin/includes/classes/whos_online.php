<?php
/*
  $Id: whos_online.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_WhosOnline_Admin {

    function delete($id) {
      global $osC_Session, $osC_Database;

      osC_Session_Admin::delete($id);

      $Qwho = $osC_Database->query('delete from :table_whos_online where session_id = :session_id');
      $Qwho->bindTable(':table_whos_online', TABLE_WHOS_ONLINE);
      $Qwho->bindValue(':session_id', $id);
      $Qwho->setLogging($_SESSION['module'], $id);
      $Qwho->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }

      return false;
    }

    function removeExpiredEntries($track_time) {
      global $osC_Database;

      $current_time = time();
      $expire_time = ($current_time - $track_time);

      $Qwho = $osC_Database->query('delete from :table_whos_online where time_last_click < :time_last_click');
      $Qwho->bindRaw(':table_whos_online', TABLE_WHOS_ONLINE);
      $Qwho->bindValue(':time_last_click', $expire_time);
      $Qwho->execute();
    }

    function getSessionData($session_id) {
      global $osC_Session, $osC_Database;

      $session_data = '';
      if (STORE_SESSIONS == 'mysql') {
        $Qsession = $osC_Database->query('select value from :table_sessions where sesskey = :sesskey');
        $Qsession->bindTable(':table_sessions', TABLE_SESSIONS);
        $Qsession->bindValue(':sesskey', $session_id);
        $Qsession->execute();

        $session_data = trim($Qsession->value('value'));
      } else {
        if ( file_exists($osC_Session->getSavePath() . '/sess_' . $session_id) && ( filesize($osC_Session->getSavePath() . '/sess_' . $session_id) > 0 ) ) {
          $session_data = trim(file_get_contents($osC_Session->getSavePath() . '/sess_' . $session_id));
        }
      }

      return $session_data;
    }
  }
?>
