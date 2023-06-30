<?php

if(!file_exists(__DIR__.DIRECTORY_SEPARATOR.'config.php')) die('Create a config.php according to <a href="README.md">README.md</a>');
require_once(__DIR__.DIRECTORY_SEPARATOR.'config.php');

$stmt = $db->prepare("SELECT post_server, post_user, COUNT(*) as post_amount, refresh_timestamp FROM posts p LEFT JOIN refresh r ON p.post_server = r.refresh_server AND p.post_user = r.refresh_account GROUP BY p.post_server, p.post_user");
$stmt->execute(); 
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$accounts = [];
if(!empty($stats)) {
	foreach ($stats as $account) {
		$accounts[] = [
			'server' => $account['post_server'],
			'user' => $account['post_user']
		];
	}
}

if(!empty($_GET['t'])) {
	// specific month
	list($y, $m) = explode('-', $_GET['t']);
} else {
	// current month
	$y = date('Y');
	$m = date('m');
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Mastodon Backup</title>

	<link rel="stylesheet" href="./theme.css">
</head>
<body>
	<div class="container">
		<nav>
			<?php
				$sql = "SELECT COUNT(id) as amount, post_pubdate FROM posts WHERE post_server = :post_server AND post_user = :post_user GROUP BY STRFTIME('%m-%Y', post_pubdate, 'unixepoch') ORDER BY post_pubdate DESC";

				$stmt = $db->prepare($sql);
				$stmt->bindValue(':post_server', $accounts[0]['server'], PDO::PARAM_STR);
				$stmt->bindValue(':post_user', $accounts[0]['user'], PDO::PARAM_STR);
				$stmt->execute(); 
				$months = $stmt->fetchAll(PDO::FETCH_ASSOC);

				if(!empty($months)) {
					echo('<details open><summary>Navigation</summary><ul class="months">');
					foreach($months as $month) {
						$display_date = date('M \'y', $month['post_pubdate']);
						$datetime = date('Y-m', $month['post_pubdate']);

						$current = ($datetime == $y.'-'.$m) ? ' aria-current="page"' : '';

						echo('<li'.$current.'>');
						echo('<a href="?t='.$datetime.'"><time datetime="'.$datetime.'">'.$display_date.'</time><span class="amount">'.$month['amount'].'</span></a>');
						echo('</li>');
					}
					echo('</ul></details>');
				}
			?>
		</nav>
		<main>
			<?php
				if(!empty($accounts)) {
					// fetch posts

					$month_start = mktime(0,0,0,$m,1,$y);
					$total_days = cal_days_in_month(CAL_GREGORIAN, $m, $y);
					$month_end = mktime(23,59,59,$m,$total_days,$y);

					$sql = "SELECT * FROM posts WHERE post_server = :post_server AND post_user = :post_user AND post_pubdate >= :post_from AND post_pubdate <= :post_to ORDER BY post_pubdate DESC";

					$stmt = $db->prepare($sql);
					$stmt->bindValue(':post_server', $accounts[0]['server'], PDO::PARAM_STR);
					$stmt->bindValue(':post_user', $accounts[0]['user'], PDO::PARAM_STR);
					$stmt->bindValue(':post_from', $month_start, PDO::PARAM_INT);
					$stmt->bindValue(':post_to', $month_end, PDO::PARAM_INT);
					$stmt->execute(); 
					$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

					if(!empty($posts)) {
						echo('<ul class="posts">');
						foreach($posts as $post) {
							echo('<li>');
							echo('<a class="permalink time" href="'.$post['post_link'].'"><time datetime="'.date('Y-m-d H:i:s', $post['post_pubdate']).'">'.date('d M y H:i', $post['post_pubdate']).'</time></a>');
							echo('<div>'.$post['post_description'].'</div>');

							if(!empty($post['post_media'])) {
								echo('<ul class="media">');
								$media = json_decode($post['post_media'], true);

								foreach ($media as $item) {
									echo('<li>');
									$filename = basename(parse_url($item['url'], PHP_URL_PATH));

									if (stripos($item['type'], 'image/') !== false) {
										$desc = $item['description'] ?: 'default';
										echo('<a class="image" href="'.$item['url'].'"><img loading="lazy" src="'.$item['url'].'" data-filename="'.$filename.'" alt="'.$desc.'" /></a>');
									} else {
										echo('<a class="file" href="'.$item['url'].'">'.$filename.'</a>');
									}
									echo('</li>');
								}
								echo('</ul>');
							}
							echo('</li>'."\n");
						}
						echo('</ul>');
					}
				}
			?>
		</main>
		<footer>
			<?php
				if(!empty($stats)) {
					foreach($stats as $user) {
						echo($user['post_server'].'/@'.$user['post_user'].': '.$user['post_amount'].' posts archived (last fetch: '.date('Y-m-d H:i', $user['refresh_timestamp']).')'."\n");
					}
				}
			?>
		</footer>
	</div>
</body>
</html>
