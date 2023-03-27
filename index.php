<?php

require_once(__DIR__.DIRECTORY_SEPARATOR.'config.php');

$stmt = $db->prepare("SELECT post_server, post_user, COUNT(*) as post_amount FROM posts GROUP BY post_server, post_user");
$stmt->execute(); 
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!empty($stats)) {
	foreach ($stats as $user) {
		echo($user['post_server'].'/@'.$user['post_user'].': '.$user['post_amount'].' posts archived'."\n");
	}
}
