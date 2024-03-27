<?php

namespace gaswelder\htmlparser\dom;

/**
 * Represents a single document.
 */
class DocumentNode extends ContainerNode
{
	public $type;

	function __construct($type = 'html')
	{
		parent::__construct();
		$this->type = $type;
		$this->nodeType = Node::DOCUMENT_NODE;
	}

	function format()
	{
		$s = '';
		foreach ($this->childNodes as $node) {
			$s .= $node->format() . "\n";
		}
		$s = preg_replace('/[ \t]+\n/', "\n", $s);
		$s = preg_replace('/\n{3,}/', "\n\n", $s);
		return $s;
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
