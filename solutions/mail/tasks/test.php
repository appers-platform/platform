<?
if(!$receiver = cli::getArgument('email')) {
	print "You should set receiver name: ./exec test::mail email=box@example.com\n";
	return;
}

\solutions\mail::send($receiver, 'Test message '.time(), 'This is body of test email with some random: '.rand(0, 999999));
