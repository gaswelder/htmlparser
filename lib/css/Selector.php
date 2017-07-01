<?php

namespace gaswelder\htmlparser\css;
use Exception;
use gaswelder\htmlparser\dom\ContainerNode;

class Selector
{
	/*
	 * Recognized selector combinators
	 */
	const DESCENDANT = ' ';
	const CHILD = '>';
	const ADJACENT_SIBLING = '+';

	/**
	 * @var array
	 */
	private $sequence;

	/**
	 * @param array $sequence Sequence of ElementsSelectors separated by "combinators".
	 */
	public function __construct($sequence)
	{
		$this->sequence = $sequence;
	}

	/**
	 * Scans the given tree and returns the matches.
	 *
	 * @param ContainerNode $tree
	 * @return array
	 */
	function select(ContainerNode $tree)
	{
		$spec = $this->sequence;
		$results = array();

		while (!empty($spec)) {
			/*
			 * Find out how to search and what to search.
			 */
			$rel = self::DESCENDANT;

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
	private function search($tree, $combinator, $spec)
	{
		$match = array();

		if ($combinator == self::ADJACENT_SIBLING) {
			if ($spec->match($tree->nextSibling)) {
				$match[] = $tree->nextSibling;
			}
		}
		else if ($combinator == self::CHILD) {
			foreach ($tree->children as $child) {
				if ($spec->match($child)) {
					$match[] = $child;
				}
			}
		}
		else if ($combinator == self::DESCENDANT) {
			foreach ($tree->children as $child) {
				if ($spec->match($child)) {
					$match[] = $child;
				}
				$match = array_merge($match, $this->search($child, $combinator, $spec));
			}
		}
		else {
			throw new Exception("Unknown combinator: '$combinator'");
		}
		return $match;
	}
}
