<?php

namespace gaswelder\htmlparser\dom;

class CommentNode extends Node
{
	function __construct($text)
	{
		parent::__construct(self::COMMENT_NODE);
	}
}
