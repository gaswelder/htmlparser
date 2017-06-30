# HTML parser

This is an HTML parser with a DOM implementation. It doesn't depend on
PHP's bundled libxml and DOM.


## Usage

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

// work with $doc
```


## Features

I wrote it to do validation for HTML documents in some of my projects.
It was also used for data mining from web.

As it was used for data mining, the DOM implementation has the
`querySelectorAll` function, so the following can be done:

```php
<?php
$deps = $doc->querySelectorAll('a[rel="nofollow"]');
foreach($deps as $node) {
	$url = $node->getAttribute('href');
	// don't follow $url...
}
```

CSS2 selectors are not implemented (except the basic attribute
selectors like in the example above) because it's rarely needed when
you have a parsed tree object anyway.

This parser is a bit strict for generally untidy HTML out there,
although I added some "mellowing" options when I had to parse some
pages out there. There are three options which are, sadly, often
needed:

* `xml_perversion` - XML syntax like `<br/>` instead of `<br>`;
* `single_quotes` - single quotes around argument values (`<a href='...'>`);
* `missing_quotes` - missing quotes around argument values (`<a class=foo>`);
* `missing_closing_tags` - cases where someone forgot to add `</div>`, for example.


## Installation

Composer dudes do this in the console:

	composer require gaswelder/htmlp

then, in the project, if not done already:

	require "vendor/autoload.php";

Old-school dudes may download the library to whatever $libdir they have
and do this:

	require "$libdir/htmlparser/init.php";

After that the usage is the same for both types of dudes.
