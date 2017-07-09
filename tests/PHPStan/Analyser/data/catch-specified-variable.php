<?php

namespace TryCatchWithSpecifiedVariable;

/** @var string|null $foo */
$foo = doFoo();
if ($foo !== null) {
	return;
}

try {

} catch (FooException $foo) {
	die;
}
