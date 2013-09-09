<?php
/*
  $Id: template.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

/**
 * The osC_Template class defines or adds elements to the page output such as the page title, page content, and javascript blocks
 */

  class osC_Template {

/**
 * Holds the template name value
 *
 * @var string
 * @access private
 */

    var $_template;

/**
 * Holds the template ID value
 *
 * @var int
 * @access private
 */

    var $_template_id;

/**
 * Holds the title of the page module
 *
 * @var string
 * @access private
 */

    var $_module;

/**
 * Holds the group name of the page
 *
 * @var string
 * @access private
 */

    var $_group;
    
/**
 * Holds the meta page title
 *
 * @var string
 * @access private
 */

    var $_meta_page_title;
    
/**
 * Holds the title of the page
 *
 * @var string
 * @access private
 */

    var $_page_title;

/**
 * Holds the image of the page
 *
 * @var string
 * @access private
 */

    var $_page_image;
    
/**
 * Holds the rel=”canonical” link to remove the duplication content
 * [#123]Two Different SEO link for one product 
 *
 * @var string
 * @access public
 */
    var $rel_canonical;

/**
 * Holds the filename of the content to be added to the page
 *
 * @var string
 * @access private
 */

    var $_page_contents;
    
/**
 * Holds the content of the box module groups
 *
 * @var string
 * @access private
 */

    var $_module_boxes = array();
    
/**
 * Holds the content of the content module groups
 *
 * @var string
 * @access private
 */

    var $_module_content = array();
    
/**
 * Holds the meta tags of the page
 *
 * @var array
 * @access private
 */

    var $_page_tags = array('generator' => array('TomatoCart -- Open Source Shopping Cart Solution'));
    
/**
 * Holds javascript filenames to be included in the page
 *
 * The javascript files must be plain javascript files without any PHP logic, and are linked to from the page
 *
 * @var array
 * @access private
 */

    var $_javascript_filenames = array('includes/general.js');
    
/**
 * Holds javascript filenames to be included in the head of the page
 *
 * The javascript files must be plain javascript files without any PHP logic, and are linked to from the page
 *
 * @var array
 * @access private
 */    
    
    var $_header_javascript_filenames = array('https://ajax.googleapis.com/ajax/libs/mootools/1.2.5/mootools-yui-compressed.js', 'ext/mootools/mootools_more.js');

/**
 * Holds javascript PHP filenames to be included in the page
 *
 * The javascript PHP filenames can consist of PHP logic to produce valid javascript syntax, and is embedded in the page
 *
 * @var array
 * @access private
 */

    var $_javascript_php_filenames = array();
    
/**
 * Holds javascript PHP filenames to be included in the head of the page
 *
 * The javascript PHP filenames can consist of PHP logic to produce valid javascript syntax, and is embedded in the page
 *
 * @var array
 * @access private
 */
    
    var $_header_javascript_php_filenames = array();
    
/**
 * Holds blocks of javascript syntax to embedd into the page
 *
 * Each block must contain its relevant <script> and </script> tags
 *
 * @var array
 * @access private
 */

    var $_javascript_blocks = array();
    
/**
 * Holds blocks of javascript syntax to embedd into the head of the page
 *
 * Each block must contain its relevant <script> and </script> tags
 *
 * @var array
 * @access private
 */
    
    var $_header_javascript_blocks = array();

/**
 * Holds style declarations to embedd into the page head
 *
 * @var array
 * @access private
 */

    var $_styles = array();

/**
 * Holds style sheet files to be included in the page
 *
 * @var array
 * @access private
 */

    var $_style_sheets = array();

/**
 * Defines if the requested page has a header
 *
 * @var boolean
 * @access private
 */

    var $_has_header = true;

/**
 * Defines if the requested page has a footer
 *
 * @var boolean
 * @access private
 */

    var $_has_footer = true;

/**
 * Defines if the requested page has box modules
 *
 * @var boolean
 * @access private
 */

    var $_has_box_modules = true;

/**
 * Defines if the requested page has content modules
 *
 * @var boolean
 * @access private
 */

    var $_has_content_modules = true;

/**
 * Defines if the requested page should display any debug messages
 *
 * @var boolean
 * @access private
 */

    var $_show_debug_messages = true;

/**
 * Setup the template class with the requested page module
 *
 * @param string $module The default page module to setup
 * @return object
 */

    function &setup($module) {
      global $osC_Template;
      
      $group = basename($_SERVER['SCRIPT_FILENAME']);

      if (($pos = strrpos($group, '.')) !== false) {
        $group = substr($group, 0, $pos);
      }

      if (empty($_GET) === false) {
        $first_array = array_slice($_GET, 0, 1);
        $_module = osc_sanitize_string(basename(key($first_array)));

        if (file_exists('includes/content/' . $group . '/' . $_module . '.php')) {
          $module = $_module;
        }
      }

      include('includes/content/' . $group . '/' . $module . '.php');

      $_page_module_name = 'osC_' . ucfirst($group) . '_' . ucfirst($module);
      $osC_Template = new $_page_module_name();
      $osC_Template->iniModules();

      require('includes/classes/actions.php');
      osC_Actions::parse();
      
      return $osC_Template;
    }
    
/**
 * Initialize Modules and preload all modules content
 *
 * @access public
 */
    
    function iniModules () {
      require_once('templates/' . $this->getCode() . '/template.php');
      
      $_template_info = 'osC_Template_' . $this->getCode();
      $object = new $_template_info();
      
      //initialize box modules
      if ($this->hasPageBoxModules()) {
        $this->osC_Modules_Boxes = new osC_Modules('boxes');
        $groups = $object->getGroups('boxes');
        if (is_array($groups) && !empty($groups)) {
          foreach ($groups as $group) {
            $content = $this->_iniGroupModules('boxes', $group);
            $this->_module_boxes[$group] = trim($content);
          }
        }
      }
      
      //initialize content modules
      if ($this->hasPageContentModules()) {
        $this->osC_Modules_Content = new osC_Modules('content');
        $groups = $object->getGroups('content');
        if (is_array($groups) && !empty($groups)) {
          foreach ($groups as $group) {
            $content = $this->_iniGroupModules('content', $group);
            
            $this->_module_content[$group] = trim($content);
          }
        }
      }
    }
    
/**
 * Load the group modules and store the content of the specified group
 * 
 * @param string $type module type "boxes" or "cotent"
 * @param string $group module group name  
 * @return string the content of the module group 
 */
    
    function _iniGroupModules($type, $group) {
      if ($type == 'boxes') {
        $modules = $this->osC_Modules_Boxes->getGroup($group);
      } else {
        $modules = $this->osC_Modules_Content->getGroup($group);
      }

      ob_start();
      foreach ($modules as $module) {
        $osC_Box = new $module();
        $osC_Box->initialize();

        if ($osC_Box->hasContent()) {
          if ($this->getCode() == DEFAULT_TEMPLATE) {
            include('templates/' . $this->getCode() . '/modules/' . $type . '/' . $osC_Box->getCode() . '.php');
          } else {
            if (file_exists('templates/' . $this->getCode() . '/modules/' . $type . '/' . $osC_Box->getCode() . '.php')) {
              include('templates/' . $this->getCode() . '/modules/' . $type . '/' . $osC_Box->getCode() . '.php');
            } else {
              include('templates/' . DEFAULT_TEMPLATE . '/modules/' . $type . '/' . $osC_Box->getCode() . '.php');
            }
          }
        }

        unset($osC_Box);
      }

      $content = ob_get_contents();
      ob_end_clean();
      
      return $content;
    }
    
/**
 * Return the content of the specified box Group
 * 
 * @param string $group module group name
 * @return string the content of the box group 
 */
    
    function getBoxGroup($group) {
      if (isset($this->_module_boxes[$group]) && !empty($this->_module_boxes[$group])) {
        return $this->_module_boxes[$group];
      }
      
      return false;
    }
    
/**
 * Return the content of the specified content Group
 * 
 * @param string $group module group name
 * @return string the content of the content group 
 */
   
    function getContentGroup($group) {
      if (isset($this->_module_content[$group]) && !empty($this->_module_content[$group])) {
        return $this->_module_content[$group];
      }
      
      return false;
    }
    
/**
 * Returns the template ID
 *
 * @access public
 * @return int
 */

    function getID() {
      if (isset($this->_template) === false) {
        $this->set();
      }

      return $this->_template_id;
    }

/**
 * Returns the template name
 *
 * @access public
 * @return string
 */

    function getCode($id = null) {
      if (isset($this->_template) === false) {
        $this->set();
      }

      if (is_numeric($id)) {
        foreach ($this->getTemplates() as $template) {
          if ($template['id'] == $id) {
            return $template['code'];
          }
        }
      } else {
        return $this->_template;
      }
    }

/**
 * Returns the page module name
 *
 * @access public
 * @return string
 */

    function getModule() {
      return $this->_module;
    }

/**
 * Returns the page group name
 *
 * @access public
 * @return string
 */

    function getGroup() {
      return $this->_group;
    }

/**
 * Returns the store logo
 *
 * @access public
 * @return string
 */

    function getLogo() {
      $directory = dir(DIR_WS_IMAGES);
      while (false !== ($entry = $directory->read())) {
        if (strpos($entry, 'logo_originals') !== false) {
          $filename = explode(".", $entry);
          $logo = DIR_WS_IMAGES . 'logo_' . $this->getCode() . '.' . $filename[1];

          if(file_exists($logo)){
            return $logo;
          }
        }
      }
      $directory->close();
      
      return DIR_WS_IMAGES . 'store_logo.png';
    }
  
/**
 * Returns the meta page title
 *
 * @access public
 * @return string
 */

    function getMetaPageTitle() {
      if (!empty($this->_meta_page_title)) {
        return $this->_meta_page_title;
      }
      
      return $this->_page_title;
    }
    
/**
 * Returns the title of the page
 *
 * @access public
 * @return string
 */

    function getPageTitle() {
      return $this->_page_title;
    }

/**
 * Returns the tags of the page separated by a comma
 *
 * @access public
 * @return string
 */

    function getPageTags() {
      $tag_string = '';

      foreach ($this->_page_tags as $key => $values) {
        $tag_string .= '<meta name="' . $key . '" content="' . rtrim(implode(', ', $values), ', ') . '" />' . "\n";
      }

      return $tag_string . "\n";
    }
    
/**
 * Check whether the module is installed
 *
 * @param string $code The code of the module
 * @param string $group The group name of moudle such as boxes, content, payment etc
 * @return bool
 */

    function isInstalled($code = '', $group = '') {
      return osC_Modules::isInstalled($code, $group);
    }

/**
 * Return the box modules assigned to the page
 *
 * @param string $group The group name of box modules to include that the template has provided
 * @return array
 */

    function getBoxModules($group) {
      if (isset($this->osC_Modules_Boxes) === false) {
        $this->osC_Modules_Boxes = new osC_Modules('boxes');
      }

      return $this->osC_Modules_Boxes->getGroup($group);
    }

/**
 * Return the content modules assigned to the page
 *
 * @param string $group The group name of content modules to include that the template has provided
 * @return array
 */

    function getContentModules($group) {
      if (isset($this->osC_Modules_Content) === false) {
        $this->osC_Modules_Content = new osC_Modules('content');
      }

      return $this->osC_Modules_Content->getGroup($group);
    }

/**
 * Returns the image of the page
 *
 * @access public
 * @return string
 */

    function getPageImage() {
      return $this->_page_image;
    }

/**
 * Returns the content filename of the page
 *
 * @access public
 * @return string
 */

    function getPageContentsFilename() {
      return $this->_page_contents;
    }

/**
 * Returns the javascript to link from or embedd to on the page
 *
 * @access public
 * @return string
 */

    function getJavascript() {
      if (!empty($this->_javascript_filenames)) {
        echo $this->_getJavascriptFilenames();
      }

      if (!empty($this->_javascript_php_filenames)) {
        $this->_getJavascriptPhpFilenames();
      }

      if (!empty($this->_javascript_blocks)) {
        echo $this->_getJavascriptBlocks();
      }
    }
    
/**
 * Returns the javascript to link from or embedd to on the head of the page
 *
 * @access public
 * @return string
 */

    function getHeaderJavascript() {
      if (!empty($this->_header_javascript_filenames)) {
        echo $this->_getHeaderJavascriptFilenames();
      }

      if (!empty($this->_header_javascript_php_filenames)) {
        $this->_getHeaderJavascriptPhpFilenames();
      }

      if (!empty($this->_header_javascript_blocks)) {
        echo $this->_getHeaderJavascriptBlocks();
      }
    }
  
/**
 * Returns the style sheet to link from or embedd to on the page
 *
 * @access public
 * @return string
 */

    function getStyleSheet() {
      if (!empty($this->_style_sheets)) {
        echo $this->getStyleSheets();
      }

      if (!empty($this->_styles)) {
        echo $this->getStyleDeclaration();
      }
    }
    
/**
 * Return all templates in an array
 *
 * @access public
 * @return array
 */

    function &getTemplates() {
      global $osC_Database;

      $templates = array();

      $Qtemplates = $osC_Database->query('select id, code, title from :table_templates');
      $Qtemplates->bindTable(':table_templates', TABLE_TEMPLATES);
      $Qtemplates->setCache('templates');
      $Qtemplates->execute();

      while ($Qtemplates->next()) {
        $templates[] = $Qtemplates->toArray();
      }

      $Qtemplates->freeResult();

      return $templates;
    }
      
/**
 * Checks to see if the page has a meta_page title
 *
 * @access public
 * @return boolean
 */

    function hasMetaPageTitle() {
      return !(empty($this->_meta_page_title) && empty($this->_page_title));
    }

/**
 * Checks to see if the page has a title set
 *
 * @access public
 * @return boolean
 */

    function hasPageTitle() {
      return !empty($this->_page_title);
    }
    
/**
 * Checks to see if the page has a meta tag set
 *
 * @access public
 * @return boolean
 */

    function hasPageTags() {
      return !empty($this->_page_tags);
    }

/**
 * Checks to see if the page has javascript to link to or embedd from
 *
 * @access public
 * @return boolean
 */

    function hasJavascript() {
      return (!empty($this->_javascript_filenames) || !empty($this->_javascript_php_filenames) || !empty($this->_javascript_blocks));
    }
    
/**
 * Checks to see if the page has javascript in the head tag to link to or embedd from
 *
 * @access public
 * @return boolean
 */

    function hasHeaderJavascript() {
      return (!empty($this->_header_javascript_filenames) || !empty($this->_header_javascript_php_filenames) || !empty($this->_header_javascript_blocks));
    }
    
/**
 * Checks to see if the page has style sheet to link to or embedd from
 *
 * @access public
 * @return boolean
 */

    function hasStyleSheet() {
      return (!empty($this->_style_sheets) || !empty($this->_styles));
    }
    
/**
 * Checks to see if the page has a footer defined
 *
 * @access public
 * @return boolean
 */

    function hasPageFooter() {
      return $this->_has_footer;
    }

/**
 * Checks to see if the page has a header defined
 *
 * @access public
 * @return boolean
 */

    function hasPageHeader() {
      return $this->_has_header;
    }

/**
 * Checks to see if the page has content modules defined
 *
 * @access public
 * @return boolean
 */

    function hasPageContentModules() {
      return $this->_has_content_modules;
    }

/**
 * Checks to see if the page has box modules defined
 *
 * @access public
 * @return boolean
 */

    function hasPageBoxModules() {
      return $this->_has_box_modules;
    }

/**
 * Checks to see if the page show display debug messages
 *
 * @access public
 * @return boolean
 */

    function showDebugMessages() {
      return $this->_show_debug_messages;
    }

/**
 * Sets the template to use
 *
 * @param string $code The code of the template to use
 * @access public
 */

    function set($code = null) {
    	//use the template box or template code to change the template
    	$set_template = null;
		  if ( (!empty($code)) || (isset($_GET['template']) && !empty($_GET['template'])) ) {
		  	if (!empty($code)) {
	  			$set_template = $code;
		  	}else {
		  		$set_template = $_GET['template'];
		  	}
		  	
		  	$_SESSION['change_template'] = true;
		  }
		  
			//there isn't any template set before or the default template is changed
		  if (!isset($_SESSION['change_template'])) {
		  	if ( (isset($_SESSION['template']) === false) || ($_SESSION['template'] != DEFAULT_TEMPLATE) ) {
		  		$set_template = DEFAULT_TEMPLATE;
		  	}
		  }

		  //the template need to be set in the session or changed
		  if ($set_template !== null) {
		  	$data = array();
		  	$data_default = array();
		  	
		  	foreach ($this->getTemplates() as $template) {
		  		if ($template['code'] == DEFAULT_TEMPLATE) {
		  			$data_default = array('id' => $template['id'], 'code' => $template['code']);
		  		} elseif ($template['code'] == $set_template) {
		  			$data = array('id' => $template['id'], 'code' => $template['code']);
		  		}
		  	}
		  	
		  	if (empty($data)) {
		  		$data =& $data_default;
		  	}
		  	
		  	$_SESSION['template'] =& $data;
		  }

      $this->_template_id =& $_SESSION['template']['id'];
      $this->_template =& $_SESSION['template']['code'];
    }
    
/**
 * Sets the meta page title
 *
 * @param string $title The title of the page
 * @access public
 */

    function setMetaPageTitle($title) {
      $this->_meta_page_title = $title;
    }
    
/**
 * Sets the title of the page
 *
 * @param string $title The title of the page to set to
 * @access public
 */

    function setPageTitle($title) {
      $this->_page_title = $title;
    }
    
/**
 * Sets the image of the page
 *
 * @param string $image The image of the page to set to
 * @access public
 */

    function setPageImage($image) {
      $this->_page_image = $image;
    }

/**
 * Sets the content of the page
 *
 * @param string $filename The content filename to include on the page
 * @access public
 */

    function setPageContentsFilename($filename) {
      $this->_page_contents = $filename;
    }

/**
 * Adds a tag to the meta keywords array
 *
 * @param string $key The keyword for the meta tag
 * @param string $value The value for the meta tag using the key
 * @access public
 */

    function addPageTags($key, $value) {
      $key = htmlspecialchars($key);
      $value = htmlspecialchars($value);
      
      $this->_page_tags[$key][] = $value;
    }

/**
 * Adds a javascript file to link to
 *
 * @param string $filename The javascript filename to link to
 * @access public
 */

    function addJavascriptFilename($filename) {
      if (!in_array($filename, $this->_javascript_filenames)) {
          $this->_javascript_filenames[] = $filename;
      }
    }
    
/**
 * Adds a javascript file to link to head tag of the page
 *
 * @param string $filename The javascript filename to link to
 * @access public
 */

    function addHeaderJavascriptFilename($filename) {
      if (!in_array($filename, $this->_header_javascript_filenames)) {
          $this->_header_javascript_filenames[] = $filename;
      }
    }

/**
 * Adds a PHP based javascript file to embedd on the page
 *
 * @param string $filename The PHP based javascript filename to embedd
 * @access public
 */

    function addJavascriptPhpFilename($filename) {
      $this->_javascript_php_filenames[] = $filename;
    }
    
/**
 * Adds a PHP based javascript file to embedd on the head of the page
 *
 * @param string $filename The PHP based javascript filename to embedd
 * @access public
 */

    function addHeaderJavascriptPhpFilename($filename) {
      $this->_header_javascript_php_filenames[] = $filename;
    }

/**
 * Adds javascript logic to the page
 *
 * @param string $javascript The javascript block to add on the page
 * @access public
 */

    function addJavascriptBlock($javascript) {
      $this->_javascript_blocks[] = $javascript;
    }
    
/**
 * Adds javascript logic to the page
 *
 * @param string $javascript The javascript block to add on the head of the page
 * @access public
 */

    function addHeaderJavascriptBlock($javascript) {
      $this->_header_javascript_blocks[] = $javascript;
    }
  
  /**
   * Adds a stylesheet to the page
   *
   * @param string  $url  URL to the style sheet
   */
  function addStyleSheet($url) {
    if ( !in_array($url, $this->_style_sheets) ) {
      $this->_style_sheets[] = $url;
    }
  }
  
  /**
   * Return the stylesheet linked to the page
   *
   * @return string
   */
  function getStyleSheets() {
    $css_files = '';

    if ( !empty($this->_style_sheets) ) {
      foreach ($this->_style_sheets as $style_sheet) {
        $css_files .= '<link rel="stylesheet" type="text/css" href="' . $style_sheet . '" />' . "\n";
      }
    }
    
    return $css_files;
  }
  
  /**
   * Adds a stylesheet declaration to the page
   *
   * @param string  $content   Style declarations
   */
  function addStyleDeclaration($content) {
    if ( !in_array($content, $this->_styles) ) {
      $this->_styles[] = $content;
    }
  }
    
  /**
   * Return the stylesheet declaration
   *
   * @return string
   */
  function getStyleDeclaration() {
    $css = '<style type="text/css">' . "\n";
    
    if ( !empty($this->_styles) ) {
      $css .= implode("\n", $this->_styles);
    }
    
    $css .= '</style>' . "\n";
    
    return $css;
  }
  
/**
 * Returns the javascript filenames to link to on the page
 *
 * @access private
 * @return string
 */

    function _getJavascriptFilenames() {
      $js_files = '';

      foreach ($this->_javascript_filenames as $filenames) {
        $js_files .= '<script language="javascript" type="text/javascript" src="' . $filenames . '"></script>' . "\n";
      }

      return $js_files;
    }
    
/**
 * Returns the javascript filenames to link to on head of the page
 *
 * @access private
 * @return string
 */

    function _getHeaderJavascriptFilenames() {
      $js_files = '';

      foreach ($this->_header_javascript_filenames as $filenames) {
        $js_files .= '<script language="javascript" type="text/javascript" src="' . $filenames . '"></script>' . "\n";
      }

      return $js_files;
    }

/**
 * Returns the PHP javascript files to embedd on the page
 *
 * @access private
 */

    function _getJavascriptPhpFilenames() {
      foreach ($this->_javascript_php_filenames as $filenames) {
        include($filenames);
      }
    }
    
/**
 * Returns the PHP javascript files to embedd on the head of the page
 *
 * @access private
 */

    function _getHeaderJavascriptPhpFilenames() {
      foreach ($this->_header_javascript_php_filenames as $filenames) {
        include($filenames);
      }
    }
    
    
    

/**
 * Returns javascript blocks to add to the page
 *
 * @access private
 * @return string
 */

    function _getJavascriptBlocks() {
      return implode("\n", $this->_javascript_blocks);
    }
    
/**
 * Returns javascript blocks to add to the head of the page
 *
 * @access private
 * @return string
 */

    function _getHeaderJavascriptBlocks() {
      return implode("\n", $this->_header_javascript_blocks);
    }
  }
?>
