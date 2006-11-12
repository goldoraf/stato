<?php
////////////////////////////////////////////////////
// PHPMailer - PHP email class
//
// Class for sending email using either
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Copyright (C) 2001 - 2003  Brent R. Matzelle
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * PHPMailer - PHP email transport class
 * @package PHPMailer
 * @author Brent R. Matzelle
 * @copyright 2001 - 2003 Brent R. Matzelle
 */
class PHPMailer
{
    /////////////////////////////////////////////////
    // PUBLIC VARIABLES
    /////////////////////////////////////////////////

    /**
     * Email priority (1 = High, 3 = Normal, 5 = low).
     * @var int
     */
    var $priority          = 3;

    /**
     * Sets the CharSet of the message.
     * @var string
     */
    var $char_set           = "iso-8859-1";

    /**
     * Sets the Content-type of the message.
     * @var string
     */
    var $content_type        = "text/plain";

    /**
     * Sets the Encoding of the message. Options for this are "8bit",
     * "7bit", "binary", "base64", and "quoted-printable".
     * @var string
     */
    var $encoding          = "8bit";

    /**
     * Holds the most recent mailer error message.
     * @var string
     */
    var $error_info         = "";

    /**
     * Sets the From email address for the message.
     * @var string
     */
    var $from               = "root@localhost";

    /**
     * Sets the From name of the message.
     * @var string
     */
    var $from_name           = "Root User";

    /**
     * Sets the Sender email (Return-Path) of the message.  If not empty,
     * will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
     * @var string
     */
    var $sender            = "";

    /**
     * Sets the Subject of the message.
     * @var string
     */
    var $subject           = "";

    /**
     * Sets the Body of the message.  This can be either an HTML or text body.
     * If HTML then run IsHTML(true).
     * @var string
     */
    var $body               = "";

    /**
     * Sets the text-only body of the message.  This automatically sets the
     * email to multipart/alternative.  This body can be read by mail
     * clients that do not have HTML email capability such as mutt. Clients
     * that can read HTML will view the normal Body.
     * @var string
     */
    var $alt_body           = "";

    /**
     * Sets word wrapping on the body of the message to a given number of 
     * characters.
     * @var int
     */
    var $word_wrap          = 0;

    /**
     * Method to send mail: ("mail", "sendmail", or "smtp").
     * @var string
     */
    var $mailer            = "mail";

    /**
     * Sets the path of the sendmail program.
     * @var string
     */
    var $sendmail          = "/usr/sbin/sendmail";
    
    /**
     * Path to PHPMailer plugins.  This is now only useful if the SMTP class 
     * is in a different directory than the PHP include path.  
     * @var string
     */
    var $plugin_dir         = "";

    /**
     *  Holds PHPMailer version.
     *  @var string
     */
    var $version           = "1.73";

    /**
     * Sets the email address that a reading confirmation will be sent.
     * @var string
     */
    var $confirm_reading_to  = "";

    /**
     *  Sets the hostname to use in Message-Id and Received headers
     *  and as default HELO string. If empty, the value returned
     *  by SERVER_NAME is used or 'localhost.localdomain'.
     *  @var string
     */
    var $hostname          = "";

    /////////////////////////////////////////////////
    // SMTP VARIABLES
    /////////////////////////////////////////////////

    /**
     *  Sets the SMTP hosts.  All hosts must be separated by a
     *  semicolon.  You can also specify a different port
     *  for each host by using this format: [hostname:port]
     *  (e.g. "smtp1.example.com:25;smtp2.example.com").
     *  Hosts will be tried in order.
     *  @var string
     */
    var $host        = "localhost";

    /**
     *  Sets the default SMTP server port.
     *  @var int
     */
    var $port        = 25;

    /**
     *  Sets the SMTP HELO of the message (Default is $hostname).
     *  @var string
     */
    var $helo        = "";

    /**
     *  Sets SMTP authentication. Utilizes the Username and Password variables.
     *  @var bool
     */
    var $smtp_auth     = false;

    /**
     *  Sets SMTP username.
     *  @var string
     */
    var $username     = "";

    /**
     *  Sets SMTP password.
     *  @var string
     */
    var $password     = "";

    /**
     *  Sets the SMTP server timeout in seconds. This function will not 
     *  work with the win32 version.
     *  @var int
     */
    var $timeout      = 10;

    /**
     *  Sets SMTP class debugging on or off.
     *  @var bool
     */
    var $smtp_debug    = false;

    /**
     * Prevents the SMTP connection from being closed after each mail 
     * sending.  If this is set to true then to close the connection 
     * requires an explicit call to SmtpClose(). 
     * @var bool
     */
    var $smtp_keep_alive = false;

