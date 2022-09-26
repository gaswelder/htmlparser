<?php

namespace gaswelder\htmlparser;

class token
{
	/*
	 * Possible token types
	 */
	const COMMENT = 'comment';
	const DOCTYPE = 'doctype';
	const TAG = 'tag';
	const TEXT = 'text';

	const XML_DECLARATION = 'xml_declaration';

	public $type;
	public $content;
	public $pos;

	function __construct($type, $content = null)
	{
		$this->type = $type;
		$this->content = $content;
	}

	function __toString()
	{
		if ($this->content === null) {
			return '[' . $this->type . ']';
		}
		if ($this->type == token::TAG) {
			$content = $this->content[0] . " ...";
		} else {
			$content = $this->content;
		}

		$n = 40;
		if (mb_strlen($content) > $n) {
			$c = mb_substr($content, 0, $n - 3) . '...';
		} else $c = $content;
		$c = str_replace(array("\r", "\n", "\t"), array(
			"\\r",
			"\\n",
			"\\t"
		), $c);
		return "[$this->type, $c]";
	}
}
