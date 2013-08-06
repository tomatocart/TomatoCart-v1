<?php
/*
  $Id: email.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  
  require('includes/classes/email_account.php');
  require('includes/classes/email_accounts.php');
  
  class toC_Json_Email {

//BEGIN: Main Panel
    function loadAccountsTree() {
      global $toC_Json, $osC_Database;
      
      $Qaccounts = $osC_Database->query('select * from :table_email_accounts where user_id = :user_id order by accounts_id asc');
      $Qaccounts->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccounts->bindInt(':user_id', $_SESSION['admin']['id']);
      $Qaccounts->execute();
      
      $nodes = array();      
      while ( $Qaccounts->next() ) {
        $children = toC_Email_Accounts_Admin::getMailBoxNodes($Qaccounts->valueInt('accounts_id'), 0);
       
        $nodes[] = array('id' => $Qaccounts->valueInt('accounts_id'),
                         'text' => $Qaccounts->value('accounts_email'),
                         'iconCls' => 'icon-folder-account-record',
                         'expanded' => true,
                         'type' => 'account',
                         'children' => $children,
                         'protocol' => $Qaccounts->value('type'));
      }
      
      echo $toC_Json->encode($nodes);
    }
    
    function listMessages() {
      global $toC_Json, $osC_Database;

      $max_execution_time = ini_get('max_execution_time');
      ini_set('max_execution_time', 1800);
      
      $start = empty($_REQUEST['start']) ? 0 : $_REQUEST['start']; 
      $limit = empty($_REQUEST['limit']) ? MAX_DISPLAY_SEARCH_RESULTS : $_REQUEST['limit']; 
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      if ( (isset($_REQUEST['check_email']) && ($_REQUEST['check_email'] == 'true')) || ($toC_Email_Account->isImap()) ) {
        $toC_Email_Account->fetchEmail($_REQUEST['folders_id']);
      }
      
      $Qmessages = $osC_Database->query('select * from :table_email_messages where accounts_id = :accounts_id and folders_id = :folders_id');
      $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessages->bindInt(':accounts_id', $toC_Email_Account->getAccountId());
      $Qmessages->bindInt(':folders_id', $_REQUEST['folders_id']);
      
      if ( isset($_REQUEST['search']) && !empty($_REQUEST['search']) ) {
        $Qmessages->appendQuery('and (subject like :subject or from_address like :from)');
        $Qmessages->bindValue(':subject', '%'. $_REQUEST['search'] . '%');
        $Qmessages->bindValue(':from', '%'. $_REQUEST['search'] . '%');  
      }
      
      $Qmessages->appendQuery('order by id desc');      
      $Qmessages->setExtBatchLimit($start, $limit);
      $Qmessages->execute();
      
      $day_start = mktime(0, 0, 0);
      $day_end = mktime(0, 0, 0, date('m'), date('d') + 1);
      
      $records = array();
      while ( $Qmessages->next() ) {
        $date = $Qmessages->value('udate');
        if ($date > $day_start && $date < $day_end) {
          $date = date('H:i', $date);
        } else {
          $date = osC_DateTime::getShort(osC_DateTime::fromUnixTimestamp($date));
        }
        
        $records[] = array(
        'id' => $Qmessages->valueInt('id'),
        'fetch_time' => $Qmessages->value('fetch_timestamp'),
        'attachments' => $Qmessages->valueInt('attachments'),
        'icon' => ($Qmessages->valueInt('attachments') == 1) ? osc_icon('email_attachment.png') : '',
        'new' => $Qmessages->valueInt('new'),
        'subject' => $Qmessages->value('subject'),
        'from_address' => htmlentities($Qmessages->value('from_address'), ENT_QUOTES, 'UTF-8'),
        'size' => $Qmessages->valueInt('size'),
        'sender' => $Qmessages->value('reply_to'),
        'date' => $date,
        'priority' => $Qmessages->valueInt('priority'),
        'messages_flag' => $Qmessages->valueInt('messages_flag'));
      } 
      
      $response = array(EXT_JSON_READER_TOTAL => $Qmessages->getBatchSize(),
                        EXT_JSON_READER_ROOT => $records,
                        'unseen' => toC_Email_Accounts_Admin::getNewMessagesAmount($_REQUEST['accounts_id'], $_REQUEST['folders_id']));
      
      ini_set('max_execution_time', $max_execution_time);
      
      echo $toC_Json->encode($response);
    }
    
    function loadMessage() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qmessage = $osC_Database->query('select a.accounts_email, b.accounts_id, b.fetch_timestamp, b.new from :table_email_accounts a, :table_email_messages b where a.accounts_id = b.accounts_id and b.id = :id');
      $Qmessage->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':id', $_REQUEST['id']);
      $Qmessage->execute();

      if ($Qmessage->numberOfRows() === 1) {
        $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($Qmessage->valueInt('accounts_id') . $Qmessage->value('accounts_email')) . '/messages/' . md5($_REQUEST['id'] . $Qmessage->valueInt('fetch_timestamp')) . '.php';
        
        $data = array();
        if (file_exists($file)) {
          require($file);
          
          $data = $cacheFile;
        } 
        
        $new = $Qmessage->valueInt('new');
        if ($new == 1) {
          toC_Email_Accounts_Admin::updateCachedMessageStatus($_REQUEST['id'], 0);
        }
        
        $response = array('success' => true,
                          'data' => $data,
                          'unseen' => toC_Email_Accounts_Admin::getNewMessagesAmount($_REQUEST['accounts_id'], $_REQUEST['folders_id']));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);  
    }
    
    function deleteMessage() {
      global $toC_Json, $osC_Language;

      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
        
      if ($toC_Email_Account->deleteMessage($_REQUEST['id'])) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);           
    }
    
    function deleteMessages() {
      global $toC_Json, $osC_Language;

      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      $batch = explode(',', $_REQUEST['batch']);
      
      $error = false;
      foreach($batch as $id){
        if (!$toC_Email_Account->deleteMessage($id)) {
          $error = true;
          break;
        }
      }
      
      if ($error == true) {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      } else {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      }
      
      echo $toC_Json->encode($response);
    }    
    
    function moveMessages() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      $batch = explode(',', $_REQUEST['batch']);
      foreach ($batch as $id) {
        if ( !$toC_Email_Account->moveMessage($id, $_REQUEST['target_folders_id']) ) {
          $error = true;
          break;
        }
      }
       
      if ($error === false) {
        $response = array('success' => true ,'feedback' => $osC_Language->get('ms_success_action_performed'), 'target_unseen' => toC_Email_Accounts_Admin::getNewMessagesAmount($_REQUEST['accounts_id'], $_REQUEST['target_folders_id']));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));               
      }
      
      echo $toC_Json->encode($response);
    }
//END: Main Panel

//BEGIN: Account    
    function listAccounts() {
      global $toC_Json, $osC_Database;
      
      $user_id = $_SESSION['admin']['id'];
      
      $Qaccounts = $osC_Database->query('select accounts_id, host, type, accounts_email, accounts_name from :table_email_accounts where user_id = :user_id');
      $Qaccounts->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccounts->bindInt(':user_id', $user_id);
      $Qaccounts->execute();
      
      $records = array();     
      while ( $Qaccounts->next() ) {
        $records[] = array('accounts_id' => $Qaccounts->valueInt('accounts_id'),
                           'accounts_email' => $Qaccounts->value('accounts_email'),
                           'accounts_name' => $Qaccounts->value('accounts_name'),
                           'incoming_mail_type' => $Qaccounts->value('type'),
                           'incoming_mail_host' => $Qaccounts->value('host'));                             
      }
       
      $response = array(EXT_JSON_READER_ROOT => $records);
                        
      echo $toC_Json->encode($response);
    }
    
    function saveAccount() {
      global $toC_Json, $osC_Language;
      
      $error = false;
      $feedback = '';
      
      $accounts_id = (isset($_REQUEST['accounts_id']) && !empty($_REQUEST['accounts_id'])) ? ($_REQUEST['accounts_id']) : null;
      
      $data['user_id'] = $_SESSION['admin']['id'];

      $data['accounts_name'] = isset($_REQUEST['accounts_name']) ? $_REQUEST['accounts_name'] : '';
      $data['accounts_email'] = isset($_REQUEST['accounts_email']) ? $_REQUEST['accounts_email'] : '';
      $data['signature'] = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : '';
      
      $data['type'] = $_REQUEST['type'];
      $data['host'] = $_REQUEST['host'];
      $data['port'] = $_REQUEST['port'];
      $data['username'] = $_REQUEST['username'];
      $data['password'] = $_REQUEST['password'];
      $data['sent'] = $_REQUEST['sent'];
      $data['drafts'] = $_REQUEST['drafts'];
      $data['trash'] = $_REQUEST['trash'];
      $data['save_copy_on_server'] = (isset($_REQUEST['save_copy_on_server']) && $_REQUEST['save_copy_on_server'] == 'on')  ? 1 : 0;
      $data['mbroot'] = isset($_REQUEST['mbroot']) ? imap::utf7_imap_encode($_REQUEST['mbroot']) : 'INBOX';
      $data['use_ssl'] = (isset($_REQUEST['use_ssl']) && $_REQUEST['use_ssl'] == 'on')  ? 1 : 0;
      $data['novalidate_cert'] = (isset($_REQUEST['novalidate_cert']) && $_REQUEST['novalidate_cert'] == 'on')  ? 1 : 0;
      $data['examine_headers'] = (isset($_REQUEST['examine_headers']) && $_REQUEST['examine_headers'] == 'on')  ? 1 : 0;
      
      $data['use_system_mailer'] = (isset($_REQUEST['use_system_mailer']) && $_REQUEST['use_system_mailer'] == 'on')  ? 1 : 0;
      $data['smtp_host'] = isset($_REQUEST['smtp_host']) ? $_REQUEST['smtp_host'] : '';
      $data['smtp_port'] = isset($_REQUEST['smtp_port']) ? $_REQUEST['smtp_port'] : 25;
      $data['smtp_encryption'] = isset($_REQUEST['smtp_encryption']) ? $_REQUEST['smtp_encryption'] : '';
      $data['smtp_username'] = isset($_REQUEST['smtp_username']) ? $_REQUEST['smtp_username'] : '';
      $data['smtp_password'] = isset($_REQUEST['smtp_password']) ? $_REQUEST['smtp_password'] : '';               
      
      if (toC_Email_Accounts_Admin::checkEmailAccount($data['username'], $accounts_id)) {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_email_account_already_exist'));
      } else {
        if ($accounts_id == null) {
          $error_info = null;
          
          if( toC_Email_Accounts_Admin::checkEmailAccountOnSever($data, $error_info) === false) {
            $error = true;
            
            $response = array('success' => false, 'feedback' => sprintf($osC_Language->get('ms_error_connect_server_failed'), $error_info));
          }
        }
        
        if ($error === false) {
          $accounts_id = toC_Email_Accounts_Admin::saveAccount($accounts_id, $data);
          
          $node = array('id' => $accounts_id,
                        'text' => $data['accounts_email'],
                        'iconCls' => 'icon-folder-account-record',
                        'expanded' => true,
                        'type' => 'account',
                        'children' => toC_Email_Accounts_Admin::getMailBoxNodes($accounts_id, 0),
                        'protocol' => $data['type']);
            
          $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'), 'accounts_node' => $node);          
        } 
      }
      
      echo $toC_Json->encode($response);
    }

    
    function loadAccount() {
      global $toC_Json;
      
      $account = toC_Email_Accounts_Admin::getData($_REQUEST['accounts_id']);   
      $account['old_password'] = $account['password'];
      $account['password'] = str_pad("", strlen($account['password']), '*');
      
      $response = array('success' => true, 'data' => $account);
      
      echo $toC_Json->encode($response);  
    }
    
    function deleteAccounts() {
      global $toC_Json, $osC_Language;
      
      $batch = explode(',', $_REQUEST['batch']);
      
      $error = false;
      
      foreach ($batch as $accounts_id) {
        $account = toC_Email_Accounts_Admin::getData($accounts_id);
        if (!toC_Email_Accounts_Admin::deleteAccount($account)) {
          $error = true;
          break;
        }
      }
      
      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
//BEGIN: Account

//BEGIN: Folder
    function addFolder() {
      global $toC_Json, $osC_Language;
            
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      $folders_id = $toC_Email_Account->addFolder($_REQUEST['accounts_id'], $_REQUEST['folders_id'], $_REQUEST['folders_name']);
      if ($folders_id !== false) {
        $response = array('success' => true, 'folders_id' => $folders_id);
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);  
    }
    
    function deleteFolder() {
      global $toC_Json, $osC_Language;
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      if ( $toC_Email_Account->deleteFolder($_REQUEST['accounts_id'], $_REQUEST['folders_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
    
    function emptyFolder() {
      global $toC_Json, $osC_Language;
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      if ( $toC_Email_Account->emptyFolder($_REQUEST['accounts_id'], $_REQUEST['folders_id']) ) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }

      echo $toC_Json->encode($response);
    }
//END: Folder
    
    function listComposerSenders() {
      global $toC_Json, $osC_Database;

      $Qaccounts = $osC_Database->query('select accounts_id, accounts_name, accounts_email, signature from :table_email_accounts where user_id = :user_id');
      $Qaccounts->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccounts->bindInt(':user_id', $_SESSION['admin']['id']);
      $Qaccounts->execute();
      
      $records = array();     
      while ( $Qaccounts->next() ) {    
        $records[] = array( 'accounts_id' => $Qaccounts->valueInt('accounts_id'),
                            'email_address' => htmlspecialchars($Qaccounts->value('accounts_name') . "<" .$Qaccounts->value('accounts_email') . ">", ENT_QUOTES, 'UTF-8'),
                            'signature' => $Qaccounts->value('signature'));           
      }
       
      echo $toC_Json->encode($records);
    }   
    
    function getImapFolders() {
      global $toC_Json, $osC_Language;

      $folders = toC_Email_Accounts_Admin::getImapFolders($_REQUEST['host'], $_REQUEST['port'], $_REQUEST['email'], $_REQUEST['password'], $_REQUEST['use_ssl'], $_REQUEST['novalidate_cert']);
      if ( $folders !== false ) {
        $response = array('success' => true, 'folders' => $folders);
      } else {
        $response = array('success' => false, 'feedback' => sprintf($osC_Language->get('ms_error_connect_server_failed'), $_REQUEST['host']));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function downloadAttachment() {
      global $toC_Json;
      
      $accounts_id = $_REQUEST['accounts_id'];
      $file_name = $_REQUEST['filename'];
      $id = $_REQUEST['id'];
      $number = $_REQUEST['number'];
      $fetch_time = $_REQUEST['fetch_time'];
      
      $account = toC_Email_Accounts_Admin::getData($accounts_id);
      
      $file_location = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $account['accounts_email']) . '/attachments' . '/' . md5($id . $fetch_time) . '-' . $number . '.php';
      if ( file_exists($file_location) ) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Length: ' . filesize($file_location));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        ob_clean();
        flush();
        readfile($file_location);
        
        exit;
      }
    }
    
    function forwardAttachments() {
      global $toC_Json, $osC_Database, $osC_Session;
      
      $data = array();
      
      $Qmessage = $osC_Database->query('select a.accounts_email, b.accounts_id, b.fetch_timestamp from :table_email_accounts a, :table_email_messages b where a.accounts_id = b.accounts_id and b.id = :id');
      $Qmessage->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':id', $_REQUEST['id']);

      if ($Qmessage->numberOfRows() == 1) {
        $accounts_id = $Qmessage->valueInt('accounts_id');
        $accounts_email = $Qmessage->value('accounts_email');
        $fetch_timestamp = $Qmessage->valueInt('fetch_timestamp');
        
        $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $accounts_email) . '/messages/' . md5($_REQUEST['id'] . $fetch_timestamp) . '.php';

        if (file_exists($file)) {
          require($file);
        
          $data = $cacheFile;   
        }

        if (sizeof($data['attachments']) > 0) {
          foreach ($data['attachments'] as $attachment) {
            $src_file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $accounts_email) . '/attachments' . '/' . md5($_REQUEST['id'] . $fetch_timestamp) . '-' . $attachment['number'] . '.php';
        
            if (file_exists($src_file)) {
              $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
              
              if (!file_exists($path)) {
                mkdir($path, 0777);
              }
              copy($src_file, $path . '/' . $attachment['name']);
            }
          }
        }
      }
      
      $response = array('success' => true);
      
      echo $toC_Json->encode($response);  
    }
    
    function loadDraft() {
      global $toC_Json, $osC_Database, $osC_Session;
      
      $data = array();
      
      $Qmessage = $osC_Database->query('select a.accounts_email, b.accounts_id, b.fetch_timestamp from :table_email_accounts a, :table_email_messages b where a.accounts_id = b.accounts_id and b.id = :id');
      $Qmessage->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':id', $_REQUEST['id']);

      if ($Qmessage->numberOfRows() == 1) {
        $accounts_id = $Qmessage->valueInt('accounts_id');
        $accounts_email = $Qmessage->value('accounts_email');
        $fetch_timestamp = $Qmessage->valueInt('fetch_timestamp');
        
        $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $accounts_email) . '/messages/' . md5($_REQUEST['id'] . $fetch_timestamp) . '.php';

        if (file_exists($file)) {
          require($file);
        
          $data = $cacheFile;   
        }

        if (sizeof($data['attachments']) > 0) {
          foreach ($data['attachments'] as $attachment) {
            $src_file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $accounts_email) . '/attachments' . '/' . md5($_REQUEST['id'] . $fetch_timestamp) . '-' . $attachment['number'] . '.php';
        
            if (file_exists($src_file)) {
              $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
              
              if (!file_exists($path)) {
                mkdir($path, 0777);
              }
              copy($src_file, $path . '/' . $attachment['name']);
            }
          }
        }
      }
      
      $response = array('success' => true, 'data' => $data);
      
      echo $toC_Json->encode($response);  
    }
    
    function saveDraft() {
      global $toC_Json, $osC_Language;
      
      $to = array();
      $emails = explode(';', $_REQUEST['to']);
      foreach($emails as $email) {
        if (!empty($email)) {
          $to[] = osC_Mail::parseEmail($email);
        }
      }
      
      $cc = array();
      if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
        $emails = explode(';', $_REQUEST['cc']);
        
        foreach($emails as $email) {
          if (!empty($email)) {
            $cc[] = osC_Mail::parseEmail($email);
          }
        }
      }
      
      $bcc = array();
      if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
        $emails = explode(';', $_REQUEST['bcc']);
        
        foreach($emails as $email) {
          if (!empty($email)) {
            $bcc[] = osC_Mail::parseEmail($email);
          }
        }
      }
      
      $attachments = array();
      if (isset($_REQUEST['attachments']) && !empty($_REQUEST['attachments'])) {
        $attachments = explode(';', $_REQUEST['attachments']);
      }
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      $data = array('accounts_id' => $toC_Email_Account->getAccountId(),
                    'id' => $_REQUEST['id'],
                    'to' => $to,
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'from' => $toC_Email_Account->getAccountName(),
                    'sender' => $toC_Email_Account->getAccountEmail(),
                    'subject' => $_REQUEST['subject'],
                    'reply_to' => $toC_Email_Account->getAccountEmail(),
                    'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                    'body' => $_REQUEST['body'],
                    'priority' => $_REQUEST['priority'],
                    'content_type' => $_REQUEST['content_type'],
                    'notification' => $_REQUEST['notification'],
                    'udate' => time(),
                    'date' =>  date('m/d/Y H:i:s'),
                    'fetch_timestamp' => time(),
                    'messages_flag' => EMAIL_MESSAGE_DRAFT,
                    'attachments' => $attachments);   
      
      if ($toC_Email_Account->saveDraft($data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function sendMail() {
      global $toC_Json, $osC_Language;
      
      $to = array();
      $emails = explode(';', $_REQUEST['to']);
      foreach($emails as $email) {
        if (!empty($email)) {
          $to[] = osC_Mail::parseEmail($email);
        }
      }
      
      $cc = array();
      if (isset($_REQUEST['cc']) && !empty($_REQUEST['cc'])) {
        $emails = explode(';', $_REQUEST['cc']);
        
        foreach($emails as $email) {
          if (!empty($email)) {
            $cc[] = osC_Mail::parseEmail($email);
          }
        }
      }
      
      $bcc = array();
      if (isset($_REQUEST['bcc']) && !empty($_REQUEST['bcc'])) {
        $emails = explode(';', $_REQUEST['bcc']);
        
        foreach($emails as $email) {
          if (!empty($email)) {
            $bcc[] = osC_Mail::parseEmail($email);
          }
        }
      }
      
      $attachments = array();
      if (isset($_REQUEST['attachments']) && !empty($_REQUEST['attachments'])) {
        $attachments = explode(';', $_REQUEST['attachments']);
      }
      
      $toC_Email_Account = new toC_Email_Account($_REQUEST['accounts_id']);
      
      $data = array('accounts_id' => $toC_Email_Account->getAccountId(),
                    'id' => $_REQUEST['id'],
                    'to' => $to,
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'from' => $toC_Email_Account->getAccountName(),
                    'sender' => $toC_Email_Account->getAccountEmail(),
                    'subject' => $_REQUEST['subject'],
                    'reply_to' => $toC_Email_Account->getAccountEmail(),
                    'full_from' => $toC_Email_Account->getAccountName() . ' <' . $toC_Email_Account->getAccountEmail() . '>',
                    'body' => $_REQUEST['body'],
                    'priority' => $_REQUEST['priority'],
                    'content_type' => $_REQUEST['content_type'],
                    'notification' => $_REQUEST['notification'],
                    'udate' => time(),
                    'date' =>  date('m/d/Y H:i:s'),
                    'fetch_timestamp' => time(),
                    'messages_flag' => EMAIL_MESSAGE_DRAFT,
                    'attachments' => $attachments);
     
      if ($toC_Email_Account->sendMail($data)) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);
    }
    
    function clearAttachmentsCache() {
      global $toC_Json, $osC_Language, $osC_Session;
      
      $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
      
      if ( file_exists($path) ) {
        $directory = new osC_DirectoryListing($path);
        
        foreach ( ($directory->getFiles()) as $file ) {
           @unlink($path . '/' . $file['name']);
        }
      }
      
      @rmdir($path);
      
      $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      
      echo $toC_Json->encode($response);
    }
      
    function listAttachments() {
      global $toC_Json, $osC_Session;
      
      $osC_DirectoryListing = new osC_DirectoryListing(DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID());
      $osC_DirectoryListing->setIncludeDirectories(false);
      
      $records = array();
      foreach ( ($osC_DirectoryListing->getFiles()) as $file ) {
         $records[] = array('name' => $file['name']);
      }
      
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
    
    function uploadAttachment() {
      global $toC_Json, $osC_Language, $osC_Session;
      
      $error = false;
      
      $path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID();
      if ( !file_exists($path) ) {
        if (!mkdir($path, 0777)) {
          $error = true;
        }
      }
      
      if ($error === false) {
        $attachment = new upload('file_upload', $path);
        
        if (!($attachment->exists() && $attachment->parse() && $attachment->save())) {
          $error = true;
        } 
      }

      if ($error === false) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));   
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      header('Content-type:text/html');
      
      echo $toC_Json->encode($response);
    }
    
    function removeAttachment() {
      global $toC_Json, $osC_Language, $osC_Session;

      $success = false;
      
      $file_path = DIR_FS_CACHE_ADMIN . 'emails/attachments/' . $osC_Session->getID() . '/' . $_REQUEST['name'];
      
      if (file_exists($file_path)) {
        if (@unlink($file_path)) {
          $success = true;
        }
      }
      
      if ($success == true) {
        $response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));   
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
      
      echo $toC_Json->encode($response);           
    }

    function updateMessageStatus() {
      global $toC_Json, $osC_Database;
      
      $new = ( isset($_REQUEST['is_read']) && ($_REQUEST['is_read'] == '1') ) ? 0 : 1; 
      
      if ( toC_Email_Accounts_Admin::updateCachedMessageStatus($_REQUEST['id'], $new) ) {
        $response = array('success' => true, 'unseen' => toC_Email_Accounts_Admin::getNewMessagesAmount($_REQUEST['accounts_id'], $_REQUEST['folders_id']));    
      } else {
        $response = array('success' => false);
      }
      
      echo $toC_Json->encode($response);
    }
    
    function printMessage() {
      global $osC_Language;
      
      $accounts_id = $_REQUEST['accounts_id'];
      $id = $_REQUEST['id'];
      $fetch_time = $_REQUEST['fetch_time'];
      $number = $_REQUEST['number'];
      $account = toC_Email_Accounts_Admin::getData($accounts_id);      
      
      $file = DIR_FS_CACHE_ADMIN . 'emails/' . md5($accounts_id . $account['accounts_email']) . '/messages' . '/' . md5($id . $fetch_time) . '.php';
      
      if (file_exists($file)) {
        require($file);
        
        $output = '<table>';
        if ($cacheFile) {
          $output .= '<tr><td><b>' . $osC_Language->get('field_from') . '</b></td><td>' . $cacheFile['from'] . htmlspecialchars(' <') . $cacheFile['sender'] . htmlspecialchars('>').  '</td></tr>';
          $output .= '<tr><td><b>' . $osC_Language->get('field_Subject') . '</b></td><td>' . $cacheFile['subject'] . '</td></tr>';
          
          if (sizeof($cacheFile['to']) > 0) {
            $output .= '<tr><td><b>' . $osC_Language->get('field_to') . '</b></td><td>';
            
            $to = array();
            for ($i = 0; $i < sizeof($cacheFile['to']); $i ++) {
              $to[] = $cacheFile['to'][$i]['name'] . htmlspecialchars(' <') . $cacheFile['to'][$i]['email'] . htmlspecialchars('>');
            }
            $output .= implode(' ;', $to) . '</td></tr>';  
          }
          
          if (sizeof($cacheFile['cc']) > 0) {
            $output .= '<tr><td><b>' . $osC_Language->get('field_message_cc') . '</b></td><td>';
            
            $cc = array();
            for ($i = 0; $i < sizeof($cacheFile['cc']); $i ++) {
              $cc[] = $cacheFile['cc'][$i]['name'] . htmlspecialchars(' <') . $cacheFile['cc'][$i]['email'] . htmlspecialchars('>');
            }
            $output .= implode(' ;', $cc) . '</td></tr>';  
          }

          if (sizeof($cacheFile['bcc']) > 0) {
            $output .= '<tr><td><b>' . $osC_Language->get('field_message_bcc') . '</b></td><td>' . ':';
            
            $bcc = array();
            for ($i = 0; $i < sizeof($cacheFile['bcc']); $i ++) {
              $bcc[] = $cacheFile['bcc'][$i]['name'] . htmlspecialchars('<'). $cacheFile['bcc'][$i]['email'] . htmlspecialchars('>');
            }
            $output .= implode(' ;', $bcc) . '</td></tr>';  
          }
          
          $output .= '<tr><td><b>' . $osC_Language->get('field_date_received') . '</b></td><td>' . $cacheFile['date'] . '</td></tr>';
          
          if (is_array($cacheFile['attachments']) && (sizeof($cacheFile['attachments']) > 0)) {
            $output .= '<tr><td><b>' . $osC_Language->get('field_attachments') . '</b></td><td>';
            
            $attachments = array();
            foreach ($cacheFile['attachments'] as $attachment) {
              $attachments[] = $attachment['name'];
            }
            
            $output .= implode(' ;', $attachments) . '</td></tr>';  
          }
          
          $output .= '</table>';     
          $output .= '<p>' . $cacheFile['body'] . '</p>';
        } 
      } 
      
      echo $output;
    }
  
    function getCustomerInfo() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      $Qcustomer = $osC_Database->query('select customers_id, customers_default_address_id, customers_firstname, customers_lastname, customers_gender, customers_dob,customers_telephone, customers_fax, date_account_created from :table_customers where customers_email_address = :customers_email_address');
      $Qcustomer->bindTable(':table_customers', TABLE_CUSTOMERS);
      $Qcustomer->bindValue(':customers_email_address', $_REQUEST['from']);
      $Qcustomer->execute();
      
      $customers_default_address_id = $Qcustomer->value('customers_default_address_id');
      $customers_id = $Qcustomer->value('customers_id');
      
      $record = array();
      if ($customers_id != null) {
        
        if ( empty($customers_default_address_id) ) {
          $record[] = array('name' => $osC_Language->get('field_name'), 'value' => $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'));
          $record[] = array('name' => $osC_Language->get('field_gender'), 'value' => ($Qcustomer->value('customers_gender') == 'm' ? $osC_Language->get('gender_male') : $osC_Language->get('gender_female')));
          $record[] = array('name' => $osC_Language->get('field_birthday'), 'value' => osC_DateTime::getShort($Qcustomer->value('customers_dob')));
          $record[] = array('name' => $osC_Language->get('field_telephone'), 'value' => $Qcustomer->value('customers_telephone'));
          $record[] = array('name' => $osC_Language->get('field_fax'), 'value' => $Qcustomer->value('customers_fax'));
          $record[] = array('name' => $osC_Language->get('field_date_account_created'), 'value' => osC_DateTime::getShort($Qcustomer->value('date_account_created')));
        } else {
          $Qaddress = $osC_Database->query('select entry_firstname, entry_lastname, entry_gender,entry_company, entry_street_address, entry_telephone, entry_fax from :table_address_book where  address_book_id = :address_book_id');
          $Qaddress->bindTable(':table_address_book', TABLE_ADDRESS_BOOK);
          $Qaddress->bindInt(':address_book_id', $customers_default_address_id);   
          $Qaddress->execute();
          
          $record[] = array('name' => $osC_Language->get('field_name'), 'value' => $Qcustomer->value('customers_firstname') . ' ' . $Qcustomer->value('customers_lastname'));
          $record[] = array('name' => $osC_Language->get('field_gender'), 'value' => ($Qaddress->value('entry_gender') == 'm' ? $osC_Language->get('gender_male') : $osC_Language->get('gender_female')));
          $record[] = array('name' => $osC_Language->get('field_birthday'), 'value' => osC_DateTime::getShort($Qcustomer->value('customers_dob')));
          $record[] = array('name' => $osC_Language->get('field_telephone'), 'value' => $Qaddress->value('entry_telephone'));
          $record[] = array('name' => $osC_Language->get('field_fax'), 'value' => $Qaddress->value('entry_fax'));
          $record[] = array('name' => $osC_Language->get('field_date_account_created'), 'value' => osC_DateTime::getShort($Qcustomer->value('date_account_created')));
        }
        
      }
      $response = array(EXT_JSON_READER_ROOT => $record);
             
      echo $toC_Json->encode($response);   

    }
    
    function listOrders() {
      global $toC_Json, $osC_Database, $osC_Language;
      
      require_once('includes/classes/order.php');
      require_once('includes/classes/currencies.php');
      
      $osC_Currencies = new osC_Currencies_Admin();
      
      $Qorders = $osC_Database->query('select o.orders_id, o.date_purchased, s.orders_status_name, ot.text as order_total from :table_orders o, :table_orders_total ot, :table_orders_status s where o.orders_id = ot.orders_id and ot.class = "total" and o.orders_status = s.orders_status_id and o.customers_email_address = :customers_email_address and s.language_id = :language_id ');
      $Qorders->bindTable(':table_orders', TABLE_ORDERS);
      $Qorders->bindTable(':table_orders_total', TABLE_ORDERS_TOTAL);
      $Qorders->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
      $Qorders->bindValue(':customers_email_address', $_REQUEST['email']);
      $Qorders->bindInt(':language_id', $osC_Language->getID());
      $Qorders->execute();
      
      $records = array();
      while ( $Qorders->next() ) {
        $osC_Order = new osC_Order($Qorders->valueInt('orders_id'));
        
        $products_table = '<table width="100%">';
        foreach ($osC_Order->getProducts() as $product) {
          $product_info = $product['name'];
          
          if ( $product['type'] == PRODUCT_TYPE_GIFT_CERTIFICATE ) {
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_name') . ': ' . $product['senders_name'] . '</i></nobr>';
            
            if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('senders_email') . ': ' . $product['senders_email'] . '</i></nobr>';
            }
            
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_name') . ': ' . $product['recipients_name'] . '</i></nobr>';
            
            if ($product['gift_certificates_type'] == GIFT_CERTIFICATE_TYPE_EMAIL) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('recipients_email') . ': ' . $product['recipients_email'] . '</i></nobr>';
            }
            
            $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $osC_Language->get('messages') . ': ' . $product['messages'] . '</i></nobr>';
          }
          
          if ( isset($product['variants']) && is_array($product['variants']) && ( sizeof($product['variants']) > 0 ) ) {
            foreach ( $product['variants'] as $variants ) {
              $product_info .= '<br /><nobr>&nbsp;&nbsp;&nbsp;<i>' . $variants['groups_name'] . ': ' . $variants['values_name'] . '</i></nobr>';
            }
          }
          
          $products_table .= '<tr><td width="15">' . $product['quantity'] . '&nbsp;x&nbsp;' . '</td><td>' . $product_info . '</td><td valign="top" align="right" width="50">' . $osC_Currencies->displayPriceWithTaxRate($product['final_price'], $product['tax'], 1, $osC_Order->getCurrency(), $osC_Order->getCurrencyValue()) . '</td></tr>';
        }
        $products_table .= '</table>';
        
        $order_total = '<table width="100%">';
        foreach ( $osC_Order->getTotals() as $total ) {
          $order_total .= '<tr><td align="right">' . $total['title'] . '&nbsp;&nbsp;&nbsp;</td><td width="60" align="right">' . $total['text'] . '</td></tr>';
        }
        $order_total .= '</table>';
        
        $records[] = array('orders_id' => $Qorders->valueInt('orders_id'),
                           'order_total' => strip_tags($Qorders->value('order_total')),
                           'date_purchased' => osC_DateTime::getShort($Qorders->value('date_purchased')),
                           'orders_status_name' => $Qorders->value('orders_status_name'),
                           'products' => $products_table,
                           'totals' => $order_total);     
      }
        
      $response = array(EXT_JSON_READER_ROOT => $records);
      
      echo $toC_Json->encode($response);
    }
  }
?>