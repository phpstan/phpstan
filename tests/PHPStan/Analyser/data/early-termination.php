<?php

namespace EarlyTermination;

$something = rand(0, 10);
if ($something % 2 === 0) {
	$var = true;
} else {
	$foo = new Bar();

	if ($something <= 5) {
		Bar::doBar();
	} elseif ($something <= 7) {
		$foo->doFoo();
	} else {
		baz();
	}
}

die;
