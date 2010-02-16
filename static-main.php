<?php

/**
 * This file is designed to be the new 'server' of sites using StaticPublisher.
 * to use this, you need to modify your .htaccess to point all requests to
 * static-main.php, rather than main.php. This file also allows for using
 * static publisher with the subsites module.
 *
 * If you are using StaticPublisher+Subsites, set the following in _config.php:
 *   FilesystemPublisher::$domain_based_caching = true;
 * If you are not using subsites, the host-map.php file will not exist (it is
 * automatically generated by the Subsites module) and the cache will default
 * to no subdirectory.
 */

$cacheOn = true;
$cacheDebug = false;
$hostmapLocation = '../subsites/host-map.php';
$homepageMapLocation = '../assets/_homepage-map.php';
date_default_timezone_set('Pacific/Auckland');

if ($cacheOn && empty($_COOKIE['bypassStaticCache'])) {
	if (isset($_GET['cacheSubdir']) && !preg_match('/[^a-zA-Z0-9\-_]/', $_GET['cacheSubdir'])) {
		$cacheDir = $_GET['cacheSubdir'].'/';
	}
	else if (file_exists($hostmapLocation)) {
		include_once $hostmapLocation;
		$subsiteHostmap['default'] = isset($subsiteHostmap['default']) ? $subsiteHostmap['default'] : '';

		// Look for the host, and find the cache dir
		$host = str_replace('www.', '', $_SERVER['HTTP_HOST']);
		$cacheDir = (isset($subsiteHostmap[$host]) ? $subsiteHostmap[$host] : $subsiteHostmap['default']) . '/';
	} else {
		$cacheDir = '';
	}

	// Look for the file in the cachedir
	$file = trim($_SERVER['REQUEST_URI'], '/');
	$file = $file ? $file : 'index';

	// Route to the 'correct' index file (if applicable)
	if ($file == 'index' && file_exists($homepageMapLocation)) {
		include_once $homepageMapLocation;
		$file = isset($homepageMap[$_SERVER['HTTP_HOST']]) ? $homepageMap[$_SERVER['HTTP_HOST']] : $file;
	}

	$file = preg_replace('/[^a-zA-Z0-9]/si', '_', $file);

	if (file_exists('../cache/'.$cacheDir.$file.'.html')) {
		header('X-cache: hit at '.@date('r'));
		echo file_get_contents('../cache/'.$cacheDir.$file.'.html');
	} elseif (file_exists('../cache/'.$cacheDir.$file.'.php')) {
		header('X-cache: hit at '.@date('r'));
		include_once '../cache/'.$cacheDir.$file.'.php';
	if ($cacheDebug) echo "<h1>File was cached</h1>";
	} else {
		header('X-cache: miss at '.@date('r') . ' on ' . $cacheDir . $file);
		// No cache hit... fallback!!!
		include 'main.php';
		if ($cacheDebug) echo "<h1>File was !NOT! cached</h1>";
	}
} else {
	include 'main.php';
}

?>