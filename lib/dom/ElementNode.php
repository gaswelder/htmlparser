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

	function format()
	{
		if ($this->value === true) {
			return $this->name;
		}
		return sprintf('%s="%s"', $this->name, htmlspecialchars($this->value));
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

	// function setInnerHTML($html)
	// {
	// 	if ($this->_isVoid()) {
	// 		throw new \Exception("Can't set inner HTML to a void node");
	// 	}
	// 	$doc = (new Parser)->parse($html);
	// 	$this->childNodes = $doc->childNodes;
	// }

	function innerHTML()
	{
		if ($this->_isVoid()) {
			return '';
		}
		$s = '';
		foreach ($this->childNodes as $node) {
			$s .= $node->format();
		}
		return $s;
	}

	function format()
	{
		$s = '<' . $this->tagName;
		foreach ($this->attributes as $attr) {
			$s .= ' ' . $attr->format();
		}
		if ($this->_isVoid()) {
			$s .= '>';
			return $s;
		}
		$s .= '>';
		foreach ($this->childNodes as $node) {
			$s .= $node->format();
		}
		$s .= '</' . $this->tagName . '>';
		return $s;
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
		return in_array(strtolower($this->tagName), self::$voidElements);
	}

	private static $blockElements = [
		'address',
		'article',
		'aside',
		'blockquote',
		'details',
		'dialog',
		'dd',
		'div',
		'dl',
		'dt',
		'fieldset',
		'figcaption',
		'figure',
		'footer',
		'form',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'header',
		'hgroup',
		'hr',
		'li',
		'main',
		'nav',
		'ol',
		'p',
		'pre',
		'section',
		'table',
		'ul',
	];

	function _isBlock()
	{
		return in_array(strtolower($this->tagName), self::$blockElements);
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
