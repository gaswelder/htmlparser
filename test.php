<?php
require 'init.php';

use gaswelder\htmlparser\Parser;
use gaswelder\htmlparser\ParsingException;

$opt = array(
	'single_quotes' => true,
	'xml_perversion' => true
);
$p = new Parser($opt);

$paths = glob('test/*.html');
foreach ($paths as $path) {
	$ok = test($p, $path) ? "OK" : "FAIL";
	echo "$path: $ok\n";
}

function test($p, $path)
{
	try {
		$doc = $p->parse(file_get_contents($path));
	} catch(ParsingException $e) {
		fwrite(STDERR, $e->getMessage()."\n");
		return false;
	}

	return true;
}

?>
