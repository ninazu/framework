<?php

namespace vendor\ninazu\framework\Component\Mail;

use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use vendor\ninazu\framework\Core\BaseComponent;

class Mail extends BaseComponent {

	protected $transport;

	/**@var Swift_Mailer $mailer */
	private $mailer;

	public function init() {
		$transport = new Swift_SendmailTransport();

		if ($this->transport) {
			$transport = (new Swift_SmtpTransport(
				$this->transport['host'],
				$this->transport['port'],
				$this->transport['encryption']
			))
				->setUsername($this->transport['login'])
				->setPassword($this->transport['password']);
		}

		$this->mailer = new Swift_Mailer($transport);
	}

	public function send(array $to, string $subject, string $text) {
		$message = (new Swift_Message($subject))
			->setFrom(isset($this->transport['login']) ? $this->transport['login'] : $this->getApplication()->getAdminEmail())
			->setTo($to)
			->setBody($text);

		return $this->mailer->send($message);
	}
}