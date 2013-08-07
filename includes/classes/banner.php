<?php
/*
  $Id: banner.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Banner {

/* Public variables */
    var $show_duplicates_in_group = false;

/* Private variables */
    var $_exists_id,
        $_shown_ids = array();

/* Class constructor */

    function osC_Banner() {
      if (SERVICE_BANNER_SHOW_DUPLICATE == 'True') {
        $this->show_duplicates_in_group = true;
      }
    }

/* Public methods */

    function activate($id) {
      $this->_setStatus($id, true);
    }

    function activateAll() {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select banners_id, date_scheduled from :table_banners where date_scheduled != ""');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->execute();

      if ($Qbanner->numberOfRows() > 0) {
        while ($Qbanner->next()) {
          if (osC_DateTime::getNow() >= $Qbanner->value('date_scheduled')) {
            $this->activate($Qbanner->valueInt('banners_id'));
          }
        }
      }

      $Qbanner->freeResult();
    }

    function expire($id) {
      $this->_setStatus($id, false);
    }

    function expireAll() {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select b.banners_id, b.expires_date, b.expires_impressions, sum(bh.banners_shown) as banners_shown from :table_banners b, :table_banners_history bh where b.status = 1 and b.banners_id = bh.banners_id group by b.banners_id');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qbanner->execute();

      if ($Qbanner->numberOfRows() > 0) {
        while ($Qbanner->next()) {
          if (!osc_empty($Qbanner->value('expires_date'))) {
            if (osC_DateTime::getNow() >= $Qbanner->value('expires_date')) {
              $this->expire($Qbanner->valueInt('banners_id'));
            }
          } elseif (!osc_empty($Qbanner->valueInt('expires_impressions'))) {
            if ( ($Qbanner->valueInt('expires_impressions') > 0) && ($Qbanner->valueInt('banners_shown') >= $Qbanner->valueInt('expires_impressions')) ) {
              $this->expire($Qbanner->valueInt('banners_id'));
            }
          }
        }
      }

      $Qbanner->freeResult();
    }

    function isActive($id) {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select banners_id from :table_banners where status = 1 and banners_id = :banners_id');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      if ($Qbanner->numberOfRows() > 0) {
        return true;
      }

      return false;
    }

    function exists($group) {
      global $osC_Database;

      $Qbanner = $osC_Database->query('select banners_id from :table_banners where status = 1 and banners_group = :banners_group');

      if ( ($this->show_duplicates_in_group === false) && (sizeof($this->_shown_ids) > 0) ) {
        $Qbanner->appendQuery('and banners_id not in (:banner_ids)');
        $Qbanner->bindRaw(':banner_ids', implode(',', $this->_shown_ids));
      }

      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindValue(':banners_group', $group);
      $Qbanner->executeRandom();

      if ($Qbanner->numberOfRows() > 0) {
        $this->_exists_id = $Qbanner->valueInt('banners_id');

        return true;
      }

      return false;
    }

    function display($id = '') {
      global $osC_Database;

      $banner_string = false;

      if (empty($id) && isset($this->_exists_id) && is_numeric($this->_exists_id)) {
        $id = $this->_exists_id;

        unset($this->_exists_id);
      }

      $Qbanner = $osC_Database->query('select * from :table_banners where banners_id = :banners_id and status = 1');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      if ($Qbanner->numberOfRows() > 0) {
        if (!osc_empty($Qbanner->value('banners_html_text'))) {
          $banner_string = $Qbanner->value('banners_html_text');
        } else {
          $banner_string = osc_link_object(osc_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $Qbanner->valueInt('banners_id')), osc_image(DIR_WS_IMAGES . $Qbanner->value('banners_image'), $Qbanner->value('banners_title')), 'target="_blank"');
        }

        $this->_updateDisplayCount($Qbanner->valueInt('banners_id'));

        if ($this->show_duplicates_in_group === false) {
          $this->_shown_ids[] = $Qbanner->valueInt('banners_id');
        }
      }

      $Qbanner->freeResult();

      return $banner_string;
    }

    function getURL($id, $increment_click = false) {
      global $osC_Database;

      $url = false;

      $Qbanner = $osC_Database->query('select banners_url from :table_banners where banners_id = :banners_id and status = 1');
      $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      if ($Qbanner->numberOfRows() > 0) {
        $url = $Qbanner->value('banners_url');

        if ($increment_click === true) {
          $this->_updateClickCount($id);
        }
      }

      $Qbanner->freeResult();

      return $url;
    }

/* Private methods */

    function _setStatus($id, $active) {
      global $osC_Database;

      if ($active === true) {
        $Qbanner = $osC_Database->query('update :table_banners set status = 1, date_status_change = now(), date_scheduled = NULL where banners_id = :banners_id');
        $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
        $Qbanner->bindInt(':banners_id', $id);
        $Qbanner->execute();
      } else {
        $Qbanner = $osC_Database->query('update :table_banners set status = 0, date_status_change = now() where banners_id = :banners_id');
        $Qbanner->bindTable(':table_banners', TABLE_BANNERS);
        $Qbanner->bindInt(':banners_id', $id);
        $Qbanner->execute();
      }

      $Qbanner->freeResult();
    }

    function _updateDisplayCount($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select count(*) as count from :table_banners_history where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
      $Qcheck->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qcheck->bindInt(':banners_id', $id);
      $Qcheck->execute();

      if ($Qcheck->valueInt('count') > 0) {
        $Qbanner = $osC_Database->query('update :table_banners_history set banners_shown = banners_shown + 1 where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
      } else {
        $Qbanner = $osC_Database->query('insert into :table_banners_history (banners_id, banners_shown, banners_history_date) values (:banners_id, 1, now())');
      }
      $Qbanner->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      $Qcheck->freeResult();
      $Qbanner->freeResult();
    }

    function _updateClickCount($id) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select count(*) as count from :table_banners_history where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
      $Qcheck->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qcheck->bindInt(':banners_id', $id);
      $Qcheck->execute();

      if ($Qcheck->valueInt('count') > 0) {
        $Qbanner = $osC_Database->query('update :table_banners_history set banners_clicked = banners_clicked + 1 where banners_id = :banners_id and date_format(banners_history_date, "%Y%m%d") = date_format(now(), "%Y%m%d")');
      } else {
        $Qbanner = $osC_Database->query('insert into :table_banners_history (banners_id, banners_clicked, banners_history_date) values (:banners_id, 1, now())');
      }
      $Qbanner->bindTable(':table_banners_history', TABLE_BANNERS_HISTORY);
      $Qbanner->bindInt(':banners_id', $id);
      $Qbanner->execute();

      $Qcheck->freeResult();
      $Qbanner->freeResult();
    }
  }
?>
