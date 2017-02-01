<?php

namespace ClassConstantNamespace;

Foo::class;
Bar::class;
self::class;
Foo::LOREM;
Foo::IPSUM;
Foo::DOLOR;
$bar::LOREM;

$foo = new Foo();
$foo::LOREM;
$foo::IPSUM;
$foo::DOLOR;

static::LOREM;
parent::LOREM;
