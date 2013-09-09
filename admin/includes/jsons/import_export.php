<?php
/*
  $Id: import_export.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

require('includes/classes/exporter.php');
require('includes/classes/importer.php');

class toC_Json_Import_Export {

  function export() {
    global $toC_Json, $osC_Language;
    
    $type = (strpos($_REQUEST['type'], 'customers') !== false) ? 'customers' : 'products';

    $param = array('type' => $type,
                   'csv_field_seperator' => $_REQUEST['seperator'],
                   'csv_field_enclosed' => $_REQUEST['enclosed'],
                   'file_type' => $_REQUEST['file_type'],
                   'compression_type' => $_REQUEST['compression']);

    $exporter = toC_Exporter::getExporter($param);
    $filename = $exporter->export();

    if ( file_exists($filename) ) {
      header("Pragma: no-cache");
      header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");      
      header("Content-Type: Application/octet-stream");
      header('Content-Disposition: attachment; filename="' . $exporter->getFileName() . '"');
      header("Content-Length: " . $exporter->getSize());
      readfile($exporter->getTempFile());

      $exporter->removeTempFile();

      exit;
    } 
    
    $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      
    echo $toC_Json->encode($response);
  }

  function import() {
    global $toC_Json, $osC_Language;
    
    $max_execution_time = ini_get('max_execution_time');
    ini_set('max_execution_time', 600);
    
    $type = (strpos($_REQUEST['type'], 'customers') !== false) ? 'customers' : 'products';

    $param = array('type' => $type,
                   'filename' => $_FILES['files'],
                   'image_file' => $_FILES['image_zip'],
                   'csv_field_seperator' => $_REQUEST['seperator'],
                   'csv_field_enclosed' => $_REQUEST['enclosed'],
    			   'csv_line_length' => $_REQUEST['line_length'],
                   'file_type' => $_REQUEST['file_type'],
                   'compression_type' => $_REQUEST['compression']);

    $importer = toC_Importer::getImporter($param);
    if ($importer->parse()) {
      $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'));
    } else {
      $response = array('success' => false ,'feedback' => $osC_Language->get('ms_error_action_not_performed'));
    }
    
    ini_set('max_execution_time', $max_execution_time);
    
    header('Content-Type: text/html');
    
    echo $toC_Json->encode($response);
  }
}
?>