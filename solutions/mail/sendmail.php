<?
namespace solutions\mail;
use solutions\mail;

class sendmail extends mail {
	static public function send($receiver_email, $title, $content, $layout = 'mail', array $params = []) {
		$html = self::renderContent($layout, $content, $params);
		$headers = self::generateHeaders($params);
		$headers['From'] = self::encodeTitle(self::getSenderName()).'<'.self::getSenderEmail().'>';
		$html = self::generateEmailSource($html, $headers);

		$headers_lines = [];
		foreach ( $headers as $k => $v )
			$headers_lines[] = "{$k}: {$v}";

		\log::debug('Sending email to '.$receiver_email.' via mail()');
		return (bool) mail($receiver_email, self::encodeTitle($title), $html, implode("\r\n", $headers_lines));
	}
}
