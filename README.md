# HTML parser

This is an HTML parser with a minimal DOM implementation.
It doesn't depend on PHP's bundled libxml and DOM and handles some of the broken
markup encountered in the wild.

## Usage example

```php
<?php
use \gaswelder\htmlparser\Parser;
use \gaswelder\htmlparser\ParsingException;

try {
	$doc = Parser::parse($html);
} catch(ParsingException $e) {
	// ...
	return;
}

$images = $doc->querySelectorAll('#posts .post img');
foreach ($images as $img) {
	$src = $img->getAttribute('src');
	echo $src, "\n";
}
```

## Features

All container nodes (DocumentNode and ElementNode) have the `querySelector` and
`querySelectorAll` methods which support a limited subset of CSS2:

- type selectors (like `div`)
- some attribute selectors (`[checked]`, `[attr="val"]`, `[attr$="val"]`, `[attr^="val"]`)
- class selectors (`.active`)
- ID selectors (`#main`)

Also they support these combinators:

- descendant (`ul li`)
- child (`ul > li`)
- sibling (`li + li`)

The nodes can be printed to the console, and the output will be similar to
Firefox's console:

```php
<?php
$list = $doc->getElementsByTagName('a');
echo $list, PHP_EOL;
```

might produce this output:

    NodeList [ <a>, <a#top>, <a.first>, <a>, <a> ]

## Installation

Composer dudes do this in the console:

    composer require gaswelder/htmlparser

Old-school dudes (if still alive) may download the library to whatever \$libdir they have and do this:

    require "$libdir/htmlparser/init.php";
