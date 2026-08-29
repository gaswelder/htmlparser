<?php

use gaswelder\htmlparser\Parser;

use function gaswelder\htmlparser\dom\format;

require __DIR__ . '/../init.php';

class FormatTest extends TestCase
{
	function testFormat1()
	{
		$cases = [
			'<p>text</p>',
			'<p><b>text</b></p>',
			"<div>\n  text\n</div>",
			"<div>\n  <b>text</b>\n</div>",
			"<div>\n  <p>text</p>\n</div>",
		];
		foreach ($cases as $html) {
			$doc = Parser::parse($html);
			$this->assertEquals($html, format($doc));
		}
	}
}
