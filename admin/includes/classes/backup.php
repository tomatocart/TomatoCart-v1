<?php
/*
  $Id: backup.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Backup_Admin {
    function backup($compression = null, $download_only = false) {
      global $osC_Database;

      if ( osc_empty(DIR_FS_BACKUP) || !is_dir(DIR_FS_BACKUP) || !is_writeable(DIR_FS_BACKUP) ) {
        return false;
      }

      osc_set_time_limit(0);

      $backup_file = 'db_' . DB_DATABASE . '-' . date('YmdHis') . '.sql';

      $fp = fopen(DIR_FS_BACKUP . $backup_file, 'w');

      $schema = '# TomatoCart Open Source Shopping Cart Solutions' . "\n" .
                '# http://www.tomatocart.com' . "\n" .
                '#' . "\n" .
                '# Database Backup For ' . STORE_NAME . "\n" .
                '# Copyright (c) ' . date('Y') . ' ' . STORE_OWNER . "\n" .
                '#' . "\n" .
                '# Database: ' . DB_DATABASE . "\n" .
                '# Database Server: ' . DB_SERVER . "\n" .
                '#' . "\n" .
                '# Backup Date: ' . osC_DateTime::getShort(null, true) . "\n\n";

      fputs($fp, $schema);

      $Qtables = $osC_Database->query('show tables');

      while ( $Qtables->next() ) {
        $table = $Qtables->value('Tables_in_' . DB_DATABASE);

        $schema = 'drop table if exists ' . $table . ';' . "\n" .
                  'create table ' . $table . ' (' . "\n";

        $table_list = array();

        $Qfields = $osC_Database->query('show fields from :table');
        $Qfields->bindTable(':table', $table);
        $Qfields->execute();

        while ( $Qfields->next() ) {
          $table_list[] = $Qfields->value('Field');

          $schema .= '  ' . $Qfields->value('Field') . ' ' . $Qfields->value('Type');

          if ( !osc_empty($Qfields->value('Default')) ) {
            $schema .= ' default \'' . $Qfields->value('Default') . '\'';
          }

          if ( $Qfields->value('Null') != 'YES' ) {
            $schema .= ' not null';
          }

          if ( !osc_empty($Qfields->value('Extra')) ) {
            $schema .= ' ' . $Qfields->value('Extra');
          }

          $schema .= ',' . "\n";
        }

        $schema = ereg_replace(",\n$", '', $schema);

// add the keys
        $Qkeys = $osC_Database->query('show keys from :table');
        $Qkeys->bindTable(':table', $table);
        $Qkeys->execute();

        $index = array();

        while ( $Qkeys->next() ) {
          $kname = $Qkeys->value('Key_name');

          if ( !isset($index[$kname]) ) {
            $index[$kname] = array('unique' => !$Qkeys->value('Non_unique'),
                                   'fulltext' => ($Qkeys->value('Index_type') == 'FULLTEXT' ? true : false),
                                   'columns' => array());
          }

          $index[$kname]['columns'][] = $Qkeys->value('Column_name');
        }

        foreach ( $index as $kname => $info ) {
          $schema .= ',' . "\n";

          $columns = implode($info['columns'], ', ');

          if ( $kname == 'PRIMARY' ) {
            $schema .= '  PRIMARY KEY (' . $columns . ')';
          } elseif ( $info['fulltext'] === true ) {
            $schema .= '  FULLTEXT ' . $kname . ' (' . $columns . ')';
          } elseif ( $info['unique'] ) {
            $schema .= '  UNIQUE ' . $kname . ' (' . $columns . ')';
          } else {
            $schema .= '  KEY ' . $kname . ' (' . $columns . ')';
          }
        }

        $schema .= "\n" . ');' . "\n\n";

        fputs($fp, $schema);

// dump the data from the tables except from the sessions table and the who's online table
        if ( ( $table != TABLE_SESSIONS ) && ( $table != TABLE_WHOS_ONLINE ) ) {
          $Qrows = $osC_Database->query('select :columns from :table');
          $Qrows->bindRaw(':columns', implode(', ', $table_list));
          $Qrows->bindTable(':table', $table);
          $Qrows->execute();

          while ( $Qrows->next() ) {
            $rows = $Qrows->toArray();

            $schema = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values (';

            foreach ( $table_list as $i ) {
              if ( !isset($rows[$i]) ) {
                $schema .= 'NULL, ';
              } elseif ( strlen($rows[$i]) > 0 ) {
                $row = addslashes($rows[$i]);
                $row = ereg_replace("\n#", "\n".'\#', $row);

                $schema .= '\'' . $row . '\', ';
              } else {
                $schema .= '\'\', ';
              }
            }

            $schema = ereg_replace(', $', '', $schema) . ');' . "\n";

            fputs($fp, $schema);
          }
        }
      }

      fclose($fp);

      unset($schema);

      switch ( $compression ) {
        case 'gzip':
          exec(CFG_APP_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);

          $backup_file .= '.gz';

          break;

        case 'zip':
          exec(CFG_APP_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
          unlink(DIR_FS_BACKUP . $backup_file);

          $backup_file .= '.zip';

          break;
      }

      if ( $download_only === true ) {
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $backup_file);

        readfile(DIR_FS_BACKUP . $backup_file);

        unlink(DIR_FS_BACKUP . $backup_file);

        exit;
      }

      if ( file_exists(DIR_FS_BACKUP . $backup_file) ) {
        return true;
      }

      return false;
    }

    function restore($filename = false) {
      global $osC_Database, $osC_Session;

      osc_set_time_limit(0);

      if ( $filename !== false ) {
        if ( file_exists(DIR_FS_BACKUP . $filename) ) {
          $restore_file = DIR_FS_BACKUP . $filename;
          $extension = substr($filename, -3);

          if ( ( $extension == 'sql' ) || ( $extension == '.gz' ) || ( $extension == 'zip' ) ) {
            switch ( $extension ) {
              case 'sql':
                $restore_from = $restore_file;

                $remove_raw = false;

                break;

              case '.gz':
                $restore_from = substr($restore_file, 0, -3);
                exec(CFG_APP_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);

                $remove_raw = true;

                break;

              case 'zip':
                $restore_from = substr($restore_file, 0, -4);
                exec(CFG_APP_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);

                $remove_raw = true;

                break;
            }

            if ( isset($restore_from) && file_exists($restore_from) ) {
              $fd = fopen($restore_from, 'rb');
              $restore_query = fread($fd, filesize($restore_from));
              fclose($fd);
            }
          }
        }
      } else {
        $sql_file = new upload('sql_file');
        $sql_file->set_output_messages('session');

        if ( $sql_file->parse() ) {
          $restore_query = fread(fopen($sql_file->tmp_filename, 'r'), filesize($sql_file->tmp_filename));
          $filename = $sql_file->filename;
        }
      }

      if ( isset($restore_query) && !empty($restore_query) ) {
        $sql_array = array();
        $sql_length = strlen($restore_query);
        $pos = strpos($restore_query, ';');

        for ( $i = $pos; $i < $sql_length; $i++ ) {
          if ( $restore_query[0] == '#' ) {
            $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
            $sql_length = strlen($restore_query);
            $i = strpos($restore_query, ';')-1;
            continue;
          }

          if ( $restore_query[($i+1)] == "\n" ) {
            for ( $j = ($i + 2); $j < $sql_length; $j++ ) {
              if ( trim($restore_query[$j]) != '' ) {
                $next = substr($restore_query, $j, 6);

                if ( $next[0] == '#' ) {
// find out where the break position is so we can remove this line (#comment line)
                  for ( $k = $j; $k < $sql_length; $k++ ) {
                    if ( $restore_query[$k] == "\n" ) {
                      break;
                    }
                  }

                  $query = substr($restore_query, 0, $i+1);
                  $restore_query = substr($restore_query, $k);
// join the query before the comment appeared, with the rest of the dump
                  $restore_query = $query . $restore_query;
                  $sql_length = strlen($restore_query);
                  $i = strpos($restore_query, ';')-1;
                  continue 2;
                }

                break;
              }
            }

            if ( $next == '' ) { // get the last insert query
              $next = 'insert';
            }

            if ( eregi('create', $next) || eregi('insert', $next) || eregi('drop t', $next) ) {
              $next = '';
              $sql_array[] = substr($restore_query, 0, $i);
              $restore_query = ltrim(substr($restore_query, $i+1));
              $sql_length = strlen($restore_query);
              $i = strpos($restore_query, ';')-1;
            }
          }
        }

// drop all tables defined in oscommerce/includes/database_tables.php
        $tables_array = array();

        foreach ( get_defined_constants() as $key => $value) {
          if ( substr($key, 0, 6) == 'TABLE_') {
            $tables_array[] = $value;
          }
        }

        if ( !empty($tables_array) ) {
          $Qdrop = $osC_Database->query('drop table if exists :tables');
          $Qdrop->bindRaw(':tables', implode(', ', $tables_array));
          $Qdrop->execute();
        }

        for ( $i = 0, $n = sizeof($sql_array); $i < $n; $i++ ) {
          $osC_Database->simpleQuery($sql_array[$i]);
        }

        $osC_Session->close();

// empty the sessions table
        $Qsessions = $osC_Database->query('delete from :table_sessions');
        $Qsessions->bindTable(':table_sessions', TABLE_SESSIONS);
        $Qsessions->execute();

// empty the who's online table
        $Qwho = $osC_Database->query('delete from :table_whos_online');
        $Qwho->bindTable(':table_whos_online', TABLE_WHOS_ONLINE);
        $Qwho->execute();

        $Qcfg = $osC_Database->query('delete from :table_configuration where configuration_key = :configuration_key');
        $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qcfg->bindValue(':configuration_key', 'DB_LAST_RESTORE');
        $Qcfg->execute();

        $Qcfg = $osC_Database->query('insert into :table_configuration values ("", "Last Database Restore", "DB_LAST_RESTORE", :filename, "Last database restore file", "6", "", "", now(), "", "")');
        $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
        $Qcfg->bindValue(':filename', $filename);
        $Qcfg->execute();

        osC_Cache::clear('configuration');

        if ( isset($remove_raw) && ( $remove_raw === true ) ) {
          unlink($restore_from);
        }

        return true;
      }

      return false;
    }

    function delete($filename) {
      $filename = basename($filename);

      if ( !empty($filename) && file_exists(DIR_FS_BACKUP . $filename) ) {
        if ( @unlink(DIR_FS_BACKUP . $filename) ) {
          return true;
        }
      }

      return false;
    }

    function forget() {
      global $osC_Database;

      $Qcfg = $osC_Database->query('delete from :table_configuration where configuration_key = :configuration_key');
      $Qcfg->bindTable(':table_configuration', TABLE_CONFIGURATION);
      $Qcfg->bindValue(':configuration_key', 'DB_LAST_RESTORE');
      $Qcfg->setLogging($_SESSION['module']);
      $Qcfg->execute();

      if ( !$osC_Database->isError() ) {
        osC_Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
