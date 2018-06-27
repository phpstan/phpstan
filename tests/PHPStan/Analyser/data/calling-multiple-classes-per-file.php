<?php

namespace CallingMultipleClasses;

function () {
    $foo = new \MultipleClasses\Foo();
    $bar = new \MultipleClasses\Bar();
    die;
};
