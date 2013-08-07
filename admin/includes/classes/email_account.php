<?php
/*
  $Id: email_account.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require('includes/classes/inbound_mail.php');
  
  class toC_Email_Account {
    var $_data = null,
        $_inbox = null,
        $_server_error_msg = null;

    function toC_Email_Account($account_id = 0) {
      global $osC_Database;
      
      $Qaccount = $osC_Database->query('select * from :table_email_accounts where accounts_id = :accounts_id');
      $Qaccount->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccount->bindInt(':accounts_id', $account_id);
      $Qaccount->execute();
      
      if ($Qaccount->numberOfRows() > 0) {
        $this->_data = $Qaccount->toArray();
      }
    }
    
    function isPop() {
      return ($this->_data['type'] == 'pop3');
    }
    
    function isImap() {
      return ($this->_data['type'] == 'imap');
    }
    
    function getAccountId() {
      return $this->_data['accounts_id'];
    }
    
    function getAccountName() {
      return $this->_data['accounts_name'];
    }
    
    function getAccountEmail() {
      return $this->_data['accounts_email'];
    }
      
    function getSignature() {
      return $this->_data['signature'];
    }
        
    function getType() {
      return $this->_data['type'];
    }
          
    function getHost() {
      return $this->_data['host'];
    }
            
    function getPort() {
      return $this->_data['port'];
    }
              
    function isUseSSL() {
      return ( ($this->_data['use_ssl'] == 1) ? true : false );
    }
                
    function isNoValidateCert() {
      return ( ($this->_data['novalidate_cert'] == 1) ? true : false );
    }
                  
    function getUsername() {
      return $this->_data['username'];
    }
                    
    function getPassword() {
      return $this->_data['password'];
    }
    
    function isSaveCopyOnServer() {
      return ( ($this->_data['save_copy_on_server'] == 1) ? true : false );
    }

    function getMBroot() {
      return $this->_data['mbroot'];
    }

    function getSent() {
      return $this->_data['sent'];
    }
  
    function getDrafts() {
      return $this->_data['drafts'];
    }
    
    function hasTrash() {
      return (isset($this->_data['trash']) && !empty($this->_data['trash']));
    }
    
    function getTrash() {
      return $this->_data['trash'];
    }
      
    function getSpam() {
      return $this->_data['spam'];
    }
                  
    function isUseSystemMailer() {
      return ( ($this->_data['use_system_mailer'] == 1) ? true : false );
    }
            
    function getSmtpSecure() {
      return $this->_data['smtp_encryption'];
    }
    
    function getSmtpHost() {
      return $this->_data['smtp_host'];
    }

    function getSmtpPort() {
      return $this->_data['smtp_port'];
    }
                
    function getSmtpEncryption() {
      return $this->_data['smtp_encryption'];
    }
                  
    function getSmtpUsername() {
      return $this->_data['smtp_username'];
    }
                    
    function getSmtpPassword() {
      return $this->_data['smtp_password'];
    }

    function connectMailServer($mailbox = 'INBOX') {
      $this->_inbox = new toC_InboundEmail(); 
      
      if ($this->_inbox->open($this->_data['host'], $this->_data['type'], $this->_data['port'], $this->_data['username'], $this->_data['password'], imap::utf7_imap_encode($mailbox), null, $this->_data['use_ssl'], $this->_data['novalidate_cert'])) {
        return true;  
      } else { 
        $this->_server_error_msg = $this->_inbox->last_error();  
        $this->_inbox->close();
        
        return false;
      }
    }
    
    function closeMailServer() {
      $this->_inbox->close();
    }
    
    function getServerLastError() {
      return $this->_server_error_msg;
    }

//BEGIN: Initialize Account
    function initializeAccount() {
      $dir_name = md5($this->_data['accounts_id'] . $this->_data['accounts_email']);
      
      if (!file_exists(DIR_FS_CACHE_ADMIN . 'emails')) {
        mkdir(DIR_FS_CACHE_ADMIN . 'emails', 0777);
      }
        
      if (!file_exists(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name)) {
        mkdir(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name);
      }
        
      if (!file_exists(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name . '/messages')) {
        mkdir(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name . '/messages');
      }
        
      if (!file_exists(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name . '/attachments')) {
        mkdir(DIR_FS_CACHE_ADMIN . 'emails/' . $dir_name . '/attachments');
      }
        
      if ( $this->isPop() ) {
        return $this->initializePop3Account();
      } elseif ( $this->isImap() ) {
        return $this->initializeImapAccount();
      }
    }
    
    function initializePop3Account() {
      global $osC_Language;
      
      $error = false;
      
      $folders = array(array('folders_name' => $osC_Language->get('pop3_mailbox_inbox'), 'folders_flag' => EMAIL_FOLDER_INBOX, 'sort_order' => 10),
                       array('folders_name' => $osC_Language->get('pop3_mailbox_sent_items'), 'folders_flag' => EMAIL_FOLDER_SENTBOX, 'sort_order' => 20),
                       array('folders_name' => $osC_Language->get('pop3_mailbox_drafts'), 'folders_flag' => EMAIL_FOLDER_DRAFT, 'sort_order' => 30),
                       array('folders_name' => $osC_Language->get('pop3_mailbox_spam'), 'folders_flag' => EMAIL_FOLDER_SPAM, 'sort_order' => 40),
                       array('folders_name' => $osC_Language->get('pop3_mailbox_trash'), 'folders_flag' => EMAIL_FOLDER_TRASH, 'sort_order' => 50));
      
      foreach ($folders as $folder) {
        if ( $this->saveCachedFolder($folder) === false ) {
          $error = true;
          
          break;
        }
      }
      
      if ($error === false) {
        return true;
      }
      
      return false;
    }
    
    function initializeImapAccount() {
      if ($this->connectMailServer()) {
        $mailboxes = $this->_inbox->get_mailboxes();
        $subscribed = $this->_inbox->get_subscribed();
        
        $subscribed_folders_names = array();
        foreach ($subscribed as $folder) {
          $subscribed_folders_names[] = imap::utf7_imap_decode($folder['name']);
        }
        
        if ( sizeof($mailboxes) > 0 ) {
          $mbroot = imap::utf7_imap_decode($mailboxes[0]['name']);
          
          if ( $this->_inbox->check_mbroot($mbroot) !== false ) {
            if ( $this->_data['mbroot'] != $mbroot ) {
              $this->updateMbroot($mbroot);
            }
          }
        }
    
        $sort_order = 10;
        foreach ($mailboxes as $mailbox) {
          $folder_name = imap::utf7_imap_decode($mailbox['name']);
          
          $folder['folders_name'] = $folder_name;
          switch ($folder['folders_name']) {
            case 'INBOX':
              $folder['folders_flag'] = EMAIL_FOLDER_INBOX; 
              break;
            case $this->_data['sent']:
              $folder['folders_flag'] = EMAIL_FOLDER_SENTBOX; break;
            case $this->_data['drafts']:
              $folder['folders_flag'] = EMAIL_FOLDER_DRAFT; break;
            case $this->_data['trash']:
              $folder['folders_flag'] = EMAIL_FOLDER_TRASH; break;
            default:
              $folder['folders_flag'] = EMAIL_FOLDER_INBOX; break;
          }
          
          $folder['accounts_id'] = $this->_data['accounts_id'];
          $folder['parent_id'] = toC_Email_Accounts_Admin::getParentId($this->_data, $folder_name, $mailbox['delimiter']);
          $folder['attributes'] = $mailbox['attributes'];
          $folder['subscribed'] = in_array($folder_name, $subscribed_folders_names) ? 1 : 0;
          $folder['delimiter'] = $mailbox['delimiter'];
          $folder['sort_order'] = $sort_order;
          $sort_order = $sort_order+ 10;
          
          if (!$this->saveCachedFolder($folder)) {
            $this->_inbox->close();
            
            return false;
          }
        }
        
        $this->_inbox->close();
        return true;
      }
      
      return false;
    }
      
    function updateMbroot($mbroot) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_email_accounts set mbroot = :mbroot where accounts_id = :accounts_id');
      $Qupdate->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qupdate->bindInt(':accounts_id', $this->getAccountId());
      $Qupdate->execute();
      
      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
//END: Initialize Account
    
//BEGIN: Fetch Email
    function fetchEmail($folders_id = null) {
      if ( $this->isPop() ) {
        $inbox = toC_Email_Accounts_Admin::getAccountDefaultInbox($this->getAccountId());
        
        $this->fetchPop3Email($inbox['folders_id']);
      } else if ( $this->isImap() ) {
        $folder = toC_Email_Accounts_Admin::getFolderData($folders_id);
          
        $this->fetchImapEmail($folders_id, $folder['folders_name']);
      }
    }
    
    function fetchPop3Email($folders_id) {
      $this->_inbox = new toC_InboundEmail();
      
      $uidls = $this->_inbox->pop3_getUIDL($this->_data);
      $uids = array_keys($uidls);
      $cached_uids = toC_Email_Accounts_Admin::getAllCachedUIDs($this->getAccountId());
      
      $uncached_uids = array();
      foreach ($uidls as $i => $uidl) {
        if (in_array($uidl, $cached_uids) === false) {
          $uncached_uids[] = $i;
        }
      }
      
      if ( sizeof($uncached_uids) > 0 ) {
        if ( $this->connectMailServer() ) {
          $sequence = implode(',', $uncached_uids);
          $overviews = imap_fetch_overview($this->_inbox->conn, $sequence, FT_UID);
          
          $RFC822 = new RFC822();
          foreach ($overviews as $overview) {
            $data = $this->_inbox->get_message($overview->uid);
            
            if ( !empty($data) ) {
              $data['messages_id'] = $overview->message_id;
              $data['accounts_id'] = $this->getAccountId();  
              $data['uid'] = $overview->uid;
              $data['uidl'] = $uidls[$overview->uid];
              $data['fetch_timestamp'] = time();
              $data['new'] = 1;
              
              $address = $RFC822->parse_address_list($data['reply_to']);
              $data['reply_to_email'] = isset($address[0]['email']) ? htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8') : '';
              
              $id = $this->saveMessage($folders_id, $data);
              $this->cacheMessage($id, $data);
            }
          }
          
          if ($this->isSaveCopyOnServer() == false) {
            $this->_inbox->delete($uids);
          }
          
          $this->closeMailServer();
          return true;
        } else {
          return false;
        }
      }
      
      return true;
    }
    
    function fetchImapEmail($folders_id, $mailbox = 'INBOX') {
      if ($this->connectMailServer($mailbox)) {
        $this->synchronizeFolders();
        
        $uids = $this->_inbox->get_message_uids(0, 0);
        $cached_uids = toC_Email_Accounts_Admin::getFolderCachedUIDs($this->getAccountId(), $folders_id);
        
        $uncached_uids = array();
        foreach ($uids as $uid) {
          if (!in_array($uid, $cached_uids)) {
            $uncached_uids[] = $uid;
          } else {
            $updated_uids[] = $uid;
          }  
        }
        
        $deleted_cached_uids = array();
        foreach ($cached_uids as $uid) {
          if (!in_array($uid, $uids)) {
            $deleted_cached_uids[] = $uid;
          } 
        }
  
        $RFC822 = new RFC822();
        if ( sizeof($uncached_uids) > 0 ) {
          $sequence = implode(',', $uncached_uids);
          $overviews = imap_fetch_overview($this->_inbox->conn, $sequence, FT_UID);
          
          foreach ($overviews as $overview) {
            $data = $this->_inbox->get_message($overview->uid);
            
            if ( !empty($data) ) {
              $data['messages_id'] = $overview->message_id;
              $data['accounts_id'] = $this->getAccountId();  
              $data['uid'] = $overview->uid;
              $data['uidl'] = $overview->uid;
              $data['fetch_timestamp'] = time();
              $data['new'] = ($overview->seen == 1) ? 0 :1;
              
              
              $address = $RFC822->parse_address_list($data['reply_to']);
              $data['reply_to_email'] = isset($address[0]['email']) ? htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8') : '';
              
              $id = $this->saveMessage($folders_id, $data);
              $this->cacheMessage($id, $data);
            }
          }
        }
        
        if ( sizeof($updated_uids) > 0 ) {
          $sequence = implode(',', $updated_uids);
          $overviews = imap_fetch_overview($this->_inbox->conn, $sequence, FT_UID);

          foreach ($overviews as $overview) {
            toC_Email_Accounts_Admin::updateImapMessageStatus($this->getAccountId(), $folders_id, $overview->uid, $overview->seen);
          }
        }
        
        if ( sizeof($deleted_cached_uids) > 0 ) {
          foreach ($deleted_cached_uids as $uid) {
            $id = toC_Email_Accounts_Admin::getMessageId($this->getAccountId(), $folders_id, $uid);
            
            $this->deleteCachedMessage($id);
          }
        }
        
        $this->closeMailServer();
      } else {
        return $this->_server_error_msg;
      }
    }
    
    function saveMessage($folders_id, $data) {
      global $osC_Database;
      
      $to = implode(',', $data['to']);

      $Qinsert = $osC_Database->query('insert into :table_email_messages (accounts_id, folders_id, uid, messages_id, new, subject, from_address, reply_to, size, udate, attachments, priority, to_address, content_type, content_transfer_encoding, fetch_timestamp, messages_flag) values(:accounts_id, :folders_id, :uid, :messages_id, :new, :subject, :from_address, :reply_to, :size, :udate, :attachments, :priority, :to_address, :content_type, :content_transfer_encoding, :fetch_timestamp, :messages_flag)');
      $Qinsert->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qinsert->bindInt(':accounts_id', $data['accounts_id']);
      $Qinsert->bindInt(':folders_id', $folders_id);
      $Qinsert->bindValue(':uid', $data['uidl']);
      $Qinsert->bindValue(':messages_id', $data['messages_id']);
      $Qinsert->bindValue(':new', $data['new']);
      $Qinsert->bindValue(':subject', trim($data['subject']));
      $Qinsert->bindValue(':from_address', trim($data['from']));
      $Qinsert->bindValue(':reply_to', trim($data['reply_to_email']));
      $Qinsert->bindInt(':size', $data['size']);
      $Qinsert->bindInt(':udate', $data['udate']);
      $Qinsert->bindValue(':attachments', $data['attachments']);
      $Qinsert->bindValue(':priority', $data['priority']);
      $Qinsert->bindValue(':to_address', trim($to));
      $Qinsert->bindValue(':content_type', $data['content_type']);
      $Qinsert->bindValue(':content_transfer_encoding', $data['content_transfer_encoding']);
      $Qinsert->bindInt(':fetch_timestamp', $data['fetch_timestamp']);
      $Qinsert->bindInt(':messages_flag', $data['messages_flag']);
      $Qinsert->execute();
      
      if ( !$osC_Database->isError() ) {
        $id = $osC_Database->nextID();
        
        return $id;
      }

      return false;
    }
    
    function cacheMessage ($id, $data)  {
      $data['subject'] = htmlspecialchars($data['subject'], ENT_QUOTES, 'UTF-8');
      $data['account_id'] = $this->_data['account_id'];
      $data['full_from'] = htmlspecialchars($data['from'], ENT_QUOTES, 'UTF-8');

      $RFC822 = new RFC822();
      $address = $RFC822->parse_address_list($data['from']);
      $data['sender'] = isset($address[0]['email']) ? htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8') : '';
      $data['from'] = isset($address[0]['personal']) ? htmlspecialchars($address[0]['personal'], ENT_QUOTES, 'UTF-8') : '';

      //to
      if ( !empty($data['to']) ) {
        $to = array();
        
        foreach($data['to'] as $address) {
          $address = $RFC822->parse_address_list($address);
          
          $to[] = array('email' => htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8'),
                        'name' => htmlspecialchars($address[0]['personal'], ENT_QUOTES, 'UTF-8'));
        }
        
        $data['to'] = $to;
      }
      
      //cc
      if ( !empty($data['cc']) ) {
        $cc = array();
          
        foreach ($data['cc'] as $address) {
          $address = $RFC822->parse_address_list($address);
          
          $cc[] = array('email' => htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8'),
                        'name' => htmlspecialchars($address[0]['personal'], ENT_QUOTES, 'UTF-8'));
        }
        $data['cc'] = $cc;
      }

      //bcc
      if ( !empty($data['bcc']) ) {
        $bcc = array();
        
        foreach ($data['bcc'] as $address) {
          $address=$RFC822->parse_address_list($address);
          
          $bcc[] = array('email' => htmlspecialchars($address[0]['email'], ENT_QUOTES, 'UTF-8'),
                         'name' => htmlspecialchars($address[0]['personal'], ENT_QUOTES, 'UTF-8'));
        }
        $data['bcc'] = $bcc;
      }

      $data['date'] = osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($data['udate']), true);
      $parts = array_reverse($this->_inbox->f("parts"));

      $html_alternative = false;
      for ($i = 0; $i < count($parts); $i++) {
        if( eregi('html', $parts[$i]['mime']) && (strtolower($parts[$i]['type']) == 'alternative' || strtolower($parts[$i]['type']) == 'related') ) {
          $html_alternative = true;
        }
      }

      $data['body'] = '';

      //attachments
      $attachments = array();

      if ( eregi('html', $data['content_type']) ) {
        $default_mime = 'text/html';
      } else {
        $default_mime = 'text/plain';
      }

      $part_count = count($parts);
      if ($part_count == 1) {
        //if there's only one part use the message parameters.
        if ( eregi('plain', $parts[0]['mime']) ) {
          $parts[0]['mime'] = $default_mime;
        }

        if( !empty($data['content_transfer_encoding']) ) {
          $parts[0]['transfer'] = $data['content_transfer_encoding'];
        }
      }

      while ( $part = array_shift($parts) ) {
        $mime = isset($part["mime"]) ? strtolower($part["mime"]) : $default_mime;

        //some clients just send html
        if ( $mime == 'html' ) {
          $mime = 'text/html';
        }

        if ( empty($data['body']) && (!eregi('attachment', $part["disposition"])) && (eregi('html', $mime) ||(eregi('plain', $mime) && (!$html_alternative || strtolower($part['type'])!='alternative')) || $mime == "text/enriched" || $mime == "unknown/unknown") ) {
          $part_body = $this->_inbox->view_part($data['uid'], $part["number"], $part["transfer"], $part["charset"]);

          switch ($mime) {
            case 'unknown/unknown':
            case 'text/plain':
              $uuencoded_attachments = $this->_inbox->extract_uuencoded_attachments($part_body);
              $part_body = Imap::text_to_html($part_body);

              for ($i = 0; $i < count($uuencoded_attachments); $i++) {
                $attachment = $uuencoded_attachments[$i];
                $attachment['number'] = $part['number'];
                
                unset($attachment['data']);
                $attachment['uuencoded_partnumber'] = $i+1;

                $attachments[] = $attachment;
              }

              break;

            case 'text/html':
              $part_body = Imap::convert_html($part_body);
              break;

            case 'text/enriched':
              $part_body = Imap::enriched_to_html($part_body);
              break;
          }

          $data['body'] .= $part_body;
        } else {
          $attachments[] = $part;
        }
      }

      //$data['event']=false;
      $data['attachments'] = array();
      $index = 0;
      for ($i = 0; $i < count($attachments); $i ++) {

        if ( $this->_inbox->part_is_attachment($attachments[$i]) ) {

          $attachment = $attachments[$i];

          $attachment['index'] = $index;
          $attachment['extension'] = Imap::get_extension($attachments[$i]["name"]);
          $data['attachments'][] = $attachment;
          
          $index++;
        }

        if ( !empty($attachments[$i]["id"]) ) {
          //when an image has an id it belongs somewhere in the text we gathered above so replace the
          //source id with the correct link to display the image.
          if ($attachments[$i]["id"] != '') {
            $tmp_id = $attachments[$i]["id"];
            
            if ( strpos($tmp_id,'>') ) {
              $tmp_id = substr($attachments[$i]["id"], 1,strlen($attachments[$i]["id"]) - 2);
            }
            $image_id = "cid:" . $tmp_id;

//            $url = $GO_MODULES->modules['email']['url']."attachment.php?account_id=".$account['id']."&mailbox=".urlencode($mailbox)."&amp;uid=".$uid."&amp;part=".$attachments[$i]["number"]."&amp;transfer=".$attachments[$i]["transfer"]."&amp;mime=".$attachments[$i]["mime"]."&amp;filename=".urlencode($attachments[$i]["name"]);
//            $data['body'] = str_replace($id, $url, $data['body']);
          }
        }
      }
      
      //save message cache
      $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($this->_data['accounts_id'] . $this->_data['accounts_email']) . '/messages/' . md5($id . $data['fetch_timestamp']) . '.php';
      if ($h = fopen($file, 'w')) {
        $cached_message = var_export($data, true);
        $date = date("r");
        $cached_message_string = '<?php //created: {' . $date . 'accounts_id: ' . $this->_data['accounts_id'] . ';;;id:' . $id . ';;;time_stamp:' . $data['fetch_timestamp'] . '}' . "\n\n" . '$cacheFile = ' . $cached_message . "\n\n" . '?>';
        
        fputs($h, $cached_message_string);
        fclose($h);
      }
      
      //save attachments cache
      $conn = $this->_inbox->conn;
      $msg_no = imap_msgno($conn, $data['uid']);
      $structure = imap_fetchstructure($conn, $msg_no);
      $file_attachment = DIR_FS_CACHE_ADMIN . 'emails/' . md5($this->_data['accounts_id'] . $this->_data['accounts_email']) . '/attachments/' . md5($id . $data['fetch_timestamp']);
      
      if ($structure->type == 1 && !empty($structure->parts)) {
        $this->cacheAttachments($msg_no, $structure->parts, 0, $file_attachment);     
      }
      
      return true;    
    }
    
    function cacheAttachments(&$msg_no, &$parts, $breadcrumb = '0', $file_attachment) {
      foreach ($parts as $k => $part) {
        $this_breadcrumb = $k + 1;
        if ($breadcrumb != '0') {
          $this_breadcrumb = $breadcrumb . '.' . $this_breadcrumb;
        }
        
        if (isset($part->parts) && !empty($part->parts)) {
          $this->cacheAttachments($msg_no, $part->parts, $this_breadcrumb, $file_attachment);
        } else if ($part->ifdisposition) {
          if (strtolower($part->disposition) == 'attachment' || ((strtolower($part->disposition) == 'inline') && $part->type != 0)) {
            $filename = Imap::handleEncodedFilename($part->dparameters[0]->value);
            $attachment_name = $file_attachment . '-' . $this_breadcrumb . '.php';
            
            $msg_part_raw = imap_fetchbody($this->_inbox->conn, $msg_no, $this_breadcrumb);
            $msg_part = Imap::handleTranserEncoding($msg_part_raw, '3');
            
            if ($h = fopen($attachment_name, 'wb')) {
              fwrite($h, $msg_part);
              fclose($h);
            }
          } 
        }
      } 
    }
//END: Fetch Email

//BEGIN: Send Email
    function sendMail($data) {
      global $osC_Session;
      
      $max_execution_time = ini_get('max_execution_time');
      
      ini_set('max_execution_time', 1800);
    
      $result = false;
      if ($this->isUseSystemMailer()) {
        $result = $this->sendSystemMail($data);
      } else {
        $result = $this->sendSmtpMail($data);
      }
      
      //clear attachments cache
      if ($result === true) {
        $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
        $osC_DirectoryListing = new osC_DirectoryListing($path);
        
        foreach ( ($osC_DirectoryListing->getFiles()) as $file ) {
          @unlink($path . '/' . $file['name']);
        }
      
        @rmdir($path);
      }
      
      ini_set('max_execution_time', $max_execution_time);
      
      return $result;
    }
  
    function sendSystemMail($data) {
      global $osC_Session;
      
      $mailer = new osC_Mail();
      $mailer->setFrom($this->getAccountName(), $this->getAccountEmail());
      
      foreach ($data['to'] as $to) {
        $mailer->addTo($to['name'], $to['email']);
      }
      
      if ($data['cc'] != null) {
        foreach ($data['cc'] as $cc) {
          $mailer->AddCC($cc['name'], $cc['email']);
        }
      }
          
      if ($data['bcc'] != null) {
        foreach ($data['bcc'] as $bcc) {
          $mailer->AddBCC($bcc['name'], $bcc['email']);
        }
      }
      
      $mailer->setSubject($data['subject']);
      if ($data['content_type'] == 'html') {
        $mailer->setBodyHTML($data['body']);
      } elseif ($data['content_type'] == 'text') {
        $mailer->setBodyPlain($data['body']);
      }
        
      $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
      $directory = new osC_DirectoryListing($path);
      if ( ($directory->getSize()) > 0 ) {
        foreach ( ($directory->getFiles()) as $file ) {
          $mailer->AddAttachment($path . '/' . $file['name']);
        }
      }
      
      if ($mailer->Send()) {
        $data['messages_flag'] = EMAIL_MESSAGE_SENT_ITEM;
        
        return $this->saveSentMessage($data);
      }
      
      return false;
    }
    
    function sendSmtpMail($data) {
      global $osC_Session;
      
      require_once(DIR_FS_CATALOG . 'ext/phpmailer/class.phpmailer.php');
      
      $mailer = new PHPMailer();
      
      $mailer->IsSMTP(); 
      $mailer->CharSet = "utf-8"; 
               
      $mailer->SMTPSecure = $this->getSmtpSecure();
      $mailer->Host = $this->getSmtpHost(); 
      $mailer->Port = $this->getSmtpPort();
      $mailer->SMTPAuth = true;
      $mailer->Username = $this->getSmtpUsername();
      $mailer->Password = $this->getSmtpPassword();
        
      $mailer->FromName = $this->getAccountName();
      $mailer->From = $this->getAccountEmail();
      
      if ($data['notification'] == 'true') {
        $mailer->ConfirmReadingTo = $this->getAccountEmail();
      }
      
      $mailer->Subject = $data['subject']; 
      $mailer->Body = $data['body'];
      $mailer->AltBody = "This is the body in plain text for non-HTML mail clients";
      
      $mailer->Priority = $data['priority'];
      $mailer->IsHTML(($data['content_type'] == 'html') ? true : false);
            
      foreach ($data['to'] as $to) {
        $mailer->AddAddress($to['email'], $to['name']);
      }
        
      if ($data['cc'] != null) {
        foreach ($data['cc'] as $cc) {
          $mailer->AddCC($cc['email'], $cc['name']);
        }
      }
      
      if ($data['bcc'] != null) {
        foreach ($data['bcc'] as $bcc) {
          $mailer->AddBCC($bcc['email'], $bcc['name']);
        }
      }
    
      $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
      $directory = new osC_DirectoryListing($path);
      foreach ( ($directory->getFiles()) as $file ) {
        $mailer->AddAttachment($path . '/' . $file['name']);
      }
      
      if ($mailer->Send()) {
        $data['messages_flag'] = EMAIL_MESSAGE_SENT_ITEM;
        
        return $this->saveSentMessage($data, $mailer);
      }
      
      return false;
    }

    
    function saveSentMessage($data, $mailer = null) {
      global $osC_Database;
      
	    if ($this->_data['sent'] != null) {
	      if ( $this->isPop() ) {
          $folder = toC_Email_Accounts_Admin::getFolderByName($this->_data['accounts_id'], $this->_data['sent']);
          
          if (empty($data['id']) || !is_numeric($data['id'])) {
            $id = $this->saveMessage($folder['folders_id'], $data);
            $data['id'] = $id;
            
            return $this->savePop3Message($data);
          } else {
            $Qupdate = $osC_Database->query('update :table_email_messages set folders_id = :folders_id, messages_flag = :messages_flag where id = :id');
            $Qupdate->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
            $Qupdate->bindInt(':folders_id', $folder['folders_id']);
            $Qupdate->bindInt(':messages_flag', EMAIL_MESSAGE_SENT_ITEM);
            $Qupdate->bindValue(':id', $data['id']);
            $Qupdate->execute();
            
            if ( !$osC_Database->isError() ) {
              return true;
            }
            
            return false;
          }
	      } elseif ($this->isImap()) {
	        if ($mailer != null) {
  	        $raw_text = $mailer->CreateHeader() . "\r\n" . $mailer->CreateBody() . "\r\n";
  	        
            if ( $this->connectMailServer($this->getDrafts()) ) {
              if ( $this->_inbox->append_message(imap::utf7_imap_encode($this->getSent()), $raw_text) ) {
                $this->closeMailServer();
                
                return true;
              }
            } 
            
            $this->closeMailServer();
            return false;
	        } else {
	          return $this->saveImapMessage($data, $this->getSent());
	        }
          
	      } 
	    }
	    
	    return true;
    }
//END: Send Email

//BEGIN: Save Draft
    function saveDraft($data) {
      global $osC_Session;
      
      $max_execution_time = ini_get('max_execution_time');
      
      ini_set('max_execution_time', 1800);
    
      if ($this->isPop()) {
        if (empty($data['id']) || !is_numeric($data['id'])) {
          $folder = toC_Email_Accounts_Admin::getFolderByName($this->_data['accounts_id'], $this->_data['drafts']);
          $id = $this->saveMessage($folder['folders_id'], $data);
          
          $data['id'] = $id;
        }
      
        $result = $this->savePop3Message($data);
      } else {
        $result = $this->saveImapMessage($data, $this->getDrafts());
      } 
      
      //clear attachments cache
      if ($result === true) {
        $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
        $osC_DirectoryListing = new osC_DirectoryListing($path);
        
        foreach ( ($osC_DirectoryListing->getFiles()) as $file ) {
          @unlink($path . '/' . $file['name']);
        }
      
        @rmdir($path);
      }
      
      ini_set('max_execution_time', $max_execution_time);
      
      return $result;
    }

    function savePop3Message($data) {
      global $osC_Session;
      
      $id = $data['id'];

      $email = toC_Email_Accounts_Admin::getCachedMessage($id);
      $email['to'] = $data['to'];
      $email['cc'] = $data['cc'];
      $email['bcc'] = $data['bcc'];
      $email['from'] = $data['from'];
      $email['sender'] = $data['sender'];
      $email['subject'] = $data['subject'];
      $email['reply_to'] = $data['reply_to'];
      $email['full_from'] = $data['full_from'];
      $email['body'] = $data['body'];
      $email['priority'] = $data['priority'];
      $email['content_type'] = $data['content_type'];
      $email['notification'] = $data['notification'];
      
      $attachments = array();
      if (!empty($data['attachments']) && is_array($data['attachments'])) {
        $index = 0;  
        $number = 2;
        
        $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
        $osC_DirectoryListing = new osC_DirectoryListing($path);
        foreach ($osC_DirectoryListing->getFiles() as $file) {
          $attachments[] = array('id' => 0,
                                 'number' => $number,
                                 'index' => $index,
                                 'name' => $file['name'],
                                 'human_size' => imap::format_size(filesize($path . '/' . $file['name'])));
          
          $index++;
          $number++;
        }
      }
      $email['attachments'] = $attachments;
      
      $cache_file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($this->getAccountId() . $this->getAccountEmail()) . '/messages/' . md5($id . $email['fetch_timestamp']) . '.php';
      if ($handle = fopen($cache_file, 'w')) {
        $text = '<?php //created: {' . date("r") . '}' . "\n\n" . '$cacheFile = ' . var_export($email, true) . "\n\n" . '?>';
        
        @fputs($handle, $text);
        @fclose($handle);
      }
      
      foreach ($attachments as $attachment) {
        $src_file = DIR_FS_CACHE_ADMIN . 'emails/' . 'attachments/' . $osC_Session->getID() . '/' . $attachment['name'];
        $dest_file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($this->getAccountId() . $this->getAccountEmail()) . '/attachments' . '/' . md5($id . $email['fetch_timestamp']) . '-' . $attachment['number'] . '.php';
        
        if ( file_exists($src_file) ) {
          @copy($src_file, $dest_file);
        }       
      }
      
      return true;
    }

    function saveImapMessage($data, $folder = null) {
      global $osC_Session;
      
      require_once(DIR_FS_CATALOG . 'ext/phpmailer/class.phpmailer.php');
      $mailer = new PHPMailer();
      
      $mailer->IsSMTP(); 
      $mailer->CharSet = "utf-8"; 
               
      $mailer->Host = $this->getSmtpHost(); 
      $mailer->Port = $this->getSmtpPort();
      $mailer->SMTPAuth = true;
      $mailer->Username = $this->getSmtpUsername();
      $mailer->Password = $this->getSmtpPassword();
        
      $mailer->FromName = $data['from'];
      $mailer->From = $data['sender'];
          
      if ($data['notification'] == 'true') {
        $mailer->ConfirmReadingTo = $this->getAccountEmail();
      }
      
      $mailer->Subject = $data['subject']; 
      $mailer->Body = $data['body'];
      $mailer->AltBody = "This is the body in plain text for non-HTML mail clients";

      $mailer->Priority = $data['priority'];
      $mailer->IsHTML(($data['content_type'] == 'html') ? true : false);
        
      foreach ($data['to'] as $to) {
        $mailer->AddAddress($to['email'], $to['name']);
      }
        
      if ($data['cc'] != null) {
        foreach ($data['cc'] as $cc) {
          $mailer->AddCC($cc['email'], $cc['name']);
        }
      }
      
      if ($data['bcc'] != null) {
        foreach ($data['bcc'] as $bcc) {
          $mailer->AddBCC($bcc['email'], $bcc['name']);
        }
      } 
    
      $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
      $osC_DirectoryListing = new osC_DirectoryListing($path);
      foreach ( $osC_DirectoryListing->getFiles() as $file ) {
        $mailer->AddAttachment($path . '/' . $file['name']);
      }

      $mailer->SetMessageType();
      $raw_text = $mailer->CreateHeader() . "\r\n" . $mailer->CreateBody() . "\r\n";
    
      if ( $this->connectMailServer($folder) ) {
        if ( $this->_inbox->append_message(imap::utf7_imap_encode($folder), $raw_text) ) {
          $this->closeMailServer();
          
          return true;
        }
      } 
      
      $this->closeMailServer();
      return false;
    }
//END: Save Draft
    
    function synchronizeFolders() {
      $mailboxes = $this->_inbox->get_mailboxes();
      $subscribed = $this->_inbox->get_subscribed();
      
      $subscribed_folders_names = array();
      foreach ($subscribed as $folder) {
        $subscribed_folders_names[] = imap::utf7_imap_decode($folder['name']);
      }
      
      $mailbox_names = array();
      foreach ($mailboxes as $mailbox) {
        $folders_name = imap::utf7_imap_decode($mailbox['name']);
        $mailbox_names[] = $folders_name;
        
        $folder = array();
        $folder['folders_name'] = $folders_name;
        $folder['folders_flag'] = EMAIL_FOLDER_INBOX;
        $folder['accounts_id'] = $this->_data['accounts_id'];
        $folder['parent_id'] = toC_Email_Accounts_Admin::getParentId($this->_data, $folders_name, $mailbox['delimiter']);
        $folder['attributes'] = $mailbox['attributes'];
        $folder['subscribed'] = in_array($folders_name, $subscribed_folders_names) ? 1 : 0;
        $folder['delimiter'] = $mailbox['delimiter'];
        
        $exist_folder = toC_Email_Accounts_Admin::getFolderByName($this->_data['accounts_id'], $folders_name);
        if ($exist_folder !== false) {
          $folder['folders_id'] = $exist_folder['folders_id'];
          $folder['folders_flag'] = $exist_folder['folders_flag'];
  
          toC_Email_Accounts_Admin::updateFolder($folder);
        } else {
          $this->saveCachedFolder($folder);
        }
      }
      
      $folders = toC_Email_Accounts_Admin::getFolders($this->_data['accounts_id']);
      foreach ($folders as $folder) {
        if (!in_array($folder['folders_name'], $mailbox_names)) {
          if (!toC_Email_Accounts_Admin::deleteFolder($this->_data, $folder['folders_id'])) {
            return false;
          }
        }
      }
    }

//BEGIN: Add Folder
    function addFolder($accounts_id, $parent_id, $folders_name) {
      $data = toC_Email_Accounts_Admin::getFolderData($parent_id);
      
      if ( $this->isImap() ) {
        if ($parent_id > 0) {
          $new_folders_name = $data['folders_name'] . $data['delimiter'] . $folders_name;
        } else {
          $new_folders_name = $this->_data['mbroot'] . $data['delimiter'] . $folders_name;
        }
        
        if ( !$this->addImapFolder($data['folders_name'], $new_folders_name) ) {
          return false;
        }
      }
      
      $data['parent_id'] = $parent_id;
      $data['folders_name'] = $data['folders_name'] . $data['delimiter'] . $folders_name;
      
      $folders_id = $this->saveCachedFolder($data);
      
      if ($folders_id !== false) {
        return $folders_id;
      }
      
      return false;
    }
      
    function addImapFolder($mailbox, $folders_name) {
      if ($this->connectMailServer($mailbox)) {
        return $this->_inbox->create_folder(imap::utf7_imap_encode($folders_name));
      } else {
        return $this->_sever_error_msg;
      }
    }
      
    function saveCachedFolder($folder) {
      global $osC_Database;
      
      $Qinsert = $osC_Database->query('insert into :table_email_folders (accounts_id, parent_id, folders_name, subscribed, sort_order, delimiter, folders_flag) values(:accounts_id, :parent_id, :folders_name, :subscribed, :sort_order, :delimiter, :folders_flag)');
      $Qinsert->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qinsert->bindInt(':accounts_id', $this->getAccountId());
      $Qinsert->bindInt(':parent_id', $folder['parent_id']);
      $Qinsert->bindValue(':folders_name',  $folder['folders_name']);
      $Qinsert->bindInt(':sort_order', $folder['sort_order']);
      $Qinsert->bindInt(':subscribed', isset($folder['subscribed']) ? $folder['subscribed'] : 1);
      $Qinsert->bindValue(':delimiter', isset($folder['delimiter']) ? $folder['delimiter'] : '.');
      $Qinsert->bindValue(':folders_flag', $folder['folders_flag']);
      $Qinsert->execute();

      if ( !$osC_Database->isError() ) {
        $folders_id = $osC_Database->nextID();
        
        return $folders_id;
      }
      
      return false;
    }
//END: Add Folder

//BEGIN: Empty Folder
    function emptyFolder($accounts_id, $folders_id) {
      global $osC_Database;
      
      $error = false;
      
      $Qmessages = $osC_Database->query('select * from :table_email_messages where accounts_id = :accounts_id and folders_id = :folders_id');
      $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessages->bindInt(':accounts_id', $accounts_id);
      $Qmessages->bindInt(':folders_id', $folders_id);
      $Qmessages->execute();
      
      while ( $Qmessages->next() ) {
        if (!$this->deleteMessage($Qmessages->valueInt('id'))) {
          $error = true; 
          
          break;
        }
      }
      
      if ( $error === false ) {
        return true;
      }
      
      return false;
    }
//END: Empty Folder

//BEGIN: delete Folder
    function deleteFolder($accounts_id, $folders_id) {
      global $osC_Database;
      
      if ($this->isPop()) {
        return $this->deleteCachedFolder($folders_id);
      } elseif ($this->isImap()) {
        $folder = toC_Email_Accounts_Admin::getFolderData($folders_id);
        $parent_folder = toC_Email_Accounts_Admin::getFolderData($folder['parent_id']);
        
        if ($this->deleteImapFolder($parent_folder['folders_name'], $folder['folders_name'])) {
          return $this->deleteCachedFolder($this->_data, $folders_id);
        }
      }
      
      return false;
    }
      
    function deleteImapFolder($parent_folders_name, $folders_name) {
      if ($this->connectMailServer($parent_folders_name)) {
        return $this->_inbox->delete_folder(imap::utf7_imap_encode($folders_name), imap::utf7_imap_encode($this->_data['mbroot']));
      }
    }
    
    function deleteCachedFolder($folders_id) {
      global $osC_Database;
      
      $error = false;
      
      //delete children folders
      $Qfolders = $osC_Database->query('select * from :table_email_folders where accounts_id = :accounts_id and parent_id = :parent_id');
      $Qfolders->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolders->bindInt(':accounts_id', $this->getAccountId());
      $Qfolders->bindInt(':parent_id', $folders_id);
      $Qfolders->execute();
      
      while ( $Qfolders->next() ) {
        if ( !$this->deleteCachedFolder($Qfolders->valueInt('folders_id')) ) {
          $error = true; 
          
          break;
        }
      }
      
      //delete messages
      if ($error === false) {
        $Qmessages = $osC_Database->query('select id, fetch_timestamp, attachments from :table_email_messages where folders_id = :folders_id');
        $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
        $Qmessages->bindInt(':folders_id', $folders_id);
        $Qmessages->execute();
        
        while ( $Qmessages->next() ) {
          if (!$this->deleteCachedMessage($Qmessages->valueInt('id'))) {
            $error = true; 
            
            break;
          }
        }
      }
      
      //delete folder
      if ($error === false) {
        $Qdelete = $osC_Database->query('delete from :table_email_folders where folders_id = :folders_id');
        $Qdelete->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
        $Qdelete->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
        $Qdelete->bindInt(':folders_id', $folders_id);
        $Qdelete->execute();
      
        if ( !$osC_Database->isError() ) {
          return true;
        }
      }
      
      return false;
    }
//END: delete Folder

//BEGIN: delete Message
    function deleteMessage($id) {
      $message = toC_Email_Accounts_Admin::getCachedMessage($id);
      $folder = toC_Email_Accounts_Admin::getFolderData($message['folders_id']);
      
      if ( $this->hasTrash() && ($folder['folders_flag'] != EMAIL_FOLDER_TRASH) ) {
        $trash = toC_Email_Accounts_Admin::getAccountDefaultTrash($this->getAccountId(), $this->getTrash());
        
        return $this->moveMessage($id, $trash['folders_id']);
      } else {
        if ( $this->isPop() ) {
          return $this->deleteCachedMessage($id);
        } elseif ( $this->isImap() ) {
          return $this->deleteImapMessage($id, $folder['folders_name'], $message['uid']);
        }
      }
    }
    
    function deleteImapMessage($id, $mailbox, $uid) {
      if ($this->connectMailServer($mailbox)) {
        if ( $this->_inbox->delete(array($uid)) ) {
          $this->closeMailServer();
          
          return $this->deleteCachedMessage($id);
        }
      }
      
      $this->closeMailServer();
      return false;
    }
          
    function deleteCachedMessage($id) {
      global $osC_Database;
      
      $Qmessage = $osC_Database->query('select fetch_timestamp, attachments from :table_email_messages where id = :id');
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':id', $id);
      $Qmessage->execute();
      
      if ( $Qmessage->numberOfRows() === 1 ) {
        $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($this->getAccountId() . $this->getAccountEmail()) . '/messages/' . md5($id . $Qmessage->valueInt('fetch_timestamp')) . '.php';
      
        if ( file_exists($file) ) {
          @unlink($file);
        }
      
        if ($Qmessage->valueInt('attachments') == 1) {
          $leading_string = md5($id . $Qmessage->value('fetch_timestamp'));  
        
          $path = DIR_FS_CACHE_ADMIN . 'emails/' . md5($account['accounts_id'] . $account['accounts_email']) . '/attachments';
          $directory = new osC_DirectoryListing($path);
          
          foreach ( ($directory->getFiles()) as $file ) {
            if ( strpos($file['name'], $leading_string) !== false ) {
              @unlink($path . '/' . $file['name']);
            }
          }
        }
        
        $Qdelete = $osC_Database->query('delete from :table_email_messages where id = :id');
        $Qdelete->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
        $Qdelete->bindInt(':id', $id);
        $Qdelete->execute();
        
        if ( !$osC_Database->isError() ) {
          return true;
        }
      }
      
      return false;
    }
//END: delete Message

//BEGIN: move message
    function moveMessage($id, $target_folders_id) {
      if ( $this->isPop() ) {
        return $this->moveCachedMessage($id, $target_folders_id);
      } elseif ( $this->isImap() ) {
        return $this->moveImapMessage($id, $target_folders_id);
      }
    }
    
    function moveImapMessage($id, $target_folders_id) {
      $message = toC_Email_Accounts_Admin::getCachedMessage($id);
      $src_folder = toC_Email_Accounts_Admin::getFolderData($message['folders_id']);
      $target_folder = toC_Email_Accounts_Admin::getFolderData($target_folders_id);
        
      if ($this->connectMailServer($src_folder['folders_name'])) {
        if ( $this->_inbox->move(imap::utf7_imap_encode($target_folder['folders_name']), array($message['uid'])) ) {
          $this->closeMailServer();
          
          return $this->moveCachedMessage($id, $target_folders_id);
        } 
      }

      $this->closeMailServer();
      return false;
    }
      
    function moveCachedMessage($id, $target_folders_id) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_email_messages set folders_id = :folders_id where id = :id');
      $Qupdate->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qupdate->bindInt(':id', $id);
      $Qupdate->bindInt(':folders_id', $target_folders_id);
      $Qupdate->execute();

      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
//END: move message    
  }