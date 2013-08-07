<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @version $Id: imap.class.inc 1693 2009-01-12 09:07:24Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 * @since Group-Office 1.0
 */

/**
 * Include RCF8222 class
 */
require_once('external/groupoffice/RFC822.class.inc');


/**
 * This class can be used to connect to IMAP or POP-3 servers
 * @version $Id: imap.class.inc 1693 2009-01-12 09:07:24Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 * @access public
 */
class imap{
	/**
	 * The recource ID that points to the IMAP stream
	 *
	 * @var     		Recource ID
	 * @access  private
	 */
	var $conn;

	/**
	 * The number of messages in the current mailbox
	 *
	 * @var     		Int
	 * @access  private
	 */
	var $count;

	/**
	 * The number of unseen messages in the current mailbox
	 *
	 * @var     		Int
	 * @access  private
	 */
	var $unseen;


	/**
	 * Current message that is get with get_message();
	 *
	 * @var     		Array
	 * @access  private
	 */
	var $message;

	/**
	 * The type of the mailserver. IMAP or POP-3
	 *
	 * @var     		String
	 * @access  private
	 */
	var $servertype;

	/**
	 * The string to connect to the mailserver with imap_open();
	 *
	 * @var     		String
	 * @access  private
	 */
	var $connectstring;

	/**
	 * Array of mailboxes fetched with get_mailboxes();
	 *
	 * @var     		Array
	 * @access  private
	 */
	var $mailboxes;

	/**
	 * Currently opened mailbox
	 *
	 * @var     		String
	 * @access  private
	 */
	var $mailbox;

	/**
	 * Sorted message uid's returned by imap_sort();
	 *
	 * @var     		Array
	 * @access  private
	 */
	var $sort;

	/**
	 * Array with mailbox info fetched with imap_mailboxinfo()
	 *
	 * @var     		Array
	 * @access  private
	 */
	var $mailbox_info;

	/**
	 * Class to handle formatting of e-mail address strings.
	 *
	 * @var     		Object
	 * @access  private
	 */
	var $RFC822;
	
	
	var $touched_folders =array();
	

	/**
	 * Constructor. Initialises RFC822 class
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		global $GO_SECURITY;

		$this->RFC822 = new RFC822();
	}

	/**
	 * Opens a connection to server
	 *
	 * @param	string	$host					The hostname of the mailserver
	 * @param	string	$type					The type of the mailserver (IMAP or POP-3)
	 * @param	int 			$port 					The port to connect to
	 * @param	string	$username	The username
	 * @param	string	$password		The password
	 * @param	string	$mailbox			The mailbox to open
	 * @param	string	$flags					Connection flags (See PHP docs imap_open()
	 * @param	bool		$ssl						Connect in SSL mode or not
	 * @param	bool		$novalidate_cert						Don't validate SSL certificate
	 * @access public
	 * @return mixed	The recource ID on success or false on failure
	 */
	function open($host, $type, $port, $username, $password, $mailbox = "INBOX", $flags = null, $ssl = false, $novalidate_cert = false, $tls=false) {
		global $GO_CONFIG;

		$this->host=$host;
		$this->username=$username;

		$this->cache_key = $this->host.'.'.$this->username;

		$this->servertype = strtolower($type);
		$this->mailbox = $mailbox;
		
		$this->touched_folders[]=$this->mailbox;
		
		$this->connectstring = $host.":".$port."/".$this->servertype.$GO_CONFIG->email_connectstring_options;

		if ($ssl) {
			$this->connectstring .= '/ssl';
		}

		if($tls)
		{
			$this->connectstring .= '/tls';
		}else
		{
			$this->connectstring .= '/notls';
		}

		if ($novalidate_cert) {
			$this->connectstring .= '/novalidate-cert';
		}
		
		if ($flags != 0) {
			$this->conn = @imap_open("{".$this->connectstring."}".$this->mailbox, $username, $password, $flags, 1);
		} else {
			$this->conn = @imap_open("{".$this->connectstring."}".$this->mailbox, $username, $password);
		}
		return $this->conn;
	}
	
	/**
	 * Returns an array of integer values pertaining to the specified user mailbox. All values contain a key based upon the resource name, and a corresponding array with the usage and limit values within.
	 * 
	 * This function will return FALSE in the case of call failure, and an array of information about the connection upon an un-parsable response from the server. 
	 *
	 * @return unknown
	 */
	
	function get_quota(){		
		if(!$this->is_imap())
		{
			return false;
		}else
		{
			return @imap_get_quotaroot($this->conn, $this->mailbox);
		}		 
	}

	/**
	 * Return last error
	 *
	 * @access public
	 * @return String Last IMAP error
	 */

	function last_error()
	{
		$error = imap_last_error();
		$this->clear_errors();
		return $error;
	}

	/**
	 * Close the connection to the mailserver
	 *
	 * @access public
	 * @return void
	 */
	function close() {
		if(isset($this->conn) && $this->conn)
		{
			unset ($this->count);
			unset ($this->unseen);

			//for supressing notices that are not really errors
			$this->clear_errors();

			imap_close($this->conn);
			unset ($this->conn);
		}
	}

	function clear_errors()
	{
		imap_errors();
	}

	/**
	 * Count total and new messages on server
	 *
	 * @param	string	$mailbox	The mailbox to get status of
	 * @access public
	 * @return void
	 */
	function status($mailbox = false, $options=SA_UNSEEN) {
		if (!$mailbox)
		$mailbox = $this->mailbox;

		$status = imap_status($this->conn, "{".$this->connectstring."}".$mailbox, $options);
		return $status;
	}

	/**
	 * Count total and new messages on server
	 *
	 * @param	string	$mailbox	The mailbox to get status of
	 * @access public
	 * @return void
	 */
	function get_unseen($mailbox = false, $recursive=false) {
		if (!$mailbox)
		$mailbox = $this->mailbox;


		if(!$recursive)
		{
			$status = imap_status($this->conn, "{".$this->connectstring."}".$mailbox, SA_UNSEEN);
			return $status->unseen;
		}else
		{
			$unseen =0;
			//Sometimes INBOX is subscribed, sometimes not. So check it manually and skip it
			//if it's subscribed.
			if($mailbox == 'INBOX')
			{
				$status = imap_status($this->conn, "{".$this->connectstring."}INBOX", SA_UNSEEN);
				$unseen+=$status->unseen;
			}
			$folders = $this->get_subscribed($mailbox);
			foreach($folders as $folder)
			{
				if($folder['name'] != 'INBOX')
				{
					$status = imap_status($this->conn, "{".$this->connectstring."}".$folder['name'], SA_UNSEEN);
					$unseen+=$status->unseen;
				}
			}
			return $unseen;
		}
	}