    /**#@+
     * @access private
     */
    var $smtp            = NULL;
    var $to              = array();
    var $cc              = array();
    var $bcc             = array();
    var $reply_to         = array();
    var $attachment      = array();
    var $custom_header    = array();
    var $message_type    = "";
    var $boundary        = array();
    var $language        = array();
    var $error_count     = 0;
    var $le              = "\n";
    /**#@-*/
    
    /////////////////////////////////////////////////
    // VARIABLE METHODS
    /////////////////////////////////////////////////

    /**
     * Sets message type to HTML.  
     * @param bool $bool
     * @return void
     */
    function is_html($bool) {
        if($bool == true)
            $this->content_type = "text/html";
        else
            $this->content_type = "text/plain";
    }

    /**
     * Sets Mailer to send message using SMTP.
     * @return void
     */
    function is_smtp() {
        $this->mailer = "smtp";
    }

    /**
     * Sets Mailer to send message using PHP mail() function.
     * @return void
     */
    function is_mail() {
        $this->mailer = "mail";
    }

    /**
     * Sets Mailer to send message using the $sendmail program.
     * @return void
     */
    function is_sendmail() {
        $this->mailer = "sendmail";
    }

    /**
     * Sets Mailer to send message using the qmail MTA. 
     * @return void
     */
    function is_qmail() {
        $this->sendmail = "/var/qmail/bin/sendmail";
        $this->mailer = "sendmail";
    }


    /////////////////////////////////////////////////
    // RECIPIENT METHODS
    /////////////////////////////////////////////////

    /**
     * Adds a "To" address.  
     * @param string $address
     * @param string $name
     * @return void
     */
    function add_address($address, $name = "") {
        $cur = count($this->to);
        $this->to[$cur][0] = trim($address);
        $this->to[$cur][1] = $name;
    }

    /**
     * Adds a "Cc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  
     * @param string $address
     * @param string $name
     * @return void
    */
    function add_cc($address, $name = "") {
        $cur = count($this->cc);
        $this->cc[$cur][0] = trim($address);
        $this->cc[$cur][1] = $name;
    }

    /**
     * Adds a "Bcc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  
     * @param string $address
     * @param string $name
     * @return void
     */
    function add_bcc($address, $name = "") {
        $cur = count($this->bcc);
        $this->bcc[$cur][0] = trim($address);
        $this->bcc[$cur][1] = $name;
    }

    /**
     * Adds a "Reply-to" address.  
     * @param string $address
     * @param string $name
     * @return void
     */
    function add_reply_to($address, $name = "") {
        $cur = count($this->reply_to);
        $this->reply_to[$cur][0] = trim($address);
        $this->reply_to[$cur][1] = $name;
    }


    /////////////////////////////////////////////////
    // MAIL SENDING METHODS
    /////////////////////////////////////////////////

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.  
     * @return bool
     */
    function send() {
        $header = "";
        $body = "";
        $result = true;

        if((count($this->to) + count($this->cc) + count($this->bcc)) < 1)
        {
            $this->set_error($this->lang("provide_address"));
            return false;
        }

        // Set whether the message is multipart/alternative
        if(!empty($this->alt_body))
            $this->content_type = "multipart/alternative";

        $this->error_count = 0; // reset errors
        $this->set_message_type();
        $header .= $this->create_header();
        $body = $this->create_body();

        if($body == "") { return false; }

        // Choose the mailer
        switch($this->mailer)
        {
            case "sendmail":
                $result = $this->sendmail_send($header, $body);
                break;
            case "mail":
                $result = $this->mail_send($header, $body);
                break;
            case "smtp":
                $result = $this->smtp_send($header, $body);
                break;
            default:
            $this->set_error($this->mailer . $this->lang("mailer_not_supported"));
                $result = false;
                break;
        }

        return $result;
    }
    
    /**
     * Sends mail using the $sendmail program.  
     * @access private
     * @return bool
     */
    function sendmail_send($header, $body) {
        if ($this->sender != "")
            $sendmail = sprintf("%s -oi -f %s -t", $this->sendmail, $this->sender);
        else
            $sendmail = sprintf("%s -oi -t", $this->sendmail);

        if(!@$mail = popen($sendmail, "w"))
        {
            $this->set_error($this->lang("execute") . $this->sendmail);
            return false;
        }

        fputs($mail, $header);
        fputs($mail, $body);
        
        $result = pclose($mail) >> 8 & 0xFF;
        if($result != 0)
        {
            $this->set_error($this->lang("execute") . $this->sendmail);
            return false;
        }

        return true;
    }

    /**
     * Sends mail using the PHP mail() function.  
     * @access private
     * @return bool
     */
    function mail_send($header, $body) {
        $to = "";
        for($i = 0; $i < count($this->to); $i++)
        {
            if($i != 0) { $to .= ", "; }
            $to .= $this->to[$i][0];
        }

        if ($this->sender != "" && strlen(ini_get("safe_mode"))< 1)
        {
            $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $this->sender);
            $params = sprintf("-oi -f %s", $this->sender);
            $rt = @mail($to, $this->encode_header($this->subject), $body, 
                        $header, $params);
        }
        else
            $rt = @mail($to, $this->encode_header($this->subject), $body, $header);

