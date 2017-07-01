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
	 *
	 * @param string $s
	 * @return SelectorsGroup
	 */
	public function parse($s)
	{
		$selectors = [];
		$sets = array_map('trim', explode(',', $s));
		foreach ($sets as $set) {
			$selectors[] = $this->parseSet($set);
		}
		return new SelectorsGroup($selectors);
	}

	/**
	 * Parses a "set specifier", which is almost the same as selector,
	 * but only in single case, without commas.
	 * <set>: <elem> [<rel>] <elem> [<rel>] ...
	 * example: #myid > div ul.myclass
	 *
	 * @param string $s
	 * @return Selector
	 */
	private function parseSet($s)
	{
		$s = trim($s);
		if ($s === '') {
			throw new Exception('Empty selector string');
		}
		$buf = new parsebuf($s);

		$sequence = [];
		$sequence[] = $this->readElementSpecifier($buf);
		while ($buf->more()) {
			$sequence[] = $this->readCombinator($buf);
			$sequence[] = $this->readElementSpecifier($buf);
		}
		return new Selector($sequence);
	}

	private function readCombinator(parsebuf $buf)
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
	private function readElementSpecifier($buf)
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

		while ($s->peek() == '[') {
			$s->get();

			$attr = $s->read_set(IDCHARS);
			$val = null;

			if ($s->peek() == '=') {
				$s->get();
				if ($s->get() != '"') {
					trigger_error("double quotes expected around attribute value");
					return null;
				}

				$val = $s->skip_until('"');
				if ($s->get() != '"') {
					trigger_error("missing closing double quote");
					return null;
				}
			}

			if ($s->get() != ']') {
				trigger_error("']' expected");
				return null;
			}

			$spec->attrs[$attr] = $val;
		}

		return $spec;
	}
}

?>
