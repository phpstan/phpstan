<?php declare(strict_types = 1);

namespace App;

function f(int $i): void {
	die;
}

f(1);
f('str');
