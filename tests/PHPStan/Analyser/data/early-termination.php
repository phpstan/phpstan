<?php

namespace EarlyTermination;

$something = rand(0, 10);
if ($something % 2 === 0) {
	$var = true;
} else {
	$foo = new Bar();
	$foo->doFoo();
}

die;
