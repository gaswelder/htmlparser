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
	 *
	 * @param string $selectorString
	 * @return NodeList
	 */
	function querySelectorAll($selectorString)
	{
		$selector = $this->selectorsParser->parse($selectorString);
		return new NodeList($selector->select($this));
	}

	function querySelector($sel)
	{
		$s = $this->querySelectorAll($sel);
		if (!empty($s)) return $s[0];
		return null;
	}
}
