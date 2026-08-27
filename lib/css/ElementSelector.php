<?php

namespace gaswelder\htmlparser\css;

use Exception;
use gaswelder\htmlparser\dom\ElementNode;

class ElementSelector
{
	public $tag = ''; // div
	public $class = '';
	public $id = '';
	public $attrs = [];
	public $pseudoclasses = []; // :empty

	/**
	 * Returns true if the given element matches this selector.
	 *
	 * @param ElementNode $child
	 * @return bool
	 */
	public function match(ElementNode $child)
	{
		$v = $this->tag;
		if ($v && strtolower($child->tagName()) != strtolower($v)) {
			return false;
		}

		$v = $this->class;
		if ($v && !in_array($v, $child->classList())) {
			return false;
		}

		$v = $this->id;
		if ($v && $child->getAttribute('id') != $v) {
			return false;
		}

		// All attribute specifiers must be satisfied.
		foreach ($this->attrs as $spec) {
			if (!$spec->match($child)) {
				return false;
			}
		}

		foreach ($this->pseudoclasses as $c) {
			switch ($c) {
				case "empty":
					if (count($child->childNodes()) > 0) {
						return false;
					}
					break;
				default:
					throw new Exception("unknown pseudo class: " . $c);
			}
		}

		return true;
	}

	function is_empty()
	{
		$a = array(
			$this->tag,
			$this->class,
			$this->id,
			$this->attrs
		);
		foreach ($a as $v) {
			if (!empty($v)) return false;
		}
		return true;
	}
}
