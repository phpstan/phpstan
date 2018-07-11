<?php

namespace AccessInternalProperty;

$foo = new \AccessInternalPropertyInInternalPath\Foo();

$foo->foo = 'foo';
$foo->foo;

$foo->internalFoo = 'internalFoo';
$foo->internalFoo;

$foo->fooFromTrait = 'fooFromTrait';
$foo->fooFromTrait;

$foo->internalFooFromTrait = 'internalFooFromTrait';
$foo->internalFooFromTrait;

$foo = new \AccessInternalPropertyInExternalPath\Foo();

$foo->foo = 'foo';
$foo->foo;

$foo->internalFoo = 'internalFoo';
$foo->internalFoo;

$foo->fooFromTrait = 'fooFromTrait';
$foo->fooFromTrait;

$foo->internalFooFromTrait = 'internalFooFromTrait';
$foo->internalFooFromTrait;
