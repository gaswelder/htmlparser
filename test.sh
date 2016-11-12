#!/bin/sh

for i in test/*.html; do
	php test.php "$i"
done
