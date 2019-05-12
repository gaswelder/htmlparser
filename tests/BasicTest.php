<?php

use gaswelder\htmlparser\tokstream;
use PHPUnit\Framework\TestCase;
use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

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

	function testAttributeSelectors()
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

		$this->assertCount(1, $doc->querySelectorAll('[id="posts"]'));
		$this->assertCount(3, $doc->querySelectorAll('[class^="post"]'));
		$this->assertCount(1, $doc->querySelectorAll('[id$="3"]'));
		$this->assertCount(0, $doc->querySelectorAll('[id$="z"]'));
		$this->assertCount(5, $doc->querySelectorAll('[id]'));
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
		foreach ($doc->querySelectorAll($selector) as $node) {
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

	function testRawTextTokens()
	{
		// When reading raw text, the lexer uses the unget buffer.
		// If done wrong, that may cause raw text tokens come out of order.
		$raw = 'This is a raw text! </head> <bwahaha>!';
		$html = '<!DOCTYPE html><html><head></head><body><script type="text">' . $raw . '</script></body></html>';

		// Read directly
		$t = new tokstream($html);
		$list1 = [];
		while ($token = $t->get()) {
			$list1[] = (string)$token;
		}

		// Read with unget
		$t = new tokstream($html);
		$list2 = [];
		$token = $t->get();
		$list2[] = (string)$token;
		while ($t->more()) {
			$t->unget($token);
			$token = $t->get();
			$token = $t->get();
			$list2[] = (string)$token;
		}

		$this->assertEquals($list1, $list2);
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

	function testCrap()
	{
		$raw = '<div class="row">
		<img src="foo.jpg" alt="I dont know how to "escape strings"">
		</div>';

		$p = new Parser();
		$doc = $p->parse($raw);
		$img = $doc->getElementsByTagName('img')[0];
		$this->assertEquals($img->getAttribute('alt'), 'I dont know how to ');
	}

	function testEntities()
	{
		$raw = 'Foo &amp; Bar Doesn&#8217;t need this.';
		$nice = 'Foo & Bar Doesn’t need this.';
		$t = new tokstream($raw);

		$list = $t->getAll();

		$this->assertCount(1, $list);
		$this->assertEquals($nice, $list[0]->content);
	}

	function testAttrEntity()
	{
		$html = '<abbr title="Eclog&aelig;">Ecl.</abbr>';
		$p = new Parser();
		$doc = $p->parse($html);

		$this->assertEquals('Eclogæ', $doc->firstChild->getAttribute('title'));
	}

	function testDoctype()
	{
		$html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<HTML>
		<HEAD>
		  <META NAME="KeyWords" CONTENT="HAHA,NOT,THAT,SIMPLE,ANYMORE">
		  <TITLE>DINOSAURS</TITLE>
		</HEAD>
		<BODY TEXT="#000000" BGCOLOR="#FFFFFF" LINK="#000000" VLINK="#666666" ALINK="#FF0000">
		</BODY>
		</HTML>';
		$p = new Parser();
		$p->parse($html);
	}

	function testSelectorCase()
	{
		// css selectors should be case-insensitive
		$html = '<!DOCTYPE html><HTML><body></body></HTML>';
		$p = new Parser();
		$doc = $p->parse($html);
		$this->assertCount(1, $doc->querySelectorAll('html'));
		$this->assertCount(1, $doc->querySelectorAll('BODY'));
	}

	function testUnquoted()
	{
		$html = '<BODY FOO=0 BAR=bar></BODY>';
		$p = new Parser();
		$doc = $p->parse($html);
		$body = $doc->querySelector('body');

		$this->assertEquals('0', $body->getAttribute('FOO'));
		$this->assertEquals('bar', $body->getAttribute('BAR'));
	}
}
