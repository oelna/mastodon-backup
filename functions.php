<?php

function normalize_mastodon_user_url($url, $returntype='url') {

	$result = null;

	$url = str_replace('http://', 'https://', $url);

	if(strtolower(substr($url, 0, 8)) !== 'https://') {
		$url = 'https://'.$url;
	}

	$parsed = parse_url($url);

	$result = $parsed['scheme'].'://';
	$result .= $parsed['host'];

	$path = explode('/', trim($parsed['path'], '/'));
	if(strtolower($path[0]) == 'users') {
		$user = $path[1];
	} else {
		$user = $path[0];
	}

	$user = ltrim($user, '@');
	$user = strtok($user, '.'); // remove everything after a dot, eg. .rss, .atom

	$result .= '/@'.$user;

	if($returntype == 'array') return ['host' => $parsed['host'], 'user' => $user];
	return $url;
}
