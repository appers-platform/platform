<div class="solutionComment">
	<div class="row">
		<div class="col-xs-6 col-md-3"></div>
		<div class='col-xs-6 col-md-6'>
			<? if(is_array($comments)) {
				foreach($comments as $comment) {
					print $this->renderPartial('comment', [ 'comment' => $comment, 'sub_comments' => $this->sub_comments]);
				}
			} ?>
			<div class="defaultComment">
				<a href="javascript:void(0);" class="top_comment hidden" rel="reply" data-id="<?=$comment->id?>"><?=__('Write comment')?></a>
				<?=\solutions\form::create(
					[
						[
							'name' => 'comment',
							'title' => __('Comment'),
							'value' => '',
							'type' => 'textarea'
						],
						[ 'name' => 'new_comment', 'value' => 1, 'type' => 'hidden' ],
						[ 'name' => 'parent_id', 'value' => 0, 'type' => 'hidden' ],
						[ 'name' => 'solution_comment_model', 'value' => $this->model_name, 'type' => 'hidden' ],
						[ 'name' => 'sign', 'value' => $this->sign, 'type' => 'hidden' ],
						[ 'name' => 'target_element_id', 'value' => $this->target_element_id, 'type' => 'hidden' ],
					],
					'',
					'?id='.$comment->id,
					'POST'
				)->setSendButtonName(__('Write'))->setCancelButton(__('Cancel'), 'javascript:void(0);')->setClass('add')?>
			</div>
		</div>
		<div class="col-xs-6 col-md-3"></div>
	</div>
</div>
