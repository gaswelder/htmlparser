<?php
namespace gaswelder\htmlparser;

class token
{
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

?>
