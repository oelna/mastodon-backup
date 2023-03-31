<?php

// connect or create the database
try {
	$db = new PDO('sqlite:'.DB_FILE);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	$config['db_version'] = $db->query("PRAGMA user_version")->fetch(PDO::FETCH_ASSOC)['user_version'];
} catch(PDOException $e) {
	print 'Exception : '.$e->getMessage();
	die('cannot connect to or open the database');
}

// first time database setup
if($config['db_version'] == 0) {
	try {
		$db->exec("PRAGMA `user_version` = 1;
			CREATE TABLE IF NOT EXISTS `posts` (
			`id` INTEGER PRIMARY KEY NOT NULL,
			`post_server` TEXT,
			`post_user` TEXT,
			`post_guid` TEXT UNIQUE NOT NULL,
			`post_link` TEXT,
			`post_description` TEXT,
			`post_media` TEXT,
			`post_pubdate` INTEGER
		);
		CREATE TABLE IF NOT EXISTS `refresh` (
			`id` INTEGER PRIMARY KEY NOT NULL,
			`refresh_server` TEXT NOT NULL,
			`refresh_account` TEXT NOT NULL,
			`refresh_timestamp` INTEGER
		);
		CREATE UNIQUE INDEX `guid` ON posts (`post_guid`);
		CREATE UNIQUE INDEX `account` ON refresh (`refresh_server`, `refresh_account`);
		");
		$config['db_version'] = 1;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		die('cannot set up initial database table!');
	}
}


// note to myself on how to upgrade the database in the future :)
// upgrade database to v2
if($config['db_version'] == 1) {
	try {
		$db->exec("PRAGMA `user_version` = 2;
			ALTER TABLE `posts` ADD `post_is_reply` INTEGER DEFAULT 0;
		");
		$config['db_version'] = 2;
	} catch(PDOException $e) {
		print 'Exception : '.$e->getMessage();
		die('cannot upgrade database table to v2!');
	}
}
