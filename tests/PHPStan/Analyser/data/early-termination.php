<?php

namespace EarlyTermination;

$something = rand(0, 10);
if ($something % 2 === 0) {
    $var = true;
} else {
    $foo = new Bar();

    if ($something <= 5) {
        Bar::doBar();
    } else {
        $foo->doFoo();
    }
}

die;
