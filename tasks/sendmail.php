<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */

class TaskMassMailer
{
	function TaskMassMailer()
	{
	}

	function RunTask($thistask)
	{
		global $DB;
		// Limit to 20 mails per minute!
		$DB->query("SELECT * FROM `dl_mailqueue` LIMIT 20");
		$ids = array();
		while ($email = $DB->fetch_row()) {
			$ids[] = $email['mail_id'];
			$this->SendMail($email);
		}
		if (!empty($ids)) {
			foreach ($ids as $i) {
				if ($where)
					$where .= " OR ";
				$where .= "`mail_id`='{$i}'";
			}
			$DB->query("DELETE FROM `dl_mailqueue` WHERE {$where}");
		}
	}

	function SendMail($email)
	{
		global $CONFIG;
		require_once(ROOT_PATH . "/engine/mime/htmlMimeMail.php");
		// Send mail to the user telling them what to do next if anything
		$mail = new htmlMimeMail();
		$mail->setHtml($email['content']);
		$mail->setReturnPath($email['from']);
		$from = $email['from'];
		$mail->setFrom($from);
		$mail->setSubject($email['subject']);
		$mail->setHeader('RW::Scripts', $CONFIG['sitename']);
		$result = $mail->send(array($email['to']), $CONFIG['mailtype']);
		if (!$result) {
			// TODO: Log error!
		}
	}
}

$loader = new TaskMassMailer();
?>
