<?php

namespace gaswelder\htmlparser\css;

use Exception;
use gaswelder\htmlparser\parsebuf;

const IDCHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890-_";

/**
 * CSS parser, a runnable with the parse method.
 */
class SelectorParser
{
	/**
	 * Parses a complete selectors group.
	 *
	 * A selectors group is a comma-separated sequence of selectors:
	 * <group>: <selector>, <selector>, ...
	 */
	static function parse(string $s): SelectorsGroup
	{
		$selectors = [];
		$sets = array_map('trim', explode(',', $s));
		foreach ($sets as $set) {
			$selectors[] = self::parseSet($set);
		}
		return new SelectorsGroup($selectors);
	}

	/**
	 * Parses a "set specifier", which is almost the same as selector,
	 * but only in single case, without commas.
	 *
	 * <set>: <elem> [<rel>] <elem> [<rel>] ...
	 * example: #myid > div ul.myclass
	 */
	private static function parseSet(string $s): Selector
	{
		$s = trim($s);
		if ($s === '') {
			throw new Exception('Empty selector string');
		}
		$buf = new parsebuf($s);

		$sequence = [];
		$sequence[] = self::readElementSpecifier($buf);
		while ($buf->more()) {
			$sequence[] = self::readCombinator($buf);
			$sequence[] = self::readElementSpecifier($buf);
		}
		return new Selector($sequence);
	}

	private static function readCombinator(parsebuf $buf): string
	{
		$combinators = [
			Selector::DESCENDANT,
			Selector::CHILD,
			Selector::ADJACENT_SIBLING
		];

		// We may have "a b", "a>b", and also "a > b";
		// In the third case the spaces are just token separators,
		// while in the first case the space is also the combinator.
		$spaces = $buf->read_set(' ');
		if ($spaces === '') {
			$c = $buf->get();
		} else {
			$c = $buf->peek();
			if (in_array($c, $combinators)) {
				$c = $buf->get();
			} else {
				$c = Selector::DESCENDANT;
			}
		}
		$buf->read_set(' ');

		if (!in_array($c, $combinators)) {
			throw new Exception("Selector combinator expected, got '$c'");
		}
		return $c;
	}

	/*
	 * Reads an "element specifier".
	 * <elem>: [<tagname>] ["." <classname>] ["#" <id>]
	 * 	[ "[" <attrname> ["=" <attvalue> ] "]" ]
	 * example: ul.funk[type="disc"]
	 */
	private static function readElementSpecifier(parsebuf $buf): ElementSelector
	{
		$spec = new ElementSelector();

		$s = $buf;

		// <tagname>?
		if (ctype_alpha($s->peek()) || $s->peek() == '-' || $s->peek() == '_') {
			$spec->tag = $s->read_set(IDCHARS);
		}

		// "." <classname>?
		if ($s->peek() == '.') {
			$s->get();
			$spec->class = $s->read_set(IDCHARS);
		}

		// "#" <id>?
		if ($s->peek() == '#') {
			$s->get();
			$spec->id = $s->read_set(IDCHARS);
		}

		// Attribute selectors.
		while ($s->peek() == '[') {
			$spec->attrs[] = AttributeSelector::parse($s);
		}

		return $spec;
	}
}
