<?php

if(!file_exists(__DIR__.DIRECTORY_SEPARATOR.'config.php')) die('Create a config.php according to <a href="README.md">README.md</a>');
require_once(__DIR__.DIRECTORY_SEPARATOR.'config.php');

$stmt = $db->prepare("SELECT post_server, post_user, COUNT(*) as post_amount, refresh_timestamp FROM posts p LEFT JOIN refresh r ON p.post_server = r.refresh_server AND p.post_user = r.refresh_account GROUP BY p.post_server, p.post_user");
$stmt->execute(); 
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!empty($stats)) {
	foreach ($stats as $user) {
		echo($user['post_server'].'/@'.$user['post_user'].': '.$user['post_amount'].' posts archived (last fetch: '.date('Y-m-d H:i', $user['refresh_timestamp']).')'."\n");

		// fetch latest posts?
		$stmt = $db->prepare("SELECT * FROM posts WHERE post_server = :post_server AND post_user = :post_user ORDER BY post_pubdate DESC LIMIT 5");
		$stmt->bindValue(':post_server', $user['post_server'], PDO::PARAM_STR);
		$stmt->bindValue(':post_user', $user['post_user'], PDO::PARAM_STR);
		$stmt->execute(); 
		$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($posts)) {
			echo('<ul>');
			foreach ($posts as $post) {
				echo('<li><time datetime="'.date('Y-m-d H:i:s', $post['post_pubdate']).'">'.date('d M y H:i', $post['post_pubdate']).'</time><div>'.$post['post_description']."</div></li>\n");
			}
			echo('</ul>');
		}
	}
}
