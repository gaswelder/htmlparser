<?php

use PHPUnit\Framework\TestCase;
use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

function parse($html)
{
	$p = new Parser();
	return $p->parse($html);
}

class BasicTest extends TestCase
{
	function test()
	{
		$html = "<!DOCTYPE html><html></html>";
		$p = new Parser();
		$p->parse($html);
	}

	function testGetElementsByTagName()
	{
		$html = '<!DOCTYPE html><html><head></head><body><script type="text">foobar!</script></body></html>';
		$p = new Parser();
		$doc = $p->parse($html);

		$bodies = $doc->getElementsByTagName('body');
		$this->assertEquals(1, $bodies->length);
		$this->assertEquals('body', $bodies[0]->tagName);
	}

	function testRawText()
	{
		$raw = 'This is a raw text! </head> <bwahaha>!';
		$html = '<!DOCTYPE html><html><head></head><body><script type="text">' . $raw . '</script></body></html>';

		$p = new Parser();
		$doc = $p->parse($html);

		$scripts = $doc->getElementsByTagName('script');
		$this->assertEquals($raw, $scripts[0]->childNodes[0]->textContent);
	}

	function testAttrEntity()
	{
		$html = '<abbr title="Eclog&aelig;">Ecl.</abbr>';
		$p = new Parser();
		$doc = $p->parse($html);

		$this->assertEquals('Eclogæ', $doc->firstChild->getAttribute('title'));
	}

	function testMeta()
	{
		$html = '<head><META name="foo" content="bar"><meta name="foo" content="bar"></head>';
		$f = parse($html)->format();
		$this->assertNotContains('</meta>', $f);
		$this->assertNotContains('</META>', $f);
	}

	function testWeirdFormatting()
	{
		$html = '<p>
		one<A HREF="CHAPTER_02.HTM#barr"
		  >two</A
		>:
	  </p>
	  three';

		$f = parse($html)->format();
		$this->assertContains('one', $f);
		$this->assertContains('two', $f);
		$this->assertContains('three', $f);
	}
}
