<?php

use PHPUnit\Framework\TestCase;
use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

class SelectorsTest extends TestCase
{
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

    function testSelectorCase()
    {
        // css selectors should be case-insensitive
        $html = '<!DOCTYPE html><HTML><body></body></HTML>';
        $p = new Parser();
        $doc = $p->parse($html);
        $this->assertCount(1, $doc->querySelectorAll('html'));
        $this->assertCount(1, $doc->querySelectorAll('BODY'));
    }

    function testNextSibling()
    {
        $html = '<section><p class="mark">1</p><h1>One</h1></section>
            <section><p class="mark">2</p><h1>Two</h1></section>';
        $doc = (new Parser)->parse($html);
        $hh = $doc->querySelectorAll('p.mark + h1');
        $this->assertCount(2, $hh);
        $this->assertEquals('One', $hh[0]->innerHTML);
        $this->assertEquals('Two', $hh[1]->innerHTML);
    }
}
