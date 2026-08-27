<?php

namespace gaswelder\htmlparser\dom;

/**
 * Represents a single document.
 */
class DocumentNode extends ContainerNode
{
	private string $doctype;

	function __construct($doctype)
	{
		parent::__construct(Node::DOCUMENT_NODE);
		$this->doctype = $doctype;
	}

	function format()
	{
		$s = '';
		$prevBlock = false;
		foreach ($this->childNodes() as $node) {
			if ($s == '' && $node instanceof TextNode) {
				continue;
			}
			$isBlock = $node instanceof ElementNode && $node->_isBlock();
			if ($isBlock && $prevBlock) {
				$s .= "\n\n";
			}
			$s .= $node->format();
			$prevBlock = $isBlock;
		}
		// $s = preg_replace('/[ \t]+\n/', "\n", $s);
		// $s = preg_replace('/\n{3,}/', "\n\n", $s);
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
