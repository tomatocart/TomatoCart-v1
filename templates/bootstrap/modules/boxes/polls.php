<?php
/**
 * TomatoCart Open Source Shopping Cart Solution
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package      TomatoCart
 * @author       TomatoCart Dev Team
 * @copyright    Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html
 * @link         http://tomatocart.com
 * @since        Version 1.1.8
 * @filesource
*/
?>

<!-- box polls start //-->

<div class="boxNew">
	<div class="boxTitle"><?php echo $osC_Box->getTitle(); ?></div>

	<div class="boxContents">
	    <?php
            global $osC_Database, $osC_Language, $osC_Template;
      
            $Qpoll = $osC_Database->query('select p.polls_id, p.polls_type, pd.polls_title from :table_polls p, :table_polls_description pd where p.polls_status = 1 and p.polls_id = pd.polls_id and pd.languages_id = :languages_id');
            $Qpoll->bindTable(':table_polls', TABLE_POLLS);
            $Qpoll->bindTable(':table_polls_description', TABLE_POLLS_DESCRIPTION);
            $Qpoll->bindInt(':languages_id', $osC_Language->getID());
            $Qpoll->executeRandomMulti();
        ?>
        <div id="polls">
        	<form name="frmPolls" id="frmPolls" method="get" action="<?php echo osc_href_link(FILENAME_JSON); ?>">
        		<?php echo osc_draw_hidden_field('polls_id', $Qpoll->valueInt('polls_id')); ?>
        		
        		<?php 
                    if ($Qpoll->numberOfRows() > 0) {
                ?>
                <h6><?php echo $Qpoll->value('polls_title'); ?></h6>
                <?php 
                        $Qanswers = $osC_Database->query('select pa.polls_id, pa.polls_answers_id, pa.votes_count, pa.sort_order, pad.answers_title from :table_polls_answers pa, :table_polls_answers_description pad where pa.polls_id = :polls_id and pa.polls_answers_id = pad.polls_answers_id and pad.languages_id = :languages_id order by pa.sort_order asc');
                        $Qanswers->bindTable(':table_polls_answers', TABLE_POLLS_ANSWERS);
                        $Qanswers->bindTable(':table_polls_answers_description', TABLE_POLLS_ANSWERS_DESCRIPTION);
                        $Qanswers->bindInt(':polls_id', $Qpoll->valueInt('polls_id'));
                        $Qanswers->bindInt(':languages_id', $osC_Language->getID());
                        $Qanswers->execute();
                        
                        if ($Qanswers->numberOfRows() > 0) {
                ?>
                <ul>
                <?php 
                            while ($Qanswers->next()) {
                                if ( $Qpoll->valueInt('polls_type') ) {
                ?>
                	<li><?php echo osc_draw_checkbox_field('vote[]', $Qanswers->valueInt('polls_answers_id'), null, 'class="poll_votes"'); ?>&nbsp;&nbsp;<?php echo $Qanswers->value('answers_title'); ?></li>
                <?php 
                                } else {
                ?>
                	<li><?php echo osc_draw_radio_field('vote[]', $Qanswers->valueInt('polls_answers_id'), null, 'class="poll_votes"'); ?>&nbsp;&nbsp;<?php echo $Qanswers->value('answers_title'); ?></li>
                <?php 
                                }
                            }
                            $Qanswers->freeResult();
                ?>
                </ul>
                <span style="float: right;"><button type="button" class="btn btn-mini" id="btnPollVote"><?php echo $osC_Language->get('button_vote'); ?></button></span>
                <button type="button" class="btn btn-mini" id="btnPollResult"><?php echo $osC_Language->get('button_result'); ?></button>
                <?php
                        }
                    }
                    $Qpoll->freeResult();
        		?>
        	</form>
        </div>
    	<script type="text/javascript">
          window.addEvent('domready', function(){
              var polls = new Polls();
          });
        </script>
  </div>
</div>

<!-- box polls end //-->
