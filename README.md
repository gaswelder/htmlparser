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

The parser can handle some of the broken markup which other libraries I tried
couldn't. There are several options that make the parser more tolerant, which
can be set to false to make it stricter:

* `missing_closing_tags` - cases where someone forgot to add `</div>`, for example;
* `missing_quotes` - missing quotes around argument values (`<a class=foo>`);
* `single_quotes` - single quotes around argument values (`<a href='...'>`);
* `xml_perversion` - mixing XHTML and HTML, like `<br/>` instead of `<br>`;
* `ignore_xml_declarations` - `<?xml ...?>` tags in the HTML document;
* `skip_crap` - skip invalid markup.

All of them except `single_quotes` are enabled by default. To specify the
options pass them as the argument to the constructor:

```php
<?php
$parser = new Parser([
	'xml_perversion' => false
]);
```

All container nodes (DocumentNode and ElementNode) have the `querySelector` and
`querySelectorAll` methods which support a limited subset of CSS2:

* type selectors (like `div`)
* simple attribute selectors (`[checked]`)
* class selectors (`.active`)
* ID selectors (`#main`)

Also they support these combinators:

* descendant (`ul li`)
* child (`ul > li`)
* sibling (`li + li`)


## Installation

Composer dudes do this in the console:

	composer require gaswelder/htmlparser

Old-school dudes may download the library to whatever $libdir they have
and do this:

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