        if (isset($old_from))
            ini_set("sendmail_from", $old_from);

        if(!$rt)
        {
            $this->set_error($this->lang("instantiate"));
            return false;
        }

        return true;
    }

    /**
     * Sends mail via SMTP using PhpSMTP (Author:
     * Chris Ryan).  Returns bool.  Returns false if there is a
     * bad MAIL FROM, RCPT, or DATA input.
     * @access private
     * @return bool
     */
    function smtp_send($header, $body) {
        include_once($this->plugin_dir . "class.smtp.php");
        $error = "";
        $bad_rcpt = array();

        if(!$this->smtp_connect())
            return false;

        $smtp_from = ($this->sender == "") ? $this->from : $this->sender;
        if(!$this->smtp->mail($smtp_from))
        {
            $error = $this->lang("from_failed") . $smtp_from;
            $this->set_error($error);
            $this->smtp->reset();
            return false;
        }

        // Attempt to send attach all recipients
        for($i = 0; $i < count($this->to); $i++)
        {
            if(!$this->smtp->recipient($this->to[$i][0]))
                $bad_rcpt[] = $this->to[$i][0];
        }
        for($i = 0; $i < count($this->cc); $i++)
        {
            if(!$this->smtp->recipient($this->cc[$i][0]))
                $bad_rcpt[] = $this->cc[$i][0];
        }
        for($i = 0; $i < count($this->bcc); $i++)
        {
            if(!$this->smtp->recipient($this->bcc[$i][0]))
                $bad_rcpt[] = $this->bcc[$i][0];
        }

        if(count($bad_rcpt) > 0) // Create error message
        {
            for($i = 0; $i < count($bad_rcpt); $i++)
            {
                if($i != 0) { $error .= ", "; }
                $error .= $bad_rcpt[$i];
            }
            $error = $this->lang("recipients_failed") . $error;
            $this->set_error($error);
            $this->smtp->reset();
            return false;
        }

        if(!$this->smtp->data($header . $body))
        {
            $this->set_error($this->lang("data_not_accepted"));
            $this->smtp->reset();
            return false;
        }
        if($this->smtp_keep_alive == true)
            $this->smtp->reset();
        else
            $this->smtp_close();

        return true;
    }

    /**
     * Initiates a connection to an SMTP server.  Returns false if the 
     * operation failed.
     * @access private
     * @return bool
     */
    function smtp_connect() {
        if($this->smtp == NULL) { $this->smtp = new SMTP(); }

        $this->smtp->do_debug = $this->smtp_debug;
        $hosts = explode(";", $this->host);
        $index = 0;
        $connection = ($this->smtp->connected()); 

        // Retry while there is no connection
        while($index < count($hosts) && $connection == false)
        {
            if(strstr($hosts[$index], ":"))
                list($host, $port) = explode(":", $hosts[$index]);
            else
            {
                $host = $hosts[$index];
                $port = $this->port;
            }

            if($this->smtp->connect($host, $port, $this->timeout))
            {
                if ($this->helo != '')
                    $this->smtp->hello($this->helo);
                else
                    $this->smtp->hello($this->server_hostname());
        
                if($this->smtp_auth)
                {
                    if(!$this->smtp->authenticate($this->username, 
                                                  $this->password))
                    {
                        $this->set_error($this->lang("authenticate"));
                        $this->smtp->reset();
                        $connection = false;
                    }
                }
                $connection = true;
            }
            $index++;
        }
        if(!$connection)
            $this->set_error($this->lang("connect_host"));

        return $connection;
    }

    /**
     * Closes the active SMTP session if one exists.
     * @return void
     */
    function smtp_close() {
        if($this->smtp != NULL)
        {
            if($this->smtp->connected())
            {
                $this->smtp->quit();
                $this->smtp->close();
            }
        }
    }

    /**
     * Sets the language for all class error messages.  Returns false 
     * if it cannot load the language file.  The default language type
     * is English.
     * @param string $lang_type Type of language (e.g. Portuguese: "br")
     * @param string $lang_path Path to the language file directory
     * @access public
     * @return bool
     */
    function set_language($lang_type, $lang_path = "language/") {
        if(file_exists($lang_path.'phpmailer.lang-'.$lang_type.'.php'))
            include($lang_path.'phpmailer.lang-'.$lang_type.'.php');
        else if(file_exists($lang_path.'phpmailer.lang-en.php'))
            include($lang_path.'phpmailer.lang-en.php');
        else
        {
            $this->set_error("Could not load language file");
            return false;
        }
        $this->language = $phpmailer_lang;
    
        return true;
    }

    /////////////////////////////////////////////////
    // MESSAGE CREATION METHODS
    /////////////////////////////////////////////////

    /**
     * Creates recipient headers.  
     * @access private
     * @return string
     */
    function addr_append($type, $addr) {
        $addr_str = $type . ": ";
        $addr_str .= $this->addr_format($addr[0]);
        if(count($addr) > 1)
        {
            for($i = 1; $i < count($addr); $i++)
                $addr_str .= ", " . $this->addr_format($addr[$i]);
        }
        $addr_str .= $this->le;

        return $addr_str;
    }
    
    /**
     * Formats an address correctly. 
     * @access private
     * @return string
     */
    function addr_format($addr) {
        if(empty($addr[1]))
            $formatted = $addr[0];
        else
        {
            $formatted = $this->encode_header($addr[1], 'phrase') . " <" . 
                         $addr[0] . ">";
        }

        return $formatted;
    }

    /**
     * Wraps message for use with mailers that do not
     * automatically perform wrapping and for quoted-printable.
     * Original written by philippe.  
     * @access private
     * @return string
     */
    function wrap_text($message, $length, $qp_mode = false) {
        $soft_break = ($qp_mode) ? sprintf(" =%s", $this->le) : $this->le;

        $message = $this->fix_eol($message);
        if (substr($message, -1) == $this->le)
            $message = substr($message, 0, -1);

        $line = explode($this->le, $message);
        $message = "";
        for ($i=0 ;$i < count($line); $i++)
        {
          $line_part = explode(" ", $line[$i]);
          $buf = "";
          for ($e = 0; $e<count($line_part); $e++)
          {
              $word = $line_part[$e];
              if ($qp_mode and (strlen($word) > $length))
              {
                $space_left = $length - strlen($buf) - 1;
                if ($e != 0)
                {
                    if ($space_left > 20)
                    {
                        $len = $space_left;
                        if (substr($word, $len - 1, 1) == "=")
                          $len--;
                        elseif (substr($word, $len - 2, 1) == "=")
                          $len -= 2;
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);
                        $buf .= " " . $part;
                        $message .= $buf . sprintf("=%s", $this->le);
                    }
                    else
                    {
                        $message .= $buf . $soft_break;
                    }
                    $buf = "";
                }
                while (strlen($word) > 0)
                {
                    $len = $length;
                    if (substr($word, $len - 1, 1) == "=")
                        $len--;
                    elseif (substr($word, $len - 2, 1) == "=")
                        $len -= 2;
                    $part = substr($word, 0, $len);
                    $word = substr($word, $len);

                    if (strlen($word) > 0)
                        $message .= $part . sprintf("=%s", $this->le);
                    else
                        $buf = $part;
                }
              }
              else
              {
                $buf_o = $buf;
                $buf .= ($e == 0) ? $word : (" " . $word); 

                if (strlen($buf) > $length and $buf_o != "")
                {
                    $message .= $buf_o . $soft_break;
                    $buf = $word;
                }
              }
          }
          $message .= $buf . $this->le;
        }

        return $message;
    }
    
    /**
     * Set the body wrapping.
     * @access private
     * @return void
     */
    function set_word_wrap() {
        if($this->word_wrap < 1)
            return;
            
        switch($this->message_type)
        {
           case "alt":
              // fall through
           case "alt_attachments":
              $this->alt_body = $this->wrap_text($this->alt_body, $this->word_wrap);
              break;
           default:
              $this->body = $this->wrap_text($this->body, $this->word_wrap);
              break;
        }
    }

    /**
     * Assembles message header.  
     * @access private
     * @return string
     */
    function create_header() {
        $result = "";
        
        // Set the boundaries
        $uniq_id = md5(uniqid(time()));
        $this->boundary[1] = "b1_" . $uniq_id;
        $this->boundary[2] = "b2_" . $uniq_id;

        $result .= $this->header_line("Date", $this->rfc_date());
        if($this->sender == "")
            $result .= $this->header_line("Return-Path", trim($this->from));
        else
            $result .= $this->header_line("Return-Path", trim($this->sender));
        
        // To be created automatically by mail()
        if($this->mailer != "mail")
        {
            if(count($this->to) > 0)
                $result .= $this->addr_append("To", $this->to);
            else if (count($this->cc) == 0)
                $result .= $this->header_line("To", "undisclosed-recipients:;");
            if(count($this->cc) > 0)
                $result .= $this->addr_append("Cc", $this->cc);
        }

        $from = array();
        $from[0][0] = trim($this->from);
        $from[0][1] = $this->from_name;
        $result .= $this->addr_append("From", $from); 

        // sendmail and mail() extract Bcc from the header before sending
        if((($this->mailer == "sendmail") || ($this->mailer == "mail")) && (count($this->bcc) > 0))
            $result .= $this->addr_append("Bcc", $this->bcc);

        if(count($this->reply_to) > 0)
            $result .= $this->addr_append("Reply-to", $this->reply_to);

        // mail() sets the subject itself
        if($this->mailer != "mail")
            $result .= $this->header_line("Subject", $this->encode_header(trim($this->subject)));

        $result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->server_hostname(), $this->le);
        $result .= $this->header_line("X-Priority", $this->priority);
        $result .= $this->header_line("X-Mailer", "PHPMailer [version " . $this->version . "]");
        
        if($this->confirm_reading_to != "")
        {
            $result .= $this->header_line("Disposition-Notification-To", 
                       "<" . trim($this->confirm_reading_to) . ">");
        }

        // Add custom headers
        for($index = 0; $index < count($this->custom_header); $index++)
        {
            $result .= $this->header_line(trim($this->custom_header[$index][0]), 
                       $this->encode_header(trim($this->custom_header[$index][1])));
        }
        $result .= $this->header_line("MIME-Version", "1.0");

        switch($this->message_type)
        {
            case "plain":
                $result .= $this->header_line("Content-Transfer-Encoding", $this->encoding);
                $result .= sprintf("Content-Type: %s; charset=\"%s\"",
                                    $this->content_type, $this->char_set);
                break;
            case "attachments":
                // fall through
            case "alt_attachments":
                if($this->inline_image_exists())
                {
                    $result .= sprintf("Content-Type: %s;%s\ttype=\"text/html\";%s\tboundary=\"%s\"%s", 
                                    "multipart/related", $this->le, $this->le, 
                                    $this->boundary[1], $this->le);
                }
                else
                {
                    $result .= $this->header_line("Content-Type", "multipart/mixed;");
                    $result .= $this->text_line("\tboundary=\"" . $this->boundary[1] . '"');
                }
                break;
            case "alt":
                $result .= $this->header_line("Content-Type", "multipart/alternative;");
                $result .= $this->text_line("\tboundary=\"" . $this->boundary[1] . '"');
                break;
        }

        if($this->mailer != "mail")
            $result .= $this->le.$this->le;

        return $result;
    }

    /**
     * Assembles the message body.  Returns an empty string on failure.
     * @access private
     * @return string
     */
    function create_body() {
        $result = "";

        $this->set_word_wrap();

        switch($this->message_type)
        {
            case "alt":
                $result .= $this->get_boundary($this->boundary[1], "", 
                                              "text/plain", "");
                $result .= $this->encode_string($this->alt_body, $this->encoding);
                $result .= $this->le.$this->le;
                $result .= $this->get_boundary($this->boundary[1], "", 
                                              "text/html", "");
                
                $result .= $this->encode_string($this->body, $this->encoding);
                $result .= $this->le.$this->le;
    
                $result .= $this->end_boundary($this->boundary[1]);
                break;
            case "plain":
                $result .= $this->encode_string($this->body, $this->encoding);
                break;
            case "attachments":
                $result .= $this->get_boundary($this->boundary[1], "", "", "");
                $result .= $this->encode_string($this->body, $this->encoding);
                $result .= $this->le;
     
                $result .= $this->attach_all();
                break;
            case "alt_attachments":
                $result .= sprintf("--%s%s", $this->boundary[1], $this->le);
                $result .= sprintf("Content-Type: %s;%s" .
                                   "\tboundary=\"%s\"%s",
                                   "multipart/alternative", $this->le, 
                                   $this->boundary[2], $this->le.$this->le);
    
                // Create text body
                $result .= $this->get_boundary($this->boundary[2], "", 
                                              "text/plain", "") . $this->le;

                $result .= $this->encode_string($this->alt_body, $this->encoding);
                $result .= $this->le.$this->le;
    
                // Create the HTML body
                $result .= $this->get_boundary($this->boundary[2], "", 
                                              "text/html", "") . $this->le;
    
                $result .= $this->encode_string($this->body, $this->encoding);
                $result .= $this->le.$this->le;

                $result .= $this->end_boundary($this->boundary[2]);
                
                $result .= $this->attach_all();
                break;
        }
        if($this->is_error())
            $result = "";

        return $result;
    }

    /**
     * Returns the start of a message boundary.
     * @access private
     */
    function get_boundary($boundary, $char_set, $content_type, $encoding) {
        $result = "";
        if($char_set == "") { $char_set = $this->char_set; }
        if($content_type == "") { $content_type = $this->content_type; }
        if($encoding == "") { $encoding = $this->encoding; }

        $result .= $this->text_line("--" . $boundary);
        $result .= sprintf("Content-Type: %s; charset = \"%s\"", 
                            $content_type, $char_set);
        $result .= $this->le;
        $result .= $this->header_line("Content-Transfer-Encoding", $encoding);
        $result .= $this->le;
       
        return $result;
    }
    
    /**
     * Returns the end of a message boundary.
     * @access private
     */
    function end_boundary($boundary) {
        return $this->le . "--" . $boundary . "--" . $this->le; 
    }
    
    /**
     * Sets the message type.
     * @access private
     * @return void
     */
    function set_message_type() {
        if(count($this->attachment) < 1 && strlen($this->alt_body) < 1)
            $this->message_type = "plain";
        else
        {
            if(count($this->attachment) > 0)
                $this->message_type = "attachments";
            if(strlen($this->alt_body) > 0 && count($this->attachment) < 1)
                $this->message_type = "alt";
            if(strlen($this->alt_body) > 0 && count($this->attachment) > 0)
                $this->message_type = "alt_attachments";
        }
    }

    /**
     * Returns a formatted header line.
     * @access private
     * @return string
     */
    function header_line($name, $value) {
        return $name . ": " . $value . $this->le;
    }

    /**
     * Returns a formatted mail line.
     * @access private
     * @return string
     */
    function text_line($value) {
        return $value . $this->le;
    }

    /////////////////////////////////////////////////
    // ATTACHMENT METHODS
    /////////////////////////////////////////////////

    /**
     * Adds an attachment from a path on the filesystem.
     * Returns false if the file could not be found
     * or accessed.
     * @param string $path Path to the attachment.
     * @param string $name Overrides the attachment name.
     * @param string $encoding File encoding (see $encoding).
     * @param string $type File extension (MIME) type.
     * @return bool
     */
    function add_attachment($path, $name = "", $encoding = "base64", 
                           $type = "application/octet-stream") {
        if(!@is_file($path))
        {
            $this->set_error($this->lang("file_access") . $path);
            return false;
        }

        $filename = basename($path);
        if($name == "")
            $name = $filename;

        $cur = count($this->attachment);
        $this->attachment[$cur][0] = $path;
        $this->attachment[$cur][1] = $filename;
        $this->attachment[$cur][2] = $name;
        $this->attachment[$cur][3] = $encoding;
        $this->attachment[$cur][4] = $type;
        $this->attachment[$cur][5] = false; // isStringAttachment
        $this->attachment[$cur][6] = "attachment";
        $this->attachment[$cur][7] = 0;

        return true;
    }

    /**
     * Attaches all fs, string, and binary attachments to the message.
     * Returns an empty string on failure.
     * @access private
     * @return string
     */
    function attach_all() {
        // Return text of body
        $mime = array();

        // Add all attachments
        for($i = 0; $i < count($this->attachment); $i++)
        {
            // Check for string attachment
            $b_string = $this->attachment[$i][5];
            if ($b_string)
                $string = $this->attachment[$i][0];
            else
                $path = $this->attachment[$i][0];

            $filename    = $this->attachment[$i][1];
            $name        = $this->attachment[$i][2];
            $encoding    = $this->attachment[$i][3];
            $type        = $this->attachment[$i][4];
            $disposition = $this->attachment[$i][6];
            $cid         = $this->attachment[$i][7];
            
            $mime[] = sprintf("--%s%s", $this->boundary[1], $this->le);
            $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $name, $this->le);
            $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->le);

            if($disposition == "inline")
                $mime[] = sprintf("Content-ID: <%s>%s", $cid, $this->le);

            $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", 
                              $disposition, $name, $this->le.$this->le);

            // Encode as string attachment
            if($b_string)
            {
                $mime[] = $this->encode_string($string, $encoding);
                if($this->is_error()) { return ""; }
                $mime[] = $this->le.$this->le;
            }
            else
            {
                $mime[] = $this->encode_file($path, $encoding);                
                if($this->is_error()) { return ""; }
                $mime[] = $this->le.$this->le;
            }
        }

        $mime[] = sprintf("--%s--%s", $this->boundary[1], $this->le);

        return join("", $mime);
    }
    
    /**
     * Encodes attachment in requested format.  Returns an
     * empty string on failure.
     * @access private
     * @return string
     */
    function encode_file ($path, $encoding = "base64") {
        if(!@$fd = fopen($path, "rb"))
        {
            $this->set_error($this->lang("file_open") . $path);
            return "";
        }
        $magic_quotes = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        $file_buffer = fread($fd, filesize($path));
        $file_buffer = $this->encode_string($file_buffer, $encoding);
        fclose($fd);
        set_magic_quotes_runtime($magic_quotes);

        return $file_buffer;
    }

    /**
     * Encodes string to requested format. Returns an
     * empty string on failure.
     * @access private
     * @return string
     */
    function encode_string ($str, $encoding = "base64") {
        $encoded = "";
        switch(strtolower($encoding)) {
          case "base64":
              // chunk_split is found in PHP >= 3.0.6
              $encoded = chunk_split(base64_encode($str), 76, $this->le);
              break;
          case "7bit":
          case "8bit":
              $encoded = $this->fix_eol($str);
              if (substr($encoded, -(strlen($this->le))) != $this->le)
                $encoded .= $this->le;
              break;
          case "binary":
              $encoded = $str;
              break;
          case "quoted-printable":
              $encoded = $this->encode_qp($str);
              break;
          default:
              $this->set_error($this->lang("encoding") . $encoding);
              break;
        }
        return $encoded;
    }

    /**
     * Encode a header string to best of Q, B, quoted or none.  
     * @access private
     * @return string
     */
    function encode_header ($str, $position = 'text') {
      $x = 0;
      
      switch (strtolower($position)) {
        case 'phrase':
          if (!preg_match('/[\200-\377]/', $str)) {
            // Can't use addslashes as we don't know what value has magic_quotes_sybase.
            $encoded = addcslashes($str, "\0..\37\177\\\"");

            if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str))
              return ($encoded);
            else
              return ("\"$encoded\"");
          }
          $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
          break;
        case 'comment':
          $x = preg_match_all('/[()"]/', $str, $matches);
          // Fall-through
        case 'text':
        default:
          $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
          break;
      }

      if ($x == 0)
        return ($str);

      $maxlen = 75 - 7 - strlen($this->char_set);
      // Try to select the encoding which should produce the shortest output
      if (strlen($str)/3 < $x) {
        $encoding = 'B';
        $encoded = base64_encode($str);
        $maxlen -= $maxlen % 4;
        $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
      } else {
        $encoding = 'Q';
        $encoded = $this->encode_q($str, $position);
        $encoded = $this->wrap_text($encoded, $maxlen, true);
        $encoded = str_replace("=".$this->le, "\n", trim($encoded));
      }

      $encoded = preg_replace('/^(.*)$/m', " =?".$this->char_set."?$encoding?\\1?=", $encoded);
      $encoded = trim(str_replace("\n", $this->le, $encoded));
      
      return $encoded;
    }
    
    /**
     * Encode string to quoted-printable.  
     * @access private
     * @return string
     */
    function encode_qp ($str) {
        $encoded = $this->fix_eol($str);
        if (substr($encoded, -(strlen($this->le))) != $this->le)
            $encoded .= $this->le;

        // Replace every high ascii, control and = characters
        $encoded = preg_replace('/([\000-\010\013\014\016-\037\075\177-\377])/e',
                  "'='.sprintf('%02X', ord('\\1'))", $encoded);
        // Replace every spaces and tabs when it's the last character on a line
        $encoded = preg_replace("/([\011\040])".$this->le."/e",
                  "'='.sprintf('%02X', ord('\\1')).'".$this->le."'", $encoded);

        // Maximum line length of 76 characters before CRLF (74 + space + '=')
        $encoded = $this->wrap_text($encoded, 74, true);

        return $encoded;
    }

    /**
     * Encode string to q encoding.  
     * @access private
     * @return string
     */
    function encode_q ($str, $position = "text") {
        // There should not be any EOL in the string
        $encoded = preg_replace("[\r\n]", "", $str);

        switch (strtolower($position)) {
          case "phrase":
            $encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
          case "comment":
            $encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
          case "text":
          default:
            // Replace every high ascii, control =, ? and _ characters
            $encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',
                  "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
        }
        
        // Replace every spaces to _ (more readable than =20)
        $encoded = str_replace(" ", "_", $encoded);

        return $encoded;
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     * @param string $string String attachment data.
     * @param string $filename Name of the attachment.
     * @param string $encoding File encoding (see $encoding).
     * @param string $type File extension (MIME) type.
     * @return void
     */
    function add_string_attachment($string, $filename, $encoding = "base64", 
                                 $type = "application/octet-stream") {
        // Append to $attachment array
        $cur = count($this->attachment);
        $this->attachment[$cur][0] = $string;
        $this->attachment[$cur][1] = $filename;
        $this->attachment[$cur][2] = $filename;
        $this->attachment[$cur][3] = $encoding;
        $this->attachment[$cur][4] = $type;
        $this->attachment[$cur][5] = true; // isString
        $this->attachment[$cur][6] = "attachment";
        $this->attachment[$cur][7] = 0;
    }
    
    /**
     * Adds an embedded attachment.  This can include images, sounds, and 
     * just about any other document.  Make sure to set the $type to an 
     * image type.  For JPEG images use "image/jpeg" and for GIF images 
     * use "image/gif".
     * @param string $path Path to the attachment.
     * @param string $cid Content ID of the attachment.  Use this to identify 
     *        the Id for accessing the image in an HTML form.
     * @param string $name Overrides the attachment name.
     * @param string $encoding File encoding (see $encoding).
     * @param string $type File extension (MIME) type.  
     * @return bool
     */
    function add_embedded_image($path, $cid, $name = "", $encoding = "base64", 
                              $type = "application/octet-stream") {
    
        if(!@is_file($path))
        {
            $this->set_error($this->lang("file_access") . $path);
            return false;
        }

        $filename = basename($path);
        if($name == "")
            $name = $filename;

        // Append to $attachment array
        $cur = count($this->attachment);
        $this->attachment[$cur][0] = $path;
        $this->attachment[$cur][1] = $filename;
        $this->attachment[$cur][2] = $name;
        $this->attachment[$cur][3] = $encoding;
        $this->attachment[$cur][4] = $type;
        $this->attachment[$cur][5] = false; // isStringAttachment
        $this->attachment[$cur][6] = "inline";
        $this->attachment[$cur][7] = $cid;
    
        return true;
    }
    
    /**
     * Returns true if an inline attachment is present.
     * @access private
     * @return bool
     */
    function inline_image_exists() {
        $result = false;
        for($i = 0; $i < count($this->attachment); $i++)
        {
            if($this->attachment[$i][6] == "inline")
            {
                $result = true;
                break;
            }
        }
        
        return $result;
    }

    /////////////////////////////////////////////////
    // MESSAGE RESET METHODS
    /////////////////////////////////////////////////

    /**
     * Clears all recipients assigned in the TO array.  Returns void.
     * @return void
     */
    function clear_addresses() {
        $this->to = array();
    }

    /**
     * Clears all recipients assigned in the CC array.  Returns void.
     * @return void
     */
    function clear_c_cs() {
        $this->cc = array();
    }

    /**
     * Clears all recipients assigned in the BCC array.  Returns void.
     * @return void
     */
    function clear_bc_cs() {
        $this->bcc = array();
    }

    /**
     * Clears all recipients assigned in the ReplyTo array.  Returns void.
     * @return void
     */
    function clear_reply_tos() {
        $this->reply_to = array();
    }

    /**
     * Clears all recipients assigned in the TO, CC and BCC
     * array.  Returns void.
     * @return void
     */
    function clear_all_recipients() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
    }

    /**
     * Clears all previously set filesystem, string, and binary
     * attachments.  Returns void.
     * @return void
     */
    function clear_attachments() {
        $this->attachment = array();
    }

    /**
     * Clears all custom headers.  Returns void.
     * @return void
     */
    function clear_custom_headers() {
        $this->custom_header = array();
    }


    /////////////////////////////////////////////////
    // MISCELLANEOUS METHODS
    /////////////////////////////////////////////////

    /**
     * Adds the error message to the error container.
     * Returns void.
     * @access private
     * @return void
     */
    function set_error($msg) {
        $this->error_count++;
        $this->error_info = $msg;
    }

    /**
     * Returns the proper RFC 822 formatted date. 
     * @access private
     * @return string
     */
    function rfc_date() {
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz/3600)*100 + ($tz%3600)/60;
        $result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

        return $result;
    }
    
    /**
     * Returns the appropriate server variable.  Should work with both 
     * PHP 4.1.0+ as well as older versions.  Returns an empty string 
     * if nothing is found.
     * @access private
     * @return mixed
     */
    function server_var($var_name) {
        global $http_server_vars;
        global $http_env_vars;

        if(!isset($_SERVER))
        {
            $_SERVER = $http_server_vars;
            if(!isset($_SERVER["REMOTE_ADDR"]))
                $_SERVER = $http_env_vars; // must be Apache
        }
        
        if(isset($_SERVER[$var_name]))
            return $_SERVER[$var_name];
        else
            return "";
    }

    /**
     * Returns the server hostname or 'localhost.localdomain' if unknown.
     * @access private
     * @return string
     */
    function server_hostname() {
        if ($this->hostname != "")
            $result = $this->hostname;
        elseif ($this->server_var('SERVER_NAME') != "")
            $result = $this->server_var('SERVER_NAME');
        else
            $result = "localhost.localdomain";

        return $result;
    }

    /**
     * Returns a message in the appropriate language.
     * @access private
     * @return string
     */
    function lang($key) {
        if(count($this->language) < 1)
            $this->set_language("en"); // set the default language
    
        if(isset($this->language[$key]))
            return $this->language[$key];
        else
            return "Language string failed to load: " . $key;
    }
    
    /**
     * Returns true if an error occurred.
     * @return bool
     */
    function is_error() {
        return ($this->error_count > 0);
    }

    /**
     * Changes every end of line from CR or LF to CRLF.  
     * @access private
     * @return string
     */
    function fix_eol($str) {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $str = str_replace("\n", $this->le, $str);
        return $str;
    }

    /**
     * Adds a custom header. 
     * @return void
     */
    function add_custom_header($custom_header) {
        $this->custom_header[] = explode(":", $custom_header, 2);
    }
}

?>