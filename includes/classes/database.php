<?php
/*
  $Id: database.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Database {
    var $is_connected = false,
        $link,
        $error_reporting = true,
        $error = false,
        $error_number,
        $error_query,
        $server,
        $username,
        $password,
        $debug = false,
        $number_of_queries = 0,
        $time_of_queries = 0,
        $nextID = null,
        $logging_transaction = false,
        $logging_transaction_action = false;

    function &connect($server, $username, $password, $type = DB_DATABASE_CLASS) {
      require('database/' . $type . '.php');

      $class = 'osC_Database_' . $type;
      $object = new $class($server, $username, $password);

      return $object;
    }

    function setConnected($boolean) {
      if ($boolean === true) {
        $this->is_connected = true;
      } else {
        $this->is_connected = false;
      }
    }

    function isConnected() {
      if ($this->is_connected === true) {
        return true;
      } else {
        return false;
      }
    }

    function &query($query) {
      $osC_Database_Result =& new osC_Database_Result($this);
      $osC_Database_Result->setQuery($query);

      return $osC_Database_Result;
    }

    function setError($error, $error_number = '', $query = '') {
      global $messageStack;

      if ($this->error_reporting === true) {
        $this->error = $error;
        $this->error_number = $error_number;
        $this->error_query = $query;

        if (isset($messageStack)) {
          $messageStack->add('debug', $this->getError());
        }
      }
    }

    function isError() {
      if ($this->error === false) {
        return false;
      } else {
        return true;
      }
    }

    function getError() {
      if ($this->isError()) {
        $error = '';

        if (!empty($this->error_number)) {
          $error .= $this->error_number . ': ';
        }

        $error .= $this->error;

        if (!empty($this->error_query)) {
          $error .= '; ' . htmlentities($this->error_query);
        }

        return $error;
      } else {
        return false;
      }
    }

    function setErrorReporting($boolean) {
      if ($boolean === true) {
        $this->error_reporting = true;
      } else {
        $this->error_reporting = false;
      }
    }

    function setDebug($boolean) {
      if ($boolean === true) {
        $this->debug = true;
      } else {
        $this->debug = false;
      }
    }

    function importSQL($sql_file, $database, $table_prefix = -1) {
      if ($this->selectDatabase($database)) {
        if (file_exists($sql_file)) {
          $fd = fopen($sql_file, 'rb');
          $import_queries = fread($fd, filesize($sql_file));
          fclose($fd);
        } else {
          $this->setError(sprintf(ERROR_SQL_FILE_NONEXISTENT, $sql_file));

          return false;
        }

        if (!get_cfg_var('safe_mode')) {
          @set_time_limit(0);
        }

        $sql_queries = array();
        $sql_length = strlen($import_queries);
        $pos = strpos($import_queries, ';');

        for ($i=$pos; $i<$sql_length; $i++) {
// remove comments
          if ($import_queries[0] == '#') {
            $import_queries = ltrim(substr($import_queries, strpos($import_queries, "\n")));
            $sql_length = strlen($import_queries);
            $i = strpos($import_queries, ';')-1;
            continue;
          }

          if ($import_queries[($i+1)] == "\n") {
            $next = '';

            for ($j=($i+2); $j<$sql_length; $j++) {
              if (!empty($import_queries[$j])) {
                $next = substr($import_queries, $j, 6);

                if ($next[0] == '#') {
// find out where the break position is so we can remove this line (#comment line)
                  for ($k=$j; $k<$sql_length; $k++) {
                    if ($import_queries[$k] == "\n") {
                      break;
                    }
                  }

                  $query = substr($import_queries, 0, $i+1);

                  $import_queries = substr($import_queries, $k);

// join the query before the comment appeared, with the rest of the dump
                  $import_queries = $query . $import_queries;
                  $sql_length = strlen($import_queries);
                  $i = strpos($import_queries, ';')-1;
                  continue 2;
                }

                break;
              }
            }

            if (empty($next)) { // get the last insert query
              $next = 'insert';
            }

            if ((strtoupper($next) == 'DROP T') || (strtoupper($next) == 'CREATE') || (strtoupper($next) == 'INSERT')) {
              $next = '';

              $sql_query = substr($import_queries, 0, $i);

              if ($table_prefix !== -1) {
                if (strtoupper(substr($sql_query, 0, 25)) == 'DROP TABLE IF EXISTS TOC_') {
                  $sql_query = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($sql_query, 25);
                } elseif (strtoupper(substr($sql_query, 0, 17)) == 'CREATE TABLE TOC_') {
                  $sql_query = 'CREATE TABLE ' . $table_prefix . substr($sql_query, 17);
                } elseif (strtoupper(substr($sql_query, 0, 16)) == 'INSERT INTO TOC_') {
                  $sql_query = 'INSERT INTO ' . $table_prefix . substr($sql_query, 16);
                }
              }

              $sql_queries[] = trim($sql_query);

              $import_queries = ltrim(substr($import_queries, $i+1));
              $sql_length = strlen($import_queries);
              $i = strpos($import_queries, ';')-1;
            }
          }
        }

        for ($i=0, $n=sizeof($sql_queries); $i<$n; $i++) {
          $this->simpleQuery($sql_queries[$i]);

          if ($this->isError()) {
            break;
          }
        }
      }

      if ($this->isError()) {
        return false;
      } else {
        return true;
      }
    }

    function hasCreatePermission($database) {
      $db_created = false;

      if (empty($database)) {
        $this->setError(ERROR_DB_NO_DATABASE_SELECTED);

        return false;
      }

      $this->setErrorReporting(false);

      if ($this->selectDatabase($database) === false) {
        $this->setErrorReporting(true);

        if ($this->simpleQuery('create database ' . $database)) {
          $db_created = true;
        }
      }

      $this->setErrorReporting(true);

      if ($this->isError() === false) {
        if ($this->selectDatabase($database)) {
          if ($this->simpleQuery('create table osCommerceTestTable1536f ( temp_id int )')) {
            if ($db_created === true) {
              $this->simpleQuery('drop database ' . $database);
            } else {
              $this->simpleQuery('drop table osCommerceTestTable1536f');
            }
          }
        }
      }

      if ($this->isError()) {
        return false;
      } else {
        return true;
      }
    }

    function numberOfQueries() {
      return $this->number_of_queries;
    }

    function timeOfQueries() {
      return $this->time_of_queries;
    }

    function getMicroTime() {
      list($usec, $sec) = explode(' ', microtime());

      return ((float)$usec + (float)$sec);
    }
    
    function escapeString($value) {
      return $this->parseString(trim($value));
    }
  }

  class osC_Database_Result {
    var $db_class,
        $sql_query,
        $query_handler,
        $result,
        $rows,
        $affected_rows,
        $cache_key,
        $cache_expire,
        $cache_data,
        $cache_read = false,
        $debug = false,
        $batch_query = false,
        $batch_number,
        $batch_rows,
        $batch_size,
        $batch_to,
        $batch_from,
        $batch_select_field,
        $logging = false,
        $logging_module,
        $logging_module_id,
        $logging_fields = array(),
        $logging_changed = array();

    function osC_Database_Result(&$db_class) {
      $this->db_class =& $db_class;
    }

    function setQuery($query) {
      $this->sql_query = $query;
    }

    function appendQuery($query) {
      $this->sql_query .= ' ' . $query;
    }

    function getQuery() {
      return $this->sql_query;
    }

    function setDebug($boolean) {
      if ($boolean === true) {
        $this->debug = true;
      } else {
        $this->debug = false;
      }
    }

    function valueMixed($column, $type = 'string') {
      if (!isset($this->result)) {
        $this->next();
      }

      switch ($type) {
        case 'protected':
          return osc_output_string_protected($this->result[$column]);
          break;
        case 'int':
          return (int)$this->result[$column];
          break;
        case 'decimal':
          return (float)$this->result[$column];
          break;
        case 'string':
        default:
          return $this->result[$column];
      }
    }

    function value($column) {
      return $this->valueMixed($column, 'string');
    }

    function valueProtected($column) {
      return $this->valueMixed($column, 'protected');
    }

    function valueInt($column) {
      return $this->valueMixed($column, 'int');
    }

    function valueDecimal($column) {
      return $this->valueMixed($column, 'decimal');
    }

    function bindValueMixed($place_holder, $value, $type = 'string', $log = true) {
      if ($log === true) {
        $this->logging_fields[substr($place_holder, 1)] = $value;
      }

      switch ($type) {
        case 'int':
          $value = intval($value);
          break;
        case 'float':
          $value = floatval($value);
          break;
        case 'raw':
          break;
        case 'string':
        default:
          $value = "'" . $this->db_class->parseString(trim($value)) . "'";
      }

      $this->bindReplace($place_holder, $value);
    }

    function bindReplace($place_holder, $value) {
      $pos = strpos($this->sql_query, $place_holder);

      if ($pos !== false) {
        $length = strlen($place_holder);
        $character_after_place_holder = substr($this->sql_query, $pos+$length, 1);

        if (($character_after_place_holder === false) || ereg('[ ,)"]', $character_after_place_holder)) {
          $this->sql_query = substr_replace($this->sql_query, $value, $pos, $length);
        }
      }
    }

    function bindValue($place_holder, $value) {
      $this->bindValueMixed($place_holder, $value, 'string');
    }

    function bindInt($place_holder, $value) {
      $this->bindValueMixed($place_holder, $value, 'int');
    }

    function bindFloat($place_holder, $value) {
      $this->bindValueMixed($place_holder, $value, 'float');
    }

    function bindRaw($place_holder, $value) {
      $this->bindValueMixed($place_holder, $value, 'raw');
    }

    function bindTable($place_holder, $value) {
      $this->bindValueMixed($place_holder, $value, 'raw', false);
    }

    function next() {
      if ($this->cache_read === true) {
        if (!is_null($this->cache_data) && is_array($this->cache_data)) {
          list(, $this->result) = each($this->cache_data);
        } else {
          list(, $this->result) = '';
        }
      } else {
        if (!isset($this->query_handler)) {
          $this->execute();
        }

        $this->result = $this->db_class->next($this->query_handler);

        if (isset($this->cache_key)) {
          if ($this->result !== false) {
            $this->cache_data[] = $this->result;
          }
        }
      }

      return $this->result;
    }

    function freeResult() {
      global $osC_Cache;

      if ($this->cache_read === false) {
        if (eregi('^SELECT', $this->sql_query)) {
          $this->db_class->freeResult($this->query_handler);
        }

        if (isset($this->cache_key)) {
          $osC_Cache->write($this->cache_key, $this->cache_data);
        }
      }

      unset($this);
    }

    function numberOfRows() {
      if (!isset($this->rows)) {
        if (!isset($this->query_handler)) {
          $this->execute();
        }

        if (isset($this->cache_key) && ($this->cache_read === true)) {
          $this->rows = sizeof($this->cache_data);
        } else {
          $this->rows = $this->db_class->numberOfRows($this->query_handler);
        }
      }

      return $this->rows;
    }

    function affectedRows() {
      if (!isset($this->affected_rows)) {
        if (!isset($this->query_handler)) {
          $this->execute();
        }

        $this->affected_rows = $this->db_class->affectedRows();
      }

      return $this->affected_rows;
    }

    function execute() {
      global $osC_Cache;

      if (isset($this->cache_key)) {
        if ($osC_Cache->read($this->cache_key, $this->cache_expire)) {
          $this->cache_data = $osC_Cache->cached_data;

          $this->cache_read = true;
        }
      }

      if ($this->cache_read === false) {
        if ($this->logging === true) {
          $this->logging_action = substr($this->sql_query, 0, strpos($this->sql_query, ' '));

          if ($this->logging_action == 'update') {
            $db = split(' ', $this->sql_query, 3);
            $this->logging_database = $db[1];

            $test = $this->db_class->simpleQuery('select ' . implode(', ', array_keys($this->logging_fields)) . ' from ' . $this->logging_database . substr($this->sql_query, osc_strrpos_string($this->sql_query, ' where ')));

            while ($result = $this->db_class->next($test)) {
              foreach ($this->logging_fields as $key => $value) {
                if ($result[$key] != $value) {
                  $this->logging_changed[] = array('key' => $this->logging_database . '.' . $key, 'old' => $result[$key], 'new' => $value);
                }
              }
            }
          } elseif ($this->logging_action == 'insert') {
            $db = split(' ', $this->sql_query, 4);
            $this->logging_database = $db[2];

            foreach ($this->logging_fields as $key => $value) {
              $this->logging_changed[] = array('key' => $this->logging_database . '.' . $key, 'old' => '', 'new' => $value);
            }
          } elseif ($this->logging_action == 'delete') {
            $db = split(' ', $this->sql_query, 4);
            $this->logging_database = $db[2];

            $del = $this->db_class->simpleQuery('select * from ' . $this->logging_database . ' ' . $db[3]);
            while ($result = $this->db_class->next($del)) {
              foreach ($result as $key => $value) {
                $this->logging_changed[] = array('key' => $this->logging_database . '.' . $key, 'old' => $value, 'new' => '');
              }
            }
          }
        }

        $this->query_handler = $this->db_class->simpleQuery($this->sql_query, $this->debug);

        if ($this->logging === true) {
          if ($this->db_class->logging_transaction_action === false) {
            $this->db_class->logging_transaction_action = $this->logging_action;
          }

          if ($this->affectedRows($this->query_handler) > 0) {
            if (!empty($this->logging_changed)) {
              if ( ($this->logging_action == 'insert') && !is_numeric($this->logging_module_id) ) {
                $this->logging_module_id = $this->db_class->nextID();
                $this->setNextID($this->logging_module_id);
              }

              if ( class_exists('osC_AdministratorsLog') ) {
                osC_AdministratorsLog::insert($this->logging_module, $this->db_class->logging_transaction_action, $this->logging_module_id, $this->logging_action, $this->logging_changed, $this->db_class->logging_transaction);
              }
            }
          }
        }

        if ($this->batch_query === true) {
          $this->batch_size = $this->db_class->getBatchSize($this->sql_query, $this->batch_select_field);

          $this->batch_to = ($this->batch_rows * $this->batch_number);
          if ($this->batch_to > $this->batch_size) {
            $this->batch_to = $this->batch_size;
          }

          $this->batch_from = ($this->batch_rows * ($this->batch_number - 1));
          if ($this->batch_to == 0) {
            $this->batch_from = 0;
          } else {
            $this->batch_from++;
          }
        }

        return $this->query_handler;
      }
    }

    function executeRandom() {
      return $this->query_handler = $this->db_class->randomQuery($this->sql_query);
    }

    function executeRandomMulti() {
      return $this->query_handler = $this->db_class->randomQueryMulti($this->sql_query);
    }

    function setCache($key, $expire = 0) {
      $this->cache_key = $key;
      $this->cache_expire = $expire;
    }

    function setLogging($module, $id = null) {
      if (defined('ENABLE_ADMINISTRATORS_LOG') && ENABLE_ADMINISTRATORS_LOG == '1') {
      	$this->logging = true;
      }
      
      $this->logging_module = $module;
      $this->logging_module_id = $id;
    }

    function setNextID($id) {
      $this->db_class->nextID = $id;
    }

    function toArray() {
      if (!isset($this->result)) {
        $this->next();
      }

      return $this->result;
    }

    function prepareSearch($keywords, $columns, $embedded = false) {
      if ($embedded === true) {
        $this->sql_query .= ' and ';
      }

      $keywords_array = explode(' ', $keywords);

      if ($this->db_class->use_fulltext === true) {
        if ($this->db_class->use_fulltext_boolean === true) {
          $keywords = '';

          foreach ($keywords_array as $keyword) {
            if ((substr($keyword, 0, 1) != '-') && (substr($keyword, 0, 1) != '+')) {
              $keywords .= '+';
            }

            $keywords .= $keyword . ' ';
          }

          $keywords = substr($keywords, 0, -1);
        }

        $this->sql_query .= $this->db_class->prepareSearch($columns);
        $this->bindValue(':keywords', $keywords);
      } else {
        foreach ($keywords_array as $keyword) {
          $this->sql_query .= $this->db_class->prepareSearch($columns);

          foreach ($columns as $column) {
            $this->bindValue(':keyword', '%' . $keyword . '%');
          }

          $this->sql_query .= ' and ';
        }

        $this->sql_query = substr($this->sql_query, 0, -5);
      }
    }

    function setBatchLimit($batch_number = 1, $maximum_rows = 20, $select_field = '') {
      $this->batch_query = true;
      $this->batch_number = (is_numeric($batch_number) ? $batch_number : 1);
      $this->batch_rows = $maximum_rows;
      $this->batch_select_field = (empty($select_field) ? '*' : $select_field);

      $from = max(($this->batch_number * $maximum_rows) - $maximum_rows, 0);

      $this->sql_query = $this->db_class->setBatchLimit($this->sql_query, $from, $maximum_rows);

    }
    
    function setExtBatchLimit($start, $maximum_rows = 20, $select_field = '') {
      $page = intval($start / $maximum_rows) + 1;
      
      $this->setBatchLimit($page, $maximum_rows, $select_field);
    }

    function getBatchSize() {
      return $this->batch_size;
    }

    function isBatchQuery() {
      if ($this->batch_query === true) {
        return true;
      }

      return false;
    }

    function getBatchTotalPages($text) {
      return sprintf($text, $this->batch_from, $this->batch_to, $this->batch_size);
    }

    function getBatchPageLinks($batch_keyword = 'page', $parameters = '', $with_pull_down_menu = true) {
    	$string = '';
    	
    	if ($with_pull_down_menu === false) {
				$string .= '<div class="pagination">';
      }

      if ( $with_pull_down_menu === true ) {
      	$string .= $this->getBatchPreviousPageLink($batch_keyword, $parameters);
        $string .= $this->getBatchPagesPullDownMenu($batch_keyword, $parameters);
        $string .= $this->getBatchNextPageLink($batch_keyword, $parameters);
      }else {
        $string .= $this->getBatchPagesList($batch_keyword, $parameters);       
      }
      
      if ($with_pull_down_menu === false) {
				$string .= '</div>';
      }
      
      return $string;
    }
    
    /**
     * Output the pagination with list style
     * 
     * @access public
     * @param $batch_keyword [string] - the default url parameter for pagination
     * @param $parameters [string] - the parameters for each page link
     * @param $max_links [int] - the maximum pages will be displayed in the list style
     * @return string
     */
    function getBatchPagesList($batch_keyword = 'page', $parameters = '', $display_links = 6) {
      global $osC_Language;
      
      //calculte the total pages
      $total_pages = ceil($this->batch_size / $this->batch_rows);
      
      //output the pagination when total pages is greater than 1
      
      if ($total_pages > 1) {
      	$string = '<ul>';
      	
      	$string .= '<li>' . $this->getBatchPreviousPageLink($batch_keyword, $parameters) . '</li>';
      	
      	if ($total_pages > $display_links) {
      		//display the the links <= $display_links -1
      		if ($_GET[$batch_keyword] <= $display_links -1) {
      			//display the links with numbers
      			for ( $i = 1; $i <= $display_links; $i++ ) {
      				if ($i == $_GET[$batch_keyword] || ($i == 1 && empty($_GET[$batch_keyword]))) {
      					$string .= '<li><span>' . $i . '</span></li>';
      				}else {
      					$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $i : $batch_keyword . '='  . $i), $i) . '</li>';
      				}
      			}
      			
      			//represent the more pages
      			$string .= '<li><span>...</span></li>';
      			
      			//display the last page link
      			$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $total_pages : $batch_keyword . '='  . $total_pages), $total_pages) . '</li>';
      		//display the first page link && extra 5 page links&& the last page link
      		}else {
      			$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . 1 : $batch_keyword . '='  . 1), 1) . '</li>';
      			
      			//represent the more pages
      			$string .= '<li><span>...</span></li>';
      			
      			//display last five links
      			if ($total_pages - $_GET[$batch_keyword] <= 3) {
      				for ($i = $total_pages -4 ; $i <= $total_pages; $i++) {
      						
      					if ($i == $_GET[$batch_keyword]) {
      						$string .= '<li><span>' . $i . '</span></li>';
      					}else {
      						$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $i : $batch_keyword . '='  . $i), $i) . '</li>';
      					}
      				}
      			//display previous two pages, current page, latter two pages
      			}else {
      				for ($i = $_GET[$batch_keyword] -2; $i < $_GET[$batch_keyword] + 2; $i++) {
      				
      					if ($i == $_GET[$batch_keyword]) {
      						$string .= '<li><span>' . $i . '</span></li>';
      					}else {
      						$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $i : $batch_keyword . '='  . $i), $i) . '</li>';
      					}
      				}
      			}
      			
      			//represent the more pages
      			if ($i < $total_pages) {
      				$string .= '<li><span>...</span></li>';
      				
      				//display the last page link
      				$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $total_pages : $batch_keyword . '='  . $total_pages), $total_pages) . '</li>';
      			}
      		}
      		
        //display total pages with number
      	}else {
      		for ( $i = 1; $i <= $total_pages; $i++ ) {
      			if ($i == $_GET[$batch_keyword] || ($i == 1 && empty($_GET[$batch_keyword]))) {
      				$string .= '<li><span>' . $i . '</span></li>';
      			}else {
      				$string .= '<li>' . osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $parameters ? $parameters . '&' . $batch_keyword . '='  . $i : $batch_keyword . '='  . $i), $i) . '</li>';
      			}
      		}
      	}
      	
      	$string .= '<li>' . $this->getBatchNextPageLink($batch_keyword, $parameters) . '</li>';
        $string .= '</ul>';
      }else {
        $string = sprintf($osC_Language->get('result_set_current_page'), 1, 1);
      }
      
      return $string;
    }

    function getBatchPagesPullDownMenu($batch_keyword = 'page', $parameters = '') {
      global $osC_Language;

      $number_of_pages = ceil($this->batch_size / $this->batch_rows);

      if ( $number_of_pages > 1 ) {
        $pages_array = array();

        for ( $i = 1; $i <= $number_of_pages; $i++ ) {
          $pages_array[] = array('id' => $i,
                                 'text' => $i);
        }

        $hidden_parameter = '';

        if ( !empty($parameters) ) {
          $parameters = explode('&', $parameters);

          foreach ( $parameters as $parameter ) {
            $keys = explode('=', $parameter, 2);

            if ( $keys[0] != $batch_keyword ) {
              $hidden_parameter .= osc_draw_hidden_field($keys[0], (isset($keys[1]) ? $keys[1] : ''));
            }
          }
        }

        $string = '<form style="display:inline" action="' . osc_href_link(basename($_SERVER['SCRIPT_FILENAME'])) . '" method="get">' . $hidden_parameter .
                  sprintf($osC_Language->get('result_set_current_page'), osc_draw_pull_down_menu($batch_keyword, $pages_array, $this->batch_number, 'onchange="this.form.submit();"'), $number_of_pages) .
                  osc_draw_hidden_session_id_field() . '</form>';
      } else {
        $string = sprintf($osC_Language->get('result_set_current_page'), 1, 1);
      }

      return $string;
    }

    function getBatchPreviousPageLink($batch_keyword = 'page', $parameters = '') {
      global $osC_Language;

      $get_parameter = '';

      if ( !empty($parameters) ) {
        $parameters = explode('&', $parameters);

        foreach ( $parameters as $parameter ) {
          $keys = explode('=', $parameter, 2);

          if ( $keys[0] != $batch_keyword ) {
            $get_parameter .= $keys[0] . (isset($keys[1]) ? '=' . $keys[1] : '') . '&';
          }
        }
      }

      if ( defined('TOC_IN_ADMIN') && ( TOC_IN_ADMIN === true ) ) {
        $back_string = osc_icon('nav_back.png');
        $back_grey_string = osc_icon('nav_back_grey.png');
      } else {
        $back_string = $osC_Language->get('result_set_previous_page');
        $back_grey_string = $osC_Language->get('result_set_previous_page');
      }
      
      $number_of_pages = ceil($this->batch_size / $this->batch_rows);
      
      $string = '';

      if ( $this->batch_number > 1 ) {
        $string .= osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $get_parameter . $batch_keyword . '=' . ($this->batch_number - 1)), $back_string) . '</span>';
      } else {
        $string .= '<span>' . $back_grey_string . '</span>';
      }
      
      return $string;
    }

    function getBatchNextPageLink($batch_keyword = 'page', $parameters = '') {
      global $osC_Language;

      $number_of_pages = ceil($this->batch_size / $this->batch_rows);

      $get_parameter = '';

      if ( !empty($parameters) ) {
        $parameters = explode('&', $parameters);

        foreach ( $parameters as $parameter ) {
          $keys = explode('=', $parameter, 2);

          if ( $keys[0] != $batch_keyword ) {
            $get_parameter .= $keys[0] . (isset($keys[1]) ? '=' . $keys[1] : '') . '&';
          }
        }
      }

      if ( defined('TOC_IN_ADMIN') && ( TOC_IN_ADMIN === true ) ) {
        $forward_string = osc_icon('nav_forward.png');
        $forward_grey_string = osc_icon('nav_forward_grey.png');
      } else {
        $forward_string = $osC_Language->get('result_set_next_page');
        $forward_grey_string = $osC_Language->get('result_set_next_page');
      }

      $string = '';
      
      if ( ( $this->batch_number < $number_of_pages ) && ( $number_of_pages != 1 ) ) {
        $string .= osc_link_object(osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $get_parameter . $batch_keyword . '=' . ($this->batch_number + 1)), $forward_string);
      } else {
        $string .= '<span>' . $forward_grey_string . '</span>';
      }
      
      return $string;
    }

    function getAjaxBatchPageLinks($batch_keyword = 'page', $parameters = '') {
      $string = $this->getAjaxBatchPreviousPageLink($batch_keyword, $parameters);
      $string .= $this->getAjaxBatchNextPageLink($batch_keyword, $parameters);

      return $string;
    }


    function getAjaxBatchPreviousPageLink($batch_keyword = 'page', $parameters = '') {
      global $osC_Language;

      $get_parameter = '';

      if ( !empty($parameters) ) {
        $parameters = explode('&', $parameters);

        foreach ( $parameters as $parameter ) {
          $keys = explode('=', $parameter, 2);

          if ( $keys[0] != $batch_keyword ) {
            $get_parameter .= $keys[0] . (isset($keys[1]) ? '=' . $keys[1] : '') . '&';
          }
        }
      }

      if ( defined('TOC_IN_ADMIN') && ( TOC_IN_ADMIN === true ) ) {
        $back_string = osc_icon('nav_back.png');
        $back_grey_string = osc_icon('nav_back_grey.png');
      } else {
        $back_string = $osC_Language->get('result_set_previous_page');
        $back_grey_string = $osC_Language->get('result_set_previous_page');
      }

      if ( $this->batch_number > 1 ) {
        $string = osc_link_object('javascript:void(0);', $back_string, 'onclick="Modalbox.show(\'' . osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $get_parameter . $batch_keyword . '=' . ($this->batch_number - 1)) . '\')"');
      } else {
        $string = $back_grey_string;
      }

      $string .= '&nbsp;';

      return $string;
    }

    function getAjaxBatchNextPageLink($batch_keyword = 'page', $parameters = '') {
      global $osC_Language;

      $number_of_pages = ceil($this->batch_size / $this->batch_rows);

      $get_parameter = '';

      if ( !empty($parameters) ) {
        $parameters = explode('&', $parameters);

        foreach ( $parameters as $parameter ) {
          $keys = explode('=', $parameter, 2);

          if ( $keys[0] != $batch_keyword ) {
            $get_parameter .= $keys[0] . (isset($keys[1]) ? '=' . $keys[1] : '') . '&';
          }
        }
      }

      if ( defined('TOC_IN_ADMIN') && ( TOC_IN_ADMIN === true ) ) {
        $forward_string = osc_icon('nav_forward.png');
        $forward_grey_string = osc_icon('nav_forward_grey.png');
      } else {
        $forward_string = $osC_Language->get('result_set_next_page');
        $forward_grey_string = $osC_Language->get('result_set_next_page');
      }

      $string = '&nbsp;';

      if ( ( $this->batch_number < $number_of_pages ) && ( $number_of_pages != 1 ) ) {
        $string .= osc_link_object('javascript:void(0);', $forward_string , 'onclick="Modalbox.show(\'' . osc_href_link(basename($_SERVER['SCRIPT_FILENAME']), $get_parameter . $batch_keyword . '=' . ($this->batch_number + 1)) . '\')"');
      } else {
        $string .= $forward_grey_string;
      }

      return $string;
    }
  }
?>
