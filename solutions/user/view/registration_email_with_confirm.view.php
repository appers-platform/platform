<?=__('Hi!

Thank you for registration.
Your email: %s
Your password: %s

', $email, $password)?>

<?=\solutions\placeholer::link(
	__('Click <link>%s</link> for complete your registration.', $confirmation_url),
	$confirmation_url	
)?>
