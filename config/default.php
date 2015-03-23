<? return [
	'defaultLocale' => 'en',

	'mysql' => [
		'host' 		=> '127.0.0.1',
		'port' 		=> 3306,
		'user' 		=> 'root',
		'password' 	=> '',
		'db' 		=> ''
	],

	'memcache' => [
		'host'		=> '127.0.0.1',
		'port'		=> 11211,
		'prefix'	=> ''
	],

	'gearman' => [
		'host'		=> '127.0.0.1',
		'port'		=> 4730,
		'prefix'	=> ''
	],

	'backgroundDriver' => '\\background\\gearman',
	'bg' => [
		'queue_ttl' => TTL_5_MIN,
		'manager_ttl' => TTL_10_MIN
	],

	'secret' => ''
];
