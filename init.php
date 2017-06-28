<?php
/*
 * File for manual inclusion.
 */

spl_autoload_register(function($className) {
	$namespace = 'gaswelder\\htmlparser\\';
	if (strpos($className, $namespace) !== 0) {
		return;
	}

	$name = str_replace('\\', '/', substr($className, strlen($namespace)));

	$path = __DIR__."/lib/$name.php";
	if (file_exists($path)) {
		require_once $path;
	}
});

?>
