<?php

namespace InheritanceOfInternalClass;

$foo = new class extends \InheritanceOfInternalClassInInternalPath\Foo {

};

$internalFoo = new class extends \InheritanceOfInternalClassInInternalPath\InternalFoo {

};

$foo = new class extends \InheritanceOfInternalClassInExternalPath\Foo {

};

$internalFoo = new class extends \InheritanceOfInternalClassInExternalPath\InternalFoo {

};
