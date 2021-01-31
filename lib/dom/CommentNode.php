<?php

namespace gaswelder\htmlparser\dom;

class CommentNode extends Node
{
	function __construct($text)
	{
		$this->nodeType = self::COMMENT_NODE;
	}

	function format()
	{
		return '';
	}
}
