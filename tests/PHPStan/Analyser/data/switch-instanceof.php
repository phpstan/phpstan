<?php

namespace SwitchInstanceOf;

$foo = doFoo();
$bar = doBar();
$baz = doBaz();

switch (true) {
	case $foo instanceof Foo:
		break;
	case $bar instanceof Bar:
	case $baz instanceof Baz:
		die;
		break;
}
