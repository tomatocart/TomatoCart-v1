<?php
/*
  $Id: polls.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2010 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Boxes_polls extends osC_Modules {
    var $_title,
        $_code = 'polls',
        $_author_name = 'TomatoCart',
        $_author_www = 'http://www.tomatocart.com',
        $_group = 'boxes';

    function osC_Boxes_polls() {
      global $osC_Language;

      $this->_title = $osC_Language->get('box_polls_heading');
    }

    function initialize() {
      global $osC_Database, $osC_Language, $osC_Template;
      
      $Qpoll = $osC_Database->query('select p.polls_id, p.polls_type, pd.polls_title from :table_polls p, :table_polls_description pd where p.polls_status = 1 and p.polls_id = pd.polls_id and pd.languages_id = :languages_id');
      $Qpoll->bindTable(':table_polls', TABLE_POLLS);
      $Qpoll->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
      $Qpoll->bindInt(':languages_id', $osC_Language->getID());
      $Qpoll->executeRandomMulti();

      $this->_content = '<div id="polls"><form name="frmPolls" id="frmPolls" action="' . osc_href_link(FILENAME_JSON) . '" method="get">' . osc_draw_hidden_field('polls_id', $Qpoll->valueInt('polls_id'));
      
      if ($Qpoll->numberOfRows() > 0) {
        $this->_content .= '<h6>' . $Qpoll->value('polls_title') . '</h6>';
        
        $Qanswers = $osC_Database->query('select pa.polls_id, pa.polls_answers_id, pa.votes_count, pa.sort_order, pad.answers_title from :table_polls_answers pa, :table_polls_answers_description pad where pa.polls_id = :polls_id and pa.polls_answers_id = pad.polls_answers_id and pad.languages_id = :languages_id order by pa.sort_order asc');
        $Qanswers->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
        $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
        $Qanswers->bindInt(':polls_id', $Qpoll->valueInt('polls_id'));
        $Qanswers->bindInt(':languages_id', $osC_Language->getID());
        $Qanswers->execute();
        
        if ($Qanswers->numberOfRows() > 0) {
          $this->_content .= '<ul>';
          
          while ($Qanswers->next()) {
            if ( $Qpoll->valueInt('polls_type') ) {
              $this->_content .= '<li>' . osc_draw_checkbox_field('vote[]', $Qanswers->valueInt('polls_answers_id'), null, 'class="poll_votes"') . '&nbsp;&nbsp;' . $Qanswers->value('answers_title') . '</li>';
            } else {
              $this->_content .= '<li>' . osc_draw_radio_field('vote[]', $Qanswers->valueInt('polls_answers_id'), null, 'class="poll_votes"') . '&nbsp;&nbsp;' . $Qanswers->value('answers_title') . '</li>';
            }
          }
          $this->_content .= '</ul>';
          
          $this->_content .= '<span style="float: right;">' . osc_draw_image_button('button_vote.png', $osC_Language->get('button_vote'), 'class="button" id="btnPollVote"') . '</span>';          
          $this->_content .= osc_draw_image_button('button_result.png', $osC_Language->get('button_result'), 'class="button" id="btnPollResult"');
          
          $Qanswers->freeResult();
        }
      }
      $Qpoll->freeResult();
      
      $this->_content .= '</form></div>';
      
      $osC_Template->addJavascriptFilename('includes/javascript/polls.js');
      $js .= '<script type="text/javascript">
              window.addEvent(\'domready\',function(){
                var polls = new Polls();
              });
              </script>';
      $this->_content .= $js . "\n";
    }

    function install() {
      global $osC_Database;

      parent::install();
      
      $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Disallow more than one vote from the same IP address', 'DISALLOW_MORE_THAN_ONE_VOTE_FROM_ONE_IP', '1', 'Disallow more than one vote from the same IP address', '19', '1', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    }
  
    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('DISALLOW_MORE_THAN_ONE_VOTE_FROM_ONE_IP');
      }

      return $this->_keys;
    }
  }
?>
