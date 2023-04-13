<?php

namespace gaswelder\htmlparser\dom;

/**
 * Collection of DOM nodes.
 */
class NodeList implements \ArrayAccess, \Iterator, \Countable
{
	public $length;
	private $items = array();
	private $cursor = 0;

	function __construct($items)
	{
		$this->items = $items;
		$this->length = count($items);
	}

	function __toString()
	{
		$nodes = [];
		foreach ($this->items as $item) {
			$nodes[] = $item->__toString();
		}
		return 'NodeList [ ' . implode(', ', $nodes) . ' ]';
	}

	function item($i)
	{
		if (!isset($this->items[$i])) {
			return null;
		}
		return $this->items[$i];
	}

	function count(): int
	{
		return $this->length;
	}

	function offsetExists($i): bool
	{
		return isset($this->items[$i]);
	}

	function offsetGet($i): mixed
	{
		return $this->item($i);
	}

	function offsetSet(mixed $i, mixed $v): void
	{
		trigger_error("Can't mess with collections");
	}

	function offsetUnset(mixed $i): void
	{
		trigger_error("Can't mess with collections");
	}

	function current(): mixed
	{
		return $this->items[$this->cursor];
	}

	function key(): mixed
	{
		return $this->cursor;
	}

	function next(): void
	{
		$this->cursor++;
	}

	function rewind(): void
	{
		$this->cursor = 0;
	}

	function valid(): bool
	{
		return isset($this->items[$this->cursor]);
	}
}
