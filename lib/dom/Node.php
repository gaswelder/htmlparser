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
	 * One of the Node:: constants.
	 */
	private int $nodeType;

	private Node|null $parentNode = null;

	/** @var Node[] */
	private array $childNodes = [];

	function __construct(int $type)
	{
		$this->nodeType = $type;
	}

	function nodeType()
	{
		return $this->nodeType;
	}

	function childNodes()
	{
		return $this->childNodes;
	}

	function parentNode()
	{
		return $this->parentNode;
	}

	function firstChild()
	{
		if (count($this->childNodes()) == 0) {
			return null;
		}
		return $this->childNodes[0];
	}

	function children()
	{
		$r = [];
		foreach ($this->childNodes as $c) {
			if ($c instanceof ElementNode) {
				$r[] = $c;
			}
		}
		return $r;
	}

	function appendChild(Node $node)
	{
		$node->remove();
		$node->parentNode = $this;
		$this->childNodes[] = $node;
	}

	/**
	 * Inserts 'newNode' before the 'beforeNode' node which is a child of this node.
	 * Returns the 'newNode'.
	 *
	 * @param Node $newNode
	 * @param Node $beforeNode
	 * @return Node
	 */
	function insertBefore($newNode, $beforeNode)
	{
		$pos = array_search($beforeNode, $this->childNodes, true);
		if ($pos < 0) {
			trigger_error("The 'before' not is not a child of the current node");
			return;
		}
		$newNode->remove();
		array_splice($this->childNodes, $pos, 0, [$newNode]);
		$newNode->parentNode = $this;
		return $newNode;
	}

	function lastChild()
	{
		$n = count($this->childNodes);
		if ($n == 0) return null;
		return $this->childNodes[$n - 1];
	}

	/**
	 * Removes this node from its parent.
	 */
	function remove()
	{
		$p = $this->parentNode;
		if (!$p) {
			return;
		}
		$pos = array_search($this, $p->childNodes, true);
		assert($pos !== false);
		array_splice($p->childNodes, $pos, 1);
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
}
