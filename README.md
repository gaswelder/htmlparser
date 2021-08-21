# HTML parser

This is an HTML parser with a minimal DOM implementation. It doesn't depend on
PHP's bundled libxml and DOM.

## Usage example

```php
<?php
use \gaswelder\htmlparser\Parser;
use \gaswelder\htmlparser\ParsingException;

$p = new Parser();
try {
	$doc = $p->parse($html);
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

The parser can handle some of the broken markup artifacts:

- missing closing tags - a missing `</div>`, for example;
- unexpected `<?xml ...?>` tags in the HTML document;

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

## Installation

Composer dudes do this in the console:

    composer require gaswelder/htmlparser

Old-school dudes (if still alive) may download the library to whatever \$libdir they have and do this:

    require "$libdir/htmlparser/init.php";

After that the usage is the same for both types of dudes.

## Dumping to the console

The nodes can be printed to the console like in the Firefox's console.
For example, this code:

```php
<?php
$list = $doc->getElementsByTagName('a');
echo $list, PHP_EOL;
```

might produce this output:

    NodeList [ <a>, <a#top>, <a.first>, <a>, <a> ]
