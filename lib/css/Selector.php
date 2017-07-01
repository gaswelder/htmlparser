<?php

namespace gaswelder\htmlparser\css;
use gaswelder\htmlparser\dom\ContainerNode;

class Selector
{
	private $parts;

	public function __construct($parts)
	{
		$this->parts = $parts;
	}

	/**
	 * Scans the given tree and returns the matches.
	 *
	 * @param ContainerNode $tree
	 * @return array
	 */
	function select(ContainerNode $tree)
	{
		$spec = $this->parts;
		$results = array();

		while (!empty($spec)) {
			/*
			 * Find out how to search and what to search.
			 */
			$rel = ' ';

			/*
			 * If a modifier follows, get it and get the next
			 * token which this time will definitely be an element
			 * specifier.
			 */
			$tok = array_shift($spec);
			if (is_string($tok)) {
				$rel = $tok;
				$tok = array_shift($spec);
			}
			assert($tok instanceof ElementSelector);

			/*
			 * By default (rel=' ') we search the whole subtrees.
			 * Modifiers like '>' and '+' limit the search to immediate
			 * children or next siblings.
			 */
			foreach ($tree->children as $node) {
				$results = array_merge($results, $this->search($node, $rel, $tok));
			}
		}
		return $results;
	}

	/*
	 * Search given tree for element specified by $spec
	 * using method $rel ('>', '+', ' ').
	 */
	private function search($tree, $rel, $spec)
	{
		$match = array();

		if ($rel == '+') {
			if ($spec->match($tree->nextSibling)) {
				$match[] = $tree->nextSibling;
			}
		}
		else if ($rel == '>') {
			foreach ($tree->children as $child) {
				if ($spec->match($child)) {
					$match[] = $child;
				}
			}
		}
		else if ($rel == ' ') {
			foreach ($tree->children as $child) {
				if ($spec->match($child)) {
					$match[] = $child;
				}
				$match = array_merge($match, $this->search($child, $rel, $spec));
			}
		}
		else {
			trigger_error("Unknown search modifier: '$rel'");
		}
		return $match;
	}
}
