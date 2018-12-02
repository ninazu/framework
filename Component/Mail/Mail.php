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
				isset($this->transport['encryption']) ? $this->transport['encryption'] : null
			))
				->setUsername($this->transport['username'])
				->setPassword($this->transport['password']);
//
//			$transport->setStreamOptions([
//				'ssl' => [
//					'allow_self_signed' => true,
//					'verify_peer' => false,
//					'verify_peer_name' => false,
//				],
//			]);
		}

		$this->mailer = new Swift_Mailer($transport);
	}

	public function send(array $to, string $subject, string $text) {
		$message = (new Swift_Message($subject))
			->setFrom(isset($this->transport['username']) ? $this->transport['username'] : $this->getApplication()->getAdminEmail())
			->setTo($to)
			->setBody($text);

		return $this->mailer->send($message);
	}
}