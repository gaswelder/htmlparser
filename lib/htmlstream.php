<?php
namespace gaswelder\htmlp;

/*
 * First stage parser for HTML documents.
 */
class tokstream
{
	const spaces = "\r\n\t ";

	/*
	 * A parsebuf instance
	 */
	private $buf;

	/*
	 * Cache for tokens that have already been read.
	 */
	private $peek = array();

	/*
	 * First occurred error message
	 */
	private $err;

	function __construct($s)
	{
		$this->buf = new parsebuf($s);
	}

	/*
	 * Returns first occurred error message
	 */
	function err()
	{
		return $this->err;
	}

	/*
	 * Returns string describing current
	 * line and column position in the buffer
	 */
	function pos()
	{
		return $this->buf->pos();
	}

	/*
	 * Returns true if there are more tokens
	 * in the stream
	 */
	function more()
	{
		if ($this->err) return false;
		return $this->peek() !== null;
	}

	/*
	 * Returns next token without removing it from the stream.
	 * Returns null if there are no more tokens.
	 */
	function peek()
	{
		if ($this->err) return null;
		if (!empty($this->peek)) {
			return $this->peek[0];
		}
		return $this->read();
	}

	/*
	 * Returns next token and removes it from the stream.
	 * Returns null if there are no more tokens.
	 */
	function get()
	{
		if ($this->err) return false;
		if (!empty($this->peek)) {
			return array_shift($this->peek);
		}
		return $this->read();
	}

	/*
	 * Pushes a token back into the stream
	 */
	function unget(token $tok)
	{
		if ($this->err) return;
		array_unshift($this->peek, $tok);
	}

	/*
	 * Reads a token from the buffer
	 */
	private function read()
	{
		if (!$this->buf->more()) {
			return null;
		}

		$pos = $this->buf->pos();

		if ($this->buf->literal_follows('<!DOCTYPE')) {
			$t = $this->read_doctype();
		}
		else if ($this->buf->literal_follows('<!--')) {
			$t = $this->read_comment();
		}
		else if ($this->buf->peek() == '<') {
			$t = $this->read_tag();
			/*
			 * If this tag starts a container for another language
			 * (like JS or CSS), read the following contents without
			 * parsing.
			 */
			$cdata = array('script', 'style');
			preg_match('/<([a-zA-Z]+)[\s>]/', $t->content, $m);
			if (isset($m[1]) && in_array(strtolower($m[1]), $cdata)) {
				$this->read_cdata($m[1]);
			}
		}
		else {
			$t = $this->read_text();
		}

		if (!$t) return null;
		$t->pos = $pos;
		return $t;
	}

	private function read_cdata($name)
	{
		/*
		 * Read everything until the closing tag
		 */
		$close = "</$name>";
		$content = $this->buf->until_literal($close);
		if (!$content) return;
		/*
		 * If the data is not empty, cache a 'text' token
		 * in the peek buffer so that next call to 'get' will
		 * return it.
		 */
		array_unshift($this->peek, new token('text', $content));
	}

	private function read_doctype()
	{
		$b = $this->buf;

		$b->skip_literal("<!DOCTYPE");

		if (!$b->read_set(self::spaces)) {
			return $this->error("Missing space after <!DOCTYPE");
		}

		$type = $b->skip_until('>');
		if ($type != "html") {
			return $this->error("Unknown doctype: $type");
		}

		$b->read_set(self::spaces);
		if ($b->get() != '>') {
			return $this->error("Missing '>'");
		}

		return new token('doctype', 'html');
	}

	private function read_comment()
	{
		$this->buf->skip_literal("<!--");
		$s = '';
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			if ($ch == '-' && $this->buf->skip_literal('->')) {
				return new token('comment', $s);
			}
			$s .= $ch;
		}
		return $this->error("--> expected");
	}

	private function read_tag()
	{
		$s = $this->buf->get();
		assert($s == '<');
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			$s .= $ch;
			if ($ch == '>') {
				return new token('tag', $s);
			}
		}
		return $this->error("Missing '>'");
	}

	private function read_text()
	{
		$s = '';
		while ($this->buf->more()) {
			$ch = $this->buf->get();
			if ($ch == '<') {
				$this->buf->unget($ch);
				break;
			}

			if ($ch == '&') {
				$this->buf->unget($ch);
				$s .= $this->read_entity();
				continue;
			}

			$s .= $ch;
		}

		return new token('text', $s);
	}

	private function read_entity()
	{
		$s = $this->buf->get();

		while ($this->buf->more() && $this->buf->peek() != ';') {
			$s .= $this->buf->get();
		}
		$s .= $this->buf->get();

		return html_entity_decode($s);
	}

	/*
	 * Set error and return null.
	 */
	private function error($msg)
	{
		if (!$this->err) $this->err = $msg;
		return null;
	}
}

?>
