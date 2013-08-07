<?php
/*
  $Id: message_stack.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2004 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class messageStack {
    var $messages;

// class constructor
    function messageStack() {
      $this->messages = array();
    }

// class methods
    function add($class, $message, $type = 'error') {
      $this->messages[] = array('class' => $class, 'type' => $type, 'message' => $message);
    }

    function add_session($class, $message, $type = 'error') {
      if (isset($_SESSION['messageToStack'])) {
        $messageToStack = $_SESSION['messageToStack'];
      } else {
        $messageToStack = array();
      }

      $messageToStack[] = array('class' => $class, 'text' => $message, 'type' => $type);

      $_SESSION['messageToStack'] = $messageToStack;

      $this->add($class, $message, $type);
    }

    function reset() {
      $this->messages = array();
    }
    
    function getMessages($class) {
      $messages = array();

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $messages[] = $this->messages[$i]['message'];
        }
      }

      return $messages;
    }

    function output($class) {
      $this->loadFromSession();
      
      $messages = '<ul>';
      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          switch ($this->messages[$i]['type']) {
            case 'error':
              $bullet_image = DIR_WS_IMAGES . 'icons/error.gif';
              break;
            case 'warning':
              $bullet_image = DIR_WS_IMAGES . 'icons/warning.gif';
              break;
            case 'success':
              $bullet_image = DIR_WS_IMAGES . 'icons/success.gif';
              break;
            default:
              $bullet_image = DIR_WS_IMAGES . 'icons/bullet_default.gif';
          }

          $messages .= '<li style="list-style-image: url(\'' . $bullet_image . '\')">' . osc_output_string($this->messages[$i]['message']) . '</li>';
        }
      }
      $messages .= '</ul>';

      return '<div class="messageStack">' . $messages . '</div>';
    }

    function outputPlain($class) {
      $message = false;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $message = osc_output_string($this->messages[$i]['message']);
          break;
        }
      }

      return $message;
    }

    function size($class) {
      $class_size = 0;

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($this->messages[$i]['class'] == $class) {
          $class_size++;
        }
      }

      return $class_size;
    }

    function loadFromSession() {
      if (isset($_SESSION['messageToStack'])) {
        $messageToStack = $_SESSION['messageToStack'];

        for ($i=0, $n=sizeof($messageToStack); $i<$n; $i++) {
          $this->add($messageToStack[$i]['class'], $messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }

        unset($_SESSION['messageToStack']);
      }
    }
  }
?>
