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

	if ($path == 'test/01.html' && !testsel($path, $doc)) {
		return false;
	}

	return true;
}

function testsel($path, $doc)
{
	$selector = 'a[rel="nofollow"][target="_blank"]';
	$result = array(
		'https://www.facebook.com/swimmingminsk',
		'http://vk.com/swimming_minsk',
		'https://metrika.yandex.by/stat/?id=19625248&amp;from=informer',
		'http://webpay.by/'
	);

	$S = $doc->querySelectorAll($selector);
	if (count($S) != count($result)) {
		fwrite(STDERR, "$path: selector $selector failed");
		return false;
	}

	foreach ($S as $s) {
		$href = $s->getAttribute('href');
		if ($href != array_shift($result)) {
			fwrite(STDERR, "$path: selector $selector failed");
			return false;
		}
	}

	return true;
}

?>
