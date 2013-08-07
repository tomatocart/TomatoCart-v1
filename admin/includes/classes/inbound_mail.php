<?php
/*
  $Id: inbound_mail.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once('external/groupoffice/imap.class.inc');

  class toC_InboundEmail extends imap {

    function pop3_open($data) {
      $socket = "tcp://" . $data['host'];
      $this->pop3socket = fsockopen($socket, $data['port']);
  
      $ret = trim(fgets($this->pop3socket, 1024));
      return true;
    }
    
    function pop3_sendCommand($command, $args='', $return=true) {
      $command .= " {$args}";
      $command = trim($command);
      $command .= "\r\n";
  
      fputs($this->pop3socket, $command);
  
      if($return) {
        $ret = trim(fgets($this->pop3socket, 1024));
        return $ret;
      }
    }
    
    function pop3_getUIDL($data) {
      $UIDLs = array();
      if($this->pop3_open($data)) {
        $this->pop3_sendCommand("USER", $data['username']);
        $this->pop3_sendCommand("PASS", $data['password']);
        $this->pop3_sendCommand("UIDL", '', false);
        fgets($this->pop3socket, 1024); 
        $UIDLs = array();
  
        $buf = '!';
  
        if (is_resource($this->pop3socket)) {
          while(!feof($this->pop3socket)) {
            $buf = fgets($this->pop3socket, 1024);
            if (trim($buf) == '.') {
              break;
            } else {
              $exUidl = explode(" ", $buf);
              $UIDLs[$exUidl[0]] = trim($exUidl[1]);
            }
            
          }
        }
        $this->pop3_cleanUp();
      }
      return $UIDLs;
    }
    
    function pop3_cleanUp() {
      fputs($this->pop3socket, "QUIT\r\n");
      $buf = fgets($this->pop3socket, 1024);
      fclose($this->pop3socket);
    }
  }
?>