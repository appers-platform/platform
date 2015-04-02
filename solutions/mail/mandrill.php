<?
namespace solutions\mail;
use solutions\mail;

class mandrill extends mail {
	static private $mandrill_instance;

	/**
	 * @return \Mandrill
	 * @throws \Exception
	 */
	static protected function getMandrillInstance() {
		if(!self::$mandrill_instance) {
			require_once ROOT.'/extLib/mandrill/Mandrill.php';
			if(!$mandrill_key = \solutions\mail::getConfig('mandrill_key'))
				throw new \Exception('You should set mail->mandrill_key param in your config');
			self::$mandrill_instance = new \Mandrill($mandrill_key);
		}

		return self::$mandrill_instance;
	}

	static public function send($receiver_email, $title, $content, $layout = 'mail', array $params = []) {
		$html = self::renderContent($layout, $content, $params);

		$message = [
			'subject' => self::encodeTitle($title),
			'html' => $html,
			'auto_text' => true,
			'from_email' => self::getSenderEmail(),
			'from_name' => self::getSenderName(),
			'to' => [ ['email' => $receiver_email, 'name' => ''] ],
			'headers' => self::generateHeaders($params),
		];
		
		$response = self::getMandrillInstance()->messages->send($message);
		\log::debug('Sending email to '.$receiver_email.' via mandrill('.self::getMandrillInstance()->apikey.'): '.print_r($response, true));
		return ($response[0]['status'] == 'sent');
	}
}
