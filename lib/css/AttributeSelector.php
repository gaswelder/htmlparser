<?php

namespace gaswelder\htmlparser\css;

use gaswelder\htmlparser\dom\ElementNode;
use gaswelder\htmlparser\parsebuf;

class AttributeSelector
{
	const has = '';
	const equal = '=';
	const startsWith = '^=';
	const endsWith = '$=';
	// '~='
	// '|='
	// '*='
	private $attributeName;
	private $op;
	private $expectedValue;

	function match(ElementNode $node)
	{
		$val = $node->getAttribute($this->attributeName);
		if ($val === null) return false;

		switch ($this->op) {
			case self::has:
				return true;
			case self::equal:
				return $val === $this->expectedValue;
			case self::startsWith:
				return strpos($val, $this->expectedValue) === 0;
			case self::endsWith:
				return substr($val, -strlen($this->expectedValue)) === $this->expectedValue;
			default:
				trigger_error("Unknown attribute selector mode: $this->op");
				return false;
		}
	}

	static function parse(parsebuf $s)
	{
		$s->expect('[');

		$selector = new self;
		$selector->attributeName = $s->read_set(IDCHARS);
		if (!$selector->attributeName) {
			return $s->error("Attribute name expected");
		}
		if ($s->peek() == ']') {
			$s->get();
			$selector->op = self::has;
			return $selector;
		}

		// Read the attribute operator.
		$ops = [
			self::endsWith,
			self::equal,
			self::startsWith
		];
		foreach ($ops as $op) {
			if ($s->skip_literal($op)) {
				$selector->op = $op;
				break;
			}
		}
		if (!$selector->op) {
			return $s->error("']' or attribute operator expected");
		}

		// Read the value in double quotes.
		$s->expect('"');
		$selector->expectedValue = $s->skip_until('"');
		$s->expect('"');

		// Read the closing bracket.
		$s->expect(']');
		return $selector;
	}
}
