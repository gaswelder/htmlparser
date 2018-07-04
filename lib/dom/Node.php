<?php
namespace gaswelder\htmlparser\dom;

/**
 * Base class for all DOM nodes.
 * Provides common constants and the nodeType field.
 */
abstract class Node
{
	const ELEMENT_NODE = 1;
	const TEXT_NODE = 3;
	const COMMENT_NODE = 8;
	const DOCUMENT_NODE = 9;

	/**
	 * Type of the node. One of the Node:: constants.
	 *
	 * @var int
	 */
	public $nodeType;

	public $parentNode = null;

	/**
	 * Returns the node following this one in the parent.
	 * Returns null if this node is the last node.
	 */
	function nextSibling()
	{
		$p = $this->parentNode;
		$pos = array_search($this, $p->childNodes, true);
		if ($pos + 1 == count($p->childNodes)) return null;
		return $p->childNodes[$pos + 1];
	}

	function __toString()
	{
		return "#node(type=$this->nodeType)";
	}

	function format()
	{
		trigger_error("format() not implemented for node type " . $this->nodeType);
	}
}
