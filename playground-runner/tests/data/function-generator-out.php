<?php

namespace Test;

use FooBar;
function foo() : iterable
{
    (yield 1);
    (yield 2);
    return;
}