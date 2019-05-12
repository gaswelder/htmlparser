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

	/**
	 * Returns true if this token is a closing tag.
	 *
	 * @param string $name (optional) specifies the name of the tag
	 * @return bool
	 */
	function isClosingTag($name = null)
	{
		if ($this->type != self::TAG) {
			return false;
		}

		if (substr($this->content, 0, 2) != '</' || substr($this->content, -1) != '>') {
			return false;
		}

		if ($name === null) {
			return true;
		}

		return strtolower(trim(substr($this->content, 2, -1))) == strtolower($name);
	}

	function __toString()
	{
		if ($this->content === null) {
			return '['.$this->type.']';
		}

		$n = 40;
		if (mb_strlen($this->content) > $n) {
			$c = mb_substr($this->content, 0, $n-3).'...';
		}
		else $c = $this->content;
		$c = str_replace(array("\r", "\n", "\t"), array(
			"\\r",
			"\\n",
			"\\t"
		), $c);
		return "[$this->type, $c]";
	}
}
