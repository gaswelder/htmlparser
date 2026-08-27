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
	public int $nodeType;

	public Node|null $parentNode = null;

	/** @var Node[] */
	public array $childNodes = [];

	/**
	 * Subset of childNodes which only has element nodes.
	 *
	 * @var Node[]
	 */
	public array $children = [];

	public Node|null $firstChild = null;

	function appendChild(Node $node)
	{
		$node->remove();
		$node->parentNode = $this;
		$this->childNodes[] = $node;
		if (!$this->firstChild) {
			$this->firstChild = $node;
		}
		if ($node->nodeType == $node::ELEMENT_NODE) {
			$this->children[] = $node;
		}
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

		if ($p->firstChild === $this) {
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
		return "";
	}
}
