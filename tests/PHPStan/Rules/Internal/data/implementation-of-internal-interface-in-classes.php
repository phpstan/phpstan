<?php

namespace ImplementationOfInternalInterface;

class Foo implements \ImplementationOfInternalInterfaceInInternalPath\Fooable
{

}

class Foo2 implements \ImplementationOfInternalInterfaceInInternalPath\InternalFooable
{

}

class Foo3 implements
	\ImplementationOfInternalInterfaceInInternalPath\Fooable,
	\ImplementationOfInternalInterfaceInInternalPath\InternalFooable,
	\ImplementationOfInternalInterfaceInInternalPath\InternalFooable2
{

}

class Foo4 implements \ImplementationOfInternalInterfaceInExternalPath\Fooable
{

}

class Foo5 implements \ImplementationOfInternalInterfaceInExternalPath\InternalFooable
{

}

class Foo6 implements
	\ImplementationOfInternalInterfaceInExternalPath\Fooable,
	\ImplementationOfInternalInterfaceInExternalPath\InternalFooable,
	\ImplementationOfInternalInterfaceInExternalPath\InternalFooable2
{

}
