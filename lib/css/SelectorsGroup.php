<?php

namespace gaswelder\htmlparser\css;

use gaswelder\htmlparser\dom\NodeList;
use gaswelder\htmlparser\dom\ContainerNode;

/**
 * Represents union of multiple selectors, such as "h1, h2, h3".
 */
class SelectorsGroup
{
	private $selectors;

	/**
	 * @param array $selectors
	 */
	function __construct($selectors)
	{
		$this->selectors = $selectors;
	}

	/**
	 * Scans the given tree and returns matches.
	 *
	 * @param ContainerNode $tree
	 * @return NodeList
	 */
	function select(ContainerNode $tree)
	{
		$results = [];
		foreach ($this->selectors as $selector) {
			$results[] = $selector->select($tree);
		}
		if (!empty($results)) {
			$results = $this->unique($results);
		}

		return new NodeList($results);
	}

	private function unique($results)
	{
		$set = call_user_func_array('array_merge', $results);
		$u = array();
		foreach ($set as $obj) {
			if (!in_array($obj, $u, true)) {
				$u[] = $obj;
			}
		}
		return $u;
	}
}
