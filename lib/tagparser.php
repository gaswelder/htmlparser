<?php

namespace gaswelder\htmlparser;

use gaswelder\htmlparser\dom\ElementNode;

/**
 * Dedicated parser for tags.
 * Tags are sequences starting with '<' and ending with '>'.
 */
class tagparser
{
	const alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	const num = "0123456789";
	const spaces = "\r\n\t ";

	/**
	 * @var parsebuf
	 */
	private $s;

	/**
	 * Parses a tag token and returns the corresponding element.
	 *
	 * @param token $tok
	 * @return ElementNode
	 */
	function parse(token $tok)
	{
		$this->s = new parsebuf($tok->content, $tok->pos);
		$s = $this->s;

		if ($s->get() != '<') {
			return $this->error("'<' expected", $tok->pos);
		}

		$name = $this->readName();
		$element = new ElementNode($name);

		while (1) {
			// Skip spaces
			$this->spaces();

			// Read attribute name. If no name, stop.
			$name = $this->attrname();
			if (!$name) break;

			// If '=' follows, read the value.
			// If not, treat the attribute as a boolean.
			if ($this->s->peek() == '=') {
				$this->s->get();
				$val = $this->readAttributeValue();
			} else {
				$val = true;
			}

			$element->setAttribute($name, $val);
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

		return $element;
	}

	private function spaces()
	{
		$this->s->read_set(self::spaces);
	}

	/**
	 * Reads the tag's name.
	 *
	 * @return string
	 * @throws ParsingException
	 */
	private function readName()
	{
		$s = $this->s;

		$name = $s->get();
		if (!$name || strpos(self::alpha, $name) === false) {
			return $this->error("Tag name expected", $s->pos());
		}
		$name .= $s->read_set(self::alpha . self::num . ':');

		return $name;
	}

	/**
	 * Reads attribute name.
	 */
	private function attrname()
	{
		$s = $this->s;
		$name = $s->read_set(self::alpha . '-_0123456789:');
		return $name;
	}

	/**
	 * Reads attribute value.
	 */
	private function readAttributeValue()
	{
		$s = $this->s;

		// If a quote character follows, read the happy standard case.
		if ($s->peek() == '"') {
			$s->get();
			$val = $s->skip_until('"');
			if ($s->get() != '"') {
				return $this->error("'\"' expected", $s->pos());
			}
			return html_entity_decode($val);
		}

		// If no quotes, try reading a value without them.
		if ($s->peek() == '_' || ctype_alnum($s->peek())) {
			return html_entity_decode($s->read_set(self::alpha . self::num));
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

		return $this->error("Unexpected character: " . $s->peek(), $s->pos());
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		throw new ParsingException($msg);
	}

	private function warning($msg)
	{
		//
	}
}
