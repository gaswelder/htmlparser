<?php

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
}
