<?php
require "lib/_.php";

$doc = parse_html_file("test/157.html");
if (!$doc) {
	exit(1);
}

$set = $doc->querySelectorAll("p.ref");
foreach ($set as $node) {
	echo format_node($node), PHP_EOL;
}

function format_node($node)
{
	$s = '<'.$node->tagName;
	foreach ($node->attrs as $k => $v) {
		$s .= " $k=\"$v\"";
	}
	$s .= '>';
	return $s;
}

?>
