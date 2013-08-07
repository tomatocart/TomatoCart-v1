<?php
/*
  $Id: email_accounts.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Email_Accounts_Admin {
  
//BEGIN: Account
    function getData($id) {
      global $osC_Database;
        
      $Qaccount = $osC_Database->query('select * from :table_email_accounts where accounts_id = :accounts_id');
      $Qaccount->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccount->bindInt(':accounts_id', $id);
      $Qaccount->execute();
        
      $result = $Qaccount->toArray();

      $Qaccount->freeResult();

      return $result;
    }
    
    function saveAccount($id = null, $data) {
      global $osC_Database, $osC_Language;
      
      if ($id != null) {
        $Qpassword = $osC_Database->query('select password from :table_email_accounts where accounts_id = :accounts_id');
        $Qpassword->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
        $Qpassword->bindInt(':accounts_id', $id);
        
        $password = $Qpassword->value('password');
        
        $fake_password = str_pad('', strlen($password), '*');
        if ($fake_password == $data['password']) {
          $data['password'] = $password;
        }
      }
      
      if ( $id != null ) {
	      $Qaccounts = $osC_Database->query('update :table_email_accounts set accounts_name = :accounts_name, accounts_email = :accounts_email, signature = :signature, port = :port, host = :host, save_copy_on_server = :save_copy_on_server, use_ssl = :use_ssl, novalidate_cert = :novalidate_cert, username = :username, password = :password, mbroot = :mbroot, sent = :sent, drafts = :drafts, trash = :trash, examine_headers = :examine_headers, use_system_mailer = :use_system_mailer, smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_encryption = :smtp_encryption, smtp_username = :smtp_username, smtp_password = :smtp_password where accounts_id = :accounts_id');
	      $Qaccounts->bindInt(':accounts_id', $id);
      } else {
        $Qaccounts = $osC_Database->query('insert into :table_email_accounts (user_id, accounts_name, accounts_email, type, host, port, save_copy_on_server, use_ssl, novalidate_cert, username, password, signature, mbroot, sent, drafts, trash, spam, examine_headers, use_system_mailer, smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password) values (:user_id, :accounts_name, :accounts_email, :type, :host, :port, :save_copy_on_server, :use_ssl, :novalidate_cert, :username, :password, :signature, :mbroot, :sent, :drafts, :trash, :spam, :examine_headers, :use_system_mailer, :smtp_host, :smtp_port, :smtp_encryption, :smtp_username, :smtp_password)');
        
        if ($data['type'] == 'pop3') {
          $data['sent'] = $osC_Language->get('pop3_mailbox_sent_items');
          $data['trash'] = $osC_Language->get('pop3_mailbox_trash');
          $data['drafts'] = $osC_Language->get('pop3_mailbox_drafts');
          $data['spam'] = $osC_Language->get('pop3_mailbox_spam');
        }
      }     
      
      $Qaccounts->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qaccounts->bindInt(':user_id', $data['user_id']);
      $Qaccounts->bindValue(':accounts_name', $data['accounts_name']);
      $Qaccounts->bindValue(':accounts_email', $data['accounts_email']);
      $Qaccounts->bindValue(':type', $data['type']);
      $Qaccounts->bindValue(':host', $data['host']);
      $Qaccounts->bindInt(':port', $data['port']);
      $Qaccounts->bindInt(':save_copy_on_server', $data['save_copy_on_server']);
      $Qaccounts->bindInt(':use_ssl', $data['use_ssl']);
      $Qaccounts->bindInt(':novalidate_cert', $data['novalidate_cert']);
      $Qaccounts->bindValue(':username', $data['username']);
      $Qaccounts->bindValue(':password', $data['password']);
      $Qaccounts->bindValue(':signature', $data['signature']);
      $Qaccounts->bindInt(':examine_headers', $data['examine_headers']);
      $Qaccounts->bindValue(':mbroot', $data['mbroot']);
      $Qaccounts->bindValue(':sent', $data['sent']);
      $Qaccounts->bindValue(':drafts', $data['drafts']);
      $Qaccounts->bindValue(':trash', $data['trash']);
      $Qaccounts->bindValue(':spam', $data['spam']);
      $Qaccounts->bindInt(':use_system_mailer', $data['use_system_mailer']);
      $Qaccounts->bindValue(':smtp_host', $data['smtp_host']);
      $Qaccounts->bindInt(':smtp_port', $data['smtp_port']);
      $Qaccounts->bindValue(':smtp_encryption', $data['smtp_encryption']);
      $Qaccounts->bindValue(':smtp_username', $data['smtp_username']);
      $Qaccounts->bindValue(':smtp_password', $data['smtp_password']);
      $Qaccounts->execute();
      
      if ( !$osC_Database->isError() ) {
        if ($id == null) {
	        $accounts_id = $osC_Database->nextID();
	        $toC_Email_Account = new toC_Email_Account($accounts_id);
	        
          if ($toC_Email_Account->initializeAccount()) {
            return $accounts_id;
          } else {
            return false;
          }
        } 
        
        return $id;
      }
      
      return false;
    }
    
    function checkEmailAccount($username, $accounts_id = null) {
      global $osC_Database;

      $Qcheck = $osC_Database->query('select * from :table_email_accounts where username = :username');
      
      if ( $accounts_id != null ) {
        $Qcheck->appendQuery('and accounts_id != :accounts_id');
        $Qcheck->bindInt(':accounts_id', $accounts_id);
      }
      
      $Qcheck->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
      $Qcheck->bindValue(':username', $username);
      $Qcheck->execute();
       
      if ( $Qcheck->numberOfRows() > 0 ) {
        return true;
      }        
      
      return false;
    }
    
    function checkEmailAccountOnSever($data, &$error) {
      $inbox = new toC_InboundEmail(); 
          
      if (!$inbox->open($data['host'], $data['type'], $data['port'], $data['username'], $data['password'], null, null, $data['use_ssl'], $data['novalidate_cert'])) {
        $error = $inbox->last_error();  

        $inbox->close();
        return false;
      }
      
      $inbox->close();
      return true;
    }
      
    function deleteAccount($account) {
      global $osC_Database;

      $error = false;
      
      $osC_Database->startTransaction();
      
      $messages_path = DIR_FS_CACHE_ADMIN . 'emails/' . md5($account['accounts_id'] . $account['accounts_email']) . '/messages';
      if ( file_exists($messages_path) ) {
        $directory = new osC_DirectoryListing($messages_path);
        
        foreach ( ($directory->getFiles()) as $file ) {
           @unlink($messages_path . '/' . $file['name']);
        }
        
        rmdir($messages_path);
      }
      
      $attachments_path = DIR_FS_CACHE_ADMIN . 'emails/' . md5($account['accounts_id'] . $account['accounts_email']) . '/attachments';
      if ( file_exists($attachments_path) ) {
        $directory = new osC_DirectoryListing($attachments_path);
        
        foreach ( ($directory->getFiles()) as $file ) {
           @unlink($attachments_path . '/' . $file['name']);
        }
        
        rmdir($attachments_path);
      }
      
      $account_path = DIR_FS_CACHE_ADMIN . 'emails/' . md5($account['accounts_id'] . $account['accounts_email']); 
      if ( file_exists($account_path) ) {
        rmdir($account_path);
      }
      
      $Qmessages = $osC_Database->query('delete from :table_email_messages where accounts_id = :accounts_id');
      $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessages->bindInt(':accounts_id', $account['accounts_id']);
      $Qmessages->setLogging($_SESSION['module'], $account['accounts_id']);
      $Qmessages->execute();
      
      if ( $osC_Database->isError() ) {
        $error = true;
      }      

      if ( $error == false ) {
        $Qfolders = $osC_Database->query('delete from :table_email_folders where accounts_id = :accounts_id');
        $Qfolders->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
        $Qfolders->bindInt(':accounts_id', $account['accounts_id']);
        $Qfolders->setLogging($_SESSION['module'], $account['accounts_id']);
        $Qfolders->execute();
        
        if ( $osC_Database->isError() ) {
          $error = true;
        }  
      }
      
      if ( $error == false ) {
        $Qaccount = $osC_Database->query('delete from :table_email_accounts where accounts_id = :accounts_id');
        $Qaccount->bindTable(':table_email_accounts', TABLE_EMAIL_ACCOUNTS);
        $Qaccount->bindInt(':accounts_id', $account['accounts_id']);
        $Qaccount->setLogging($_SESSION['module'], $account['accounts_id']);
        $Qaccount->execute();
      
        if ( $osC_Database->isError() ) {
          $error = true;
        }  
      }

      if ($error === false) {
        $osC_Database->commitTransaction();

        return true;
      }

      $osC_Database->rollbackTransaction();

      return false;
    }
//END: Account
     
    function getAllCachedUIDs($accounts_id) {
      global $osC_Database;
      
      $Qmessages = $osC_Database->query('select uid from :table_email_messages where accounts_id = :accounts_id');
      $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessages->bindInt(':accounts_id', $accounts_id);
      $Qmessages->execute();
      
      $uids = array();
      while ( $Qmessages->next() ) {
        $uids[] = $Qmessages->value('uid');
      }     
      
      return $uids;
    }
    
    function getFolderCachedUIDs($accounts_id, $folders_id) {
      global $osC_Database;
      
      $Qmessages = $osC_Database->query('select uid from :table_email_messages where accounts_id = :accounts_id and folders_id = :folders_id');
      $Qmessages->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessages->bindInt(':accounts_id', $accounts_id);
      $Qmessages->bindInt(':folders_id', $folders_id);
      $Qmessages->execute();
      
      $uids = array();
      while ( $Qmessages->next() ) {
        $uids[] = $Qmessages->value('uid');
      }     
      
      return $uids;
    }
    
    function getCachedMessage($id) {
      global $osC_Database;
      
      $Qmessage = $osC_Database->query('select * from :table_email_messages where id = :id');
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':id', $id);
      
      $result = $Qmessage->toArray();

      $Qmessage->freeResult();

      return $result;
    }
      
    function getMessageId($accounts_id, $folders_id, $uid) {
      global $osC_Database;
      
      $Qmessage = $osC_Database->query('select id from :table_email_messages where accounts_id = :accounts_id and folders_id = :folders_id and uid = :uid');
      $Qmessage->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qmessage->bindInt(':accounts_id', $accounts_id);
      $Qmessage->bindInt(':folders_id', $folders_id);
      $Qmessage->bindValue(':uid', $uid);
      $Qmessage->execute();
      
      return $Qmessage->valueInt('id');
    }
    
    function getImapFolders($host, $port, $email, $password, $use_ssl, $novalidate_cert) {
      $inbox = new toC_InboundEmail(); 
          
      if ($inbox->open($host, 'imap', $port, $email, $password, null, null, $use_ssl, $novalidate_cert)) {
        $mailboxes = $inbox->get_mailboxes();
        
        $folders = array();  
        foreach ($mailboxes as $mailbox) {
          $folders[] = array(imap::utf7_imap_decode($mailbox['name']));
        }
        
        $inbox->close();
        return $folders;
      }
      
      $inbox->close();
      return false;
    }

    function getNewMessagesAmount($accounts_id, $folders_id) {
      global $osC_Database;
      
      $Qcount = $osC_Database->query('select count(*) amount from :table_email_messages where accounts_id = :accounts_id and folders_id = :folders_id and new =:new');
      $Qcount->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qcount->bindInt(':accounts_id', $accounts_id);
      $Qcount->bindInt(':folders_id', $folders_id);
      $Qcount->bindValue(':new', 1);
      $Qcount->execute();
      
      $messages_amount = $Qcount->value('amount');
       
      return $messages_amount;
    }

 
    function updateCachedMessageStatus($id, $status = 0) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_email_messages set new = :new where id = :id');
      $Qupdate->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qupdate->bindInt(':id', $id);
      $Qupdate->bindInt(':new', $status);
      $Qupdate->execute();
      
      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }

    function updateImapMessageStatus($accounts_id, $folders_id, $uid, $seen) {
      global $osC_Database;
      
      $Qupdate = $osC_Database->query('update :table_email_messages set new = :new where accounts_id = :accounts_id and folders_id = :folders_id and uid = :uid');
      $Qupdate->bindTable(':table_email_messages', TABLE_EMAIL_MESSAGES);
      $Qupdate->bindInt(':accounts_id', $accounts_id);
      $Qupdate->bindInt(':folders_id', $folders_id);
      $Qupdate->bindInt(':uid', $uid);
      $Qupdate->bindInt(':new', (($seen == 1) ? 0 : 1));
      $Qupdate->setLogging($_SESSION['module'], $uid);
      $Qupdate->execute();
      
      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
    
    function updateFolder($folder) {
      global $osC_Database;
       
      $Qfolder = $osC_Database->query('update :table_email_folders set(folders_name = :folders_name, subscribed = :subscribed, parent_id = :parent_id, sort = :sort, delimiter = :delimiter, attibutes = :attibutes, folders_flag = :folders_flag) where folders_id = :folders_id');
      $Qfolder->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolder->bindInt(':folders_id', $folder['folders_id']);
      $Qfolder->bindInt(':accounts_id', $folder['accounts_id']);
      $Qfolder->bindInt(':parent_id', $folder['parent_id']);
      $Qfolder->bindValue(':folders_name',  $folder['name']);
      $Qfolder->bindInt(':sort', $folder['sort']);
      $Qfolder->bindInt(':attibutes', $folder['attibutes']);
      $Qfolder->bindInt(':subscribed', $folder['subscribed']);
      $Qfolder->bindValue(':delimiter', $folder['delimiter']);
      $Qfolder->execute(':folders_flag', $folder['flag']);

      if ( !$osC_Database->isError() ) {
        return true;
      }
      
      return false;
    }
    
    function getParentId($account, $path, $delimiter) {
      $folders = explode($delimiter, $path);
      array_pop($folders);
      
      if (sizeof($folders) > 0) {
        $parent_name = implode($delimiter, $folders);
        $parent_folder = toC_Email_Accounts_Admin::getFolderByName($account['accounts_id'], $parent_name);
        
        if ($parent_folder !== false) {
          return $parent_folder['folders_id'];
        } else {
          return false;
        }
      } else {
        return 0;
      }
    }
        
    function getFolderData($id) {
      global $osC_Database;
      
      $Qfolder = $osC_Database->query('select * from :table_email_folders where folders_id = :folders_id');
      $Qfolder->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolder->bindInt(':folders_id', $id);
      $Qfolder->execute();
      
      $data = $Qfolder->toArray();
      
      $Qfolder->freeResult();
      
      return $data;
    }
  
    function getFolderByName($accounts_id, $folders_name) {
      global $osC_Database; 
       
      $Qfolder = $osC_Database->query('select * from :table_email_folders where folders_name = :folders_name and accounts_id = :accounts_id');
      $Qfolder->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolder->bindInt(':accounts_id', $accounts_id);
      $Qfolder->bindValue(':folders_name', $folders_name);
      $Qfolder->execute();   
      
      $result = $Qfolder->toArray();
      
      $Qfolder->freeResult();
      
      return $result;
    }
    
    function getAccountDefaultInbox($accounts_id) {
      global $osC_Database; 
       
      $Qinbox = $osC_Database->query('select * from :table_email_folders where parent_id = 0 and accounts_id = :accounts_id and folders_flag = :folders_flag');
      $Qinbox->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qinbox->bindInt(':accounts_id', $accounts_id);
      $Qinbox->bindInt(':folders_flag', EMAIL_FOLDER_INBOX);
      $Qinbox->execute();   
      
      $result = $Qinbox->toArray();
      
      $Qinbox->freeResult();
      
      return $result;
    }
      
    function getAccountDefaultTrash($accounts_id, $folders_name) {
      global $osC_Database; 
       
      $Qtrash = $osC_Database->query('select * from :table_email_folders where accounts_id = :accounts_id and folders_flag = :folders_flag and folders_name = :folders_name');
      $Qtrash->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qtrash->bindInt(':accounts_id', $accounts_id);
      $Qtrash->bindInt(':folders_flag', EMAIL_FOLDER_TRASH);
      $Qtrash->bindValue(':folders_name', $folders_name);
      $Qtrash->execute();   
      
      $result = $Qtrash->toArray();
      
      $Qtrash->freeResult();
      
      return $result;
    }
      
    function getFolders($accounts_id, $parent_id = 0) {
      global $osC_Database;
      
      $Qfolders = $osC_Database->query("select * from :table_email_folders where accounts_id = :accounts_id and parent_id = :parent_id order by sort_order ASC");
      $Qfolders->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolders->bindInt(':accounts_id', $accounts_id);
      $Qfolders->bindInt(':parent_id', $parent_id);
      $Qfolders->execute();
   
      $folders = array();
      while ( $Qfolders->next() ) {
        $folders[] = array('folders_id' => $Qfolders->valueInt('folders_id'),
                           'accounts_id' => $Qfolders->valueInt('accounts_id'),
                           'folders_name' => $Qfolders->value('folders_name'),
                           'subscribed' => $Qfolders->value('subscribed'),
                           'delimiter' => $Qfolders->value('delimiter'),
                           'attributes' => $Qfolders->valueInt('attributes'),
                           'parent_id' => $Qfolders->valueInt('parent_id'),
                           'msgcount' => $Qfolders->value('msgcount'),
                           'unseen' => $Qfolders->value('unseen'));
      }
  
      return $folders;
    }
    
    function getSubscribedFolders($accounts_id, $folders_id = -1) {
      global $osC_Database;
        
      $Qfolders = $osC_Database->query("select * from :table_email_folders");
    
      if ($accounts_id > 0) {
        $Qfolders->appendQuery(" where accounts_id = :accounts_id and (subscribed = 1 or folders_name = 'INBOX')");
      } else {
        $Qfolders->appendQuery(" where (subscribed = 1 or folders_name = 'INBOX')");
      }
    
      if ($folders_id > -1) {
        $Qfolders->appendQuery(" and parent_id = :parent_id");
      }
      $Qfolders->appendQuery(" order by sort_order ASC");
      
      $Qfolders->bindTable(':table_email_folders', TABLE_EMAIL_FOLDERS);
      $Qfolders->bindInt(':accounts_id', $accounts_id);
      $Qfolders->bindInt(':parent_id', $folders_id);
      $Qfolders->execute();
        
      $folders = array();
      while ( $Qfolders->next() ) {
        $folders[] = array('folders_id' => $Qfolders->valueInt('folders_id'),
                           'accounts_id' => $Qfolders->valueInt('accounts_id'),
                           'folders_name' => $Qfolders->value('folders_name'),
                           'delimiter' => $Qfolders->value('delimiter'),
                           'attributes' => $Qfolders->valueInt('attributes'),
                           'parent_id' => $Qfolders->valueInt('parent_id'),
                           'folders_flag' => $Qfolders->value('folders_flag'));
      }
  
      return $folders;
    }
    
    function getMailBoxNodes($accounts_id, $folders_id) {
      $folders = toC_Email_Accounts_Admin::getSubscribedFolders($accounts_id, $folders_id);
      
      $nodes = array();
      foreach ($folders as $folder) {
        $children = toC_Email_Accounts_Admin::getMailBoxNodes($accounts_id, $folder['folders_id']);
        
        $pos = strrpos($folder['folders_name'], $folder['delimiter']);
    
        if ( ($pos > 0) && ($folder['delimiter'] != '') )  {
          $folders_name = substr($folder['folders_name'], $pos + 1);
        } else {
          $folders_name = $folder['folders_name'];
        }
        
        switch ($folder['folders_flag']) {
          case EMAIL_FOLDER_INBOX:
            $iconCls = 'icon-folder-inbox-record'; break;
          case EMAIL_FOLDER_SENTBOX:
            $iconCls = 'icon-folder-sent-record'; break;
          case EMAIL_FOLDER_DRAFT:
            $iconCls = 'icon-folder-drafts-record'; break;
          case EMAIL_FOLDER_TRASH:
            $iconCls = 'icon-folder-trash-record'; break;
          case EMAIL_FOLDER_SPAM:
            $iconCls = 'icon-folder-spam-record '; break;
          default:
            $iconCls = 'icon-folder-default-record ';
        }
        
        $unseen = toC_Email_Accounts_Admin::getNewMessagesAmount($accounts_id, $folder['folders_id']);
        if ($unseen > 0) {
          $text = '<b>' . $folders_name . ' (' . $unseen . ')' . '</b>'; 
        } else {
          $text = $folders_name; 
        }
        
        $node = array('id' => $accounts_id . '_' . $folder['folders_id'],
                      'text' => $text,
                      'name' => $folders_name,
                      'iconCls' => $iconCls,
                      'parent_id' => $folder['parent_id'],
                      'type' => 'folder');
        
        if (sizeof($children) > 0) {
          $node['expanded'] = true;
          $node['children'] = $children;
        } else {
          $node['leaf'] = true;
        }
        
        $nodes[] = $node;
      }
      return $nodes;
    }
  }