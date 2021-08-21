<?php

use gaswelder\htmlparser\dom\ElementNode;
use gaswelder\htmlparser\dom\TextNode;
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

	function testNextElementSibling()
	{
		$html = '<body><b></b>text<i></i>';
		$doc = (new Parser)->parse($html);
		$b = $doc->querySelector('b');
		$next = $b->nextSibling;
		$this->assertInstanceOf(TextNode::class, $next);

		$nextElement = $b->nextElementSibling;
		$this->assertInstanceOf(ElementNode::class, $nextElement);
		$this->assertEquals('i', $nextElement->tagName);
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

	function testAutoclosing()
	{
		$table = [
			['<p>a<p>b', "<!DOCTYPE html><p>a</p>\n<p>b</p>"],
			['<p>a<div>b</div>', "<!DOCTYPE html><p>a</p>\n<div>b</div>"],
			['<td><p>hoho</td>', '<!DOCTYPE html><td><p>hoho</p></td>']
		];

		foreach ($table as $case) {
			[$html, $fmt] = $case;
			$f = trim(parse($html)->format());
			$this->assertEquals($fmt, $f);
		}
	}

	function testScriptContents()
	{
		$html = "<SCRIPT language=\"JavaScript\">
		$(document).ready(function () {
						if (option_val < val && option_val != 0) {
							$(elm).attr('disabled', 'disabled');
						}
					});";
		$doc = parse($html);
	}
}
