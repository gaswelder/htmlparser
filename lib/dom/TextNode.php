<?php

namespace gaswelder\htmlparser\dom;

class TextNode extends Node
{
	private string $textContent;

	function __construct(string $text)
	{
		parent::__construct(self::TEXT_NODE);
		$this->textContent = $text;
	}

	function textContent()
	{
		return $this->textContent;
	}

	function __toString()
	{
		if (strlen($this->textContent) > 60) {
			$s = substr($this->textContent, 0, 57) . '...';
		} else {
			$s = $this->textContent;
		}
		return "#text \"$s\"";
	}
}
