<?php

namespace gaswelder\htmlparser\css;

use gaswelder\htmlparser\dom\ElementNode;

class ElementSelector
{
	public $tag = '';
	public $class = '';
	public $id = '';
	public $attrs = array();

	/**
	 * Returns true if the given element matches this selector.
	 *
	 * @param ElementNode $child
	 * @return bool
	 */
	public function match(ElementNode $child)
	{
		$v = $this->tag;
		if ($v && $child->tagName != $v) {
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

		$v = $this->attrs;
		foreach ($v as $name => $val) {
			$chval = $child->getAttribute($name);
			/*
			 * If the node doesn't have it, return false.
			 */
			if ($chval === null) return false;
			/*
			 * If the selector specifies a concrete value and
			 * it doesn't match, return false.
			 */
			if ($val !== null && $val !== $chval) {
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
