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
	/**
	 * Parses the given HTML source.
	 *
	 * @param string $htmlSource
	 * @return DocumentNode
	 * @throws ParsingException
	 */
	static function parse($htmlSource)
	{
		// Skip UTF-8 marker if it's present
		if (substr($htmlSource, 0, 3) == UTF8_BOM) {
			$htmlSource = substr($htmlSource, 3);
		}

		$tokens = new tokstream($htmlSource);
		$doc = new DocumentNode();

		// Read doctype if it's there.
		if ($tokens->more() && $tokens->peek()->type == 'doctype') {
			$doc->type = $tokens->get()->content;
		}

		// Discard invalid nodes at the top level.
		while ($tokens->more() && $tokens->peek()->isClosingTag()) {
			$tokens->get();
		}

		self::parseContents($tokens, $doc, []);
		return $doc;
	}

	/**
	 * Reads and appends contents belonging to the given container node.
	 */
	private static function parseContents(tokstream $tokens, ContainerNode $parent, $ancestors)
	{
		while (true) {
			$node = self::parseNode($tokens, $parent, $ancestors);
			if (!$node) break;
			$parent->appendChild($node);
		}
	}

	/**
	 * Returns the next node belonging to the given container.
	 * Returns null if there are no more such nodes.
	 */
	private static function parseNode(tokstream $tokens, ContainerNode $parent, $ancestors)
	{
		// Discard doctypes and XML headers strewn around the document.
		while ($tokens->more()) {
			$next = $tokens->peek();
			if ($next->type == token::DOCTYPE) {
				$tokens->get();
				continue;
			}
			if ($next->type == token::XML_DECLARATION) {
				$tokens->get();
				continue;
			}
			break;
		}

		if (!$tokens->more()) {
			return null;
		}

		$token = $tokens->get();

		if ($token->type == token::TEXT) {
			return new TextNode($token->content);
		}
		if ($token->type == token::COMMENT) {
			return new CommentNode($token->content);
		}
		if ($token->type != token::TAG) {
			throw new ParsingException("Unexpected token: $token at $token->pos");
		}

		if ($parent instanceof ElementNode) {
			// Normal closing tag as expected.
			if ($token->isClosingTag($parent->tagName)) {
				return null;
			}

			// Auto-close 'p'
			if ($token->isClosingTag() && strtolower($parent->tagName) == 'p') {
				$tokens->unget($token);
				return null;
			}
		}

		if ($token->isClosingTag()) {
			$n = strtolower($token->_closingTagName());

			// This is a tag that closes something else than the current container.
			// If there's a matching opening ancestor tag, assume it closes that
			// ancestor and therefore the current container also.
			$hasAncestor = false;
			foreach ($ancestors as $p) {
				if ($p instanceof ElementNode && strtolower($p->tagName) == $n) {
					$hasAncestor = true;
					break;
				}
			}
			if ($hasAncestor) {
				// $tokens->unget($token);
				return null;
			}

			// If this tag closes nothing, discard it and try the next token.
			return self::parseNode($tokens, $parent, $ancestors);
		}

		$node = (new tagparser())->parse($token);
		if ($node->_isVoid()) {
			return $node;
		}

		// Autoclose <p> tags.
		if ($parent instanceof ElementNode) {
			if (strtolower($parent->tagName) == 'p' && $node->_isBlock()) {
				$tokens->unget($token);
				return null;
			}
		}

		// The node is a container, recurse.
		self::parseContents($tokens, $node, array_merge($ancestors, [$parent]));

		return $node;
	}
}
