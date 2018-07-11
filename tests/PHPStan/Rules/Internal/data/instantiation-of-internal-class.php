<?php

namespace InstantiationOfInternalClass;

$foo = new \InstantiationOfInternalClassInInternalPath\Foo();
$internalFoo = new \InstantiationOfInternalClassInInternalPath\InternalFoo();

$foo = new \InstantiationOfInternalClassInExternalPath\Foo();
$internalFoo = new \InstantiationOfInternalClassInExternalPath\InternalFoo();
