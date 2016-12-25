<?php

namespace StrictComparison;

1 === 1;
1 === '1'; // wrong
1 !== '1'; // wrong
doFoo() === doBar();
1 === null;
(new Bar()) === 1; // wrong

/** @var Foo[]|Collection|bool $unionIterableType */
$unionIterableType = doFoo();
1 === $unionIterableType;
false === $unionIterableType;
$unionIterableType === [new Foo()];
$unionIterableType === new Collection();
