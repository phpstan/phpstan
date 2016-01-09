<?php

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Stmt\Function_;

$foo = new Function_();
$bar = $foo;
while ($foo instanceof ArrayDimFetch) {
	die;
}
