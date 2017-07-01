<?php

namespace gaswelder\htmlparser\dom;

class TextNode extends Node
{
	public $textContent;

	function __construct($text)
	{
		$this->textContent = $text;
		$this->nodeType = self::TEXT_NODE;
	}

	function __toString()
	{
		if (mb_strlen($this->textContent) > 60) {
			$s = mb_substr($this->textContent, 0, 57).'...';
		} else {
			$s = $this->textContent;
		}
		return "#text \"$s\"";
	}
}
