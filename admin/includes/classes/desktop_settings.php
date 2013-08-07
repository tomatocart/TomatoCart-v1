<?php
/*
  $Id: access.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Desktop_Settings {
    var $_settings = null,
        $_user_name = null,
        $_modules = array();
    
    function toC_Desktop_Settings() {
      $this->_user_name = $_SESSION['admin']['username'];
      
      $this->initialize();
    }
    
    function initialize() {
      global $osC_Database;

      //initiallize settings
      $Qsettings = $osC_Database->query('select user_settings from :table_administrators where user_name = :user_name');
      $Qsettings->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qsettings->bindValue(':user_name', $this->_user_name);
      $Qsettings->execute();
      
      $settings = $Qsettings->value('user_settings');
      $settings = unserialize($settings);
      
      if( !is_array($settings) || empty($settings) || !isset($settings['desktop']) ) {
        $this->_settings = $this->getDefaultSettings();
        
        $this->save($this->_settings);
      }else {
        $this->_settings = $settings['desktop'];
      }
      
      //initialize modules
      $access = osC_Access::getLevels();
      ksort($access);

      $modules = array();
      foreach ( $access as $group => $links ) {
        $modules[] = $group;
        
        foreach ( $links as $link ) {
          $module = $link['module'];
        
          if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
            $modules[] = $module;
            
            foreach ( $link['subgroups'] as $subgroup ) {
              $modules[] = $module;
            }
          }else {
            $modules[] = $module;
          }
        }
      }
      $this->_modules = $modules;
    }
    
    function saveDesktop($data) {
      $this->_settings['autorun'] = $data['autorun'];
      $this->_settings['quickstart'] = $data['quickstart'];
      $this->_settings['contextmenu'] = $data['contextmenu'];
      $this->_settings['shortcut'] = $data['shortcut'];
      $this->_settings['theme'] = $data['theme'];
      $this->_settings['wallpaper'] = $data['wallpaper'];
      $this->_settings['transparency'] = $data['transparency'];
      $this->_settings['backgroundcolor'] = $data['backgroundcolor'];
      $this->_settings['fontcolor'] = $data['fontcolor'];
      $this->_settings['wallpaperposition'] = $data['wallpaperposition'];
      $this->_settings['sidebaropen'] = $data['sidebaropen'];
      $this->_settings['sidebartransparency'] = $data['sidebartransparency'];
      $this->_settings['sidebarbackgroundcolor'] = $data['sidebarbackgroundcolor'];
      
      return $this->save($this->_settings);
    }
    
    function save($data) {
      global $osC_Database;
      
      $Qsettings = $osC_Database->query('select user_settings from :table_administrators where user_name = :user_name');
      $Qsettings->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $Qsettings->bindValue(':user_name', $this->_user_name);
      $Qsettings->execute();
      
      $settings = $Qsettings->value('user_settings');
      $settings = unserialize($settings);
      
      if(is_array($data) && !empty($settings['desktop'])) {
        $settings['desktop'] = array_merge($settings['desktop'] ,$data);
      } else {
        $settings['desktop'] = $data;
      }
      
      $update = $osC_Database->query('update :table_administrators set user_settings = :user_settings where user_name = :user_name');
      $update->bindTable(':table_administrators', TABLE_ADMINISTRATORS);
      $update->bindValue(':user_settings', serialize($settings));
      $update->bindValue(':user_name', $this->_user_name);
      $update->execute();
      
      if ( $osC_Database->isError() ) {
        return false;
      }

      return true;
    }
    
    function getDefaultSettings() {
      $settings = array();

      $settings['theme'] = 'vistablue';
      $settings['transparency'] = '100';
      $settings['backgroundcolor'] = '3A6EA5';
      $settings['fontcolor'] = 'FFFFFF';
      $settings['wallpaper'] = 'blank';
      $settings['wallpaperposition'] = 'tile';
      
      $settings['autorun'] = '["dashboard-win"]';
      $settings['contextmenu'] = '[]';
      $settings['quickstart'] = '["articles_categories-win","articles-win","faqs-win","slide_images-win","products-win","customers-win","orders-win", "invoices-win", "coupons-win","gift_certificates-win","dashboard-win"]';
      $settings['shortcut'] = '["articles_categories-win","articles-win","faqs-win","slide_images-win","products-win","customers-win","orders-win", "invoices-win", "coupons-win","gift_certificates-win","dashboard-win"]';
      $settings['wizard_complete'] = false;
      
      $settings['dashboards'] = 'overview:0,new_orders:1,new_customers:2,new_reviews:0,orders_statistics:1';
      
      $settings['gadgets'] = '["clock","new_customers","new_orders"]';
      $settings['sidebartransparency'] = '5';
      $settings['sidebarbackgroundcolor'] = 'FFFFFF';
      $settings['sidebaropen'] = true;
      $settings['livefeed'] = 0;
      
      return $settings;
    }
    
    function setLastLiveFeed($timestamp) {
      $this->_settings['livefeed'] = $timestamp;
      $this->save($this->_settings);
      
      return true;          
    }
    
    function getLastLiveFeed() {
      $timestamp = '';
      
      if (isset($this->_settings['livefeed'])) {
        $timestamp = $this->_settings['livefeed'];
      } 
      
      return $timestamp;          
    }

    function saveDashBoards($portlets) {
      $this->_settings['dashboards'] = $portlets;
      $this->save($this->_settings);
      
      return true;    
    }
    
    function getDashBoards() {
      $dashboards = '';
      
      if (isset($this->_settings['dashboards'])) {
        $dashboards = $this->_settings['dashboards'];
      } 
      
      return $dashboards;
    }
    
    function saveGadgets($gadgets) {
      $this->_settings['gadgets'] = $gadgets;
      return $this->save($this->_settings);
    }
    
    function getGadgets() {
      $sidebar_gadgets = '';
      
      if ( isset($this->_settings['gadgets']) ) {
        $sidebar_gadgets = $this->_settings['gadgets'];
      }
      
      return $sidebar_gadgets;
    }
    
    function isSidebarOpen() {
      return $this->_settings['sidebaropen'];
    }
        
    function getWallpaper() {
      $code = (isset($this->_settings['wallpaper']) && !empty($this->_settings['wallpaper'])) ? $this->_settings['wallpaper'] : 'blank';
      
      $wallpapers = $this->getWallpapers();
      $path = '';
      foreach($wallpapers as $tmp) {
        if($code == $tmp['code'])
          $path = $tmp['path'];
      }
      
      $wallpaper = array();
      $wallpaper['code'] = $code;
      $wallpaper['path'] = $path;
      
      return $wallpaper;
    }
    
    function getTheme() {
      $code = (isset($this->_settings['theme']) && !empty($this->_settings['theme'])) ? $this->_settings['theme'] : 'vistablue';
      
      $themes = $this->getThemes();
      $path = '';
      foreach($themes as $tmp) {
        if($code == $tmp['code'])
          $path = $tmp['path'];
      }
      
      $theme = array();
      $theme['code'] = $code;
      $theme['path'] = $path;
      
      return $theme;
    }
    
    function getLaunchers(){
      $autorun     = (isset($this->_settings['autorun']) && !empty($this->_settings['autorun'])) ? $this->_settings['autorun'] : '[]';
      $shortcut    = (isset($this->_settings['shortcut']) && !empty($this->_settings['shortcut'])) ? $this->_settings['shortcut'] : '[]';
      $quickstart  = (isset($this->_settings['quickstart']) && !empty($this->_settings['quickstart'])) ? $this->_settings['quickstart'] : '[]';
      $contextmenu = (isset($this->_settings['contextmenu']) && !empty($this->_settings['contextmenu'])) ? $this->_settings['contextmenu'] : '[]';
      
      return "{'autorun': " . $autorun . ", 
               'contextmenu': " . $contextmenu . ", 
               'quickstart': " . $quickstart . ",
               'shortcut': " . $shortcut . "}";
    }
            
    function getStyles() {
      global $toC_Json;

      $backgroundcolor = (isset($this->_settings['backgroundcolor']) && !empty($this->_settings['backgroundcolor'])) ? $this->_settings['backgroundcolor'] : '#3A6EA5';
      $fontcolor = (isset($this->_settings['fontcolor']) && !empty($this->_settings['fontcolor'])) ? $this->_settings['fontcolor'] : 'FFFFFF';
      $transparency = (isset($this->_settings['transparency']) && !empty($this->_settings['transparency'])) ? $this->_settings['transparency'] : '100';
      $sidebartransparency = isset($this->_settings['sidebartransparency']) ? $this->_settings['sidebartransparency'] : '100';
      $sidebarbackgroundcolor = isset($this->_settings['sidebarbackgroundcolor']) ? $this->_settings['sidebarbackgroundcolor'] : '#1F90FF';
      $wallpaperposition = (isset($this->_settings['wallpaperposition']) && !empty($this->_settings['wallpaperposition'])) ? $this->_settings['wallpaperposition'] : 'tile';

      $styles = array();
      $styles['backgroundcolor'] = $backgroundcolor;
      $styles['fontcolor'] = $fontcolor;
      $styles['theme'] = $this->getTheme();
      $styles['transparency'] = $transparency;
      $styles['sidebartransparency'] = $sidebartransparency;
      $styles['sidebarbackgroundcolor'] = $sidebarbackgroundcolor;
      $styles['wallpaper'] = $this->getWallpaper();
      $styles['wallpaperposition'] = $wallpaperposition;
      
      return $toC_Json->encode($styles);
    }
    
    function getWallpapers() {
      global $osC_Database;
      
      $result = simplexml_load_file(realpath('templates/default/desktop/wallpapers/wallpapers.xml'));  
      
      $wallpapers = array();
      foreach ($result->Wallpaper as $wallpaper) {
        $wallpapers[] = array(
          'code' => strval($wallpaper->Code),
          'name' => strval($wallpaper->Name),
          'thumbnail' => strval($wallpaper->Thumbnail),
          'path' => strval($wallpaper->File)
        );
      }
      
      return $wallpapers;
    }

    function getThemes() {
      global $osC_Database;
      
      $result = simplexml_load_file(realpath('templates/default/desktop/themes/themes.xml'));  
      
      $themes = array();
      foreach ($result->Theme as $theme) {

        $themes[] = array(
          'code' => strval($theme->Code),
          'name' => strval($theme->Name),
          'thumbnail' => strval($theme->Thumbnail),
          'path' => strval($theme->Path)
        );
      }
      
      return $themes;
    }
    
    function getModules() {
      global $toC_Json, $osC_Language;
      
      if (isset($_SESSION['admin'])) {
        $access = osC_Access::getLevels();
        ksort($access);
      }

      $modules = array();
      foreach ( $access as $group => $links ) {
        $modules[] = 'new TocDesktop.' . ucfirst($group) . 'GroupWindow()';
        
        foreach ( $links as $link ) {
          $module = str_replace(' ', '', ucwords(str_replace('_', ' ', $link['module'])));
        
          if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
            $modules[] = 'new TocDesktop.' . $module . 'SubGroupWindow()';
            
            foreach ( $link['subgroups'] as $subgroup ) {
              $params = isset($subgroup['params']) ? $subgroup['params'] : null;
              $modules[] = 'new TocDesktop.' . $module . 
              'Window({id: \'' . $subgroup['identifier'] . '\', title: \'' . $subgroup['title'] . '\', iconCls: \'' . $subgroup['iconCls'] . '\', shortcutIconCls: \'' . $subgroup['shortcutIconCls'] . '\', params: ' . $toC_Json->encode($params) . '})';
            }
          }else {
            $modules[] = 'new TocDesktop.' . $module . 'Window()';
          }
        }
      }

      $modules[] = 'new TocDesktop.LanguagesGroupWindow()';
      $languages = array();
      foreach ( $osC_Language->getAll() as $l ) {
        $modules[] = 'new TocDesktop.' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($l['code'])))) . 'Window()';
      }
      
      $menu = '[' . implode(',' , $modules) . ']';
     
      return $menu;
    }
    
    function hasAccess($module) {
      if (in_array($module, $this->_modules)) {
        return true;
      }
      
      return false;
    }
    
    function isWizardComplete() { 
      if ( $this->hasAccess('configuration_wizard') ) {
        return $this->_settings['wizard_complete'];
      } 
      
      return true;
    }
    
    function setWizardComplete() {
      $this->_settings['wizard_complete'] = true;
      
      $this->save($this->_settings);
    }
    
    function LoopLauncher($launcher, $module) {
      foreach ($launcher as $value) {
        $value = str_replace('"', '', $value );
        
        if ( strcmp($module, $value) ==0 ) {
          $result = true;
          break;
        } else {
          $result = false;
        } 
      }
      return $result;
    }
    
    function listModules($settings) {
      global $osC_Language;

      $autorun = (explode(",", (substr($settings['autorun'], 1, strlen($settings['autorun'])-2))));
      $contextmenu = explode(",", (substr($settings['contextmenu'], 1, strlen($settings['contextmenu'])-2)));
      $quickstart = (explode(",", (substr($settings['quickstart'], 1, strlen($settings['quickstart'])-2))));
      $shortcut = (explode(",", (substr($settings['shortcut'], 1, strlen($settings['shortcut'])-2))));
      
      if (isset($_SESSION['admin'])) {
        $access = osC_Access::getLevels();
        ksort($access);
      }
     
      $modules = array();
      foreach ( $access as $group => $links ) {
        $module = htmlentities(osC_Access::getGroupTitle($group), ENT_QUOTES, 'UTF-8');
        foreach ( $links as $link ) {
          $secmodule = ucwords(str_replace('_', ' ', $link['module']));
          
          if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
            foreach ( $link['subgroups'] as $subgroup ) {
              
              $Aautorun = self::LoopLauncher($autorun, $subgroup['identifier']);
              $Acontextmenu = self::LoopLauncher($contextmenu, $subgroup['identifier']);
              $Aquickstart = self::LoopLauncher($quickstart, $subgroup['identifier']);
              $Ashortcut = self::LoopLauncher($shortcut, $subgroup['identifier']);
              
              $modules[] = array('parent' => $module,
                                 'text'=>htmlentities($subgroup['title'], ENT_QUOTES, 'UTF-8'),
                                 'id'=>$subgroup['identifier'],
                                 'autorun'=>$Aautorun,
                                 'contextmenu'=>$Acontextmenu,
                                 'quickstart'=>$Aquickstart,
                                 'shortcut'=>$Ashortcut);
            }
          } else {
            $link['module'] =$link['module'].'-win';
            $Aautorun = self::LoopLauncher($autorun, $link['module']);
            $Acontextmenu = self::LoopLauncher($contextmenu, $link['module']);
            $Aquickstart = self::LoopLauncher($quickstart, $link['module']);
            $Ashortcut = self::LoopLauncher($shortcut, $link['module']);
            $modules[] = array('parent' => $module,
                               'text'=>htmlentities($secmodule, ENT_QUOTES, 'UTF-8'),
                               'id'=> $link['module'],
                               'autorun'=>$Aautorun,
                               'contextmenu'=>$Acontextmenu,
                               'quickstart'=>$Aquickstart,
                               'shortcut'=>$Ashortcut);
          }
        }
      }

      foreach ( $access as $group => $links ) {
        $autorun = array();
        $contextmenu = array();
        $quickstart = array();
        $shortcut = array();
        $module_title = htmlentities(osC_Access::getGroupTitle($group), ENT_QUOTES, 'UTF-8');
            
        foreach($modules as $id => $module) {
          if($module['parent'] == $module_title) {
            $autorun[] = $module['autorun'];
            $quickstart[] = $module['quickstart'];
            $contextmenu[] = $module['contextmenu'];
            $shortcut[] = $module['shortcut'];
          }
        }
        
        $modules[] = array('parent' => $module_title,
                           'text'=> $osC_Language->get('--All--'),
                           'id'=> '',
                           'autorun'=> !in_array(false, $autorun),
                           'contextmenu'=> !in_array(false, $contextmenu),
                           'quickstart'=> !in_array(false, $quickstart),
                           'shortcut'=> !in_array(false, $shortcut));
      }
      
      return $modules;
    }
    
    function outputModules() {
      $output = '';
      
      if (isset($_SESSION['admin'])) {
        $access = osC_Access::getLevels();
        ksort($access);
      }    
      
      foreach ( $access as $group => $links ) {
        $group_class = '';
        $modules = array();
        foreach ( $links as $link ) {
          if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
            $modules[] = '\'' . $link['module'] . '-subgroup' . '\'';
          } else {
            $modules[] = '\'' . $link['module'] . '-win' . '\'';
          }
        }       
        $group_class = 'TocDesktop.' . ucfirst($group) . 'GroupWindow = Ext.extend(Ext.app.Module, {' . "\n";
        $group_class .= 'appType : \'group\',' . "\n";
        $group_class .= 'id : \'' . $group . '-grp\',' . "\n";
        $group_class .= 'title : \'' . htmlentities(osC_Access::getGroupTitle($group), ENT_QUOTES, 'UTF-8') . '\',' . "\n";
        $group_class .= 'menu : new Ext.menu.Menu(),' . "\n";
        $group_class .= 'items : [' . implode(',' , $modules) . '],' . "\n";
        $group_class .= 'init : function(){' . "\n";
        $group_class .= 'this.launcher = {' . "\n";
        $group_class .= 'text: this.title,' . "\n";
        $group_class .= 'iconCls: \'icon-' . $group . '-grp\',' . "\n";
        $group_class .= 'menu: this.menu' . "\n";
        $group_class .= '}}});' . "\n" . "\n";
        
        $output .= $group_class;
        
        foreach ( $links as $link ) {
        
          if ( is_array($link['subgroups']) && !empty($link['subgroups']) ) {
            $modules = array();
            
            foreach ( $link['subgroups'] as $subgroup ) {
              $modules[] = '\'' . $subgroup['identifier'] . '\'';
            }
            
            $group_class = '';
            $module = str_replace(' ', '', ucwords(str_replace('_', ' ', $link['module'])));
            $group_class = 'TocDesktop.' . $module . 'SubGroupWindow = Ext.extend(Ext.app.Module, {' . "\n";
            $group_class .= 'appType : \'subgroup\',' . "\n";
            $group_class .= 'id : \'' . $link['module'] . '-subgroup\',' . "\n";
            $group_class .= 'title : \'' . htmlentities($link['title'], ENT_QUOTES, 'UTF-8') . '\',' . "\n";
            $group_class .= 'menu : new Ext.menu.Menu(),' . "\n";
            $group_class .= 'items : [' . implode(',' , $modules) . '],' . "\n";
            $group_class .= 'init : function(){' . "\n";
            $group_class .= 'this.launcher = {' . "\n";
            
            $group_class .= 'text: this.title,' . "\n";
            $group_class .= 'iconCls: \'icon-' . $link['module'] . '-subgroup\',' . "\n";
            $group_class .= 'menu: this.menu' . "\n";
            $group_class .= '}}});' . "\n" . "\n";
            
            $output .= $group_class;
            
            $group_class = '';
            $module = str_replace(' ', '', ucwords(str_replace('_', ' ', $link['module'])));
            $group_class = 'TocDesktop.' . $module . 'Window = Ext.extend(Ext.app.Module, {' . "\n";
            $group_class .= 'appType : \'win\',' . "\n";
            $group_class .= 'id : \'' . $link['module'] . '-win\',' . "\n";
            $group_class .= 'title: \'' . htmlentities($link['title'], ENT_QUOTES, 'UTF-8') . '\',' . "\n";
            $group_class .= 'init : function(){' . "\n";
            $group_class .= 'this.launcher = {' . "\n";
            
            
            $group_class .= 'text: this.title,' . "\n";
            $group_class .= 'iconCls: this.iconCls,' . "\n";
            $group_class .= 'shortcutIconCls: this.shortcutIconCls,' . "\n";
          
            $group_class .= 'scope: this' . "\n";
            $group_class .= '}}});' . "\n" . "\n";
            
            $output .= $group_class;
                        
          } else {
            $group_class = '';
            $module = str_replace(' ', '', ucwords(str_replace('_', ' ', $link['module'])));
            $group_class = 'TocDesktop.' . $module . 'Window = Ext.extend(Ext.app.Module, {' . "\n";
            $group_class .= 'appType : \'win\',' . "\n";
            $group_class .= 'id : \'' . $link['module'] . '-win\',' . "\n";
            $group_class .= 'title: \'' . htmlentities($link['title'], ENT_QUOTES, 'UTF-8') . '\',' . "\n";
            $group_class .= 'init : function(){' . "\n";
            $group_class .= 'this.launcher = {' . "\n";
            
            
            $group_class .= 'text: this.title,' . "\n";
            $group_class .= 'iconCls: \'icon-' . $link['module'] . '-win\',' . "\n";
            $group_class .= 'shortcutIconCls: \'icon-' . $link['module'] . '-shortcut\',' . "\n";
          
            $group_class .= 'scope: this' . "\n";
            $group_class .= '}}});' . "\n" . "\n";
            
            $output .= $group_class;
          }
        }
      }
      
      $output .= $this->getLangModules();
      
      return $output;
    }
    
    function getLangModules() {
      global $osC_Language;
      
      $languages = array();
      foreach ( $osC_Language->getAll() as $l ) {
        $languages[] = '\'lang-' . strtolower($l['code']) . '-win' . '\'';
      }
      
      $output = 'TocDesktop.LanguagesGroupWindow = Ext.extend(Ext.app.Module, {';
      $output .= 'appType : \'group\',';
      
      $output .= 'id : \'languages-grp\',';
      $output .= 'menu : new Ext.menu.Menu(),';
      $output .= 'items : [' . implode(',', $languages) . '],';
      $output .= 'init : function(){';
      $output .= 'this.launcher = {';
      $output .= 'text: \'' . $osC_Language->get('header_title_languages') . '\',';
      $output .= 'iconCls: \'icon-languages-grp\',';
      $output .= 'menu: this.menu';
      $output .= '}';
      $output .= '}';
      $output .= '});';
      
      foreach ( $osC_Language->getAll() as $l ) {
    
        $output .= 'TocDesktop.' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($l['code'])))) . 'Window = Ext.extend(Ext.app.Module, {';
        $output .= 'appType : \'grid\',';
        $output .= 'id : \'lang-' . strtolower($l['code']) . '-win\',';
        $output .= 'init : function(){';
          $output .= 'this.launcher = {';
            $output .= 'text: \'' . $l['name'] . '\',';
            $output .= 'iconCls: \'icon-' . $l['country_iso'] . '-win\',';
            $output .= 'shortcutIconCls: \'icon-' . $l['code'] . '-shortcut\',';
            $output .= 'handler: function(){window.location = "' . osc_href_link_admin(FILENAME_DEFAULT, 'admin_language=' . $l['code']) . '";},';
            $output .= 'scope: this';
            $output .= '}';
          $output .= '}';
        $output .= '});';
      }

      return $output;
    }
    
    function getArrayFromQueryString( $urlQuery ){
      if(strlen($urlQuery) == 0){ return array(); }
      if($urlQuery[0] == '?'){ $urlQuery = substr($urlQuery, 1); }
  
      $separator = '&';
  
      $urlQuery = $separator . $urlQuery;
      $refererQuery = trim($urlQuery);
  
      $values = explode($separator, $refererQuery);
  
      $nameToValue = array();
      foreach($values as $value) {
        if( false !== strpos($value, '=')) {
          $exploded = explode('=',$value);
          $nameToValue[$exploded[0]] = $exploded[1];
        }
      }
      return $nameToValue;
    }
  }
?>
