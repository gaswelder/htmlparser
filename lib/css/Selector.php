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
		if (empty($sequence)) {
			throw new Exception("Empty selector");
		}
		$this->sequence = $sequence;
	}

	/**
	 * Scans the given tree and returns the matches.
	 *
	 * @param ContainerNode $node
	 * @return array
	 */
	function select(ContainerNode $node)
	{
		// The sequence may start as "> foo" or just "foo".
		// In the latter case we prepend the implied ' ' (descendant) combinator.
		$sequence = $this->sequence;
		if ($sequence[0] instanceof ElementSelector) {
			array_unshift($sequence, self::DESCENDANT);
		}
		return filter([$node], $sequence);
	}
}

// Takes a list of subtrees and returns the elements that match the selector sequence.
function filter(array $nodes, array $sequence): array
{
	if (count($sequence) === 0) {
		return $nodes;
	}
	if (count($sequence) === 1) {
		throw new Exception("got one item in selector sequence, expecting combinator + spec");
	}
	[$combinator, $spec] = $sequence;
	$rest = array_slice($sequence, 2);

	$newStage = [];
	foreach ($nodes as $node) {
		$newStage = array_merge($newStage, search($node, $combinator, $spec));
	}
	return filter($newStage, $rest);
}

function search(ContainerNode $node, $combinator, $spec): array
{
	$matches = [];
	if ($combinator == Selector::ADJACENT_SIBLING) {
		$next = $node->nextElementSibling;
		if ($next && $spec->match($next)) {
			$matches[] = $next;
		}
	} else if ($combinator == Selector::CHILD) {
		foreach ($node->children as $child) {
			if ($spec->match($child)) {
				$matches[] = $child;
			}
		}
	} else if ($combinator == Selector::DESCENDANT) {
		foreach ($node->children as $child) {
			if ($spec->match($child)) {
				$matches[] = $child;
			}
			$matches = array_merge($matches, search($child, $combinator, $spec));
		}
	} else {
		throw new Exception("Unknown combinator: '$combinator'");
	}
	return $matches;
}
