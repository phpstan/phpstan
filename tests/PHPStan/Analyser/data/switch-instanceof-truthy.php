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
	case $baz instanceof Baz:
		die;
		break;
}
