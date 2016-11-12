<?php

class html_node_proto
{
	const ELEMENT_NODE = 1;
	const TEXT_NODE = 3;
	const COMMENT_NODE = 8;

	public $nodeType;
}

class html_comment extends html_node_proto
{
	function __construct( $text ) {
		$this->nodeType = self::COMMENT_NODE;
	}
}

class html_text extends html_node_proto
{
	public $textContent;
	function __construct( $text ) {
		$this->textContent = $text;
		$this->nodeType = self::TEXT_NODE;
	}
}

class html_node extends html_node_proto
{
	public $parentNode = null;
	public $childNodes = array();
	public $firstChild = null;

	function appendChild( $node )
	{
		$node->parentNode = $this;
		$this->childNodes[] = $node;
		if( !$this->firstChild ) {
			$this->firstChild = $node;
		}
	}

	function remove() {
		if( !$this->parentNode ) return;
		/*
		 * Find the index at which this node is stored in the parent.
		 */
		$p = $this->parentNode;
		foreach( $p->childNodes as $i => $node ) {
			if( $node === $this ) {
				break;
			}
		}
		array_splice( $p->childNodes, $i, 1 );
		if( $i == 0 )
		{
			if( !empty( $p->childNodes ) ) {
				$p->firstChild = $p->childNodes[0];
			} else {
				$p->firstChild = null;
			}
		}
		$this->parentNode = null;
	}
	
	function getElementsByTagName( $name )
	{
		$list = array();
		foreach( $this->childNodes as $ch ) {
			if( $ch->tagName == $name ) {
				$list[] = $ch;
			}
		}
		return new html_collection( $list );
	}

	function __toString() {
		return get_class( $this );
	}
}

class html_doc extends html_node
{
	public $type;

	function __construct( $type = 'html' ) {
		$this->type = $type;
	}
}

class html_element extends html_node
{
	public $tagName;
	public $attrs = array();

	function __construct( $name ) {
		$this->tagName = $name;
		$this->nodeType = self::ELEMENT_NODE;
	}

	function setAttribute( $k, $v ) {
		$this->attrs[$k] = $v;
	}

	function getAttribute( $k ) {
		if( isset( $this->attrs[$k] ) ) {
			return $this->attrs[$k];
		}
		return null;
	}

	private static $singles = array(
		'hr', 'img', 'br', 'meta', 'link', 'input'
	);

	function is_single() {
		return in_array( $this->tagName, self::$singles );
	}

	function __toString() {
		return "html_element($this->tagName)";
	}
}

class html_collection implements ArrayAccess
{
	public $length;
	private $items = array();

	function __construct( $items ) {
		$this->items = $items;
	}

	function item( $i ) {
		if( !isset( $this->items[$i] ) ) {
			return null;
		}
		return $this->items[$i];
	}

	function offsetExists( $i ) {
		return isset( $this->items[$i] );
	}

	function offsetGet( $i ) {
		return $this->item( $i );
	}

	function offsetSet( $i, $v ) {
		trigger_error( "Can't mess with collections" );
	}

	function offsetUnset( $i ) {
		trigger_error( "Can't mess with collections" );
	}
}


?>
