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

	function __toString()
	{
		return "#node(type=$this->nodeType)";
	}

	function format()
	{
		trigger_error("format() not implemented for node type " . $this->nodeType);
	}
}
