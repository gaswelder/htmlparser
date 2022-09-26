<?php

use gaswelder\htmlparser\dom\ElementNode;
use gaswelder\htmlparser\dom\TextNode;
use PHPUnit\Framework\TestCase;
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
}
