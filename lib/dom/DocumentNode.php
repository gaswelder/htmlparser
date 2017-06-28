<?php

namespace gaswelder\htmlparser\dom;

class DocumentNode extends ContainerNode
{
	public $type;

	function __construct($type = 'html')
	{
		parent::__construct();
		$this->type = $type;
	}
}
