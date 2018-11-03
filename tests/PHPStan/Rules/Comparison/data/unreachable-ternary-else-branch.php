<?php

function (string $str) {

	$str ? 'foo' : 'bar';
	true ? 'foo' : 'bar'; // unreachable

	$str ?: 'bar';
	true ?: 'bar'; // unreachable

};
