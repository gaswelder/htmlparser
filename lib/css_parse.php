<?php
namespace htmlp;

// spec: foo.bar#id:first-child
// tagname: foo
// classname: bar
// id: id
// ext: first-child
const SPACES = " \t";
const IDCHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890-_";

// selector: <set>, <set>, ...
function parse_selector($s)
{
	$selector = array();
	$sets = array_map('trim', explode(',', $s));
	foreach ($sets as $set) {
		$selector[] = parse_set($set);
	}
	return $selector;
}

// selector: <elem> [<rel>] <elem> [<rel>] ...
// example: #myid > div ul.myclass
function parse_set($s)
{
	$rels = array('>', '+');

	$set = array();

	$buf = new parsebuf($s);
	$buf->read_set(SPACES);
	while ($buf->more()) {
		/*
		 * If on of relation modifiers follows, read it
		 */
		if (in_array($buf->peek(), $rels)) {
			$set[] = $buf->get();
			$buf->read_set(SPACES);
			/*
			 * Make sure that an element specifier follows
			 */
			if (!$buf->more() || in_array($buf->peek(), $rels)) {
				trigger_error("Element specifier expected");
				break;
			}
		}

		/*
		 * Read element specifier
		 */
		$spec = read_elem($buf);
		if (spec_is_empty($spec)) {
			break;
		}

		$set[] = $spec;
		$buf->read_set(SPACES);
	}

	if ($buf->more()) {
		$ch = $buf->peek();
		trigger_error("Unexpected character '$ch' in $str");
	}
	return $set;
}

function spec_is_empty($spec)
{
	foreach ($spec as $val) {
		if (!empty($val)) return false;
	}
	return true;
}

function read_elem($buf)
{
	$spec = array(
		'tag' => '',
		'class' => '',
		'id' => '',
		'ext' => array()
	);

	$s = $buf;

	if (ctype_alpha($s->peek()) || $s->peek() == '-' || $s->peek() == '_') {
		$spec['tag'] = $s->read_set(IDCHARS);
	}

	if ($s->peek() == '.') {
		$s->get();
		$spec['class'] = $s->read_set(IDCHARS);
	}

	if ($s->peek() == '#') {
		$s->get();
		$spec['id'] = $s->read_set(IDCHARS);
	}

	while ($s->peek() == ':') {
		$s->get();
		$spec['ext'][] = $s->read_set(IDCHARS);
	}

	return $spec;
}

?>
