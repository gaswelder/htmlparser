<?php

namespace gaswelder\htmlparser\dom;

use gaswelder\htmlparser\css\SelectorParser;

/**
 * A node that has children and all the related
 * method for working with them.
 */
abstract class ContainerNode extends Node
{
	public $parentNode = null;
	public $childNodes = array();
	/*
	 * Subset of childNodes which only has element nodes
	 */
	public $children = array();
	public $firstChild = null;

	private $selectorsParser;

	function __construct()
	{
		$this->selectorsParser = new SelectorParser();
	}

	function appendChild($node)
	{
		$node->parentNode = $this;
		$this->childNodes[] = $node;
		if (!$this->firstChild) {
			$this->firstChild = $node;
		}
		if ($node->nodeType == $node::ELEMENT_NODE) {
			$this->children[] = $node;
		}
	}

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
			}
			else {
				$p->firstChild = null;
			}
		}

		$this->parentNode = null;
	}

	function getElementsByTagName($name)
	{
		$list = array();
		foreach ($this->childNodes as $ch) {
			if ($ch->tagName == $name) {
				$list[] = $ch;
			}
		}
		return new Collection($list);
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

	function querySelectorAll($sel)
	{
		$selector = $this->selectorsParser->parse($sel);
		return $selector->select([$this]);
	}

	function querySelector($sel)
	{
		$s = $this->querySelectorAll($sel);
		if (!empty($s)) return $s[0];
		return null;
	}

	function __toString()
	{
		return get_class($this);
	}
}
