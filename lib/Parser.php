<?php
namespace gaswelder\htmlparser;

use gaswelder\htmlparser\dom\DocumentNode;
use gaswelder\htmlparser\dom\CommentNode;
use gaswelder\htmlparser\dom\TextNode;
use gaswelder\htmlparser\dom\ElementNode;

const UTF8_BOM = "\xEF\xBB\xBF";

class Parser
{
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

	/*
	 * DocumentNode object that we will return on success
	 */
	private $doc;

	/**
	 * @var tokstream
	 */
	private $s;

	/**
	 * @var tagparser
	 */
	private $tagParser;

	function __construct($options = array())
	{
		/*
		 * Fill in defaults options where needed
		 */
		$k = array_diff(array_keys($options), array_keys(self::$def));
		if (!empty($k)) {
			throw new \Exception("Unknown options: ".implode(', ', $k));
		}
		foreach (self::$def as $k => $v) {
			if (!isset($options[$k])) {
				$options[$k] = $v;
			}
		}
		$this->options = $options;
		$this->tagParser = new tagparser($options);
	}

	function parse($s)
	{
		/*
		 * Skip UTF-8 marker if it's present
		 */
		if (substr($s, 0, 3) == UTF8_BOM) {
			$s = substr($s, 3);
		}

		$this->s = new tokstream($s);
		$this->doc = new DocumentNode();

		$t = $this->tok();
		if (!$t) {
			return $this->error("No data");
		}
		if ($t->type != token::DOCTYPE) {
			return $this->error("Missing doctype");
		}

		$this->doc->type = $t->content;

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

			if ($t->type == token::COMMENT) {
				$this->doc->appendChild(new CommentNode($t->content));
				continue;
			}

			if ($t->type == token::TEXT && ctype_space($t->content)) {
				continue;
			}

			return $t;
		}
	}

	private function skip_empty_text()
	{
		while ($t = $this->s->peek()) {
			if ($t->type == token::TEXT && ctype_space($t->content)) {
				$this->s->get();
				continue;
			}

			if ($t->type == token::COMMENT) {
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
	private function parse_subtree($parent_element = null)
	{
		$tok = $this->tok();
		if (!$tok || $tok->type != token::TAG) {
			return $this->error("No tag in the stream ($tok)", $this->s->pos());
		}

		/*
		 * This must be an opening tag.
		 */
		if (strpos($tok->content, '</') === 0) {
			$msg = "Unexpected closing tag ($tok->content)";
			if($parent_element) {
				$msg .= " (inside $parent_element->tagName)";
			}
			return $this->error($msg, $this->s->pos());
		}

		/*
		 * Parse the tag token into an element.
		 */
		$element = $this->tagParser->parse($tok);
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
		 * Closing tag that we will expect
		 */
		$close = strtolower("</".$element->tagName.">");

		/*
		 * Process the tokens that will correspond to child nodes of
		 * the current element.
		 */
		while ($tok = $this->tok()) {
			/*
			 * If this is our closing tag, put it back and exit the
			 * loop.
			 */
			if ($tok->type == token::TAG && strtolower($tok->content) == $close) {
				$this->s->unget($tok);
				break;
			}

			/*
			 * Convert whatever comes in into a node and append as
			 * a child to the tree.
			 */
			switch ($tok->type) {
			case token::TEXT:
				$element->appendChild(new TextNode($tok->content));
				break;
			case token::TAG:
				$this->s->unget($tok);
				$subtree = $this->parse_subtree($element);
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
		if (!$tok || $tok->type != token::TAG || strtolower($tok->content) != $close) {
			return $this->error("$close expected", $this->s->pos());
		}
		$this->s->get();
		return $element;
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		throw new ParsingException($msg);
	}
}

?>
