<?php
namespace htmlp;

class html_parser
{
	const alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	const num = "0123456789";
	const spaces = "\r\n\t ";

	private static $singles = array(
		'hr',
		'img',
		'br',
		'meta',
		'link',
		'input',
		'base'
	);

	/*
	 * Parsing options and their defaults
	 */
	private $options;
	private static $def = array(
		'xml_perversion' => true,
		'single_quotes' => true,
		'missing_quotes' => false
	);

	private $error = null;

	/*
	 * html_doc object that we will return on success
	 */
	private $doc;

	/*
	 * html tokens stream
	 */
	private $s;

	function __construct($options = array())
	{
		/*
		 * Fill in defaults options where needed
		 */
		$k = array_diff(array_keys($options), array_keys(self::$def));
		if (!empty($k)) {
			trigger_error("Unknown options: ".implode(', ', $k));
		}
		foreach (self::$def as $k => $v) {
			if (!isset($options[$k])) {
				$options[$k] = $v;
			}
		}
		$this->options = $options;
	}

	function parse($s)
	{
		$this->error = null;
		$this->s = new htmlstream($s);
		$this->doc = new html_doc();

		$t = $this->tok();
		if (!$t) {
			return $this->error($this->s->err());
		}

		if ($t->type == 'doctype') {
			$this->doc->type = $t->content;
		}
		else {
			return $this->error("Missing doctype");
		}

		$tree = $this->parse_subtree();
		if (!$tree) {
			return $this->error("Empty document");
		}

		$this->doc->appendChild($tree);

		$this->skip_empty_text();
		if ($this->s->more()) {
			$tok = $this->s->peek();
			return $this->error("Only one root element allowed, $tok");
		}
		return $this->doc;
	}

	/*
	 * Returns next "significant" token from the stream.
	 * Non-significant tokens (spaces and comments) that are
	 * encountered are automatically added to the document tree.
	 */
	private function tok()
	{
		while (1) {
			$t = $this->s->get();
			if (!$t) return null;

			if ($t->type == 'comment') {
				$this->doc->appendChild(new html_comment($t->content));
				continue;
			}

			if ($t->type == 'text' && ctype_space($t->content)) {
				continue;
			}

			return $t;
		}
	}

	private function skip_empty_text()
	{
		if ($this->error) return null;
		while ($t = $this->s->peek()) {
			if ($t->type == 'text' && ctype_space($t->content)) {
				$this->s->get();
				continue;
			}

			if ($t->type == 'comment') {
				$this->s->get();
				continue;
			}

			break;
		}
	}

	/*
	 * Returns a subtree or just one element if that element is not a
	 * container. Assumes that the next token will be a tag. If not,
	 * returns null;
	 */
	private function parse_subtree()
	{
		if ($this->error) return null;

		$tok = $this->tok();
		if (!$tok || $tok->type != 'tag') {
			return $this->error("No tag in the stream ($tok)", $this->s->pos());
		}

		/*
		 * This must be an opening tag.
		 */
		if (strpos($tok->content, '</') === 0) {
			return $this->error("Unexpected closing tag ($tok->content)",
				$this->s->pos());
		}

		/*
		 * Parse the tag token into an element.
		 */
		$element = $this->parse_tag($tok);
		if (!$element) {
			return $this->error("Couldn't parse the tag ($tok->content)",
				$this->s->pos());
		}

		/*
		 * If the element is not a container kind, return the element.
		 */
		if (in_array(strtolower($element->tagName), self::$singles)) {
			return $element;
		}

		/*
		 * Process the tokens that will correspond to child nodes of
		 * the current element.
		 */
		$close = strtolower("</".$element->tagName.">");
		while ($tok = $this->tok()) {
			/*
			 * If this is our closing tag, put it back and exit the
			 * loop.
			 */
			if ($tok->type == 'tag' && strtolower($tok->content) == $close) {
				$this->s->unget($tok);
				break;
			}

			/*
			 * Convert whatever comes in into a node and append as
			 * a child to the tree.
			 */
			switch ($tok->type) {
			case 'text':
				$element->appendChild(new html_text($tok->content));
				break;
			case 'tag':
				$this->s->unget($tok);
				$subtree = $this->parse_subtree();
				if (!$subtree){
					return $this->error("Subtree failed");
				}
				$element->appendChild($subtree);
				break;
			default:
				return $this->error("Unexpected token: $tok", $tok->pos);
			}
		}

		$tok = $this->s->peek();
		if (!$tok || $tok->type != 'tag' || strtolower($tok->content) != $close) {
			return $this->error("$close expected", $this->s->pos());
		}
		$this->s->get();
		return $element;
	}

	/*
	 * Parses a tag string and returns a corresponding element.
	 */
	private function parse_tag(token $tok)
	{
		if ($this->error) return null;

		/*
		 * This is a parser inside a parser, so we create another
		 * stream to work with.
		 */
		$s = new parsebuf($tok->content, $tok->pos);

		assert($s->get() == '<');

		/*
		 * Read the tag name.
		 */
		$name = $s->get();
		if (!$name || strpos(self::alpha, $name) === false) {
			return $this->error("Tag name expected", $s->pos());
		}
		$name .= $s->read_set(self::alpha.self::num);

		$element = new html_element($name);

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
		if ($this->error) return null;
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
		if ($this->error) return null;
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

	function err()
	{
		$err = $this->s->err();
		if (!$err) $err = $this->error;
		return $err;
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		//trigger_error($msg);
		//exit;
		if (!$this->error) {
			$this->error = $msg;
		}
		return null;
	}
}

?>
