<?php
namespace gaswelder\htmlparser\css;

use gaswelder\htmlparser\parsebuf;

const SPACES = " \t";
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
		$rels = array('>', '+');

		/*
		 * The parsed set will be an array of tokens of two types:
		 * element specifier and relation modifier.
		 * Relation modifier is just a single character like '>' or '+'.
		 * An element specifier is an array with keys described below.
		 */
		$set = array();

		$buf = new parsebuf(trim($s));
		while ($buf->more()) {
			/*
			 * If one of relation modifiers follows, read it
			 */
			if (in_array($buf->peek(), $rels)) {
				$set[] = $buf->get();
				$buf->read_set(SPACES);
				/*
				 * Make sure that an element specifier follows
				 */
				if (!$buf->more() || in_array($buf->peek(), $rels)) {
					trigger_error("Element specifier expected");
					return null;
				}
			}

			/*
			 * Read element specifier
			 */
			$spec = $this->readElementSpecifier($buf);
			if (!$spec) return null;
			if ($spec->is_empty()) {
				break;
			}

			$set[] = $spec;
			$buf->read_set(SPACES);
		}

		if ($buf->more()) {
			$ch = $buf->peek();
			trigger_error("Unexpected character '$ch' in $s");
			return null;
		}
		return new Selector($set);
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
