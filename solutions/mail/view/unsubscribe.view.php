<? if($this->unsubscribed){ ?>
	<p class="unsubscribe_message">
		<?=__('You have been successfully unsubscribed. If you did this in error, you may re-subscribe by clicking the link below.')?><br>
		<a href="?email=<?=urlencode(\request::get('email'))?>&subscribe=1"><?=__('Re-subscribe')?></a>
	</p>
<? } else if($this->subscribed) { ?>
	<p class="unsubscribe_message">
		<?=__('You have been successfully re-subscribed.')?>
	</p>
<? } else { ?>
	<p class="unsubscribe_message">
		...
	</p>
<? } ?>