	/**
	 * Check if this is an IMAP server
	 *
	 * @param	string	$mailbox	The mailbox to get status of
	 * @access public
	 * @return bool	 True if this is an IMAP server
	 */

	function is_imap() {
		if ($this->servertype == "imap") {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Sort message UID's into $this->sort (see imap_sort() PHP docs)
	 *
	 * @param	int	$sort_type	The column
	 * @param	string $reverse Reverse sorting (0 or 1)
	 * @param	string $search Search query
	 * @access public
	 * @return int	 Number of sorted messages
	 */
	function sort($sort_type = SORTDATE, $reverse = "1", $query = '') {

		$this->sort_type=$sort_type;
		$this->sort_reverse=$reverse;

		if ($query != '') {
			$this->sort = imap_sort($this->conn, $sort_type, $reverse, SE_UID+SE_NOPREFETCH, trim($query));
		} else {
			$this->sort = imap_sort($this->conn, $sort_type, $reverse, SE_UID+SE_NOPREFETCH);			
		}
	}

	function build_search_query($subject = '', $from = '', $to = '', $cc = '', $body = '', $before = '', $since = '', $before = '', $since = '', $flagged = '', $answered = '', $seen='') {
		$query = '';
		if ($subject != '') {
			$query .= 'SUBJECT "'.$subject.'" ';
		}
		if ($from != '') {
			$query .= 'FROM "'.$from.'" ';
		}
		if ($to != '') {
			$query .= 'TO "'.$to.'" ';
		}
		if ($cc != '') {
			$query .= 'CC "'.$cc.'" ';
		}
		if ($body != '') {
			$query .= 'BODY "'.$body.'" ';
		}

		if ($before != '') {
			$unix_before = Date::to_unixtime($before);
			$query .= 'BEFORE "'.date('d-M-Y', $unix_before).'" ';
		}

		if ($since != '') {
			$unix_since = Date::to_unixtime($since);
			$query .= 'SINCE "'.date('d-M-Y', $unix_since).'" ';
		}

		if ($flagged != '') {
			$query .= $flagged.' ';
		}

		if ($answered != '') {
			$query .= $answered.' ';
		}

		if ($seen != '') {
			$query .= $seen.' ';
		}

		return $query;
	}

	function get_message_uids($first, $offset, $sort_type = SORTDATE, $reverse = "1", $query = '')
	{
		//get the unseen and total messages		
		$status = $this->status($this->mailbox, SA_UNSEEN+SA_MESSAGES);
		if($status)
		{
			$this->unseen = $status->unseen;
			$this->count = $status->messages;
		}else
		{
			$this->unseen = $this->count = 0;
		}
		$this->query = $query;
		$this->first = $first;
		$this->offset = $offset;
		
		//sort the uid's
		$this->sort($sort_type, $reverse, $query);
		
		return $this->get_uids_subset($first, $offset);
	}
	
	function get_uids_subset($first, $offset)
	{
		$first=intval($first);
		$offset=intval($offset);
		
		$this->first = $first;
		$this->offset = $offset;
		
		$count = count($this->sort);

		$last = $first + $offset;
		if ($offset == 0 || ($last > $count)) {
			$last = $count;
		}

		$index = 0;
		$sorted_uids = array ();
		for ($i = $first; $i < $last; $i ++) {
			$sorted_uids[] = $this->sort[$i];
		}	
		return $sorted_uids;
	}
		
	function get_message_headers($uids)
	{		
		$messages = array();
		$get = implode(',', $uids);
			
		$overviews = imap_fetch_overview($this->conn, $get, FT_UID);
		foreach($overviews as $overview)
		{
			$message = $this->get_message($overview, false, true);			
			
			//$cached_message['folder_id']=$folder_id;
			$cached_message['uid']=$message['uid'];
			$cached_message['new']=$message['new'] ? '1' : '0';
			$cached_message['subject']=$message['subject'];
			$cached_message['size']=$message['uid'];
			$cached_message['udate']=$message['udate'];
			$cached_message['attachments']=$message['attachments'] ? '1' : '0';
			$cached_message['flagged']=$message['flagged'] ? '1' : '0';
			$cached_message['answered']=$message['answered'] ? '1' : '0';
			$cached_message['priority']=empty($message['priority']) ? 3 : intval($message['priority']);			
			$cached_message['from']=$message['from'];
			$cached_message['reply_to']=$message['reply_to'];
			$cached_message['to']=implode(',', $message['to']);

			$cached_message['notification']=$message['notification'];
			$cached_message['content_type']=$message['content_type'];
			$cached_message['content_transfer_encoding']=$message['content_transfer_encoding'];

			$messages[$message['uid']]=$cached_message;
		}
		return $messages;
	}


	/**
	 * Get's all mime header values in an associative array
	 *
	 * @param String $header MIME headers
	 * @return Array with header values
	 */


	function get_header_values($header)
	{
		$header = str_replace("\r\n", "\n", $header);
		//commented with \h is better but only supported from 5.2.4
		//$header = preg_replace("/\n\h/", " ", $header);
		$header = preg_replace("/\n\s/", " ", $header);

		$lines = explode("\n", $header);

		$values = array();
		foreach($lines as $line)
		{
			$line = stripslashes($line);
			$colpos = strpos($line, ':');

			if($colpos)
			{
				$key = strtolower(trim(substr($line,0,$colpos)));
				$value = trim(substr($line, $colpos+1));

				$values[$key]=$value;
			}
		}

		$message["priority"] = isset($values['x-priority']) ? $values['x-priority'] : '';
		$message['attachments']=false;

		if (!empty($values['content-type']) && eregi("([^/]*)/([^ ;\n\t]*)", $values['content-type'], $regs)) {
			$mtype = strtolower($regs[1]);
			$stype = strtolower($regs[2]);
		}else
		{
			$mtype = '';
			$stype = '';
		}
		if ($mtype != 'text' && $stype != 'alternative' && $stype != 'related' && !empty($mtype) && !empty($stype))
		{
			$message["attachments"] = true;
		}


		$RFC822 = new RFC822();

		$message["to"]=array();
		if(isset($values['to']))
		{
			$to = $RFC822->parse_address_list($values['to']);
			foreach($to as $address)
			{
				$message['to'][]=$RFC822->write_address($this->enc_utf8($address['personal']), $address['email']);
			}
		}

		$message["cc"]=array();
		if(isset($values['cc']))
		{
			$cc = $RFC822->parse_address_list($values['cc']);
			foreach($cc as $address)
			{
				$message['cc'][]=$RFC822->write_address($this->enc_utf8($address['personal']), $address['email']);
			}
		}
		
		$message["bcc"]=array();
		if(isset($values['bcc']))
		{
			$cc = $RFC822->parse_address_list($values['bcc']);
			foreach($cc as $address)
			{
				$message['bcc'][]=$RFC822->write_address($this->enc_utf8($address['personal']), $address['email']);
			}
		}
			
		if(!isset($values['from']))
		$values['from']='';
			
		//$from = $RFC822->parse_address_list($values['from']);
		$message["from"] = isset($values['from']) ? $this->enc_utf8($values['from']) : '';
		//$message["sender"] = isset($from[0]['email']) ? $this->enc_utf8($from[0]['email']) : '';
		if(isset($values['date'])){
			
			//a popular mail client sends the time incorrect. Here's a hack to make it work
			if(substr($values['date'],-2)=='UT')
			{
				$values['date'].='C';
			}
			
			$message['udate']=strtotime($values['date']);
		}else
		{
			$message['udate']=0;
		}
		
		
		
		$message['subject'] = isset($values['subject']) ? $this->enc_utf8($values['subject']) : '';

		$message["notification"]=isset($values['disposition-notification-to']) ? $this->enc_utf8($values['disposition-notification-to']) : false;
		$message['content_type']=isset($values['content-type']) ? $values['content-type'] : false;
		$message['content_transfer_encoding']=isset($values['content-transfer-encoding']) ? $values['content-transfer-encoding'] : '';

		return $message;
	}



	function get_source($uid)
	{
		return imap_fetchheader($this->conn,$uid, FT_UID).imap_body($this->conn, $uid, FT_UID);
	}

	/**
	 * Get one message with the structure
	 *
	 * @param int $uid The unique identifier of the
	 * @param string $part Get a specific part of a message
	 * @access public
	 * @return array The E-mail message elements
	 */
	function get_message($uid, $fetchstructure=true, $nocache=false) {
		if ($this->conn) {
			unset ($this->message);

			if(is_object($uid))
			{
				$this->message['uid'] = $uid->uid;
				//$this->message['number'] = $uid->msgno;
				$this->message['new'] = (!$uid->seen && $this->is_imap());
				$this->message["flagged"] = $uid->flagged;
				$this->message["size"] = $uid->size;
				$this->message["answered"] = $uid->answered;
			}else
			{
				$this->message['uid'] = $uid;
				//$this->message['number'] = imap_msgno($this->conn, $uid);

				/*if(!@$headerinfo = imap_headerinfo($this->conn, $this->message['number']))
				{
					//message doesn't exist
					return false;
				}
				$this->message['new'] = (($headerinfo->Recent == 'N' || $headerinfo->Unseen == 'U')  && $this->is_imap());
				$this->message["flagged"] = $headerinfo->Flagged=='F';
				$this->message["size"] = $headerinfo->Size;
				$this->message["answered"] = $headerinfo->Answered=='A';*/
			}				
			$this->message["parts"] = array();

			if($fetchstructure)
			{
				$structure = imap_fetchstructure($this->conn, $this->message['uid'], FT_UID);
				//debug($structure);
				$this->get_parts($structure);
			}

			$header = imap_fetchheader($this->conn, $this->message['uid'], FT_UID);
			$values = $this->get_header_values($header);

			$this->message = array_merge($this->message, $values);

			if(!isset($this->message["reply_to"]))
			{
				$this->message["reply_to"] = $this->message['from'];
			}
			return $this->message;
		} else {
			return false;
		}
	}

	/**
	 * Get structured parts of a message
	 *
	 * @param array $mimeobj An array returned by imap_fetch_structure()
	 * @param string $section The current section of the message
	 * @access private
	 * @return void
	 */
	function get_parts($mimeobj, $section = 0, $parttype='') {
		if (isset ($mimeobj->type)) {
			$type = $this->get_mime_type($mimeobj->type);
		} else {
			$type = 'text';
		}

		$full_mime_type = $type."/".$mimeobj->subtype;
		//echo $full_mime_type.' '.$section.'<br />';
		$encoding = $this->get_encoding($mimeobj->encoding);

		$name = '';

		if(!empty($mimeobj->description))
		{
			$name = $this->enc_utf8($mimeobj->description);
		}else
		{
			if (isset ($mimeobj->dparameters)) {
				$params = $mimeobj->dparameters;
				for ($x = 0; $x < count($params); $x ++) {
					if(is_object($params) && isset($params->$x))
					{
						$param = $params->$x;
					}elseif(is_array($params) && isset($params[$x]))
					{
						$param = $params[$x];
					}else
					{
						$param = null;
					}

					if(isset($param))
					{
						if (substr(strtolower($param->attribute),0,8) == 'filename' && $param->value != '') {
							//$name = $this->enc_utf8(quoted_printable_decode($param->value));
							$name .= $param->value;
						}
					}
				}
			}

			if (empty($name) && isset ($mimeobj->parameters)) {
				$params = $mimeobj->parameters;
				for ($x = 0; $x < count($params); $x ++) {
					if(is_object($params) && isset($params->$x))
					{
						$param = $params->$x;
					}elseif(is_array($params) && isset($params[$x]))
					{
						$param = $params[$x];
					}else
					{
						$param = null;
					}

					if(isset($param))
					{
						if ((substr(strtolower($param->attribute),0,4) == 'name') && $param->value != '') {
							$name .= $param->value;//$this->enc_utf8($param->value);
						}
					}
				}
			}

			if(substr($name,0,2)=='=?')
			{
				$name = $this->enc_utf8($name);
			}else
			{
				$start = strpos($name,"''");
				if($start)
				{
					$name = substr($name, $start);
				}
				$name = rawurldecode($name);
			}
		}
		$x = 0;
		if (isset ($mimeobj->parts)) {
			for ($x = 0; $x < count($mimeobj->parts); $x ++) {
					

				// If we are in the root of the object increment by whole integers

				if ($section == 0) {
					/*if(strtolower($mimeobj->subtype)!='related' && strtolower($mimeobj->subtype)!='mixed' && strtolower($mimeobj->subtype)!='alternative')
					 {
						$nsection = $x +1;
						}else {
						//$nsection=0;
						}*/
					$nsection = $x +1;
				} elseif (
				($pos = strrpos($section, ".")) &&
				($mimeobj->parts[0]->type != TYPEMULTIPART || $mimeobj->parts[0]->subtype != 'RELATED')
				) {
					$subsection = (int) substr($section, $pos +1) + $x;
					if ($subsection == '') {
						$subsection = '0';
					}
					$nsection = substr($section, 0, $pos).".". ($subsection +1);
				} else {
					$nsection = $section;
				}

				// If there are more parts to the part about to be processed reference it as a header with ".0"
				// but only if the child of this child isn't MULTIPART

				if (isset ($mimeobj->parts[$x]->parts) && count($mimeobj->parts[$x]->parts))// && strtolower($mimeobj->parts[$x]->subtype) != 'rfc822')
				{
					// Funny really, if a mime section is a inline message that has a multipart body you reference the message
					// mime section with "2" the inline message header with "2.0" and the subsections with	"2.x"
					// However if the mime section is a inline message with only 1 part then you reference the
					// mime section in the message with 2.0 and the	inline message body with 2.1

					//echo $mimeobj->parts[$x]->type.' '.TYPEMESSAGE.' ';
					//echo $mimeobj->parts[$x]->parts[0]->type.' '.TYPEMULTIPART.' '.$section.' '.$full_mime_type.'<br />';
					//if (!($mimeobj->parts[$x]->type == TYPEMESSAGE && $mimeobj->parts[$x]->parts[0]->type == TYPEMULTIPART)) {
					if ($mimeobj->parts[$x]->type != TYPEMESSAGE) {
						//echo 'ja';
						$nsection .= ".0";
					} else {
						$nsection .= "";
					}

					//$nsection .= ".0";
				}

				if(strtolower($full_mime_type) != 'message/rfc822')
				{
					$this->get_parts($mimeobj->parts[$x], $nsection, $mimeobj->subtype);
				}/*else {
					
				if (strtolower($mimeobj->parts[$x]->subtype) == 'mixed') {
				$section .= ".2";
				}
				}*/


			}
		}

		// If after processing the entire MIME object the $x variable is still zero then we didn't
		// process a multipart mime message.

		if ($x == 0 && $section == 0) {
			$section = "1";
		}

		if ($type != "multipart" && $full_mime_type) {
			/*if (eregi('message', $full_mime_type)) {
				$section ++;
				}*/

			$part_charset = 'ISO-8859-15';
			if ($mimeobj->ifparameters) {
				for ($x = 0; $x < count($mimeobj->parameters); $x ++) {
					$param = $mimeobj->parameters[$x];
					if ((strtolower($param->attribute) == 'charset') && ($param->value != '')) {
						$part_charset = $param->value;
						break;
					}
				}
			}

			if(empty($name) && strtolower($full_mime_type) == 'message/rfc822')
			{
				$name='Message.eml';
			}

			//echo $section.': '.$full_mime_type.' '.$name.'<br>';
			$bytes = isset ($mimeobj->bytes) ? $mimeobj->bytes : 0;
			$tmp = Array (
				'number' => $section, 
				'id' => $mimeobj->ifid ? $mimeobj->id : 0, 
				'name' => $name, 
				'mime' => $full_mime_type, 
				'transfer' => $encoding, 
				'charset' => $part_charset, 
				'disposition' => $mimeobj->ifdisposition ? $mimeobj->disposition : '', 
				'size' => $bytes,
				'human_size'=>$this->format_size($bytes),
				'type' => $parttype);

			array_unshift($this->message["parts"], $tmp);
		}
	}
	/**
	 * Check if a part is an attachment
	 *
	 * @param array $part returned by get_message or get_parts
	 * @return bool 
	 */
	function part_is_attachment($part)
	{
		return (eregi("ATTACHMENT", $part["disposition"]) || ($part["name"] != '' && empty($part["id"]))
		&& 
		!($part['type']=='APPLEDOUBLE' && $part['mime']== 'application/APPLEFILE'));
	}
	
	/*function is_attachment($part)
	{
		return	(
		eregi("attachment", $part["disposition"]) ||
		($part['name']!='' && !eregi("inline", $part["disposition"])) ||
		($part["id"] == '' && eregi("inline", $part["disposition"]))
			
		)
		&& !eregi("message/RFC822", $part["mime"]);
	}*/
	
	/*
		function print_structure($mimeobj, $depth = 0, $section = 0)
		{
		for($y = 0; $y < $depth; $y++)
		{
		echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		}
		echo($this->get_mime_type($mimeobj->type) . "/{$mimeobj->subtype},");
		echo($this->get_encoding($mimeobj->encoding) . "(<B>$section</B>)<br>");

		$x=0;
		if (isset($mimeobj->parts))
		{

		for($x = 0; $x < count($mimeobj->parts); $x++)
		{
		// If we are in the root of the object increment by whole	integers

		if($section == 0)
		{
		$nsection = $x + 1;
		$subsection = 0;
		// If we are in the object and the first sub-object of our	object isn't multipart
		// then increment the postfix by ".1" otherwise	we are multipart or a message
		// and leave the section id alone to be handled by the next	code block

		//else if(($pos = strrpos($section, ".")) && sizeof($mimeobj->parts) > 1)
		}else if(($pos = strrpos($section, ".")) && ($mimeobj->parts[0]->type != TYPEMULTIPART || $mimeobj->parts[0]->subtype != 'RELATED'))
		//}elseif($pos = strrpos($section, "."))
		{
		$subsection = (int) substr($section, $pos+1)+$x;
		if ($subsection == '')
		{
		$subsection = '0';
		}
		$nsection = substr($section, 0, $pos) .	"." . ($subsection + 1);
		}else
		{
		$nsection = $section;

		}

		// If there are more parts to the part about to be processed reference it as a header with ".0"
		// but only if the child of this child isn't MULTIPART

		if(isset($mimeobj->parts[$x]->parts) && count($mimeobj->parts[$x]->parts))
		{
		// Funny really, if a mime section is a inline message that has a multipart body you reference the message
		// mime section with "2" the inline	message header with "2.0" and the subsections with	"2.x"
		// However if the mime section is a inline message with only 1 part then you reference the
		// mime section in the message with 2.0 and the	inline message body with 2.1

		if(!($mimeobj->parts[$x]->type == TYPEMESSAGE && $mimeobj->parts[$x]->parts[0]->type ==	TYPEMULTIPART))
		{
		$nsection .= ".0";
		}else
		{
		$nsection .= "";
		}
		}

		$this->print_structure($mimeobj->parts[$x], $depth + 1, $nsection);
		}
		}

		// If after processing the entire MIME object the $x variable is still zero then we didn't
		// process a multipart mime message, it's just normal email so say so here.

		if($x == 0 && $section == 0)
		{
		echo($this->get_mime_type($mimeobj->type) . "/{$mimeobj->subtype}, ");
		echo($this->get_encoding($mimeobj->encoding) . "(<B>1</B>) (<B>NOT MIME MULTIPART</B>)<br>");
		}
		}
		*/

	/**
		* Get the encoding of a message part in text
		*
		* @param int $encoding The encoding type as ut comes from imap_fetch_structure()
		* @access private
		* @return String The Encoding
		*/

	function get_encoding($encoding) {
		switch ($encoding) {
			case 0 :
				$encoding = '7BIT';
				break;
			case 1 :
				$encoding = '8BIT';
				break;
			case 2 :
				$encoding = 'BINARY';
				break;
			case 3 :
				$encoding = 'BASE64';
				break;
			case 4 :
				$encoding = 'QUOTED-PRINTABLE';
				break;
			case 5 :
				$encoding = 'OTHER';
				break;
			default :
				$encoding = 'none';
				break;
		}

		return $encoding;
	}

	/**
		* Get the mime type of a message part in text
		*
		* @param int $type The mime type as ut comes from imap_fetch_structure()
		* @access private
		* @return String The mime type
		*/

	function get_mime_type($type) {
		switch ($type) {
			case 0 :
				$mime_type = 'text';
				break;
			case 1 :
				$mime_type = 'multipart';
				break;
			case 2 :
				$mime_type = 'message';
				break;
			case 3 :
				$mime_type = 'application';
				break;
			case 4 :
				$mime_type = 'audio';
				break;
			case 5 :
				$mime_type = 'image';
				break;
			case 6 :
				$mime_type = 'video';
				break;
			case 7 :
				$mime_type = 'model';
				break;
			default :
				$mime_type = 'unknown';
		}
		return $mime_type;
	}

	/**
		* Search a plain text header for a value
		*
		* @param String $fieldname The name of the field
		* @param String $header The plain text header to search through
		* @access private
		* @return string The value of the fieldname if found.
		*/

	function get_header_value($fieldname, $header) {
		$value=false;
		$results = array();
		if (preg_match("/\v$fieldname (.*)\v/", $header, $results)) {
			$value = trim($results[1]);
		}

		return $value;
	}

	/**
		* Delete messages from the IMAP server
		*
		* @param Array $messages An array of message UID's
		* @access public
		* @return void
		*/

	function delete($messages) {
		for ($i = 0; $i < count($messages); $i ++) {
			@ imap_delete($this->conn, $messages[$i], FT_UID);
		}
		return @ imap_expunge($this->conn);
	}





	/**
		* Return a message part
		*
		* @param int $uid The message UID
		* @param float $part_no The message part identifier
		* @param String $transfer The transfer-encoding
		* @param String $part_charset The character set of the part if applicable
		* @access public
		* @return string Message part data
		*/

	function view_part($uid, $part_no, $transfer, $part_charset = '') {
		global $GO_CONFIG;

		$charset='UTF-8';
		
		$transfer=strtoupper($transfer);

		$str = imap_fetchbody($this->conn, $uid, $part_no, FT_UID);

		if ($transfer == 'BASE64')
		{
			$str = imap_base64($str);
		}elseif ($transfer == 'QUOTED-PRINTABLE')
		{
			$str = quoted_printable_decode($str);
		}

		//PHP sets us-ascii when the charset is unknown. Try to detect it.
		if(strtolower($part_charset)=='us-ascii')
		{
			if(function_exists('mb_detect_encoding'))
			{
				$part_charset = mb_detect_encoding($str, "ASCII,JIS,UTF-8,ISO-8859-1,ISO-8859-15,EUC-JP,SJIS");
			}else
			{
				$part_charset = 'ISO-8859-1';
			}
		}

		if ($part_charset != '' && $part_charset != $charset) {
			if($converted = @iconv($part_charset, $charset.'//IGNORE', $str))
			{
				return str_replace($part_charset, $charset, $converted);
			}
		}
		return ($str);
	}

	function f($name) {
		$value = isset ($this->message[$name]) ? $this->message[$name] : false;
		return $value;
	}

	/**
		* Get the delimiter that is used to delimit Mailbox names
		*
		* @access public
		* @return mixed The delimiter or false on failure
		*/

	function get_mailbox_delimiter() {
		$list = imap_getmailboxes($this->conn, "{".$this->connectstring."}", '%');
		if (is_array($list)) {
			$folder = array_shift($list);
			if (strlen($folder->delimiter) > 0) {
				return $folder->delimiter;
			}
		}
		return false;
	}

	/**
		* Check if the given mailbox root is valid and return it with the correct delimiter
		*
		* @param $mbroot The Mailbox root. (eg. INBOX/)
		* @access public
		* @return mixed Mailbox root with delimiter or false on failure
		*/

	function check_mbroot($mbroot) {
		$mbroot = trim($mbroot);

		$list = imap_getmailboxes($this->conn, "{".$this->connectstring."}", '%');
		if (is_array($list)) {
			while ($folder = array_shift($list)) {
				if (!isset ($delimiter) && strlen($folder->delimiter) > 0) {
					$delimiter = $folder->delimiter;
					if (substr($mbroot, -1) == $delimiter) {
						$mbroot = substr($mbroot, 0, -1);
					}
				}

				if (str_replace("{".$this->connectstring."}", "", $folder->name) == $mbroot) {
					return $mbroot.$delimiter;
				}
			}
		}
		return false;
	}

	/**
		* Return all mailboxes in an array with name, delimiter and attributes
		*
		* @param String $mailbox_root The mailbox root
		* @param bool $name_only Return only the mailbox names in the result
		* @access public
		* @return array The mailboxes
		*/
	function get_mailboxes($mailbox_root = '', $name_only = false) {

		//echo $mailbox_root.' -- > ';

		//echo '>"'.$mailbox_root.'"   ->   "'.$this->utf7_encode($mailbox_root).'"<hr />';

		$this->mailboxes = array ();
		$list = imap_getmailboxes($this->conn, "{".$this->connectstring."}", $mailbox_root.'%');
		if (is_array($list)) {
			foreach ($list as $value) {
				if (substr($value->name, -1) != $value->delimiter && strlen($value->delimiter) > 0) {
					if ($name_only) {
						$this->mailboxes[] = str_replace("{".$this->connectstring."}", "", $value->name);
					} else {

						$mailbox['name'] = str_replace("{".$this->connectstring."}", "", $value->name);
						$mailbox['delimiter'] = $value->delimiter;
						$mailbox['attributes'] = $value->attributes;
						$this->mailboxes[] = $mailbox;
						if (!($mailbox['attributes'] & LATT_NOINFERIORS)) {
							$this->mailboxes = array_merge($this->mailboxes, $this->get_mailboxes($mailbox['name'].$mailbox['delimiter']));
						}
					}
				}
			}
		}

		return $this->mailboxes;
	}

	/**
		* Return all subscribed mailboxes in an array with name, delimiter and attributes
		*
		* @param String $mailbox_root The mailbox root
		* @param bool $name_only Return only the mailbox names in the result
		* @access public
		* @return array The subscribed mailboxes
		*/
	function get_subscribed($mailbox_root = '', $name_only = false) {
		$this->mailboxes = array ();
		$list = imap_getsubscribed($this->conn, "{".$this->connectstring."}", $mailbox_root.'%');
		if (is_array($list)) {
			foreach ($list as $value) {
				if (substr($value->name, -1) != $value->delimiter && strlen($value->delimiter) > 0) {
					$mailbox['name'] = str_replace("{".$this->connectstring."}", "",$value->name);

					$mailbox['delimiter'] = $value->delimiter;
					$mailbox['attributes'] = $value->attributes;

					if ($name_only) {
						$this->mailboxes[] = $mailbox['name'];
					} else {
						$this->mailboxes[] = $mailbox;
					}

					if (!($mailbox['attributes'] & LATT_NOINFERIORS)) {
						$this->mailboxes = array_merge($this->mailboxes, $this->get_subscribed($mailbox['name'].$mailbox['delimiter'], $name_only));
					}
				}
			}
		}

		return $this->mailboxes;
	}

	/**
		* Check if a mailbox is subscribed
		*
		* @param String $name The name of the mailbox
		* @param String $mailbox_root The mailbox root
		* @access public
		* @return bool
		*/
	function is_subscribed($name, $mailbox_root) {
		$this->get_subscribed($mailbox_root);
		for ($i = 0; $i < count($this->mailboxes); $i ++) {
			if ($this->mailboxes[$i]['name'] == $name) {
				return true;
			}
		}
		return false;
	}

	/**
		* Subscribe a mailbox
		*
		* @param String $name The name of the mailbox
		* @access public
		* @return bool True on success
		*/
	function subscribe($name) {
		return imap_subscribe($this->conn, "{".$this->connectstring."}".$name);
	}

	/**
		* Unsubscribe a mailbox
		*
		* @param String $name The name of the mailbox
		* @access public
		* @return bool True on success
		*/
	function unsubscribe($name) {
		return imap_unsubscribe($this->conn, "{".$this->connectstring."}".$name);
	}

	/**
		* Delete a mailbox
		*
		* @param String $name The name of the mailbox
		* @param String $mailbox_root The mailbox root
		* @access public
		* @return bool True on success
		*/
	function delete_folder($name, $mailbox_root) {
		
		$children = $this->get_mailboxes($name);
		//debug($children);
		foreach($children as $child)
		{
			if($child['name']!=$name)
			{
				if(!$this->delete_folder($child['name'], $mailbox_root))
				{
					return false;
				}
			}
		}
		
		//$this->unsubscribe($name);
		return imap_deletemailbox($this->conn, "{".$this->connectstring."}".$name);		
	}

	/**
		* Create a mailbox
		*
		* @param String $name The name of the mailbox
		* @access public
		* @return bool True on success
		*/
	function create_folder($name, $delimiter='.') {

		//echo imap_utf7_encode(utf8_decode($name)).' -> '.$this->utf7_encode($name);

		if (imap_createmailbox($this->conn, "{".$this->connectstring."}".$name)) {
			return $this->subscribe($name);
		}
	}

	/**
		* Rename a mailbox
		*
		* @param String $old_name The current name of the mailbox
		* @param String $new_name The new name of the mailbox
		* @access public
		* @return bool True on success
		*/
	function rename_folder($old_name, $new_name) {

		$children = $this->get_mailboxes($old_name);
		if(imap_renamemailbox($this->conn, "{".$this->connectstring."}".$old_name, "{".$this->connectstring."}".$new_name))
		{
			foreach($children as $old_child)
			{
				$old_child = $old_child['name'];
				$pos = strpos($old_child, $old_name);
				$new_child = substr_replace($old_child, $new_name, $pos, strlen($old_name));
				//echo 'Renaming: '.$old_child.' to: '.$new_child.' <br />';
				if(!$this->unsubscribe($old_child))
				{
					return false;
				}elseif(!$this->subscribe($new_child))
				{
					if(imap_renamemailbox($this->conn, "{".$this->connectstring."}".$old_child, "{".$this->connectstring."}".$new_child))
					{
						if(!$this->subscribe($new_child))
						{
							return false;
						}
					}
				}
			}
		}
		return true;
	}




	/**
		* Move messages to another mailbox
		*
		* @param String $folder The mailbox where the messages need to go
		* @param Array $messages An array of message UID's to move
		* @access public
		* @return bool True on success
		*/
	function move($folder, $messages, $expunge=true) {
		$messageset = implode(",", $messages);
		if (imap_mail_move($this->conn, $messageset, $folder, CP_UID)) {
			
			if(!in_array($folder, $this->touched_folders))
			{
				$this->touched_folders[]=$folder;
			}
			
			if($expunge)
				$this->expunge();
			
			return true;
		}
		return false;
	}
	
	function expunge()
	{
		imap_expunge($this->conn);
		//debug('Expunged');
	}

	/**
		* Append a message to a maibox
		*
		* @param String $mailbox The mailbox where the message needs to go
		* @param String $body The message body
		* @param Int $flags The message flags (Unseen, Replied, flagged)
		* @access public
		* @return bool True on success
		*/
	function append_message($mailbox, $body, $flags = "") {
		if (@ imap_append($this->conn, "{".$this->connectstring."}".$mailbox, $body, $flags)) {
			return true;
		} else {
			return false;
		}
	}

	/**
		* Set message flags
		*
		* @param String $mailbox The mailbox where the message is
		* @param String $uid_array An array of message UID's
		* @param Int $flags The message flags (Unseen, Replied, flagged)
		* @param String $action If action is reset the given flags will be cleared
		* @access public
		* @return bool True on success
		*/
	function set_message_flag($mailbox = "INBOX", $uid_array, $flags, $action = "") {
		if ($mailbox == $this->mailbox || $this->reopen($mailbox)) {
			$msgno_set = implode(",", $uid_array);

			if ($action == "reset") {
				if (imap_clearflag_full($this->conn, $msgno_set, $flags, ST_UID)) {
					return true;
				} else {
					return false;
				}
			} else {
				if (imap_setflag_full($this->conn, $msgno_set, $flags, ST_UID)) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/**
		* Re-open a mailbox
		*
		* @param String $mailbox The mailbox to open
		* @param Int $flags Connection flags see imap_open() PHP docs.
		* @access public
		* @return bool True on success
		*/
	function reopen($mailbox = "INBOX", $flags = "") {
		if (imap_reopen($this->conn, "{".$this->connectstring."}".$mailbox, $flags)) {
			$this->mailbox = $mailbox;
			return true;
		} else {
			return false;
		}
	}

	/**
		* Get mailbox message info and put it in $this->mailbox_info.
		*
		* @access public
		* @return bool True on success
		*/
	function mailbox_info() {
		$info = imap_mailboxmsginfo($this->conn);
		if ($info) {
			$this->mailbox_info = array ();
			$this->mailbox_info["date"] = $info->Date;
			$this->mailbox_info["driver"] = $info->Driver;
			$this->mailbox_info["mailbox"] = $info->Mailbox;
			$this->mailbox_info["nmsgs"] = $info->Nmsgs;
			$this->mailbox_info["recent"] = $info->Recent;
			$this->mailbox_info["size"] = $info->Size;
			return true;
		} else {
			return false;
		}
	}


	function enc_utf8($str) {
		//some mail clients create encoded strings such: =?iso-8859-1?Q? "Andr=E9=20Mc=20Intyre" ?=
		//containing space values inside, but they mustn't. The space values have to be removed before
		//they are going to be converted to utf8.

		if (strpos($str, '=?') !== false) {
			//$str = str_replace(" ", "", $str);
			//return imap_utf8($str);
			return @iconv_mime_decode($str,null,'UTF-8');
		}else {
			if (function_exists('iconv'))
			{
				if($converted = @iconv('ISO-8859-15', $GLOBALS['charset'].'//IGNORE', $str))
				{
					return $converted;
				}
			}
		}
		return $str;

		/*
		 $text = '';
		 if($elements = imap_mime_header_decode($str))
		 {
		 foreach($elements as $element)
		 {
			$text .= $element->text;
			}
			return utf8_encode($text);
			}	*/
	}


	function encode_imap_path($str, $delimiter='.')
	{
		$encoded = array();
		$mailboxes = explode($delimiter, $str);
		foreach($mailboxes as $mailbox)
		{
			$encoded[]=$this->utf7_encode($mailbox);
		}
		return implode($delimiter, $encoded);
	}

	function decode_imap_path($str, $delimiter='.')
	{
		$decoded = array();
		$mailboxes = explode($delimiter, $str);
		foreach($mailboxes as $mailbox)
		{
			$decoded[]=$this->utf7_decode($mailbox);
		}
		return implode($delimiter, $decoded);
	}

	function utf7_imap_encode($input)
	{
//		$charset = 'UTF-8';
//		if (extension_loaded('iconv')) {
//			if($converted_input = @iconv($charset, 'UTF-7', $input))
//			{
//				$input=preg_replace("/&/","&-",$converted_input);
//				$input=preg_replace("/\+A/","&A",$input);
//				$input=preg_replace("/&ACY /","&- ",$input);
//				$input=preg_replace("/&ACY/","&",$input);
//				$input=preg_replace("/&AF8(-)?/","_",$input);
//				$input=preg_replace("/&ADs(-)?/",";",$input);
//				return $input;
//			}
//		}
//		return @imap_utf7_encode(utf8_decode($input));
    $converted_input = @mb_convert_encoding($input, "UTF7-IMAP", "UTF8");
    return $converted_input;
	}



	function utf7_imap_decode($input)
	{
//		$charset='UTF-8';
//
//		if (extension_loaded('iconv')) {
//			$input=preg_replace("/&A/","+A",$input);
//			$input=preg_replace("/&-/","&",$input);
//
//			if($converted_input = @iconv('UTF-7', $charset, $input))
//			{
//				return $converted_input;
//			}
//		}
//		return utf8_encode(@imap_utf7_decode($input));
		
		$converted_input = @mb_convert_encoding($input, "UTF8", "UTF7-IMAP");
		return $converted_input;
	}
	
	
	function extract_uuencoded_attachments(&$body)
	{
		$body = str_replace("\r", '', $body);
		$regex = "/(begin ([0-7]{3}) (.+))\n(.+)\nend/Us";
    
		preg_match_all($regex, $body, $matches);
      
    $attachments = array();
        
    for ($i = 0; $i < count($matches[3]); $i++) {
			$boundary	= $matches[1][$i];
			$fileperm	= $matches[2][$i];
			$filename	= $matches[3][$i];
			
			$size = strlen($matches[4][$i]);
			
			$attachments[]=array(
				'boundary'=>$matches[1][$i],				
				'permissions'=>$matches[2][$i],
				'name'=>$matches[3][$i],
				'data'=>$matches[4][$i],
				'disposition'=>'ATTACHMENT',
				'type'=>'',
				'size'=>$size,
				'human_size'=>Number::format_size($size),
				'mime'=>File::get_mime($matches[3][$i])			
			);
    }
    
    //remove it from the body.
    $body = preg_replace($regex, "", $body);
    //debug($body);
    
    return $attachments;
	}


  /**
   * Convert plain text to HTML
   *
   * @param string $text Plain text string
   * @access public
   * @return string HTML formatted string
   */
  function text_to_html($text) {
    $text = nl2br($text);
    $text = str_replace("\r", "", $text);
    $text = str_replace("\n", "", $text);

    return ($text);
  }



  /**
   * Convert Dangerous HTML to safe HTML for display inside of Group-Office
   *
   * This also removes everything outside the body and replaces mailto links
   *
   * @param string $text Plain text string
   * @access public
   * @return string HTML formatted string
   */
  function convert_html($html) {

    $html = str_replace("\r", '', $html);
    $html = str_replace("\n",' ', $html);
  
    $to_removed_array = array (
      "'<html[^>]*>'si",
      "'</html>'si",
      "'<body[^>]*>'si",
      "'</body>'si",
      "'<meta[^>]*>'si",
      "'<head[^>]*>.*?</head>'si",
      "'<style[^>]*>.*?</style>'si",
      "'<script[^>]*>.*?</script>'si",
      "'<object[^>]*>.*?</object>'si",
      "'<embed[^>]*>.*?</embed>'si",
      "'<applet[^>]*>.*?</applet>'si",
      "'<form[^>]*>'si",
      "'<input[^>]*>'si",
      "'<select[^>]*>.*?</select>'si",
      "'<textarea[^>]*>.*?</textarea>'si",
      "'</form>'si"
    );
  
    $html = preg_replace($to_removed_array, '', $html);
    $html = preg_replace("/([\"']?)javascript:/i", "$1removed_script:", $html);
  
    return $html;
  }
  

  /**
   * Convert an enriched formated string to HTML format
   *
   * @param string $enriched Enriched formatted string
   * @access public
   * @return string HTML formated string
   */
  function enriched_to_html($enriched) {

    // We add space at the beginning and end of the string as it will
    // make some regular expression checks later much easier (so we
    // don't have to worry about start/end of line characters)
    $enriched = ' '.$enriched.' ';

    // Get color parameters into a more useable format.
    $enriched = preg_replace('/<color><param>([\da-fA-F]+),([\da-fA-F]+),([\da-fA-F]+)<\/param>/Uis', '<color r=\1 g=\2 b=\3>', $enriched);
    $enriched = preg_replace('/<color><param>(red|blue|green|yellow|cyan|magenta|black|white)<\/param>/Uis', '<color n=\1>', $enriched);

    // Get font family parameters into a more useable format.
    $enriched = preg_replace('/<fontfamily><param>(\w+)<\/param>/Uis', '<fontfamily f=\1>', $enriched);

    // Single line breaks become spaces, double line breaks are a
    // real break. This needs to do <nofill> tracking to be
    // compliant but we don't want to deal with state at this
    // time, so we fake it some day we should rewrite this to
    // handle <nofill> correctly.
    $enriched = preg_replace('/([^\n])\r\n([^\r])/', '\1 \2', $enriched);
    $enriched = preg_replace('/(\r\n)\r\n/', '\1', $enriched);

    // We try to protect against bad stuff here.
    $enriched = @ htmlspecialchars($enriched, ENT_QUOTES);

    // Now convert the known tags to html. Try to remove any tag
    // parameters to stop people from trying to pull a fast one
    $enriched = preg_replace('/(?<!&lt;)&lt;bold.*&gt;(.*)&lt;\/bold&gt;/Uis', '<span style="font-weight: bold">\1</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;italic.*&gt;(.*)&lt;\/italic&gt;/Uis', '<span style="font-style: italic">\1</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;underline.*&gt;(.*)&lt;\/underline&gt;/Uis', '<span style="text-decoration: underline">\1</span>', $enriched);
    $enriched = preg_replace_callback('/(?<!&lt;)&lt;color r=([\da-fA-F]+) g=([\da-fA-F]+) b=([\da-fA-F]+)&gt;(.*)&lt;\/color&gt;/Uis', 'colorize', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;color n=(red|blue|green|yellow|cyan|magenta|black|white)&gt;(.*)&lt;\/color&gt;/Uis', '<span style="color: \1">\2</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;fontfamily&gt;(.*)&lt;\/fontfamily&gt;/Uis', '\1', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;fontfamily f=(\w+)&gt;(.*)&lt;\/fontfamily&gt;/Uis', '<span style="font-family: \1">\2</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;smaller.*&gt;/Uis', '<span style="font-size: smaller">', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;\/smaller&gt;/Uis', '</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;bigger.*&gt;/Uis', '<span style="font-size: larger">', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;\/bigger&gt;/Uis', '</span>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;fixed.*&gt;(.*)&lt;\/fixed&gt;/Uis', '<font face="fixed">\1</font>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;center.*&gt;(.*)&lt;\/center&gt;/Uis', '<div align="center">\1</div>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;flushleft.*&gt;(.*)&lt;\/flushleft&gt;/Uis', '<div align="left">\1</div>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;flushright.*&gt;(.*)&lt;\/flushright&gt;/Uis', '<div align="right">\1</div>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;flushboth.*&gt;(.*)&lt;\/flushboth&gt;/Uis', '<div align="justify">\1</div>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;paraindent.*&gt;(.*)&lt;\/paraindent&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);
    $enriched = preg_replace('/(?<!&lt;)&lt;excerpt.*&gt;(.*)&lt;\/excerpt&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);

    // Now we remove the leading/trailing space we added at the
    // start.
    $enriched = preg_replace('/^ (.*) $/s', '\1', $enriched);

    $enriched = nl2br($enriched);
    $enriched = str_replace("\r", "", $enriched);
    $enriched = str_replace("\n", "", $enriched);

    return $enriched;
  }
  
  /**
   * Returns the extension of a filename
   *
   * @param string $filename The complete filename
   * @access public
   * @return string  The extension of a filename
   */
  function get_extension($filename=null) {
    $extension = '';
    $pos = strrpos($filename, '.');
    if ($pos) {
      $extension = substr($filename, $pos +1, strlen($filename));
    }
    return strtolower($extension);
  }
  
  /**
   * Format a size to a human readable format.
   *
   * @param int $size The size in bytes
   * @param int $decimals Number of decimals to display
   * @access public
   * @return string
   */

  function format_size($size, $decimals = 2) {
    global $osC_Language;
    
    if($size==0)
      return 0;
   
    $decimal_separator = $osC_Language->getNumericDecimalSeparator();
    $thousands_separator = $osC_Language->getNumericThousandsSeparator();
      
    switch ($size) {
      case ($size > 1073741824) :
        $size = number_format($size / 1073741824, $decimals, $decimal_separator, $thousands_separator);
        $size .= " GB";
        break;

      case ($size > 1048576) :
        $size = number_format($size / 1048576, $decimals, $decimal_separator, $thousands_separator);
        $size .= " MB";
        break;

      case ($size > 1024) :
        $size = number_format($size / 1024, $decimals, $decimal_separator, $thousands_separator);
        $size .= " KB";
        break;

      default :
        $size = number_format($size, $decimals, $decimal_separator, $thousands_separator);
        $size .= " bytes";
        break;
    }
    return $size;
  }
  
  function handleEncodedFilename($name) {
    $imapDecode = imap_mime_header_decode($name);
    if ($imapDecode[0]->charset != 'default') {
      $encoding = $imapDecode[0]->charset;
      $name = $imapDecode[0]->text; 
    } else {
      if (strpos($name, "''") !== false) {
        $encoding = substr($name, 0, strpos($name, "'"));
        while (strpos($name, "'") !== false) {
          $name = trim(substr($name, (strpos($name, "'") + 1), strlen($name)));
        }
      }
      
      $name = urldecode($name);
    }
    
    if (isset($encoding)) {
      return (strtolower($encoding) == 'utf-8') ? $name : mb_convert_encoding($name, 'UTF-8', $encoding);
    } else {
      return $name;
    }
  }

  function handleTranserEncoding($str, $enc = 0) {
    switch ($enc) {
      case 2:
        $ret = $str;
        break;
      case 3:
        $ret = base64_decode($str);
        break;
      case 4:
        $ret = quoted_printable_decode($str);
        break;
      case 0:
      case 1:
      case 5:
      default:
        $ret = $str;
        break;
    }
    return $ret;
  }
}
