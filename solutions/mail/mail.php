<?
namespace solutions;

class mail extends solution {
	const ENABLING_REQUIRED = true;
	const SEND_QUEUE = 'solution-mail-send';

	/**
	 * @param string $receiver_email
	 * @param string $title
	 * @param string $content
	 * @param string $layout - if you use 'mail', will be used file /layout/mail.view.php
	 * @param array $params - additional params: List-Unsubscribe, .
	 * @throws \Exception
	 * @return bool
	 */
	static public function send($receiver_email, $title, $content, $layout = 'mail', array $params = []) {
		$params['receiver_email'] = $receiver_email;
		$params['title'] = $title;

		if($debug = self::getConfig('debug', false)) {
			file_put_contents($debug, self::renderContent($layout, $content, $params));
		}

		if(self::getConfig('debugOnly', false))
			return true;

		if(!self::getConfig('background', false)) {
			static::directSend($receiver_email, $title, $content, $layout, $params);
			return true;
		} else {
			\log::debug('Mail to '.$receiver_email.' will be send via bg.');
			\bg::run([__CLASS__, 'directSend'], [$receiver_email, $title, $content, $layout, $params]);
		}
	}

	/**
	 * @param string $receiver_email
	 * @param string $title
	 * @param string $content
	 * @param string $layout - if you use 'mail', will be used file /layout/mail.view.php
	 * @param array $params - additional params: List-Unsubscribe, .
	 * @throws \Exception
	 * @return bool
	 */
	static public function directSend($receiver_email, $title, $content, $layout = 'mail', array $params = []) {
		if(self::isUnSubscribed($receiver_email)) {
			\log::debug('Sending email to '.$receiver_email.' - mock!');
			return false;
		}
		switch(self::getConfig('driver', false)) {
			case 'mandrill':
				return mail\mandrill::send($receiver_email, $title, $content, $layout, $params);
				break;
			default:
				return mail\sendmail::send($receiver_email, $title, $content, $layout, $params);
				break;
		}
	}

	static protected function renderContent($layout, $content, array $context = []) {
		$context['content'] = $content;
		$controller = self::controller('mail');
		if($layout) $controller->view = $layout;
		return (string) $controller->renderFile($controller->getView(), $context);
	}

	static protected function getSenderEmail() {
		if($email = self::getConfig('sender_email', false))
			return $email;
		return 'notifications@'.PROJECT;
	}

	static protected function getSenderName() {
		if($name = self::getConfig('sender_name', false))
			return $name;
		return PROJECT.' notifications';
	}

	static protected function encodeTitle($title) {
		return '=?UTF-8?B?'.base64_encode($title).'?=';
	}

	static protected function generateHeaders(array $params) {
		$headers = [];
		if(isset($params['List-Unsubscribe'])) {
			$headers['List-Unsubscribe'] = '<'.$params['List-Unsubscribe'].'>';
		}

		return $headers;
	}

	static public function isUnSubscribed($email) {
		$model = new unsubscribedModel();
		$model->email = $email;
		$model->find();

		return (bool) $model->getId();
	}

	static protected function generateEmailSource($html, array &$headers = []) {
		$plain = preg_replace('/<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/', '$2:' . "\n" . '$1', $html);
		$plain = str_replace('<span class="foot-line"></span>', '--', $plain);
		$plain = trim(strip_tags($plain));
		$plain = str_replace('&nbsp;', ' ', $plain);
		$plain = preg_replace('/\t+/', '', $plain);
		$plain = preg_replace('/ {2,}/', ' ', $plain);
		$plain = preg_replace('/\n{2,}/', "\n\n", $plain);

		$boundary = 'b1_' . md5(uniqid(time()));
		$headers['MIME-Version'] = '1.0';
		$headers['Content-Type'] = 'multipart/alternative; boundary=' . $boundary;
		$headers['Content-Transfer-Encoding'] = '8bit';
		
		$result = '--' . $boundary . "\r\n";
		$result .= 'Content-Type: text/plain; charset="utf-8"' . "\r\n";
		$result .= 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n";
		$result .= $plain;
		$result .= "\r\n\r\n";

		$result .= '--' . $boundary . "\r\n";
		$result .= 'Content-Type: text/html; charset="utf-8"' . "\r\n";
		$result .= 'Content-Transfer-Encoding: 8bit' . "\r\n\r\n";
		$result .= $html;
		$result .= "\r\n\r\n";
		$result .= '--' . $boundary . "--\r\n";

		return $result;
	}
}
