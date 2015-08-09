<span class="votingSolution voting_<?=$target_element_id?>_<?=$dependency_entity?>">
	<span class="value <?=($model->rate!=0)?($model->rate<0?'minus':'plus'):''?>">
		<?=($model->rate<=0?'':'+')?>
		<?=(int)$model->rate?>
	</span>
	<? if($can_vote) {?>
		<a target="_self" href="javascript:void(0);" rel="+" title="<?=__('+1')?>" data-id="<?=$target_element_id?>" class="likes-dislikes__like">
			&nbsp;
		</a>
		<a target="_self" href="javascript:void(0);" rel="-" title="<?=__('-1')?>" data-id="<?=$target_element_id?>" class="likes-dislikes__dislike">
			&nbsp;
		</a>
	<? } ?>
</span>
