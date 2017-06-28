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
}
