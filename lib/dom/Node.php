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
	 * Removes this node from the parent node's children.
	 */
	function remove()
	{
		if (!$this->parentNode) return;

		$p = $this->parentNode;

		$pos = array_search($this, $p->childNodes, true);
		/*
		 * This element must be in the parent's childNodes
		 * list, but not necessarily in the children list.
		 */
		assert($pos !== false);
		array_splice($p->childNodes, $pos, 1);

		$pos = array_search($this, $p->children, true);
		if ($pos !== false) {
			array_splice($p->children, $pos, 1);
		}

		if ($p->firstChild == $this) {
			if (!empty($p->childNodes)) {
				$p->firstChild = $p->childNodes[0];
			} else {
				$p->firstChild = null;
			}
		}

		$this->parentNode = null;
	}

	/**
	 * Returns the node following this one in the parent.
	 * Returns null if this node is the last node.
	 */
	function nextSibling(): ?Node
	{
		$p = $this->parentNode;
		$pos = array_search($this, $p->childNodes, true);
		if ($pos + 1 == count($p->childNodes)) return null;
		return $p->childNodes[$pos + 1];
	}

	function nextElementSibling(): ?ElementNode
	{
		$node = $this->nextSibling();
		while ($node && !($node instanceof ElementNode)) {
			$node = $node->nextSibling();
		}
		return $node;
	}

	/**
	 * Returns the node immediately preceding this node in its parent's childNodes list.
	 * Returns null if this node is the first child.
	 */
	function previousSibling(): ?Node
	{
		$p = $this->parentNode;
		$pos = array_search($this, $p->childNodes, true);
		if ($pos == 0) return null;
		return $p->childNodes[$pos - 1];
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
