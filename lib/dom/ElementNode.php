<?php

namespace gaswelder\htmlparser\dom;

class Attr
{
	public string $name;
	public mixed $value;

	function __construct(string $k, mixed $v)
	{
		$this->name = $k;
		$this->value = $v;
	}
}

class ElementNode extends ContainerNode
{
	private string $tagName;

	/** @var Attr[] */
	private $attributes = [];

	function __construct(string $name)
	{
		parent::__construct(self::ELEMENT_NODE);
		$this->tagName = $name;
	}

	function tagName()
	{
		return $this->tagName;
	}

	function innerHTML()
	{
		$s = [];
		foreach ($this->childNodes() as $c) {
			$s[] = format($c);
		}
		return implode('', $s);
	}

	private function findAttr(string $name)
	{
		$namelow = strtolower($name);
		foreach ($this->attributes as $i => $attr) {
			if (strtolower($attr->name) == $namelow) {
				return $i;
			}
		}
		return -1;
	}

	function classList()
	{
		$v = $this->getAttribute("class");
		if ($v !== null) {
			return preg_split('/[ ]+/', $v);
		}
		return [];
	}

	function attributes()
	{
		$r = [];
		foreach ($this->attributes as $a) {
			$r[$a->name] = $a->value;
		}
		return $r;
	}

	function setAttribute(string $k, mixed $v)
	{
		$i = $this->findAttr($k);
		if ($i >= 0) {
			$this->attributes[$i]->value = $v;
		} else {
			$this->attributes[] = new Attr($k, $v);
		}
	}

	function getAttribute(string $k)
	{
		$i = $this->findAttr($k);
		if ($i < 0) {
			return null;
		}
		return $this->attributes[$i]->value;
	}

	function removeAttribute(string $k)
	{
		$i = $this->findAttr($k);
		if ($i >= 0) {
			array_splice($this->attributes, $i, 1);
		}
	}

	function __toString()
	{
		$s = '<' . $this->tagName;
		$id = $this->getAttribute('id');
		if ($id) {
			$s .= "#$id";
		}
		foreach ($this->classList() as $className) {
			$s .= ".$className";
		}
		$s .= '>';
		return $s;
	}
}
