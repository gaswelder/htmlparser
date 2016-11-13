<?php
require 'lib/_.php';

$paths = glob('test/*.html');
foreach ($paths as $path) {
	$doc = htmlp\parse_html_file($path);
	echo $doc ? "OK" : "FAIL", PHP_EOL;
}

?>
