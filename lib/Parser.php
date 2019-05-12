<?php
namespace gaswelder\htmlparser;

use gaswelder\htmlparser\dom\DocumentNode;
use gaswelder\htmlparser\dom\CommentNode;
use gaswelder\htmlparser\dom\TextNode;
use gaswelder\htmlparser\dom\ContainerNode;
use gaswelder\htmlparser\dom\ElementNode;

const UTF8_BOM = "\xEF\xBB\xBF";

class Parser
{
	/*
	 * Parsing options and their defaults
	 */
	private $options;
	private static $def = [
		'single_quotes' => true,
		'missing_quotes' => true,
		'missing_closing_tags' => true,
		'ignore_xml_declarations' => true,
		'skip_crap' => true
	];

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
			throw new \Exception("Unknown options: " . implode(', ', $k));
		}
		foreach (self::$def as $k => $v) {
			if (!isset($options[$k])) {
				$options[$k] = $v;
			}
		}
		$this->options = $options;
		$this->tagParser = new tagparser($options);
	}

	/**
	 * Parses the given HTML source.
	 *
	 * @param string $htmlSource
	 * @return DocumentNode
	 * @throws ParsingException
	 */
	function parse($htmlSource)
	{
		/*
		 * Skip UTF-8 marker if it's present
		 */
		if (substr($htmlSource, 0, 3) == UTF8_BOM) {
			$htmlSource = substr($htmlSource, 3);
		}

		$this->s = new tokstream($htmlSource);

		$doc = new DocumentNode();

		// Read doctype if it's there.
		if ($this->s->more() && $this->s->peek()->type == 'doctype') {
			$doc->type = $this->s->get()->content;
		}

		// Discard invalid nodes at the top level.
		while ($this->s->more() && $this->s->peek()->isClosingTag()) {
			$this->s->get();
		}

		$this->parseContents($doc, []);
		return $doc;
	}

	/**
	 * Reads and appends contents belonging to the given container node.
	 */
	private function parseContents(ContainerNode $parent, $ancestors)
	{
		while (true) {
			$node = $this->parseNode($parent, $ancestors);
			if (!$node) break;
			$parent->appendChild($node);
		}
	}

	/**
	 * Returns the next node belonging to the given container.
	 * Returns null if there are no more such nodes.
	 */
	private function parseNode(ContainerNode $parent, $ancestors)
	{
		$s = $this->s;

		// Discard doctypes and XML headers strewn around the document.
		while ($s->more()) {
			$next = $s->peek();
			if ($next->type == token::DOCTYPE) {
				$s->get();
				continue;
			}
			if ($next->type == token::XML_DECLARATION && $this->options['ignore_xml_declarations']) {
				$s->get();
				continue;
			}
			break;
		}

		if (!$s->more()) {
			return null;
		}

		$token = $s->get();

		if ($token->type == token::TEXT) {
			return new TextNode($token->content);
		}
		if ($token->type == token::COMMENT) {
			return new CommentNode($token->content);
		}
		if ($token->type != token::TAG) {
			return $this->error("Unexpected token: $token", $token->pos);
		}

		if ($parent instanceof ElementNode) {
			// Normal closing tag as expected.
			if ($token->isClosingTag($parent->tagName)) {
				return null;
			}

			// Auto-close 'p'
			if ($token->isClosingTag() && strtolower($parent->tagName) == 'p') {
				$s->unget($token);
				return null;
			}
		}

		if ($token->isClosingTag()) {
			// echo "seeing $token under $parent->tagName\n";
			// echo "- ";
			// foreach ($ancestors as $p) {
			// 	echo "/$p";
			// }
			// echo "\n";
		}

		$node = $this->tagParser->parse($token);
		if ($node->_isVoid()) {
			return $node;
		}

		// Autoclose <p> tags.
		if ($parent instanceof ElementNode) {
			if (strtolower($parent->tagName) == 'p' && $node->_isBlock()) {
				$s->unget($token);
				return null;
			}
		}

		// The node is a container, recurse.
		$this->parseContents($node, array_merge($ancestors, [$parent]));

		return $node;
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		throw new ParsingException($msg);
	}
}
