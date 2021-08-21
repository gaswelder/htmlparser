<?php

use gaswelder\htmlparser\dom\ElementNode;
use gaswelder\htmlparser\dom\DocumentNode;

class HTMLWriter
{
	function write(DocumentNode $doc)
	{
		$s = '';
		foreach ($doc->childNodes as $node) {
			$s .= $this->writeNode($node);
		}
		return $s;
	}

	private function writeNode($node)
	{
		switch ($node->nodeType) {
			case 1:
				return $this->element($node);
			case 3:
				return $node->textContent;
			case 8:
				break;
			default:
				throw new Exception("Unknown nodeType: $node->nodeType");
		}
	}

	private function element(ElementNode $node)
	{
		$s = $this->tag($node);
		if ($node->_isVoid()) {
			return $s;
		}
		foreach ($node->childNodes as $c) {
			$s .= $this->writeNode($c);
		}
		$s .= "</$node->tagName>";
		return $s;
	}

	private function tag(ElementNode $node)
	{
		$s = "<$node->tagName";
		foreach ($node->attributes as $attr) {
			$s .= sprintf(' %s="%s"', $attr->name, $attr->value);
		}
		$s .= '>';
		return $s;
	}
}
