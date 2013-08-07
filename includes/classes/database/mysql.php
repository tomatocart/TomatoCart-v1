<?php
/*
  $Id: mysql.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Database_mysql extends osC_Database {
    var $use_transactions = false,
        $use_fulltext = false,
        $use_fulltext_boolean = false,
        $sql_parse_string = 'mysql_escape_string',
        $sql_parse_string_with_connection_handler = false;

    function osC_Database_mysql($server, $username, $password) {
      $this->server = $server;
      $this->username = $username;
      $this->password = $password;

      if (function_exists('mysql_real_escape_string')) {
        $this->sql_parse_string = 'mysql_real_escape_string';
        $this->sql_parse_string_with_connection_handler = true;
      }

      if ($this->is_connected === false) {
        $this->connect();
      }
    }

    function connect() {
      if (defined('USE_PCONNECT') && (USE_PCONNECT == 'true')) {
        $connect_function = 'mysql_pconnect';
      } else {
        $connect_function = 'mysql_connect';
      }

      if ($this->link = @$connect_function($this->server, $this->username, $this->password)) {
        $this->setConnected(true);
        $this->resetSQLMode();
        $this->setUTF();
        
        return true;
      } else {
        $this->setError(mysql_error(), mysql_errno());

        return false;
      }
    }

    function disconnect() {
      if ($this->isConnected()) {
        if (@mysql_close($this->link)) {
          return true;
        } else {
          return false;
        }
      } else {
        return true;
      }
    }

    function selectDatabase($database) {
      if ($this->isConnected()) {
        if (@mysql_select_db($database, $this->link)) {
          return true;
        } else {
          $this->setError(mysql_error($this->link), mysql_errno($this->link));

          return false;
        }
      } else {
        return false;
      }
    }
    
    function resetSQLMode() {
      @mysql_query("SET @@sql_mode = ''");
    }

    function setUTF() {
      if ( function_exists( 'mysql_set_charset' ) ) {
        @mysql_set_charset('utf8', $this->link);
      } else {
        @mysql_query( "SET NAMES 'utf8'", $this->link );
      }
    }
    
    function parseString($value) {
      if ($this->sql_parse_string_with_connection_handler === true) {
        return call_user_func_array($this->sql_parse_string, array($value, $this->link));
      } else {
        return call_user_func_array($this->sql_parse_string, array($value));
      }
    }

    function simpleQuery($query, $debug = false) {
      global $messageStack, $osC_Services;

      if ($this->isConnected()) {
        $this->number_of_queries++;

        if ( ($debug === false) && ($this->debug === true) ) {
          $debug = true;
        }

        if (isset($osC_Services) && $osC_Services->isStarted('debug')) {
          if ( ($debug === false) && (SERVICE_DEBUG_OUTPUT_DB_QUERIES == '1') ) {
            $debug = true;
          }

          if (!osc_empty(SERVICE_DEBUG_EXECUTION_TIME_LOG) && (SERVICE_DEBUG_LOG_DB_QUERIES == '1')) {
            @error_log('QUERY ' . $query . "\n", 3, SERVICE_DEBUG_EXECUTION_TIME_LOG);
          }
        } elseif ($debug === true) {
          $debug = false;
        }

        if ($debug === true) {
          $time_start = $this->getMicroTime();
        }

        $resource = @mysql_query($query, $this->link);

        if ($debug === true) {
          $time_end = $this->getMicroTime();

          $query_time = number_format($time_end - $time_start, 5);

          if ($this->debug === true) {
            $this->time_of_queries += $query_time;
          }

          echo '<div style="font-family: Verdana, Arial, sans-serif; font-size: 7px; font-weight: bold;">[<a href="#query' . $this->number_of_queries . '">#' . $this->number_of_queries . '</a>]</div>';

          $messageStack->add('debug', '<a name=\'query' . $this->number_of_queries . '\'></a>[#' . $this->number_of_queries . ' - ' . $query_time . 's] ' . $query, 'warning');
        }

        if ($resource !== false) {
          $this->error = false;
          $this->error_number = null;
          $this->error_query = null;

          return $resource;
        } else {
          $this->setError(mysql_error($this->link), mysql_errno($this->link), $query);

          return false;
        }
      } else {
        return false;
      }
    }

    function dataSeek($row_number, $resource) {
      return @mysql_data_seek($resource, $row_number);
    }

    function randomQuery($query) {
      $query .= ' order by rand() limit 1';

      return $this->simpleQuery($query);
    }

    function randomQueryMulti($query) {
      $resource = $this->simpleQuery($query);

      $num_rows = $this->numberOfRows($resource);

      if ($num_rows > 0) {
        $random_row = osc_rand(0, ($num_rows - 1));

        $this->dataSeek($random_row, $resource);

        return $resource;
      } else {
        return false;
      }
    }

    function next($resource) {
      return @mysql_fetch_assoc($resource);
    }

    function freeResult($resource) {
      if (@mysql_free_result($resource)) {
        return true;
      } else {
        $this->setError('Resource \'osC_Database->' . $resource . '\' could not be freed.');

        return false;
      }
    }

    function nextID() {
      if ( is_numeric($this->nextID) ) {
        $id = $this->nextID;
        $this->nextID = null;

        return $id;
      } elseif ($id = @mysql_insert_id($this->link)) {
        return $id;
      } else {
        $this->setError(mysql_error($this->link), mysql_errno($this->link));

        return false;
      }
    }

    function numberOfRows($resource) {
      return @mysql_num_rows($resource);
    }

    function affectedRows() {
      return mysql_affected_rows($this->link);
    }

    function startTransaction() {
      $this->logging_transaction = true;

      if ($this->use_transactions === true) {
        return $this->simpleQuery('start transaction');
      }

      return false;
    }

    function commitTransaction() {
      if ($this->logging_transaction === true) {
        $this->logging_transaction = false;
        $this->logging_transaction_action = false;
      }

      if ($this->use_transactions === true) {
        return $this->simpleQuery('commit');
      }

      return false;
    }

    function rollbackTransaction() {
      if ($this->logging_transaction === true) {
        $this->logging_transaction = false;
        $this->logging_transaction_action = false;
      }

      if ($this->use_transactions === true) {
        return $this->simpleQuery('rollback');
      }

      return false;
    }

    function setBatchLimit($sql_query, $from, $maximum_rows) {
      return $sql_query . ' limit ' . $from . ', ' . $maximum_rows;
    }

    function getBatchSize($sql_query, $select_field = '*') {
      if (strpos($sql_query, 'SQL_CALC_FOUND_ROWS') !== false) {
        $bb = $this->query('select found_rows() as total');
      } else {
        $total_query = substr($sql_query, 0, strpos($sql_query, ' limit '));

        $pos_to = strlen($total_query);
        $pos_from = strpos($total_query, ' from ');

        if (($pos_group_by = strpos($total_query, ' group by ', $pos_from)) !== false) {
          if ($pos_group_by < $pos_to) {
            $pos_to = $pos_group_by;
          }
        }

        if (($pos_having = strpos($total_query, ' having ', $pos_from)) !== false) {
          if ($pos_having < $pos_to) {
            $pos_to = $pos_having;
          }
        }

        if (($pos_order_by = strpos($total_query, ' order by ', $pos_from)) !== false) {
          if ($pos_order_by < $pos_to) {
            $pos_to = $pos_order_by;
          }
        }

        $bb = $this->query('select count(' . $select_field . ') as total ' . substr($total_query, $pos_from, ($pos_to - $pos_from)));
      }

      return $bb->value('total');
    }

    function prepareSearch($columns) {
      if ($this->use_fulltext === true) {
        return 'match (' . implode(', ', $columns) . ') against (:keywords' . (($this->use_fulltext_boolean === true) ? ' in boolean mode' : '') . ')';
      } else {
        $search_sql = '(';

        foreach ($columns as $column) {
          $search_sql .= $column . ' like :keyword or ';
        }

        $search_sql = substr($search_sql, 0, -4) . ')';

        return $search_sql;
      }
    }
  }
?>
