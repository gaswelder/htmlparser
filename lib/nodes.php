<?php
class html_node_proto
{
	const ELEMENT_NODE = 1;
	const TEXT_NODE = 3;
	const COMMENT_NODE = 8;

	public $nodeType;
}

class html_comment extends html_node_proto
{
	function __construct($text)
	{
		$this->nodeType = self::COMMENT_NODE;
	}
}

class html_text extends html_node_proto
{
	public $textContent;
	function __construct($text)
	{
		$this->textContent = $text;
		$this->nodeType = self::TEXT_NODE;
	}
}

class html_node extends html_node_proto
{
	public $parentNode = null;
	public $childNodes = array();
	/*
	 * Subset of childNodes which only has element nodes
	 */
	public $children = array();
	public $firstChild = null;

	function appendChild($node)
	{
		$node->parentNode = $this;
		$this->childNodes[] = $node;
		if (!$this->firstChild) {
			$this->firstChild = $node;
		}
		if($node->nodeType == $node::ELEMENT_NODE) {
			$this->children[] = $node;
		}
	}

	function remove()
	{
		if (!$this->parentNode) return;

		$p = $this->parentNode;

		$pos = array_search($this, $p->childNodes, true);
		/*
		 * This element must be in the parent's childNodes
		 * list, but not necessarily in the children list.
		 */
		assert($pos !== false);
		array_splice($p->childNodes, $pos, 1);

		$pos = array_search($this, $p->children, true);
		if ($pos !== false) {
			array_splice($p->children, $pos, 1);
		}

		if ($p->firstChild == $this) {
			if (!empty($p->childNodes)) {
				$p->firstChild = $p->childNodes[0];
			}
			else {
				$p->firstChild = null;
			}
		}

		$this->parentNode = null;
	}

	function getElementsByTagName($name)
	{
		$list = array();
		foreach ($this->childNodes as $ch) {
			if ($ch->tagName == $name) {
				$list[] = $ch;
			}
		}
		return new html_collection($list);
	}

	function getElementById($id)
	{
		foreach ($this->childNodes as $ch) {
			if ($ch->nodeType != $ch::ELEMENT_NODE) continue;
			if ($ch->getAttribute('id') == $id) {
				return $ch;
			}
			$ch = $ch->getElementById($id);
			if ($ch) return $ch;
		}
		return null;
	}

	function __toString()
	{
		return get_class($this);
	}
}

class html_doc extends html_node
{
	public $type;

	function __construct($type = 'html')
	{
		$this->type = $type;
	}
}

class html_element extends html_node
{
	public $tagName;
	public $attrs = array();
	public $classList = array();

	function __construct($name)
	{
		$this->tagName = $name;
		$this->nodeType = self::ELEMENT_NODE;
	}

	function setAttribute($k, $v)
	{
		if($k == 'class') {
			$this->classList = preg_split('/[ ]+/', $v);
		}
		$this->attrs[$k] = $v;
	}

	function getAttribute($k)
	{
		if (isset($this->attrs[$k])) {
			return $this->attrs[$k];
		}
		return null;
	}

	private static $singles = array(
		'hr',
		'img',
		'br',
		'meta',
		'link',
		'input'
	);

	function is_single()
	{
		return in_array($this->tagName, self::$singles);
	}

	function __toString()
	{
		return "html_element($this->tagName)";
	}
}

class html_collection implements ArrayAccess
{
	public $length;
	private $items = array();

	function __construct($items)
	{
		$this->items = $items;
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
}

?>
