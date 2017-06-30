<?php

namespace gaswelder\htmlparser;

use gaswelder\htmlparser\dom\ElementNode;

/**
 * Dedicated parser for tags themselves.
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

	private $options;

	function __construct($options)
	{
		$this->options = $options;
	}

	/**
	 * Parses a tag token and returns the corresponding element.
	 *
	 * @param token $tok
	 * @return ElementNode
	 */
	function parse(token $tok)
	{
		$s = new parsebuf($tok->content, $tok->pos);

		if ($s->get() != '<') {
			return $this->error("'<' expected", $tok->pos);
		}

		/*
		 * Read the tag name.
		 */
		$name = $s->get();
		if (!$name || strpos(self::alpha, $name) === false) {
			return $this->error("Tag name expected", $s->pos());
		}
		$name .= $s->read_set(self::alpha.self::num);

		$element = new ElementNode($name);

		/*
		 * Read attributes, one pair/flag at a time.
		 */
		while (ctype_space($s->peek())) {
			$s->read_set(self::spaces);
			list($name, $val) = $this->tagattr($s);
			if (!$name) {
				break;
			}
			$element->setAttribute($name, $val);
		}

		if ($this->options['xml_perversion'] && $s->peek() == '/') {
			$s->get();
		}

		$ch = $s->get();
		if ($ch != '>') {
			return $this->error("'>' expected, got '$ch'", $s->pos());
		}

		return $element;
	}

	private function tagattr(parsebuf $s)
	{
		/*
		 * Read attribute name.
		 */
		$name = $s->read_set(self::alpha.'-_0123456789');
		if (!$name) {
			return array(null, null);
		}

		/*
		 * If no '=' follows, this is a flag only.
		 */
		if ($s->peek() != '=') {
			return array($name, true);
		}
		$s->get();

		/*
		 * Read the value.
		 */
		$val = $this->tagval($s);
		if ($val === null) {
			return array(null, null);
		}

		return array($name, $val);
	}

	private function tagval(parsebuf $s)
	{
		if ($s->peek() == '"') {
			$s->get();
			$val = $s->skip_until('"');
			if ($s->get() != '"') {
				return $this->error("'\"' expected", $s->pos());
			}
			return $val;
		}

		if ($this->options['missing_quotes'] && ctype_alpha($s->peek())) {
			return $s->read_set(self::alpha);
		}

		if ($this->options['single_quotes'] && $s->peek() == "'") {
			$s->get();
			$val = $s->skip_until("'");
			if ($s->get() != "'") {
				return $this->error("''' expected", $s->pos());
			}
			return $val;
		}

		return $this->error("Unexpected character: ".$s->peek(), $s->pos());
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		throw new ParsingException($msg);
	}
}
