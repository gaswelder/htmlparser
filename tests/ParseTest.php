<?php

use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

class ParseTest extends TestCase
{
	function test()
	{
		$html = "<!DOCTYPE html><html></html>";
		$p = new Parser();
		$p->parse($html);
	}

	function testNested()
	{
		$doc = Parser::parse('<p><span>info</span></p>');
		$p = $doc->firstChild;
		$this->assertEquals('p', $p->tagName);
		$span = $p->firstChild;
		$this->assertEquals('span', $span->tagName);
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
		$f = Parser::parse($html)->format();
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

		$f = Parser::parse($html)->format();
		$this->assertContains('one', $f);
		$this->assertContains('two', $f);
		$this->assertContains('three', $f);
	}

	function testAutoclosing()
	{
		$table = [
			['<p>a<p>b', "<p>a</p>\n\n<p>b</p>"],
			['<p>a<div>b</div>', "<p>a</p>\n\n<div>b</div>"],
			['<td><p>hoho</td>', "<td>\n<p>hoho</p>\n</td>"]
		];

		foreach ($table as $case) {
			[$html, $fmt] = $case;
			$f = trim(Parser::parse($html)->format());
			$this->assertEquals($f, $fmt);
		}
	}

	function testScriptContents()
	{
		$html = "<p>a</p><SCRIpt language=\"JavaScript\">
		$(document).ready(function () {
						if (option_val < val && option_val != 0) {
							$(elm).attr('disabled', 'disabled');
						}
					});</scripT><p>b</p>";
		$doc = Parser::parse($html);
		$tags = [];
		foreach ($doc->childNodes as $cn) {
			if ($cn->nodeType == 1) {
				$tags[] = $cn->tagName;
			}
		}
		$this->assertEquals(['p', 'SCRIpt', 'p'], $tags);
	}

	function testDataAttr()
	{
		$doc = Parser::parse('<p data-foo-bar="123"></p>');
		$p = $doc->querySelector('p');
		$this->assertEquals('123', $p->getAttribute("data-foo-bar"));
	}
}
