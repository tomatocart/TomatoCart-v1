<?php
/*
  $Id: directory_listing.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_DirectoryListing {

/* Private methods */

    var $_directory = '',
        $_include_files = true,
        $_include_directories = true,
        $_exclude_entries = array('.', '..','.svn'),
        $_stats = false,
        $_recursive = false,
        $_check_extension = array(),
        $_add_directory_to_filename = false,
        $_listing;

/* Class constructor */

    function osC_DirectoryListing($directory = '', $stats = false) {
      $this->setDirectory(realpath($directory));
      $this->setStats($stats);
    }

/* Public methods */

    function setDirectory($directory) {
      $this->_directory = $directory;
    }

    function setIncludeFiles($boolean) {
      if ($boolean === true) {
        $this->_include_files = true;
      } else {
        $this->_include_files = false;
      }
    }

    function setIncludeDirectories($boolean) {
      if ($boolean === true) {
        $this->_include_directories = true;
      } else {
        $this->_include_directories = false;
      }
    }

    function setExcludeEntries($entries) {
      if (is_array($entries)) {
        foreach ($entries as $value) {
          if (!in_array($value, $this->_exclude_entries)) {
            $this->_exclude_entries[] = $value;
          }
        }
      } elseif (is_string($entries)) {
        if (!in_array($entries, $this->_exclude_entries)) {
          $this->_exclude_entries[] = $entries;
        }
      }
    }

    function setStats($boolean) {
      if ($boolean === true) {
        $this->_stats = true;
      } else {
        $this->_stats = false;
      }
    }

    function setRecursive($boolean) {
      if ($boolean === true) {
        $this->_recursive = true;
      } else {
        $this->_recursive = false;
      }
    }

    function setCheckExtension($extension) {
      $this->_check_extension[] = $extension;
    }

    function setAddDirectoryToFilename($boolean) {
      if ($boolean === true) {
        $this->_add_directory_to_filename = true;
      } else {
        $this->_add_directory_to_filename = false;
      }
    }

    function read($directory = '') {
      if (empty($directory)) {
        $directory = $this->_directory;
      }

      if (!is_array($this->_listing)) {
        $this->_listing = array();
      }

      if ($dir = @dir($directory)) {
        while (($entry = $dir->read()) !== false) {
          if (!in_array($entry, $this->_exclude_entries)) {
            if (($this->_include_files === true) && is_file($dir->path . '/' . $entry)) {
              if (empty($this->_check_extension) || in_array(substr($entry, strrpos($entry, '.')+1), $this->_check_extension)) {
                if ($this->_add_directory_to_filename === true) {
                  if ($dir->path != $this->_directory) {
                    $entry = substr($dir->path, strlen($this->_directory)+1) . '/' . $entry;
                  }
                }

                $this->_listing[] = array('name' => $entry,
                                          'is_directory' => false);
                if ($this->_stats === true) {
                  $stats = array('size' => filesize($dir->path . '/' . $entry),
                                 'permissions' => fileperms($dir->path . '/' . $entry),
                                 'user_id' => fileowner($dir->path . '/' . $entry),
                                 'group_id' => filegroup($dir->path . '/' . $entry),
                                 'last_modified' => filemtime($dir->path . '/' . $entry));
                  $this->_listing[sizeof($this->_listing)-1] = array_merge($this->_listing[sizeof($this->_listing)-1], $stats);
                }
              }
            } elseif (is_dir($dir->path . '/' . $entry)) {
              if ($this->_include_directories === true) {
                $entry_name= $entry;

                if ($this->_add_directory_to_filename === true) {
                  if ($dir->path != $this->_directory) {
                    $entry_name = substr($dir->path, strlen($this->_directory)+1) . '/' . $entry;
                  }
                }

                $this->_listing[] = array('name' => $entry_name,
                                          'is_directory' => true);
                if ($this->_stats === true) {
                  $stats = array('size' => filesize($dir->path . '/' . $entry),
                                 'permissions' => fileperms($dir->path . '/' . $entry),
                                 'user_id' => fileowner($dir->path . '/' . $entry),
                                 'group_id' => filegroup($dir->path . '/' . $entry),
                                 'last_modified' => filemtime($dir->path . '/' . $entry));
                  $this->_listing[sizeof($this->_listing)-1] = array_merge($this->_listing[sizeof($this->_listing)-1], $stats);
                }
              }

              if ($this->_recursive === true) {
                $this->read($dir->path . '/' . $entry);
              }
            }
          }
        }

        $dir->close();
        unset($dir);
      }
    }

    function getFiles($sort_by_directories = true) {
      if (!is_array($this->_listing)) {
        $this->read();
      }

      if (is_array($this->_listing) && (sizeof($this->_listing) > 0)) {
        if ($sort_by_directories === true) {
          usort($this->_listing, array($this, '_sortListing'));
        }

        return $this->_listing;
      }

      return array();
    }

    function getSize() {
      if (!is_array($this->_listing)) {
        $this->read();
      }

      return sizeof($this->_listing);
    }

    function getDirectory() {
      return $this->_directory;
    }

/* Private methods */

    function _sortListing($a, $b) {
      return strcmp((($a['is_directory'] === true) ? 'D' : 'F') . $a['name'], (($b['is_directory'] === true) ? 'D' : 'F') . $b['name']);
    }
  }
?>
