<?php

namespace SwitchInstanceOfNot;

$foo = doFoo();
$bar = doBar();
$baz = doBaz();

switch (false) {
	case $bar instanceof Bar:
		break;
	case !$baz instanceof Baz:
		'bazForSure';
		break;
	case $foo instanceof Foo:
		die;
}
