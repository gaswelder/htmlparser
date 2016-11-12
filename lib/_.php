<?php

$dir = dirname(__FILE__);
require $dir.'/parsebuf.php';
require $dir.'/htmlstream.php';
require $dir.'/parser.php';
require $dir.'/nodes.php';
require $dir.'/token.php';

/*
 * Parses the given HTML file.
 */
function parse_html_file( $path )
{
	$s = file_get_contents($path);
	$p = new html_parser();
	$doc = $p->parse($s);
	if($err = $p->err()) {
		fwrite(STDERR, "$path: $err\n");
		exit;
	}
	return $doc;
}

/*
 * Parses the given string as HTML document.
 */
function parse_html( $html )
{
	return html_parser::parse( $html );
}

?>
