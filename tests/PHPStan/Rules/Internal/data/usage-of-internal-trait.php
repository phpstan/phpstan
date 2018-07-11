<?php

namespace UsageOfInternalTrait;

class Foo
{

	use \UsageOfInternalTraitInInternalPath\FooTrait;
	use \UsageOfInternalTraitInInternalPath\InternalFooTrait;

}

class Foo2
{

	use \UsageOfInternalTraitInInternalPath\FooTrait,
		\UsageOfInternalTraitInInternalPath\InternalFooTrait;

}

class Foo3
{

	use \UsageOfInternalTraitInExternalPath\FooTrait;
	use \UsageOfInternalTraitInExternalPath\InternalFooTrait;

}

class Foo4
{

	use \UsageOfInternalTraitInExternalPath\FooTrait,
		\UsageOfInternalTraitInExternalPath\InternalFooTrait;

}
