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
		$s = '<!DOCTYPE ' . $this->type . ">\n";
		foreach ($this->childNodes as $node) {
			$s .= $node->format() . "\n";
		}
		return $s;
	}
}
