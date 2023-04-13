<?php

use gaswelder\htmlparser\dom\ElementNode;
use gaswelder\htmlparser\dom\TextNode;
use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

class DOMTest extends TestCase
{
	function testNextElementSibling()
	{
		$html = '<body><b></b>text<i></i>';
		$doc = Parser::parse($html);
		$b = $doc->querySelector('b');
		$next = $b->nextSibling();
		$this->assertInstanceOf(TextNode::class, $next);

		$nextElement = $b->nextElementSibling();
		$this->assertInstanceOf(ElementNode::class, $nextElement);
		$this->assertEquals('i', $nextElement->tagName);
	}

	function testEscapedAttributeValues()
	{
		$doc = Parser::parse('<body><a val="isn&#039;t"></a></body>');
		$val = $doc->querySelector('a')->getAttribute('val');
		$this->assertEquals($val, "isn't");
	}

	function testEscapedText()
	{
		$doc = Parser::parse('<body>isn&#039;t</body>');
		$val = $doc->firstChild->firstChild->textContent;
		$this->assertEquals($val, "isn't");
	}
}
