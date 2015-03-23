<div class="<?=$this->getConfig('css', false)['formClass']?>">
	<h1><?=__('Hi!')?></h1>
	<?=__('You can:')?>
	<ul>
		<li><a href="<?=$this->getUrl('name')?>"><?=__('Change name')?></a></li>
		<li><a href="<?=$this->getUrl('email')?>"><?=__('Change email')?></a></li>
		<li><a href="<?=$this->getUrl('password')?>"><?=__('Change password')?></a></li>
	</ul>
</div>
