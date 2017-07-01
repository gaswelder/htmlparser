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

	function testAttributes()
	{
		$html = '<!DOCTYPE html><html><body>
			<!--noindex-->
			<a rel="nofollow" href="#" id="move_up"><img src="/img/up.png" width="32"></a>
			<a rel="nofollow" target="_blank" href="https://www.facepalm.com/foobar" id="facepalm" title="Facepalm"></a>
			<a rel="nofollow" target="_blank" href="http://kgb.com/foobar" id="vkgb" title="velkom komrad"></a>
			<!--/noindex-->
			<div class="container">
				<header>
					<div id="logo"></div>
					<a href="http://ooogle.com">oogle</a>
				</header>
			</div>
			<a href="https://bigbrother.com" target="_blank" rel="nofollow">Big Brother is watching you</a>
			</body>
			</html>';
		$selector = 'a[rel="nofollow"][target="_blank"]';
		$p = new Parser();
		$doc = $p->parse($html);
		$expect = [
			'https://www.facepalm.com/foobar',
			'http://kgb.com/foobar',
			'https://bigbrother.com'
		];
		$links = [];
		foreach($doc->querySelectorAll($selector) as $node) {
			$links[] = $node->getAttribute('href');
		}
		$this->assertEquals($expect, $links);
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
}
