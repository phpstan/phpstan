<?php

namespace UnresolvableTypes;

/**
 * @param array<int, int, int> $arrayWithTooManyArgs
 * @param iterable<int, int, int> $iterableWithTooManyArgs
 * @param \Foo<int> $genericFoo
 */
function test(
    $arrayWithTooManyArgs,
    $iterableWithTooManyArgs,
    $genericFoo
) {
    die;
}
