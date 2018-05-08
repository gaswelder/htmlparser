<?php

namespace gaswelder\htmlparser\dom;

class Attr
{
	public $name;
	public $value;

	function __construct($k, $v)
	{
		$this->name = $k;
		$this->value = $v;
	}
}

class ElementNode extends ContainerNode
{
	public $tagName;
	public $attributes = [];
	public $classList = array();

	function __construct($name)
	{
		parent::__construct();
		$this->tagName = $name;
		$this->nodeType = self::ELEMENT_NODE;
	}

	private function findAttr($name)
	{
		foreach ($this->attributes as $i => $attr) {
			if ($attr->name == $name) {
				return $i;
			}
		}
		return -1;
	}

	function setAttribute($k, $v)
	{
		if ($k == 'class') {
			$this->classList = preg_split('/[ ]+/', $v);
		}
		$i = $this->findAttr($k);
		if ($i < 0) {
			$i = count($this->attributes);
			$this->attributes[] = new Attr($k, $v);
		} else {
			$this->attributes[$i]->value = $v;
		}
	}

	function getAttribute($k)
	{
		$i = $this->findAttr($k);
		if ($i < 0) {
			return null;
		}
		return $this->attributes[$i]->value;
	}

	private static $voidElements = [
		'area',
		'base',
		'br',
		'col',
		'embed',
		'hr',
		'img',
		'input',
		'keygen',
		'link',
		'menuitem',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
	];

	/**
	 * Returns true if this element is a "void" element like <br> or <img>.
	 *
	 * @return bool
	 */
	function _isVoid()
	{
		return in_array($this->tagName, self::$voidElements);
	}

	function __toString()
	{
		$s = '<' . $this->tagName;
		$id = $this->getAttribute('id');
		if ($id) {
			$s .= "#$id";
		}
		foreach ($this->classList as $className) {
			$s .= ".$className";
		}
		$s .= '>';
		return $s;
	}
}
