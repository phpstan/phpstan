<?php

namespace AnonymousFunction;

$integer = 1;
function (string $str) use ($integer, $bar) {
	die;
};
