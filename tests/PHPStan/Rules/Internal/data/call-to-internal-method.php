<?php

namespace CheckInternalMethodCall;

$foo = new \CheckInternalMethodCallInInternalPath\Foo();
$foo->foo();
$foo->internalFoo();

$bar = new \CheckInternalMethodCallInInternalPath\Bar();
$bar->internalFoo();
$bar->internalFoo2();

$foo->fooFromTrait();
$foo->internalFooFromTrait();

$foo = new \CheckInternalMethodCallInExternalPath\Foo();
$foo->foo();
$foo->internalFoo();

$bar = new \CheckInternalMethodCallInExternalPath\Bar();
$bar->internalFoo();
$bar->internalFoo2();

$foo->fooFromTrait();
$foo->internalFooFromTrait();
