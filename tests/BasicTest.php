<?php

use gaswelder\htmlparser\tokstream;
use PHPUnit\Framework\TestCase;
use gaswelder\htmlparser\Parser;
require __DIR__.'/../init.php';

class BasicTest extends TestCase
{
	function test()
	{
		$html = "<!DOCTYPE html><html></html>";
		$p = new Parser();
		$p->parse($html);
	}

	function testPeek()
	{
		$html = "<!DOCTYPE html><html><head></head><body></body></html>";
		$t = new tokstream($html);
		$this->assertEquals($t->peek(), $t->peek());
	}
}
