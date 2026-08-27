<?php

namespace gaswelder\htmlparser\dom;

/**
 * A collection of DOM nodes.
 */
class NodeList implements \ArrayAccess, \Iterator, \Countable
{
	public int $length;
	private $items = [];
	private $cursor = 0;

	function __construct(array $items)
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

	function item(int $i): ElementNode|null
	{
		if (!isset($this->items[$i])) {
			return null;
		}
		return $this->items[$i];
	}

	function offsetGet($i): ElementNode|null
	{
		return $this->item($i);
	}

	function current(): ElementNode|null
	{
		return $this->items[$this->cursor];
	}

	function count(): int
	{
		return $this->length;
	}

	function offsetExists($i): bool
	{
		return isset($this->items[$i]);
	}

	function offsetSet($i, $v): void
	{
		trigger_error("Can't mess with collections");
	}

	function offsetUnset($i): void
	{
		trigger_error("Can't mess with collections");
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
