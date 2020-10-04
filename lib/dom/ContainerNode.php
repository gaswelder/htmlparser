<?php

namespace gaswelder\htmlparser\dom;

use gaswelder\htmlparser\css\SelectorParser;

/**
 * A node that has children and all the related
 * methods for working with them.
 */
abstract class ContainerNode extends Node
{
	public $childNodes = array();
	/*
	 * Subset of childNodes which only has element nodes
	 */
	public $children = array();
	public $firstChild = null;

	function __construct()
	{
	}

	function appendChild($node)
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

	function getElementsByTagName($name)
	{
		return $this->querySelectorAll($name);
	}

	function getElementById($id)
	{
		foreach ($this->childNodes as $ch) {
			if ($ch->nodeType != $ch::ELEMENT_NODE) continue;
			if ($ch->getAttribute('id') == $id) {
				return $ch;
			}
			$ch = $ch->getElementById($id);
			if ($ch) return $ch;
		}
		return null;
	}

	/**
	 * Returns list of all elements matching the given CSS selector.
	 */
	function querySelectorAll(string $selectorString): NodeList
	{
		$selector = SelectorParser::parse($selectorString);
		return $selector->select($this);
	}

	/**
	 * Returns the first node matching the given selector or null.
	 *
	 * @param string $selector
	 * @return ElementNode|null
	 */
	function querySelector($selector)
	{
		$s = $this->querySelectorAll($selector);
		if (!empty($s)) return $s[0];
		return null;
	}
}
