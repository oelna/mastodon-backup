<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = [
	'password' => 'your-super-strong-password', // will eventually be used when deleting from DB

	'accounts' => [
		'https://mas.to/@oelna',
		'https://dice.camp/@Sophie',
		'https://mastodon.social/@siracusa'
	]
];


define('ROOT', __DIR__);
define('DS', DIRECTORY_SEPARATOR);
define('DB_FILE', ROOT.DS.'mastodon.db');

require_once(ROOT.DS.'database.php');
require_once(ROOT.DS.'functions.php');
