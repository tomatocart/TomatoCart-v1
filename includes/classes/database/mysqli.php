<?php
/*
  $Id: mysqli.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('mysql.php');

  class osC_Database_mysqli extends osC_Database_mysql {
    var $use_transactions = true;

    function osC_Database_mysqli($server, $username, $password) {
      $this->server = $server;
      $this->username = $username;
      $this->password = $password;

      if ($this->is_connected === false) {
        $this->connect();
      }
    }

    function connect() {
      if ($this->link = @mysqli_connect($this->server, $this->username, $this->password)) {
        $this->setConnected(true);
        $this->resetSQLMode();
        $this->setUTF();

        return true;
      } else {
        $this->setError(mysqli_connect_error(), mysqli_connect_errno());

        return false;
      }
    }

    function disconnect() {
      if ($this->isConnected()) {
        if (@mysqli_close($this->link)) {
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
        if (@mysqli_select_db($this->link, $database)) {
          return true;
        } else {
          $this->setError(mysqli_error($this->link), mysqli_errno($this->link));

          return false;
        }
      } else {
        return false;
      }
    }
      
    function resetSQLMode() {
      @mysqli_query( $this->link, "SET @@sql_mode = ''" ); 
    }
    
    function setUTF() {
      if ( function_exists( 'mysql_set_charset' ) ) {
        @mysqli_set_charset($this->link, 'utf8');
      } else {
        @mysqli_query( $this->link, "SET NAMES 'utf8'" );
      }
    }
  
    function parseString($value) {
      return mysqli_real_escape_string($this->link, $value);
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

        $resource = @mysqli_query($this->link, $query);

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
          $this->setError(mysqli_error($this->link), mysqli_errno($this->link), $query);

          return false;
        }
      } else {
        return false;
      }
    }

    function dataSeek($row_number, $resource) {
      return @mysqli_data_seek($resource, $row_number);
    }

    function next($resource) {
      return @mysqli_fetch_assoc($resource);
    }

    function freeResult($resource) {
      return @mysqli_free_result($resource);
    }

    function nextID() {
      if ( is_numeric($this->nextID) ) {
        $id = $this->nextID;
        $this->nextID = null;

        return $id;
      } elseif ($id = @mysqli_insert_id($this->link)) {
        return $id;
      } else {
        $this->setError(mysqli_error($this->link), mysqli_errno($this->link));

        return false;
      }
    }

    function numberOfRows($resource) {
      return @mysqli_num_rows($resource);
    }

    function affectedRows() {
      return @mysqli_affected_rows($this->link);
    }

    function startTransaction() {
      $this->logging_transaction = true;

      if ($this->use_transactions === true) {
        return @mysqli_autocommit($this->link, false);
      }

      return false;
    }

    function commitTransaction() {
      if ($this->logging_transaction === true) {
        $this->logging_transaction = false;
        $this->logging_transaction_action = false;
      }

      if ($this->use_transactions === true) {
        $result = @mysqli_commit($this->link);

        @mysqli_autocommit($this->link, true);

        return $result;
      }

      return false;
    }

    function rollbackTransaction() {
      if ($this->logging_transaction === true) {
        $this->logging_transaction = false;
        $this->logging_transaction_action = false;
      }

      if ($this->use_transactions === true) {
        $result = @mysqli_rollback($this->link);

        @mysqli_autocommit($this->link, true);

        return $result;
      }

      return false;
    }
  }
?>
