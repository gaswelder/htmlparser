<?php

namespace gaswelder\htmlparser\dom;

/**
 * Collection of DOM nodes.
 */
class NodeList implements \ArrayAccess, \Iterator
{
	public $length;
	private $items = array();
	private $cursor = 0;

	function __construct($items)
	{
		$this->items = $items;
		$this->length = count($items);
	}

	function item($i)
	{
		if (!isset($this->items[$i])) {
			return null;
		}
		return $this->items[$i];
	}

	function offsetExists($i)
	{
		return isset($this->items[$i]);
	}

	function offsetGet($i)
	{
		return $this->item($i);
	}

	function offsetSet($i, $v)
	{
		trigger_error("Can't mess with collections");
	}

	function offsetUnset($i)
	{
		trigger_error("Can't mess with collections");
	}

	function current() {
		return $this->items[$this->cursor];
	}

	function key() {
		return $this->cursor;
	}

	function next() {
		$this->cursor++;
	}

	function rewind() {
		$this->cursor = 0;
	}

	function valid() {
		return isset($this->items[$this->cursor]);
	}
}
