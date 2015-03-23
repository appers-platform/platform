<div class="panel panel-default comment">
	<div class="panel-body">
		<div class="info">
			<span class="name"><?=\solutions\user\userModel::get($comment->user_id)->name?></span>
			<span class="time"><?=date('Y.m.d H:i:s', $comment->time)?></span>
			<span class="rate">
				<? \solutions\voting::show(
					get_class($comment),
					$comment->getId(),
					!(!\solutions\user::getCurrent() || $comment->user_id != \solutions\user::getCurrent()->getId()),
					['\solutions\comment','rateCallback'],
					[get_class($comment), $comment->getId()]
				) ?>
			</span>
		</div>
		<div class="content"><?=$comment->text?></div>

		<div class="functions">
			<? if(!\solutions\user::getCurrent() || $comment->user_id != \solutions\user::getCurrent()->getId()) {?>
				<a href="javascript:void(0);" rel="reply" data-id="<?=$comment->id?>"><?=__('Reply')?></a>
			<? }else{ ?>
				<a href="javascript:void(0);" rel="delete" data-id="<?=$comment->id?>"><?=__('Delete')?></a>
			<? } ?>
			<? if((!\solutions\user::getCurrent() || $comment->user_id != \solutions\user::getCurrent()->getId()) && $this->getConfig('complains')) { ?>
				<a href="javascript:void(0);" rel="complaint" data-id="<?=$comment->id?>"><?=__('Complaint')?></a>
			<? } ?>
			<? if((!\solutions\user::getCurrent() || $comment->user_id != \solutions\user::getCurrent()->getId()) && $this->getConfig('spam_report')) { ?>
				<a href="javascript:void(0);" rel="spam" data-id="<?=$comment->id?>"><?=__('Spam')?></a>
			<? } ?>
		</div>

		<? if($sub_comments[$comment->getId()]){ ?>
		<div class="sub_comments">
			<? foreach($sub_comments[$comment->getId()] as $sub_comment) {
				print $this->renderPartial('comment', [ 'comment' => $sub_comment, 'sub_comments' => $sub_comments]);
			} ?>
		</div>
		<? } ?>
	</div>
</div>
