<?php

namespace InheritanceOfInternalInterface;

interface Foo extends \InheritanceOfInternalInterfaceInInternalPath\Fooable
{

}

interface Foo2 extends \InheritanceOfInternalInterfaceInInternalPath\InternalFooable
{

}

interface Foo3 extends
	\InheritanceOfInternalInterfaceInInternalPath\Fooable,
	\InheritanceOfInternalInterfaceInInternalPath\InternalFooable,
	\InheritanceOfInternalInterfaceInInternalPath\InternalFooable2
{

}

interface Foo4 extends \InheritanceOfInternalInterfaceInExternalPath\Fooable
{

}

interface Foo5 extends \InheritanceOfInternalInterfaceInExternalPath\InternalFooable
{

}

interface Foo6 extends
	\InheritanceOfInternalInterfaceInExternalPath\Fooable,
	\InheritanceOfInternalInterfaceInExternalPath\InternalFooable,
	\InheritanceOfInternalInterfaceInExternalPath\InternalFooable2
{

}
