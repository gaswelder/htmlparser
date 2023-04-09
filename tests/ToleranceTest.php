<?php

use gaswelder\htmlparser\Parser;

require __DIR__ . '/../init.php';

class ToleranceTest extends TestCase
{
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

    function testCrap2()
    {
        $html = '<img data-image-caption="<p>your wp plugin has a bug</p>
        " />';
        $doc = Parser::parse($html);
        $img = $doc->firstChild;
        $this->assertEquals(trim($img->getAttribute('data-image-caption')), '<p>your wp plugin has a bug</p>');
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

    function testUnquoted()
    {
        $html = '<BODY FOO=0 BAR=bar target=_blank color=#333333></BODY>';
        $p = new Parser();
        $doc = $p->parse($html);
        $body = $doc->querySelector('body');

        $this->assertEquals('0', $body->getAttribute('FOO'));
        $this->assertEquals('bar', $body->getAttribute('BAR'));
        $this->assertEquals('#333333', $body->getAttribute('color'));
    }

    function testInvalidClosingTags()
    {
        $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
        <HTML>
        <BODY>
        <TABLE>
        <TD>One</TD>
        <TD>Two</TD>
        </TR>
        </TABLE>
        Foo bar';

        $f = Parser::parse($html)->format();
        $this->assertContains('Foo bar', $f);
    }

    function testInvalidNesting1()
    {
        $html = '
        <div>
        <table>
        </div>
        </table>';

        $p = new Parser();
        $p->parse($html);
    }

    function testAsp()
    {
        $doc = Parser::parse('<p><%abc%></p>');
        $this->assertEquals('', $doc->querySelector('p')->innerHTML());
    }

    function testSpaceAfterEqual()
    {
        $doc = Parser::parse("<a href= 'http://foo'>foo</a>");
        $this->assertEquals('http://foo', $doc->querySelector('a')->getAttribute('href'));
    }

    function testNonTag()
    {
        $doc = Parser::parse("<b>A <->B</-></b>");
        $this->assertEquals("A <->B</->", $doc->querySelector('b')->innerHTML());
    }
}
