<?php
require 'lib/parser.php';

$opt = array(
	'single_quotes' => true,
	'xml_perversion' => true
);
$p = new \gaswelder\htmlp\parser($opt);

$paths = glob('test/*.html');
foreach ($paths as $path) {
	$doc = $p->parse(file_get_contents($path));
	if($err = $p->err()) {
		fwrite(STDERR, "$path: $err\n");
	}
	echo $path.': ';
	echo $doc ? "OK" : "FAIL", PHP_EOL;
}

?>
