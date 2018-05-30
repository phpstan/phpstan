<?php

namespace RemoveArrayFromIterable;

function test ($foo) : array {
	if (is_iterable($foo)) {
		return array_values(is_array($foo) ? $foo : iterator_to_array($foo));
	}
}

function test2($foo) : array {
	if (is_iterable($foo)) {
		if (is_array($foo)) {
			return array_values($foo);
		}

		return array_values(iterator_to_array($foo));
	}
}
