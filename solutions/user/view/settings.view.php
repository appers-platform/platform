<div class="<?=$this->getConfig('css', false)['formClass']?>">
	<h1><?=__('Hi!')?></h1>
	<?=__('You can:')?>
	<ul>
		<li><a target="_self" href="<?=$this->getUrl('name')?>"><?=__('Change name')?></a></li>
		<li><a target="_self" href="<?=$this->getUrl('email')?>"><?=__('Change email')?></a></li>
		<li><a target="_self" href="<?=$this->getUrl('password')?>"><?=__('Change password')?></a></li>
	</ul>
</div>
