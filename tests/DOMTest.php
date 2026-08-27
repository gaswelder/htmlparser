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
		$this->assertEquals('i', $nextElement->tagName());
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
		$text = $doc->firstChild()->firstChild();
		if (!($text instanceof TextNode)) {
			throw new Exception("expected a text node");
		}
		$val = $text->textContent();
		$this->assertEquals($val, "isn't");
	}

	function testRemoveAttribute()
	{
		$doc = Parser::parse('<img src="one" srcset="two" sizes="three">');
		$img = $doc->querySelector('img');
		$img->removeAttribute('srcset');
		$img->removeAttribute('sizes');
		$this->assertEquals(trim($doc->format()), '<img src="one">');
	}

	function testGetAttribute()
	{
		$doc = Parser::parse('<img SRC="one">');
		$img = $doc->querySelector('img');
		$this->assertEquals($img->getAttribute("src"), "one");
	}
}
