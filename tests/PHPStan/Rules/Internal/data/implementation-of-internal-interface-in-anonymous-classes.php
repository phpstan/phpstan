<?php

namespace ImplementationOfInternalInterface;

$fooable = new class implements \ImplementationOfInternalInterfaceInInternalPath\Fooable {

};

$fooable2 = new class implements \ImplementationOfInternalInterfaceInInternalPath\InternalFooable {

};

$fooable3 = new class implements
	\ImplementationOfInternalInterfaceInInternalPath\Fooable,
	\ImplementationOfInternalInterfaceInInternalPath\InternalFooable,
	\ImplementationOfInternalInterfaceInInternalPath\InternalFooable2 {

};

$fooable = new class implements \ImplementationOfInternalInterfaceInExternalPath\Fooable {

};

$fooable2 = new class implements \ImplementationOfInternalInterfaceInExternalPath\InternalFooable {

};

$fooable3 = new class implements
	\ImplementationOfInternalInterfaceInExternalPath\Fooable,
	\ImplementationOfInternalInterfaceInExternalPath\InternalFooable,
	\ImplementationOfInternalInterfaceInExternalPath\InternalFooable2 {

};
