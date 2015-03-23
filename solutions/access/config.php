<?
return [
	'routes' => [
		'/access/denied'	=> 'denied',
	],
	'autoLoad' => [
		['\\solutions\\access', 'init']
	],
	'userSolution'		=> [ 'enabled' => true ],
	'listeners'			=> [
		[ 'solutions.user.delete' => ['\\solutions\\access', 'callbackUserDelete'] ],
	]
];
