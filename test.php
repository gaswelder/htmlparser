<?php
require 'lib/_.php';

$opt = array(
	'single_quotes' => true,
	'xml_perversion' => true
);

$paths = glob('test/*.html');
foreach ($paths as $path) {
	$doc = htmlp\parse_html_file($path, $opt);
	echo $doc ? "OK" : "FAIL", PHP_EOL;
}

?>
