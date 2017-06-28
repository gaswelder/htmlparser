<?php

namespace gaswelder\htmlparser\dom;

class ElementNode extends ContainerNode
{
	public $tagName;
	public $attrs = array();
	public $classList = array();

	function __construct($name)
	{
		parent::__construct();
		$this->tagName = $name;
		$this->nodeType = self::ELEMENT_NODE;
	}

	function setAttribute($k, $v)
	{
		if ($k == 'class') {
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
		return "ElementNode($this->tagName)";
	}
}
