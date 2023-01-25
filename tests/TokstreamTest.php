<?php

use gaswelder\htmlparser\tokstream;

require __DIR__ . '/../init.php';

class TokstreamTest extends TestCase
{
    function testPeek()
    {
        $html = "<!DOCTYPE html><html><head></head><body></body></html>";
        $t = new tokstream($html);
        $this->assertEquals($t->peek(), $t->peek());
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

    function testEntities()
    {
        $raw = 'Foo &amp; Bar Doesn&#8217;t need this.';
        $nice = 'Foo & Bar Doesn’t need this.';
        $t = new tokstream($raw);

        $list = $t->getAll();

        $this->assertCount(1, $list);
        $this->assertEquals($nice, $list[0]->content);
    }
}
