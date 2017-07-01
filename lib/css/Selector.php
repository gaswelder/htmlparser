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
	 * @param ContainerNode $tree
	 * @return array
	 */
	function select(ContainerNode $tree)
	{
		$stage = [$tree];
		$combinator = self::DESCENDANT;
		$sequence = $this->sequence;

		while (!empty($sequence)) {
			// Get a selector
			$sel = array_shift($sequence);
			if (!($sel instanceof ElementSelector)) {
				throw new Exception("Selector expected in the sequence, got ''$sel'");
			}

			// Apply the selector and the combinator to the current result.
			$stage = $this->scan($stage, $combinator, $sel);

			// Read a combinator
			$combinator = array_shift($sequence);
			if (!$combinator) {
				break;
			}

			if (empty($sequence)) {
				throw new Exception("Unexpected end of sequence");
			}
		}

		return $stage;
	}

	private function scan($stage, $combinator, $selector)
	{
		$newStage = [];
		foreach ($stage as $tree) {
			$newStage = array_merge($newStage, $this->search($tree, $combinator, $selector));
		}
		return $newStage;
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
