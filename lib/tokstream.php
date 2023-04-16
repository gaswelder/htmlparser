<?php

namespace gaswelder\htmlparser;

/*
 * First stage parser for HTML documents.
 */

class tokstream
{
	const spaces = "\r\n\t ";
	const alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	const num = "0123456789";

	/*
	 * A parsebuf instance
	 */
	private $buf;

	/*
	 * Cache for tokens that have already been read.
	 */
	private $peek = array();

	/**
	 * @param string $htmlSource
	 */
	function __construct($htmlSource)
	{
		$this->buf = new parsebuf($htmlSource);
	}

	/*
	 * Returns string describing current
	 * line and column position in the buffer
	 */
	function pos()
	{
		return $this->buf->pos();
	}

	/**
	 * Returns true if there are more tokens in the stream.
	 *
	 * @return bool
	 */
	function more()
	{
		return $this->peek() !== null;
	}

	/**
	 * Returns next token without removing it from the stream.
	 * Returns null if there are no more tokens.
	 *
	 * @return token|null
	 */
	function peek()
	{
		if (empty($this->peek)) {
			$t = $this->read();
			if (!$t) return null;
			array_unshift($this->peek, $t);
		}
		return $this->peek[0];
	}

	/**
	 * Returns next token and removes it from the stream.
	 * Returns null if there are no more tokens.
	 *
	 * @return token|null
	 */
	function get()
	{
		if (!empty($this->peek)) {
			return array_shift($this->peek);
		}
		return $this->read();
	}

	/*
	 * Pushes a token back into the stream
	 */
	function unget(token $tok)
	{
		array_unshift($this->peek, $tok);
	}

	/**
	 * Reads all the rest of the buffer and returns the tokens as array.
	 *
	 * @return array
	 */
	function getAll()
	{
		$all = [];
		while ($this->more()) {
			$all[] = $this->get();
		}
		return $all;
	}

	/*
	 * Reads a token from the buffer
	 */
	private function read()
	{
		if (!$this->buf->more()) {
			return null;
		}

		$pos = $this->buf->pos();
		$t = null;

		if ($this->buf->literal_follows('<!DOCTYPE')) {
			$t = $this->read_doctype();
		} else if ($this->buf->literal_follows('<!--')) {
			$t = $this->read_comment();
		}
		// Invalid XML declarations.
		else if ($this->buf->literal_follows('<?xml')) {
			$t = $this->read_xml_declaration();
		}
		// Invalid ASP tags, discard
		else if ($this->buf->literal_follows('<%')) {
			$this->discard_asp_tag();
			return $this->read();
		}
		// If this tag starts a container for another language (like JS or CSS)
		// ("raw text container"), read the raw text.
		else if ($this->buf->peek() == '<') {
			$t = $this->read_tag();
			$tagName = $t->content[0];
			$tagNameLC = strtolower($tagName);
			if ($tagNameLC == 'style' || $tagNameLC == 'script') {
				$rawText = $this->readRawText($tagName);
				array_unshift($this->peek, $rawText);
			}
		} else {
			$t = $this->read_text();
		}

		if (!$t) return null;
		$t->pos = $pos;
		return $t;
	}

	/*
	 * Reads raw text until the end tag with the given name.
	 */
	private function readRawText($name)
	{
		$b = $this->buf;

		$close = "</$name>";
		$n = strlen($close);
		$content = '';
		while ($b->more() && strtolower($b->peekN($n)) != strtolower($close)) {
			$content .= $b->get();
		}
		return new token(token::TEXT, $content);
	}

	private function read_doctype()
	{
		$b = $this->buf;

		$b->skip_literal("<!DOCTYPE");

		if (!$b->read_set(self::spaces)) {
			return $this->error("Missing space after <!DOCTYPE");
		}

		$type = $b->skip_until('>');

		$b->read_set(self::spaces);
		if ($b->get() != '>') {
			return $this->error("Missing '>'");
		}

		return new token(token::DOCTYPE, 'html');
	}

	private function read_xml_declaration()
	{
		$b = $this->buf;
		$b->skip_literal('<?xml');
		if (!$b->read_set(self::spaces)) {
			return $this->error("Missing space after <?xml");
		}
		$content = $b->until_literal("?>");
		if (!$b->skip_literal("?>")) {
			return $this->error("'?>' expected");
		}
		return new token(token::XML_DECLARATION);
	}

