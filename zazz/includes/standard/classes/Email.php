<?php
require_once dirname(__FILE__) . '/email/class.phpmailer.php';
require_once dirname(__FILE__) . '/email/class.smtp.php';
require_once dirname(__FILE__) . '/Logger.php';
require_once dirname(__FILE__) .'/../functions.php';

class Email {

	const CATEGORY = '';
	
	private $content;

	/**
	 * Make an email.
	 * @param string $message The message
	 * @param boolean $plain True if you want the message to be just what is passed in, false 
	 *	(default) if want an HTML frame.
	 */
	public function __construct($message, $plain = false) {
		$server = getServerName();
		$header_location = 'http://' . $server . '/images/header.jpg';

		if(!$plain) {
			$this->content = '
			<style>
				html, body, table, tr, td {font-size:13px;font-family:Arial,sans-serif;}
				a { text-decoration:none; color:#19499a; }
				h4 { font-size: 20px; }
				p, li { padding-bottom: 20px; padding-top: 5px; color: black;}
			</style>
			<html style="background-color:#f6f6f6;">
			<body style="background-color: #f6f6f6;margin:0;padding:0;font-size:13px;font-family:Arial,sans-serif;width:100%;height:100%;">
					<table width="605px" align="center" bgcolor="#ffffff" border="0" bordercolor="#939393" cellpadding="0" cellspacing="0" style="border:1px solid #939393;width:605px;">
						<tr>
							<td>
								<img width="605px" src="' . $header_location . '">
							</td>
						</tr>
						<tr>
							<td>
								<div>
									<table width="605px" cellpadding="20" style="font-family:Arial,sans-serif;font-size:13px;color:black;">
										<tr>
											<td height="250px">
												' . $message . '
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					</table>
				</body>
			</html>';
		} else {
			$this->content = $message;
		}
	}

	/**
	 * Send an email, specifiying the sender and recipient information.
	 * @param string $from From email address
	 * @param string $fromname From person
	 * @param string $replyto Reply address
	 * @param string $to To address
	 * @param string $name To person
	 * @param string $subject Title of email
	 * @param string $category Used for grouping the emails in sendgrid
	 */
	public function send($from, $fromname, $replyto, $to, $name, $subject, $category = '') {

		ob_start();
		
		$mail = new PHPMailer();
		$mail->IsSMTP();
		# Send Grid
		$mail->Host = "smtp.sendgrid.net";
		$mail->Port = 25;
		$mail->SMTPAuth = true;
		$mail->Username = 'smcguinn057';
		$mail->Password = 'jIpEVVn##!!mKD';

		$mail->From = $from;
		$mail->FromName = "$fromname";
		$mail->AddAddress($to, $name);
		$mail->AddReplyTo($replyto, "$replyto");
		$mail->WordWrap = 50;

		# SendGrid Custom Categories
		if ($category === '') {
			$json = '{"category" : "' . CATEGORY . '"}';
		} else {
			$json = '{"category" : "' . $category . '"}';
		}
		$mail->addCustomHeader('X-SMTPAPI: ' . $json);

		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $this->content;
		if (!$mail->Send()) {
			/* @var $logger Logger */
			$logger = Logger::get();
			$logger->log(ob_get_flush());
		} else {
			ob_end_clean();
		}
	}

}

?>
