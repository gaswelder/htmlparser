<?php
namespace gaswelder\htmlparser;

use gaswelder\htmlparser\dom\DocumentNode;
use gaswelder\htmlparser\dom\CommentNode;
use gaswelder\htmlparser\dom\TextNode;
use gaswelder\htmlparser\dom\ContainerNode;

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
	private static $def = [
		'xml_perversion' => true,
		'single_quotes' => true,
		'missing_quotes' => false,
		'missing_closing_tags' => true,
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
		$this->parseTree($doc);
		return $doc;
	}

	private function parseTree(ContainerNode $parent)
	{
		$s = $this->s;

		// Read while there are more tokens and the next
		// token is not a closing tag.
		while ($s->more() && !$s->peek()->isClosingTag()) {
			$token = $s->get();

			if ($token->type == token::DOCTYPE) {
				continue;
			}

			// If an opening tag, create the element.
			if ($token->type == token::TAG) {
				$node = $this->tagParser->parse($token);
				if (!$this->isChildless($node)) {
					$this->parseTree($node);
					$t = $s->get();
					if (!$t || !$t->isClosingTag($node->tagName)) {
						if ($this->options['missing_closing_tags']) {
							$s->unget($t);
						} else {
							return $this->error("Expected closing tag for '$node->tagName', got $t", $t->pos);
						}
					}
				}
			}
			else if ($token->type == token::TEXT) {
				$node = new TextNode($token->content);
			}
			else if ($token->type == token::COMMENT) {
				$node = new CommentNode($token->content);
			}
			else {
				return $this->error("Unexpected token: $token", $token->pos);
			}

			$parent->appendChild($node);
		}
	}

	/**
	 * Returns true if the given element is a "childless" element
	 * like <br> or <img>/
	 *
	 * @param ContainerNode $node
	 * @return bool
	 */
	private function isChildless(ContainerNode $node)
	{
		return in_array(strtolower($node->tagName), self::$singles);
	}

	private function error($msg, $pos = null)
	{
		if ($pos) $msg .= " at $pos";
		throw new ParsingException($msg);
	}
}

?>