	private function discard_asp_tag()
	{
		$this->buf->skip_literal('<%');
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			if ($ch == '>') {
				break;
			}
		}
	}

	private function read_comment()
	{
		$this->buf->skip_literal("<!--");
		$s = '';
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			if ($ch == '-' && $this->buf->skip_literal('->')) {
				return new token(token::COMMENT, $s);
			}
			$s .= $ch;
		}
		return $this->error("--> expected");
	}

	private function read_tag()
	{
		$this->buf->expect('<');
		$s = $this->buf;

		// Read tag name.
		// Read whatever is there, don't validate.
		$tagName = $s->read_set(self::alpha . self::num . ':-/');
		if ($tagName === '') {
			return $this->error("Tag name expected, got '$tagName' at " . $s->pos());
		}

		// Read the attributes.
		$attrs = [];
		while (true) {
			$s->read_set(self::spaces);

			// Read attribute name. If no name, stop.
			$attrName = $s->read_set(self::alpha . '-_0123456789:');
			if (!$attrName) {
				break;
			}

			$s->read_set(self::spaces);
			// If '=' follows, read the value.
			// If not, treat the attribute as a boolean.
			if ($s->peekget('=')) {
				$s->read_set(self::spaces);
				$val = $this->readAttributeValue();
				$attrs[$attrName] = $val;
			} else {
				$attrs[$attrName] = true;
			}
		}

		// Skip optional XML-style ending.
		if ($s->peek() == '/') {
			$s->get();
		}

		// Skip some crap.
		$crap = '';
		while ($s->more() && $s->peek() != '>') {
			$crap .= $s->get();
		}
		if ($crap) {
			$this->warning("skipped crap: $crap");
		}

		$ch = $s->get();
		if ($ch != '>') {
			return $this->error("'>' expected, got '$ch'", $s->pos());
		}

		return new token(token::TAG, [$tagName, $attrs]);
	}

	/**
	 * Reads attribute value, what follows after a "attr=".
	 */
	private function readAttributeValue()
	{
		$s = $this->buf;

		// If a quote character follows, read the happy standard case.
		if ($s->peek() == '"') {
			$s->get();
			$val = $s->skip_until('"');
			if ($s->get() != '"') {
				return $this->error("'\"' expected", $s->pos());
			}
			return html_entity_decode($val);
		}

		// Try reading a value in single quotes.
		if ($s->peek() == "'") {
			$s->get();
			$val = $s->skip_until("'");
			if ($s->get() != "'") {
				return $this->error("''' expected", $s->pos());
			}
			return html_entity_decode($val);
		}

		// If no quotes, try reading a value without them.
		$val = $s->read_set(self::alpha . self::num . '#_+-');
		if ($val === '') {
			return $this->error("Couldn't get attribute value: " . $s->peek() . " at " . $s->pos());
		}
		return html_entity_decode($val);
	}

	private function read_text()
	{
		$s = '';
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			if ($ch == '<') {
				$this->buf->unget($ch);
				break;
			}

			if ($ch == '&') {
				$this->buf->unget($ch);
				$s .= $this->read_entity();
				continue;
			}

			$s .= $ch;
		}

		return new token(token::TEXT, $s);
	}

	// Entities are treated as an encoding by this parser.
	// They are replaced here at the lower level and don't ever get to the user code.
	private function read_entity()
	{
		$b = $this->buf;

		$s = $b->expect('&');
		if ($b->peek() == '#') {
			$s .= $b->get();
			$s .= $b->read_set('0123456789');
		} else {
			while (ctype_alnum($b->peek())) {
				$s .= $b->get();
			}
		}

		if ($b->peek() == ';') {
			$b->get();
		} else {
			$this->warning("';' expected");
		}
		$s .= ';';
		return html_entity_decode($s);
	}

	/**
	 * Reports an error and aborts.
	 *
	 * @param string $msg
	 * @throws ParsingException
	 */
	private function error($msg)
	{
		throw new ParsingException($msg);
	}

	private function warning($msg)
	{
		// var_dump($msg);
	}
}
