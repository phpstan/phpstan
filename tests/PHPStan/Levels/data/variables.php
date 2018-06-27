<?php

namespace Levels\Variables;

function (int $foo) {
    echo $foo;
    echo $bar;
    if (rand(0, 1)) {
        $baz = true;
    }

    echo $baz;

    if (isset($foo)) {
    }

    if (isset($bar)) {
    }

    if (isset($baz)) {
    }
};
