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

	function testQuerySelectorAll()
	{
		$html = '<!DOCTYPE html><html><body>
			<div>
				<div id="posts">
					<div class="post foo bar" id="p1"></div>
					<div class="qwe rty post" id="p2"></div>
				</div>
				<div>
					<div class="post asd" id="p3"></div>
					<div class="post" id="p4"></div>
				</div>
			</div>
		</body></html>';

		$p = new Parser();
		$doc = $p->parse($html);
		$posts = $doc->querySelectorAll('#posts .post');
		$ids = [];
		foreach ($posts as $node) {
			$ids[] = $node->getAttribute('id');
		}

		$this->assertEquals($ids, ['p1', 'p2']);
	}
}
