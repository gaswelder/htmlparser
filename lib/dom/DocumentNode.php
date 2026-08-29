<?php

namespace gaswelder\htmlparser\dom;

/**
 * Represents a single document.
 */
class DocumentNode extends ContainerNode
{
	private string $doctype;

	function __construct(string $doctype)
	{
		parent::__construct(Node::DOCUMENT_NODE);
		$this->doctype = $doctype;
	}

	function doctype()
	{
		return $this->doctype;
	}

	/**
	 * Creates a new HTML element with the given tag name.
	 *
	 * @param string $tagName
	 * @return ElementNode
	 */
	function createElement($tagName)
	{
		return new ElementNode($tagName);
	}

	/**
	 * Creates a new text node with the given text.
	 *
	 * @param string $text
	 * @return TextNode
	 */
	function createTextNode($text)
	{
		return new TextNode($text);
	}

	function __toString()
	{
		return 'document node';
	}
}
