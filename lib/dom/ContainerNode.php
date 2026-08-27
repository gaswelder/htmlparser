<?php

namespace gaswelder\htmlparser\dom;

use gaswelder\htmlparser\css\SelectorParser;

/**
 * A node that has children and all the related
 * methods for working with them.
 */
abstract class ContainerNode extends Node
{
	function __construct() {}

	function getElementsByTagName(string $name)
	{
		return $this->querySelectorAll($name);
	}

	function getElementById(string $id)
	{
		foreach ($this->childNodes as $ch) {
			if (!($ch instanceof ElementNode)) {
				continue;
			}
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
