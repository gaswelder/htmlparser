<?php

class AssertException extends Exception
{
}

class TestCase
{
	function assertEquals($actual, $expected)
	{
		if ($actual !== $expected) {
			echo "expected: $expected\n";
			echo "actual:   $actual\n";
			throw new AssertException();
		}
	}

	function assertContains($substring, $string)
	{
		if (strpos($string, $substring) === false) {
			echo "expected '$string' to contain '$substring'\n";
			throw new AssertException();
		}
	}

	function assertNotContains($substring, $string)
	{
		if (strpos($string, $substring) !== false) {
			echo "expected '$string' to not contain '$substring'\n";
			throw new AssertException();
		}
	}

	function assertCount($n, $list)
	{
		$m = count($list);
		if ($m != $n) {
			$e = var_export($list);
			echo "expected count of '$e' to be $n, got $m\n";
			throw new AssertException();
		}
	}

	function assertInstanceOf($className, $obj)
	{
		if (!($obj instanceof $className)) {
			$cn = get_class($obj);
			echo "expected object to be instance of $className, got $cn\n";
			throw new AssertException();
		}
	}
}

function loadFile($path)
{
	$cc = get_declared_classes();
	require $path;
	$cc1 = get_declared_classes();
	return array_diff($cc1, $cc);
}

function matches($name, $patterns)
{
	if (count($patterns) == 0) {
		return true;
	}
	foreach ($patterns as $p) {
		if (str_contains($name, $p)) {
			return true;
		}
	}
	return false;
}

function runFile($path, $testPatterns)
{
	$fails = 0;
	$ok = 0;
	$skipped = 0;
	$newClasses = loadFile($path);
	foreach ($newClasses as $cn) {
		if (!preg_match('/Test$/', $cn)) {
			continue;
		}
		$test = new $cn();
		foreach (get_class_methods($test) as $name) {
			if (substr($name, 0, 4) != "test") {
				continue;
			}
			if (!matches(substr($name, 4), $testPatterns)) {
				$skipped++;
				continue;
			}
			try {
				call_user_func_array([$test, $name], []);
				echo "OK $name\n";
				$ok++;
			} catch (AssertException $e) {
				echo "FAIL $name\n";
				$fails++;
			}
		}
	}
	return [$ok, $fails, $skipped];
}

$testPatterns = [];
$args = array_slice($argv, 1);
if (count($args) > 0 && $args[0] == '-t') {
	array_shift($args);
	if (count($args) == 0) {
		fwrite(STDERR, "-t requires an argument\n");
		exit(1);
	}
	$testPatterns = explode(',', array_shift($args));
}

if (count($args) == 0) {
	fwrite(STDERR, "usage: php $argv[0] <test-file>...\n");
	exit(1);
}

$fails = 0;
$ok = 0;
$skipped = 0;
foreach ($args as $path) {
	echo $path, "\n";
	[$ok1, $fails1, $skipped1] = runFile($path, $testPatterns);
	$fails += $fails1;
	$ok += $ok1;
	$skipped += $skipped1;
	echo "\n";
}
echo "fails: $fails, successes: $ok, skipped: $skipped\n";
