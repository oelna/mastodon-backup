<?php

if(!file_exists(__DIR__.DIRECTORY_SEPARATOR.'config.php')) die('Create a config.php according to <a href="README.md">README.md</a>');
require_once(__DIR__.DIRECTORY_SEPARATOR.'config.php');

$feed = [];

foreach ($config['accounts'] as $account) {
	if(empty($account)) continue;
	$normalized = normalize_mastodon_user_url($account, 'array');

	$url = 'https://'.$normalized['host'].'/@'.$normalized['user'].'.rss';
	$xml = simplexml_load_file($url);

	if ($xml) {
		$server = parse_url($account, PHP_URL_HOST);
		$user = ltrim(parse_url($account, PHP_URL_PATH), '/@'); // todo

		foreach ($xml->channel->item as $item) {
			$guid = (string) $item->guid;
			$link = (string) $item->link;
			$description = (string) $item->description;
			$pubDate = (integer) strtotime($item->pubDate);

			$media = null;
			$ns_media = $item->children('http://search.yahoo.com/mrss/');

			if(!empty($ns_media)) {
				$media = [];

				foreach($ns_media as $media_item) {
					$attributes = $media_item->attributes();

					$insert = [];
					$insert['url'] = (string) $attributes->url;
					$insert['type'] = (string) $attributes->type;
					$insert['filesize'] = (integer) $attributes->fileSize;
					$insert['medium'] = (string) $attributes->medium;
					$insert['description'] = (string) $ns_media->content->description;
					$insert['rating'] = (string) $ns_media->content->rating;

					$media[] = $insert;
				}
			}

			$feed[] = [
				'server' => $server,
				'user' => $user,
				'guid' => $guid,
				'link' => $link,
				'description' => $description,
				'pubdate' => $pubDate,
				'media' => ($media !== null) ? json_encode($media) : null
			];
		}

		// log fetch
		$sql = "INSERT OR REPLACE INTO refresh (refresh_server, refresh_account, refresh_timestamp) VALUES (?,?,?)";
		$db->prepare($sql)->execute([$server, $user, time()]);
	}
}

$stmt = $db->prepare("INSERT OR IGNORE INTO posts (post_server, post_user, post_guid, post_link, post_description, post_media, post_pubdate) VALUES (:post_server, :post_user, :post_guid, :post_link, :post_description, :post_media, :post_pubdate)");

foreach($feed as $item) {
	$stmt->bindValue(':post_server', $item['server'], PDO::PARAM_STR);
	$stmt->bindValue(':post_user', $item['user'], PDO::PARAM_STR);
	$stmt->bindValue(':post_guid', $item['guid'], PDO::PARAM_STR);
	$stmt->bindValue(':post_link', $item['link'], PDO::PARAM_STR);
	$stmt->bindValue(':post_description', $item['description'], PDO::PARAM_STR);
	$stmt->bindValue(':post_pubdate', $item['pubdate'], PDO::PARAM_INT);

	if($item['media'] !== null) {
		$stmt->bindValue(':post_media', $item['media'], PDO::PARAM_STR);
	} else {
		$stmt->bindValue(':post_media', $item['media'], PDO::PARAM_NULL);
	}

	$stmt->execute();
}
