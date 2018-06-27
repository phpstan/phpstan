<?php declare(strict_types = 1);

namespace Test;

function () {
    $foo = new ClassWithToString();
    $foo->acceptsString($foo);
};
