<?php
require 'lib/_.php';

$path = $argv[1];

$doc = parse_html_file($path);
echo $doc ? "OK" : "FAIL", PHP_EOL;

?>
