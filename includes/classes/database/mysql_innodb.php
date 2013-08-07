<?php
/*
  $Id: mysql_innodb.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('mysql.php');

  class osC_Database_mysql_innodb extends osC_Database_mysql {
    var $use_transactions = true,
        $use_fulltext = false,
        $use_fulltext_boolean = false;

    function osC_Database_mysql_innodb($server, $username, $password) {
      $this->osC_Database_mysql($server, $username, $password);
    }

    function prepareSearch($columns) {
      $search_sql = '(';

      foreach ($columns as $column) {
        $search_sql .= $column . ' like :keyword or ';
      }

      $search_sql = substr($search_sql, 0, -4) . ')';

      return $search_sql;
    }
  }
?>
