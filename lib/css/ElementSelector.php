<?php

namespace gaswelder\htmlparser\css;

use gaswelder\htmlparser\dom\ElementNode;

class ElementSelector
{
	public $tag = '';
	public $class = '';
	public $id = '';

	// Attribute specifiers
	public $attrs = [];

	/**
	 * Returns true if the given element matches this selector.
	 *
	 * @param ElementNode $child
	 * @return bool
	 */
	public function match(ElementNode $child)
	{
		$v = $this->tag;
		if ($v && strtolower($child->tagName) != strtolower($v)) {
			return false;
		}

		$v = $this->class;
		if ($v && !in_array($v, $child->classList)) {
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
