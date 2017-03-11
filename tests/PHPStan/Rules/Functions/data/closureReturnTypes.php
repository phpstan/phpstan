<?php

namespace ClosureReturnTypes;

use SomeOtherNamespace\Baz;

function () {
    return 1;
    return 'foo';
    return;
};

function (): int {
    return 1;
    return 'foo';
};

function (): string {
    return 'foo';
    return 1;
};

function (): Foo {
    return new Foo();
    return new Bar();
};

function (): \SomeOtherNamespace\Foo {
    return new Foo();
    return new \SomeOtherNamespace\Foo();
};

function (): Baz {
    return new Foo();
    return new Baz();
};

function (): \Traversable {
    /** @var int[]|\Traversable $foo */
    $foo = doFoo();
    return $foo;
};
