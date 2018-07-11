<?php

namespace AccessInternalStaticProperty;

\AccessInternalStaticPropertyInInternalPath\Foo::$foo = 'foo';
\AccessInternalStaticPropertyInInternalPath\Foo::$foo;

\AccessInternalStaticPropertyInInternalPath\Foo::$internalFoo = 'foo';
\AccessInternalStaticPropertyInInternalPath\Foo::$internalFoo;

$foo = new \AccessInternalStaticPropertyInInternalPath\Foo();

$foo::$foo = 'foo';
$foo::$foo;

$foo::$internalFoo = 'foo';
$foo::$internalFoo;

\AccessInternalStaticPropertyInInternalPath\FooTrait::$fooFromTrait = 'foo';
\AccessInternalStaticPropertyInInternalPath\FooTrait::$fooFromTrait;

\AccessInternalStaticPropertyInInternalPath\FooTrait::$internalFooFromTrait = 'foo';
\AccessInternalStaticPropertyInInternalPath\FooTrait::$internalFooFromTrait;

$foo = new \AccessInternalStaticPropertyInInternalPath\Foo();

$foo::$fooFromTrait = 'foo';
$foo::$fooFromTrait;

$foo::$internalFooFromTrait = 'foo';
$foo::$internalFooFromTrait;

\AccessInternalStaticPropertyInExternalPath\Foo::$foo = 'foo';
\AccessInternalStaticPropertyInExternalPath\Foo::$foo;

\AccessInternalStaticPropertyInExternalPath\Foo::$internalFoo = 'foo';
\AccessInternalStaticPropertyInExternalPath\Foo::$internalFoo;

$foo = new \AccessInternalStaticPropertyInExternalPath\Foo();

$foo::$foo = 'foo';
$foo::$foo;

$foo::$internalFoo = 'foo';
$foo::$internalFoo;

\AccessInternalStaticPropertyInExternalPath\FooTrait::$fooFromTrait = 'foo';
\AccessInternalStaticPropertyInExternalPath\FooTrait::$fooFromTrait;

\AccessInternalStaticPropertyInExternalPath\FooTrait::$internalFooFromTrait = 'foo';
\AccessInternalStaticPropertyInExternalPath\FooTrait::$internalFooFromTrait;

$foo = new \AccessInternalStaticPropertyInExternalPath\Foo();

$foo::$fooFromTrait = 'foo';
$foo::$fooFromTrait;

$foo::$internalFooFromTrait = 'foo';
$foo::$internalFooFromTrait;
