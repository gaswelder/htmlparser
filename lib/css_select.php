<?php
namespace gaswelder\htmlparser;

function select($set, $selector)
{
	$query = parse_selector($selector);
	/*
	 * In general case a single query specifies several groups,
	 * separated by commas (for example, "p, blockquote").
	 * A parsed selector is thus an array of such "groups", for each
	 * of which we do separate filtering and then merge the results
	 * into one set.
	 */
	$result = array();
	foreach ($query as $group) {
		$result = array_merge($result, select_subgroup($set, $group));
	}
	return unique($result);
}

function unique($set)
{
	$u = array();
	foreach ($set as $obj) {
		if (!in_array($obj, $u, true)) {
			$u[] = $obj;
		}
	}
	return $u;
}

function select_subgroup($set, $spec)
{
	$results = array();

	while (!empty($spec)) {
		/*
		 * Find out how to search and what to search.
		 */
		$rel = ' ';

		/*
		 * If a modifier follows, get it and get the next
		 * token which this time will definitely be an element
		 * specifier.
		 */
		$tok = array_shift($spec);
		if (is_string($tok)) {
			$rel = $tok;
			$tok = array_shift($spec);
		}
		assert($tok instanceof element_selector);

		/*
		 * By default (rel=' ') we search the whole subtrees.
		 * Modifiers like '>' and '+' limit the search to immediate
		 * children or next siblings.
		 */
		foreach ($set as $tree) {
			$results = array_merge($results, search($tree, $rel, $tok));
		}
	}
	return $results;
}

/*
 * Search given tree for element specified by $spec
 * using method $rel ('>', '+', ' ').
 */
function search($tree, $rel, $spec)
{
	$match = array();

	if ($rel == '+') {
		if (match($tree->nextSibling, $spec)) {
			$match[] = $tree->nextSibling;
		}
	}
	else if ($rel == '>') {
		foreach ($tree->children as $child) {
			if (match($child, $spec)) {
				$match[] = $child;
			}
		}
	}
	else if ($rel == ' ') {
		foreach ($tree->children as $child) {
			if (match($child, $spec)) {
				$match[] = $child;
			}
			$match = array_merge($match, search($child, $rel, $spec));
		}
	}
	else {
		trigger_error("Unknown search modifier: '$rel'");
	}
	return $match;
}

function match($child, element_selector $spec)
{
	$v = $spec->tag;
	if ($v && $child->tagName != $v) {
		return false;
	}

	$v = $spec->class;
	if ($v && !in_array($v, $child->classList)) {
		return false;
	}

	$v = $spec->id;
	if ($v && $child->getAttribute('id') != $v) {
		return false;
	}

	$v = $spec->attrs;
	foreach ($v as $name => $val) {
		$chval = $child->getAttribute($name);
		/*
		 * If the node doesn't have it, return false.
		 */
		if ($chval === null) return false;
		/*
		 * If the selector specifies a concrete value and
		 * it doesn't match, return false.
		 */
		if ($val !== null && $val !== $chval) {
			return false;
		}
	}

	return true;
}

?>
