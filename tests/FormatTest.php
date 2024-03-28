<?php

use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

class FormatTest extends TestCase
{
	function testFormat1()
	{
		$cases = [
			'<p>text</p>',
			'<p><b>text</b></p>',
			'<div> text</div>',
			'<div><b>text</b></div>',
			"<div>\n\t<p>text</p>\n</div>",
		];
		foreach ($cases as $html) {
			$doc = Parser::parse($html);
			$this->assertEquals($doc->format(), $html);
		}
	}
}
