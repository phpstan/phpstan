<?php

namespace SwitchInstanceOf;

/** @var object $object */
$object = doFoo();

$foo = doFoo();
$bar = doBar();
$baz = doBaz();

switch ($object) {
	case $foo instanceof Foo:
		break;
	case $bar instanceof Bar:
		break;
	case $baz instanceof Baz:
		die;
		break;
}
