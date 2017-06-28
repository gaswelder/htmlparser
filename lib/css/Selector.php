<?php

namespace gaswelder\htmlparser\css;

class Selector
{
	private $groups;

	public function __construct($groups)
	{
		$this->groups = $groups;
	}

	public function select($set)
	{
		/*
		 * In general case a single query specifies several groups,
		 * separated by commas (for example, "p, blockquote").
		 * A parsed selector is thus an array of such "groups", for each
		 * of which we do separate filtering and then merge the results
		 * into one set.
		 */
		$result = array();
		foreach ($this->groups as $group) {
			$result = array_merge($result, $this->select_subgroup($set, $group));
		}
		return $this->unique($result);
	}

	private function unique($set)
	{
		$u = array();
		foreach ($set as $obj) {
			if (!in_array($obj, $u, true)) {
				$u[] = $obj;
			}
		}
		return $u;
	}

	private function select_subgroup($set, $spec)
	{
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
			foreach ($set as $tree) {
				$results = array_merge($results, $this->search($tree, $rel, $tok));
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
